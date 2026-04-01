<?php

namespace App\Models;

use CodeIgniter\Model;

class SaleItemModel extends Model
{
    protected $table            = 'sale_items';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = ['sale_id', 'product_id', 'quantity', 'unit_price', 'discount', 'total_price'];

    // Dates
    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    /**
     * Get items with product details
     */
    public function getItemsWithProducts($saleId)
    {
        $builder = $this->builder();
        $builder->select('sale_items.*, products.product_name, products.sku, units.unit_name, units.unit_symbol');
        $builder->join('products', 'products.id = sale_items.product_id');
        $builder->join('units', 'units.id = products.unit_id', 'left');
        $builder->where('sale_items.sale_id', $saleId);
        
        return $builder->get()->getResultArray();
    }

    /**
     * Get top selling products
     */
    public function getTopSelling($limit = 10, $startDate = null, $endDate = null)
    {
        $builder = $this->builder();
        $builder->select('product_id, products.product_name, SUM(quantity) as total_quantity, SUM(total_price) as total_revenue');
        $builder->join('products', 'products.id = sale_items.product_id');
        $builder->groupBy('product_id');
        $builder->orderBy('total_quantity', 'DESC');
        $builder->limit($limit);
        
        if ($startDate && $endDate) {
            $builder->join('sales', 'sales.id = sale_items.sale_id');
            $builder->where('sale_date >=', $startDate);
            $builder->where('sale_date <=', $endDate);
        }
        
        return $builder->get()->getResultArray();
    }
}