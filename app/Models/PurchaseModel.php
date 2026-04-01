<?php

namespace App\Models;

use CodeIgniter\Model;

class PurchaseModel extends Model
{
    protected $table            = 'purchases';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = true;
    protected $protectFields    = true;
    protected $allowedFields    = [
        'purchase_number', 'supplier_id', 'supplier_name', 'invoice_number',
        'purchase_date', 'subtotal', 'tax', 'discount', 'total_amount',
        'currency', 'exchange_rate', 'payment_status', 'payment_method',
        'notes', 'status', 'created_by', 'updated_by'
    ];

    // Dates
    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';
    protected $deletedField  = 'deleted_at';

    // Validation
    protected $validationRules = [
        'purchase_number' => 'required|is_unique[purchases.purchase_number,id,{id}]',
        'purchase_date' => 'required|valid_date',
        'subtotal' => 'required|decimal',
        'total_amount' => 'required|decimal',
        'currency' => 'required|in_list[LRD,USD]',
        'payment_status' => 'required|in_list[Paid,Unpaid,Partial]',
        'status' => 'required|in_list[Draft,Received,Cancelled]'
    ];

    /**
     * Get purchase with items and supplier
     */
    public function getPurchaseWithItems($purchaseId)
    {
        $purchase = $this->find($purchaseId);
        if (!$purchase) return null;
        
        // Get items
        $itemModel = new PurchaseItemModel();
        $purchase['items'] = $itemModel->getItemsWithProducts($purchaseId);
        
        // Get supplier
        if ($purchase['supplier_id']) {
            $supplierModel = new SupplierModel();
            $purchase['supplier'] = $supplierModel->find($purchase['supplier_id']);
        }
        
        return $purchase;
    }

    /**
     * Get purchases by date range
     */
    public function getByDateRange($startDate, $endDate)
    {
        return $this->where('purchase_date >=', $startDate)
                    ->where('purchase_date <=', $endDate)
                    ->orderBy('purchase_date', 'DESC')
                    ->findAll();
    }

    /**
     * Get purchases by supplier
     */
    public function getBySupplier($supplierId)
    {
        return $this->where('supplier_id', $supplierId)
                    ->orderBy('purchase_date', 'DESC')
                    ->findAll();
    }

    /**
     * Get total purchases by date range
     */
    public function getTotalByDateRange($startDate, $endDate, $currency = null)
    {
        $builder = $this->builder();
        $builder->select('SUM(total_amount) as total', false);
        $builder->where('deleted_at', null);
        $builder->where('status', 'Received');

        if ($startDate) {
            $builder->where('purchase_date >=', $startDate);
        }

        if ($endDate) {
            $builder->where('purchase_date <=', $endDate);
        }
        
        if ($currency) {
            $builder->where('currency', $currency);
        }
        
        $result = $builder->get()->getRowArray();
        
        return (float) ($result['total'] ?? 0);
    }

    /**
     * Generate purchase number
     */
    public function generatePurchaseNumber()
    {
        $year = date('Y');
        $month = date('m');
        
        $lastPurchase = $this->where('purchase_number LIKE', "PO-{$year}{$month}%")
                             ->orderBy('id', 'DESC')
                             ->first();
        
        if ($lastPurchase) {
            $lastNumber = (int) substr($lastPurchase['purchase_number'], -4);
            $newNumber = $lastNumber + 1;
        } else {
            $newNumber = 1;
        }
        
        return "PO-{$year}{$month}-" . str_pad($newNumber, 4, '0', STR_PAD_LEFT);
    }

    /**
     * Receive purchase (update inventory)
     */
    public function receivePurchase($purchaseId, $userId)
    {
        $purchase = $this->find($purchaseId);
        if (!$purchase || $purchase['status'] !== 'Draft') return false;
        
        // Update purchase status
        $updated = $this->update($purchaseId, [
            'status' => 'Received',
            'updated_by' => $userId
        ]);
        
        if ($updated) {
            // Update inventory
            $itemModel = new PurchaseItemModel();
            $items = $itemModel->where('purchase_id', $purchaseId)->findAll();
            
            $productModel = new ProductModel();
            foreach ($items as $item) {
                $productModel->updateStock($item['product_id'], $item['quantity'], 'add', $userId);
            }
        }
        
        return $updated;
    }
}
