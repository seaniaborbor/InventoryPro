<?php

namespace App\Models;

use CodeIgniter\Model;

class ProductionJobItemModel extends Model
{
    protected $table            = 'production_job_items';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = true;
    protected $protectFields    = true;
    protected $allowedFields    = [
        'production_job_id', 'item_id', 'quantity_required', 'quantity_used',
        'unit_cost', 'total_cost', 'notes'
    ];

    // Dates
    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';
    protected $deletedField  = 'deleted_at';

    /**
     * Get items for a production job with product details
     */
    public function getItemsWithDetails($jobId)
    {
        return $this->select('production_job_items.*, products.product_name, products.product_code, products.unit')
            ->join('products', 'products.id = production_job_items.item_id', 'left')
            ->where('production_job_items.production_job_id', $jobId)
            ->findAll();
    }

    /**
     * Get total cost for a job
     */
    public function getTotalCost($jobId)
    {
        $result = $this->selectSum('total_cost')
            ->where('production_job_id', $jobId)
            ->first();

        return $result['total_cost'] ?? 0;
    }
}