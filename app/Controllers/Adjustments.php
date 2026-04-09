<?php

namespace App\Controllers;

use App\Models\AdjustmentEventModel;
use App\Models\ProductModel;
use App\Models\CustomerModel;
use App\Models\SaleModel;
use App\Models\ProductionJobModel;
use App\Models\SaleItemModel;
use App\Models\AuditLogModel;

class Adjustments extends BaseController
{
    protected $adjustmentModel;
    protected $productModel;
    protected $customerModel;
    protected $saleModel;
    protected $productionJobModel;
    protected $saleItemModel;
    protected $auditLogModel;

    public function __construct()
    {
        $this->adjustmentModel = new AdjustmentEventModel();
        $this->productModel = new ProductModel();
        $this->customerModel = new CustomerModel();
        $this->saleModel = new SaleModel();
        $this->productionJobModel = new ProductionJobModel();
        $this->saleItemModel = new SaleItemModel();
        $this->auditLogModel = new AuditLogModel();

        if (!session()->get('isLoggedIn')) {
            return redirect()->to('/auth/login');
        }
    }

    /**
     * List all adjustment events from sales or production jobs
     */
    public function index()
    {
        $db = \Config\Database::connect();
        $filters = [
            'product_id'  => $this->request->getGet('product_id'),
            'event_type'  => $this->request->getGet('event_type'),
            'source_type' => $this->request->getGet('source_type'),
            'start_date'  => $this->request->getGet('start_date') ?: date('Y-m-01'),
            'end_date'    => $this->request->getGet('end_date') ?: date('Y-m-t'),
        ];

        $events = $this->adjustmentModel->getEventsWithDetails($filters);
        $paginator = $this->adjustmentModel->pager;

        // Resolve source info (sale/job reference)
        foreach ($events as &$event) {
            $event['source_url'] = '';
            $event['source_label'] = '';
            if ($event['related_sale_id']) {
                $sale = $this->saleModel->select('invoice_number')->where('id', $event['related_sale_id'])->first();
                $event['source_url'] = $sale ? base_url('sales/view/' . $event['related_sale_id']) : '';
                $event['source_label'] = $sale ? ('Sale: ' . $sale['invoice_number']) : 'Sale #' . $event['related_sale_id'];
            }
            if ($event['related_production_job_id']) {
                $job = $this->productionJobModel->select('job_reference')->where('id', $event['related_production_job_id'])->first();
                $event['source_url'] = $job ? base_url('production/view/' . $event['related_production_job_id']) : '';
                $event['source_label'] = $job ? ('Job: ' . $job['job_reference']) : 'Job #' . $event['related_production_job_id'];
            }
        }
        unset($event);

        $summary = $this->adjustmentModel->getSummary($filters['start_date'], $filters['end_date']);

        $data = [
            'title'       => 'Adjustments',
            'events'      => $events,
            'pager'       => $paginator,
            'products'    => $this->productModel->findAll(),
            'summary'     => $summary,
            'filters'     => $filters,
            'activePage'  => 'adjustments',
            'activeSubPage' => 'adjustments',
        ];

        return view('adjustments/index', $data);
    }

    /**
     * Create adjustment form for a sale (refund/return)
     */
    public function createFromSale($saleId)
    {
        $sale = $this->saleModel->getSaleWithItems($saleId);
        if (!$sale) {
            return redirect()->to('/sales')->with('error', 'Sale not found.');
        }

        $data = [
            'title'      => 'Adjustment for Sale ' . $sale['invoice_number'],
            'source'     => 'sale',
            'sale'       => $sale,
            'sourceId'   => $sale['id'],
            'products'   => [],
            'customers'  => [],
            'activePage' => 'adjustments',
        ];

        foreach ($sale['items'] as $item) {
            $product = $this->productModel->find($item['product_id']);
            $data['products'][] = [
                'id'           => $item['product_id'],
                'product_name' => $item['product_name'] ?? ($product['product_name'] ?? 'Product #' . $item['product_id']),
                'purchase_price' => $product['purchase_price'] ?? 0,
            ];
        }

        return view('adjustments/create', $data);
    }

    /**
     * Create adjustment form for a production job (damage during production)
     */
    public function createFromJob($jobId)
    {
        $job = $this->productionJobModel->find($jobId);
        if (!$job) {
            return redirect()->to('/production/jobs')->with('error', 'Production job not found.');
        }

        // Get products that were used as materials in the job
        $materialModel = new \App\Models\ProductionMaterialModel();
        $materials = $materialModel->where('production_job_id', $jobId)->findAll();

        $data = [
            'title'      => 'Adjustment for Job ' . $job['job_reference'],
            'source'     => 'production',
            'sourceId'   => $job['id'],
            'job'        => $job,
            'products'   => [],
            'materials'  => $materials,
            'customers'  => [],
            'activePage' => 'adjustments',
        ];

        foreach ($materials as $mat) {
            $product = $this->productModel->find($mat['product_id']);
            $data['products'][] = [
                'id'              => $mat['product_id'],
                'product_name'    => $product['product_name'] ?? 'Product #' . $mat['product_id'],
                'purchase_price'  => $product['purchase_price'] ?? 0,
            ];
        }

        return view('adjustments/create', $data);
    }

    /**
     * Store adjustment (must have a source context — sale or production job)
     */
    public function store()
    {
        if (!$this->request->isAJAX()) {
            return redirect()->back();
        }

        $json = $this->request->getJSON(true);

        // Validate required fields
        if (empty($json['product_id']) || empty($json['event_type']) || empty($json['quantity'])) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Product, event type, and quantity are required.']);
        }

        // Determine source context
        $source = $json['source'] ?? null;
        $saleId = $json['related_sale_id'] ?? null;
        $jobId = $json['related_production_job_id'] ?? null;

        if ($source === 'sale' && !$saleId) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Adjustment must be linked to a sale.']);
        }
        if ($source === 'production' && !$jobId) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Adjustment must be linked to a production job.']);
        }

        $quantity = abs($json['quantity']);
        $product = $this->productModel->find($json['product_id']);
        if (!$product) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Product not found.']);
        }

        $unitCost = $json['unit_cost'] ?? $product['purchase_price'] ?? 0;
        $totalValue = $quantity * $unitCost;

        $db = \Config\Database::connect();
        $db->transStart();

        try {
            $eventData = [
                'product_id'                  => $json['product_id'],
                'event_type'                  => $json['event_type'],
                'quantity'                    => $quantity,
                'unit_cost'                   => $unitCost,
                'total_value'                 => $totalValue,
                'currency'                    => $json['currency'] ?? 'LRD',
                'reference'                   => $json['reference'] ?? null,
                'description'                 => $json['description'] ?? null,
                'adjust_stock'                => 1,
                'event_date'                  => date('Y-m-d H:i:s'),
                'related_sale_id'             => $saleId,
                'related_production_job_id'   => $jobId,
                'customer_id'                 => $json['customer_id'] ?? ($json['sale_customer_id'] ?? null),
                'created_by'                  => session()->get('user_id') ?? 1,
            ];

            $eventId = $this->adjustmentModel->insert($eventData);

            // Adjust inventory for refunds (subtract), returns (add), damage (subtract)
            $stockAction = null;
            if ($json['event_type'] === 'Damage' || $json['event_type'] === 'Refund' || $json['event_type'] === 'Theft') {
                $stockAction = 'subtract';
            } elseif ($json['event_type'] === 'Return') {
                $stockAction = 'add';
            }

            if ($stockAction !== null) {
                if ($stockAction === 'subtract' && $product['quantity'] < $quantity) {
                    $db->transRollback();
                    return $this->response->setJSON([
                        'status' => 'error',
                        'message' => 'Insufficient stock for: ' . $product['product_name'] . '. Available: ' . number_format($product['quantity'], 2) . ', Required: ' . number_format($quantity, 2),
                    ]);
                }
                $this->productModel->updateStock($json['product_id'], $quantity, $stockAction, session()->get('user_id'));
            }

            $db->transComplete();

            $this->auditLogModel->log(
                session()->get('user_id') ?? 1,
                'adjustment_create',
                'AdjustmentEvent',
                $eventId,
                null,
                $eventData
            );

            return $this->response->setJSON(['status' => 'success', 'message' => 'Adjustment recorded successfully.']);
        } catch (\Exception $e) {
            $db->transRollback();
            log_message('error', '[Adjustment Store] ' . $e->getMessage());
            return $this->response->setJSON(['status' => 'error', 'message' => 'Database error: ' . $e->getMessage()]);
        }
    }

    /**
     * View single adjustment
     */
    public function view($id)
    {
        $event = $this->adjustmentModel->getEventWithDetails($id);
        if (!$event) {
            return redirect()->to('/adjustments')->with('error', 'Adjustment not found.');
        }

        $event['source_url'] = '';
        $event['source_label'] = '';
        if ($event['related_sale_id']) {
            $sale = $this->saleModel->select('invoice_number')->where('id', $event['related_sale_id'])->first();
            $event['source_url'] = $sale ? base_url('sales/view/' . $event['related_sale_id']) : '';
            $event['source_label'] = $sale ? 'Sale: ' . $sale['invoice_number'] : 'Sale #' . $event['related_sale_id'];
        }
        if ($event['related_production_job_id']) {
            $job = $this->productionJobModel->select('job_reference')->where('id', $event['related_production_job_id'])->first();
            $event['source_url'] = $job ? base_url('production/view/' . $event['related_production_job_id']) : '';
            $event['source_label'] = $job ? 'Job: ' . $job['job_reference'] : 'Job #' . $event['related_production_job_id'];
        }

        $data = ['title' => 'Adjustment #' . $id, 'event' => $event, 'activePage' => 'adjustments'];
        return view('adjustments/view', $data);
    }

    /**
     * Edit adjustment
     */
    public function edit($id)
    {
        $event = $this->adjustmentModel->find($id);
        if (!$event) {
            return redirect()->to('/adjustments')->with('error', 'Adjustment not found.');
        }

        $source = 'unknown';
        if ($event['related_sale_id']) $source = 'sale';
        if ($event['related_production_job_id']) $source = 'production';

        $data = [
            'title'      => 'Edit Adjustment #' . $id,
            'source'     => $source,
            'event'      => $event,
            'products'   => $this->productModel->findAll(),
            'customers'  => $this->customerModel->findAll(),
            'activePage' => 'adjustments',
        ];

        return view('adjustments/edit', $data);
    }

    /**
     * Update adjustment
     */
    public function update($id)
    {
        if (!$this->request->isAJAX()) {
            return redirect()->back();
        }

        $json = $this->request->getJSON(true);
        $oldEvent = $this->adjustmentModel->find($id);
        if (!$oldEvent) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Adjustment not found.']);
        }

        $quantity = abs($json['quantity'] ?? $oldEvent['quantity']);
        $unitCost = $json['unit_cost'] ?? $oldEvent['unit_cost'] ?? 0;

        $this->adjustmentModel->update($id, [
            'product_id'  => $json['product_id'] ?? $oldEvent['product_id'],
            'event_type'  => $json['event_type'] ?? $oldEvent['event_type'],
            'quantity'    => $quantity,
            'unit_cost'   => $unitCost,
            'total_value' => $quantity * $unitCost,
            'currency'    => $json['currency'] ?? $oldEvent['currency'],
            'reference'   => $json['reference'] ?? $oldEvent['reference'],
            'description' => $json['description'] ?? $oldEvent['description'],
            'event_date'  => $json['event_date'] ?? $oldEvent['event_date'],
            'customer_id' => $json['customer_id'] ?? $oldEvent['customer_id'],
            'updated_by'  => session()->get('user_id') ?? 1,
        ]);

        $this->auditLogModel->log(
            session()->get('user_id') ?? 1,
            'adjustment_update',
            'AdjustmentEvent',
            $id,
            $oldEvent,
            ['updated' => true]
        );

        return $this->response->setJSON(['status' => 'success', 'message' => 'Adjustment updated.']);
    }

    /**
     * Delete adjustment
     */
    public function delete($id)
    {
        if (!$this->request->isAJAX()) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Invalid request.']);
        }

        $event = $this->adjustmentModel->find($id);
        if (!$event) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Adjustment not found.']);
        }

        $this->adjustmentModel->delete($id);

        $this->auditLogModel->log(
            session()->get('user_id') ?? 1,
            'adjustment_delete',
            'AdjustmentEvent',
            $id,
            $event,
            null
        );

        return $this->response->setJSON(['status' => 'success', 'message' => 'Adjustment deleted.']);
    }

    /**
     * API: Get adjustments for a sale (used in sale view)
     */
    public function apiGetBySale($saleId)
    {
        $events = $this->adjustmentModel->getBySaleId($saleId);
        return $this->response->setJSON(['status' => 'success', 'data' => $events]);
    }

    /**
     * API: Get adjustments for a production job (used in job view)
     */
    public function apiGetByJob($jobId)
    {
        $events = $this->adjustmentModel->getByJobId($jobId);
        return $this->response->setJSON(['status' => 'success', 'data' => $events]);
    }
}
