<?php

namespace App\Controllers;

use App\Models\PurchaseModel;
use App\Models\PurchaseItemModel;
use App\Models\SupplierModel;
use App\Models\ProductModel;
use App\Models\StockMovementModel;
use App\Models\AuditLogModel;

class Purchases extends BaseController
{
    protected $purchaseModel;
    protected $purchaseItemModel;
    protected $supplierModel;
    protected $productModel;
    protected $stockMovementModel;
    protected $auditLogModel;
    
    public function __construct()
    {
        $this->purchaseModel = new PurchaseModel();
        $this->purchaseItemModel = new PurchaseItemModel();
        $this->supplierModel = new SupplierModel();
        $this->productModel = new ProductModel();
        $this->stockMovementModel = new StockMovementModel();
        $this->auditLogModel = new AuditLogModel();
        
        if (!session()->get('isLoggedIn')) {
            return redirect()->to('/auth/login');
        }
    }
    
    /**
     * Purchases List
     */
    public function index()
    {
        $data = [
            'title' => 'Purchases',
            'purchases' => $this->purchaseModel->orderBy('created_at', 'DESC')->paginate(20),
            'pager' => $this->purchaseModel->pager,
            'activePage' => 'purchases',
            'activeSubPage' => 'purchases'
        ];
        
        return view('purchases/index', $data);
    }
    
    /**
     * Create Purchase Form
     */
    public function create()
    {
        $data = [
            'title' => 'New Purchase',
            'suppliers' => $this->supplierModel->orderBy('supplier_name', 'ASC')->findAll(),
            'products' => $this->productModel->findAll(),
            'purchase_number' => $this->purchaseModel->generatePurchaseNumber(),
            'activePage' => 'purchases',
            'activeSubPage' => 'create'
        ];
        
        return view('purchases/create', $data);
    }
    
    /**
     * Store Purchase - FIXED VERSION
     */
    public function store()
    {
        // Check if AJAX request
        if (!$this->request->isAJAX()) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Invalid request']);
        }
        
        // Get POST data (supports both JSON and form data)
        if (strpos($this->request->getHeaderLine('Content-Type'), 'application/json') !== false) {
            $input = $this->request->getJSON(true);
        } else {
            $input = $this->request->getPost();
        }
        
        log_message('debug', 'Purchase store input: ' . json_encode($input));
        
        // Validate required fields
        if (empty($input['supplier_id'])) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Supplier is required']);
        }
        
        if (empty($input['items']) || !is_array($input['items']) || count($input['items']) === 0) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'At least one product is required']);
        }
        
        // Calculate totals
        $subtotal = 0;
        foreach ($input['items'] as $item) {
            $subtotal += $item['quantity'] * $item['unit_price'];
        }
        
        $discount = isset($input['discount']) ? (float) $input['discount'] : 0;
        $tax = isset($input['tax']) ? (float) $input['tax'] : 0;
        $totalAmount = $subtotal + $tax - $discount;
        
        // Prepare purchase data
        $purchaseData = [
            'purchase_number' => $this->purchaseModel->generatePurchaseNumber(),
            'supplier_id' => $input['supplier_id'],
            'supplier_name' => null, // Will be populated from supplier
            'invoice_number' => $input['invoice_number'] ?? null,
            'purchase_date' => $input['purchase_date'] ?? date('Y-m-d'),
            'subtotal' => $subtotal,
            'tax' => $tax,
            'discount' => $discount,
            'total_amount' => $totalAmount,
            'currency' => $input['currency'] ?? 'LRD',
            'exchange_rate' => 1,
            'payment_status' => $input['payment_status'] ?? 'Unpaid',
            'payment_method' => $input['payment_method'] ?? null,
            'notes' => $input['notes'] ?? null,
            'status' => 'Draft',
            'created_by' => session()->get('user_id')
        ];
        
        // Get supplier name
        $supplier = $this->supplierModel->find($input['supplier_id']);
        if ($supplier) {
            $purchaseData['supplier_name'] = $supplier['supplier_name'];
        }
        
        // Insert purchase
        $purchaseId = $this->purchaseModel->insert($purchaseData);
        
        if (!$purchaseId) {
            log_message('error', 'Failed to create purchase: ' . json_encode($this->purchaseModel->errors()));
            return $this->response->setJSON(['status' => 'error', 'message' => 'Failed to create purchase']);
        }
        
        // Insert purchase items
        foreach ($input['items'] as $item) {
            $itemData = [
                'purchase_id' => $purchaseId,
                'product_id' => $item['product_id'],
                'quantity' => $item['quantity'],
                'unit_price' => $item['unit_price'],
                'total_price' => $item['quantity'] * $item['unit_price']
            ];
            $this->purchaseItemModel->insert($itemData);
        }
        
        // Log audit
        $this->auditLogModel->log(
            session()->get('user_id'),
            'purchase_create',
            'Purchase',
            $purchaseId,
            null,
            $purchaseData
        );
        
        return $this->response->setJSON([
            'status' => 'success',
            'message' => 'Purchase order created successfully',
            'purchase_id' => $purchaseId,
            'purchase_number' => $purchaseData['purchase_number']
        ]);
    }
    
    /**
     * View Purchase
     */
    public function view($id)
    {
        $purchase = $this->purchaseModel->getPurchaseWithItems($id);
        
        if (!$purchase) {
            return redirect()->to('/purchases')->with('error', 'Purchase not found');
        }
        
        $data = [
            'title' => 'Purchase #' . $purchase['purchase_number'],
            'purchase' => $purchase,
            'activePage' => 'purchases'
        ];
        
        return view('purchases/view', $data);
    }
    
    /**
     * Receive Purchase (add to inventory)
     */
    public function receive($id)
    {
        if (!$this->request->isAJAX()) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Invalid request']);
        }
        
        $purchase = $this->purchaseModel->find($id);
        
        if (!$purchase) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Purchase not found']);
        }
        
        if ($purchase['status'] !== 'Draft') {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Purchase already received or cancelled']);
        }
        
        // Get purchase items
        $items = $this->purchaseItemModel->where('purchase_id', $id)->findAll();
        
        // Update inventory for each product
        foreach ($items as $item) {
            $product = $this->productModel->find($item['product_id']);
            
            if ($product) {
                $oldQuantity = $product['quantity'];
                $newQuantity = $oldQuantity + $item['quantity'];
                
                // Update product quantity
                $this->productModel->update($item['product_id'], [
                    'quantity' => $newQuantity,
                    'updated_by' => session()->get('user_id')
                ]);
                
                // Log stock movement
                $this->stockMovementModel->logMovement([
                    'product_id' => $item['product_id'],
                    'movement_type' => 'Purchase',
                    'reference_type' => 'purchase',
                    'reference_id' => $id,
                    'quantity' => $item['quantity'],
                    'previous_quantity' => $oldQuantity,
                    'new_quantity' => $newQuantity,
                    'unit_price' => $item['unit_price'],
                    'total_value' => $item['total_price'],
                    'currency' => $purchase['currency'],
                    'created_by' => session()->get('user_id')
                ]);
            }
        }
        
        // Update purchase status
        $oldData = $purchase;
        $newData = ['status' => 'Received', 'updated_by' => session()->get('user_id')];
        $this->purchaseModel->update($id, $newData);
        
        // Log audit
        $this->auditLogModel->log(
            session()->get('user_id'),
            'purchase_receive',
            'Purchase',
            $id,
            $oldData,
            $newData
        );
        
        return $this->response->setJSON([
            'status' => 'success',
            'message' => 'Purchase received and inventory updated successfully'
        ]);
    }
    
    /**
     * Add Payment to Purchase
     */
    public function addPayment($id)
    {
        if (!$this->request->isAJAX()) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Invalid request']);
        }
        
        $purchase = $this->purchaseModel->find($id);
        
        if (!$purchase) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Purchase not found']);
        }
        
        $amount = $this->request->getPost('amount');
        
        if (!$amount || $amount <= 0) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Invalid payment amount']);
        }
        
        // For simplicity, just update payment status (full payment)
        $oldData = $purchase;
        $newData = ['payment_status' => 'Paid', 'updated_by' => session()->get('user_id')];
        $this->purchaseModel->update($id, $newData);
        
        // Log audit
        $this->auditLogModel->log(
            session()->get('user_id'),
            'purchase_payment',
            'Purchase',
            $id,
            $oldData,
            $newData
        );
        
        return $this->response->setJSON([
            'status' => 'success',
            'message' => 'Payment recorded successfully'
        ]);
    }
    
    /**
     * Get Products for Purchase (AJAX)
     */
    public function getProducts()
    {
        if (!$this->request->isAJAX()) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Invalid request']);
        }
        
        $search = $this->request->getGet('search');
        $products = $this->productModel;
        
        if ($search) {
            $products->groupStart()
                     ->like('product_name', $search)
                     ->orLike('sku', $search)
                     ->groupEnd();
        }
        
        $products = $products->findAll(20);
        
        $formattedProducts = [];
        foreach ($products as $product) {
            $formattedProducts[] = [
                'id' => $product['id'],
                'name' => $product['product_name'],
                'sku' => $product['sku'],
                'purchase_price' => $product['purchase_price'],
                'selling_price' => $product['selling_price'],
                'unit_id' => $product['unit_id']
            ];
        }
        
        return $this->response->setJSON(['status' => 'success', 'data' => $formattedProducts]);
    }
}