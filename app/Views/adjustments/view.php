<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0"><i class="bi bi-arrow-down-up me-2"></i>Adjustment #<?= esc($event['id']) ?>
                    <?php
                        $colors = ['Damage' => 'danger', 'Refund' => 'warning', 'Theft' => 'dark', 'Return' => 'success', 'Other' => 'secondary'];
                        $c = $colors[$event['event_type']] ?? 'secondary';
                    ?>
                    <span class="badge bg-<?= $c ?> ms-2"><?= esc($event['event_type']) ?></span>
                </h5>
                <a href="<?= base_url('adjustments') ?>" class="btn btn-secondary btn-sm"><i class="bi bi-arrow-left"></i> Back</a>
            </div>
            <div class="card-body">
                <div class="row mb-3">
                    <div class="col-md-4">
                        <strong>Product:</strong><br><?= esc($event['product_name'] ?? '—') ?>
                    </div>
                    <div class="col-md-4">
                        <strong>Quantity:</strong><br><?= number_format($event['quantity'], 2) ?>
                    </div>
                    <div class="col-md-4">
                        <strong>Value:</strong><br><?= esc($event['currency'] ?? 'LRD') ?> <?= number_format($event['total_value'], 2) ?>
                    </div>
                </div>
                <div class="row mb-3">
                    <div class="col-md-4">
                        <strong>Unit Cost:</strong><br><?= number_format($event['unit_cost'] ?? 0, 2) ?>
                    </div>
                    <div class="col-md-4">
                        <strong>Adjust Stock:</strong><br><?= $event['adjust_stock'] ? 'Yes' : 'No (record only)' ?>
                    </div>
                    <div class="col-md-4">
                        <strong>Date:</strong><br><?= date('M j, Y H:i', strtotime($event['event_date'])) ?>
                    </div>
                </div>
                <?php if (!empty($event['customer_name'])): ?>
                <div class="row mb-3">
                    <div class="col-md-6">
                        <strong>Customer:</strong><br><?= esc($event['customer_name']) ?>
                    </div>
                </div>
                <?php endif; ?>
                <?php if (!empty($event['reference'])): ?>
                <div class="row mb-3">
                    <div class="col-md-6">
                        <strong>Reference:</strong><br><?= esc($event['reference']) ?>
                    </div>
                </div>
                <?php endif; ?>
                <?php if (!empty($event['description'])): ?>
                <div class="mb-3">
                    <strong>Description:</strong>
                    <div class="border p-3 bg-light rounded"><?= nl2br(esc($event['description'])) ?></div>
                </div>
                <?php endif; ?>

                <!-- Audit info -->
                <hr>
                <div class="row">
                    <div class="col-md-6">
                        <strong>Logged By:</strong> <?= esc($event['created_by_name'] ?? 'Unknown') ?><br>
                        <small class="text-muted"><?= !empty($event['created_at']) ? date('M j, Y H:i', strtotime($event['created_at'])) : '' ?></small>
                    </div>
                    <div class="col-md-6">
                        <strong>Last Updated:</strong><br>
                        <?php if (!empty($event['updated_by'])): ?>
                            By <?= esc($event['updated_by_name'] ?? 'Unknown') ?><br>
                            <small class="text-muted"><?= !empty($event['updated_at']) ? date('M j, Y H:i', strtotime($event['updated_at'])) : '' ?></small>
                        <?php else: ?>
                            <small class="text-muted">Not updated</small>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?= $this->endSection() ?>
