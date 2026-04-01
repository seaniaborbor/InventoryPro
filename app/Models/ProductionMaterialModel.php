<?php

namespace App\Models;

use CodeIgniter\Model;

class ProductionMaterialModel extends Model
{
    protected $table            = 'production_materials';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = ['production_job_id', 'product_id', 'quantity_used', 'unit_cost', 'total_cost'];

    // Dates
    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    /**
     * Get materials with product details
     */
    public function getMaterialsWithProducts($jobId)
    {
        $builder = $this->builder();
        $builder->select('production_materials.*, products.product_name, products.sku, units.unit_name, units.unit_symbol');
        $builder->join('products', 'products.id = production_materials.product_id');
        $builder->join('units', 'units.id = products.unit_id', 'left');
        $builder->where('production_materials.production_job_id', $jobId);
        
        return $builder->get()->getResultArray();
    }

    /**
     * Get material usage summary
     */
    public function getMaterialUsageSummary($startDate, $endDate)
    {
        $builder = $this->builder();
        $builder->select('product_id, products.product_name, SUM(quantity_used) as total_quantity, SUM(total_cost) as total_cost');
        $builder->join('production_jobs', 'production_jobs.id = production_materials.production_job_id');
        $builder->join('products', 'products.id = production_materials.product_id');
        $builder->where('production_jobs.production_date >=', $startDate);
        $builder->where('production_jobs.production_date <=', $endDate);
        $builder->where('production_jobs.status', 'Completed');
        $builder->groupBy('product_id');
        $builder->orderBy('total_quantity', 'DESC');
        
        return $builder->get()->getResultArray();
    }
}