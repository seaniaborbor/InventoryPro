<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<div class="d-flex flex-column flex-lg-row justify-content-between align-items-lg-center gap-3 mb-4">
    <div>
        <h2 class="mb-1"><i class="bi bi-speedometer2 me-2"></i>Dashboard</h2>
        <p class="text-muted mb-0">A live operational summary of inventory, sales, expenses, purchases, production, and adjustments.</p>
    </div>

    <form id="dashboardFilters" class="row g-2 align-items-end dashboard-filter-form">
        <div class="col-md-4 col-lg-3">
            <label for="dashboardPeriod" class="form-label mb-1">Period</label>
            <select class="form-select" id="dashboardPeriod" name="period">
                <option value="last_7_days" <?= $defaultPeriod === 'last_7_days' ? 'selected' : '' ?>>Last 7 Days</option>
                <option value="last_30_days" <?= $defaultPeriod === 'last_30_days' ? 'selected' : '' ?>>Last 30 Days</option>
                <option value="last_90_days" <?= $defaultPeriod === 'last_90_days' ? 'selected' : '' ?>>Last 90 Days</option>
                <option value="last_year" <?= $defaultPeriod === 'last_year' ? 'selected' : '' ?>>Last 12 Months</option>
                <option value="all" <?= $defaultPeriod === 'all' ? 'selected' : '' ?>>All Time</option>
                <option value="custom" <?= $defaultPeriod === 'custom' ? 'selected' : '' ?>>Custom Range</option>
            </select>
        </div>
        <div class="col-md-3 col-lg-3 custom-date-wrap">
            <label for="dashboardStartDate" class="form-label mb-1">Start Date</label>
            <input type="date" class="form-control" id="dashboardStartDate" name="start_date" value="<?= esc($defaultStartDate) ?>">
        </div>
        <div class="col-md-3 col-lg-3 custom-date-wrap">
            <label for="dashboardEndDate" class="form-label mb-1">End Date</label>
            <input type="date" class="form-control" id="dashboardEndDate" name="end_date" value="<?= esc($defaultEndDate) ?>">
        </div>
        <div class="col-md-4 col-lg-3">
            <button type="submit" class="btn btn-primary w-100 dashboard-filter-button px-3">
                <span class="dashboard-filter-button-content px-5">
                    <i class="bi bi-funnel"></i>
                    <span>Apply</span>
                </span>
            </button>
        </div>
    </form>
</div>

<div class="alert alert-light border d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-2 mb-4">
    <div>
        <strong id="dashboardPeriodLabel">Loading...</strong>
        <span class="text-muted">This dashboard keeps all money values separated by currency.</span>
    </div>
    <small class="text-muted">Last updated: <span id="dashboardUpdatedAt">-</span></small>
</div>

<!-- Row 1: Summary Cards -->
<div class="row g-3 mb-4">
    <div class="col-md-6 col-xl-3">
        <div class="card h-100 border-0 shadow-sm">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <div class="text-muted small text-uppercase">Products</div>
                        <h3 class="mb-1" id="summaryProducts">0</h3>
                        <div class="small text-muted"><span id="summaryCategories">0</span> categories tracked</div>
                    </div>
                    <i class="bi bi-box-seam fs-2 text-primary"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-6 col-xl-3">
        <div class="card h-100 border-0 shadow-sm">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <div class="text-muted small text-uppercase">Stock Alerts</div>
                        <h3 class="mb-1"><span id="summaryLowStock">0</span> low</h3>
                        <div class="small text-muted"><span id="summaryOutOfStock">0</span> out of stock</div>
                    </div>
                    <i class="bi bi-exclamation-triangle fs-2 text-warning"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-6 col-xl-3">
        <div class="card h-100 border-0 shadow-sm">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <div class="text-muted small text-uppercase">Units In Stock</div>
                        <h3 class="mb-1" id="summaryUnits">0</h3>
                        <div class="small text-muted"><span id="summaryCustomers">0</span> customers on file</div>
                    </div>
                    <i class="bi bi-stack fs-2 text-success"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-6 col-xl-3">
        <div class="card h-100 border-0 shadow-sm">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <div class="text-muted small text-uppercase">Users & Suppliers</div>
                        <h3 class="mb-1"><span id="summaryUsers">0</span> active users</h3>
                        <div class="small text-muted"><span id="summarySuppliers">0</span> suppliers available</div>
                    </div>
                    <i class="bi bi-people fs-2 text-info"></i>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Row 2: Financial Cards -->
<div class="row g-3 mb-4">
    <div class="col-lg-6 col-xl-3">
        <div class="card h-100 border-0 shadow-sm">
            <div class="card-header bg-white border-0 pb-0">
                <h6 class="mb-0">Sales In Period</h6>
            </div>
            <div class="card-body">
                <div class="dashboard-currency-line"><span>LRD</span><strong id="salesLrd">L$ 0.00</strong></div>
                <div class="dashboard-currency-line"><span>USD</span><strong id="salesUsd">$ 0.00</strong></div>
                <div class="small text-muted mt-2"><span id="salesCount">0</span> sales records</div>
            </div>
        </div>
    </div>
    <div class="col-lg-6 col-xl-3">
        <div class="card h-100 border-0 shadow-sm">
            <div class="card-header bg-white border-0 pb-0">
                <h6 class="mb-0">Expenses In Period</h6>
            </div>
            <div class="card-body">
                <div class="dashboard-currency-line"><span>LRD</span><strong id="expensesLrd">L$ 0.00</strong></div>
                <div class="dashboard-currency-line"><span>USD</span><strong id="expensesUsd">$ 0.00</strong></div>
                <div class="small text-muted mt-2"><span id="expenseCount">0</span> expense entries</div>
            </div>
        </div>
    </div>
    <div class="col-lg-6 col-xl-3">
        <div class="card h-100 border-0 shadow-sm">
            <div class="card-header bg-white border-0 pb-0">
                <h6 class="mb-0">Purchases Received</h6>
            </div>
            <div class="card-body">
                <div class="dashboard-currency-line"><span>LRD</span><strong id="purchasesLrd">L$ 0.00</strong></div>
                <div class="dashboard-currency-line"><span>USD</span><strong id="purchasesUsd">$ 0.00</strong></div>
                <div class="small text-muted mt-2"><span id="purchaseCount">0</span> received purchases</div>
            </div>
        </div>
    </div>
    <div class="col-lg-6 col-xl-3">
        <div class="card h-100 border-0 shadow-sm">
            <div class="card-header bg-white border-0 pb-0">
                <h6 class="mb-0">Production Cost</h6>
            </div>
            <div class="card-body">
                <div class="dashboard-currency-line"><span>LRD</span><strong id="productionLrd">L$ 0.00</strong></div>
                <div class="dashboard-currency-line"><span>USD</span><strong id="productionUsd">$ 0.00</strong></div>
                <div class="small text-muted mt-2"><span id="productionCount">0</span> completed jobs</div>
            </div>
        </div>
    </div>
</div>

<!-- Row 3: Net Profit, Efficiency, and Adjustments -->
<div class="row g-3 mb-4">
    <div class="col-lg-6 col-xl-3">
        <div class="card h-100 border-0 shadow-sm">
            <div class="card-header bg-white border-0 pb-0">
                <h6 class="mb-0">Net Profit / Loss</h6>
            </div>
            <div class="card-body">
                <div class="dashboard-currency-line">
                    <span>LRD</span>
                    <strong id="netProfitLrd" class="text-success">L$ 0.00</strong>
                </div>
                <div class="dashboard-currency-line">
                    <span>USD</span>
                    <strong id="netProfitUsd" class="text-success">$ 0.00</strong>
                </div>
                <div class="small text-muted mt-2">Revenue - All Costs</div>
            </div>
        </div>
    </div>
    <div class="col-lg-6 col-xl-3">
        <div class="card h-100 border-0 shadow-sm">
            <div class="card-header bg-white border-0 pb-0">
                <h6 class="mb-0">Production Efficiency</h6>
            </div>
            <div class="card-body text-center">
                <div class="display-6 fw-bold" id="productionEfficiency">100%</div>
                <div class="small text-muted mt-2">Material usage efficiency</div>
                <div class="progress mt-2" style="height: 8px;">
                    <div id="efficiencyBar" class="progress-bar bg-success" style="width: 100%"></div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-lg-6 col-xl-6">
        <div class="card h-100 border-0 shadow-sm">
            <div class="card-header bg-white border-0 pb-0">
                <h6 class="mb-0">Stock Adjustments (Damage, Theft, Refunds)</h6>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-6">
                        <div class="dashboard-currency-line">
                            <span>Damage LRD</span>
                            <strong id="damageLrd" class="text-danger">L$ 0.00</strong>
                        </div>
                        <div class="dashboard-currency-line">
                            <span>Damage USD</span>
                            <strong id="damageUsd" class="text-danger">$ 0.00</strong>
                        </div>
                        <div class="dashboard-currency-line">
                            <span>Theft LRD</span>
                            <strong id="theftLrd" class="text-danger">L$ 0.00</strong>
                        </div>
                        <div class="dashboard-currency-line">
                            <span>Theft USD</span>
                            <strong id="theftUsd" class="text-danger">$ 0.00</strong>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="dashboard-currency-line">
                            <span>Refunds LRD</span>
                            <strong id="refundsLrd" class="text-warning">L$ 0.00</strong>
                        </div>
                        <div class="dashboard-currency-line">
                            <span>Refunds USD</span>
                            <strong id="refundsUsd" class="text-warning">$ 0.00</strong>
                        </div>
                        <div class="dashboard-currency-line">
                            <span>Returns LRD</span>
                            <strong id="returnsLrd" class="text-info">L$ 0.00</strong>
                        </div>
                        <div class="dashboard-currency-line">
                            <span>Returns USD</span>
                            <strong id="returnsUsd" class="text-info">$ 0.00</strong>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Row 4: Charts -->
<div class="row g-3 mb-4">
    <div class="col-xl-8">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-header bg-white">
                <h5 class="mb-0"><i class="bi bi-graph-up-arrow me-2"></i>Financial Trend By Currency</h5>
                <small class="text-muted">Sales and expenses stay separated between LRD and USD.</small>
            </div>
            <div class="card-body">
                <canvas id="financialTrendChart" height="140"></canvas>
            </div>
        </div>
    </div>
    <div class="col-xl-4">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-header bg-white">
                <h5 class="mb-0"><i class="bi bi-bar-chart me-2"></i>Activity Volume</h5>
                <small class="text-muted">Count-based activity by period.</small>
            </div>
            <div class="card-body">
                <canvas id="activityTrendChart" height="140"></canvas>
            </div>
        </div>
    </div>
</div>

<!-- Row 5: Category and Operational Snapshot -->
<div class="row g-3 mb-4">
    <div class="col-lg-5">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-header bg-white">
                <h5 class="mb-0"><i class="bi bi-diagram-3 me-2"></i>Inventory By Category</h5>
            </div>
            <div class="card-body">
                <canvas id="categorySnapshotChart" height="190"></canvas>
            </div>
        </div>
    </div>
    <div class="col-lg-7">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-header bg-white">
                <h5 class="mb-0"><i class="bi bi-lightning-charge me-2"></i>Operational Snapshot</h5>
            </div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-sm-6 col-xl-3">
                        <div class="border rounded p-3 h-100">
                            <div class="small text-muted text-uppercase">Sales</div>
                            <div class="fs-4 fw-semibold" id="activitySales">0</div>
                            <div class="small text-muted">Transactions in period</div>
                        </div>
                    </div>
                    <div class="col-sm-6 col-xl-3">
                        <div class="border rounded p-3 h-100">
                            <div class="small text-muted text-uppercase">Expenses</div>
                            <div class="fs-4 fw-semibold" id="activityExpenses">0</div>
                            <div class="small text-muted">Recorded entries</div>
                        </div>
                    </div>
                    <div class="col-sm-6 col-xl-3">
                        <div class="border rounded p-3 h-100">
                            <div class="small text-muted text-uppercase">Draft Purchases</div>
                            <div class="fs-4 fw-semibold" id="activityDraftPurchases">0</div>
                            <div class="small text-muted">Awaiting receiving</div>
                        </div>
                    </div>
                    <div class="col-sm-6 col-xl-3">
                        <div class="border rounded p-3 h-100">
                            <div class="small text-muted text-uppercase">Draft Production</div>
                            <div class="fs-4 fw-semibold" id="activityDraftProduction">0</div>
                            <div class="small text-muted">Jobs pending completion</div>
                        </div>
                    </div>
                </div>
                <div class="alert alert-warning mt-3 mb-0 low-stock-summary" id="lowStockSummary">
                    Low stock attention needed for <strong id="lowStockSummaryCount">0</strong> item(s).
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Row 6: Top Products and Recent Transactions -->
<div class="row g-3">
    <div class="col-xl-5">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-header bg-white">
                <h5 class="mb-0"><i class="bi bi-trophy me-2"></i>Top Selling Products</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0" id="topProductsTable">
                        <thead>
                            <tr>
                                <th>Product</th>
                                <th>Qty Sold</th>
                                <th>Invoices</th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    <div class="col-xl-7">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-header bg-white">
                <h5 class="mb-0"><i class="bi bi-clock-history me-2"></i>Recent Financial Activity</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0" id="recentTransactionsTable">
                        <thead>
                            <tr>
                                <th>Date</th>
                                <th>Module</th>
                                <th>Reference</th>
                                <th>Party / Category</th>
                                <th>Amount</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Row 7: Low Stock Watchlist -->
<div class="row mt-4">
    <div class="col-12">
        <div class="card border-0 shadow-sm" id="lowStockPanel">
            <div class="card-header bg-white">
                <h5 class="mb-0"><i class="bi bi-exclamation-octagon me-2"></i>Low Stock Watchlist</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0" id="lowStockTable">
                        <thead>
                            <tr>
                                <th>Product</th>
                                <th>Current Stock</th>
                                <th>Minimum Stock</th>
                                <th>Unit</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
let financialTrendChart;
let activityTrendChart;
let categorySnapshotChart;
let topProductsTable;
let recentTransactionsTable;
let lowStockTable;

function formatCurrency(amount, currency) {
    const numericAmount = parseFloat(amount || 0);
    const symbol = currency === 'USD' ? '$' : 'L$';
    return `${symbol} ${numericAmount.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 })}`;
}

function formatNumber(value) {
    return parseFloat(value || 0).toLocaleString('en-US', { maximumFractionDigits: 2 });
}

function toggleCustomDateInputs() {
    const isCustom = $('#dashboardPeriod').val() === 'custom';
    $('.custom-date-wrap').toggle(isCustom);
}

function getFilterParams() {
    return {
        period: $('#dashboardPeriod').val(),
        start_date: $('#dashboardStartDate').val(),
        end_date: $('#dashboardEndDate').val()
    };
}

function loadDashboardData() {
    $.ajax({
        url: '<?= base_url('dashboard/get-data') ?>',
        type: 'GET',
        data: getFilterParams(),
        dataType: 'json',
        success: function(response) {
            if (response.status !== 'success') {
                return;
            }

            const data = response.data;
            updateSummaryCards(data);
            updateFinancialCards(data.financial_summary, data.activity_summary);
            updateNetProfitAndEfficiency(data);
            updateAdjustmentsCards(data.financial_summary);
            updateOperationalSummary(data.activity_summary, data.inventory_summary);
            updateFinancialTrendChart(data.financial_trend);
            updateActivityTrendChart(data.activity_trend);
            updateCategorySnapshotChart(data.category_snapshot);
            updateTopProductsTable(data.top_products);
            updateRecentTransactions(data.recent_transactions);
            updateLowStockTable(data.low_stock_items);
            $('#dashboardPeriodLabel').text(data.filters.label);
            $('#dashboardUpdatedAt').text(data.updated_at);
        },
        error: function() {
            console.error('Failed to load dashboard data');
        }
    });
}

function updateSummaryCards(data) {
    $('#summaryProducts').text(data.inventory_summary.total_products);
    $('#summaryCategories').text(data.inventory_summary.total_categories);
    $('#summaryLowStock').text(data.inventory_summary.low_stock_count);
    $('#summaryOutOfStock').text(data.inventory_summary.out_of_stock_count);
    $('#summaryUnits').text(formatNumber(data.inventory_summary.total_units_in_stock));
    $('#summaryCustomers').text(data.relationship_summary.customers);
    $('#summaryUsers').text(data.relationship_summary.active_users);
    $('#summarySuppliers').text(data.relationship_summary.suppliers);
}

function updateFinancialCards(financial, activity) {
    $('#salesLrd').text(formatCurrency(financial.sales.LRD, 'LRD'));
    $('#salesUsd').text(formatCurrency(financial.sales.USD, 'USD'));
    $('#salesCount').text(financial.sales.count || activity.sales_count || 0);

    $('#expensesLrd').text(formatCurrency(financial.expenses.LRD, 'LRD'));
    $('#expensesUsd').text(formatCurrency(financial.expenses.USD, 'USD'));
    $('#expenseCount').text(financial.expenses.count || activity.expense_count || 0);

    $('#purchasesLrd').text(formatCurrency(financial.purchases.LRD, 'LRD'));
    $('#purchasesUsd').text(formatCurrency(financial.purchases.USD, 'USD'));
    $('#purchaseCount').text(financial.purchases.count || activity.purchase_count || 0);

    $('#productionLrd').text(formatCurrency(financial.production_cost.LRD, 'LRD'));
    $('#productionUsd').text(formatCurrency(financial.production_cost.USD, 'USD'));
    $('#productionCount').text(financial.production_cost.count || activity.production_count || 0);
}

function updateNetProfitAndEfficiency(data) {
    if (data.net_profit) {
        const profitLrd = data.net_profit.LRD.net_profit;
        const profitUsd = data.net_profit.USD.net_profit;
        
        $('#netProfitLrd').text(formatCurrency(profitLrd, 'LRD'));
        $('#netProfitUsd').text(formatCurrency(profitUsd, 'USD'));
        
        $('#netProfitLrd').removeClass('text-success text-danger').addClass(profitLrd >= 0 ? 'text-success' : 'text-danger');
        $('#netProfitUsd').removeClass('text-success text-danger').addClass(profitUsd >= 0 ? 'text-success' : 'text-danger');
    }
    
    if (data.production_efficiency) {
        const efficiency = data.production_efficiency.LRD;
        $('#productionEfficiency').text(efficiency + '%');
        $('#efficiencyBar').css('width', efficiency + '%');
        $('#efficiencyBar').removeClass('bg-success bg-warning bg-danger')
            .addClass(efficiency >= 80 ? 'bg-success' : (efficiency >= 50 ? 'bg-warning' : 'bg-danger'));
    }
}

function updateAdjustmentsCards(financial) {
    if (financial.adjustments) {
        const adjustments = financial.adjustments;
        
        // Damage
        $('#damageLrd').text(formatCurrency((adjustments.Damage?.total_value || 0), 'LRD'));
        $('#damageUsd').text(formatCurrency((adjustments.USD?.Damage?.total_value || 0), 'USD'));
        
        // Theft
        $('#theftLrd').text(formatCurrency((adjustments.Theft?.total_value || 0), 'LRD'));
        $('#theftUsd').text(formatCurrency((adjustments.USD?.Theft?.total_value || 0), 'USD'));
        
        // Refunds
        $('#refundsLrd').text(formatCurrency((adjustments.Refund?.total_value || 0), 'LRD'));
        $('#refundsUsd').text(formatCurrency((adjustments.USD?.Refund?.total_value || 0), 'USD'));
        
        // Returns
        $('#returnsLrd').text(formatCurrency((adjustments.Return?.total_value || 0), 'LRD'));
        $('#returnsUsd').text(formatCurrency((adjustments.USD?.Return?.total_value || 0), 'USD'));
    }
}

function updateOperationalSummary(activity, inventory) {
    $('#activitySales').text(activity.sales_count || 0);
    $('#activityExpenses').text(activity.expense_count || 0);
    $('#activityDraftPurchases').text(activity.draft_purchases || 0);
    $('#activityDraftProduction').text(activity.draft_production_jobs || 0);
    $('#lowStockSummaryCount').text(inventory.low_stock_count || 0);
    $('#lowStockSummary').toggle((inventory.low_stock_count || 0) > 0);
}

function updateFinancialTrendChart(data) {
    const ctx = document.getElementById('financialTrendChart').getContext('2d');

    if (financialTrendChart) {
        financialTrendChart.destroy();
    }

    financialTrendChart = new Chart(ctx, {
        type: 'bar',
        data: {
            labels: data.labels,
            datasets: [
                {
                    label: 'Sales LRD',
                    data: data.series.sales_lrd,
                    backgroundColor: 'rgba(13, 110, 253, 0.75)',
                    borderColor: '#0d6efd',
                    borderWidth: 1,
                    yAxisID: 'lrd'
                },
                {
                    label: 'Expenses LRD',
                    data: data.series.expenses_lrd,
                    backgroundColor: 'rgba(220, 53, 69, 0.65)',
                    borderColor: '#dc3545',
                    borderWidth: 1,
                    yAxisID: 'lrd'
                },
                {
                    label: 'Sales USD',
                    data: data.series.sales_usd,
                    type: 'line',
                    borderColor: '#198754',
                    backgroundColor: 'rgba(25, 135, 84, 0.12)',
                    tension: 0.3,
                    fill: false,
                    yAxisID: 'usd'
                },
                {
                    label: 'Expenses USD',
                    data: data.series.expenses_usd,
                    type: 'line',
                    borderColor: '#fd7e14',
                    backgroundColor: 'rgba(253, 126, 20, 0.12)',
                    tension: 0.3,
                    fill: false,
                    yAxisID: 'usd'
                }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            interaction: {
                mode: 'index',
                intersect: false
            },
            scales: {
                lrd: {
                    type: 'linear',
                    position: 'left',
                    ticks: {
                        callback: function(value) {
                            return 'L$ ' + Number(value).toLocaleString();
                        }
                    }
                },
                usd: {
                    type: 'linear',
                    position: 'right',
                    grid: {
                        drawOnChartArea: false
                    },
                    ticks: {
                        callback: function(value) {
                            return '$ ' + Number(value).toLocaleString();
                        }
                    }
                }
            },
            plugins: {
                legend: {
                    position: 'bottom'
                }
            }
        }
    });
}

function updateActivityTrendChart(data) {
    const ctx = document.getElementById('activityTrendChart').getContext('2d');

    if (activityTrendChart) {
        activityTrendChart.destroy();
    }

    activityTrendChart = new Chart(ctx, {
        type: 'line',
        data: {
            labels: data.labels,
            datasets: [
                {
                    label: 'Sales',
                    data: data.series.sales_count,
                    borderColor: '#0d6efd',
                    backgroundColor: 'rgba(13, 110, 253, 0.08)',
                    tension: 0.25
                },
                {
                    label: 'Purchases',
                    data: data.series.purchase_count,
                    borderColor: '#20c997',
                    backgroundColor: 'rgba(32, 201, 151, 0.08)',
                    tension: 0.25
                },
                {
                    label: 'Expenses',
                    data: data.series.expense_count,
                    borderColor: '#ffc107',
                    backgroundColor: 'rgba(255, 193, 7, 0.08)',
                    tension: 0.25
                },
                {
                    label: 'Production',
                    data: data.series.production_count,
                    borderColor: '#6f42c1',
                    backgroundColor: 'rgba(111, 66, 193, 0.08)',
                    tension: 0.25
                }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'bottom'
                }
            }
        }
    });
}

function updateCategorySnapshotChart(items) {
    const ctx = document.getElementById('categorySnapshotChart').getContext('2d');

    if (categorySnapshotChart) {
        categorySnapshotChart.destroy();
    }

    categorySnapshotChart = new Chart(ctx, {
        type: 'bar',
        data: {
            labels: items.map(item => item.category_name),
            datasets: [
                {
                    label: 'Stock Units',
                    data: items.map(item => item.stock_units),
                    backgroundColor: 'rgba(25, 135, 84, 0.75)'
                },
                {
                    label: 'Products',
                    data: items.map(item => item.product_count),
                    backgroundColor: 'rgba(13, 202, 240, 0.75)'
                }
            ]
        },
        options: {
            indexAxis: 'y',
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'bottom'
                }
            }
        }
    });
}

function updateTopProductsTable(products) {
    if (!topProductsTable) {
        topProductsTable = $('#topProductsTable').DataTable({
            pageLength: 5,
            searching: true,
            ordering: true,
            info: true,
            lengthChange: false,
            language: {
                emptyTable: 'No product sales found for this range.'
            }
        });
    }

    topProductsTable.clear();

    if (products && products.length) {
        products.forEach(product => {
            const productUrl = '<?= base_url('inventory/products/view/') ?>' + product.id;
            topProductsTable.row.add([
                `<a href="${productUrl}" class="text-decoration-none">${escapeHtml(product.product_name)}</a>`,
                formatNumber(product.total_quantity),
                String(product.invoice_count)
            ]);
        });
    }

    topProductsTable.draw();
}

function updateRecentTransactions(transactions) {
    if (!recentTransactionsTable) {
        recentTransactionsTable = $('#recentTransactionsTable').DataTable({
            pageLength: 10,
            searching: true,
            ordering: true,
            info: true,
            lengthChange: false,
            order: [[0, 'desc']],
            language: {
                emptyTable: 'No recent financial activity found for this range.'
            }
        });
    }

    recentTransactionsTable.clear();

    if (transactions && transactions.length) {
        transactions.forEach(item => {
            recentTransactionsTable.row.add([
                item.date ? item.date.substring(0, 16).replace('T', ' ') : '-',
                `<span class="badge bg-light text-dark border">${escapeHtml(item.module)}</span>`,
                `<a href="${item.link}" class="text-decoration-none">${escapeHtml(item.reference)}</a>`,
                escapeHtml(item.party),
                formatCurrency(item.amount, item.currency),
                `<span class="badge ${resolveStatusClass(item.status)}">${escapeHtml(item.status)}</span>`
            ]);
        });
    }

    recentTransactionsTable.draw();
}

function updateLowStockTable(items) {
    if (!lowStockTable) {
        lowStockTable = $('#lowStockTable').DataTable({
            pageLength: 10,
            searching: true,
            ordering: true,
            info: true,
            lengthChange: false,
            language: {
                emptyTable: 'No low stock items right now.'
            }
        });
    }

    lowStockTable.clear();

    if (items && items.length) {
        items.forEach(item => {
            lowStockTable.row.add([
                escapeHtml(item.product_name),
                `<span class="text-danger fw-semibold">${formatNumber(item.quantity)}</span>`,
                formatNumber(item.minimum_stock),
                escapeHtml(item.unit_symbol || item.unit_name || '-'),
                `<a href="<?= base_url('inventory/products/edit/') ?>${item.id}" class="btn btn-sm btn-outline-primary">Review</a>`
            ]);
        });
    }

    lowStockTable.draw();
}

function resolveStatusClass(status) {
    switch (status) {
        case 'Paid':
        case 'Recorded':
        case 'Received':
        case 'Completed':
            return 'bg-success';
        case 'Partial':
        case 'Partially Paid':
            return 'bg-warning text-dark';
        case 'Unpaid':
        case 'Draft':
            return 'bg-secondary';
        default:
            return 'bg-light text-dark border';
    }
}

function escapeHtml(str) {
    if (!str) return '';
    return str.replace(/[&<>]/g, function(m) {
        if (m === '&') return '&amp;';
        if (m === '<') return '&lt;';
        if (m === '>') return '&gt;';
        return m;
    });
}

$(document).ready(function() {
    toggleCustomDateInputs();
    loadDashboardData();

    $('#dashboardPeriod').on('change', function() {
        toggleCustomDateInputs();
        if ($(this).val() !== 'custom') {
            loadDashboardData();
        }
    });

    $('#dashboardFilters').on('submit', function(event) {
        event.preventDefault();
        loadDashboardData();
    });

    setInterval(loadDashboardData, 60000);
});
</script>

<style>
.dashboard-currency-line {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 0.35rem 0;
    border-bottom: 1px solid rgba(0, 0, 0, 0.06);
}

.dashboard-currency-line:last-child {
    border-bottom: 0;
}

.dashboard-filter-button {
    min-height: 38px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
}

.dashboard-filter-button-content {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 0.5rem;
}

.dashboard-filter-form .custom-date-wrap {
    display: none;
}

.low-stock-summary {
    display: none;
}
</style>
<?= $this->endSection() ?>