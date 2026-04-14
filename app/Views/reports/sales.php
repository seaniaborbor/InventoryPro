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
                <form method="get" class="row mb-4">
                    <div class="col-md-2">
                        <label class="form-label">Period</label>
                        <select name="period" class="form-select">
                            <option value="all" <?= $selectedPeriod == 'all' ? 'selected' : '' ?>>All Time</option>
                            <option value="last_7_days" <?= $selectedPeriod == 'last_7_days' ? 'selected' : '' ?>>Last 7 Days</option>
                            <option value="last_month" <?= $selectedPeriod == 'last_month' ? 'selected' : '' ?>>Last Month</option>
                            <option value="last_year" <?= $selectedPeriod == 'last_year' ? 'selected' : '' ?>>Last Year</option>
                            <option value="custom" <?= $selectedPeriod == 'custom' ? 'selected' : '' ?>>Custom Range</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">Start Date</label>
                        <input type="date" name="start_date" class="form-control" value="<?= $startDate ?>">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">End Date</label>
                        <input type="date" name="end_date" class="form-control" value="<?= $endDate ?>">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">Currency</label>
                        <select name="currency" class="form-select">
                            <option value="">All Currencies</option>
                            <option value="LRD" <?= $selectedCurrency == 'LRD' ? 'selected' : '' ?>>LRD</option>
                            <option value="USD" <?= $selectedCurrency == 'USD' ? 'selected' : '' ?>>USD</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">Product</label>
                        <select name="product_id" class="form-select">
                            <option value="">All Products</option>
                            <?php foreach ($products as $p): ?>
                                <option value="<?= $p['id'] ?>" <?= $selectedProduct == $p['id'] ? 'selected' : '' ?>>
                                    <?= esc($p['product_name']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">&nbsp;</label>
                        <button type="submit" class="btn btn-primary w-100">Filter</button>
                    </div>
                </form>

                <!-- Summary Cards -->
                <div class="row mb-4">
                    <div class="col-md-3">
                        <div class="card bg-primary text-white">
                            <div class="card-body">
                                <h6 class="card-title"><i class="bi bi-cart"></i> Gross Sales</h6>
                                <h3 class="mb-0">
                                    <?php if ($selectedCurrency == 'USD'): ?>
                                        $ <?= number_format($grossSalesUSD ?? 0, 2) ?>
                                    <?php else: ?>
                                        L$ <?= number_format($grossSalesLRD ?? 0, 2) ?>
                                    <?php endif; ?>
                                </h3>
                                <small>Total sales before returns</small>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card bg-danger text-white">
                            <div class="card-body">
                                <h6 class="card-title"><i class="bi bi-arrow-return-left"></i> Refunds</h6>
                                <h3 class="mb-0">
                                    <?php if ($selectedCurrency == 'USD'): ?>
                                        $ <?= number_format($refundsUSD ?? 0, 2) ?>
                                    <?php else: ?>
                                        L$ <?= number_format($refundsLRD ?? 0, 2) ?>
                                    <?php endif; ?>
                                </h3>
                                <small>Money returned to customers</small>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card bg-info text-white">
                            <div class="card-body">
                                <h6 class="card-title"><i class="bi bi-arrow-repeat"></i> Returns</h6>
                                <h3 class="mb-0">
                                    <?php if ($selectedCurrency == 'USD'): ?>
                                        $ <?= number_format($returnsUSD ?? 0, 2) ?>
                                    <?php else: ?>
                                        L$ <?= number_format($returnsLRD ?? 0, 2) ?>
                                    <?php endif; ?>
                                </h3>
                                <small>Products returned (value)</small>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card bg-success text-white">
                            <div class="card-body">
                                <h6 class="card-title"><i class="bi bi-cash-stack"></i> Net Sales</h6>
                                <h3 class="mb-0">
                                    <?php if ($selectedCurrency == 'USD'): ?>
                                        $ <?= number_format($netSalesUSD ?? 0, 2) ?>
                                    <?php else: ?>
                                        L$ <?= number_format($netSalesLRD ?? 0, 2) ?>
                                    <?php endif; ?>
                                </h3>
                                <small>Gross Sales - Refunds</small>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Sales by Payment Method -->
                <div class="row mb-4">
                    <div class="col-md-6">
                        <div class="card">
                            <div class="card-header">
                                <h6 class="mb-0"><i class="bi bi-credit-card"></i> Sales by Payment Method</h6>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table table-sm">
                                        <thead>
                                            <tr>
                                                <th>Payment Method</th>
                                                <th class="text-end">Count</th>
                                                <th class="text-end">Amount</th>
                                            </thead>
                                        <tbody>
                                            <?php foreach ($salesByMethod as $method): ?>
                                            <tr>
                                                <td><?= esc($method['payment_method']) ?></td>
                                                <td class="text-end"><?= $method['count'] ?></td>
                                                <td class="text-end">
                                                    <?= $selectedCurrency == 'USD' ? '$' : 'L$' ?> <?= number_format($method['total'], 2) ?>
                                                </td>
                                            </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="card">
                            <div class="card-header">
                                <h6 class="mb-0"><i class="bi bi-bar-chart"></i> Daily Sales Trend</h6>
                            </div>
                            <div class="card-body">
                                <canvas id="dailySalesChart" height="200"></canvas>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Top Products -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h6 class="mb-0"><i class="bi bi-trophy"></i> Top Selling Products</h6>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-sm">
                                <thead>
                                    <tr>
                                        <th>Product</th>
                                        <th class="text-end">Quantity Sold</th>
                                        <th class="text-end">Revenue</th>
                                    </thead>
                                <tbody>
                                    <?php foreach ($topProducts as $product): ?>
                                    <tr>
                                        <td><?= esc($product['product_name']) ?> (<?= esc($product['sku']) ?>)</td>
                                        <td class="text-end"><?= number_format($product['total_quantity'], 2) ?></td>
                                        <td class="text-end">
                                            <?= $selectedCurrency == 'USD' ? '$' : 'L$' ?> <?= number_format($product['total_revenue'], 2) ?>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- Sales Log Table -->
                <div class="card">
                    <div class="card-header">
                        <h6 class="mb-0"><i class="bi bi-list"></i> Sales Transactions</h6>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover" id="salesTable">
                                <thead>
                                    <tr>
                                        <th>Date</th>
                                        <th>Invoice #</th>
                                        <th>Customer</th>
                                        <th>Items</th>
                                        <th>Subtotal</th>
                                        <th>Discount</th>
                                        <th>Tax</th>
                                        <th>Total</th>
                                        <th>Payment</th>
                                        <th>Status</th>
                                        <th>Seller</th>
                                    </thead>
                                <tbody>
                                    <?php foreach ($salesLog as $sale): ?>
                                    <tr>
                                        <td><?= date('Y-m-d', strtotime($sale['sale_date'])) ?></td>
                                        <td>
                                            <a href="<?= base_url('sales/view/' . $sale['id']) ?>">
                                                <?= esc($sale['invoice_number']) ?>
                                            </a>
                                        </td>
                                        <td><?= esc($sale['customer_name'] ?? 'Walk-in') ?></td>
                                        <td class="text-center"><?= $sale['items_count'] ?? 0 ?></td>
                                        <td class="text-end"><?= $selectedCurrency == 'USD' ? '$' : 'L$' ?> <?= number_format($sale['subtotal'], 2) ?></td>
                                        <td class="text-end"><?= $selectedCurrency == 'USD' ? '$' : 'L$' ?> <?= number_format($sale['discount'], 2) ?></td>
                                        <td class="text-end"><?= $selectedCurrency == 'USD' ? '$' : 'L$' ?> <?= number_format($sale['tax'], 2) ?></td>
                                        <td class="text-end fw-bold"><?= $selectedCurrency == 'USD' ? '$' : 'L$' ?> <?= number_format($sale['total_amount'], 2) ?></td>
                                        <td><?= esc($sale['payment_method']) ?></td>
                                        <td>
                                            <?php if ($sale['payment_status'] == 'Paid'): ?>
                                                <span class="badge bg-success">Paid</span>
                                            <?php elseif ($sale['payment_status'] == 'Partial'): ?>
                                                <span class="badge bg-warning">Partial</span>
                                            <?php else: ?>
                                                <span class="badge bg-danger">Unpaid</span>
                                            <?php endif; ?>
                                        </td>
                                        <td><?= esc($sale['seller_name'] ?? '-') ?></td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
$(document).ready(function() {
    // Initialize DataTable
    $('#salesTable').DataTable({
        pageLength: 25,
        order: [[0, 'desc']],
        responsive: true
    });
    
    // Daily Sales Chart
    <?php if (!empty($dailySales)): ?>
    const dailyCtx = document.getElementById('dailySalesChart').getContext('2d');
    new Chart(dailyCtx, {
        type: 'line',
        data: {
            labels: <?= json_encode(array_column($dailySales, 'date')) ?>,
            datasets: [{
                label: 'Sales Amount',
                data: <?= json_encode(array_column($dailySales, 'total_combined')) ?>,
                borderColor: '#0d6efd',
                backgroundColor: 'rgba(13, 110, 253, 0.1)',
                fill: true,
                tension: 0.4
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: true,
            plugins: {
                legend: { position: 'top' }
            }
        }
    });
    <?php endif; ?>
});
</script>
<?= $this->endSection() ?>