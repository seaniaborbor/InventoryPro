<?php

namespace App\Controllers;

use App\Models\CustomerModel;
use App\Models\AuditLogModel;

class Customers extends BaseController
{
    protected $customerModel;
    protected $auditLogModel;
    
    public function __construct()
    {
        $this->customerModel = new CustomerModel();
        $this->auditLogModel = new AuditLogModel();
        
        if (!session()->get('isLoggedIn')) {
            return redirect()->to('/auth/login');
        }
    }
    
    /**
     * Customers List
     */
    public function index()
    {
        $data = [
            'title' => 'Customers',
            'customers' => $this->customerModel->getAllWithStats(),
            'activePage' => 'sales'
        ];
        
        return view('customers/index', $data);
    }
    
    /**
     * Store Customer
     */
    public function store()
    {
        $rules = [
            'customer_name' => 'required|min_length[2]|max_length[255]',
            'phone' => 'required|max_length[50]',
            'email' => 'permit_empty|valid_email|max_length[255]|is_unique[customers.email]'
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
            'customer_name' => $this->request->getPost('customer_name'),
            'business_name' => $this->request->getPost('business_name'),
            'phone' => $this->request->getPost('phone'),
            'email' => $this->request->getPost('email'),
            'address' => $this->request->getPost('address'),
            'credit_limit' => $this->request->getPost('credit_limit') ?: 0,
            'created_by' => session()->get('user_id')
        ];
        
        if ($this->customerModel->insert($data)) {
            $customerId = $this->customerModel->getInsertID();
            
            $this->auditLogModel->log(
                session()->get('user_id'),
                'customer_create',
                'Customer',
                $customerId,
                null,
                $data
            );
            
            if ($this->request->isAJAX()) {
                return $this->response->setJSON([
                    'status' => 'success',
                    'message' => 'Customer added successfully',
                    'customer' => $this->customerModel->find($customerId)
                ]);
            }
            return redirect()->to('/customers')->with('success', 'Customer added successfully');
        }
        
        if ($this->request->isAJAX()) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Failed to add customer']);
        }
        return redirect()->back()->with('error', 'Failed to add customer');
    }
    
    /**
     * Edit Customer
     */
    public function edit($id)
    {
        $customer = $this->customerModel->getCustomerWithSales($id);
        
        if (!$customer) {
            return redirect()->to('/customers')->with('error', 'Customer not found');
        }
        
        $data = [
            'title' => 'Edit Customer',
            'customer' => $customer,
            'activePage' => 'sales'
        ];
        
        return view('customers/edit', $data);
    }
    
    /**
     * Update Customer
     */
    public function update($id)
    {
        $customer = $this->customerModel->find($id);
        
        if (!$customer) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Customer not found']);
        }
        
        $rules = [
            'customer_name' => 'required|min_length[2]|max_length[255]',
            'phone' => 'required|max_length[50]',
            'email' => 'permit_empty|valid_email|max_length[255]|is_unique[customers.email,id,' . $id . ']'
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
        
        $oldData = $customer;
        $data = [
            'customer_name' => $this->request->getPost('customer_name'),
            'business_name' => $this->request->getPost('business_name'),
            'phone' => $this->request->getPost('phone'),
            'email' => $this->request->getPost('email'),
            'address' => $this->request->getPost('address'),
            'credit_limit' => $this->request->getPost('credit_limit') ?: 0,
            'updated_by' => session()->get('user_id')
        ];
        
        if ($this->customerModel->update($id, $data)) {
            $this->auditLogModel->log(
                session()->get('user_id'),
                'customer_update',
                'Customer',
                $id,
                $oldData,
                $data
            );
            
            if ($this->request->isAJAX()) {
                return $this->response->setJSON(['status' => 'success', 'message' => 'Customer updated successfully']);
            }
            return redirect()->to('/customers')->with('success', 'Customer updated successfully');
        }
        
        if ($this->request->isAJAX()) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Failed to update customer']);
        }
        return redirect()->back()->with('error', 'Failed to update customer');
    }
    
    /**
     * Delete Customer
     */
    public function delete($id)
    {
        $customer = $this->customerModel->find($id);
        
        if (!$customer) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Customer not found']);
        }
        
        // Check if customer has sales
        $saleModel = new \App\Models\SaleModel();
        $saleCount = $saleModel->where('customer_id', $id)->countAllResults();
        
        if ($saleCount > 0) {
            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'Cannot delete customer with ' . $saleCount . ' sale(s). You can deactivate instead.'
            ]);
        }
        
        $oldData = $customer;
        
        if ($this->customerModel->delete($id)) {
            $this->auditLogModel->log(
                session()->get('user_id'),
                'customer_delete',
                'Customer',
                $id,
                $oldData
            );
            
            return $this->response->setJSON(['status' => 'success', 'message' => 'Customer deleted successfully']);
        }
        
        return $this->response->setJSON(['status' => 'error', 'message' => 'Failed to delete customer']);
    }
    
    /**
     * View Customer Details
     */
    public function view($id)
    {
        $customer = $this->customerModel->getCustomerWithSales($id);
        
        if (!$customer) {
            return redirect()->to('/customers')->with('error', 'Customer not found');
        }
        
        $data = [
            'title' => 'Customer Details',
            'customer' => $customer,
            'activePage' => 'sales'
        ];
        
        return view('customers/view', $data);
    }
    
    /**
     * Search Customers (AJAX)
     */
    public function search()
    {
        if (!$this->request->isAJAX()) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Invalid request']);
        }
        
        $keyword = $this->request->getGet('q');
        $customers = $this->customerModel->search($keyword);
        
        return $this->response->setJSON(['status' => 'success', 'data' => $customers]);
    }
}