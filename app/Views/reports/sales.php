<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0"><i class="bi bi-cart me-2"></i>Sales Reports</h5>
            </div>
            <div class="card-body">
                <!-- Filter Form -->
                <form method="get" class="row g-3 mb-4">
                    <div class="col-md-2">
                        <label class="form-label">Period</label>
                        <select name="period" id="periodFilter" class="form-select">
                            <option value="all" <?= ($selectedPeriod ?? 'all') === 'all' ? 'selected' : '' ?>>All Sales Log</option>
                            <option value="last_7_days" <?= ($selectedPeriod ?? '') === 'last_7_days' ? 'selected' : '' ?>>Last 7 Days</option>
                            <option value="last_month" <?= ($selectedPeriod ?? '') === 'last_month' ? 'selected' : '' ?>>Last Month</option>
                            <option value="last_year" <?= ($selectedPeriod ?? '') === 'last_year' ? 'selected' : '' ?>>Last Year</option>
                            <option value="custom" <?= ($selectedPeriod ?? '') === 'custom' ? 'selected' : '' ?>>Custom Range</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">Start Date</label>
                        <input type="date" name="start_date" id="startDateFilter" class="form-control" value="<?= $startDate ?>">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">End Date</label>
                        <input type="date" name="end_date" id="endDateFilter" class="form-control" value="<?= $endDate ?>">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">Currency</label>
                        <select name="currency" class="form-select">
                            <option value="">All Currencies</option>
                            <option value="LRD" <?= $selectedCurrency == 'LRD' ? 'selected' : '' ?>>LRD Only</option>
                            <option value="USD" <?= $selectedCurrency == 'USD' ? 'selected' : '' ?>>USD Only</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Product</label>
                        <select name="product_id" class="form-select">
                            <option value="">All Products</option>
                            <?php foreach ($products as $product): ?>
                            <option value="<?= $product['id'] ?>" <?= $selectedProduct == $product['id'] ? 'selected' : '' ?>>
                                <?= $product['product_name'] ?> (<?= $product['sku'] ?>)
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">&nbsp;</label>
                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary">Apply Filters</button>
                            <a href="<?= base_url('reports/sales') ?>" class="btn btn-outline-secondary">Reset</a>
                            <div class="btn-group">
                                <button type="button" class="btn btn-success" onclick="exportReport('pdf')">
                                    <i class="bi bi-file-pdf"></i> PDF
                                </button>
                                <button type="button" class="btn btn-info" onclick="exportReport('excel')">
                                    <i class="bi bi-file-excel"></i> Excel
                                </button>
                            </div>
                        </div>
                    </div>
                </form>
                
                <!-- Summary Cards -->
                <div class="row mb-4">
                    <?php if (!$selectedCurrency): ?>
                        <!-- Show separate currency cards when no currency filter -->
                        <div class="col-md-4 mt-3">
                            <div class="card bg-primary text-white">
                                <div class="card-body text-center">
                                    <h6 class="card-title">LRD Sales</h6>
                                    <h4><?= formatCurrency($totalSalesLRD, 'LRD') ?></h4>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4 mt-3">
                            <div class="card bg-info text-white">
                                <div class="card-body text-center">
                                    <h6 class="card-title">USD Sales</h6>
                                    <h4><?= formatCurrency($totalSalesUSD, 'USD') ?></h4>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4 mt-3">
                            <div class="card bg-success text-white">
                                <div class="card-body text-center">
                                    <h6 class="card-title">Total Sales</h6>
                                    <h4><?= formatCurrency($totalSalesLRD + $totalSalesUSD, 'LRD') ?></h4>
                                </div>
                            </div>
                        </div>
                    <?php else: ?>
                        <!-- Show single currency when filtered -->
                        <div class="col-md-4 mt-3">
                            <div class="card bg-primary text-white">
                                <div class="card-body text-center">
                                    <h6 class="card-title">Total Sales</h6>
                                    <h4><?= formatCurrency($selectedCurrency == 'LRD' ? $totalSalesLRD : $totalSalesUSD, $selectedCurrency) ?></h4>
                                </div>
                            </div>
                        </div>
                    <?php endif; ?>
                    
                    <div class="col-md-4 mt-3">
                        <div class="card bg-warning text-dark">
                            <div class="card-body text-center">
                                <h6 class="card-title">Total Tax</h6>
                                <h4><?= formatCurrency($totalTax, $selectedCurrency ?: 'LRD') ?></h4>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4 mt-3">
                        <div class="card bg-secondary text-white">
                            <div class="card-body text-center">
                                <h6 class="card-title">Total Discount</h6>
                                <h4><?= formatCurrency($totalDiscount, $selectedCurrency ?: 'LRD') ?></h4>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4 mt-3">
                        <div class="card bg-dark text-white">
                            <div class="card-body text-center">
                                <h6 class="card-title">Transactions</h6>
                                <h4>
                                    <?php 
                                    echo count($salesLog);
                                    ?>
                                </h4>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Daily Sales Chart -->
                <div class="row mb-4">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-header">
                                <h6 class="mb-0">Sales Trend</h6>
                            </div>
                            <div class="card-body">
                                <canvas id="salesChart" height="300"></canvas>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="row">
                    <!-- Top Products -->
                    <div class="col-md-6 mb-4">
                        <div class="card">
                            <div class="card-header">
                                <h6 class="mb-0">Top Selling Products</h6>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table table-hover table-sm" id="topProductsTable">
                                        <thead>
                                            <tr>
                                                <th data-sort="string">Product</th>
                                                <th data-sort="int" class="text-end">Qty Sold</th>
                                                <th data-sort="float" class="text-end">Revenue</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($topProducts as $product): ?>
                                            <?php $productHistoryId = $product['product_id'] ?? $product['id'] ?? null; ?>
                                            <tr class="clickable-row" data-url="<?= $productHistoryId ? base_url('reports/productHistory/' . $productHistoryId) : '' ?>">
                                                <td>
                                                    <strong><?= $product['product_name'] ?></strong><br>
                                                    <small class="text-muted"><?= $product['sku'] ?></small>
                                                </td>
                                                <td class="text-end"><?= number_format($product['total_quantity'], 2) ?></td>
                                                <td class="text-end fw-bold"><?= formatCurrency($product['total_revenue'], $selectedCurrency ?: 'LRD') ?></td>
                                            </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Payment Methods -->
                    <div class="col-md-6 mb-4">
                        <div class="card">
                            <div class="card-header">
                                <h6 class="mb-0">Sales by Payment Method</h6>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table table-hover table-sm" id="paymentMethodsTable">
                                        <thead>
                                            <tr>
                                                <th data-sort="string">Method</th>
                                                <th data-sort="int" class="text-end">Count</th>
                                                <th data-sort="float" class="text-end">Total</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($salesByMethod as $method): ?>
                                            <tr>
                                                <td><span class="badge bg-secondary"><?= $method['payment_method'] ?></span></td>
                                                <td class="text-end"><?= $method['count'] ?></td>
                                                <td class="text-end fw-bold"><?= formatCurrency($method['total'], $selectedCurrency ?: 'LRD') ?></td>
                                            </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Customer Purchase Summary -->
                <div class="row mb-4">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-header">
                                <h6 class="mb-0">Customer Purchase Summary</h6>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table table-hover table-sm" id="customerSummaryTable">
                                        <thead>
                                            <tr>
                                                <th data-sort="string">Customer</th>
                                                <th data-sort="float" class="text-end">Total LRD</th>
                                                <th data-sort="float" class="text-end">Total USD</th>
                                                <th data-sort="float" class="text-end">Total Combined</th>
                                                <th data-sort="int" class="text-end">Transactions</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($customerSummary as $customer): ?>
                                            <tr class="clickable-row" data-url="<?= base_url('reports/customerHistory/' . $customer['customer_id']) ?>">
                                                <td><strong><?= $customer['customer_name'] ?: 'Walk-in Customer' ?></strong></td>
                                                <td class="text-end"><?= formatCurrency($customer['total_lrd'], 'LRD') ?></td>
                                                <td class="text-end"><?= formatCurrency($customer['total_usd'], 'USD') ?></td>
                                                <td class="text-end fw-bold"><?= formatCurrency($customer['total_lrd'] + $customer['total_usd'], 'LRD') ?></td>
                                                <td class="text-end"><?= $customer['total_transactions'] ?></td>
                                            </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Seller Sales Summary -->
                <div class="row mb-4">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-header">
                                <h6 class="mb-0">Seller Sales Summary</h6>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table table-hover table-sm" id="sellerSummaryTable">
                                        <thead>
                                            <tr>
                                                <th data-sort="string">Seller</th>
                                                <th data-sort="float" class="text-end">Total LRD</th>
                                                <th data-sort="float" class="text-end">Total USD</th>
                                                <th data-sort="float" class="text-end">Total Combined</th>
                                                <th data-sort="int" class="text-end">Transactions</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($sellerSummary as $seller): ?>
                                            <tr class="clickable-row" data-url="<?= base_url('reports/sellerHistory/' . $seller['user_id']) ?>">
                                                <td><strong><?= $seller['full_name'] ?: 'Unknown' ?></strong></td>
                                                <td class="text-end"><?= formatCurrency($seller['total_lrd'], 'LRD') ?></td>
                                                <td class="text-end"><?= formatCurrency($seller['total_usd'], 'USD') ?></td>
                                                <td class="text-end fw-bold"><?= formatCurrency($seller['total_lrd'] + $seller['total_usd'], 'LRD') ?></td>
                                                <td class="text-end"><?= $seller['total_transactions'] ?></td>
                                            </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                
                <!-- Sales Log -->
                <div class="card">
                    <div class="card-header">
                        <h6 class="mb-0">Sales Log</h6>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover table-sm" id="salesLogTable">
                                <thead>
                                    <tr>
                                        <th data-sort="string">Date/Time</th>
                                        <th data-sort="string">Invoice #</th>
                                        <th data-sort="string">Customer</th>
                                        <th data-sort="string">Seller</th>
                                        <th data-sort="int" class="text-end">Items</th>
                                        <th data-sort="float" class="text-end">Total Amount</th>
                                        <th data-sort="string">Payment Method</th>
                                        <th data-sort="float" class="text-end">Amount Paid</th>
                                        <th data-sort="float" class="text-end">Balance</th>
                                        <th data-sort="string">Status</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($salesLog as $sale): ?>
                                    <tr>
                                        <td>
                                            <small class="text-muted">
                                                <?= date('M d, Y', strtotime($sale['sale_date'])) ?><br>
                                                <?= date('H:i', strtotime($sale['created_at'])) ?>
                                            </small>
                                        </td>
                                        <td>
                                            <a href="<?= base_url('sales/view/' . $sale['id']) ?>" class="text-decoration-none">
                                                #<?= str_pad($sale['id'], 6, '0', STR_PAD_LEFT) ?>
                                            </a>
                                        </td>
                                        <td>
                                            <strong><?= $sale['customer_name'] ?: 'Walk-in Customer' ?></strong>
                                        </td>
                                        <td>
                                            <span class="badge bg-info"><?= $sale['seller_name'] ?: 'Unknown' ?></span>
                                        </td>
                                        <td class="text-end">
                                            <?= $sale['items_count'] ?>
                                        </td>
                                        <td class="text-end fw-bold">
                                            <?= formatCurrency($sale['total_amount'], $sale['currency']) ?>
                                        </td>
                                        <td>
                                            <span class="badge bg-secondary"><?= $sale['payment_method'] ?></span>
                                        </td>
                                        <td class="text-end">
                                            <?= formatCurrency($sale['amount_paid'] ?? 0, $sale['currency']) ?>
                                        </td>
                                        <td class="text-end">
                                            <?php
                                            $balance = ($sale['total_amount'] ?? 0) - ($sale['amount_paid'] ?? 0);
                                            $balanceClass = $balance > 0 ? 'text-danger' : 'text-success';
                                            ?>
                                            <span class="<?= $balanceClass ?> fw-bold">
                                                <?= formatCurrency($balance, $sale['currency']) ?>
                                            </span>
                                        </td>
                                        <td>
                                            <?php
                                            $status = $sale['payment_status'] ?? 'Unpaid';
                                            $statusClass = $status == 'Paid' ? 'bg-success' : ($status == 'Partial' ? 'bg-warning' : 'bg-danger');
                                            ?>
                                            <span class="badge <?= $statusClass ?>"><?= $status ?></span>
                                        </td>
                                        <td>
                                            <a href="<?= base_url('sales/view/' . $sale['id']) ?>" class="btn btn-sm btn-outline-primary">
                                                <i class="bi bi-eye"></i> View
                                            </a>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                        
                        <?php if (empty($salesLog)): ?>
                            <div class="text-center text-muted py-4">
                                <i class="bi bi-receipt fs-1"></i>
                                <p>No sales found for the selected period.</p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
let salesChart;

$(document).ready(function() {
    toggleCustomDateInputs();

    // Initialize chart
    var chartElement = document.getElementById('salesChart');
    if (chartElement) {
        var ctx = chartElement.getContext('2d');
        var dailyLabels = <?= json_encode(array_values(array_column($dailySales ?? [], 'date'))) ?>;
        var totalDays = dailyLabels.length;
        var maxVisibleTicks = totalDays <= 7 ? totalDays : (totalDays <= 31 ? 8 : (totalDays <= 90 ? 10 : 12));
        var datasets = [];
        
        <?php if (!$selectedCurrency): ?>
            datasets = [
                {
                    label: 'LRD Sales',
                    data: <?= json_encode(array_values(array_column($dailySales ?? [], 'total_lrd'))) ?>,
                    borderColor: '#0d6efd',
                    backgroundColor: 'rgba(13, 110, 253, 0.1)',
                    tension: 0.4,
                    fill: true
                },
                {
                    label: 'USD Sales',
                    data: <?= json_encode(array_values(array_column($dailySales ?? [], 'total_usd'))) ?>,
                    borderColor: '#198754',
                    backgroundColor: 'rgba(25, 135, 84, 0.1)',
                    tension: 0.4,
                    fill: true
                }
            ];
        <?php else: ?>
            datasets = [{
                label: 'Sales Amount (<?= $selectedCurrency ?>)',
                data: <?= json_encode(array_values(array_column($dailySales ?? [], 'total'))) ?>,
                borderColor: '#0d6efd',
                backgroundColor: 'rgba(13, 110, 253, 0.1)',
                tension: 0.4,
                fill: true
            }];
        <?php endif; ?>
        
        salesChart = new Chart(ctx, {
            type: 'line',
            data: {
                labels: dailyLabels,
                datasets: datasets
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    x: {
                        ticks: {
                            autoSkip: true,
                            maxTicksLimit: maxVisibleTicks,
                            maxRotation: totalDays > 31 ? 45 : 0,
                            minRotation: 0,
                            callback: function(value) {
                                return formatChartDateLabel(this.getLabelForValue(value), totalDays);
                            }
                        },
                        grid: {
                            display: totalDays <= 31
                        }
                    },
                    y: {
                        beginAtZero: true,
                        ticks: {
                            callback: function(value) {
                                return value.toLocaleString();
                            }
                        }
                    }
                },
                plugins: {
                    tooltip: {
                        callbacks: {
                            title: function(context) {
                                if (!context.length) {
                                    return '';
                                }

                                return formatChartTooltipDate(context[0].label, totalDays);
                            },
                            label: function(context) {
                                return context.dataset.label + ': ' + context.parsed.y.toLocaleString();
                            }
                        }
                    },
                    legend: { position: 'top' }
                }
            }
        });
    }

    initReportTable('#topProductsTable', [[1, 'desc']]);
    initReportTable('#paymentMethodsTable', [[1, 'desc']]);
    initReportTable('#customerSummaryTable', [[4, 'desc']]);
    initReportTable('#sellerSummaryTable', [[4, 'desc']]);
    initReportTable('#salesLogTable', [[0, 'desc']]);

    $('#periodFilter').on('change', function() {
        toggleCustomDateInputs();
    });

    $(document).on('click', '.clickable-row', function() {
        const url = $(this).data('url');
        if (url) {
            window.location.href = url;
        }
    });
});

function toggleCustomDateInputs() {
    var isCustom = $('#periodFilter').val() === 'custom';
    $('#startDateFilter, #endDateFilter').prop('disabled', !isCustom);
}

function initReportTable(selector, order) {
    if (!$.fn.DataTable.isDataTable(selector)) {
        $(selector).DataTable({
            pageLength: 20,
            lengthChange: false,
            ordering: true,
            searching: true,
            info: true,
            order: order,
            language: {
                search: 'Search:',
                paginate: {
                    previous: 'Prev',
                    next: 'Next'
                }
            }
        });
    }
}

function formatChartDateLabel(label, totalDays) {
    if (!label) {
        return '';
    }

    var date = new Date(label + 'T00:00:00');
    if (Number.isNaN(date.getTime())) {
        return label;
    }

    if (totalDays <= 31) {
        return date.toLocaleDateString(undefined, { month: 'short', day: 'numeric' });
    }

    if (totalDays <= 90) {
        return date.toLocaleDateString(undefined, { month: 'short', day: 'numeric' });
    }

    return date.toLocaleDateString(undefined, { month: 'short', year: '2-digit' });
}

function formatChartTooltipDate(label, totalDays) {
    if (!label) {
        return '';
    }

    var date = new Date(label + 'T00:00:00');
    if (Number.isNaN(date.getTime())) {
        return label;
    }

    if (totalDays <= 31) {
        return date.toLocaleDateString(undefined, { weekday: 'short', month: 'short', day: 'numeric', year: 'numeric' });
    }

    return date.toLocaleDateString(undefined, { month: 'long', day: 'numeric', year: 'numeric' });
}

function exportReport(type) {
    var params = new URLSearchParams(window.location.search);
    params.set('type', type);
    
    var url = '<?= base_url("reports/sales/export") ?>?' + params.toString();
    
    if (type === 'pdf') {
        window.open(url, '_blank');
    } else {
        window.location.href = url;
    }
}
</script>
<?= $this->endSection() ?>
