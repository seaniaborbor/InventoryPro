<?php

namespace App\Models;

use CodeIgniter\Model;

class ExpenseCategoryModel extends Model
{
    protected $table            = 'expense_categories';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = ['category_name', 'description'];

    // Dates
    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    // Validation
    protected $validationRules = [
        'category_name' => 'required|min_length[2]|max_length[100]|is_unique[expense_categories.category_name,id,{id}]'
    ];

    /**
     * Get categories with expense count
     */
    public function getAllWithExpenseCount()
    {
        $categories = $this->findAll();
        $expenseModel = new ExpenseModel();
        
        foreach ($categories as &$cat) {
            $cat['expense_count'] = $expenseModel->where('expense_category_id', $cat['id'])->countAllResults();
        }
        
        return $categories;
    }

    /**
     * Get options for dropdown
     */
    public function getOptions()
    {
        $categories = $this->orderBy('category_name', 'ASC')->findAll();
        $options = [];
        foreach ($categories as $cat) {
            $options[$cat['id']] = $cat['category_name'];
        }
        return $options;
    }
}