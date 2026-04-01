<?php

namespace App\Models;

use CodeIgniter\Model;

class ExpenseModel extends Model
{
    protected $table            = 'expenses';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = true;
    protected $protectFields    = true;
    protected $allowedFields    = [
        'expense_category_id', 'amount', 'currency', 'exchange_rate',
        'expense_date', 'description', 'receipt_image', 'created_by', 'updated_by'
    ];

    // Dates
    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';
    protected $deletedField  = 'deleted_at';

    /**
     * Get expense with category
     */
    public function getExpenseWithCategory($expenseId)
    {
        $builder = $this->builder();
        $builder->select('expenses.*, expense_categories.category_name');
        $builder->join('expense_categories', 'expense_categories.id = expenses.expense_category_id');
        $builder->where('expenses.id', $expenseId);
        
        return $builder->get()->getRowArray();
    }

    /**
     * Get expenses by date range
     */
    public function getByDateRange($startDate, $endDate)
    {
        $builder = $this->builder();
        $builder->where('deleted_at', null);

        if ($startDate) {
            $builder->where('expense_date >=', $startDate);
        }

        if ($endDate) {
            $builder->where('expense_date <=', $endDate);
        }

        return $builder->orderBy('expense_date', 'DESC')->get()->getResultArray();
    }

    /**
     * Get expenses by category
     */
    public function getByCategory($categoryId, $startDate = null, $endDate = null)
    {
        $builder = $this->builder();
        $builder->where('deleted_at', null);
        $builder->where('expense_category_id', $categoryId);
        
        if ($startDate) {
            $builder->where('expense_date >=', $startDate);
        }

        if ($endDate) {
            $builder->where('expense_date <=', $endDate);
        }
        
        return $builder->orderBy('expense_date', 'DESC')->get()->getResultArray();
    }

    /**
     * Get expense summary by category
     */
    public function getSummaryByCategory($startDate, $endDate, $currency = null, $categoryId = null)
    {
        $builder = $this->builder();
        $builder->select('expense_categories.id as category_id, expense_categories.category_name, expenses.currency, SUM(expenses.amount) as total_amount, COUNT(expenses.id) as expense_count');
        $builder->join('expense_categories', 'expense_categories.id = expenses.expense_category_id');
        $builder->where('expenses.deleted_at', null);

        if ($startDate) {
            $builder->where('expense_date >=', $startDate);
        }

        if ($endDate) {
            $builder->where('expense_date <=', $endDate);
        }

        $builder->groupBy('expense_category_id, expenses.currency');
        $builder->orderBy('total_amount', 'DESC');
        
        if ($currency) {
            $builder->where('currency', $currency);
        }

        if ($categoryId) {
            $builder->where('expenses.expense_category_id', $categoryId);
        }
        
        return $builder->get()->getResultArray();
    }

    /**
     * Get total expenses by date range
     */
    public function getTotalByDateRange($startDate, $endDate, $currency = null)
    {
        $builder = $this->builder();
        $builder->select('SUM(amount) as total', false);
        $builder->where('deleted_at', null);

        if ($startDate) {
            $builder->where('expense_date >=', $startDate);
        }

        if ($endDate) {
            $builder->where('expense_date <=', $endDate);
        }
        
        if ($currency) {
            $builder->where('currency', $currency);
        }
        
        $result = $builder->get()->getRowArray();
        
        return (float) ($result['total'] ?? 0);
    }

    /**
     * Get detailed expenses with category and user metadata
     */
    public function getDetailedExpenses($startDate = null, $endDate = null, $currency = null, $categoryId = null, $createdBy = null)
    {
        $builder = $this->builder();
        $builder->select('expenses.*, expense_categories.category_name, creator.full_name as created_by_name, updater.full_name as updated_by_name');
        $builder->join('expense_categories', 'expense_categories.id = expenses.expense_category_id');
        $builder->join('users as creator', 'creator.id = expenses.created_by', 'left');
        $builder->join('users as updater', 'updater.id = expenses.updated_by', 'left');
        $builder->where('expenses.deleted_at', null);

        if ($startDate) {
            $builder->where('expense_date >=', $startDate);
        }

        if ($endDate) {
            $builder->where('expense_date <=', $endDate);
        }

        if ($currency) {
            $builder->where('expenses.currency', $currency);
        }

        if ($categoryId) {
            $builder->where('expenses.expense_category_id', $categoryId);
        }

        if ($createdBy) {
            $builder->where('expenses.created_by', $createdBy);
        }

        return $builder->orderBy('expense_date', 'DESC')
                       ->orderBy('expenses.created_at', 'DESC')
                       ->get()
                       ->getResultArray();
    }

    /**
     * Get expense summary by creator and currency
     */
    public function getSummaryByUser($startDate = null, $endDate = null, $currency = null, $categoryId = null)
    {
        $builder = $this->builder();
        $builder->select('users.id as user_id, users.full_name, expenses.currency, SUM(expenses.amount) as total_amount, COUNT(expenses.id) as expense_count');
        $builder->join('users', 'users.id = expenses.created_by', 'left');
        $builder->where('expenses.deleted_at', null);

        if ($startDate) {
            $builder->where('expense_date >=', $startDate);
        }

        if ($endDate) {
            $builder->where('expense_date <=', $endDate);
        }

        if ($currency) {
            $builder->where('expenses.currency', $currency);
        }

        if ($categoryId) {
            $builder->where('expenses.expense_category_id', $categoryId);
        }

        $builder->groupBy('expenses.created_by, expenses.currency');
        $builder->orderBy('total_amount', 'DESC');

        return $builder->get()->getResultArray();
    }
}
