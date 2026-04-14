<?php

namespace App\Controllers;

use App\Models\SaleModel;
use App\Models\SaleItemModel;
use App\Models\CustomerModel;
use App\Models\UserModel;
use App\Models\ProductionJobModel;
use App\Models\ProductionMaterialModel;
use App\Models\AuditLogModel;

class Leaderboard extends BaseController
{
    protected $saleModel;
    protected $saleItemModel;
    protected $customerModel;
    protected $userModel;
    protected $productionJobModel;
    protected $productionMaterialModel;
    protected $auditLogModel;

    public function __construct()
    {
        $this->saleModel = new SaleModel();
        $this->saleItemModel = new SaleItemModel();
        $this->customerModel = new CustomerModel();
        $this->userModel = new UserModel();
        $this->productionJobModel = new ProductionJobModel();
        $this->productionMaterialModel = new ProductionMaterialModel();
        $this->auditLogModel = new AuditLogModel();

        if (!session()->get('isLoggedIn')) {
            return redirect()->to('/auth/login');
        }
    }

    public function index()
    {
        $period = $this->request->getGet('period') ?: 'last_30_days';
        $startDate = $this->request->getGet('start_date');
        $endDate = $this->request->getGet('end_date');
        $periodData = $this->resolvePeriod($period, $startDate, $endDate);

        $data = [
            'title' => 'Leaderboard & Activity Dashboard',
            'activePage' => 'leaderboard',
            'period' => $periodData['period'],
            'startDate' => $periodData['start_date'],
            'endDate' => $periodData['end_date'],
            'periodLabel' => $periodData['label'],
        ];

        return view('leaderboard/index', $data);
    }

    public function getData()
    {
        if (!$this->request->isAJAX()) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Invalid request']);
        }

        $period = $this->request->getGet('period') ?: 'last_30_days';
        $startDate = $this->request->getGet('start_date');
        $endDate = $this->request->getGet('end_date');
        $periodData = $this->resolvePeriod($period, $startDate, $endDate);

        $response = [
            'status' => 'success',
            'data' => [
                'period' => $periodData,
                'top_customers' => $this->getTopCustomers($periodData['start_date'], $periodData['end_date']),
                'top_products' => $this->getTopProducts($periodData['start_date'], $periodData['end_date']),
                'top_staff' => $this->getTopStaff($periodData['start_date'], $periodData['end_date']),
                'top_users_activity' => $this->getTopUsersByAuditLogs($periodData['start_date'], $periodData['end_date']),
                'updated_at' => date('Y-m-d H:i:s'),
            ],
        ];

        return $this->response->setJSON($response);
    }

    private function resolvePeriod($period, $startDate = null, $endDate = null)
    {
        $today = date('Y-m-d');

        switch ($period) {
            case 'last_7_days':
                return [
                    'period' => 'last_7_days',
                    'start_date' => date('Y-m-d', strtotime('-6 days')),
                    'end_date' => $today,
                    'label' => 'Last 7 Days',
                ];
            case 'last_30_days':
                return [
                    'period' => 'last_30_days',
                    'start_date' => date('Y-m-d', strtotime('-29 days')),
                    'end_date' => $today,
                    'label' => 'Last 30 Days',
                ];
            case 'last_90_days':
                return [
                    'period' => 'last_90_days',
                    'start_date' => date('Y-m-d', strtotime('-89 days')),
                    'end_date' => $today,
                    'label' => 'Last 90 Days',
                ];
            case 'last_year':
                return [
                    'period' => 'last_year',
                    'start_date' => date('Y-m-d', strtotime('-364 days')),
                    'end_date' => $today,
                    'label' => 'Last 12 Months',
                ];
            case 'custom':
                return [
                    'period' => 'custom',
                    'start_date' => $startDate ?: date('Y-m-d', strtotime('-29 days')),
                    'end_date' => $endDate ?: $today,
                    'label' => 'Custom Range',
                ];
            case 'all':
            default:
                $minDate = $this->getEarliestRecordDate();
                return [
                    'period' => 'all',
                    'start_date' => $minDate ?: date('Y-m-d', strtotime('-364 days')),
                    'end_date' => $today,
                    'label' => 'All Time',
                ];
        }
    }

    private function getEarliestRecordDate()
    {
        $db = \Config\Database::connect();
        $candidates = [];

        $sources = [
            ['table' => 'sales', 'field' => 'sale_date'],
            ['table' => 'production_jobs', 'field' => 'production_date'],
            ['table' => 'audit_logs', 'field' => 'created_at'],
        ];

        foreach ($sources as $source) {
            $row = $db->table($source['table'])
                ->selectMin($source['field'], 'min_date')
                ->get()
                ->getRowArray();

            if (!empty($row['min_date'])) {
                $candidates[] = $row['min_date'];
            }
        }

        if (empty($candidates)) {
            return null;
        }

        sort($candidates);
        return $candidates[0];
    }

    /**
     * i. Top Customers by Purchase Value and Production Jobs
     */
    private function getTopCustomers($startDate, $endDate)
    {
        $db = \Config\Database::connect();

        // Get customers by sales value
        $salesQuery = $db->table('sales')
            ->select('customers.id, customers.customer_name, customers.business_name, 
                      COALESCE(SUM(sales.total_amount), 0) as total_sales_value, 
                      COUNT(sales.id) as sales_count')
            ->join('customers', 'customers.id = sales.customer_id', 'left')
            ->where('sales.deleted_at', null)
            ->where('sales.customer_id IS NOT NULL')
            ->where('sales.sale_date >=', $startDate)
            ->where('sales.sale_date <=', $endDate)
            ->groupBy('customers.id')
            ->get()
            ->getResultArray();

        // Get customers by production jobs
        $productionQuery = $db->table('production_jobs')
            ->select('customers.id, customers.customer_name, customers.business_name,
                      COALESCE(SUM(production_jobs.total_material_cost), 0) as total_production_value,
                      COUNT(production_jobs.id) as production_count')
            ->join('customers', 'customers.id = production_jobs.customer_id', 'left')
            ->where('production_jobs.deleted_at', null)
            ->where('production_jobs.customer_id IS NOT NULL')
            ->where('production_jobs.production_date >=', $startDate)
            ->where('production_jobs.production_date <=', $endDate)
            ->groupBy('customers.id')
            ->get()
            ->getResultArray();

        // Merge both sources
        $customers = [];
        
        foreach ($salesQuery as $sale) {
            $id = $sale['id'];
            if (!isset($customers[$id])) {
                $customers[$id] = [
                    'id' => $id,
                    'customer_name' => $sale['customer_name'] ?? 'Unknown',
                    'business_name' => $sale['business_name'] ?? '',
                    'total_sales_value' => 0,
                    'sales_count' => 0,
                    'total_production_value' => 0,
                    'production_count' => 0,
                    'total_value' => 0,
                    'total_transactions' => 0,
                ];
            }
            $customers[$id]['total_sales_value'] = (float) $sale['total_sales_value'];
            $customers[$id]['sales_count'] = (int) $sale['sales_count'];
            $customers[$id]['total_value'] += (float) $sale['total_sales_value'];
            $customers[$id]['total_transactions'] += (int) $sale['sales_count'];
        }

        foreach ($productionQuery as $prod) {
            $id = $prod['id'];
            if (!isset($customers[$id])) {
                $customers[$id] = [
                    'id' => $id,
                    'customer_name' => $prod['customer_name'] ?? 'Unknown',
                    'business_name' => $prod['business_name'] ?? '',
                    'total_sales_value' => 0,
                    'sales_count' => 0,
                    'total_production_value' => 0,
                    'production_count' => 0,
                    'total_value' => 0,
                    'total_transactions' => 0,
                ];
            }
            $customers[$id]['total_production_value'] = (float) $prod['total_production_value'];
            $customers[$id]['production_count'] = (int) $prod['production_count'];
            $customers[$id]['total_value'] += (float) $prod['total_production_value'];
            $customers[$id]['total_transactions'] += (int) $prod['production_count'];
        }

        // Sort by total value
        usort($customers, function($a, $b) {
            return $b['total_value'] <=> $a['total_value'];
        });

        return array_slice($customers, 0, 5);
    }

    /**
     * ii. Top Products by Sales and Production Usage
     */
    private function getTopProducts($startDate, $endDate)
    {
        $db = \Config\Database::connect();

        // Products sold in sales
        $salesQuery = $db->table('sale_items')
            ->select('products.id, products.product_name, products.sku,
                      COALESCE(SUM(sale_items.quantity), 0) as total_quantity_sold,
                      COALESCE(SUM(sale_items.total_price), 0) as total_sales_value,
                      COUNT(DISTINCT sale_items.sale_id) as sales_count')
            ->join('products', 'products.id = sale_items.product_id')
            ->join('sales', 'sales.id = sale_items.sale_id')
            ->where('sales.deleted_at', null)
            ->where('sales.sale_date >=', $startDate)
            ->where('sales.sale_date <=', $endDate)
            ->groupBy('products.id')
            ->get()
            ->getResultArray();

        // Products used in production
        $productionQuery = $db->table('production_materials')
            ->select('products.id, products.product_name, products.sku,
                      COALESCE(SUM(production_materials.quantity_used), 0) as total_quantity_used,
                      COALESCE(SUM(production_materials.total_cost), 0) as total_production_value,
                      COUNT(DISTINCT production_materials.production_job_id) as usage_count')
            ->join('products', 'products.id = production_materials.product_id')
            ->join('production_jobs', 'production_jobs.id = production_materials.production_job_id')
            ->where('production_jobs.deleted_at', null)
            ->where('production_jobs.production_date >=', $startDate)
            ->where('production_jobs.production_date <=', $endDate)
            ->groupBy('products.id')
            ->get()
            ->getResultArray();

        // Merge both sources
        $products = [];
        
        foreach ($salesQuery as $sale) {
            $id = $sale['id'];
            $products[$id] = [
                'id' => $id,
                'product_name' => $sale['product_name'],
                'sku' => $sale['sku'],
                'total_quantity_sold' => (float) $sale['total_quantity_sold'],
                'total_sales_value' => (float) $sale['total_sales_value'],
                'sales_count' => (int) $sale['sales_count'],
                'total_quantity_used' => 0,
                'total_production_value' => 0,
                'usage_count' => 0,
                'total_quantity' => (float) $sale['total_quantity_sold'],
                'total_value' => (float) $sale['total_sales_value'],
                'total_transactions' => (int) $sale['sales_count'],
            ];
        }

        foreach ($productionQuery as $prod) {
            $id = $prod['id'];
            if (!isset($products[$id])) {
                $products[$id] = [
                    'id' => $id,
                    'product_name' => $prod['product_name'],
                    'sku' => $prod['sku'],
                    'total_quantity_sold' => 0,
                    'total_sales_value' => 0,
                    'sales_count' => 0,
                    'total_quantity_used' => 0,
                    'total_production_value' => 0,
                    'usage_count' => 0,
                    'total_quantity' => 0,
                    'total_value' => 0,
                    'total_transactions' => 0,
                ];
            }
            $products[$id]['total_quantity_used'] = (float) $prod['total_quantity_used'];
            $products[$id]['total_production_value'] = (float) $prod['total_production_value'];
            $products[$id]['usage_count'] = (int) $prod['usage_count'];
            $products[$id]['total_quantity'] += (float) $prod['total_quantity_used'];
            $products[$id]['total_value'] += (float) $prod['total_production_value'];
            $products[$id]['total_transactions'] += (int) $prod['usage_count'];
        }

        // Sort by total quantity moved
        usort($products, function($a, $b) {
            return $b['total_quantity'] <=> $a['total_quantity'];
        });

        return array_slice($products, 0, 5);
    }

    /**
     * iii. Top Staff by Sales and Production Activities
     */
    private function getTopStaff($startDate, $endDate)
    {
        $db = \Config\Database::connect();

        // Staff sales performance
        $salesQuery = $db->table('sales')
            ->select('users.id, users.full_name, users.username,
                      COALESCE(SUM(sales.total_amount), 0) as total_sales_value,
                      COUNT(sales.id) as sales_count')
            ->join('users', 'users.id = sales.created_by', 'left')
            ->where('sales.deleted_at', null)
            ->where('sales.created_by IS NOT NULL')
            ->where('sales.sale_date >=', $startDate)
            ->where('sales.sale_date <=', $endDate)
            ->groupBy('users.id')
            ->get()
            ->getResultArray();

        // Staff production performance
        $productionQuery = $db->table('production_jobs')
            ->select('users.id, users.full_name, users.username,
                      COALESCE(SUM(production_jobs.total_material_cost), 0) as total_production_value,
                      COUNT(production_jobs.id) as production_count')
            ->join('users', 'users.id = production_jobs.created_by', 'left')
            ->where('production_jobs.deleted_at', null)
            ->where('production_jobs.created_by IS NOT NULL')
            ->where('production_jobs.production_date >=', $startDate)
            ->where('production_jobs.production_date <=', $endDate)
            ->groupBy('users.id')
            ->get()
            ->getResultArray();

        // Merge both sources
        $staff = [];
        
        foreach ($salesQuery as $sale) {
            $id = $sale['id'];
            $staff[$id] = [
                'id' => $id,
                'full_name' => $sale['full_name'] ?? 'Unknown',
                'username' => $sale['username'] ?? '',
                'total_sales_value' => (float) $sale['total_sales_value'],
                'sales_count' => (int) $sale['sales_count'],
                'total_production_value' => 0,
                'production_count' => 0,
                'total_value' => (float) $sale['total_sales_value'],
                'total_transactions' => (int) $sale['sales_count'],
            ];
        }

        foreach ($productionQuery as $prod) {
            $id = $prod['id'];
            if (!isset($staff[$id])) {
                $staff[$id] = [
                    'id' => $id,
                    'full_name' => $prod['full_name'] ?? 'Unknown',
                    'username' => $prod['username'] ?? '',
                    'total_sales_value' => 0,
                    'sales_count' => 0,
                    'total_production_value' => 0,
                    'production_count' => 0,
                    'total_value' => 0,
                    'total_transactions' => 0,
                ];
            }
            $staff[$id]['total_production_value'] = (float) $prod['total_production_value'];
            $staff[$id]['production_count'] = (int) $prod['production_count'];
            $staff[$id]['total_value'] += (float) $prod['total_production_value'];
            $staff[$id]['total_transactions'] += (int) $prod['production_count'];
        }

        // Sort by total value
        usort($staff, function($a, $b) {
            return $b['total_value'] <=> $a['total_value'];
        });

        return array_slice($staff, 0, 5);
    }

    /**
     * iv. Top Users by Audit Log Activities (REAL AUDIT LOGS)
     */
    private function getTopUsersByAuditLogs($startDate, $endDate)
    {
        $db = \Config\Database::connect();

        // Query audit_logs table for user activities
        $query = $db->table('audit_logs al')
            ->select('
                u.id,
                u.full_name,
                u.username,
                COUNT(al.id) as total_activities,
                SUM(CASE 
                    WHEN al.action LIKE "%create%" 
                    OR al.action LIKE "%add%" 
                    OR al.action LIKE "%store%" 
                    THEN 1 ELSE 0 
                END) as create_count,
                SUM(CASE 
                    WHEN al.action LIKE "%update%" 
                    OR al.action LIKE "%edit%" 
                    OR al.action LIKE "%change%" 
                    THEN 1 ELSE 0 
                END) as update_count,
                SUM(CASE 
                    WHEN al.action LIKE "%delete%" 
                    OR al.action LIKE "%remove%" 
                    OR al.action LIKE "%destroy%" 
                    THEN 1 ELSE 0 
                END) as delete_count,
                SUM(CASE 
                    WHEN al.action LIKE "%login%" 
                    OR al.action LIKE "%logout%" 
                    OR al.action LIKE "%authenticate%" 
                    THEN 1 ELSE 0 
                END) as login_count,
                SUM(CASE 
                    WHEN al.action LIKE "%export%" 
                    OR al.action LIKE "%print%" 
                    OR al.action LIKE "%download%" 
                    THEN 1 ELSE 0 
                END) as export_count
            ')
            ->join('users u', 'u.id = al.user_id', 'left')
            ->where('al.user_id IS NOT NULL')
            ->where('al.created_at >=', $startDate . ' 00:00:00')
            ->where('al.created_at <=', $endDate . ' 23:59:59')
            ->groupBy('al.user_id')
            ->orderBy('total_activities', 'DESC')
            ->limit(5)
            ->get()
            ->getResultArray();

        // If no audit logs found, return message
        if (empty($query) || $query[0]['total_activities'] == 0) {
            return [
                'message' => 'No audit log activities found for this period. Audit logs track user actions like creates, updates, and deletes.',
                'empty' => true
            ];
        }

        // Format the data
        $result = [];
        foreach ($query as $row) {
            $result[] = [
                'id' => $row['id'],
                'full_name' => $row['full_name'] ?? $row['username'],
                'username' => $row['username'],
                'activity_count' => (int) $row['total_activities'],
                'create_count' => (int) ($row['create_count'] ?? 0),
                'update_count' => (int) ($row['update_count'] ?? 0),
                'delete_count' => (int) ($row['delete_count'] ?? 0),
                'login_count' => (int) ($row['login_count'] ?? 0),
                'export_count' => (int) ($row['export_count'] ?? 0),
            ];
        }

        return $result;
    }
}