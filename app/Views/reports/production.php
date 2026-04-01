<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<?php
$showSingleCurrency = !empty($selectedCurrency);
$activeCurrency = $selectedCurrency ?: 'LRD';
?>
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0"><i class="bi bi-gear me-2"></i>Production Reports</h5>
            </div>
            <div class="card-body">
                <form method="get" class="row g-3 mb-4">
                    <div class="col-md-2">
                        <label class="form-label">Period</label>
                        <select name="period" id="productionPeriodFilter" class="form-select">
                            <option value="all" <?= ($selectedPeriod ?? 'all') === 'all' ? 'selected' : '' ?>>All Records</option>
                            <option value="last_week" <?= ($selectedPeriod ?? '') === 'last_week' ? 'selected' : '' ?>>Last Week</option>
                            <option value="last_month" <?= ($selectedPeriod ?? '') === 'last_month' ? 'selected' : '' ?>>Last Month</option>
                            <option value="last_year" <?= ($selectedPeriod ?? '') === 'last_year' ? 'selected' : '' ?>>Last Year</option>
                            <option value="custom" <?= ($selectedPeriod ?? '') === 'custom' ? 'selected' : '' ?>>Custom Range</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">Start Date</label>
                        <input type="date" name="start_date" id="productionStartDate" class="form-control" value="<?= $startDate ?>">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">End Date</label>
                        <input type="date" name="end_date" id="productionEndDate" class="form-control" value="<?= $endDate ?>">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">Status</label>
                        <select name="status" class="form-select">
                            <option value="">All Statuses</option>
                            <option value="Draft" <?= ($selectedStatus ?? '') === 'Draft' ? 'selected' : '' ?>>Draft</option>
                            <option value="Completed" <?= ($selectedStatus ?? '') === 'Completed' ? 'selected' : '' ?>>Completed</option>
                            <option value="Cancelled" <?= ($selectedStatus ?? '') === 'Cancelled' ? 'selected' : '' ?>>Cancelled</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">Currency</label>
                        <select name="currency" class="form-select">
                            <option value="">All Currencies</option>
                            <option value="LRD" <?= ($selectedCurrency ?? '') === 'LRD' ? 'selected' : '' ?>>LRD Only</option>
                            <option value="USD" <?= ($selectedCurrency ?? '') === 'USD' ? 'selected' : '' ?>>USD Only</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">Recorded By</label>
                        <select name="created_by" class="form-select">
                            <option value="">All Users</option>
                            <?php foreach ($users as $user): ?>
                            <option value="<?= $user['id'] ?>" <?= ($selectedUser ?? '') == $user['id'] ? 'selected' : '' ?>>
                                <?= esc($user['full_name']) ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Finished Product</label>
                        <select name="finished_product_id" class="form-select">
                            <option value="">All Finished Products</option>
                            <?php foreach ($products as $product): ?>
                            <option value="<?= $product['id'] ?>" <?= ($selectedFinishedProduct ?? '') == $product['id'] ? 'selected' : '' ?>>
                                <?= esc($product['product_name']) ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-8 d-flex align-items-end gap-2">
                        <button type="submit" class="btn btn-primary">Apply Filters</button>
                        <a href="<?= base_url('reports/production') ?>" class="btn btn-outline-secondary">Reset</a>
                        <button type="button" class="btn btn-success" onclick="exportProductionReport('pdf')">
                            <i class="bi bi-file-pdf"></i> PDF
                        </button>
                        <button type="button" class="btn btn-info" onclick="exportProductionReport('excel')">
                            <i class="bi bi-file-excel"></i> Excel
                        </button>
                    </div>
                </form>

                <div class="row mb-4">
                    <div class="col-md-3 mb-3">
                        <div class="card bg-primary text-white h-100">
                            <div class="card-body">
                                <h6 class="card-title">Total Jobs</h6>
                                <h3><?= number_format($costSummary['job_count']) ?></h3>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3 mb-3">
                        <div class="card bg-success text-white h-100">
                            <div class="card-body">
                                <h6 class="card-title">Completed Jobs</h6>
                                <h3><?= number_format($costSummary['completed_count']) ?></h3>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3 mb-3">
                        <div class="card bg-warning text-dark h-100">
                            <div class="card-body">
                                <h6 class="card-title">Draft Jobs</h6>
                                <h3><?= number_format($costSummary['draft_count']) ?></h3>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3 mb-3">
                        <div class="card bg-secondary text-white h-100">
                            <div class="card-body">
                                <h6 class="card-title">Cancelled Jobs</h6>
                                <h3><?= number_format($costSummary['cancelled_count']) ?></h3>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4 mb-3">
                        <div class="card border-0 bg-light h-100">
                            <div class="card-body">
                                <h6 class="card-title">Material Cost LRD</h6>
                                <h3><?= formatCurrency($costSummary['total_cost_lrd'], 'LRD') ?></h3>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4 mb-3">
                        <div class="card border-0 bg-light h-100">
                            <div class="card-body">
                                <h6 class="card-title">Material Cost USD</h6>
                                <h3><?= formatCurrency($costSummary['total_cost_usd'], 'USD') ?></h3>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4 mb-3">
                        <div class="card border-0 bg-light h-100">
                            <div class="card-body">
                                <h6 class="card-title">Quantity Produced</h6>
                                <h3><?= number_format($costSummary['quantity_produced'], 2) ?></h3>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row mb-4">
                    <div class="col-lg-8 mb-4">
                        <div class="card h-100">
                            <div class="card-header">
                                <h6 class="mb-0">Production Activity Trend</h6>
                            </div>
                            <div class="card-body">
                                <canvas id="productionChart" height="320"></canvas>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-4 mb-4">
                        <div class="card h-100">
                            <div class="card-header">
                                <h6 class="mb-0">Status Summary</h6>
                            </div>
                            <div class="card-body">
                                <table class="table table-sm mb-0">
                                    <tbody>
                                        <tr><td>Draft</td><td class="text-end fw-bold"><?= number_format($statusSummary['Draft'] ?? 0) ?></td></tr>
                                        <tr><td>Completed</td><td class="text-end fw-bold"><?= number_format($statusSummary['Completed'] ?? 0) ?></td></tr>
                                        <tr><td>Cancelled</td><td class="text-end fw-bold"><?= number_format($statusSummary['Cancelled'] ?? 0) ?></td></tr>
                                    </tbody>
                                </table>
                                <div class="alert alert-info mt-3 mb-0">
                                    Job counts are safe to compare across filters even when currency is mixed.
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row mb-4">
                    <div class="col-lg-6 mb-4">
                        <div class="card h-100">
                            <div class="card-header">
                                <h6 class="mb-0">Material Usage Summary</h6>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table table-hover table-sm" id="materialUsageTable">
                                        <thead>
                                            <tr>
                                                <th>Material</th>
                                                <th>Currency</th>
                                                <th class="text-end">Jobs</th>
                                                <th class="text-end">Qty Used</th>
                                                <th class="text-end">Total Cost</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($materialUsage as $material): ?>
                                            <tr>
                                                <td>
                                                    <strong><?= esc($material['product_name']) ?></strong><br>
                                                    <small class="text-muted"><?= esc($material['sku']) ?></small>
                                                </td>
                                                <td><span class="badge bg-secondary"><?= esc($material['currency']) ?></span></td>
                                                <td class="text-end"><?= number_format($material['jobs_count']) ?></td>
                                                <td class="text-end"><?= number_format($material['total_quantity'], 2) ?></td>
                                                <td class="text-end fw-bold"><?= formatCurrency($material['total_cost'], $material['currency']) ?></td>
                                            </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-6 mb-4">
                        <div class="card h-100">
                            <div class="card-header">
                                <h6 class="mb-0">Finished Product Summary</h6>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table table-hover table-sm" id="finishedProductTable">
                                        <thead>
                                            <tr>
                                                <th>Finished Product</th>
                                                <th>Currency</th>
                                                <th class="text-end">Jobs</th>
                                                <th class="text-end">Qty Produced</th>
                                                <th class="text-end">Material Cost</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($finishedProductSummary as $item): ?>
                                            <tr>
                                                <td><?= esc($item['product_name']) ?></td>
                                                <td><span class="badge bg-secondary"><?= esc($item['currency']) ?></span></td>
                                                <td class="text-end"><?= number_format($item['job_count']) ?></td>
                                                <td class="text-end"><?= number_format($item['total_quantity'], 2) ?></td>
                                                <td class="text-end fw-bold"><?= formatCurrency($item['total_cost'], $item['currency']) ?></td>
                                            </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row mb-4">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-header">
                                <h6 class="mb-0">Production Activity By User</h6>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table table-hover table-sm" id="productionUserTable">
                                        <thead>
                                            <tr>
                                                <th>User</th>
                                                <th>Currency</th>
                                                <th class="text-end">Jobs</th>
                                                <th class="text-end">Qty Produced</th>
                                                <th class="text-end">Material Cost</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($productionByUser as $item): ?>
                                            <tr>
                                                <td><?= esc($item['full_name'] ?: 'Unknown') ?></td>
                                                <td><span class="badge bg-secondary"><?= esc($item['currency']) ?></span></td>
                                                <td class="text-end"><?= number_format($item['job_count']) ?></td>
                                                <td class="text-end"><?= number_format($item['total_quantity'], 2) ?></td>
                                                <td class="text-end fw-bold"><?= formatCurrency($item['total_cost'], $item['currency']) ?></td>
                                            </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card">
                    <div class="card-header">
                        <h6 class="mb-0">Production Job Log</h6>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover table-sm" id="productionLogTable">
                                <thead>
                                    <tr>
                                        <th>Date</th>
                                        <th>Job Ref</th>
                                        <th>Job Name</th>
                                        <th>Finished Product</th>
                                        <th>Status</th>
                                        <th>Currency</th>
                                        <th class="text-end">Qty Produced</th>
                                        <th class="text-end">Material Cost</th>
                                        <th>Recorded By</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($jobs as $job): ?>
                                    <tr>
                                        <td><?= date('Y-m-d', strtotime($job['production_date'])) ?></td>
                                        <td><strong><?= esc($job['job_reference']) ?></strong></td>
                                        <td><?= esc($job['job_name']) ?></td>
                                        <td><?= esc($job['finished_product_name'] ?: 'Consumables only') ?></td>
                                        <td>
                                            <span class="badge <?= $job['status'] === 'Completed' ? 'bg-success' : ($job['status'] === 'Cancelled' ? 'bg-secondary' : 'bg-warning text-dark') ?>">
                                                <?= esc($job['status']) ?>
                                            </span>
                                        </td>
                                        <td><?= esc($job['currency']) ?></td>
                                        <td class="text-end"><?= number_format($job['quantity_produced'], 2) ?></td>
                                        <td class="text-end fw-bold"><?= formatCurrency($job['total_material_cost'], $job['currency']) ?></td>
                                        <td><?= esc($job['created_by_name'] ?: 'Unknown') ?></td>
                                        <td>
                                            <a href="<?= base_url('production/view/' . $job['id']) ?>" class="btn btn-sm btn-outline-primary">
                                                <i class="bi bi-eye"></i> View
                                            </a>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>

                        <?php if (empty($jobs)): ?>
                            <div class="text-center text-muted py-4">
                                <i class="bi bi-gear fs-1"></i>
                                <p>No production jobs found for the selected filters.</p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
let productionChart;

$(document).ready(function() {
    toggleProductionCustomDates();
    renderProductionChart();

    initProductionTable('#materialUsageTable', [[4, 'desc']]);
    initProductionTable('#finishedProductTable', [[3, 'desc']]);
    initProductionTable('#productionUserTable', [[2, 'desc']]);
    initProductionTable('#productionLogTable', [[0, 'desc']]);

    $('#productionPeriodFilter').on('change', function() {
        toggleProductionCustomDates();
    });
});

function toggleProductionCustomDates() {
    const isCustom = $('#productionPeriodFilter').val() === 'custom';
    $('#productionStartDate, #productionEndDate').prop('disabled', !isCustom);
}

function initProductionTable(selector, order) {
    if (!$.fn.DataTable.isDataTable(selector)) {
        $(selector).DataTable({
            pageLength: 20,
            lengthChange: false,
            ordering: true,
            searching: true,
            info: true,
            order: order
        });
    }
}

function renderProductionChart() {
    const chartElement = document.getElementById('productionChart');
    if (!chartElement) {
        return;
    }

    const chartData = <?= json_encode($productionTrend) ?>;
    if (!chartData || !chartData.labels || !chartData.labels.length) {
        return;
    }

    const totalLabels = chartData.labels.length;
    const maxTicks = totalLabels <= 7 ? totalLabels : (totalLabels <= 31 ? 8 : 12);
    const ctx = chartElement.getContext('2d');

    productionChart = new Chart(ctx, {
        type: 'line',
        data: chartData,
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                x: {
                    ticks: {
                        autoSkip: true,
                        maxTicksLimit: maxTicks,
                        maxRotation: totalLabels > 12 ? 45 : 0,
                        minRotation: 0,
                        callback: function(value) {
                            return formatProductionChartLabel(this.getLabelForValue(value));
                        }
                    }
                },
                y: {
                    beginAtZero: true,
                    ticks: {
                        precision: 0
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

                            return formatProductionTooltipTitle(context[0].label);
                        }
                    }
                },
                legend: {
                    position: 'top'
                }
            }
        }
    });
}

function formatProductionChartLabel(label) {
    if (!label) {
        return '';
    }

    const date = new Date(label + 'T00:00:00');
    return Number.isNaN(date.getTime()) ? label : date.toLocaleDateString(undefined, { month: 'short', day: 'numeric' });
}

function formatProductionTooltipTitle(label) {
    if (!label) {
        return '';
    }

    const date = new Date(label + 'T00:00:00');
    return Number.isNaN(date.getTime()) ? label : date.toLocaleDateString(undefined, { weekday: 'short', month: 'short', day: 'numeric', year: 'numeric' });
}

function exportProductionReport(type) {
    const params = new URLSearchParams(window.location.search);
    params.set('type', type);

    const url = '<?= base_url('reports/production/export') ?>?' + params.toString();
    if (type === 'pdf') {
        window.open(url, '_blank');
    } else {
        window.location.href = url;
    }
}
</script>
<?= $this->endSection() ?>
