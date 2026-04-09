<?php

namespace App\Controllers;

use App\Models\SaleModel;
use App\Models\SaleItemModel;
use App\Models\CustomerModel;
use App\Models\ProductModel;
use App\Models\StockMovementModel;
use App\Models\AuditLogModel;
use App\Models\SystemSettingModel;

class Sales extends BaseController
{
    protected $saleModel;
    protected $saleItemModel;
    protected $customerModel;
    protected $productModel;
    protected $stockMovementModel;
    protected $auditLogModel;
    protected $settingsModel;

    public function __construct()
    {
        $this->saleModel = new SaleModel();
        $this->saleItemModel = new SaleItemModel();
        $this->customerModel = new CustomerModel();
        $this->productModel = new ProductModel();
        $this->stockMovementModel = new StockMovementModel();
        $this->auditLogModel = new AuditLogModel();
        $this->settingsModel = new SystemSettingModel();

        if (!session()->get('isLoggedIn')) {
            return redirect()->to('/auth/login');
        }
    }

    /**
     * Sales List
     */
    public function index()
    {
        $data = [
            'title' => 'Sales',
            'sales' => $this->saleModel->orderBy('created_at', 'DESC')->paginate(20),
            'pager' => $this->saleModel->pager,
            'activePage' => 'sales',
            'activeSubPage' => 'sales'
        ];

        return view('sales/index', $data);
    }

    /**
     * Create Sale Form
     */
    public function create()
    {
        $data = [
            'title' => 'New Sale',
            'customers' => $this->customerModel->orderBy('customer_name', 'ASC')->findAll(),
            'products' => $this->productModel->where('quantity >', 0)->findAll(),
            'invoice_number' => $this->saleModel->generateInvoiceNumber(),
            'tax_rate' => $this->settingsModel->get('tax_rate', 0),
            'activePage' => 'sales',
            'activeSubPage' => 'create'
        ];

        return view('sales/create', $data);
    }

    /**
     * Store Sale - Complete Implementation
     */
    public function store()
    {
        // Check if AJAX request
        if (!$this->request->isAJAX()) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Invalid request']);
        }

        // Get JSON input
        $input = $this->request->getJSON(true);

        // Debug log
        log_message('debug', 'Sales store input: ' . json_encode($input));

        // Validate input
        if (empty($input) || empty($input['items'])) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'No items in sale']);
        }

        $items = $input['items'];
        $customerId = $input['customer_id'] ?? null;
        $currency = $input['currency'] ?? 'LRD';
        $paymentMethod = $input['payment_method'] ?? 'Cash';
        $paymentStatus = $input['payment_status'] ?? 'Unpaid';
        $amountPaid = $input['amount_paid'] ?? 0;
        $discount = $input['discount'] ?? 0;
        $notes = $input['notes'] ?? '';

        // Calculate totals
        $subtotal = 0;
        foreach ($items as $item) {
            $subtotal += $item['quantity'] * $item['unit_price'] - ($item['discount'] ?? 0);
        }

        $tax = 0; // Calculate tax if needed
        $totalAmount = $subtotal + $tax - $discount;
        $balanceDue = $paymentStatus === 'Paid' ? 0 : ($paymentStatus === 'Partial' ? $totalAmount - $amountPaid : $totalAmount);

        // Get customer name if customer_id is provided
        $customerName = 'Walk-in Customer';
        $customerPhone = null;
        $customerEmail = null;

        if ($customerId) {
            $customer = $this->customerModel->find($customerId);
            if ($customer) {
                $customerName = $customer['customer_name'];
                $customerPhone = $customer['phone'];
                $customerEmail = $customer['email'];
            }
        }

        // Create sale record
        $saleData = [
            'invoice_number' => $this->saleModel->generateInvoiceNumber(),
            'customer_id' => $customerId,
            'customer_name' => $customerName,
            'customer_phone' => $customerPhone,
            'customer_email' => $customerEmail,
            'sale_date' => date('Y-m-d'),
            'subtotal' => $subtotal,
            'tax' => $tax,
            'discount' => $discount,
            'total_amount' => $totalAmount,
            'currency' => $currency,
            'exchange_rate' => 1,
            'payment_method' => $paymentMethod,
            'payment_status' => $paymentStatus,
            'amount_paid' => $amountPaid,
            'balance_due' => $balanceDue,
            'notes' => $notes,
            'created_by' => session()->get('user_id')
        ];

        // Insert sale
        $saleId = $this->saleModel->insert($saleData);

        if (!$saleId) {
            log_message('error', 'Failed to create sale: ' . json_encode($this->saleModel->errors()));
            return $this->response->setJSON(['status' => 'error', 'message' => 'Failed to create sale']);
        }

        // Insert sale items and update inventory
        foreach ($items as $item) {
            $product = $this->productModel->find($item['product_id']);

            if (!$product) {
                continue;
            }

            // Check stock
            if ($product['quantity'] < $item['quantity']) {
                // Rollback - delete the sale
                $this->saleModel->delete($saleId);
                return $this->response->setJSON([
                    'status' => 'error',
                    'message' => 'Insufficient stock for product: ' . $product['product_name']
                ]);
            }

            // Add sale item
            $itemData = [
                'sale_id' => $saleId,
                'product_id' => $item['product_id'],
                'quantity' => $item['quantity'],
                'unit_price' => $item['unit_price'],
                'discount' => $item['discount'] ?? 0,
                'total_price' => ($item['quantity'] * $item['unit_price']) - ($item['discount'] ?? 0)
            ];

            $this->saleItemModel->insert($itemData);

            // Update inventory
            $oldQuantity = $product['quantity'];
            $newQuantity = $oldQuantity - $item['quantity'];

            $this->productModel->update($product['id'], [
                'quantity' => $newQuantity,
                'updated_by' => session()->get('user_id')
            ]);

            // Log stock movement
            $this->stockMovementModel->logMovement([
                'product_id' => $product['id'],
                'movement_type' => 'Sale',
                'reference_type' => 'sale',
                'reference_id' => $saleId,
                'quantity' => -$item['quantity'],
                'previous_quantity' => $oldQuantity,
                'new_quantity' => $newQuantity,
                'unit_price' => $item['unit_price'],
                'total_value' => $item['quantity'] * $item['unit_price'],
                'currency' => $currency,
                'created_by' => session()->get('user_id')
            ]);
        }

        // Log audit
        $this->auditLogModel->log(
            session()->get('user_id'),
            'sale_create',
            'Sale',
            $saleId,
            null,
            ['invoice_number' => $saleData['invoice_number'], 'total' => $totalAmount]
        );

        return $this->response->setJSON([
            'status' => 'success',
            'message' => 'Sale created successfully',
            'sale_id' => $saleId,
            'invoice_number' => $saleData['invoice_number']
        ]);
    }


    /**
     * View Sale Details
     */
    public function view($id)
    {
        $sale = $this->saleModel->getSaleWithItems($id);

        if (!$sale) {
            return redirect()->to('/sales')->with('error', 'Sale not found');
        }

        $data = [
            'title' => 'Sale #' . $sale['invoice_number'],
            'sale' => $sale,
            'activePage' => 'sales'
        ];

        return view('sales/view', $data);
    }

    /**
     * Invoice View/Print
     */
    public function invoice($id)
    {
        $sale = $this->saleModel->getSaleWithItems($id);

        if (!$sale) {
            return redirect()->to('/sales')->with('error', 'Sale not found');
        }

        $businessInfo = [
            'name' => $this->settingsModel->get('business_name', 'Innovative Graphics'),
            'address' => $this->settingsModel->get('business_address', 'Broad & Benson Streets, Monrovia, Liberia'),
            'phone' => $this->settingsModel->get('business_phone', '+231-778-651-747'),
            'email' => $this->settingsModel->get('business_email', 'info@innovativegraphics.com'),
            'logo' => $this->settingsModel->get('business_logo', null)
        ];

        $data = [
            'title' => 'Invoice #' . $sale['invoice_number'],
            'sale' => $sale,
            'business' => $businessInfo,
            'activePage' => 'sales'
        ];

        return view('sales/invoice', $data);
    }

    /**
     * Print Invoice
     */
    public function print($id)
    {
        $sale = $this->saleModel->getSaleWithItems($id);

        if (!$sale) {
            return redirect()->to('/sales')->with('error', 'Sale not found');
        }

        $businessInfo = [
            'name' => $this->settingsModel->get('business_name', 'Innovative Graphics'),
            'address' => $this->settingsModel->get('business_address', 'Broad & Benson Streets, Monrovia, Liberia'),
            'phone' => $this->settingsModel->get('business_phone', '+231-778-651-747'),
            'email' => $this->settingsModel->get('business_email', 'info@innovativegraphics.com')
        ];

        $data = [
            'sale' => $sale,
            'business' => $businessInfo
        ];

        return view('sales/print_invoice', $data);
    }

    /**
     * Email Invoice
     */
    public function email($id)
    {
        $sale = $this->saleModel->getSaleWithItems($id);

        if (!$sale) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Sale not found']);
        }

        if (!$sale['customer_email']) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Customer email not available']);
        }

        // Generate PDF
        $businessInfo = [
            'name' => $this->settingsModel->get('business_name', 'Innovative Graphics'),
            'address' => $this->settingsModel->get('business_address', 'Broad & Benson Streets, Monrovia, Liberia'),
            'phone' => $this->settingsModel->get('business_phone', '+231-778-651-747'),
            'email' => $this->settingsModel->get('business_email', 'info@innovativegraphics.com')
        ];

        $html = view('sales/print_invoice', ['sale' => $sale, 'business' => $businessInfo]);

        // Load PDF library
        $dompdf = new \Dompdf\Dompdf();
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();
        $pdf = $dompdf->output();

        // Send email
        $email = \Config\Services::email();
        $email->setTo($sale['customer_email']);
        $email->setFrom($businessInfo['email'], $businessInfo['name']);
        $email->setSubject('Invoice #' . $sale['invoice_number']);
        $email->setMessage("Dear " . ($sale['customer_name'] ?: 'Customer') . ",\n\nPlease find attached invoice #" . $sale['invoice_number'] . ".\n\nThank you for your business!\n\n" . $businessInfo['name']);
        $email->attach($pdf, 'application/pdf', 'invoice_' . $sale['invoice_number'] . '.pdf');

        if ($email->send()) {
            return $this->response->setJSON(['status' => 'success', 'message' => 'Invoice sent successfully']);
        }

        return $this->response->setJSON(['status' => 'error', 'message' => 'Failed to send email']);
    }

    /**
     * Add Payment
     */
    public function addPayment($id)
    {
        if (!$this->request->isAJAX()) {
            return redirect()->back()->with('error', 'Invalid request');
        }

        $sale = $this->saleModel->find($id);

        if (!$sale) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Sale not found']);
        }

        $amount = $this->request->getPost('amount');
        $paymentMethod = $this->request->getPost('payment_method');

        if ($amount <= 0) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Invalid payment amount']);
        }

        $newAmountPaid = $sale['amount_paid'] + $amount;
        $newBalanceDue = $sale['total_amount'] - $newAmountPaid;
        $newPaymentStatus = $newBalanceDue <= 0 ? 'Paid' : ($newAmountPaid > 0 ? 'Partial' : 'Unpaid');

        $oldData = $sale;
        $newData = [
            'amount_paid' => $newAmountPaid,
            'balance_due' => $newBalanceDue,
            'payment_status' => $newPaymentStatus,
            'updated_by' => session()->get('user_id')
        ];

        if ($this->saleModel->update($id, $newData)) {
            // Log payment
            $this->auditLogModel->log(
                session()->get('user_id'),
                'sale_payment',
                'Sale',
                $id,
                $oldData,
                $newData
            );

            return $this->response->setJSON([
                'status' => 'success',
                'message' => 'Payment added successfully',
                'data' => [
                    'amount_paid' => $newAmountPaid,
                    'balance_due' => $newBalanceDue,
                    'payment_status' => $newPaymentStatus
                ]
            ]);
        }

        return $this->response->setJSON(['status' => 'error', 'message' => 'Failed to add payment']);
    }

    /**
     * Get Products for Sale (AJAX)
     */
    public function getProducts()
    {
        if (!$this->request->isAJAX()) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Invalid request']);
        }

        $search = $this->request->getGet('search');
        $products = $this->productModel->where('quantity >', 0);

        if ($search) {
            $products->groupStart()
                ->like('product_name', $search)
                ->orLike('sku', $search)
                ->orLike('barcode', $search)
                ->groupEnd();
        }

        $products = $products->findAll(20);

        foreach ($products as &$product) {
            $product['formatted_price'] = formatCurrency($product['selling_price'], 'LRD');
            $product['formatted_stock'] = number_format($product['quantity'], 2);
        }

        return $this->response->setJSON(['status' => 'success', 'data' => $products]);
    }

    /**
     * Calculate Total (AJAX)
     */
    public function calculateTotal()
    {
        if (!$this->request->isAJAX()) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Invalid request']);
        }

        $items = $this->request->getPost('items');
        $discount = $this->request->getPost('discount') ?: 0;
        $taxRate = $this->settingsModel->get('tax_rate', 0);

        $subtotal = 0;
        foreach ($items as $item) {
            $subtotal += $item['quantity'] * $item['unit_price'];
        }

        $tax = $subtotal * ($taxRate / 100);
        $total = $subtotal + $tax - $discount;

        return $this->response->setJSON([
            'status' => 'success',
            'data' => [
                'subtotal' => $subtotal,
                'tax' => $tax,
                'discount' => $discount,
                'total' => $total,
                'formatted_subtotal' => formatCurrency($subtotal, 'LRD'),
                'formatted_tax' => formatCurrency($tax, 'LRD'),
                'formatted_discount' => formatCurrency($discount, 'LRD'),
                'formatted_total' => formatCurrency($total, 'LRD')
            ]
        ]);
    }

    /**
     * Get Exchange Rate
     */
    private function getExchangeRate($currency)
    {
        if ($currency === 'LRD') {
            return 1;
        }

        $rateModel = new \App\Models\CurrencyRateModel();
        $rate = $rateModel->getRate('USD', 'LRD');

        return $rate ?: 180;
    }

    /**
     * Generate QR Code for Invoice
     */
    private function generateQRCode($saleId)
    {
        // Simple QR code generation - you can use a library for more complex QR codes
        $url = base_url('sales/verify/' . $saleId);
        return 'https://chart.googleapis.com/chart?chs=150x150&cht=qr&chl=' . urlencode($url);
    }
}