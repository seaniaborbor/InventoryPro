<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<?php
$showSingleCurrency = !empty($selectedCurrency);
$activeCurrency = $selectedCurrency ?: 'LRD';
$expenseEntryCount = count($expense_log);
?>
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0"><i class="bi bi-graph-up me-2"></i>Financial Reports</h5>
            </div>
            <div class="card-body">
                <form method="get" class="row g-3 mb-4">
                    <div class="col-md-2">
                        <label class="form-label">Period</label>
                        <select name="period" id="financialPeriodFilter" class="form-select">
                            <option value="all" <?= ($selectedPeriod ?? 'all') === 'all' ? 'selected' : '' ?>>All Records</option>
                            <option value="last_week" <?= ($selectedPeriod ?? '') === 'last_week' ? 'selected' : '' ?>>Last Week</option>
                            <option value="last_month" <?= ($selectedPeriod ?? '') === 'last_month' ? 'selected' : '' ?>>Last Month</option>
                            <option value="last_year" <?= ($selectedPeriod ?? '') === 'last_year' ? 'selected' : '' ?>>Last Year</option>
                            <option value="custom" <?= ($selectedPeriod ?? '') === 'custom' ? 'selected' : '' ?>>Custom Range</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">Start Date</label>
                        <input type="date" name="start_date" id="financialStartDate" class="form-control" value="<?= $startDate ?>">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">End Date</label>
                        <input type="date" name="end_date" id="financialEndDate" class="form-control" value="<?= $endDate ?>">
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
                        <label class="form-label">Expense Category</label>
                        <select name="category" class="form-select">
                            <option value="">All Categories</option>
                            <?php foreach ($expenseCategories as $category): ?>
                            <option value="<?= $category['id'] ?>" <?= ($selectedCategory ?? '') == $category['id'] ? 'selected' : '' ?>>
                                <?= esc($category['category_name']) ?>
                            </option>
                            <?php endforeach; ?>
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
                    <div class="col-12">
                        <div class="d-flex flex-wrap gap-2">
                            <button type="submit" class="btn btn-primary">Apply Filters</button>
                            <a href="<?= base_url('reports/financial') ?>" class="btn btn-outline-secondary">Reset</a>
                            <button type="button" class="btn btn-success" onclick="exportFinancialReport('pdf')">
                                <i class="bi bi-file-pdf"></i> PDF
                            </button>
                            <button type="button" class="btn btn-info" onclick="exportFinancialReport('excel')">
                                <i class="bi bi-file-excel"></i> Excel
                            </button>
                        </div>
                        <small class="text-muted d-block mt-2">
                            Revenue, COGS, and net profit follow the selected date range and currency. Expense category and recorded-by filters refine the expense analysis sections.
                        </small>
                    </div>
                </form>

                <div class="row mb-4">
                    <?php if ($showSingleCurrency): ?>
                        <div class="col-md-3 mb-3">
                            <div class="card bg-primary text-white h-100">
                                <div class="card-body">
                                    <h6 class="card-title">Revenue</h6>
                                    <h3><?= formatCurrency($revenue[$activeCurrency], $activeCurrency) ?></h3>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3 mb-3">
                            <div class="card bg-warning text-dark h-100">
                                <div class="card-body">
                                    <h6 class="card-title">Estimated COGS</h6>
                                    <h3><?= formatCurrency($cogs[$activeCurrency], $activeCurrency) ?></h3>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3 mb-3">
                            <div class="card bg-danger text-white h-100">
                                <div class="card-body">
                                    <h6 class="card-title">Expenses</h6>
                                    <h3><?= formatCurrency($expenses[$activeCurrency], $activeCurrency) ?></h3>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3 mb-3">
                            <div class="card <?= $net_profit[$activeCurrency] >= 0 ? 'bg-success' : 'bg-dark' ?> text-white h-100">
                                <div class="card-body">
                                    <h6 class="card-title">Net Profit</h6>
                                    <h3><?= formatCurrency($net_profit[$activeCurrency], $activeCurrency) ?></h3>
                                </div>
                            </div>
                        </div>
                    <?php else: ?>
                        <div class="col-md-3 mb-3">
                            <div class="card bg-primary text-white h-100">
                                <div class="card-body">
                                    <h6 class="card-title">LRD Net Profit</h6>
                                    <h3><?= formatCurrency($net_profit['LRD'], 'LRD') ?></h3>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3 mb-3">
                            <div class="card bg-success text-white h-100">
                                <div class="card-body">
                                    <h6 class="card-title">USD Net Profit</h6>
                                    <h3><?= formatCurrency($net_profit['USD'], 'USD') ?></h3>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3 mb-3">
                            <div class="card bg-danger text-white h-100">
                                <div class="card-body">
                                    <h6 class="card-title">LRD Expenses</h6>
                                    <h3><?= formatCurrency($expenses['LRD'], 'LRD') ?></h3>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3 mb-3">
                            <div class="card bg-secondary text-white h-100">
                                <div class="card-body">
                                    <h6 class="card-title">USD Expenses</h6>
                                    <h3><?= formatCurrency($expenses['USD'], 'USD') ?></h3>
                                </div>
                            </div>
                        </div>
                    <?php endif; ?>
                    <div class="col-md-4 mb-3">
                        <div class="card border-0 bg-light h-100">
                            <div class="card-body">
                                <h6 class="card-title">Received Purchases Context</h6>
                                <div class="d-flex justify-content-between">
                                    <span>LRD</span>
                                    <strong><?= formatCurrency($received_purchases['LRD'], 'LRD') ?></strong>
                                </div>
                                <div class="d-flex justify-content-between">
                                    <span>USD</span>
                                    <strong><?= formatCurrency($received_purchases['USD'], 'USD') ?></strong>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4 mb-3">
                        <div class="card border-0 bg-light h-100">
                            <div class="card-body">
                                <h6 class="card-title">Expense Entries</h6>
                                <h3><?= number_format($expenseEntryCount) ?></h3>
                                <small class="text-muted">Entries matching the expense filters.</small>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4 mb-3">
                        <div class="card border-0 bg-light h-100">
                            <div class="card-body">
                                <h6 class="card-title">Gross Profit Snapshot</h6>
                                <div class="d-flex justify-content-between">
                                    <span>LRD</span>
                                    <strong><?= formatCurrency($gross_profit['LRD'], 'LRD') ?></strong>
                                </div>
                                <div class="d-flex justify-content-between">
                                    <span>USD</span>
                                    <strong><?= formatCurrency($gross_profit['USD'], 'USD') ?></strong>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row mb-4">
                    <div class="col-lg-8 mb-4">
                        <div class="card h-100">
                            <div class="card-header">
                                <h6 class="mb-0">Financial Chart</h6>
                            </div>
                            <div class="card-body">
                                <canvas id="financialChart" height="320"></canvas>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-4 mb-4">
                        <div class="card h-100">
                            <div class="card-header">
                                <h6 class="mb-0">Financial Summary</h6>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table table-sm align-middle mb-0">
                                        <thead>
                                            <tr>
                                                <th>Metric</th>
                                                <th class="text-end">LRD</th>
                                                <th class="text-end">USD</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr>
                                                <td>Revenue</td>
                                                <td class="text-end"><?= formatCurrency($revenue['LRD'], 'LRD') ?></td>
                                                <td class="text-end"><?= formatCurrency($revenue['USD'], 'USD') ?></td>
                                            </tr>
                                            <tr>
                                                <td>Estimated COGS</td>
                                                <td class="text-end"><?= formatCurrency($cogs['LRD'], 'LRD') ?></td>
                                                <td class="text-end"><?= formatCurrency($cogs['USD'], 'USD') ?></td>
                                            </tr>
                                            <tr>
                                                <td>Gross Profit</td>
                                                <td class="text-end"><?= formatCurrency($gross_profit['LRD'], 'LRD') ?></td>
                                                <td class="text-end"><?= formatCurrency($gross_profit['USD'], 'USD') ?></td>
                                            </tr>
                                            <tr>
                                                <td>Expenses</td>
                                                <td class="text-end"><?= formatCurrency($expenses['LRD'], 'LRD') ?></td>
                                                <td class="text-end"><?= formatCurrency($expenses['USD'], 'USD') ?></td>
                                            </tr>
                                            <tr class="table-active">
                                                <td><strong>Net Profit</strong></td>
                                                <td class="text-end fw-bold <?= $net_profit['LRD'] >= 0 ? 'text-success' : 'text-danger' ?>">
                                                    <?= formatCurrency($net_profit['LRD'], 'LRD') ?>
                                                </td>
                                                <td class="text-end fw-bold <?= $net_profit['USD'] >= 0 ? 'text-success' : 'text-danger' ?>">
                                                    <?= formatCurrency($net_profit['USD'], 'USD') ?>
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                                <div class="alert alert-info mt-3 mb-0">
                                    Estimated COGS is derived from product purchase prices and sales exchange rates where needed.
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row mb-4">
                    <div class="col-lg-6 mb-4">
                        <div class="card h-100">
                            <div class="card-header">
                                <h6 class="mb-0">Expense Categories</h6>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table table-hover table-sm" id="expenseCategoryTable">
                                        <thead>
                                            <tr>
                                                <th>Category</th>
                                                <th>Currency</th>
                                                <th class="text-end">Entries</th>
                                                <th class="text-end">Total</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($expenses_by_category as $item): ?>
                                            <tr>
                                                <td><?= esc($item['category_name']) ?></td>
                                                <td><span class="badge bg-secondary"><?= esc($item['currency']) ?></span></td>
                                                <td class="text-end"><?= number_format($item['expense_count']) ?></td>
                                                <td class="text-end fw-bold"><?= formatCurrency($item['total_amount'], $item['currency']) ?></td>
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
                                <h6 class="mb-0">Expense Activity By User</h6>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table table-hover table-sm" id="expenseUserTable">
                                        <thead>
                                            <tr>
                                                <th>User</th>
                                                <th>Currency</th>
                                                <th class="text-end">Entries</th>
                                                <th class="text-end">Total</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($expenses_by_user as $item): ?>
                                            <tr>
                                                <td><?= esc($item['full_name'] ?: 'Unknown') ?></td>
                                                <td><span class="badge bg-secondary"><?= esc($item['currency']) ?></span></td>
                                                <td class="text-end"><?= number_format($item['expense_count']) ?></td>
                                                <td class="text-end fw-bold"><?= formatCurrency($item['total_amount'], $item['currency']) ?></td>
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
                        <h6 class="mb-0">Expense Log</h6>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover table-sm" id="expenseLogTable">
                                <thead>
                                    <tr>
                                        <th>Date</th>
                                        <th>Category</th>
                                        <th>Description</th>
                                        <th class="text-end">Amount</th>
                                        <th>Currency</th>
                                        <th>Recorded By</th>
                                        <th>Recorded At</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($expense_log as $expense): ?>
                                    <tr>
                                        <td><?= date('Y-m-d', strtotime($expense['expense_date'])) ?></td>
                                        <td><span class="badge bg-secondary"><?= esc($expense['category_name']) ?></span></td>
                                        <td><?= esc($expense['description'] ?: '-') ?></td>
                                        <td class="text-end fw-bold"><?= formatCurrency($expense['amount'], $expense['currency']) ?></td>
                                        <td><?= esc($expense['currency']) ?></td>
                                        <td><?= esc($expense['created_by_name'] ?: 'Unknown') ?></td>
                                        <td><?= date('M d, Y H:i', strtotime($expense['created_at'])) ?></td>
                                        <td>
                                            <button
                                                type="button"
                                                class="btn btn-sm btn-outline-primary view-expense-detail"
                                                data-id="<?= $expense['id'] ?>"
                                                data-category="<?= esc($expense['category_name'], 'attr') ?>"
                                                data-date="<?= esc($expense['expense_date'], 'attr') ?>"
                                                data-description="<?= esc($expense['description'] ?: '-', 'attr') ?>"
                                                data-amount="<?= esc(number_format((float) $expense['amount'], 2, '.', ''), 'attr') ?>"
                                                data-currency="<?= esc($expense['currency'], 'attr') ?>"
                                                data-created-by="<?= esc($expense['created_by_name'] ?: 'Unknown', 'attr') ?>"
                                                data-created-at="<?= esc(date('M d, Y H:i', strtotime($expense['created_at'])), 'attr') ?>"
                                                data-updated-by="<?= esc($expense['updated_by_name'] ?: '-', 'attr') ?>"
                                                data-updated-at="<?= esc($expense['updated_at'] ? date('M d, Y H:i', strtotime($expense['updated_at'])) : '-', 'attr') ?>"
                                                data-receipt="<?= esc($expense['receipt_image'] ? base_url($expense['receipt_image']) : '', 'attr') ?>">
                                                <i class="bi bi-eye"></i> View
                                            </button>
                                            <a href="<?= base_url('expenses/edit/' . $expense['id']) ?>" class="btn btn-sm btn-outline-secondary">
                                                <i class="bi bi-pencil"></i> Edit
                                            </a>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>

                        <?php if (empty($expense_log)): ?>
                            <div class="text-center text-muted py-4">
                                <i class="bi bi-wallet2 fs-1"></i>
                                <p>No expense entries found for the selected filters.</p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<div class="modal fade" id="expenseDetailModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Expense Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label text-muted">Category</label>
                        <div class="fw-semibold" id="expenseDetailCategory">-</div>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label text-muted">Expense Date</label>
                        <div class="fw-semibold" id="expenseDetailDate">-</div>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label text-muted">Amount</label>
                        <div class="fw-semibold" id="expenseDetailAmount">-</div>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label text-muted">Recorded By</label>
                        <div class="fw-semibold" id="expenseDetailCreatedBy">-</div>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label text-muted">Recorded At</label>
                        <div class="fw-semibold" id="expenseDetailCreatedAt">-</div>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label text-muted">Last Updated</label>
                        <div class="fw-semibold" id="expenseDetailUpdatedAt">-</div>
                    </div>
                    <div class="col-12">
                        <label class="form-label text-muted">Description</label>
                        <div class="border rounded p-3 bg-light" id="expenseDetailDescription">-</div>
                    </div>
                    <div class="col-12" id="expenseDetailReceiptWrapper" style="display:none;">
                        <a href="#" id="expenseDetailReceipt" target="_blank" class="btn btn-info">
                            <i class="bi bi-image"></i> View Receipt
                        </a>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<script>
let financialChart;

$(document).ready(function() {
    toggleFinancialCustomDates();
    renderFinancialChart();

    initFinancialTable('#expenseCategoryTable', [[3, 'desc']]);
    initFinancialTable('#expenseUserTable', [[3, 'desc']]);
    initFinancialTable('#expenseLogTable', [[0, 'desc']]);

    $('#financialPeriodFilter').on('change', function() {
        toggleFinancialCustomDates();
    });

    $(document).on('click', '.view-expense-detail', function() {
        const button = $(this);
        $('#expenseDetailCategory').text(button.attr('data-category'));
        $('#expenseDetailDate').text(button.attr('data-date'));
        $('#expenseDetailAmount').text(button.attr('data-currency') + ' ' + Number(button.attr('data-amount')).toLocaleString());
        $('#expenseDetailCreatedBy').text(button.attr('data-created-by'));
        $('#expenseDetailCreatedAt').text(button.attr('data-created-at'));
        $('#expenseDetailUpdatedAt').text(button.attr('data-updated-by') + ' / ' + button.attr('data-updated-at'));
        $('#expenseDetailDescription').text(button.attr('data-description'));

        const receipt = button.attr('data-receipt');
        if (receipt) {
            $('#expenseDetailReceipt').attr('href', receipt);
            $('#expenseDetailReceiptWrapper').show();
        } else {
            $('#expenseDetailReceipt').attr('href', '#');
            $('#expenseDetailReceiptWrapper').hide();
        }

        new bootstrap.Modal(document.getElementById('expenseDetailModal')).show();
    });
});

function toggleFinancialCustomDates() {
    const isCustom = $('#financialPeriodFilter').val() === 'custom';
    $('#financialStartDate, #financialEndDate').prop('disabled', !isCustom);
}

function initFinancialTable(selector, order) {
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

function renderFinancialChart() {
    const chartElement = document.getElementById('financialChart');
    if (!chartElement) {
        return;
    }

    const chartData = <?= json_encode($financial_trend) ?>;
    if (!chartData || !chartData.labels || !chartData.labels.length) {
        return;
    }

    const totalLabels = chartData.labels.length;
    const maxTicks = totalLabels <= 7 ? totalLabels : (totalLabels <= 31 ? 8 : 12);
    const ctx = chartElement.getContext('2d');

    financialChart = new Chart(ctx, {
        type: chartData.mode === 'summary' ? 'bar' : 'line',
        data: {
            labels: chartData.labels,
            datasets: chartData.datasets
        },
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
                            return formatFinancialChartLabel(this.getLabelForValue(value), chartData);
                        }
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

                            return formatFinancialTooltipTitle(context[0].label, chartData);
                        },
                        label: function(context) {
                            const currency = chartData.currency || context.dataset.label;
                            return context.dataset.label + ': ' + currency + ' ' + context.parsed.y.toLocaleString();
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

function formatFinancialChartLabel(label, chartData) {
    if (!label) {
        return '';
    }

    if (chartData.mode === 'summary') {
        return label;
    }

    if (chartData.bucket === 'month') {
        const date = new Date(label + '-01T00:00:00');
        return Number.isNaN(date.getTime()) ? label : date.toLocaleDateString(undefined, { month: 'short', year: '2-digit' });
    }

    const date = new Date(label + 'T00:00:00');
    return Number.isNaN(date.getTime()) ? label : date.toLocaleDateString(undefined, { month: 'short', day: 'numeric' });
}

function formatFinancialTooltipTitle(label, chartData) {
    if (!label || chartData.mode === 'summary') {
        return label;
    }

    if (chartData.bucket === 'month') {
        const date = new Date(label + '-01T00:00:00');
        return Number.isNaN(date.getTime()) ? label : date.toLocaleDateString(undefined, { month: 'long', year: 'numeric' });
    }

    const date = new Date(label + 'T00:00:00');
    return Number.isNaN(date.getTime()) ? label : date.toLocaleDateString(undefined, { weekday: 'short', month: 'short', day: 'numeric', year: 'numeric' });
}

function exportFinancialReport(type) {
    const params = new URLSearchParams(window.location.search);
    params.set('type', type);

    const url = '<?= base_url('reports/financial/export') ?>?' + params.toString();
    if (type === 'pdf') {
        window.open(url, '_blank');
    } else {
        window.location.href = url;
    }
}
</script>
<?= $this->endSection() ?>
