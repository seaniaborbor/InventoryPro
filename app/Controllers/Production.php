<?php

namespace App\Controllers;

use App\Models\ProductionJobModel;
use App\Models\ProductionJobItemModel;
use App\Models\ProductionMaterialModel;
use App\Models\BomTemplateModel;
use App\Models\BomTemplateItemModel;
use App\Models\ProductModel;
use App\Models\StockMovementModel;
use App\Models\AuditLogModel;
use App\Models\ProductionCategoryModel;
use App\Models\CustomerModel;

class Production extends BaseController
{
    protected $productionJobModel;
    protected $productionJobItemModel;
    protected $productionMaterialModel;
    protected $bomTemplateModel;
    protected $bomTemplateItemModel;
    protected $productModel;
    protected $stockMovementModel;
    protected $auditLogModel;
    protected $productionCategoryModel;

    protected $customerModel;

    public function __construct()
    {
        $this->productionJobModel = new ProductionJobModel();
        $this->productionJobItemModel = new ProductionJobItemModel();
        $this->productionMaterialModel = new ProductionMaterialModel();
        $this->bomTemplateModel = new BomTemplateModel();
        $this->bomTemplateItemModel = new BomTemplateItemModel();
        $this->productModel = new ProductModel();
        $this->stockMovementModel = new StockMovementModel();
        $this->auditLogModel = new AuditLogModel();
        $this->productionCategoryModel = new ProductionCategoryModel();
        $this->customerModel = new CustomerModel();

        if (!session()->get('isLoggedIn')) {
            return redirect()->to('/auth/login');
        }
    }

    private function requireAnyPermission(array $permissions, string $message = 'Access denied. You do not have permission for this action.')
    {
        foreach ($permissions as $permission) {
            if (has_permission($permission)) {
                return null;
            }
        }

        if ($this->request->isAJAX()) {
            return $this->response->setStatusCode(403)->setJSON([
                'status' => 'error',
                'message' => $message,
            ]);
        }

        return redirect()->to('/dashboard')->with('error', $message);
    }

    /**
     * Production Jobs List
     */
    public function jobs()
    {
        // Get filter parameters
        $status = $this->request->getGet('status');
        $customerId = $this->request->getGet('customer_id');
        $categoryId = $this->request->getGet('category_id');
        $dateFrom = $this->request->getGet('date_from');
        $dateTo = $this->request->getGet('date_to');
        $search = $this->request->getGet('search');

        // Build query with filters
        $builder = $this->productionJobModel->builder();

        if ($status && $status !== 'all') {
            $builder->where('status', $status);
        }

        if ($customerId) {
            $builder->where('customer_id', $customerId);
        }

        if ($categoryId) {
            $builder->where('production_category_id', $categoryId);
        }

        if ($dateFrom) {
            $builder->where('production_date >=', $dateFrom);
        }

        if ($dateTo) {
            $builder->where('production_date <=', $dateTo);
        }

        if ($search) {
            $builder->groupStart()
                ->like('job_reference', $search)
                ->orLike('job_name', $search)
                ->orLike('notes', $search)
                ->groupEnd();
        }

        $data = [
            'title' => 'Production Jobs',
            'jobs' => $this->productionJobModel->orderBy('created_at', 'DESC')->paginate(20),
            'pager' => $this->productionJobModel->pager,
            'activePage' => 'production',
            'activeSubPage' => 'jobs',
            // Filter data for the view
            'filters' => [
                'status' => $status,
                'customer_id' => $customerId,
                'category_id' => $categoryId,
                'date_from' => $dateFrom,
                'date_to' => $dateTo,
                'search' => $search
            ],
            'customers' => $this->customerModel->findAll(),
            'categories' => $this->productionCategoryModel->findAll()
        ];

        // Add customer names, category names, and last editor
        foreach ($data['jobs'] as &$job) {
            if ($job['customer_id']) {
                $customer = $this->customerModel->find($job['customer_id']);
                $job['customer_name'] = $customer ? $customer['customer_name'] : '—';
            }
            if ($job['production_category_id']) {
                $category = $this->productionCategoryModel->find($job['production_category_id']);
                $job['category_name'] = $category ? $category['category_name'] : '—';
            }
            if ($job['updated_by']) {
                $db = \Config\Database::connect();
                $user = $db->table('users')->select('full_name')->where('id', $job['updated_by'])->get()->getFirstRow();
                $job['updater_name'] = $user ? $user->full_name : 'Unknown';
            }
        }

        return view('production/jobs', $data);
    }

    /**
     * Create Production Job Form
     */
    public function create()
    {
        $data = [
            'title' => 'New Production Job',
            'products' => $this->productModel->findAll(),
            'customers' => $this->customerModel->findAll(),
            'categories' => $this->productionCategoryModel->findAll(),
            'bomTemplates' => $this->bomTemplateModel->findAll(),
            'job_reference' => $this->productionJobModel->generateJobReference(),
            'activePage' => 'production',
            'activeSubPage' => 'create'
        ];

        return view('production/create', $data);
    }

    public function store()
    {
        if (!$this->request->isAJAX()) {
            return redirect()->to('/production/create');
        }

        $json = $this->request->getJSON(true);

        if (empty($json['materials']) || !is_array($json['materials'])) {
            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'No materials provided.'
            ]);
        }

        $db = \Config\Database::connect();
        $db->transStart();

        try {
            $jobData = [
                'job_reference' => $this->productionJobModel->generateJobReference(),
                'job_name' => $json['job_name'],
                'customer_id' => $json['customer_id'] ?? null,
                'production_date' => $json['production_date'],
                'production_category_id' => $json['production_category_id'] ?? null,
                'quantity_produced' => $json['quantity_produced'] ?? 0,
                'currency' => $json['currency'] ?? 'LRD',
                'total_material_cost' => 0,
                'notes' => $json['notes'] ?? '',
                'status' => $json['status'] === 'Completed' ? 'Completed' : 'Draft',
                'payment_status' => $json['payment_status'] ?? 'Unpaid',
                'amount_paid' => $json['amount_paid'] ?? 0,
                'created_by' => session()->get('user_id') ?? 1
            ];

            $jobId = $this->productionJobModel->insert($jobData);
            $totalCost = 0;

            foreach ($json['materials'] as $mat) {
                $lineTotal = $mat['quantity'] * $mat['unit_cost'];
                $totalCost += $lineTotal;

                $this->productionMaterialModel->insert([
                    'production_job_id' => $jobId,
                    'product_id' => $mat['product_id'],
                    'quantity_used' => $mat['quantity'],  // Fixed: use quantity_used instead of quantity
                    'unit_cost' => $mat['unit_cost'],
                    'total_cost' => $lineTotal,
                ]);
            }

            $this->productionJobModel->update($jobId, ['total_material_cost' => $totalCost]);

            // If status is Completed, deduct inventory immediately
            if ($json['status'] === 'Completed') {
                $userId = session()->get('user_id') ?? 1;
                foreach ($json['materials'] as $mat) {
                    $product = $this->productModel->find($mat['product_id']);
                    if (!$product) {
                        $db->transRollback();
                        return $this->response->setJSON([
                            'status' => 'error',
                            'message' => 'Product ID ' . $mat['product_id'] . ' not found.'
                        ]);
                    }
                    if ($product['quantity'] < $mat['quantity']) {
                        $db->transRollback();
                        return $this->response->setJSON([
                            'status' => 'error',
                            'message' => 'Insufficient stock for: ' . $product['product_name'] .
                                '. Required: ' . $mat['quantity'] . ', Available: ' . $product['quantity']
                        ]);
                    }
                    $deducted = $this->productModel->updateStock(
                        $mat['product_id'],
                        $mat['quantity'],
                        'subtract',
                        $userId
                    );
                    if (!$deducted) {
                        $db->transRollback();
                        return $this->response->setJSON([
                            'status' => 'error',
                            'message' => 'Failed to deduct stock for product ID: ' . $mat['product_id']
                        ]);
                    }
                }
            }

            $db->transComplete();

            // Audit log
            $this->auditLogModel->log(
                session()->get('user_id') ?? 1,
                'production_create',
                'ProductionJob',
                $jobId,
                null,
                ['job_reference' => $jobData['job_reference'], 'total_cost' => $totalCost]
            );

            return $this->response->setJSON([
                'status' => 'success',
                'message' => 'Production job created successfully',
                'job_id' => $jobId
            ]);

        } catch (\Exception $e) {
            $db->transRollback();
            log_message('error', '[Production Store] ' . $e->getMessage());
            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'Database error: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * View Production Job Details
     */
    public function view($id)
    {
        $job = $this->productionJobModel->getJobWithDetails($id);

        if (!$job) {
            return redirect()->to('/production/jobs')
                ->with('error', 'Production job not found.');
        }

        $data = [
            'title' => 'Production Job #' . ($job['job_reference'] ?? $id),
            'job' => $job,
            'activePage' => 'production'
        ];

        return view('production/view', $data);
    }

    /**
     * BOM Templates List
     */
    public function bom()
    {
        if ($response = $this->requireAnyPermission(['manage_bom'])) {
            return $response;
        }

        $data = [
            'title' => 'Bill of Materials Templates',
            'templates' => $this->bomTemplateModel->findAll(),
            'products' => $this->productModel->findAll(),
            'activePage' => 'production',
            'activeSubPage' => 'bom'
        ];

        return view('production/bom', $data);
    }

    /**
     * Create BOM Template Form
     */
    public function createBom()
    {
        if ($response = $this->requireAnyPermission(['manage_bom'])) {
            return $response;
        }

        $data = [
            'title' => 'Create BOM Template',
            'products' => $this->productModel->findAll(),
            'activePage' => 'production'
        ];

        return view('production/create_bom', $data);
    }

    /**
     * Store BOM Template
     */
    public function storeBom()
    {
        if ($response = $this->requireAnyPermission(['manage_bom'])) {
            return $response;
        }

        if (!$this->request->isAJAX()) {
            return redirect()->to('/production/bom');
        }

        $json = $this->request->getJSON(true);

        // Validate template name
        if (empty($json['template_name'])) {
            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'Template name is required.'
            ]);
        }

        // Validate items
        if (empty($json['items']) || !is_array($json['items'])) {
            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'Please add at least one material to the template.'
            ]);
        }

        $db = \Config\Database::connect();
        $db->transStart();

        try {
            // Insert the template
            $templateId = $this->bomTemplateModel->insert([
                'template_name' => $json['template_name'],
                'description' => $json['description'] ?? '',
                'finished_product_id' => $json['finished_product_id'] ?: null,
                'created_by' => session()->get('user_id') ?? 1
            ]);

            if (!$templateId) {
                $db->transRollback();
                return $this->response->setJSON([
                    'status' => 'error',
                    'message' => 'Failed to save template: ' . implode(', ', $this->bomTemplateModel->errors())
                ]);
            }

            // Insert items
            $inserted = $this->bomTemplateItemModel->addItems($templateId, $json['items']);

            if (!$inserted) {
                $db->transRollback();
                return $this->response->setJSON([
                    'status' => 'error',
                    'message' => 'Template saved but failed to insert materials.'
                ]);
            }

            $db->transComplete();

            // Audit log
            $this->auditLogModel->log(
                session()->get('user_id') ?? 1,
                'bom_create',
                'BomTemplate',
                $templateId,
                null,
                ['template_name' => $json['template_name'], 'item_count' => count($json['items'])]
            );

            return $this->response->setJSON([
                'status' => 'success',
                'message' => 'BOM template created successfully.',
                'template_id' => $templateId
            ]);

        } catch (\Exception $e) {
            $db->transRollback();
            log_message('error', '[BOM StoreBom] ' . $e->getMessage());
            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'Database error: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Edit BOM Template
     */
    public function editBom($id)
    {
        if ($response = $this->requireAnyPermission(['manage_bom'])) {
            return $response;
        }

        $template = $this->bomTemplateModel->find($id);

        if (!$template) {
            return redirect()->to('/production/bom')->with('error', 'Template not found');
        }

        $data = [
            'title' => 'Edit BOM Template',
            'template' => $template,
            'products' => $this->productModel->findAll(),
            'activePage' => 'production'
        ];

        return view('production/edit_bom', $data);
    }

    /**
     * Edit Production Job Form
     */
    public function edit($id)
    {
        if ($response = $this->requireAnyPermission(['manage_production'])) {
            return $response;
        }

        $job = $this->productionJobModel->getJobWithDetails($id);

        if (!$job) {
            return redirect()->to('/production/jobs')
                ->with('error', 'Production job not found.');
        }

        // Block editing of completed or cancelled jobs
        if (in_array($job['status'], ['Completed', 'Cancelled'])) {
            return redirect()->to('/production/view/' . $id)
                ->with('error', 'Completed or cancelled jobs cannot be edited.');
        }

        $data = [
            'title' => 'Edit Production Job #' . ($job['job_reference'] ?? $id),
            'job' => $job,
            'products' => $this->productModel->findAll(),
            'customers' => $this->customerModel->findAll(),
            'categories' => $this->productionCategoryModel->findAll(),
            'bomTemplates' => $this->bomTemplateModel->findAll(),
            'activePage' => 'production'
        ];

        return view('production/edit', $data);
    }

    /**
     * Update Production Job
     */
    public function update($id)
    {
        if ($response = $this->requireAnyPermission(['manage_production'])) {
            return $response;
        }

        if (!$this->request->isAJAX()) {
            return redirect()->to('/production/jobs');
        }

        $json = $this->request->getJSON(true);

        if (empty($json['materials']) || !is_array($json['materials'])) {
            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'No materials provided.'
            ]);
        }

        // Verify job exists
        $job = $this->productionJobModel->find($id);

        if (!$job) {
            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'Production job not found.'
            ]);
        }

        // Block update if job is completed or cancelled
        if (in_array($job['status'], ['Completed', 'Cancelled'])) {
            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'Completed or cancelled jobs cannot be edited.'
            ]);
        }

        $db = \Config\Database::connect();
        $db->transStart();

        try {
            // Update job record
            $jobData = [
                'job_name' => $json['job_name'],
                'customer_id' => $json['customer_id'] ?? null,
                'production_date' => $json['production_date'],
                'production_category_id' => $json['production_category_id'] ?? null,
                'quantity_produced' => $json['quantity_produced'] ?? 0,
                'currency' => $json['currency'] ?? 'LRD',
                'notes' => $json['notes'] ?? '',
                'updated_by' => session()->get('user_id') ?? 1,
            ];

            $this->productionJobModel->update($id, $jobData);

            // Delete old materials and re-insert fresh
            $this->productionMaterialModel->where('production_job_id', $id)->delete();

            $totalCost = 0;

            foreach ($json['materials'] as $mat) {
                $lineTotal = $mat['quantity'] * $mat['unit_cost'];
                $totalCost += $lineTotal;

                $this->productionMaterialModel->insert([
                    'production_job_id' => $id,
                    'product_id' => $mat['product_id'],
                    'quantity_used' => $mat['quantity'],  // Fixed: use quantity_used
                    'unit_cost' => $mat['unit_cost'],
                    'total_cost' => $lineTotal
                ]);
            }

            // Recalculate and update total cost
            $this->productionJobModel->update($id, ['total_material_cost' => $totalCost]);

            $db->transComplete();

            // Audit log
            $this->auditLogModel->log(
                session()->get('user_id') ?? 1,
                'production_update',
                'ProductionJob',
                $id,
                null,
                ['job_reference' => $job['job_reference'], 'total_cost' => $totalCost]
            );

            return $this->response->setJSON([
                'status' => 'success',
                'message' => 'Production job updated successfully',
                'job_id' => $id
            ]);

        } catch (\Exception $e) {
            $db->transRollback();
            log_message('error', '[Production Update] ' . $e->getMessage());
            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'Database error: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Complete Production Job
     */
    public function complete($id)
    {
        if ($response = $this->requireAnyPermission(['manage_production'])) {
            return $response;
        }

        if (!$this->request->isAJAX()) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Invalid request']);
        }

        $job = $this->productionJobModel->find($id);

        if (!$job) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Production job not found.']);
        }

        if ($job['status'] !== 'Draft') {
            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'Only Draft jobs can be completed. This job is: ' . $job['status']
            ]);
        }

        $userId = session()->get('user_id') ?? 1;
        $db = \Config\Database::connect();
        $db->transStart();

        try {
            // Get all materials for this job
            $materials = $this->productionMaterialModel
                ->where('production_job_id', $id)
                ->findAll();

            if (empty($materials)) {
                return $this->response->setJSON([
                    'status' => 'error',
                    'message' => 'No materials found for this job.'
                ]);
            }

            // Check stock is sufficient for all materials before deducting anything
            foreach ($materials as $material) {
                $product = $this->productModel->find($material['product_id']);
                if (!$product) {
                    return $this->response->setJSON([
                        'status' => 'error',
                        'message' => 'Product ID ' . $material['product_id'] . ' not found.'
                    ]);
                }
                if ($product['quantity'] < $material['quantity_used']) {
                    return $this->response->setJSON([
                        'status' => 'error',
                        'message' => 'Insufficient stock for: ' . $product['product_name'] .
                            '. Required: ' . $material['quantity_used'] .
                            ', Available: ' . $product['quantity']
                    ]);
                }
            }

            // Deduct each material from inventory
            foreach ($materials as $material) {
                $deducted = $this->productModel->updateStock(
                    $material['product_id'],
                    $material['quantity_used'],
                    'subtract',
                    $userId
                );

                if (!$deducted) {
                    $db->transRollback();
                    return $this->response->setJSON([
                        'status' => 'error',
                        'message' => 'Failed to deduct stock for product ID: ' . $material['product_id']
                    ]);
                }
            }

            // Mark job as Completed
            $this->productionJobModel->update($id, [
                'status' => 'Completed',
                'updated_by' => $userId
            ]);

            $db->transComplete();

            // Audit log
            $this->auditLogModel->log(
                $userId,
                'production_complete',
                'ProductionJob',
                $id,
                null,
                ['job_reference' => $job['job_reference'], 'status' => 'Completed']
            );

            return $this->response->setJSON([
                'status' => 'success',
                'message' => 'Production job completed. Inventory has been updated.'
            ]);

        } catch (\Exception $e) {
            $db->transRollback();
            log_message('error', '[Production Complete] ' . $e->getMessage());
            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'Database error: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Cancel Production Job
     */
    public function cancel($id)
    {
        if ($response = $this->requireAnyPermission(['manage_production'])) {
            return $response;
        }

        if (!$this->request->isAJAX()) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Invalid request']);
        }

        $job = $this->productionJobModel->find($id);

        if (!$job) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Production job not found.']);
        }

        if ($job['status'] !== 'Draft') {
            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'Only Draft jobs can be cancelled. This job is: ' . $job['status']
            ]);
        }

        $userId = session()->get('user_id') ?? 1;

        $this->productionJobModel->update($id, [
            'status' => 'Cancelled',
            'updated_by' => $userId
        ]);

        // Audit log
        $this->auditLogModel->log(
            $userId,
            'production_cancel',
            'ProductionJob',
            $id,
            null,
            ['job_reference' => $job['job_reference'], 'status' => 'Cancelled']
        );

        return $this->response->setJSON([
            'status' => 'success',
            'message' => 'Production job has been cancelled.'
        ]);
    }

    /**
     * Delete Production Job
     */
    public function delete($id)
    {
        if ($response = $this->requireAnyPermission(['delete_records'])) {
            return $response;
        }

        if (!$this->request->isAJAX()) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Invalid request']);
        }

        $job = $this->productionJobModel->find($id);

        if (!$job) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Production job not found.']);
        }

        if ($job['status'] === 'Completed') {
            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'Completed jobs cannot be deleted. Cancel the job first if needed.'
            ]);
        }

        $userId = session()->get('user_id') ?? 1;
        $db = \Config\Database::connect();
        $db->transStart();

        try {
            // Hard delete materials
            $this->productionMaterialModel
                ->where('production_job_id', $id)
                ->delete();

            // Soft delete the job
            $this->productionJobModel->delete($id);

            $db->transComplete();

            // Audit log
            $this->auditLogModel->log(
                $userId,
                'production_delete',
                'ProductionJob',
                $id,
                ['job_reference' => $job['job_reference'], 'status' => $job['status']],
                null
            );

            return $this->response->setJSON([
                'status' => 'success',
                'message' => 'Production job deleted successfully.'
            ]);

        } catch (\Exception $e) {
            $db->transRollback();
            log_message('error', '[Production Delete] ' . $e->getMessage());
            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'Database error: ' . $e->getMessage()
            ]);
        }
    }

    /*
     * Update BOM Template
     */
    public function updateBom($id)
    {
        if ($response = $this->requireAnyPermission(['manage_bom'])) {
            return $response;
        }

        if (!$this->request->isAJAX()) {
            return redirect()->to('/production/bom');
        }

        $json = $this->request->getJSON(true);

        if (empty($json['template_name'])) {
            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'Template name is required.'
            ]);
        }

        if (empty($json['items']) || !is_array($json['items'])) {
            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'Please add at least one material.'
            ]);
        }

        $template = $this->bomTemplateModel->find($id);

        if (!$template) {
            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'Template not found.'
            ]);
        }

        $db = \Config\Database::connect();
        $db->transStart();

        try {
            // Update template
            $this->bomTemplateModel->update($id, [
                'template_name' => $json['template_name'],
                'description' => $json['description'] ?? '',
                'finished_product_id' => $json['finished_product_id'] ?: null,
            ]);

            // Replace all items
            $this->bomTemplateItemModel->updateItems($id, $json['items']);

            $db->transComplete();

            $this->auditLogModel->log(
                session()->get('user_id') ?? 1,
                'bom_update',
                'BomTemplate',
                $id,
                null,
                ['template_name' => $json['template_name'], 'item_count' => count($json['items'])]
            );

            return $this->response->setJSON([
                'status' => 'success',
                'message' => 'BOM template updated successfully.',
            ]);

        } catch (\Exception $e) {
            $db->transRollback();
            log_message('error', '[BOM UpdateBom] ' . $e->getMessage());
            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'Database error: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Delete BOM Template
     */
    public function deleteBom($id)
    {
        if ($response = $this->requireAnyPermission(['delete_records'])) {
            return $response;
        }

        if (!$this->request->isAJAX()) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Invalid request']);
        }

        $template = $this->bomTemplateModel->find($id);

        if (!$template) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Template not found.']);
        }

        $db = \Config\Database::connect();
        $db->transStart();

        try {
            // Hard delete items first
            $this->bomTemplateItemModel->where('bom_template_id', $id)->delete();

            // Hard delete the template
            $this->bomTemplateModel->delete($id);

            $db->transComplete();

            $this->auditLogModel->log(
                session()->get('user_id') ?? 1,
                'bom_delete',
                'BomTemplate',
                $id,
                ['template_name' => $template['template_name']],
                null
            );

            return $this->response->setJSON([
                'status' => 'success',
                'message' => 'Template deleted successfully.'
            ]);

        } catch (\Exception $e) {
            $db->transRollback();
            log_message('error', '[BOM DeleteBom] ' . $e->getMessage());
            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'Database error: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Get Materials for BOM
     */
    public function getMaterials($templateId)
    {
        if (!$this->request->isAJAX()) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Invalid request']);
        }

        $items = $this->bomTemplateItemModel
            ->select('bom_template_items.*, products.product_name')
            ->join('products', 'products.id = bom_template_items.product_id')
            ->where('bom_template_items.bom_template_id', $templateId)
            ->findAll();

        // Add unit_cost from product's purchase_price for the frontend
        foreach ($items as &$item) {
            $product = $this->productModel->find($item['product_id']);
            $item['unit_cost'] = $product ? $product['purchase_price'] : 0;
        }

        return $this->response->setJSON(['status' => 'success', 'data' => $items]);
    }

    /**
     * Production Categories List
     */
    public function categories()
    {
        if ($response = $this->requireAnyPermission(['manage_production'])) {
            return $response;
        }

        $data = [
            'title' => 'Production Categories',
            'categories' => $this->productionCategoryModel->getAllWithJobCounts(),
            'activePage' => 'production',
            'activeSubPage' => 'categories'
        ];

        return view('production/categories', $data);
    }

    /**
     * Store Production Category
     */
    public function storeCategory()
    {
        if ($response = $this->requireAnyPermission(['manage_production'])) {
            return $response;
        }

        if (!$this->request->isAJAX()) {
            return redirect()->to('/production/categories');
        }

        $rules = [
            'category_name' => 'required|min_length[2]|max_length[100]|is_unique[production_categories.category_name]'
        ];

        if (!$this->validate($rules)) {
            return $this->response->setJSON([
                'status' => 'error',
                'errors' => $this->validator->getErrors()
            ]);
        }

        $data = [
            'category_name' => $this->request->getPost('category_name'),
            'description' => $this->request->getPost('description') ?? ''
        ];

        $categoryId = $this->productionCategoryModel->insert($data);

        if ($categoryId) {
            $this->auditLogModel->log(
                session()->get('user_id') ?? 1,
                'production_category_create',
                'ProductionCategory',
                $categoryId,
                null,
                ['category_name' => $data['category_name']]
            );

            return $this->response->setJSON([
                'status' => 'success',
                'message' => 'Category created successfully',
                'category_id' => $categoryId
            ]);
        } else {
            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'Failed to create category'
            ]);
        }
    }

    /**
     * Update Production Category
     */
    public function updateCategory($id)
    {
        if ($response = $this->requireAnyPermission(['manage_production'])) {
            return $response;
        }

        if (!$this->request->isAJAX()) {
            return redirect()->to('/production/categories');
        }

        $category = $this->productionCategoryModel->find($id);
        if (!$category) {
            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'Category not found'
            ]);
        }

        $rules = [
            'category_name' => 'required|min_length[2]|max_length[100]|is_unique[production_categories.category_name,id,' . $id . ']'
        ];

        if (!$this->validate($rules)) {
            return $this->response->setJSON([
                'status' => 'error',
                'errors' => $this->validator->getErrors()
            ]);
        }

        $data = [
            'category_name' => $this->request->getPost('category_name'),
            'description' => $this->request->getPost('description') ?? ''
        ];

        if ($this->productionCategoryModel->update($id, $data)) {
            $this->auditLogModel->log(
                session()->get('user_id') ?? 1,
                'production_category_update',
                'ProductionCategory',
                $id,
                ['category_name' => $category['category_name']],
                ['category_name' => $data['category_name']]
            );

            return $this->response->setJSON([
                'status' => 'success',
                'message' => 'Category updated successfully'
            ]);
        } else {
            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'Failed to update category'
            ]);
        }
    }

    /**
     * Delete Production Category
     */
    public function deleteCategory($id)
    {
        if ($response = $this->requireAnyPermission(['manage_production'])) {
            return $response;
        }

        if (!$this->request->isAJAX()) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Invalid request']);
        }

        $category = $this->productionCategoryModel->find($id);
        if (!$category) {
            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'Category not found'
            ]);
        }

        // Check if category has jobs
        $jobCount = $this->productionJobModel->where('production_category_id', $id)->countAllResults();
        if ($jobCount > 0) {
            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'Cannot delete category with existing jobs. Please reassign or delete the jobs first.'
            ]);
        }

        if ($this->productionCategoryModel->delete($id)) {
            $this->auditLogModel->log(
                session()->get('user_id') ?? 1,
                'production_category_delete',
                'ProductionCategory',
                $id,
                ['category_name' => $category['category_name']],
                null
            );

            return $this->response->setJSON([
                'status' => 'success',
                'message' => 'Category deleted successfully'
            ]);
        } else {
            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'Failed to delete category'
            ]);
        }
    }

    /**
     * Get Job Items via AJAX
     */
    public function getJobItems($jobId)
    {
        if (!$this->request->isAJAX()) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Invalid request']);
        }

        $job = $this->productionJobModel->find($jobId);
        if (!$job) {
            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'Job not found'
            ]);
        }

        // Get job items with product details
        $items = $this->productionJobItemModel->select('production_job_items.*, products.product_name, products.product_code, products.unit')
            ->join('products', 'products.id = production_job_items.item_id', 'left')
            ->where('production_job_items.production_job_id', $jobId)
            ->findAll();

        return $this->response->setJSON([
            'status' => 'success',
            'items' => $items
        ]);
    }
}