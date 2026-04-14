<?php

namespace App\Controllers;

use App\Models\ProductModel;
use App\Models\SaleModel;
use App\Models\PurchaseModel;
use App\Models\ExpenseModel;
use App\Models\ProductionJobModel;
use App\Models\StockMovementModel;
use App\Models\CategoryModel;
use App\Models\SystemSettingModel;
use App\Models\CustomerModel;
use App\Models\UserModel;
use App\Models\AdjustmentEventModel;

class Reports extends BaseController
{
    protected $productModel;
    protected $saleModel;
    protected $purchaseModel;
    protected $expenseModel;
    protected $productionJobModel;
    protected $stockMovementModel;
    protected $categoryModel;
    protected $settingsModel;
    protected $customerModel;
    protected $userModel;
    protected $adjustmentEventModel;
    protected $db;

    public function __construct()
    {
        $this->productModel = new ProductModel();
        $this->saleModel = new SaleModel();
        $this->purchaseModel = new PurchaseModel();
        $this->expenseModel = new ExpenseModel();
        $this->productionJobModel = new ProductionJobModel();
        $this->stockMovementModel = new StockMovementModel();
        $this->categoryModel = new CategoryModel();
        $this->settingsModel = new SystemSettingModel();
        $this->customerModel = new CustomerModel();
        $this->userModel = new UserModel();
        $this->adjustmentEventModel = new AdjustmentEventModel();
        $this->db = \Config\Database::connect();

        if (!session()->get('isLoggedIn')) {
            return redirect()->to('/auth/login');
        }
    }

    /**
     * Inventory Reports
     */
    public function inventory()
    {
        $categoryId = $this->request->getGet('category');
        $stockStatus = $this->request->getGet('stock_status');

        $data = $this->getInventoryReportData($categoryId, $stockStatus);
        $data['title'] = 'Inventory Reports';
        $data['activePage'] = 'reports';
        $data['activeSubPage'] = 'inventory';

        return view('reports/inventory', $data);
    }

    /**
     * Generate Inventory Report View
     */
    public function generateInventory()
    {
        $categoryId = $this->request->getGet('category');
        $stockStatus = $this->request->getGet('stock_status');

        $data = $this->getInventoryReportData($categoryId, $stockStatus);
        $data['title'] = 'Inventory Report';
        $data['activePage'] = 'reports';

        return view('reports/inventory', $data);
    }

    /**
     * Export Inventory Report
     */
    public function exportInventory()
    {
        $type = $this->request->getGet('type');
        $categoryId = $this->request->getGet('category');
        $stockStatus = $this->request->getGet('stock_status');

        $data = $this->getInventoryReportData($categoryId, $stockStatus);

        if ($type === 'pdf') {
            return $this->exportPDF('reports/inventory_pdf', $data, 'inventory_report.pdf');
        } else {
            $rows = [
                [
                    'product_name' => 'Product Name',
                    'sku' => 'SKU',
                    'category_name' => 'Category',
                    'quantity' => 'Current Stock',
                    'minimum_stock' => 'Min Stock',
                    'purchase_price' => 'Purchase Price',
                    'selling_price' => 'Selling Price',
                    'stock_value_purchase' => 'Stock Value (Purchase)',
                    'stock_value_selling' => 'Stock Value (Selling)',
                    'status' => 'Status',
                ]
            ];

            foreach ($data['products'] as $product) {
                $rows[] = [
                    'product_name' => $product['product_name'],
                    'sku' => $product['sku'],
                    'category_name' => $product['category_name'] ?? 'Uncategorized',
                    'quantity' => $product['quantity'],
                    'minimum_stock' => $product['minimum_stock'],
                    'purchase_price' => $product['purchase_price'],
                    'selling_price' => $product['selling_price'],
                    'stock_value_purchase' => $product['stock_value_purchase'],
                    'stock_value_selling' => $product['stock_value_selling'],
                    'status' => $product['stock_status_label'],
                ];
            }

            return $this->exportExcel($rows, 'Inventory Report', 'inventory_report.xlsx');
        }
    }

    /**
     * Sales Reports
     */
    /**
     * Sales Reports - FIXED to include adjustments
     */
    public function sales()
    {
        $period = $this->request->getGet('period');
        [$startDate, $endDate, $period] = $this->resolveSalesReportPeriod($period);
        $currency = $this->request->getGet('currency');
        $productId = $this->request->getGet('product_id');

        // Get sales data
        $topProducts = $this->getTopProducts($startDate, $endDate, $productId, $currency);
        $salesByMethod = $this->getSalesByPaymentMethod($startDate, $endDate, $currency);
        $salesLog = $this->getSalesLog($startDate, $endDate, $currency, $productId);
        $customerSummary = $this->getCustomerPurchaseSummary($startDate, $endDate);
        $sellerSummary = $this->getSellerSalesSummary($startDate, $endDate);
        $dailySales = $this->getDailySales($startDate, $endDate, $currency);

        // Get adjustment impact on sales (Refunds and Returns)
        $adjustmentSummary = $this->getAdjustmentSummary($startDate, $endDate, $currency);

        // Calculate totals by currency
        $grossSalesLRD = $this->saleModel->getTotalByDateRange($startDate, $endDate, 'LRD');
        $grossSalesUSD = $this->saleModel->getTotalByDateRange($startDate, $endDate, 'USD');

        // Get refunds and returns from adjustments
        $refundsLRD = $adjustmentSummary['LRD']['Refund'] ?? 0;
        $refundsUSD = $adjustmentSummary['USD']['Refund'] ?? 0;
        $returnsLRD = $adjustmentSummary['LRD']['Return'] ?? 0;
        $returnsUSD = $adjustmentSummary['USD']['Return'] ?? 0;

        // Calculate net sales (gross sales minus refunds)
        $netSalesLRD = $grossSalesLRD - $refundsLRD;
        $netSalesUSD = $grossSalesUSD - $refundsUSD;

        // Calculate refund percentage
        $refundPercentageLRD = $grossSalesLRD > 0 ? ($refundsLRD / $grossSalesLRD) * 100 : 0;
        $refundPercentageUSD = $grossSalesUSD > 0 ? ($refundsUSD / $grossSalesUSD) * 100 : 0;

        $totalTax = $this->getSalesAggregate($startDate, $endDate, 'tax', $currency);
        $totalDiscount = $this->getSalesAggregate($startDate, $endDate, 'discount', $currency);

        $data = [
            'title' => 'Sales Reports',
            'startDate' => $startDate,
            'endDate' => $endDate,
            'selectedPeriod' => $period,
            'selectedCurrency' => $currency,
            'selectedProduct' => $productId,
            'topProducts' => $topProducts,
            'salesByMethod' => $salesByMethod,
            'salesLog' => $salesLog,
            'customerSummary' => $customerSummary,
            'sellerSummary' => $sellerSummary,
            'dailySales' => $dailySales,
            // Sales totals
            'grossSalesLRD' => $grossSalesLRD,
            'grossSalesUSD' => $grossSalesUSD,
            'refundsLRD' => $refundsLRD,
            'refundsUSD' => $refundsUSD,
            'returnsLRD' => $returnsLRD,
            'returnsUSD' => $returnsUSD,
            'netSalesLRD' => $netSalesLRD,
            'netSalesUSD' => $netSalesUSD,
            'refundPercentageLRD' => $refundPercentageLRD,
            'refundPercentageUSD' => $refundPercentageUSD,
            'totalTax' => $totalTax,
            'totalDiscount' => $totalDiscount,
            'products' => $this->productModel->findAll(),
            'activePage' => 'reports',
            'activeSubPage' => 'sales'
        ];

        return view('reports/sales', $data);
    }

    /**
     * Export Sales Report
     */
    public function exportSales()
    {
        $type = $this->request->getGet('type');
        $period = $this->request->getGet('period');
        [$startDate, $endDate] = $this->resolveSalesReportPeriod($period);
        $currency = $this->request->getGet('currency');
        $productId = $this->request->getGet('product_id');

        $builder = $this->saleModel->select('sales.*, customers.customer_name, users.full_name as seller_name')
            ->join('customers', 'customers.id = sales.customer_id', 'left')
            ->join('users', 'users.id = sales.created_by', 'left')
            ->orderBy('sale_date', 'DESC');

        $this->applySaleDateRange($builder, $startDate, $endDate);

        if ($currency) {
            $builder->where('sales.currency', $currency);
        }

        if ($productId) {
            $builder->join('sale_items', 'sale_items.sale_id = sales.id');
            $builder->where('sale_items.product_id', $productId);
            $builder->groupBy('sales.id');
        }

        $sales = $builder->findAll();

        $data = [
            'sales' => $sales,
            'startDate' => $startDate,
            'endDate' => $endDate,
            'date' => date('Y-m-d H:i:s'),
            'business_name' => $this->settingsModel->get('business_name', 'Innovative Graphics')
        ];

        if ($type === 'pdf') {
            return $this->exportPDF('reports/sales_pdf', $data, 'sales_report.pdf');
        } else {
            return $this->exportExcel($sales, 'Sales Report', 'sales_report.xlsx');
        }
    }

    /**
     * Financial Reports (Profit & Loss)
     */
    public function financial()
    {
        $period = $this->request->getGet('period');
        [$startDate, $endDate, $period] = $this->resolveFinancialReportPeriod($period);
        $currency = $this->request->getGet('currency');
        $categoryId = $this->request->getGet('category');
        $createdBy = $this->request->getGet('created_by');

        $revenueLRD = $this->saleModel->getTotalByDateRange($startDate, $endDate, 'LRD');
        $revenueUSD = $this->saleModel->getTotalByDateRange($startDate, $endDate, 'USD');

        $cogs = $this->calculateCOGS($startDate, $endDate);

        $expensesLRD = $this->expenseModel->getTotalByDateRange($startDate, $endDate, 'LRD');
        $expensesUSD = $this->expenseModel->getTotalByDateRange($startDate, $endDate, 'USD');

        $expensesByCategory = $this->expenseModel->getSummaryByCategory($startDate, $endDate, $currency, $categoryId);
        $expensesByUser = $this->expenseModel->getSummaryByUser($startDate, $endDate, $currency, $categoryId);
        $expenseLog = $this->expenseModel->getDetailedExpenses($startDate, $endDate, $currency, $categoryId, $createdBy);
        $financialTrend = $this->getFinancialTrend($startDate, $endDate, $currency);
        $receivedPurchases = [
            'LRD' => $this->purchaseModel->getTotalByDateRange($startDate, $endDate, 'LRD'),
            'USD' => $this->purchaseModel->getTotalByDateRange($startDate, $endDate, 'USD'),
        ];

        $adjustmentSummary = $this->getAdjustmentSummary($startDate, $endDate, $currency);

        $grossProfitLRD = $revenueLRD - $cogs['LRD'];
        $grossProfitUSD = $revenueUSD - $cogs['USD'];

        $netProfitLRD = $grossProfitLRD - $expensesLRD;
        $netProfitUSD = $grossProfitUSD - $expensesUSD;

        $data = [
            'title' => 'Financial Reports',
            'startDate' => $startDate,
            'endDate' => $endDate,
            'selectedPeriod' => $period,
            'selectedCurrency' => $currency,
            'selectedCategory' => $categoryId,
            'selectedUser' => $createdBy,
            'revenue' => ['LRD' => $revenueLRD, 'USD' => $revenueUSD],
            'cogs' => $cogs,
            'gross_profit' => ['LRD' => $grossProfitLRD, 'USD' => $grossProfitUSD],
            'expenses' => ['LRD' => $expensesLRD, 'USD' => $expensesUSD],
            'expenses_by_category' => $expensesByCategory,
            'expenses_by_user' => $expensesByUser,
            'expense_log' => $expenseLog,
            'financial_trend' => $financialTrend,
            'received_purchases' => $receivedPurchases,
            'adjustment_summary' => $adjustmentSummary,
            'net_profit' => ['LRD' => $netProfitLRD, 'USD' => $netProfitUSD],
            'categories' => $this->categoryModel->findAll(),
            'expenseCategories' => model(\App\Models\ExpenseCategoryModel::class)->findAll(),
            'users' => $this->userModel->findAll(),
            'activePage' => 'reports',
            'activeSubPage' => 'financial'
        ];

        return view('reports/financial', $data);
    }

    /**
     * Export Financial Report
     */
    public function exportFinancial()
    {
        $type = $this->request->getGet('type');
        $period = $this->request->getGet('period');
        [$startDate, $endDate] = $this->resolveFinancialReportPeriod($period);
        $currency = $this->request->getGet('currency');
        $categoryId = $this->request->getGet('category');
        $createdBy = $this->request->getGet('created_by');

        $revenueLRD = $this->saleModel->getTotalByDateRange($startDate, $endDate, 'LRD');
        $revenueUSD = $this->saleModel->getTotalByDateRange($startDate, $endDate, 'USD');
        $cogs = $this->calculateCOGS($startDate, $endDate);
        $expensesLRD = $this->expenseModel->getTotalByDateRange($startDate, $endDate, 'LRD');
        $expensesUSD = $this->expenseModel->getTotalByDateRange($startDate, $endDate, 'USD');
        $expenseLog = $this->expenseModel->getDetailedExpenses($startDate, $endDate, $currency, $categoryId, $createdBy);

        $data = [
            'startDate' => $startDate,
            'endDate' => $endDate,
            'selectedCurrency' => $currency,
            'revenueLRD' => $revenueLRD,
            'revenueUSD' => $revenueUSD,
            'cogsLRD' => $cogs['LRD'],
            'cogsUSD' => $cogs['USD'],
            'expensesLRD' => $expensesLRD,
            'expensesUSD' => $expensesUSD,
            'grossProfitLRD' => $revenueLRD - $cogs['LRD'],
            'grossProfitUSD' => $revenueUSD - $cogs['USD'],
            'netProfitLRD' => ($revenueLRD - $cogs['LRD']) - $expensesLRD,
            'netProfitUSD' => ($revenueUSD - $cogs['USD']) - $expensesUSD,
            'expenseLog' => $expenseLog,
            'date' => date('Y-m-d H:i:s'),
            'business_name' => $this->settingsModel->get('business_name', 'Innovative Graphics')
        ];

        if ($type === 'pdf') {
            return $this->exportPDF('reports/financial_pdf', $data, 'financial_report.pdf');
        } else {
            $summary = [
                ['Metric', 'LRD', 'USD'],
                ['Revenue', number_format($revenueLRD, 2), number_format($revenueUSD, 2)],
                ['Cost of Goods Sold', number_format($cogs['LRD'], 2), number_format($cogs['USD'], 2)],
                ['Gross Profit', number_format($revenueLRD - $cogs['LRD'], 2), number_format($revenueUSD - $cogs['USD'], 2)],
                ['Expenses', number_format($expensesLRD, 2), number_format($expensesUSD, 2)],
                ['Net Profit', number_format(($revenueLRD - $cogs['LRD']) - $expensesLRD, 2), number_format(($revenueUSD - $cogs['USD']) - $expensesUSD, 2)]
            ];
            $summary[] = [];
            $summary[] = ['Expense Date', 'Category', 'Amount / Currency', 'Recorded By'];
            foreach ($expenseLog as $expense) {
                $summary[] = [
                    $expense['expense_date'],
                    $expense['category_name'] ?? 'Uncategorized',
                    number_format((float) $expense['amount'], 2) . ' ' . $expense['currency'],
                    $expense['created_by_name'] ?? 'Unknown',
                ];
            }
            return $this->exportExcel($summary, 'Profit & Loss', 'financial_report.xlsx');
        }
    }

    /**
     * Production Reports
     */
    /**
     * Production Reports - FIXED to include damage/waste from adjustments
     */
    public function production()
    {
        $period = $this->request->getGet('period');
        [$startDate, $endDate, $period] = $this->resolveProductionReportPeriod($period);
        $status = $this->request->getGet('status');
        $currency = $this->request->getGet('currency');
        $finishedProductId = $this->request->getGet('finished_product_id');
        $createdBy = $this->request->getGet('created_by');

        // Get production data
        $jobs = $this->getProductionJobsDetailed($startDate, $endDate, $status, $currency, $finishedProductId, $createdBy);
        $materialUsage = $this->getProductionMaterialUsageReport($startDate, $endDate, $status, $currency, $finishedProductId, $createdBy);
        $costSummary = $this->getProductionCostSummary($startDate, $endDate, $status, $currency, $finishedProductId, $createdBy);
        $statusSummary = $this->getProductionStatusSummary($startDate, $endDate, $currency, $finishedProductId, $createdBy);
        $productionByUser = $this->getProductionSummaryByUser($startDate, $endDate, $status, $currency, $finishedProductId);
        $finishedProductSummary = $this->getFinishedProductProductionSummary($startDate, $endDate, $status, $currency, $finishedProductId, $createdBy);
        $productionTrend = $this->getProductionTrend($startDate, $endDate, $currency, $status, $finishedProductId, $createdBy);

        // Get damage/waste from adjustments
        $adjustmentSummary = $this->getAdjustmentSummary($startDate, $endDate, $currency);
        $damageLRD = $adjustmentSummary['LRD']['Damage'] ?? 0;
        $damageUSD = $adjustmentSummary['USD']['Damage'] ?? 0;
        $theftLRD = $adjustmentSummary['LRD']['Theft'] ?? 0;
        $theftUSD = $adjustmentSummary['USD']['Theft'] ?? 0;

        // Calculate material costs
        $totalMaterialCostLRD = $costSummary['total_cost_lrd'];
        $totalMaterialCostUSD = $costSummary['total_cost_usd'];

        // Calculate net material cost after damage
        $netMaterialCostLRD = $totalMaterialCostLRD - $damageLRD;
        $netMaterialCostUSD = $totalMaterialCostUSD - $damageUSD;

        // Calculate damage percentage
        $damagePercentageLRD = $totalMaterialCostLRD > 0 ? ($damageLRD / $totalMaterialCostLRD) * 100 : 0;
        $damagePercentageUSD = $totalMaterialCostUSD > 0 ? ($damageUSD / $totalMaterialCostUSD) * 100 : 0;

        // Calculate efficiency (percentage of material that was used successfully)
        $efficiencyLRD = 100 - $damagePercentageLRD;
        $efficiencyUSD = 100 - $damagePercentageUSD;

        // Get quantity produced
        $totalQuantityProduced = 0;
        foreach ($jobs as $job) {
            $totalQuantityProduced += (float) ($job['quantity_produced'] ?? 0);
        }

        // Calculate cost per unit
        $costPerUnitLRD = $totalQuantityProduced > 0 ? $netMaterialCostLRD / $totalQuantityProduced : 0;
        $costPerUnitUSD = $totalQuantityProduced > 0 ? $netMaterialCostUSD / $totalQuantityProduced : 0;

        $data = [
            'title' => 'Production Reports',
            'startDate' => $startDate,
            'endDate' => $endDate,
            'selectedPeriod' => $period,
            'selectedStatus' => $status,
            'selectedCurrency' => $currency,
            'selectedFinishedProduct' => $finishedProductId,
            'selectedUser' => $createdBy,
            'jobs' => $jobs,
            'materialUsage' => $materialUsage,
            'costSummary' => $costSummary,
            'statusSummary' => $statusSummary,
            'productionByUser' => $productionByUser,
            'finishedProductSummary' => $finishedProductSummary,
            'productionTrend' => $productionTrend,
            // Material cost data
            'totalMaterialCostLRD' => $totalMaterialCostLRD,
            'totalMaterialCostUSD' => $totalMaterialCostUSD,
            'damageLRD' => $damageLRD,
            'damageUSD' => $damageUSD,
            'theftLRD' => $theftLRD,
            'theftUSD' => $theftUSD,
            'netMaterialCostLRD' => $netMaterialCostLRD,
            'netMaterialCostUSD' => $netMaterialCostUSD,
            'damagePercentageLRD' => $damagePercentageLRD,
            'damagePercentageUSD' => $damagePercentageUSD,
            'efficiencyLRD' => $efficiencyLRD,
            'efficiencyUSD' => $efficiencyUSD,
            // Production metrics
            'totalQuantityProduced' => $totalQuantityProduced,
            'costPerUnitLRD' => $costPerUnitLRD,
            'costPerUnitUSD' => $costPerUnitUSD,
            'products' => $this->productModel->findAll(),
            'users' => $this->userModel->findAll(),
            'activePage' => 'reports',
            'activeSubPage' => 'production'
        ];

        return view('reports/production', $data);
    }

    /**
     * Export Production Report
     */
    public function exportProduction()
    {
        $type = $this->request->getGet('type');
        $period = $this->request->getGet('period');
        [$startDate, $endDate] = $this->resolveProductionReportPeriod($period);
        $status = $this->request->getGet('status');
        $currency = $this->request->getGet('currency');
        $finishedProductId = $this->request->getGet('finished_product_id');
        $createdBy = $this->request->getGet('created_by');

        $jobs = $this->getProductionJobsDetailed($startDate, $endDate, $status, $currency, $finishedProductId, $createdBy);
        $costSummary = $this->getProductionCostSummary($startDate, $endDate, $status, $currency, $finishedProductId, $createdBy);

        $data = [
            'startDate' => $startDate,
            'endDate' => $endDate,
            'jobs' => $jobs,
            'costSummary' => $costSummary,
            'date' => date('Y-m-d H:i:s'),
            'business_name' => $this->settingsModel->get('business_name', 'Innovative Graphics')
        ];

        if ($type === 'pdf') {
            return $this->exportPDF('reports/production_pdf', $data, 'production_report.pdf');
        }

        $rows = [
            ['Job Reference', 'Date', 'Job Name', 'Finished Product', 'Qty Produced', 'Status', 'Currency', 'Material Cost', 'Created By'],
        ];
        foreach ($jobs as $job) {
            $rows[] = [
                $job['job_reference'],
                $job['production_date'],
                $job['job_name'],
                $job['finished_product_name'] ?? 'Consumables only',
                number_format((float) ($job['quantity_produced'] ?? 0), 2),
                $job['status'],
                $job['currency'],
                number_format((float) ($job['total_material_cost'] ?? 0), 2),
                $job['created_by_name'] ?? 'Unknown',
            ];
        }

        return $this->exportExcel($rows, 'Production Report', 'production_report.xlsx');
    }

    /**
     * Stock Movement Report
     */
    public function stockMovement()
    {
        $startDate = $this->request->getGet('start_date') ?: date('Y-m-01');
        $endDate = $this->request->getGet('end_date') ?: date('Y-m-t');
        $productId = $this->request->getGet('product_id');

        $movements = $this->stockMovementModel->getByDateRange($startDate, $endDate);

        if ($productId) {
            $movements = array_filter($movements, function ($m) use ($productId) {
                return $m['product_id'] == $productId;
            });
        }

        $data = [
            'title' => 'Stock Movement Report',
            'startDate' => $startDate,
            'endDate' => $endDate,
            'movements' => $movements,
            'products' => $this->productModel->findAll(),
            'selectedProduct' => $productId,
            'activePage' => 'reports'
        ];

        return view('reports/stock_movement', $data);
    }

    /**
     * Export General Report
     */
    public function export()
    {
        $type = $this->request->getPost('type');
        $reportType = $this->request->getPost('report_type');
        $data = $this->request->getPost('data');

        switch ($reportType) {
            case 'inventory':
                return $this->exportInventory();
            case 'sales':
                return $this->exportSales();
            case 'financial':
                return $this->exportFinancial();
            default:
                return redirect()->back()->with('error', 'Invalid report type');
        }
    }

    // ==================== Helper Methods ====================

    /**
     * Get Daily Sales
     */
    private function getDailySales($startDate, $endDate, $currency = null)
    {
        $db = \Config\Database::connect();

        if (!$startDate || !$endDate) {
            [$startDate, $endDate] = $this->getSalesDateBounds();
            if (!$startDate || !$endDate) {
                return [];
            }
        }

        $builder = $db->table('sales');
        $builder->select('sale_date as date, 
                         SUM(CASE WHEN currency = "LRD" THEN total_amount ELSE 0 END) as total_lrd,
                         SUM(CASE WHEN currency = "USD" THEN total_amount ELSE 0 END) as total_usd,
                         COUNT(CASE WHEN currency = "LRD" THEN 1 END) as count_lrd,
                         COUNT(CASE WHEN currency = "USD" THEN 1 END) as count_usd,
                         SUM(total_amount) as total_combined,
                         COUNT(id) as count_combined');
        $this->applySaleDateRange($builder, $startDate, $endDate);

        if ($currency) {
            $builder->where('currency', $currency);
        }

        $builder->groupBy('sale_date');
        $builder->orderBy('sale_date', 'ASC');

        $rows = $builder->get()->getResultArray();
        $resultsByDate = [];
        foreach ($rows as $row) {
            $resultsByDate[$row['date']] = $row;
        }

        $results = [];
        $currentDate = strtotime($startDate);
        $lastDate = strtotime($endDate);

        while ($currentDate <= $lastDate) {
            $date = date('Y-m-d', $currentDate);
            $row = $resultsByDate[$date] ?? [
                'date' => $date,
                'total_lrd' => 0,
                'total_usd' => 0,
                'count_lrd' => 0,
                'count_usd' => 0,
                'total_combined' => 0,
                'count_combined' => 0,
            ];

            if ($currency) {
                $row['total'] = $row['total_combined'];
                $row['count'] = $row['count_combined'];
                $row['currency'] = $currency;
            }

            $results[] = $row;
            $currentDate = strtotime('+1 day', $currentDate);
        }

        return $results;
    }

    /**
     * Get Top Selling Products
     */
    private function getTopProducts($startDate, $endDate, $productId = null, $currency = null, $limit = 10)
    {
        $db = \Config\Database::connect();

        $builder = $db->table('sale_items');
        $builder->select('products.id as product_id, products.product_name, products.sku, SUM(sale_items.quantity) as total_quantity, SUM(sale_items.total_price) as total_revenue');
        $builder->join('products', 'products.id = sale_items.product_id');
        $builder->join('sales', 'sales.id = sale_items.sale_id');
        $this->applySaleDateRange($builder, $startDate, $endDate);

        if ($productId) {
            $builder->where('sale_items.product_id', $productId);
        }

        if ($currency) {
            $builder->where('sales.currency', $currency);
        }

        $builder->groupBy('sale_items.product_id');
        $builder->orderBy('total_quantity', 'DESC');
        $builder->limit($limit);

        return $builder->get()->getResultArray();
    }

    /**
     * Get Sales by Payment Method
     */
    private function getSalesByPaymentMethod($startDate, $endDate, $currency = null)
    {
        $db = \Config\Database::connect();

        $builder = $db->table('sales');
        $builder->select('payment_method, COUNT(*) as count, SUM(total_amount) as total');
        $this->applySaleDateRange($builder, $startDate, $endDate);

        if ($currency) {
            $builder->where('currency', $currency);
        }

        $builder->groupBy('payment_method');

        return $builder->get()->getResultArray();
    }

    /**
     * Get Sales Log with Customer and Seller Info
     */
    private function getSalesLog($startDate, $endDate, $currency = null, $productId = null)
    {
        $db = \Config\Database::connect();

        $builder = $db->table('sales');
        $builder->select('sales.*, customers.customer_name, users.full_name as seller_name, 
                         (SELECT COUNT(*) FROM sale_items WHERE sale_items.sale_id = sales.id) as items_count');
        $builder->join('customers', 'customers.id = sales.customer_id', 'left');
        $builder->join('users', 'users.id = sales.created_by', 'left');
        $this->applySaleDateRange($builder, $startDate, $endDate);

        if ($currency) {
            $builder->where('sales.currency', $currency);
        }

        if ($productId) {
            $builder->join('sale_items', 'sale_items.sale_id = sales.id');
            $builder->where('sale_items.product_id', $productId);
            $builder->groupBy('sales.id');
        }

        $builder->orderBy('sale_date', 'DESC');
        $builder->orderBy('sales.created_at', 'DESC');

        return $builder->get()->getResultArray();
    }

    /**
     * Get Customer Purchase Summary
     */
    private function getCustomerPurchaseSummary($startDate, $endDate)
    {
        $db = \Config\Database::connect();

        $builder = $db->table('sales');
        $builder->select('customers.customer_name, customers.id as customer_id, 
                         SUM(CASE WHEN sales.currency = "LRD" THEN sales.total_amount ELSE 0 END) as total_lrd,
                         SUM(CASE WHEN sales.currency = "USD" THEN sales.total_amount ELSE 0 END) as total_usd,
                         COUNT(sales.id) as total_transactions,
                         SUM(CASE WHEN sales.currency = "LRD" THEN sales.total_amount ELSE 0 END) + SUM(CASE WHEN sales.currency = "USD" THEN sales.total_amount ELSE 0 END) as total_combined');
        $builder->join('customers', 'customers.id = sales.customer_id', 'left');
        $this->applySaleDateRange($builder, $startDate, $endDate);
        $builder->where('sales.customer_id IS NOT NULL');
        $builder->groupBy('sales.customer_id');
        $builder->orderBy('total_combined', 'DESC', false);

        return $builder->get()->getResultArray();
    }

    /**
     * Get Seller Sales Summary
     */
    private function getSellerSalesSummary($startDate, $endDate)
    {
        $db = \Config\Database::connect();

        $builder = $db->table('sales');
        $builder->select('users.full_name, users.id as user_id,
                         SUM(CASE WHEN sales.currency = "LRD" THEN sales.total_amount ELSE 0 END) as total_lrd,
                         SUM(CASE WHEN sales.currency = "USD" THEN sales.total_amount ELSE 0 END) as total_usd,
                         COUNT(sales.id) as total_transactions,
                         SUM(CASE WHEN sales.currency = "LRD" THEN sales.total_amount ELSE 0 END) + SUM(CASE WHEN sales.currency = "USD" THEN sales.total_amount ELSE 0 END) as total_combined');
        $builder->join('users', 'users.id = sales.created_by', 'left');
        $this->applySaleDateRange($builder, $startDate, $endDate);
        $builder->groupBy('sales.created_by');
        $builder->orderBy('total_combined', 'DESC', false);

        return $builder->get()->getResultArray();
    }

    /**
     * Get a sales aggregate using a fresh builder to avoid model state leaking
     */
    private function getSalesAggregate($startDate, $endDate, $column, $currency = null)
    {
        $allowedColumns = ['tax', 'discount'];
        if (!in_array($column, $allowedColumns, true)) {
            return 0;
        }

        $builder = $this->saleModel->builder();
        $builder->select("SUM({$column}) as total", false);
        $builder->where('deleted_at', null);
        $this->applySaleDateRange($builder, $startDate, $endDate);

        if ($currency) {
            $builder->where('currency', $currency);
        }

        $result = $builder->get()->getRowArray();

        return (float) ($result['total'] ?? 0);
    }

    /**
     * Resolve sales report period filters
     */
    private function resolveSalesReportPeriod($period = null)
    {
        $today = date('Y-m-d');
        $requestedStartDate = trim((string) $this->request->getGet('start_date'));
        $requestedEndDate = trim((string) $this->request->getGet('end_date'));

        if ($period === 'last_7_days') {
            return [date('Y-m-d', strtotime('-6 days')), $today, $period];
        }

        if ($period === 'last_month') {
            return [date('Y-m-01', strtotime('first day of last month')), date('Y-m-t', strtotime('last day of last month')), $period];
        }

        if ($period === 'last_year') {
            return [date('Y-01-01', strtotime('last year')), date('Y-12-31', strtotime('last year')), $period];
        }

        if ($period === 'custom' || $requestedStartDate || $requestedEndDate) {
            return [$requestedStartDate ?: null, $requestedEndDate ?: null, 'custom'];
        }

        return [null, null, 'all'];
    }

    /**
     * Apply optional sales date filters to a builder
     */
    private function applySaleDateRange($builder, $startDate, $endDate)
    {
        if ($startDate) {
            $builder->where('sale_date >=', $startDate);
        }

        if ($endDate) {
            $builder->where('sale_date <=', $endDate);
        }

        return $builder;
    }

    /**
     * Get earliest and latest sales dates for all-log reports
     */
    private function getSalesDateBounds()
    {
        $builder = $this->saleModel->builder();
        $builder->select('MIN(sale_date) as start_date, MAX(sale_date) as end_date', false);
        $builder->where('deleted_at', null);
        $result = $builder->get()->getRowArray();

        return [
            $result['start_date'] ?? null,
            $result['end_date'] ?? null,
        ];
    }

    /**
     * Calculate Cost of Goods Sold (COGS)
     */
    private function calculateCOGS($startDate, $endDate)
    {
        $db = \Config\Database::connect();

        $builder = $db->table('sale_items');
        $builder->select('sale_items.product_id, sale_items.quantity, products.purchase_price, sales.currency, sales.exchange_rate');
        $builder->join('products', 'products.id = sale_items.product_id');
        $builder->join('sales', 'sales.id = sale_items.sale_id');
        $this->applyOptionalDateRange($builder, 'sale_date', $startDate, $endDate);

        $sales = $builder->get()->getResultArray();

        $cogs = ['LRD' => 0, 'USD' => 0];
        foreach ($sales as $sale) {
            $cost = (float) $sale['quantity'] * (float) $sale['purchase_price'];

            if (($sale['currency'] ?? 'LRD') === 'USD') {
                $exchangeRate = (float) ($sale['exchange_rate'] ?? 0);
                $cogs['USD'] += $exchangeRate > 0 ? $cost / $exchangeRate : 0;
            } else {
                $cogs['LRD'] += $cost;
            }
        }

        return $cogs;
    }

    /**
     * Resolve financial report period filters
     */
    private function resolveFinancialReportPeriod($period = null)
    {
        $today = date('Y-m-d');
        $requestedStartDate = trim((string) $this->request->getGet('start_date'));
        $requestedEndDate = trim((string) $this->request->getGet('end_date'));

        if ($period === 'last_week' || $period === 'last_7_days') {
            return [date('Y-m-d', strtotime('-6 days')), $today, 'last_week'];
        }

        if ($period === 'last_month') {
            return [date('Y-m-01', strtotime('first day of last month')), date('Y-m-t', strtotime('last day of last month')), 'last_month'];
        }

        if ($period === 'last_year') {
            return [date('Y-01-01', strtotime('last year')), date('Y-12-31', strtotime('last year')), 'last_year'];
        }

        if ($period === 'custom' || $requestedStartDate || $requestedEndDate) {
            return [$requestedStartDate ?: null, $requestedEndDate ?: null, 'custom'];
        }

        return [null, null, 'all'];
    }

    /**
     * Apply an optional date range to a builder
     */
    private function applyOptionalDateRange($builder, $field, $startDate, $endDate)
    {
        if ($startDate) {
            $builder->where($field . ' >=', $startDate);
        }

        if ($endDate) {
            $builder->where($field . ' <=', $endDate);
        }

        return $builder;
    }

    /**
     * Get financial date bounds using both sales and expenses
     */
    private function getFinancialDateBounds()
    {
        $db = \Config\Database::connect();

        $salesBounds = $db->table('sales')
            ->select('MIN(sale_date) as start_date, MAX(sale_date) as end_date', false)
            ->where('deleted_at', null)
            ->get()
            ->getRowArray();

        $expenseBounds = $db->table('expenses')
            ->select('MIN(expense_date) as start_date, MAX(expense_date) as end_date', false)
            ->where('deleted_at', null)
            ->get()
            ->getRowArray();

        $startCandidates = array_filter([$salesBounds['start_date'] ?? null, $expenseBounds['start_date'] ?? null]);
        $endCandidates = array_filter([$salesBounds['end_date'] ?? null, $expenseBounds['end_date'] ?? null]);

        return [
            !empty($startCandidates) ? min($startCandidates) : null,
            !empty($endCandidates) ? max($endCandidates) : null,
        ];
    }

    /**
     * Get trend data for the financial report
     */
    private function getFinancialTrend($startDate, $endDate, $currency = null)
    {
        if (!$startDate || !$endDate) {
            [$startDate, $endDate] = $this->getFinancialDateBounds();
        }

        if (!$startDate || !$endDate) {
            return ['mode' => 'empty', 'labels' => [], 'datasets' => []];
        }

        $start = new \DateTime($startDate);
        $end = new \DateTime($endDate);
        $daySpan = (int) $start->diff($end)->format('%a') + 1;
        $useMonthlyBuckets = $daySpan > 45;

        if ($currency) {
            return $this->buildSingleCurrencyFinancialTrend($startDate, $endDate, $currency, $useMonthlyBuckets);
        }

        return [
            'mode' => 'summary',
            'labels' => ['Revenue', 'Expenses', 'Net Profit'],
            'datasets' => [
                [
                    'label' => 'LRD',
                    'backgroundColor' => 'rgba(13, 110, 253, 0.75)',
                    'borderColor' => '#0d6efd',
                    'data' => [
                        round($this->saleModel->getTotalByDateRange($startDate, $endDate, 'LRD'), 2),
                        round($this->expenseModel->getTotalByDateRange($startDate, $endDate, 'LRD'), 2),
                        round(($this->saleModel->getTotalByDateRange($startDate, $endDate, 'LRD') - $this->calculateCOGS($startDate, $endDate)['LRD']) - $this->expenseModel->getTotalByDateRange($startDate, $endDate, 'LRD'), 2),
                    ],
                ],
                [
                    'label' => 'USD',
                    'backgroundColor' => 'rgba(25, 135, 84, 0.75)',
                    'borderColor' => '#198754',
                    'data' => [
                        round($this->saleModel->getTotalByDateRange($startDate, $endDate, 'USD'), 2),
                        round($this->expenseModel->getTotalByDateRange($startDate, $endDate, 'USD'), 2),
                        round(($this->saleModel->getTotalByDateRange($startDate, $endDate, 'USD') - $this->calculateCOGS($startDate, $endDate)['USD']) - $this->expenseModel->getTotalByDateRange($startDate, $endDate, 'USD'), 2),
                    ],
                ],
            ],
        ];
    }

    private function buildSingleCurrencyFinancialTrend($startDate, $endDate, $currency, $useMonthlyBuckets)
    {
        $db = \Config\Database::connect();
        $salesRows = [];
        $expenseRows = [];
        $labels = [];

        if ($useMonthlyBuckets) {
            $salesRows = $db->table('sales')
                ->select("DATE_FORMAT(sale_date, '%Y-%m') as bucket, SUM(total_amount) as total", false)
                ->where('deleted_at', null)
                ->where('currency', $currency)
                ->where('sale_date >=', $startDate)
                ->where('sale_date <=', $endDate)
                ->groupBy("DATE_FORMAT(sale_date, '%Y-%m')", false)
                ->orderBy('bucket', 'ASC')
                ->get()
                ->getResultArray();

            $expenseRows = $db->table('expenses')
                ->select("DATE_FORMAT(expense_date, '%Y-%m') as bucket, SUM(amount) as total", false)
                ->where('deleted_at', null)
                ->where('currency', $currency)
                ->where('expense_date >=', $startDate)
                ->where('expense_date <=', $endDate)
                ->groupBy("DATE_FORMAT(expense_date, '%Y-%m')", false)
                ->orderBy('bucket', 'ASC')
                ->get()
                ->getResultArray();

            $cursor = new \DateTime(date('Y-m-01', strtotime($startDate)));
            $last = new \DateTime(date('Y-m-01', strtotime($endDate)));
            while ($cursor <= $last) {
                $labels[] = $cursor->format('Y-m');
                $cursor->modify('+1 month');
            }
        } else {
            $salesRows = $db->table('sales')
                ->select('sale_date as bucket, SUM(total_amount) as total', false)
                ->where('deleted_at', null)
                ->where('currency', $currency)
                ->where('sale_date >=', $startDate)
                ->where('sale_date <=', $endDate)
                ->groupBy('sale_date')
                ->orderBy('sale_date', 'ASC')
                ->get()
                ->getResultArray();

            $expenseRows = $db->table('expenses')
                ->select('expense_date as bucket, SUM(amount) as total', false)
                ->where('deleted_at', null)
                ->where('currency', $currency)
                ->where('expense_date >=', $startDate)
                ->where('expense_date <=', $endDate)
                ->groupBy('expense_date')
                ->orderBy('expense_date', 'ASC')
                ->get()
                ->getResultArray();

            $cursor = new \DateTime($startDate);
            $last = new \DateTime($endDate);
            while ($cursor <= $last) {
                $labels[] = $cursor->format('Y-m-d');
                $cursor->modify('+1 day');
            }
        }

        $salesMap = [];
        foreach ($salesRows as $row) {
            $salesMap[$row['bucket']] = (float) $row['total'];
        }

        $expenseMap = [];
        foreach ($expenseRows as $row) {
            $expenseMap[$row['bucket']] = (float) $row['total'];
        }

        $salesData = [];
        $expenseData = [];
        $netData = [];
        foreach ($labels as $label) {
            $revenue = $salesMap[$label] ?? 0;
            $expense = $expenseMap[$label] ?? 0;
            $salesData[] = round($revenue, 2);
            $expenseData[] = round($expense, 2);
            $netData[] = round($revenue - $expense, 2);
        }

        return [
            'mode' => 'trend',
            'bucket' => $useMonthlyBuckets ? 'month' : 'day',
            'labels' => $labels,
            'currency' => $currency,
            'datasets' => [
                [
                    'label' => 'Revenue',
                    'borderColor' => '#0d6efd',
                    'backgroundColor' => 'rgba(13, 110, 253, 0.12)',
                    'data' => $salesData,
                    'fill' => false,
                ],
                [
                    'label' => 'Expenses',
                    'borderColor' => '#dc3545',
                    'backgroundColor' => 'rgba(220, 53, 69, 0.12)',
                    'data' => $expenseData,
                    'fill' => false,
                ],
                [
                    'label' => 'Net',
                    'borderColor' => '#198754',
                    'backgroundColor' => 'rgba(25, 135, 84, 0.12)',
                    'data' => $netData,
                    'fill' => false,
                ],
            ],
        ];
    }

    /**
     * Get Material Usage Summary
     */
    private function getMaterialUsageSummary($startDate, $endDate)
    {
        $db = \Config\Database::connect();

        $builder = $db->table('production_materials');
        $builder->select('products.product_name, SUM(production_materials.quantity_used) as total_quantity, SUM(production_materials.total_cost) as total_cost');
        $builder->join('products', 'products.id = production_materials.product_id');
        $builder->join('production_jobs', 'production_jobs.id = production_materials.production_job_id');
        $builder->where('production_date >=', $startDate);
        $builder->where('production_date <=', $endDate);
        $builder->groupBy('product_id');
        $builder->orderBy('total_quantity', 'DESC');

        return $builder->get()->getResultArray();
    }

    /**
     * Resolve production report period filters
     */
    private function resolveProductionReportPeriod($period = null)
    {
        $today = date('Y-m-d');
        $requestedStartDate = trim((string) $this->request->getGet('start_date'));
        $requestedEndDate = trim((string) $this->request->getGet('end_date'));

        if ($period === 'last_week' || $period === 'last_7_days') {
            return [date('Y-m-d', strtotime('-6 days')), $today, 'last_week'];
        }

        if ($period === 'last_month') {
            return [date('Y-m-01', strtotime('first day of last month')), date('Y-m-t', strtotime('last day of last month')), 'last_month'];
        }

        if ($period === 'last_year') {
            return [date('Y-01-01', strtotime('last year')), date('Y-12-31', strtotime('last year')), 'last_year'];
        }

        if ($period === 'custom' || $requestedStartDate || $requestedEndDate) {
            return [$requestedStartDate ?: null, $requestedEndDate ?: null, 'custom'];
        }

        return [null, null, 'all'];
    }

    /**
     * Get detailed production jobs for reporting (FIXED - without finished_product_id join)
     */
    private function getProductionJobsDetailed($startDate = null, $endDate = null, $status = null, $currency = null, $finishedProductId = null, $createdBy = null)
    {
        $db = \Config\Database::connect();

        $builder = $db->table('production_jobs');
        $builder->select('production_jobs.*, creator.full_name as created_by_name, updater.full_name as updated_by_name');
        $builder->join('users as creator', 'creator.id = production_jobs.created_by', 'left');
        $builder->join('users as updater', 'updater.id = production_jobs.updated_by', 'left');
        $builder->where('production_jobs.deleted_at', null);
        $this->applyOptionalDateRange($builder, 'production_jobs.production_date', $startDate, $endDate);

        if ($status) {
            $builder->where('production_jobs.status', $status);
        }

        if ($currency) {
            $builder->where('production_jobs.currency', $currency);
        }

        if ($createdBy) {
            $builder->where('production_jobs.created_by', $createdBy);
        }

        return $builder->orderBy('production_jobs.production_date', 'DESC')
            ->orderBy('production_jobs.created_at', 'DESC')
            ->get()
            ->getResultArray();
    }

    /**
     * Get production material usage report (FIXED)
     */
    private function getProductionMaterialUsageReport($startDate = null, $endDate = null, $status = null, $currency = null, $finishedProductId = null, $createdBy = null)
    {
        $db = \Config\Database::connect();

        $builder = $db->table('production_materials');
        $builder->select('production_materials.product_id, products.product_name, products.sku, production_jobs.currency, SUM(production_materials.quantity_used) as total_quantity, SUM(production_materials.total_cost) as total_cost, COUNT(DISTINCT production_jobs.id) as jobs_count');
        $builder->join('production_jobs', 'production_jobs.id = production_materials.production_job_id');
        $builder->join('products', 'products.id = production_materials.product_id');
        $builder->where('production_jobs.deleted_at', null);
        $this->applyOptionalDateRange($builder, 'production_jobs.production_date', $startDate, $endDate);

        if ($status) {
            $builder->where('production_jobs.status', $status);
        }

        if ($currency) {
            $builder->where('production_jobs.currency', $currency);
        }

        if ($createdBy) {
            $builder->where('production_jobs.created_by', $createdBy);
        }

        $builder->groupBy('production_materials.product_id, production_jobs.currency');
        $builder->orderBy('total_cost', 'DESC');

        return $builder->get()->getResultArray();
    }

    /**
     * Get production cost summary (FIXED)
     */
    private function getProductionCostSummary($startDate = null, $endDate = null, $status = null, $currency = null, $finishedProductId = null, $createdBy = null)
    {
        $jobs = $this->getProductionJobsDetailed($startDate, $endDate, $status, $currency, $finishedProductId, $createdBy);

        $summary = [
            'total_cost_lrd' => 0,
            'total_cost_usd' => 0,
            'job_count' => count($jobs),
            'completed_count' => 0,
            'draft_count' => 0,
            'cancelled_count' => 0,
            'quantity_produced' => 0,
        ];

        foreach ($jobs as $job) {
            $jobCurrency = $job['currency'] ?? 'LRD';
            $jobCost = (float) ($job['total_material_cost'] ?? 0);
            $summary['quantity_produced'] += (float) ($job['quantity_produced'] ?? 0);

            if ($jobCurrency === 'USD') {
                $summary['total_cost_usd'] += $jobCost;
            } else {
                $summary['total_cost_lrd'] += $jobCost;
            }

            if (($job['status'] ?? '') === 'Completed') {
                $summary['completed_count']++;
            } elseif (($job['status'] ?? '') === 'Cancelled') {
                $summary['cancelled_count']++;
            } else {
                $summary['draft_count']++;
            }
        }

        return $summary;
    }

    /**
     * Get production status summary (FIXED)
     */
    private function getProductionStatusSummary($startDate = null, $endDate = null, $currency = null, $finishedProductId = null, $createdBy = null)
    {
        $db = \Config\Database::connect();
        $builder = $db->table('production_jobs');
        $builder->select('status, COUNT(*) as job_count');
        $builder->where('deleted_at', null);
        $this->applyOptionalDateRange($builder, 'production_date', $startDate, $endDate);

        if ($currency) {
            $builder->where('currency', $currency);
        }

        if ($createdBy) {
            $builder->where('created_by', $createdBy);
        }

        $builder->groupBy('status');
        $rows = $builder->get()->getResultArray();

        $summary = ['Draft' => 0, 'Completed' => 0, 'Cancelled' => 0];
        foreach ($rows as $row) {
            $summary[$row['status']] = (int) $row['job_count'];
        }

        return $summary;
    }

    /**
     * Get production summary by user (FIXED)
     */
    private function getProductionSummaryByUser($startDate = null, $endDate = null, $status = null, $currency = null, $finishedProductId = null)
    {
        $db = \Config\Database::connect();
        $builder = $db->table('production_jobs');
        $builder->select('users.id as user_id, users.full_name, production_jobs.currency, COUNT(*) as job_count, SUM(production_jobs.total_material_cost) as total_cost, SUM(production_jobs.quantity_produced) as total_quantity');
        $builder->join('users', 'users.id = production_jobs.created_by', 'left');
        $builder->where('production_jobs.deleted_at', null);
        $this->applyOptionalDateRange($builder, 'production_jobs.production_date', $startDate, $endDate);

        if ($status) {
            $builder->where('production_jobs.status', $status);
        }

        if ($currency) {
            $builder->where('production_jobs.currency', $currency);
        }

        $builder->groupBy('production_jobs.created_by, production_jobs.currency');
        $builder->orderBy('job_count', 'DESC');

        return $builder->get()->getResultArray();
    }

    /**
     * Get finished product production summary (FIXED - returns empty array if column doesn't exist)
     */
    private function getFinishedProductProductionSummary($startDate = null, $endDate = null, $status = null, $currency = null, $finishedProductId = null, $createdBy = null)
    {
        $db = \Config\Database::connect();

        // Check if finished_product_id column exists
        $fields = $db->getFieldNames('production_jobs');
        if (!in_array('finished_product_id', $fields)) {
            return []; // Return empty array if column doesn't exist
        }

        $builder = $db->table('production_jobs');
        $builder->select('products.id as product_id, products.product_name, production_jobs.currency, COUNT(*) as job_count, SUM(production_jobs.quantity_produced) as total_quantity, SUM(production_jobs.total_material_cost) as total_cost');
        $builder->join('products', 'products.id = production_jobs.finished_product_id', 'left');
        $builder->where('production_jobs.deleted_at', null);
        $builder->where('production_jobs.finished_product_id IS NOT NULL');
        $this->applyOptionalDateRange($builder, 'production_jobs.production_date', $startDate, $endDate);

        if ($status) {
            $builder->where('production_jobs.status', $status);
        }

        if ($currency) {
            $builder->where('production_jobs.currency', $currency);
        }

        if ($finishedProductId) {
            $builder->where('production_jobs.finished_product_id', $finishedProductId);
        }

        if ($createdBy) {
            $builder->where('production_jobs.created_by', $createdBy);
        }

        $builder->groupBy('production_jobs.finished_product_id, production_jobs.currency');
        $builder->orderBy('total_quantity', 'DESC');

        return $builder->get()->getResultArray();
    }

    /**
     * Get production trend (FIXED)
     */
    private function getProductionTrend($startDate = null, $endDate = null, $currency = null, $status = null, $finishedProductId = null, $createdBy = null)
    {
        if (!$startDate || !$endDate) {
            $bounds = $this->getProductionDateBounds();
            $startDate = $startDate ?: $bounds[0];
            $endDate = $endDate ?: $bounds[1];
        }

        if (!$startDate || !$endDate) {
            return ['labels' => [], 'datasets' => []];
        }

        $db = \Config\Database::connect();
        $builder = $db->table('production_jobs');
        $builder->select('production_date as bucket,
            COUNT(*) as total_jobs,
            SUM(CASE WHEN status = "Completed" THEN 1 ELSE 0 END) as completed_jobs,
            SUM(CASE WHEN status = "Draft" THEN 1 ELSE 0 END) as draft_jobs,
            SUM(CASE WHEN status = "Cancelled" THEN 1 ELSE 0 END) as cancelled_jobs');
        $builder->where('deleted_at', null);
        $builder->where('production_date >=', $startDate);
        $builder->where('production_date <=', $endDate);

        if ($currency) {
            $builder->where('currency', $currency);
        }

        if ($status) {
            $builder->where('status', $status);
        }

        if ($createdBy) {
            $builder->where('created_by', $createdBy);
        }

        $builder->groupBy('production_date');
        $builder->orderBy('production_date', 'ASC');
        $rows = $builder->get()->getResultArray();
        $rowMap = [];
        foreach ($rows as $row) {
            $rowMap[$row['bucket']] = $row;
        }

        $labels = [];
        $total = [];
        $completed = [];
        $draft = [];
        $cancelled = [];
        $cursor = strtotime($startDate);
        $last = strtotime($endDate);
        while ($cursor <= $last) {
            $date = date('Y-m-d', $cursor);
            $labels[] = $date;
            $row = $rowMap[$date] ?? ['total_jobs' => 0, 'completed_jobs' => 0, 'draft_jobs' => 0, 'cancelled_jobs' => 0];
            $total[] = (int) $row['total_jobs'];
            $completed[] = (int) $row['completed_jobs'];
            $draft[] = (int) $row['draft_jobs'];
            $cancelled[] = (int) $row['cancelled_jobs'];
            $cursor = strtotime('+1 day', $cursor);
        }

        return [
            'labels' => $labels,
            'datasets' => [
                ['label' => 'Total Jobs', 'borderColor' => '#0d6efd', 'backgroundColor' => 'rgba(13, 110, 253, 0.12)', 'data' => $total, 'fill' => false],
                ['label' => 'Completed', 'borderColor' => '#198754', 'backgroundColor' => 'rgba(25, 135, 84, 0.12)', 'data' => $completed, 'fill' => false],
                ['label' => 'Draft', 'borderColor' => '#ffc107', 'backgroundColor' => 'rgba(255, 193, 7, 0.12)', 'data' => $draft, 'fill' => false],
                ['label' => 'Cancelled', 'borderColor' => '#dc3545', 'backgroundColor' => 'rgba(220, 53, 69, 0.12)', 'data' => $cancelled, 'fill' => false],
            ],
        ];
    }

    /**
     * Get production date bounds
     */
    private function getProductionDateBounds()
    {
        $builder = $this->productionJobModel->builder();
        $builder->select('MIN(production_date) as start_date, MAX(production_date) as end_date', false);
        $builder->where('deleted_at', null);
        $result = $builder->get()->getRowArray();

        return [
            $result['start_date'] ?? null,
            $result['end_date'] ?? null,
        ];
    }

    /**
     * Build inventory report data
     */
    private function getInventoryReportData($categoryId = null, $stockStatus = null)
    {
        $products = $this->productModel->getAllProductsWithRelations();

        $products = array_map(function ($product) {
            $quantity = (float) ($product['quantity'] ?? 0);
            $minimumStock = (float) ($product['minimum_stock'] ?? 0);
            $purchasePrice = (float) ($product['purchase_price'] ?? 0);
            $sellingPrice = (float) ($product['selling_price'] ?? 0);

            if ($quantity <= 0) {
                $status = 'out';
                $statusLabel = 'Out of Stock';
            } elseif ($quantity <= $minimumStock) {
                $status = 'low';
                $statusLabel = 'Low Stock';
            } else {
                $status = 'normal';
                $statusLabel = 'In Stock';
            }

            $product['stock_status'] = $status;
            $product['stock_status_label'] = $statusLabel;
            $product['stock_value_purchase'] = $quantity * $purchasePrice;
            $product['stock_value_selling'] = $quantity * $sellingPrice;
            $product['potential_margin'] = $product['stock_value_selling'] - $product['stock_value_purchase'];

            return $product;
        }, $products);

        if ($categoryId) {
            $products = array_values(array_filter($products, function ($product) use ($categoryId) {
                return (string) ($product['category_id'] ?? '') === (string) $categoryId;
            }));
        }

        if ($stockStatus) {
            $products = array_values(array_filter($products, function ($product) use ($stockStatus) {
                return ($product['stock_status'] ?? null) === $stockStatus;
            }));
        }

        $summary = [
            'total_products' => count($products),
            'total_stock' => array_sum(array_column($products, 'quantity')),
            'low_stock_count' => count(array_filter($products, fn($product) => ($product['stock_status'] ?? '') === 'low')),
            'out_of_stock_count' => count(array_filter($products, fn($product) => ($product['stock_status'] ?? '') === 'out')),
            'purchase_value' => array_sum(array_column($products, 'stock_value_purchase')),
            'selling_value' => array_sum(array_column($products, 'stock_value_selling')),
            'potential_margin' => array_sum(array_column($products, 'potential_margin')),
        ];

        return [
            'products' => $products,
            'summary' => $summary,
            'categorySummary' => $this->getInventoryCategorySummary($products),
            'recentMovements' => $this->getInventoryRecentMovements($categoryId),
            'date' => date('Y-m-d H:i:s'),
            'business_name' => $this->settingsModel->get('business_name', 'Innovative Graphics'),
            'categoryId' => $categoryId,
            'stockStatus' => $stockStatus,
            'categories' => $this->categoryModel->findAll(),
        ];
    }

    /**
     * Get category summary for inventory
     */
    private function getInventoryCategorySummary(array $products)
    {
        $summary = [];
        foreach ($products as $product) {
            $categoryName = $product['category_name'] ?? 'Uncategorized';
            if (!isset($summary[$categoryName])) {
                $summary[$categoryName] = [
                    'category_name' => $categoryName,
                    'product_count' => 0,
                    'total_stock' => 0,
                    'purchase_value' => 0,
                    'selling_value' => 0,
                ];
            }

            $summary[$categoryName]['product_count']++;
            $summary[$categoryName]['total_stock'] += (float) ($product['quantity'] ?? 0);
            $summary[$categoryName]['purchase_value'] += (float) ($product['stock_value_purchase'] ?? 0);
            $summary[$categoryName]['selling_value'] += (float) ($product['stock_value_selling'] ?? 0);
        }

        $summary = array_values($summary);
        usort($summary, fn($a, $b) => $b['selling_value'] <=> $a['selling_value']);

        return $summary;
    }

    /**
     * Get recent inventory movements
     */
    private function getInventoryRecentMovements($categoryId = null, $limit = 50)
    {
        $db = \Config\Database::connect();
        $builder = $db->table('stock_movements');
        $builder->select('stock_movements.*, products.product_name, products.sku, categories.category_name, users.full_name as created_by_name');
        $builder->join('products', 'products.id = stock_movements.product_id', 'left');
        $builder->join('categories', 'categories.id = products.category_id', 'left');
        $builder->join('users', 'users.id = stock_movements.created_by', 'left');

        if ($categoryId) {
            $builder->where('products.category_id', $categoryId);
        }

        $builder->orderBy('stock_movements.created_at', 'DESC');
        $builder->limit($limit);

        return $builder->get()->getResultArray();
    }

    /**
     * Export to Excel
     */
    private function exportExcel($data, $title, $filename)
    {
        if (!class_exists('\PhpOffice\PhpSpreadsheet\Spreadsheet')) {
            header('Content-Type: text/csv');
            header('Content-Disposition: attachment;filename="' . str_replace('.xlsx', '.csv', $filename) . '"');
            header('Cache-Control: max-age=0');

            $output = fopen('php://output', 'w');
            fputcsv($output, [$title]);
            fputcsv($output, []);

            if (!empty($data) && is_array($data[0] ?? null)) {
                fputcsv($output, array_keys($data[0]));
                foreach ($data as $item) {
                    fputcsv($output, $item);
                }
            } else {
                fputcsv($output, ['No data available']);
            }

            fclose($output);
            exit();
        }

        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        $sheet->setCellValue('A1', $title);
        $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(14);

        $row = 3;
        if (!empty($data) && is_array($data[0] ?? null)) {
            $col = 'A';
            foreach (array_keys($data[0]) as $key) {
                $sheet->setCellValue($col . $row, ucwords(str_replace('_', ' ', $key)));
                $sheet->getStyle($col . $row)->getFont()->setBold(true);
                $col++;
            }
            $row++;

            foreach ($data as $item) {
                $col = 'A';
                foreach ($item as $value) {
                    $sheet->setCellValue($col . $row, $value);
                    $col++;
                }
                $row++;
            }
        } else {
            $sheet->setCellValue('A3', 'No data available');
        }

        foreach (range('A', $sheet->getHighestColumn()) as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . $filename . '"');
        header('Cache-Control: max-age=0');

        $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
        $writer->save('php://output');
        exit();
    }

    /**
     * Export to PDF
     */
    private function exportPDF($view, $data, $filename)
    {
        if (!class_exists('\Dompdf\Dompdf')) {
            return view($view, $data);
        }

        $html = view($view, $data);

        $dompdf = new \Dompdf\Dompdf();
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();
        $dompdf->stream($filename, array('Attachment' => 0));
        exit();
    }

    /**
     * Customer Purchase History
     */
    public function customerHistory($customerId)
    {
        $startDate = $this->request->getGet('start_date') ?: date('Y-m-01');
        $endDate = $this->request->getGet('end_date') ?: date('Y-m-t');

        $customer = $this->customerModel->find($customerId);
        if (!$customer) {
            return redirect()->to('/reports/sales')->with('error', 'Customer not found');
        }

        $sales = $this->saleModel->select('sales.*, users.full_name as seller_name')
            ->join('users', 'users.id = sales.created_by', 'left')
            ->where('customer_id', $customerId)
            ->where('sale_date >=', $startDate)
            ->where('sale_date <=', $endDate)
            ->orderBy('sale_date', 'DESC')
            ->findAll();

        $data = [
            'title' => 'Purchase History - ' . $customer['customer_name'],
            'customer' => $customer,
            'sales' => $sales,
            'startDate' => $startDate,
            'endDate' => $endDate,
            'activePage' => 'reports'
        ];

        return view('reports/customer_history', $data);
    }

    /**
     * Seller Sales History
     */
    public function sellerHistory($userId)
    {
        $startDate = $this->request->getGet('start_date') ?: date('Y-m-01');
        $endDate = $this->request->getGet('end_date') ?: date('Y-m-t');

        $user = $this->userModel->find($userId);
        if (!$user) {
            return redirect()->to('/reports/sales')->with('error', 'User not found');
        }

        $sales = $this->saleModel->select('sales.*, customers.customer_name')
            ->join('customers', 'customers.id = sales.customer_id', 'left')
            ->where('created_by', $userId)
            ->where('sale_date >=', $startDate)
            ->where('sale_date <=', $endDate)
            ->orderBy('sale_date', 'DESC')
            ->findAll();

        $data = [
            'title' => 'Sales History - ' . $user['full_name'],
            'user' => $user,
            'sales' => $sales,
            'startDate' => $startDate,
            'endDate' => $endDate,
            'activePage' => 'reports'
        ];

        return view('reports/seller_history', $data);
    }

    /**
     * Product Sales History
     */
    public function productHistory($productId)
    {
        $startDate = $this->request->getGet('start_date') ?: date('Y-m-01');
        $endDate = $this->request->getGet('end_date') ?: date('Y-m-t');

        $product = $this->productModel->find($productId);
        if (!$product) {
            return redirect()->to('/reports/sales')->with('error', 'Product not found');
        }

        $db = \Config\Database::connect();
        $sales = $db->table('sale_items')
            ->select('sale_items.*, sales.sale_date, sales.currency, sales.payment_method, 
                           customers.customer_name, users.full_name as seller_name')
            ->join('sales', 'sales.id = sale_items.sale_id')
            ->join('customers', 'customers.id = sales.customer_id', 'left')
            ->join('users', 'users.id = sales.created_by', 'left')
            ->where('sale_items.product_id', $productId)
            ->where('sales.sale_date >=', $startDate)
            ->where('sales.sale_date <=', $endDate)
            ->orderBy('sales.sale_date', 'DESC')
            ->get()
            ->getResultArray();

        $data = [
            'title' => 'Sales History - ' . $product['product_name'],
            'product' => $product,
            'sales' => $sales,
            'startDate' => $startDate,
            'endDate' => $endDate,
            'activePage' => 'reports'
        ];

        return view('reports/product_history', $data);
    }

    /**
     * Get adjustment event summary for financial reports (FIXED)
     */
    /**
     * Get adjustment event summary for financial reports
     */
    private function getAdjustmentSummary($startDate, $endDate, $currency = null)
    {
        $db = \Config\Database::connect();

        $builder = $db->table('adjustment_events')
            ->select('event_type, COUNT(*) as event_count, SUM(total_value) as total_value, currency')
            ->where('adjustment_events.deleted_at', null);

        if ($startDate && $endDate) {
            $builder->where('adjustment_events.event_date >=', $startDate);
            $builder->where('adjustment_events.event_date <=', $endDate);
        }

        if ($currency) {
            $builder->where('adjustment_events.currency', $currency);
        }

        $rows = $builder->groupBy(['event_type', 'currency'])->get()->getResultArray();

        $summary = [
            'LRD' => ['Damage' => 0, 'Refund' => 0, 'Theft' => 0, 'Return' => 0, 'Other' => 0, 'total' => 0],
            'USD' => ['Damage' => 0, 'Refund' => 0, 'Theft' => 0, 'Return' => 0, 'Other' => 0, 'total' => 0]
        ];

        foreach ($rows as $row) {
            $cur = $row['currency'] ?? 'LRD';
            if (isset($summary[$cur][$row['event_type']])) {
                $summary[$cur][$row['event_type']] = (float) ($row['total_value'] ?? 0);
                $summary[$cur]['total'] += (float) ($row['total_value'] ?? 0);
            }
        }

        return $summary;
    }

    /**
     * Get adjustment impact on sales (returns/refunds)
     */
    private function getSalesAdjustmentImpact($startDate, $endDate, $currency = null)
    {
        $db = \Config\Database::connect();

        $builder = $db->table('adjustment_events')
            ->select('event_type, SUM(total_value) as total_value, currency')
            ->where('deleted_at', null)
            ->whereIn('event_type', ['Refund', 'Return']);

        if ($startDate && $endDate) {
            $builder->where('event_date >=', $startDate);
            $builder->where('event_date <=', $endDate);
        }

        if ($currency) {
            $builder->where('currency', $currency);
        }

        $builder->groupBy(['event_type', 'currency']);
        $rows = $builder->get()->getResultArray();

        $impact = ['Refund' => 0, 'Return' => 0];
        foreach ($rows as $row) {
            $impact[$row['event_type']] = (float) $row['total_value'];
        }

        return $impact;
    }

    /**
     * Get net sales after returns/refunds
     */
    private function getNetSales($startDate, $endDate, $currency = null)
    {
        $grossSales = $this->saleModel->getTotalByDateRange($startDate, $endDate, $currency);
        $refunds = $this->getSalesAdjustmentImpact($startDate, $endDate, $currency)['Refund'] ?? 0;
        $returns = $this->getSalesAdjustmentImpact($startDate, $endDate, $currency)['Return'] ?? 0;

        // Returns add back to stock but don't affect revenue
        // Refunds reduce revenue
        $netSales = $grossSales - $refunds;

        return [
            'gross_sales' => $grossSales,
            'refunds' => $refunds,
            'returns_value' => $returns,
            'net_sales' => $netSales
        ];
    }


    /**
     * Get inventory adjustment summary (damage, theft, etc.)
     */
    private function getInventoryAdjustmentSummary($startDate, $endDate, $currency = null)
    {
        $db = \Config\Database::connect();

        $builder = $db->table('adjustment_events')
            ->select('event_type, SUM(quantity) as total_quantity, SUM(total_value) as total_value, currency')
            ->where('deleted_at', null)
            ->whereIn('event_type', ['Damage', 'Theft']);

        if ($startDate && $endDate) {
            $builder->where('event_date >=', $startDate);
            $builder->where('event_date <=', $endDate);
        }

        if ($currency) {
            $builder->where('currency', $currency);
        }

        $builder->groupBy(['event_type', 'currency']);
        $rows = $builder->get()->getResultArray();

        $summary = [
            'Damage' => ['quantity' => 0, 'value' => 0],
            'Theft' => ['quantity' => 0, 'value' => 0]
        ];

        foreach ($rows as $row) {
            $summary[$row['event_type']] = [
                'quantity' => (float) $row['total_quantity'],
                'value' => (float) $row['total_value']
            ];
        }

        return $summary;
    }

    /**
     * Adjustments Report - Show all adjustments by category
     */
    public function adjustments()
    {
        $startDate = $this->request->getGet('start_date') ?: date('Y-m-01');
        $endDate = $this->request->getGet('end_date') ?: date('Y-m-t');
        $eventType = $this->request->getGet('event_type');
        $currency = $this->request->getGet('currency');

        $db = \Config\Database::connect();

        $builder = $db->table('adjustment_events')
            ->select('adjustment_events.*, products.product_name, customers.customer_name, 
                  sales.invoice_number, production_jobs.job_reference')
            ->join('products', 'products.id = adjustment_events.product_id', 'left')
            ->join('customers', 'customers.id = adjustment_events.customer_id', 'left')
            ->join('sales', 'sales.id = adjustment_events.related_sale_id', 'left')
            ->join('production_jobs', 'production_jobs.id = adjustment_events.related_production_job_id', 'left')
            ->where('adjustment_events.deleted_at', null)
            ->where('adjustment_events.event_date >=', $startDate)
            ->where('adjustment_events.event_date <=', $endDate);

        if ($eventType) {
            $builder->where('adjustment_events.event_type', $eventType);
        }

        if ($currency) {
            $builder->where('adjustment_events.currency', $currency);
        }

        $adjustments = $builder->orderBy('adjustment_events.event_date', 'DESC')->get()->getResultArray();

        // Get summary by event type
        $summaryByType = $this->getAdjustmentSummary($startDate, $endDate, $currency);

        $data = [
            'title' => 'Adjustments Report',
            'adjustments' => $adjustments,
            'summaryByType' => $summaryByType,
            'startDate' => $startDate,
            'endDate' => $endDate,
            'selectedEventType' => $eventType,
            'selectedCurrency' => $currency,
            'eventTypes' => ['Damage', 'Refund', 'Return', 'Theft', 'Other'],
            'activePage' => 'reports',
            'activeSubPage' => 'adjustments'
        ];

        return view('reports/adjustments', $data);
    }


    /**
     * Export Adjustments Report to Excel
     */
    public function exportAdjustments()
    {
        $startDate = $this->request->getGet('start_date') ?: date('Y-m-01');
        $endDate = $this->request->getGet('end_date') ?: date('Y-m-t');
        $eventType = $this->request->getGet('event_type');
        $currency = $this->request->getGet('currency');

        $db = \Config\Database::connect();

        $builder = $db->table('adjustment_events')
            ->select('adjustment_events.*, products.product_name, customers.customer_name, 
                  sales.invoice_number, production_jobs.job_reference')
            ->join('products', 'products.id = adjustment_events.product_id', 'left')
            ->join('customers', 'customers.id = adjustment_events.customer_id', 'left')
            ->join('sales', 'sales.id = adjustment_events.related_sale_id', 'left')
            ->join('production_jobs', 'production_jobs.id = adjustment_events.related_production_job_id', 'left')
            ->where('adjustment_events.deleted_at', null)
            ->where('adjustment_events.event_date >=', $startDate)
            ->where('adjustment_events.event_date <=', $endDate);

        if ($eventType) {
            $builder->where('adjustment_events.event_type', $eventType);
        }

        if ($currency) {
            $builder->where('adjustment_events.currency', $currency);
        }

        $adjustments = $builder->orderBy('adjustment_events.event_date', 'DESC')->get()->getResultArray();

        // Prepare data for export
        $exportData = [];
        foreach ($adjustments as $adj) {
            $exportData[] = [
                'Date' => date('Y-m-d', strtotime($adj['event_date'])),
                'Product' => $adj['product_name'] ?? 'Unknown',
                'Event Type' => $adj['event_type'],
                'Quantity' => $adj['quantity'],
                'Unit Cost' => $adj['unit_cost'],
                'Total Value' => $adj['total_value'],
                'Currency' => $adj['currency'],
                'Reference' => $adj['reference'] ?? '-',
                'Customer' => $adj['customer_name'] ?? '-',
                'Source' => $adj['invoice_number'] ?? ($adj['job_reference'] ?? '-'),
                'Description' => $adj['description'] ?? '-',
            ];
        }

        return $this->exportExcel($exportData, 'Adjustments Report', 'adjustments_report_' . date('Y-m-d') . '.xlsx');
    }

    /**
     * Profit & Loss Report (Simplified)
     */
    public function profitLoss()
    {
        $period = $this->request->getGet('period');
        [$startDate, $endDate, $period] = $this->resolveFinancialReportPeriod($period);
        $currency = $this->request->getGet('currency');

        // Revenue
        $revenueLRD = $this->saleModel->getTotalByDateRange($startDate, $endDate, 'LRD');
        $revenueUSD = $this->saleModel->getTotalByDateRange($startDate, $endDate, 'USD');

        // Returns & Refunds (Adjustments that affect revenue)
        $adjustmentSummary = $this->getAdjustmentSummary($startDate, $endDate, $currency);

        // Cost of Goods Sold
        $cogs = $this->calculateCOGS($startDate, $endDate);

        // Expenses
        $expensesLRD = $this->expenseModel->getTotalByDateRange($startDate, $endDate, 'LRD');
        $expensesUSD = $this->expenseModel->getTotalByDateRange($startDate, $endDate, 'USD');

        // Calculate Net Profit
        $netRevenueLRD = $revenueLRD - ($adjustmentSummary['LRD']['Refund'] ?? 0);
        $netRevenueUSD = $revenueUSD - ($adjustmentSummary['USD']['Refund'] ?? 0);

        $grossProfitLRD = $netRevenueLRD - $cogs['LRD'];
        $grossProfitUSD = $netRevenueUSD - $cogs['USD'];

        $netProfitLRD = $grossProfitLRD - $expensesLRD - ($adjustmentSummary['LRD']['Damage'] ?? 0) - ($adjustmentSummary['LRD']['Theft'] ?? 0);
        $netProfitUSD = $grossProfitUSD - $expensesUSD - ($adjustmentSummary['USD']['Damage'] ?? 0) - ($adjustmentSummary['USD']['Theft'] ?? 0);

        $data = [
            'title' => 'Profit & Loss Statement',
            'startDate' => $startDate,
            'endDate' => $endDate,
            'selectedPeriod' => $period,
            'selectedCurrency' => $currency,
            'revenue' => ['LRD' => $revenueLRD, 'USD' => $revenueUSD],
            'refunds' => ['LRD' => $adjustmentSummary['LRD']['Refund'] ?? 0, 'USD' => $adjustmentSummary['USD']['Refund'] ?? 0],
            'returns' => ['LRD' => $adjustmentSummary['LRD']['Return'] ?? 0, 'USD' => $adjustmentSummary['USD']['Return'] ?? 0],
            'net_revenue' => ['LRD' => $netRevenueLRD, 'USD' => $netRevenueUSD],
            'cogs' => $cogs,
            'gross_profit' => ['LRD' => $grossProfitLRD, 'USD' => $grossProfitUSD],
            'expenses' => ['LRD' => $expensesLRD, 'USD' => $expensesUSD],
            'damage_loss' => ['LRD' => $adjustmentSummary['LRD']['Damage'] ?? 0, 'USD' => $adjustmentSummary['USD']['Damage'] ?? 0],
            'theft_loss' => ['LRD' => $adjustmentSummary['LRD']['Theft'] ?? 0, 'USD' => $adjustmentSummary['USD']['Theft'] ?? 0],
            'net_profit' => ['LRD' => $netProfitLRD, 'USD' => $netProfitUSD],
            'activePage' => 'reports',
            'activeSubPage' => 'profit-loss'
        ];

        return view('reports/profit_loss', $data);
    }


    /**
     * Reports Summary Dashboard - Shows how all reports correspond
     */
    public function summary()
    {
        $startDate = $this->request->getGet('start_date') ?: date('Y-m-01');
        $endDate = $this->request->getGet('end_date') ?: date('Y-m-t');
        $currency = $this->request->getGet('currency') ?: 'LRD';

        // Sales data
        $grossSales = $this->saleModel->getTotalByDateRange($startDate, $endDate, $currency);

        // Adjustment data
        $adjustmentSummary = $this->getAdjustmentSummary($startDate, $endDate, $currency);
        $refunds = $adjustmentSummary[$currency]['Refund'] ?? 0;
        $returns = $adjustmentSummary[$currency]['Return'] ?? 0;
        $damage = $adjustmentSummary[$currency]['Damage'] ?? 0;
        $theft = $adjustmentSummary[$currency]['Theft'] ?? 0;

        // Production data
        $productionCost = $this->productionJobModel
            ->selectSum('total_material_cost')
            ->where('production_date >=', $startDate)
            ->where('production_date <=', $endDate)
            ->where('currency', $currency)
            ->where('status', 'Completed')
            ->first();
        $totalProductionCost = $productionCost['total_material_cost'] ?? 0;

        // Expense data
        $expenses = $this->expenseModel->getTotalByDateRange($startDate, $endDate, $currency);

        // Net calculations
        $netSales = $grossSales - $refunds;
        $totalLosses = $damage + $theft;
        $netProfit = $netSales - $totalProductionCost - $expenses - $totalLosses;

        $data = [
            'title' => 'Reports Summary',
            'startDate' => $startDate,
            'endDate' => $endDate,
            'currency' => $currency,
            'grossSales' => $grossSales,
            'refunds' => $refunds,
            'returns' => $returns,
            'netSales' => $netSales,
            'productionCost' => $totalProductionCost,
            'damage' => $damage,
            'theft' => $theft,
            'totalLosses' => $totalLosses,
            'expenses' => $expenses,
            'netProfit' => $netProfit,
            'activePage' => 'reports',
            'activeSubPage' => 'summary'
        ];

        return view('reports/summary', $data);
    }


}