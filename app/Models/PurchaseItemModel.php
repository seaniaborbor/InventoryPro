<?php

namespace App\Models;

use CodeIgniter\Model;

class PurchaseItemModel extends Model
{
    protected $table            = 'purchase_items';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = ['purchase_id', 'product_id', 'quantity', 'unit_price', 'total_price'];

    // Dates
    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    /**
     * Get items with product details
     */
    public function getItemsWithProducts($purchaseId)
    {
        $builder = $this->builder();
        $builder->select('purchase_items.*, products.product_name, products.sku, units.unit_name, units.unit_symbol');
        $builder->join('products', 'products.id = purchase_items.product_id');
        $builder->join('units', 'units.id = products.unit_id', 'left');
        $builder->where('purchase_items.purchase_id', $purchaseId);
        
        return $builder->get()->getResultArray();
    }
}