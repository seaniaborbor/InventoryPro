<?php

namespace App\Controllers;

use App\Models\SupplierModel;
use App\Models\PurchaseModel;
use App\Models\AuditLogModel;

class Suppliers extends BaseController
{
    protected $supplierModel;
    protected $purchaseModel;
    protected $auditLogModel;
    
    public function __construct()
    {
        $this->supplierModel = new SupplierModel();
        $this->purchaseModel = new PurchaseModel();
        $this->auditLogModel = new AuditLogModel();
        
        if (!session()->get('isLoggedIn')) {
            return redirect()->to('/auth/login');
        }
    }
    
    /**
     * Suppliers List
     */
    public function index()
    {
        $data = [
            'title' => 'Suppliers',
            'suppliers' => $this->supplierModel->getAllWithStats(),
            'activePage' => 'purchases',
            'activeSubPage' => 'suppliers'
        ];
        
        return view('suppliers/index', $data);
    }
    
    /**
     * Store Supplier
     */
    public function store()
    {
        $rules = [
            'supplier_name' => 'required|min_length[2]|max_length[255]',
            'phone' => 'required|max_length[50]',
            'email' => 'permit_empty|valid_email|max_length[255]|is_unique[suppliers.email]',
            'contact_person' => 'permit_empty|max_length[255]'
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
            'supplier_name' => $this->request->getPost('supplier_name'),
            'contact_person' => $this->request->getPost('contact_person'),
            'phone' => $this->request->getPost('phone'),
            'email' => $this->request->getPost('email'),
            'address' => $this->request->getPost('address'),
            'tax_number' => $this->request->getPost('tax_number'),
            'payment_terms' => $this->request->getPost('payment_terms'),
            'created_by' => session()->get('user_id')
        ];
        
        if ($this->supplierModel->insert($data)) {
            $supplierId = $this->supplierModel->getInsertID();
            
            $this->auditLogModel->log(
                session()->get('user_id'),
                'supplier_create',
                'Supplier',
                $supplierId,
                null,
                $data
            );
            
            if ($this->request->isAJAX()) {
                return $this->response->setJSON([
                    'status' => 'success',
                    'message' => 'Supplier added successfully',
                    'supplier' => $this->supplierModel->find($supplierId)
                ]);
            }
            return redirect()->to('/suppliers')->with('success', 'Supplier added successfully');
        }
        
        if ($this->request->isAJAX()) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Failed to add supplier']);
        }
        return redirect()->back()->with('error', 'Failed to add supplier');
    }
    
    /**
     * Edit Supplier
     */
    public function edit($id)
    {
        $supplier = $this->supplierModel->getSupplierWithPurchases($id);
        
        if (!$supplier) {
            return redirect()->to('/suppliers')->with('error', 'Supplier not found');
        }
        
        $data = [
            'title' => 'Edit Supplier',
            'supplier' => $supplier,
            'activePage' => 'purchases'
        ];
        
        return view('suppliers/edit', $data);
    }
    
    /**
     * Update Supplier
     */
    public function update($id)
    {
        $supplier = $this->supplierModel->find($id);
        
        if (!$supplier) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Supplier not found']);
        }
        
        $rules = [
            'supplier_name' => 'required|min_length[2]|max_length[255]',
            'phone' => 'required|max_length[50]',
            'email' => 'permit_empty|valid_email|max_length[255]|is_unique[suppliers.email,id,' . $id . ']'
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
        
        $oldData = $supplier;
        $data = [
            'supplier_name' => $this->request->getPost('supplier_name'),
            'contact_person' => $this->request->getPost('contact_person'),
            'phone' => $this->request->getPost('phone'),
            'email' => $this->request->getPost('email'),
            'address' => $this->request->getPost('address'),
            'tax_number' => $this->request->getPost('tax_number'),
            'payment_terms' => $this->request->getPost('payment_terms'),
            'updated_by' => session()->get('user_id')
        ];
        
        if ($this->supplierModel->update($id, $data)) {
            $this->auditLogModel->log(
                session()->get('user_id'),
                'supplier_update',
                'Supplier',
                $id,
                $oldData,
                $data
            );
            
            if ($this->request->isAJAX()) {
                return $this->response->setJSON(['status' => 'success', 'message' => 'Supplier updated successfully']);
            }
            return redirect()->to('/suppliers')->with('success', 'Supplier updated successfully');
        }
        
        if ($this->request->isAJAX()) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Failed to update supplier']);
        }
        return redirect()->back()->with('error', 'Failed to update supplier');
    }
    
    /**
     * Delete Supplier
     */
    public function delete($id)
    {
        $supplier = $this->supplierModel->find($id);
        
        if (!$supplier) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Supplier not found']);
        }
        
        // Check if supplier has purchases
        $purchaseCount = $this->purchaseModel->where('supplier_id', $id)->countAllResults();
        
        if ($purchaseCount > 0) {
            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'Cannot delete supplier with ' . $purchaseCount . ' purchase(s)'
            ]);
        }
        
        $oldData = $supplier;
        
        if ($this->supplierModel->delete($id)) {
            $this->auditLogModel->log(
                session()->get('user_id'),
                'supplier_delete',
                'Supplier',
                $id,
                $oldData
            );
            
            return $this->response->setJSON(['status' => 'success', 'message' => 'Supplier deleted successfully']);
        }
        
        return $this->response->setJSON(['status' => 'error', 'message' => 'Failed to delete supplier']);
    }
    
    /**
     * View Supplier Details
     */
    public function view($id)
    {
        $supplier = $this->supplierModel->getSupplierWithPurchases($id);
        
        if (!$supplier) {
            return redirect()->to('/suppliers')->with('error', 'Supplier not found');
        }
        
        $data = [
            'title' => 'Supplier Details',
            'supplier' => $supplier,
            'activePage' => 'purchases'
        ];
        
        return view('suppliers/view', $data);
    }
    
    /**
     * Search Suppliers (AJAX)
     */
    public function search()
    {
        if (!$this->request->isAJAX()) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Invalid request']);
        }
        
        $keyword = $this->request->getGet('q');
        $suppliers = $this->supplierModel->search($keyword);
        
        return $this->response->setJSON(['status' => 'success', 'data' => $suppliers]);
    }
}