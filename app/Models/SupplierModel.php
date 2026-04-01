<?php

namespace App\Models;

use CodeIgniter\Model;

class SupplierModel extends Model
{
    protected $table            = 'suppliers';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = true;
    protected $protectFields    = true;
    protected $allowedFields    = [
        'supplier_name', 'contact_person', 'phone', 'email', 
        'address', 'created_by', 'updated_by'
    ];

    // Dates
    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';
    protected $deletedField  = 'deleted_at';

    // Validation
    protected $validationRules = [
        'supplier_name' => 'required|min_length[2]|max_length[255]',
        'phone' => 'required|max_length[50]',
        'email' => 'permit_empty|valid_email|max_length[255]'
    ];

    /**
     * Get supplier with purchase history
     */
    public function getSupplierWithPurchases($supplierId)
    {
        $supplier = $this->find($supplierId);
        if (!$supplier) return null;
        
        $purchaseModel = new PurchaseModel();
        $supplier['purchases'] = $purchaseModel->where('supplier_id', $supplierId)
                                               ->orderBy('purchase_date', 'DESC')
                                               ->limit(10)
                                               ->find();
        
        return $supplier;
    }

    /**
     * Get suppliers with purchase count and total spent
     */
    public function getAllWithStats()
    {
        $suppliers = $this->findAll();
        $purchaseModel = new PurchaseModel();
        
        foreach ($suppliers as &$supplier) {
            // Get purchase count
            $supplier['purchase_count'] = $purchaseModel->where('supplier_id', $supplier['id'])->countAllResults();
            
            // Get total spent
            $totalSpent = $purchaseModel->select('SUM(total_amount) as total')
                                       ->where('supplier_id', $supplier['id'])
                                       ->where('status', 'Received')
                                       ->first();
            $supplier['total_spent'] = $totalSpent['total'] ?? 0;
        }
        
        return $suppliers;
    }

    /**
     * Get supplier options for dropdown
     */
    public function getOptions()
    {
        $suppliers = $this->orderBy('supplier_name', 'ASC')->findAll();
        $options = [];
        foreach ($suppliers as $supplier) {
            $options[$supplier['id']] = $supplier['supplier_name'];
        }
        return $options;
    }

    /**
     * Search suppliers
     */
    public function search($keyword)
    {
        return $this->like('supplier_name', $keyword)
                    ->orLike('contact_person', $keyword)
                    ->orLike('phone', $keyword)
                    ->orLike('email', $keyword)
                    ->findAll();
    }
}