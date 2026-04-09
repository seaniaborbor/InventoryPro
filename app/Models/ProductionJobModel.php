<?php

namespace App\Models;

use CodeIgniter\Model;

class ProductionJobModel extends Model
{
    protected $table            = 'production_jobs';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = true;
    protected $protectFields    = true;
    protected $allowedFields    = [
        'job_reference', 'job_name', 'customer_id', 'production_date', 'finished_product_id',
        'production_category_id', 'quantity_produced', 'total_material_cost', 'currency', 'exchange_rate',
        'notes', 'status', 'payment_status', 'amount_paid', 'created_by', 'updated_by'
    ];

    // Dates
    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';
    protected $deletedField  = 'deleted_at';

    /**
     * Get production job with materials and finished product
     */
    public function getJobWithDetails($jobId)
    {
        $job = $this->find($jobId);
        if (!$job) return null;

        // Get materials used
        $materialModel = new ProductionMaterialModel();
        $job['materials'] = $materialModel->getMaterialsWithProducts($jobId);

        // Get category name
        if ($job['production_category_id']) {
            $categoryModel = new \App\Models\ProductionCategoryModel();
            $category = $categoryModel->find($job['production_category_id']);
            $job['category_name'] = $category ? $category['category_name'] : 'Unknown';
        }

        // Get customer name
        if ($job['customer_id']) {
            $customerModel = new \App\Models\CustomerModel();
            $customer = $customerModel->find($job['customer_id']);
            $job['customer_name'] = $customer ? $customer['customer_name'] : 'Unknown';
        }

        // Get creator and updater user info
        $userModel = new \App\Models\UserModel();
        if ($job['created_by']) {
            $creator = $userModel->find($job['created_by']);
            $job['creator_name'] = $creator ? $creator['full_name'] : 'Unknown';
            $job['created_at_display'] = $job['created_at'] ? date('M j, Y H:i', strtotime($job['created_at'])) : '';
        }
        if ($job['updated_by']) {
            $updater = $userModel->find($job['updated_by']);
            $job['updater_name'] = $updater ? $updater['full_name'] : 'Unknown';
            $job['updated_at_display'] = $job['updated_at'] ? date('M j, Y H:i', strtotime($job['updated_at'])) : '';
        }

        return $job;
    }

    /**
     * Get jobs by date range
     */
    public function getByDateRange($startDate, $endDate)
    {
        return $this->where('production_date >=', $startDate)
                    ->where('production_date <=', $endDate)
                    ->orderBy('production_date', 'DESC')
                    ->findAll();
    }

    /**
     * Complete production job (deduct materials)
     */
    public function completeJob($jobId, $userId)
    {
        $job = $this->find($jobId);
        if (!$job || $job['status'] !== 'Draft') return false;
        
        // Update job status
        $updated = $this->update($jobId, [
            'status' => 'Completed',
            'updated_by' => $userId
        ]);
        
        if ($updated) {
            // Deduct materials from inventory
            $materialModel = new ProductionMaterialModel();
            $materials = $materialModel->where('production_job_id', $jobId)->findAll();
            
            $productModel = new ProductModel();
            foreach ($materials as $material) {
                $productModel->updateStock($material['product_id'], $material['quantity_used'], 'subtract', $userId);
            }
            
            // Add finished product to inventory if applicable
            if ($job['finished_product_id'] && $job['quantity_produced'] > 0) {
                $productModel->updateStock($job['finished_product_id'], $job['quantity_produced'], 'add', $userId);
            }
        }
        
        return $updated;
    }

    /**
     * Generate job reference number
     */
    public function generateJobReference()
    {
        $year = date('Y');
        $month = date('m');
        
        $lastJob = $this->where('job_reference LIKE', "JOB-{$year}{$month}%")
                        ->orderBy('id', 'DESC')
                        ->first();
        
        if ($lastJob) {
            $lastNumber = (int) substr($lastJob['job_reference'], -4);
            $newNumber = $lastNumber + 1;
        } else {
            $newNumber = 1;
        }
        
        return "JOB-{$year}{$month}-" . str_pad($newNumber, 4, '0', STR_PAD_LEFT);
    }

    /**
     * Get production cost summary
     */
    public function getCostSummary($startDate, $endDate)
    {
        $jobs = $this->where('production_date >=', $startDate)
                     ->where('production_date <=', $endDate)
                     ->where('status', 'Completed')
                     ->findAll();
        
        $summary = [
            'total_cost' => 0,
            'job_count' => count($jobs),
            'by_currency' => ['LRD' => 0, 'USD' => 0]
        ];
        
        foreach ($jobs as $job) {
            $summary['total_cost'] += $job['total_material_cost'];
            $summary['by_currency'][$job['currency']] += $job['total_material_cost'];
        }
        
        return $summary;
    }
}