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
                    <div class="col-md-6">
                        <strong>Job Name:</strong><br>
                        <?= esc($job['job_name']) ?>
                    </div>
                    <div class="col-md-6">
                        <strong>Production Date:</strong><br>
                        <?= date('M j, Y', strtotime($job['production_date'])) ?>
                    </div>
                </div>

                <div class="row mb-4">
                    <div class="col-md-6">
                        <strong>Finished Product:</strong><br>
                        <?= !empty($job['finished_product_name'])
                            ? esc($job['finished_product_name'])
                            : '<span class="text-muted">Consumables only (No finished product)</span>' ?>
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
                <!-- Left: Edit button (Draft only) -->
                <div>
                    <?php if ($status === 'Draft'): ?>
                        <a href="<?= base_url('production/edit/' . $job['id']) ?>" class="btn btn-warning btn-sm">
                            <i class="bi bi-pencil"></i> Edit Job
                        </a>
                    <?php endif; ?>
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
</script>

<?= $this->endSection() ?>