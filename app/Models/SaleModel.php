<?php

namespace App\Models;

use CodeIgniter\Model;

class SaleModel extends Model
{
    protected $table            = 'sales';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = true;
    protected $protectFields    = true;
    protected $allowedFields    = [
        'invoice_number', 'customer_id', 'customer_name', 'customer_phone', 'customer_email',
        'sale_date', 'subtotal', 'tax', 'discount', 'total_amount', 'currency', 'exchange_rate',
        'payment_method', 'payment_status', 'amount_paid', 'balance_due', 'notes', 'qr_code',
        'created_by', 'updated_by'
    ];

    // Dates
    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';
    protected $deletedField  = 'deleted_at';

    // Validation
    protected $validationRules = [
        'invoice_number' => 'required|is_unique[sales.invoice_number,id,{id}]',
        'sale_date' => 'required|valid_date',
        'subtotal' => 'required|decimal',
        'total_amount' => 'required|decimal',
        'currency' => 'required|in_list[LRD,USD]',
        'payment_method' => 'required',
        'payment_status' => 'required|in_list[Paid,Unpaid,Partial]'
    ];

    /**
     * Get sale with items and customer
     */
    public function getSaleWithItems($saleId)
    {
        $sale = $this->find($saleId);
        if (!$sale) return null;
        
        // Get items
        $itemModel = new SaleItemModel();
        $sale['items'] = $itemModel->getItemsWithProducts($saleId);
        
        // Get customer
        if ($sale['customer_id']) {
            $customerModel = new CustomerModel();
            $sale['customer'] = $customerModel->find($sale['customer_id']);
        }
        
        return $sale;
    }

    /**
     * Get sales by date range
     */
    public function getByDateRange($startDate, $endDate)
    {
        return $this->where('sale_date >=', $startDate)
                    ->where('sale_date <=', $endDate)
                    ->orderBy('sale_date', 'DESC')
                    ->findAll();
    }

    /**
     * Get sales by customer
     */
    public function getByCustomer($customerId)
    {
        return $this->where('customer_id', $customerId)
                    ->orderBy('sale_date', 'DESC')
                    ->findAll();
    }

    /**
     * Get total sales by date range
     */
    public function getTotalByDateRange($startDate, $endDate, $currency = null)
    {
        $builder = $this->builder();
        $builder->select('SUM(total_amount) as total', false);
        $builder->where('deleted_at', null);

        if ($startDate) {
            $builder->where('sale_date >=', $startDate);
        }

        if ($endDate) {
            $builder->where('sale_date <=', $endDate);
        }
        
        if ($currency) {
            $builder->where('currency', $currency);
        }
        
        $result = $builder->get()->getRowArray();
        
        return (float) ($result['total'] ?? 0);
    }

    /**
     * Generate invoice number
     */
    public function generateInvoiceNumber()
    {
        $year = date('Y');
        $month = date('m');
        
        $lastSale = $this->where('invoice_number LIKE', "INV-{$year}{$month}%")
                         ->orderBy('id', 'DESC')
                         ->first();
        
        if ($lastSale) {
            $lastNumber = (int) substr($lastSale['invoice_number'], -4);
            $newNumber = $lastNumber + 1;
        } else {
            $newNumber = 1;
        }
        
        return "INV-{$year}{$month}-" . str_pad($newNumber, 4, '0', STR_PAD_LEFT);
    }

    /**
     * Get daily sales summary
     */
    public function getDailySales($date)
    {
        $sales = $this->where('sale_date', $date)->findAll();
        
        $summary = [
            'total_amount' => 0,
            'count' => count($sales),
            'by_currency' => ['LRD' => 0, 'USD' => 0],
            'by_method' => []
        ];
        
        foreach ($sales as $sale) {
            $summary['total_amount'] += $sale['total_amount'];
            $summary['by_currency'][$sale['currency']] += $sale['total_amount'];
            
            if (!isset($summary['by_method'][$sale['payment_method']])) {
                $summary['by_method'][$sale['payment_method']] = 0;
            }
            $summary['by_method'][$sale['payment_method']] += $sale['total_amount'];
        }
        
        return $summary;
    }

    /**
     * Complete sale (deduct inventory)
     */
    public function completeSale($saleId, $userId)
    {
        $sale = $this->find($saleId);
        if (!$sale) return false;
        
        // Update inventory
        $itemModel = new SaleItemModel();
        $items = $itemModel->where('sale_id', $saleId)->findAll();
        
        $productModel = new ProductModel();
        foreach ($items as $item) {
            $productModel->updateStock($item['product_id'], $item['quantity'], 'subtract', $userId);
        }
        
        return true;
    }
}
