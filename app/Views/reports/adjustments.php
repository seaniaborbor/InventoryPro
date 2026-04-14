<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0"><i class="bi bi-arrow-down-up me-2"></i>Adjustments Report</h5>
            </div>
            <div class="card-body">
                <!-- Filter Form -->
                <form method="get" class="row mb-4">
                    <div class="col-md-3">
                        <label class="form-label">Start Date</label>
                        <input type="date" name="start_date" class="form-control" value="<?= $startDate ?>">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">End Date</label>
                        <input type="date" name="end_date" class="form-control" value="<?= $endDate ?>">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Event Type</label>
                        <select name="event_type" class="form-select">
                            <option value="">All Types</option>
                            <?php foreach ($eventTypes as $type): ?>
                                <option value="<?= $type ?>" <?= $selectedEventType == $type ? 'selected' : '' ?>>
                                    <?= $type ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">Currency</label>
                        <select name="currency" class="form-select">
                            <option value="">All</option>
                            <option value="LRD" <?= $selectedCurrency == 'LRD' ? 'selected' : '' ?>>LRD</option>
                            <option value="USD" <?= $selectedCurrency == 'USD' ? 'selected' : '' ?>>USD</option>
                        </select>
                    </div>
                    <div class="col-md-1">
                        <label class="form-label">&nbsp;</label>
                        <button type="submit" class="btn btn-primary w-100">Filter</button>
                    </div>
                </form>

                <!-- Summary Cards -->
                <div class="row mb-4">
                    <?php foreach ($summaryByType as $currency => $types): ?>
                        <?php if ($currency === 'LRD'): ?>
                            <div class="col-md-6">
                                <div class="card bg-info text-white">
                                    <div class="card-header">LRD Adjustments</div>
                                    <div class="card-body">
                                        <table class="table table-sm table-borderless text-white">
                                            <?php foreach ($types as $type => $value): ?>
                                                <?php if ($type !== 'total' && $value > 0): ?>
                                                    <tr>
                                                        <td><?= $type ?>:</td>
                                                        <td class="text-end">L$ <?= number_format($value, 2) ?></td>
                                                    </tr>
                                                <?php endif; ?>
                                            <?php endforeach; ?>
                                            <tr class="border-top">
                                                <td><strong>Total:</strong></td>
                                                <td class="text-end"><strong>L$ <?= number_format($types['total'], 2) ?></strong></td>
                                            </tr>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        <?php elseif ($currency === 'USD'): ?>
                            <div class="col-md-6">
                                <div class="card bg-success text-white">
                                    <div class="card-header">USD Adjustments</div>
                                    <div class="card-body">
                                        <table class="table table-sm table-borderless text-white">
                                            <?php foreach ($types as $type => $value): ?>
                                                <?php if ($type !== 'total' && $value > 0): ?>
                                                    <tr>
                                                        <td><?= $type ?>:</td>
                                                        <td class="text-end">$ <?= number_format($value, 2) ?></td>
                                                    </tr>
                                                <?php endif; ?>
                                            <?php endforeach; ?>
                                            <tr class="border-top">
                                                <td><strong>Total:</strong></td>
                                                <td class="text-end"><strong>$ <?= number_format($types['total'], 2) ?></strong></td>
                                            </tr>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        <?php endif; ?>
                    <?php endforeach; ?>
                </div>

                <!-- Adjustments Table -->
                <div class="table-responsive">
                    <table class="table table-hover" id="adjustmentsTable">
                        <thead>
                            <tr>
                                <th>Date</th>
                                <th>Product</th>
                                <th>Type</th>
                                <th>Quantity</th>
                                <th>Unit Cost</th>
                                <th>Total Value</th>
                                <th>Currency</th>
                                <th>Reference</th>
                                <th>Customer</th>
                                <th>Source</th>
                            </thead>
                        <tbody>
                            <?php if (empty($adjustments)): ?>
                                <tr>
                                    <td colspan="10" class="text-center text-muted py-4">
                                        No adjustments found for the selected criteria.
                                    </td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($adjustments as $adj): ?>
                                    <tr>
                                        <td><?= date('Y-m-d', strtotime($adj['event_date'])) ?></td>
                                        <td><?= esc($adj['product_name'] ?? 'Unknown') ?></td>
                                        <td>
                                            <span class="badge bg-<?= $adj['event_type'] === 'Damage' ? 'danger' : ($adj['event_type'] === 'Refund' ? 'warning' : ($adj['event_type'] === 'Return' ? 'info' : 'secondary')) ?>">
                                                <?= esc($adj['event_type']) ?>
                                            </span>
                                        </td>
                                        <td class="text-end"><?= number_format($adj['quantity'], 2) ?></td>
                                        <td class="text-end"><?= number_format($adj['unit_cost'], 2) ?></td>
                                        <td class="text-end fw-bold"><?= number_format($adj['total_value'], 2) ?></td>
                                        <td><?= esc($adj['currency']) ?></td>
                                        <td><?= esc($adj['reference'] ?: '-') ?></td>
                                        <td><?= esc($adj['customer_name'] ?: '-') ?></td>
                                        <td>
                                            <?php if ($adj['invoice_number']): ?>
                                                <a href="<?= base_url('sales/view/' . $adj['related_sale_id']) ?>" class="btn btn-sm btn-link">
                                                    Sale: <?= esc($adj['invoice_number']) ?>
                                                </a>
                                            <?php elseif ($adj['job_reference']): ?>
                                                <a href="<?= base_url('production/view/' . $adj['related_production_job_id']) ?>" class="btn btn-sm btn-link">
                                                    Job: <?= esc($adj['job_reference']) ?>
                                                </a>
                                            <?php else: ?>
                                                -
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
$(document).ready(function() {
    $('#adjustmentsTable').DataTable({
        pageLength: 25,
        order: [[0, 'desc']],
        responsive: true
    });
});
</script>
<?= $this->endSection() ?>