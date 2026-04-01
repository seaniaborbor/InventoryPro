<?php

namespace App\Models;

use CodeIgniter\Model;

class StockMovementModel extends Model
{
    protected $table            = 'stock_movements';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = [
        'product_id', 'movement_type', 'reference_type', 'reference_id',
        'quantity', 'previous_quantity', 'new_quantity', 'unit_price',
        'total_value', 'currency', 'exchange_rate', 'created_by'
    ];

    // Dates - IMPORTANT: Don't set createdField here, let timestamps handle it
    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = false;

    /**
     * Log stock movement - FIXED VERSION
     */
    public function logMovement($data)
    {
        // Ensure required fields are present
        if (!isset($data['product_id']) || !isset($data['quantity'])) {
            log_message('error', 'StockMovementModel::logMovement - Missing required fields');
            return false;
        }
        
        // Build clean data array with string keys only
        $insertData = [];
        
        // Add each field individually with string keys
        $insertData['product_id'] = (int) $data['product_id'];
        $insertData['movement_type'] = isset($data['movement_type']) ? (string) $data['movement_type'] : 'Adjustment';
        $insertData['reference_type'] = isset($data['reference_type']) ? (string) $data['reference_type'] : 'manual';
        $insertData['reference_id'] = isset($data['reference_id']) ? (int) $data['reference_id'] : 0;
        $insertData['quantity'] = (float) $data['quantity'];
        $insertData['previous_quantity'] = isset($data['previous_quantity']) ? (float) $data['previous_quantity'] : 0;
        $insertData['new_quantity'] = isset($data['new_quantity']) ? (float) $data['new_quantity'] : 0;
        $insertData['created_by'] = isset($data['created_by']) ? (int) $data['created_by'] : session()->get('user_id');
        
        // Optional fields
        if (isset($data['unit_price'])) {
            $insertData['unit_price'] = (float) $data['unit_price'];
        }
        if (isset($data['total_value'])) {
            $insertData['total_value'] = (float) $data['total_value'];
        }
        if (isset($data['currency'])) {
            $insertData['currency'] = (string) $data['currency'];
        }
        if (isset($data['exchange_rate'])) {
            $insertData['exchange_rate'] = (float) $data['exchange_rate'];
        }
        
        log_message('debug', 'StockMovementModel::logMovement - Inserting data: ' . json_encode($insertData));
        
        // Use direct database insert to avoid model issues
        $db = \Config\Database::connect();
        $builder = $db->table($this->table);
        
        try {
            $result = $builder->insert($insertData);
            if ($result) {
                return $db->insertID();
            }
            log_message('error', 'StockMovementModel::logMovement - Insert failed');
            return false;
        } catch (\Exception $e) {
            log_message('error', 'StockMovementModel::logMovement - Error: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Get movements by product
     */
    public function getByProduct($productId, $limit = 100)
    {
        return $this->where('product_id', $productId)
                    ->orderBy('created_at', 'DESC')
                    ->limit($limit)
                    ->findAll();
    }

    /**
     * Get movements by date range
     */
    public function getByDateRange($startDate, $endDate)
    {
        return $this->where('created_at >=', $startDate)
                    ->where('created_at <=', $endDate)
                    ->orderBy('created_at', 'DESC')
                    ->findAll();
    }

    /**
     * Get stock movement summary
     */
    public function getSummary($startDate, $endDate)
    {
        $summary = [
            'purchases' => 0,
            'sales' => 0,
            'production' => 0,
            'adjustments' => 0
        ];
        
        $movements = $this->getByDateRange($startDate, $endDate);
        
        foreach ($movements as $movement) {
            $type = strtolower($movement['movement_type']);
            if (isset($summary[$type])) {
                $summary[$type] += abs($movement['quantity']);
            }
        }
        
        return $summary;
    }
    
    /**
     * Get all movements with product names
     */
    public function getAllWithProductNames()
    {
        return $this->select('stock_movements.*, products.product_name')
                    ->join('products', 'products.id = stock_movements.product_id', 'left')
                    ->orderBy('created_at', 'DESC')
                    ->findAll();
    }
    
    /**
     * Get movements for a specific product with pagination
     */
    public function getProductMovements($productId, $limit = 50)
    {
        return $this->where('product_id', $productId)
                    ->orderBy('created_at', 'DESC')
                    ->limit($limit)
                    ->findAll();
    }
}