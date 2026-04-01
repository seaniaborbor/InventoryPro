<?php

namespace App\Controllers;

use App\Models\ExpenseModel;
use App\Models\ExpenseCategoryModel;
use App\Models\AuditLogModel;
use App\Models\SystemSettingModel;

class Expenses extends BaseController
{
    protected $expenseModel;
    protected $expenseCategoryModel;
    protected $auditLogModel;
    protected $settingsModel;
    
    public function __construct()
    {
        $this->expenseModel = new ExpenseModel();
        $this->expenseCategoryModel = new ExpenseCategoryModel();
        $this->auditLogModel = new AuditLogModel();
        $this->settingsModel = new SystemSettingModel();
        
        if (!session()->get('isLoggedIn')) {
            return redirect()->to('/auth/login');
        }
    }
    
    /**
     * Expenses List
     */
    public function index()
    {
        $startDate = $this->request->getGet('start_date') ?: date('Y-m-01');
        $endDate = $this->request->getGet('end_date') ?: date('Y-m-t');
        $categoryId = $this->request->getGet('category');
        $currency = $this->request->getGet('currency');
        
        $expenses = $this->expenseModel
            ->select('expenses.*, expense_categories.category_name, 
                     creator.username as created_by_name, 
                     updater.username as updated_by_name')
            ->join('expense_categories', 'expense_categories.id = expenses.expense_category_id')
            ->join('users as creator', 'creator.id = expenses.created_by', 'left')
            ->join('users as updater', 'updater.id = expenses.updated_by', 'left');
        
        if ($startDate && $endDate) {
            $expenses->where('expense_date >=', $startDate)
                     ->where('expense_date <=', $endDate);
        }
        
        if ($categoryId) {
            $expenses->where('expense_category_id', $categoryId);
        }
        
        if ($currency) {
            $expenses->where('currency', $currency);
        }
        
        $expenses = $expenses->orderBy('expense_date', 'DESC')->paginate(20);
        
        // Get summary by category
        $summary = $this->expenseModel->getSummaryByCategory($startDate, $endDate);
        
        $data = [
            'title' => 'Expenses',
            'expenses' => $expenses,
            'pager' => $this->expenseModel->pager,
            'categories' => $this->expenseCategoryModel->findAll(),
            'summary' => $summary,
            'startDate' => $startDate,
            'endDate' => $endDate,
            'selectedCategory' => $categoryId,
            'selectedCurrency' => $currency,
            'activePage' => 'expenses'
        ];
        
        return view('expenses/index', $data);
    }
    
    /**
     * Create Expense Form
     */
    public function create()
    {
        $data = [
            'title' => 'Add Expense',
            'categories' => $this->expenseCategoryModel->findAll(),
            'activePage' => 'expenses'
        ];
        
        return view('expenses/create', $data);
    }
    
    /**
     * Store Expense
     */
    public function store()
    {
        $rules = [
            'expense_category_id' => 'required|is_not_unique[expense_categories.id]',
            'amount' => 'required|decimal',
            'currency' => 'required|in_list[LRD,USD]',
            'expense_date' => 'required|valid_date',
            'description' => 'permit_empty|max_length[500]'
        ];
        
        if (!$this->validate($rules)) {
            if ($this->request->isAJAX()) {
                return $this->response->setJSON([
                    'status' => 'error',
                    'errors' => $this->validator->getErrors()
                ]);
            }
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }
        
        $data = [
            'expense_category_id' => $this->request->getPost('expense_category_id'),
            'amount' => $this->request->getPost('amount'),
            'currency' => $this->request->getPost('currency'),
            'exchange_rate' => $this->getExchangeRate($this->request->getPost('currency')),
            'expense_date' => $this->request->getPost('expense_date'),
            'description' => $this->request->getPost('description'),
            'created_by' => session()->get('user_id')
        ];
        
        // Handle receipt upload
        $receipt = $this->request->getFile('receipt');
        if ($receipt && $receipt->isValid() && !$receipt->hasMoved()) {
            $newName = $receipt->getRandomName();
            $receipt->move('uploads/receipts', $newName);
            $data['receipt_image'] = 'uploads/receipts/' . $newName;
        }
        
        if ($this->expenseModel->insert($data)) {
            $expenseId = $this->expenseModel->getInsertID();
            
            $this->auditLogModel->log(
                session()->get('user_id'),
                'expense_create',
                'Expense',
                $expenseId,
                null,
                $data
            );
            
            if ($this->request->isAJAX()) {
                return $this->response->setJSON(['status' => 'success', 'message' => 'Expense added successfully']);
            }
            return redirect()->to('/expenses')->with('success', 'Expense added successfully');
        }
        
        if ($this->request->isAJAX()) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Failed to add expense']);
        }
        return redirect()->back()->with('error', 'Failed to add expense');
    }
    
    /**
     * Edit Expense
     */
    public function edit($id)
    {
        $expense = $this->expenseModel
            ->select('expenses.*, expense_categories.category_name,
                     creator.username as created_by_name,
                     updater.username as updated_by_name')
            ->join('expense_categories', 'expense_categories.id = expenses.expense_category_id')
            ->join('users as creator', 'creator.id = expenses.created_by', 'left')
            ->join('users as updater', 'updater.id = expenses.updated_by', 'left')
            ->where('expenses.id', $id)
            ->first();
        
        if (!$expense) {
            return redirect()->to('/expenses')->with('error', 'Expense not found');
        }
        
        $data = [
            'title' => 'Edit Expense',
            'expense' => $expense,
            'categories' => $this->expenseCategoryModel->findAll(),
            'activePage' => 'expenses'
        ];
        
        return view('expenses/edit', $data);
    }
    
    /**
     * Update Expense
     */
    public function update($id)
    {
        $expense = $this->expenseModel->find($id);
        
        if (!$expense) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Expense not found']);
        }
        
        $rules = [
            'expense_category_id' => 'required|is_not_unique[expense_categories.id]',
            'amount' => 'required|decimal',
            'currency' => 'required|in_list[LRD,USD]',
            'expense_date' => 'required|valid_date'
        ];
        
        if (!$this->validate($rules)) {
            if ($this->request->isAJAX()) {
                return $this->response->setJSON([
                    'status' => 'error',
                    'errors' => $this->validator->getErrors()
                ]);
            }
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }
        
        $oldData = $expense;
        $data = [
            'expense_category_id' => $this->request->getPost('expense_category_id'),
            'amount' => $this->request->getPost('amount'),
            'currency' => $this->request->getPost('currency'),
            'exchange_rate' => $this->getExchangeRate($this->request->getPost('currency')),
            'expense_date' => $this->request->getPost('expense_date'),
            'description' => $this->request->getPost('description'),
            'updated_by' => session()->get('user_id')
        ];
        
        // Handle receipt upload
        $receipt = $this->request->getFile('receipt');
        if ($receipt && $receipt->isValid() && !$receipt->hasMoved()) {
            // Delete old receipt if exists
            if ($expense['receipt_image'] && file_exists($expense['receipt_image'])) {
                unlink($expense['receipt_image']);
            }
            $newName = $receipt->getRandomName();
            $receipt->move('uploads/receipts', $newName);
            $data['receipt_image'] = 'uploads/receipts/' . $newName;
        }
        
        if ($this->expenseModel->update($id, $data)) {
            $this->auditLogModel->log(
                session()->get('user_id'),
                'expense_update',
                'Expense',
                $id,
                $oldData,
                $data
            );
            
            if ($this->request->isAJAX()) {
                return $this->response->setJSON(['status' => 'success', 'message' => 'Expense updated successfully']);
            }
            return redirect()->to('/expenses')->with('success', 'Expense updated successfully');
        }
        
        if ($this->request->isAJAX()) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Failed to update expense']);
        }
        return redirect()->back()->with('error', 'Failed to update expense');
    }
    
    /**
     * Delete Expense
     */
    public function delete($id)
    {
        $expense = $this->expenseModel->find($id);
        
        if (!$expense) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Expense not found']);
        }
        
        $oldData = $expense;
        
        // Delete receipt file if exists
        if ($expense['receipt_image'] && file_exists($expense['receipt_image'])) {
            unlink($expense['receipt_image']);
        }
        
        if ($this->expenseModel->delete($id)) {
            $this->auditLogModel->log(
                session()->get('user_id'),
                'expense_delete',
                'Expense',
                $id,
                $oldData
            );
            
            return $this->response->setJSON(['status' => 'success', 'message' => 'Expense deleted successfully']);
        }
        
        return $this->response->setJSON(['status' => 'error', 'message' => 'Failed to delete expense']);
    }
    
    /**
     * Expense Categories Management
     */
    public function categories()
    {
        $data = [
            'title' => 'Expense Categories',
            'categories' => $this->expenseCategoryModel->getAllWithExpenseCount(),
            'activePage' => 'expenses'
        ];
        
        return view('expenses/categories', $data);
    }
    
    /**
     * Store Category
     */
    public function storeCategory()
    {
        $rules = [
            'category_name' => 'required|min_length[2]|max_length[100]|is_unique[expense_categories.category_name]'
        ];
        
        if (!$this->validate($rules)) {
            if ($this->request->isAJAX()) {
                return $this->response->setJSON([
                    'status' => 'error',
                    'errors' => $this->validator->getErrors()
                ]);
            }
            return redirect()->back()->with('error', 'Category name is required and must be unique');
        }
        
        $data = [
            'category_name' => $this->request->getPost('category_name'),
            'description' => $this->request->getPost('description')
        ];
        
        if ($this->expenseCategoryModel->insert($data)) {
            $this->auditLogModel->log(
                session()->get('user_id'),
                'expense_category_create',
                'ExpenseCategory',
                $this->expenseCategoryModel->getInsertID(),
                null,
                $data
            );
            
            if ($this->request->isAJAX()) {
                return $this->response->setJSON(['status' => 'success', 'message' => 'Category created successfully']);
            }
            return redirect()->to('/expenses/categories')->with('success', 'Category created successfully');
        }
        
        if ($this->request->isAJAX()) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Failed to create category']);
        }
        return redirect()->back()->with('error', 'Failed to create category');
    }
    
    /**
     * Update Category
     */
    public function updateCategory($id)
    {
        $category = $this->expenseCategoryModel->find($id);
        
        if (!$category) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Category not found']);
        }
        
        $rules = [
            'category_name' => 'required|min_length[2]|max_length[100]|is_unique[expense_categories.category_name,id,' . $id . ']'
        ];
        
        if (!$this->validate($rules)) {
            return $this->response->setJSON([
                'status' => 'error',
                'errors' => $this->validator->getErrors()
            ]);
        }
        
        $oldData = $category;
        $data = [
            'category_name' => $this->request->getPost('category_name'),
            'description' => $this->request->getPost('description')
        ];
        
        if ($this->expenseCategoryModel->update($id, $data)) {
            $this->auditLogModel->log(
                session()->get('user_id'),
                'expense_category_update',
                'ExpenseCategory',
                $id,
                $oldData,
                $data
            );
            
            return $this->response->setJSON(['status' => 'success', 'message' => 'Category updated successfully']);
        }
        
        return $this->response->setJSON(['status' => 'error', 'message' => 'Failed to update category']);
    }
    
    /**
     * Delete Category
     */
    public function deleteCategory($id)
    {
        $category = $this->expenseCategoryModel->find($id);
        
        if (!$category) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Category not found']);
        }
        
        // Check if category has expenses
        $expenseCount = $this->expenseModel->where('expense_category_id', $id)->countAllResults();
        
        if ($expenseCount > 0) {
            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'Cannot delete category with ' . $expenseCount . ' expense(s)'
            ]);
        }
        
        $oldData = $category;
        
        if ($this->expenseCategoryModel->delete($id)) {
            $this->auditLogModel->log(
                session()->get('user_id'),
                'expense_category_delete',
                'ExpenseCategory',
                $id,
                $oldData
            );
            
            return $this->response->setJSON(['status' => 'success', 'message' => 'Category deleted successfully']);
        }
        
        return $this->response->setJSON(['status' => 'error', 'message' => 'Failed to delete category']);
    }
    
    /**
     * Get Exchange Rate
     */
    private function getExchangeRate($currency)
    {
        if ($currency === 'LRD') {
            return 1;
        }
        
        $rateModel = new \App\Models\CurrencyRateModel();
        $rate = $rateModel->getRate('USD', 'LRD');
        
        return $rate ?: 180;
    }
}