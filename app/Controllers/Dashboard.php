<?php

namespace App\Controllers;

use App\Models\CategoryModel;
use App\Models\CustomerModel;
use App\Models\ExpenseModel;
use App\Models\ProductModel;
use App\Models\ProductionJobModel;
use App\Models\PurchaseModel;
use App\Models\SaleModel;
use App\Models\AdjustmentEventModel;

class Dashboard extends BaseController
{
    protected $productModel;
    protected $saleModel;
    protected $expenseModel;
    protected $categoryModel;
    protected $purchaseModel;
    protected $productionJobModel;
    protected $customerModel;
    protected $adjustmentModel;

    public function __construct()
    {
        $this->productModel = new ProductModel();
        $this->saleModel = new SaleModel();
        $this->expenseModel = new ExpenseModel();
        $this->categoryModel = new CategoryModel();
        $this->purchaseModel = new PurchaseModel();
        $this->productionJobModel = new ProductionJobModel();
        $this->customerModel = new CustomerModel();
        $this->adjustmentModel = new AdjustmentEventModel();

        if (!session()->get('isLoggedIn')) {
            return redirect()->to('/auth/login');
        }
    }

    public function index()
    {
        $period = $this->request->getGet('period') ?: 'last_30_days';
        $startDate = $this->request->getGet('start_date');
        $endDate = $this->request->getGet('end_date');
        $periodData = $this->resolveDashboardPeriod($period, $startDate, $endDate);

        return view('dashboard/index', [
            'title' => 'Dashboard',
            'activePage' => 'dashboard',
            'defaultPeriod' => $periodData['period'],
            'defaultStartDate' => $periodData['start_date'],
            'defaultEndDate' => $periodData['end_date'],
        ]);
    }

    public function getData()
    {
        if (!$this->request->isAJAX()) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Invalid request']);
        }

        $period = $this->request->getGet('period') ?: 'last_30_days';
        $startDate = $this->request->getGet('start_date');
        $endDate = $this->request->getGet('end_date');
        $periodData = $this->resolveDashboardPeriod($period, $startDate, $endDate);

        $response = [
            'status' => 'success',
            'data' => [
                'filters' => $periodData,
                'inventory_summary' => $this->getInventorySummary(),
                'relationship_summary' => $this->getRelationshipSummary(),
                'financial_summary' => $this->getFinancialSummary($periodData['start_date'], $periodData['end_date']),
                'activity_summary' => $this->getActivitySummary($periodData['start_date'], $periodData['end_date']),
                'financial_trend' => $this->getFinancialTrend($periodData['start_date'], $periodData['end_date']),
                'activity_trend' => $this->getActivityTrend($periodData['start_date'], $periodData['end_date']),
                'category_snapshot' => $this->getCategorySnapshot(),
                'top_products' => $this->getTopProducts($periodData['start_date'], $periodData['end_date']),
                'recent_transactions' => $this->getRecentTransactions($periodData['start_date'], $periodData['end_date']),
                'low_stock_items' => $this->getLowStockItemsDetailed(),
                'net_profit' => $this->getNetProfit($periodData['start_date'], $periodData['end_date']),
                'production_efficiency' => $this->getProductionEfficiency($periodData['start_date'], $periodData['end_date']),
                'updated_at' => date('Y-m-d H:i:s'),
            ],
        ];

        return $this->response->setJSON($response);
    }

    private function resolveDashboardPeriod($period, $startDate = null, $endDate = null)
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
                $minDate = $this->getEarliestRecordDate() ?: date('Y-m-d', strtotime('-364 days'));
                return [
                    'period' => 'all',
                    'start_date' => $minDate,
                    'end_date' => $today,
                    'label' => 'All Time',
                ];

            case 'last_30_days':
            default:
                return [
                    'period' => 'last_30_days',
                    'start_date' => date('Y-m-d', strtotime('-29 days')),
                    'end_date' => $today,
                    'label' => 'Last 30 Days',
                ];
        }
    }

    private function getEarliestRecordDate()
    {
        $db = \Config\Database::connect();
        $candidates = [];

        $sources = [
            ['table' => 'sales', 'field' => 'sale_date'],
            ['table' => 'expenses', 'field' => 'expense_date'],
            ['table' => 'purchases', 'field' => 'purchase_date'],
            ['table' => 'production_jobs', 'field' => 'production_date'],
            ['table' => 'adjustment_events', 'field' => 'event_date'],
        ];

        foreach ($sources as $source) {
            $row = $db->table($source['table'])
                ->selectMin($source['field'], 'min_date')
                ->where('deleted_at', null)
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

    private function getInventorySummary()
    {
        $allProducts = $this->productModel->where('deleted_at', null)->findAll();
        $lowStockItems = $this->productModel
            ->where('products.deleted_at', null)
            ->where('products.quantity <= products.minimum_stock', null, false)
            ->findAll();
        $outOfStockItems = $this->productModel
            ->where('deleted_at', null)
            ->where('quantity <=', 0)
            ->findAll();

        $totalUnits = 0;
        foreach ($allProducts as $product) {
            $totalUnits += (float) ($product['quantity'] ?? 0);
        }

        return [
            'total_products' => count($allProducts),
            'total_categories' => $this->categoryModel->where('deleted_at', null)->countAllResults(),
            'total_units_in_stock' => $totalUnits,
            'low_stock_count' => count($lowStockItems),
            'out_of_stock_count' => count($outOfStockItems),
        ];
    }

    private function getRelationshipSummary()
    {
        return [
            'customers' => $this->customerModel->where('deleted_at', null)->countAllResults(),
            'suppliers' => $this->countTableRows('suppliers'),
            'active_users' => $this->countTableRows('users', ['is_active' => 1, 'deleted_at' => null]),
        ];
    }

    private function getFinancialSummary($startDate, $endDate)
    {
        $adjustmentSummary = $this->adjustmentModel->getSummary($startDate, $endDate);
        
        return [
            'sales' => $this->getCurrencySummaryForTable('sales', 'sale_date', 'total_amount', $startDate, $endDate, ['deleted_at' => null]),
            'expenses' => $this->getCurrencySummaryForTable('expenses', 'expense_date', 'amount', $startDate, $endDate, ['deleted_at' => null]),
            'purchases' => $this->getCurrencySummaryForTable('purchases', 'purchase_date', 'total_amount', $startDate, $endDate, ['deleted_at' => null, 'status' => 'Received']),
            'production_cost' => $this->getCurrencySummaryForTable('production_jobs', 'production_date', 'total_material_cost', $startDate, $endDate, ['deleted_at' => null, 'status' => 'Completed']),
            'adjustments' => $adjustmentSummary,
        ];
    }

    private function getCurrencySummaryForTable($table, $dateField, $amountField, $startDate, $endDate, array $conditions = [])
    {
        $db = \Config\Database::connect();
        $summary = [
            'LRD' => 0,
            'USD' => 0,
            'count' => 0,
        ];

        $rows = $db->table($table)
            ->select("currency, SUM({$amountField}) as total_amount, COUNT(id) as record_count", false)
            ->where($dateField . ' >=', $startDate)
            ->where($dateField . ' <=', $endDate)
            ->where($conditions)
            ->groupBy('currency')
            ->get()
            ->getResultArray();

        foreach ($rows as $row) {
            $currency = $row['currency'] ?? 'LRD';
            if (!isset($summary[$currency])) {
                $summary[$currency] = 0;
            }

            $summary[$currency] = (float) ($row['total_amount'] ?? 0);
            $summary['count'] += (int) ($row['record_count'] ?? 0);
        }

        return $summary;
    }

    private function getActivitySummary($startDate, $endDate)
    {
        return [
            'sales_count' => $this->countByDateRange('sales', 'sale_date', $startDate, $endDate, ['deleted_at' => null]),
            'expense_count' => $this->countByDateRange('expenses', 'expense_date', $startDate, $endDate, ['deleted_at' => null]),
            'purchase_count' => $this->countByDateRange('purchases', 'purchase_date', $startDate, $endDate, ['deleted_at' => null, 'status' => 'Received']),
            'production_count' => $this->countByDateRange('production_jobs', 'production_date', $startDate, $endDate, ['deleted_at' => null, 'status' => 'Completed']),
            'draft_production_jobs' => $this->countTableRows('production_jobs', ['deleted_at' => null, 'status' => 'Draft']),
            'draft_purchases' => $this->countTableRows('purchases', ['deleted_at' => null, 'status' => 'Draft']),
        ];
    }

    private function countByDateRange($table, $dateField, $startDate, $endDate, array $conditions = [])
    {
        $db = \Config\Database::connect();
        return $db->table($table)
            ->where($dateField . ' >=', $startDate)
            ->where($dateField . ' <=', $endDate)
            ->where($conditions)
            ->countAllResults();
    }

    private function countTableRows($table, array $conditions = [])
    {
        $db = \Config\Database::connect();
        return $db->table($table)->where($conditions)->countAllResults();
    }

    private function getFinancialTrend($startDate, $endDate)
    {
        $buckets = $this->buildTimeBuckets($startDate, $endDate);
        $series = [
            'sales_lrd' => array_fill(0, count($buckets['items']), 0),
            'sales_usd' => array_fill(0, count($buckets['items']), 0),
            'expenses_lrd' => array_fill(0, count($buckets['items']), 0),
            'expenses_usd' => array_fill(0, count($buckets['items']), 0),
        ];

        $bucketIndex = [];
        foreach ($buckets['items'] as $index => $bucket) {
            $bucketIndex[$bucket['key']] = $index;
        }

        $sales = $this->saleModel
            ->select('sale_date, currency, total_amount')
            ->where('deleted_at', null)
            ->where('sale_date >=', $startDate)
            ->where('sale_date <=', $endDate)
            ->findAll();

        foreach ($sales as $sale) {
            $key = $this->resolveBucketKey($sale['sale_date'], $buckets['granularity']);
            if (!isset($bucketIndex[$key])) {
                continue;
            }

            $dataset = strtoupper($sale['currency']) === 'USD' ? 'sales_usd' : 'sales_lrd';
            $series[$dataset][$bucketIndex[$key]] += (float) $sale['total_amount'];
        }

        $expenses = $this->expenseModel
            ->select('expense_date, currency, amount')
            ->where('deleted_at', null)
            ->where('expense_date >=', $startDate)
            ->where('expense_date <=', $endDate)
            ->findAll();

        foreach ($expenses as $expense) {
            $key = $this->resolveBucketKey($expense['expense_date'], $buckets['granularity']);
            if (!isset($bucketIndex[$key])) {
                continue;
            }

            $dataset = strtoupper($expense['currency']) === 'USD' ? 'expenses_usd' : 'expenses_lrd';
            $series[$dataset][$bucketIndex[$key]] += (float) $expense['amount'];
        }

        return [
            'labels' => array_column($buckets['items'], 'label'),
            'granularity' => $buckets['granularity'],
            'series' => $series,
        ];
    }

    private function getActivityTrend($startDate, $endDate)
    {
        $buckets = $this->buildTimeBuckets($startDate, $endDate);
        $series = [
            'sales_count' => array_fill(0, count($buckets['items']), 0),
            'purchase_count' => array_fill(0, count($buckets['items']), 0),
            'expense_count' => array_fill(0, count($buckets['items']), 0),
            'production_count' => array_fill(0, count($buckets['items']), 0),
        ];

        $bucketIndex = [];
        foreach ($buckets['items'] as $index => $bucket) {
            $bucketIndex[$bucket['key']] = $index;
        }

        $datasets = [
            'sales' => ['model' => $this->saleModel, 'date_field' => 'sale_date', 'series' => 'sales_count', 'conditions' => ['deleted_at' => null]],
            'purchases' => ['model' => $this->purchaseModel, 'date_field' => 'purchase_date', 'series' => 'purchase_count', 'conditions' => ['deleted_at' => null, 'status' => 'Received']],
            'expenses' => ['model' => $this->expenseModel, 'date_field' => 'expense_date', 'series' => 'expense_count', 'conditions' => ['deleted_at' => null]],
            'production' => ['model' => $this->productionJobModel, 'date_field' => 'production_date', 'series' => 'production_count', 'conditions' => ['deleted_at' => null, 'status' => 'Completed']],
        ];

        foreach ($datasets as $dataset) {
            $rows = $dataset['model']
                ->select($dataset['date_field'])
                ->where($dataset['date_field'] . ' >=', $startDate)
                ->where($dataset['date_field'] . ' <=', $endDate)
                ->where($dataset['conditions'])
                ->findAll();

            foreach ($rows as $row) {
                $key = $this->resolveBucketKey($row[$dataset['date_field']], $buckets['granularity']);
                if (isset($bucketIndex[$key])) {
                    $series[$dataset['series']][$bucketIndex[$key]]++;
                }
            }
        }

        return [
            'labels' => array_column($buckets['items'], 'label'),
            'granularity' => $buckets['granularity'],
            'series' => $series,
        ];
    }

    private function buildTimeBuckets($startDate, $endDate)
    {
        $start = new \DateTime($startDate);
        $end = new \DateTime($endDate);
        $days = (int) $start->diff($end)->format('%a') + 1;

        $granularity = $days <= 31 ? 'day' : ($days <= 180 ? 'week' : 'month');
        $buckets = [];

        if ($granularity === 'day') {
            $cursor = clone $start;
            while ($cursor <= $end) {
                $buckets[] = [
                    'key' => $cursor->format('Y-m-d'),
                    'label' => $cursor->format('M d'),
                ];
                $cursor->modify('+1 day');
            }
        } elseif ($granularity === 'week') {
            $cursor = clone $start;
            while ($cursor <= $end) {
                $weekStart = clone $cursor;
                $weekEnd = clone $cursor;
                $weekEnd->modify('+6 days');
                if ($weekEnd > $end) {
                    $weekEnd = clone $end;
                }

                $buckets[] = [
                    'key' => $weekStart->format('o-\WW'),
                    'label' => $weekStart->format('M d') . ' - ' . $weekEnd->format('M d'),
                ];

                $cursor = $weekEnd->modify('+1 day');
            }
        } else {
            $cursor = new \DateTime($start->format('Y-m-01'));
            $finalMonth = new \DateTime($end->format('Y-m-01'));

            while ($cursor <= $finalMonth) {
                $buckets[] = [
                    'key' => $cursor->format('Y-m'),
                    'label' => $cursor->format('M Y'),
                ];
                $cursor->modify('+1 month');
            }
        }

        return [
            'granularity' => $granularity,
            'items' => $buckets,
        ];
    }

    private function resolveBucketKey($date, $granularity)
    {
        $dateTime = new \DateTime($date);

        if ($granularity === 'day') {
            return $dateTime->format('Y-m-d');
        }

        if ($granularity === 'week') {
            return $dateTime->format('o-\WW');
        }

        return $dateTime->format('Y-m');
    }

    private function getCategorySnapshot()
    {
        $categories = $this->categoryModel->where('deleted_at', null)->findAll();
        $snapshot = [];

        foreach ($categories as $category) {
            $products = $this->productModel
                ->where('deleted_at', null)
                ->where('category_id', $category['id'])
                ->findAll();

            $stockUnits = 0;
            foreach ($products as $product) {
                $stockUnits += (float) ($product['quantity'] ?? 0);
            }

            if (!empty($products)) {
                $snapshot[] = [
                    'category_name' => $category['category_name'],
                    'product_count' => count($products),
                    'stock_units' => $stockUnits,
                ];
            }
        }

        usort($snapshot, static function ($first, $second) {
            return $second['stock_units'] <=> $first['stock_units'];
        });

        return array_slice($snapshot, 0, 8);
    }

    private function getTopProducts($startDate, $endDate)
    {
        $db = \Config\Database::connect();

        return $db->table('sale_items')
            ->select('products.id, products.product_name, SUM(sale_items.quantity) as total_quantity, COUNT(DISTINCT sale_items.sale_id) as invoice_count')
            ->join('sales', 'sales.id = sale_items.sale_id')
            ->join('products', 'products.id = sale_items.product_id')
            ->where('sales.deleted_at', null)
            ->where('sales.sale_date >=', $startDate)
            ->where('sales.sale_date <=', $endDate)
            ->groupBy('sale_items.product_id, products.product_name, products.id')
            ->orderBy('total_quantity', 'DESC')
            ->limit(8)
            ->get()
            ->getResultArray();
    }

    private function getRecentTransactions($startDate, $endDate)
    {
        $entries = [];

        $sales = $this->saleModel
            ->select('id, invoice_number, customer_name, total_amount, currency, payment_status, created_at, sale_date')
            ->where('deleted_at', null)
            ->where('sale_date >=', $startDate)
            ->where('sale_date <=', $endDate)
            ->orderBy('created_at', 'DESC')
            ->findAll(6);

        foreach ($sales as $sale) {
            $entries[] = [
                'date' => $sale['created_at'],
                'module' => 'Sales',
                'reference' => $sale['invoice_number'],
                'party' => $sale['customer_name'] ?: 'Walk-in Customer',
                'amount' => (float) $sale['total_amount'],
                'currency' => $sale['currency'],
                'status' => $sale['payment_status'],
                'link' => base_url('sales/view/' . $sale['id']),
            ];
        }

        $purchases = $this->purchaseModel
            ->select('id, purchase_number, supplier_name, total_amount, currency, payment_status, created_at, purchase_date')
            ->where('deleted_at', null)
            ->where('purchase_date >=', $startDate)
            ->where('purchase_date <=', $endDate)
            ->orderBy('created_at', 'DESC')
            ->findAll(6);

        foreach ($purchases as $purchase) {
            $entries[] = [
                'date' => $purchase['created_at'],
                'module' => 'Purchases',
                'reference' => $purchase['purchase_number'],
                'party' => $purchase['supplier_name'] ?: 'Supplier',
                'amount' => (float) $purchase['total_amount'],
                'currency' => $purchase['currency'],
                'status' => $purchase['payment_status'],
                'link' => base_url('purchases/view/' . $purchase['id']),
            ];
        }

        $expenses = $this->expenseModel
            ->select('expenses.id, expenses.amount, expenses.currency, expenses.created_at, expenses.expense_date, expense_categories.category_name')
            ->join('expense_categories', 'expense_categories.id = expenses.expense_category_id', 'left')
            ->where('expenses.deleted_at', null)
            ->where('expense_date >=', $startDate)
            ->where('expense_date <=', $endDate)
            ->orderBy('expenses.created_at', 'DESC')
            ->findAll(6);

        foreach ($expenses as $expense) {
            $entries[] = [
                'date' => $expense['created_at'],
                'module' => 'Expenses',
                'reference' => 'EXP-' . $expense['id'],
                'party' => $expense['category_name'] ?: 'Expense',
                'amount' => (float) $expense['amount'],
                'currency' => $expense['currency'],
                'status' => 'Recorded',
                'link' => base_url('expenses/edit/' . $expense['id']),
            ];
        }

        usort($entries, static function ($first, $second) {
            return strtotime($second['date']) <=> strtotime($first['date']);
        });

        return array_slice($entries, 0, 12);
    }

    private function getLowStockItemsDetailed()
    {
        return $this->productModel
            ->select('products.id, products.product_name, products.quantity, products.minimum_stock, units.unit_name, units.unit_symbol')
            ->join('units', 'units.id = products.unit_id', 'left')
            ->where('products.deleted_at', null)
            ->where('products.quantity <= products.minimum_stock', null, false)
            ->orderBy('products.quantity', 'ASC')
            ->findAll(10);
    }

    /**
     * Calculate Net Profit for the dashboard
     */
    private function getNetProfit($startDate, $endDate)
    {
        $financial = $this->getFinancialSummary($startDate, $endDate);
        
        // Get sales revenue
        $revenueLRD = $financial['sales']['LRD'];
        $revenueUSD = $financial['sales']['USD'];
        
        // Get refunds from adjustments
        $adjustments = $financial['adjustments'];
        $refundsLRD = 0;
        $refundsUSD = 0;
        
        if (isset($adjustments['Refund'])) {
            $refundsLRD = $adjustments['Refund']['total_value'] ?? 0;
        }
        if (isset($adjustments['USD']['Refund'])) {
            $refundsUSD = $adjustments['USD']['Refund']['total_value'] ?? 0;
        }
        
        // Get COGS (purchases)
        $cogsLRD = $financial['purchases']['LRD'];
        $cogsUSD = $financial['purchases']['USD'];
        
        // Get operating expenses
        $expensesLRD = $financial['expenses']['LRD'];
        $expensesUSD = $financial['expenses']['USD'];
        
        // Get damage and theft losses from adjustments
        $damageLRD = 0;
        $damageUSD = 0;
        $theftLRD = 0;
        $theftUSD = 0;
        
        if (isset($adjustments['Damage'])) {
            $damageLRD = $adjustments['Damage']['total_value'] ?? 0;
        }
        if (isset($adjustments['USD']['Damage'])) {
            $damageUSD = $adjustments['USD']['Damage']['total_value'] ?? 0;
        }
        if (isset($adjustments['Theft'])) {
            $theftLRD = $adjustments['Theft']['total_value'] ?? 0;
        }
        if (isset($adjustments['USD']['Theft'])) {
            $theftUSD = $adjustments['USD']['Theft']['total_value'] ?? 0;
        }
        
        $totalLossesLRD = $damageLRD + $theftLRD;
        $totalLossesUSD = $damageUSD + $theftUSD;
        
        // Calculate net sales (revenue minus refunds)
        $netSalesLRD = $revenueLRD - $refundsLRD;
        $netSalesUSD = $revenueUSD - $refundsUSD;
        
        // Calculate gross profit (net sales minus COGS)
        $grossProfitLRD = $netSalesLRD - $cogsLRD;
        $grossProfitUSD = $netSalesUSD - $cogsUSD;
        
        // Calculate net profit (gross profit minus expenses and losses)
        $netProfitLRD = $grossProfitLRD - $expensesLRD - $totalLossesLRD;
        $netProfitUSD = $grossProfitUSD - $expensesUSD - $totalLossesUSD;
        
        return [
            'LRD' => [
                'revenue' => $revenueLRD,
                'refunds' => $refundsLRD,
                'net_sales' => $netSalesLRD,
                'cogs' => $cogsLRD,
                'gross_profit' => $grossProfitLRD,
                'expenses' => $expensesLRD,
                'damage' => $damageLRD,
                'theft' => $theftLRD,
                'total_losses' => $totalLossesLRD,
                'net_profit' => $netProfitLRD,
            ],
            'USD' => [
                'revenue' => $revenueUSD,
                'refunds' => $refundsUSD,
                'net_sales' => $netSalesUSD,
                'cogs' => $cogsUSD,
                'gross_profit' => $grossProfitUSD,
                'expenses' => $expensesUSD,
                'damage' => $damageUSD,
                'theft' => $theftUSD,
                'total_losses' => $totalLossesUSD,
                'net_profit' => $netProfitUSD,
            ],
        ];
    }

    /**
     * Calculate Production Efficiency (percentage of material not wasted)
     */
    private function getProductionEfficiency($startDate, $endDate)
    {
        $financial = $this->getFinancialSummary($startDate, $endDate);
        
        $materialCostLRD = $financial['production_cost']['LRD'];
        $materialCostUSD = $financial['production_cost']['USD'];
        
        $adjustments = $financial['adjustments'];
        
        $damageLRD = 0;
        $damageUSD = 0;
        
        if (isset($adjustments['Damage'])) {
            $damageLRD = $adjustments['Damage']['total_value'] ?? 0;
        }
        if (isset($adjustments['USD']['Damage'])) {
            $damageUSD = $adjustments['USD']['Damage']['total_value'] ?? 0;
        }
        
        // Efficiency = (Total Material - Damage) / Total Material * 100
        $efficiencyLRD = $materialCostLRD > 0 ? (($materialCostLRD - $damageLRD) / $materialCostLRD) * 100 : 100;
        $efficiencyUSD = $materialCostUSD > 0 ? (($materialCostUSD - $damageUSD) / $materialCostUSD) * 100 : 100;
        
        return [
            'LRD' => round($efficiencyLRD, 2),
            'USD' => round($efficiencyUSD, 2),
            'material_cost_lrd' => $materialCostLRD,
            'material_cost_usd' => $materialCostUSD,
            'damage_lrd' => $damageLRD,
            'damage_usd' => $damageUSD,
        ];
    }
}