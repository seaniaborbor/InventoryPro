<?php

namespace App\Models;

use CodeIgniter\Model;

class CustomerModel extends Model
{
    protected $table            = 'customers';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = true;
    protected $protectFields    = true;
    protected $allowedFields    = [
        'customer_name', 'business_name', 'phone', 'email', 
        'address', 'credit_limit', 'payment_terms', 'created_by', 'updated_by'
    ];

    // Dates
    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';
    protected $deletedField  = 'deleted_at';

    // Validation
    protected $validationRules = [
        'customer_name' => 'required|min_length[2]|max_length[255]',
        'phone' => 'required|max_length[50]',
        'email' => 'permit_empty|valid_email|max_length[255]',
        'credit_limit' => 'permit_empty|decimal'
    ];

    /**
     * Get customer with sales history
     */
    public function getCustomerWithSales($customerId)
    {
        $customer = $this->find($customerId);
        if (!$customer) return null;
        
        $saleModel = new SaleModel();
        $customer['sales'] = $saleModel->where('customer_id', $customerId)
                                       ->orderBy('sale_date', 'DESC')
                                       ->limit(10)
                                       ->find();
        
        // Calculate total spent
        $totalSpent = $saleModel->select('SUM(total_amount) as total')
                               ->where('customer_id', $customerId)
                               ->where('payment_status', 'Paid')
                               ->first();
        $customer['total_spent'] = $totalSpent['total'] ?? 0;
        
        return $customer;
    }

    /**
     * Get customers with sales count and total spent
     */
    public function getAllWithStats()
    {
        $customers = $this->findAll();
        $saleModel = new SaleModel();
        
        foreach ($customers as &$customer) {
            // Get sales count
            $customer['sales_count'] = $saleModel->where('customer_id', $customer['id'])->countAllResults();
            
            // Get total spent
            $totalSpent = $saleModel->select('SUM(total_amount) as total')
                                   ->where('customer_id', $customer['id'])
                                   ->where('payment_status', 'Paid')
                                   ->first();
            $customer['total_spent'] = $totalSpent['total'] ?? 0;
            
            // Get unpaid balance
            $balance = $saleModel->select('SUM(balance_due) as total')
                                ->where('customer_id', $customer['id'])
                                ->where('payment_status !=', 'Paid')
                                ->first();
            $customer['balance_due'] = $balance['total'] ?? 0;
        }
        
        return $customers;
    }

    /**
     * Get customer options for dropdown
     */
    public function getOptions()
    {
        $customers = $this->orderBy('customer_name', 'ASC')->findAll();
        $options = [];
        foreach ($customers as $customer) {
            $options[$customer['id']] = $customer['customer_name'] . 
                                        ($customer['business_name'] ? ' (' . $customer['business_name'] . ')' : '');
        }
        return $options;
    }

    /**
     * Search customers
     */
    public function search($keyword)
    {
        return $this->like('customer_name', $keyword)
                    ->orLike('business_name', $keyword)
                    ->orLike('phone', $keyword)
                    ->orLike('email', $keyword)
                    ->findAll();
    }

    /**
     * Get customers with credit limit exceeded
     */
    public function getCustomersExceedingCreditLimit()
    {
        $customers = $this->findAll();
        $saleModel = new SaleModel();
        $exceeding = [];
        
        foreach ($customers as $customer) {
            if ($customer['credit_limit'] > 0) {
                $balance = $saleModel->select('SUM(balance_due) as total')
                                    ->where('customer_id', $customer['id'])
                                    ->where('payment_status !=', 'Paid')
                                    ->first();
                
                if (($balance['total'] ?? 0) > $customer['credit_limit']) {
                    $customer['balance_due'] = $balance['total'];
                    $exceeding[] = $customer;
                }
            }
        }
        
        return $exceeding;
    }
}