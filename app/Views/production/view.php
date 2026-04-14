<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">
                    <i class="bi bi-gear me-2"></i>Production Job #<?= esc($job['job_reference']) ?>
                    <?php
                        $status = $job['status'] ?? 'Draft';
                        $badgeClass = match($status) {
                            'Completed' => 'bg-success',
                            'Cancelled' => 'bg-secondary',
                            default     => 'bg-warning text-dark'
                        };
                    ?>
                    <span class="badge <?= $badgeClass ?> ms-2"><?= esc($status) ?></span>
                </h5>
                <a href="<?= base_url('production/jobs') ?>" class="btn btn-secondary btn-sm">
                    <i class="bi bi-arrow-left"></i> Back to List
                </a>
            </div>

            <div class="card-body">
                <div class="row mb-4">
                    <div class="col-md-4">
                        <strong>Job Name:</strong><br>
                        <?= esc($job['job_name']) ?>
                    </div>
                    <div class="col-md-4">
                        <strong>Production Date:</strong><br>
                        <?= date('M j, Y', strtotime($job['production_date'])) ?>
                    </div>
                    <div class="col-md-4">
                        <strong>Customer:</strong><br>
                        <?php if (!empty($job['customer_id'])): ?>
                            <?= esc($job['customer_name'] ?? 'Unknown') ?>
                        <?php else: ?>
                            <span class="text-muted">No customer assigned</span>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="row mb-4">
                    <div class="col-md-6">
                        <strong>Category:</strong><br>
                        <?php if (!empty($job['production_category_id'])): ?>
                            <?php
                            $categoryName = $job['category_name'] ?? 'Unknown';
                            ?>
                            <?= esc($categoryName) ?>
                        <?php else: ?>
                            <span class="text-muted">No category assigned</span>
                        <?php endif; ?>
                    </div>
                    <div class="col-md-6">
                        <strong>Quantity Produced:</strong><br>
                        <?= number_format($job['quantity_produced'] ?? 0, 2) ?>
                    </div>
                </div>

                <div class="row mb-4">
                    <div class="col-md-6">
                        <strong>Currency:</strong><br>
                        <?= esc($job['currency'] ?? 'LRD') ?>
                    </div>
                    <div class="col-md-6">
                        <strong>Total Material Cost:</strong><br>
                        <span class="fw-bold fs-5">
                            <?= esc($job['currency'] ?? 'LRD') ?>
                            <?= number_format($job['total_material_cost'] ?? 0, 2) ?>
                        </span>
                    </div>
                </div>

                <?php if (!empty($job['notes'])): ?>
                <div class="mb-4">
                    <strong>Notes:</strong><br>
                    <div class="border p-3 bg-light rounded">
                        <?= nl2br(esc($job['notes'])) ?>
                    </div>
                </div>
                <?php endif; ?>

                <!-- Adjustments (Damage during production) -->
                <div class="card mb-4" id="adjustmentsSection">
                    <div class="card-header bg-light d-flex justify-content-between align-items-center">
                        <h6 class="mb-0"><i class="bi bi-exclamation-triangle me-2"></i>Damage / Waste</h6>
                        <a href="<?= base_url('adjustments/from-job/' . $job['id']) ?>" class="btn btn-sm btn-outline-danger">
                            <i class="bi bi-plus-circle"></i> Record Damage
                        </a>
                    </div>
                    <div class="card-body">
                        <div id="adjustmentList" class="text-muted"><em>Loading...</em></div>
                    </div>
                </div>

                <!-- Audit Information -->
                <div class="card bg-light mb-4">
                    <div class="card-body">
                        <h6 class="card-title mb-3"><i class="bi bi-info-circle me-2"></i>Job Activity</h6>
                        <div class="row">
                            <div class="col-md-6">
                                <strong>Created By:</strong> <?= esc($job['creator_name'] ?? 'Unknown') ?><br>
                                <span class="text-muted small"><?= esc($job['created_at_display'] ?? '') ?></span>
                            </div>
                            <div class="col-md-6">
                                <strong>Last Updated:</strong><br>
                                <?php if (!empty($job['updated_at'])): ?>
                                    By <?= esc($job['updater_name'] ?? 'Unknown') ?><br>
                                    <span class="text-muted small"><?= esc($job['updated_at_display'] ?? '') ?></span>
                                <?php else: ?>
                                    <span class="text-muted small">Not updated yet</span>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Materials Used -->
                <h6 class="mb-3"><i class="bi bi-list-check me-2"></i>Materials Used</h6>
                <div class="table-responsive">
                    <table class="table table-bordered table-hover">
                        <thead class="table-light">
                            <tr>
                                <th>Material</th>
                                <th class="text-end">Quantity Used</th>
                                <th class="text-end">Unit Cost</th>
                                <th class="text-end">Total Cost</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($job['materials'])): ?>
                                <tr>
                                    <td colspan="4" class="text-center text-muted py-4">No materials recorded for this job.</td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($job['materials'] as $material): ?>
                                    <tr>
                                        <td><strong><?= esc($material['product_name']) ?></strong></td>
                                        <td class="text-end"><?= number_format($material['quantity_used'] ?? 0, 2) ?></td>
                                        <td class="text-end"><?= number_format($material['unit_cost'] ?? 0, 2) ?></td>
                                        <td class="text-end fw-bold"><?= number_format($material['total_cost'] ?? 0, 2) ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="card-footer d-flex justify-content-between align-items-center">
                <!-- Left: Edit and Print buttons -->
                <div>
                    <?php if ($status === 'Draft'): ?>
                        <a href="<?= base_url('production/edit/' . $job['id']) ?>" class="btn btn-warning btn-sm">
                            <i class="bi bi-pencil"></i> Edit Job
                        </a>
                    <?php endif; ?>
                    
                    <!-- PRINT BUTTON - Available for all statuses -->
                    <a href="<?= base_url('production/print/' . $job['id']) ?>" target="_blank" class="btn btn-info btn-sm text-white">
                        <i class="bi bi-printer"></i> Print Worksheet
                    </a>
                </div>

                <!-- Right: Action buttons -->
                <div class="d-flex gap-2">
                    <?php if ($status === 'Draft'): ?>
                        <button class="btn btn-danger btn-sm" onclick="deleteJob(<?= $job['id'] ?>)">
                            <i class="bi bi-trash"></i> Delete
                        </button>
                        <button class="btn btn-secondary btn-sm" onclick="cancelJob(<?= $job['id'] ?>)">
                            <i class="bi bi-x-circle"></i> Cancel Job
                        </button>
                        <button class="btn btn-success" onclick="completeJob(<?= $job['id'] ?>)">
                            <i class="bi bi-check-circle"></i> Mark as Completed
                        </button>
                    <?php elseif ($status === 'Cancelled'): ?>
                        <button class="btn btn-danger btn-sm" onclick="deleteJob(<?= $job['id'] ?>)">
                            <i class="bi bi-trash"></i> Delete
                        </button>
                    <?php endif; ?>
                </div>
            </div>

        </div>
    </div>
</div>

<script>
const CSRF_TOKEN = '<?= csrf_hash() ?>';
const CSRF_NAME  = '<?= csrf_token() ?>';

function ajaxAction(url, confirmMsg, onSuccess) {
    if (!confirm(confirmMsg)) return;

    $.ajax({
        url: url,
        type: 'POST',
        contentType: 'application/json',
        dataType: 'json',
        headers: { 'X-Requested-With': 'XMLHttpRequest' },
        data: JSON.stringify({ [CSRF_NAME]: CSRF_TOKEN }),
        success: function(response) {
            if (response.status === 'success') {
                alert('✅ ' + response.message);
                onSuccess();
            } else {
                alert('❌ ' + response.message);
            }
        },
        error: function(xhr) {
            alert('❌ Server error. Check console for details.');
            console.error(xhr.responseText);
        }
    });
}

function completeJob(id) {
    ajaxAction(
        '<?= base_url('production/complete/') ?>' + id,
        '⚠️ Completing this job will deduct all materials from inventory and add the finished product to stock. This cannot be undone. Continue?',
        () => location.reload()
    );
}

function cancelJob(id) {
    ajaxAction(
        '<?= base_url('production/cancel/') ?>' + id,
        'Cancel this production job?',
        () => location.reload()
    );
}

function deleteJob(id) {
    ajaxAction(
        '<?= base_url('production/delete/') ?>' + id,
        '🗑️ Permanently delete this production job and all its materials? This cannot be undone.',
        () => window.location.href = '<?= base_url('production/jobs') ?>'
    );
}

// Load adjustments for this production job
$(document).ready(function() {
    const jobId = <?= $job['id'] ?>;
    
    $.ajax({
        url: '<?= base_url('adjustments/api/get-by-job/') ?>' + jobId,
        type: 'GET',
        dataType: 'json',
        success: function(response) {
            if (response.status === 'success' && response.data && response.data.length > 0) {
                let html = '<div class="table-responsive"><table class="table table-sm table-bordered">';
                html += '<thead><tr><th>Date</th><th>Product</th><th>Type</th><th>Quantity</th><th>Total Value</th><th>Reference</th></tr></thead><tbody>';
                
                response.data.forEach(function(adj) {
                    html += '<tr>';
                    html += '<td>' + new Date(adj.event_date).toLocaleDateString() + '</td>';
                    html += '<td>' + (adj.product_name || 'Product #' + adj.product_id) + '</td>';
                    html += '<td><span class="badge bg-danger">' + adj.event_type + '</span></td>';
                    html += '<td>' + parseFloat(adj.quantity).toFixed(2) + '</td>';
                    html += '<td>' + adj.currency + ' ' + parseFloat(adj.total_value).toFixed(2) + '</td>';
                    html += '<td>' + (adj.reference || '-') + '</td>';
                    html += '</tr>';
                });
                
                html += '</tbody></table></div>';
                $('#adjustmentList').html(html);
            } else {
                $('#adjustmentList').html('<em class="text-muted">No damage/waste recorded for this job.</em>');
            }
        },
        error: function() {
            $('#adjustmentList').html('<em class="text-muted">Unable to load adjustment data.</em>');
        }
    });
});
</script>

<?= $this->endSection() ?>