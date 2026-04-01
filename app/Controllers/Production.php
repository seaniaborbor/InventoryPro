<?php

namespace App\Controllers;

use App\Models\ProductionJobModel;
use App\Models\ProductionMaterialModel;
use App\Models\BomTemplateModel;
use App\Models\BomTemplateItemModel;
use App\Models\ProductModel;
use App\Models\StockMovementModel;
use App\Models\AuditLogModel;

class Production extends BaseController
{
    protected $productionJobModel;
    protected $productionMaterialModel;
    protected $bomTemplateModel;
    protected $bomTemplateItemModel;
    protected $productModel;
    protected $stockMovementModel;
    protected $auditLogModel;

    public function __construct()
    {
        $this->productionJobModel = new ProductionJobModel();
        $this->productionMaterialModel = new ProductionMaterialModel();
        $this->bomTemplateModel = new BomTemplateModel(); // Now exists
        $this->bomTemplateItemModel = new BomTemplateItemModel(); // Now exists
        $this->productModel = new ProductModel();
        $this->stockMovementModel = new StockMovementModel();
        $this->auditLogModel = new AuditLogModel();

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
        $data = [
            'title' => 'Production Jobs',
            'jobs' => $this->productionJobModel->orderBy('created_at', 'DESC')->paginate(20),
            'pager' => $this->productionJobModel->pager,
            'activePage' => 'production'
        ];

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
            'bomTemplates' => $this->bomTemplateModel->findAll(),
            'job_reference' => $this->productionJobModel->generateJobReference(),
            'activePage' => 'production'
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
                'production_date' => $json['production_date'],
                'finished_product_id' => $json['finished_product_id'] ?: null,
                'quantity_produced' => $json['quantity_produced'] ?? 0,
                'currency' => $json['currency'] ?? 'LRD',
                'total_material_cost' => 0,
                'notes' => $json['notes'] ?? '',
                'status' => 'Draft',
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
                    'quantity' => $mat['quantity'],
                    'unit_cost' => $mat['unit_cost'],
                    'total_cost' => $lineTotal
                ]);
            }

            $this->productionJobModel->update($jobId, ['total_material_cost' => $totalCost]);

            $db->transComplete();

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
     * View Production Job
     */
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

        // print_r($data); // Debugging line to check data structure
        // exit();

        return view('production/view', $data);
    }

    /**
     * Get single job with finished product name and materials
     */
    public function getJobWithDetails($id)
    {
        // Get main job data with finished product name
        $job = $this->select('production_jobs.*, products.product_name as finished_product_name')
            ->join('products', 'products.id = production_jobs.finished_product_id', 'left')
            ->where('production_jobs.id', $id)
            ->first();

        if (!$job) {
            return null;
        }

        // Get materials for this job
        $materials = $this->db->table('production_materials')
            ->select('production_materials.*, products.product_name')
            ->join('products', 'products.id = production_materials.product_id')
            ->where('production_materials.production_job_id', $id)
            ->get()
            ->getResultArray();

        $job['materials'] = $materials;

        return $job;
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
            'activePage' => 'production'
        ];

        // print_r($data); // Debugging line to check data structure
        // exit();

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
            // Insert the template — columns: template_name, description, finished_product_id, created_by
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

            // Insert items using addItems() — columns: bom_template_id, product_id, quantity
            // Note: BomTemplateItemModel has no unit_cost column — cost is pulled live from products.purchase_price
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
            // Update job record — same columns as store()
            $jobData = [
                'job_name' => $json['job_name'],
                'production_date' => $json['production_date'],
                'finished_product_id' => $json['finished_product_id'] ?: null,
                'quantity_produced' => $json['quantity_produced'] ?? 0,
                'currency' => $json['currency'] ?? 'LRD',
                'notes' => $json['notes'] ?? '',
            ];

            $this->productionJobModel->update($id, $jobData);

            // Delete old materials and re-insert fresh — clean approach
            $this->productionMaterialModel->where('production_job_id', $id)->delete();

            $totalCost = 0;

            foreach ($json['materials'] as $mat) {
                $lineTotal = $mat['quantity'] * $mat['unit_cost'];
                $totalCost += $lineTotal;

                $this->productionMaterialModel->insert([
                    'production_job_id' => $id,
                    'product_id' => $mat['product_id'],
                    'quantity' => $mat['quantity'],
                    'unit_cost' => $mat['unit_cost'],
                    'total_cost' => $lineTotal
                ]);
            }

            // Recalculate and update total cost
            $this->productionJobModel->update($id, ['total_material_cost' => $totalCost]);

            $db->transComplete();

            // Audit log — mirrors the pattern from store()
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
     * - Deducts materials from inventory (quantity_used)
     * - Adds finished product to inventory if applicable
     * - Logs stock movements for each material and finished product
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

            // Add finished product to inventory if applicable
            if ($job['finished_product_id'] && $job['quantity_produced'] > 0) {
                $added = $this->productModel->updateStock(
                    $job['finished_product_id'],
                    $job['quantity_produced'],
                    'add',
                    $userId
                );

                if (!$added) {
                    $db->transRollback();
                    return $this->response->setJSON([
                        'status' => 'error',
                        'message' => 'Failed to add finished product to inventory.'
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
     * - Only Draft jobs can be cancelled
     * - Does NOT touch inventory (nothing was deducted yet)
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
     * - Only Draft or Cancelled jobs can be deleted
     * - Deletes materials records too (hard delete since ProductionMaterialModel has no soft delete)
     * - Soft deletes the job itself (model has useSoftDeletes = true)
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
            // Hard delete materials (no soft delete on ProductionMaterialModel)
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
            // Update template — allowed fields: template_name, description, finished_product_id
            $this->bomTemplateModel->update($id, [
                'template_name' => $json['template_name'],
                'description' => $json['description'] ?? '',
                'finished_product_id' => $json['finished_product_id'] ?: null,
            ]);

            // Replace all items — updateItems() deletes old then insertBatch new
            // BomTemplateItemModel allowed fields: bom_template_id, product_id, quantity
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
            // Hard delete items first (BomTemplateItemModel has no soft delete)
            $this->bomTemplateItemModel->where('bom_template_id', $id)->delete();

            // Hard delete the template (BomTemplateModel also has no soft delete)
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

        $items = $this->bomTemplateItemModel->where('bom_template_id', $templateId)
            ->select('bom_template_items.*, products.product_name')
            ->join('products', 'products.id = bom_template_items.product_id')
            ->findAll();

        return $this->response->setJSON(['status' => 'success', 'data' => $items]);
    }
}
