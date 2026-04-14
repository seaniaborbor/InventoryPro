<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<div class="row">
    <div class="col-12">
        <!-- Print Controls -->
        <div class="mb-3 no-print">
            <div class="btn-group">
                <button class="btn btn-primary" onclick="window.print()">
                    <i class="bi bi-printer"></i> Print / Save as PDF
                </button>
                <button class="btn btn-secondary" onclick="window.close()">
                    <i class="bi bi-x-circle"></i> Close
                </button>
            </div>
        </div>

        <!-- Worksheet Content -->
        <div class="card" id="printArea">
            <div class="card-body">
                <!-- Header -->
                <div class="worksheet-header" style="border-bottom: 3px solid #0d6efd; margin-bottom: 20px; padding-bottom: 15px;">
                    <div class="row">
                        <div class="col-8">
                            <h2><?= esc($business['name'] ?? 'Innovative Graphics') ?></h2>
                            <p class="text-muted mb-0">
                                <i class="bi bi-geo-alt"></i> <?= esc($business['address'] ?? 'Broad & Benson Streets, Monrovia, Liberia') ?><br>
                                <i class="bi bi-telephone"></i> <?= esc($business['phone'] ?? '+231-778-651-747') ?> | 
                                <i class="bi bi-envelope"></i> <?= esc($business['email'] ?? 'info@innovativegraphics.com') ?>
                            </p>
                        </div>
                        <div class="col-4 text-end">
                            <h4 class="text-primary">Production Worksheet</h4>
                            <h5 class="mb-0">#<?= esc($job['job_reference']) ?></h5>
                            <?php
                                $status = $job['status'] ?? 'Draft';
                                $badgeClass = match($status) {
                                    'Completed' => 'bg-success',
                                    'Cancelled' => 'bg-secondary',
                                    default => 'bg-warning text-dark'
                                };
                            ?>
                            <span class="badge <?= $badgeClass ?> mt-2" style="font-size: 14px; padding: 5px 12px;"><?= esc($status) ?></span>
                        </div>
                    </div>
                </div>

                <!-- Job Details -->
                <div class="row mb-4">
                    <div class="col-md-6">
                        <table class="table table-sm table-borderless">
                            <tr>
                                <td width="35%"><strong>Job Name:</strong></td>
                                <td><?= esc($job['job_name']) ?></td>
                            </tr>
                            <tr>
                                <td><strong>Production Date:</strong></td>
                                <td><?= date('F j, Y', strtotime($job['production_date'])) ?></td>
                            </tr>
                            <tr>
                                <td><strong>Customer:</strong></td>
                                <td><?= esc($job['customer_name'] ?? 'Walk-in Customer') ?></td>
                            </tr>
                        </table>
                    </div>
                    <div class="col-md-6">
                        <table class="table table-sm table-borderless">
                            <tr>
                                <td width="35%"><strong>Category:</strong></td>
                                <td><?= esc($job['category_name'] ?? 'General') ?></td>
                            </tr>
                            <tr>
                                <td><strong>Quantity Produced:</strong></td>
                                <td><?= number_format($job['quantity_produced'] ?? 0, 2) ?> units</td>
                            </tr>
                            <tr>
                                <td><strong>Created By:</strong></td>
                                <td><?= esc($job['creator_name'] ?? 'System') ?></td>
                            </tr>
                        </table>
                    </div>
                </div>

                <!-- Materials Table -->
                <h5 class="mt-3 mb-3"><i class="bi bi-list-check"></i> Materials Used</h5>
                <div class="table-responsive">
                    <table class="table table-bordered table-sm">
                        <thead class="table-light">
                            <tr>
                                <th>#</th>
                                <th>Material / Product</th>
                                <th class="text-end">Quantity Used</th>
                                <th class="text-end">Unit Cost (<?= esc($job['currency'] ?? 'LRD') ?>)</th>
                                <th class="text-end">Total Cost (<?= esc($job['currency'] ?? 'LRD') ?>)</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($job['materials'])): ?>
                                <tr>
                                    <td colspan="5" class="text-center text-muted py-4">No materials recorded</td>
                                </tr>
                            <?php else: ?>
                                <?php $counter = 1; $totalCost = 0; ?>
                                <?php foreach ($job['materials'] as $material): ?>
                                    <?php 
                                        $lineTotal = ($material['quantity_used'] ?? 0) * ($material['unit_cost'] ?? 0);
                                        $totalCost += $lineTotal;
                                    ?>
                                    <tr>
                                        <td><?= $counter++ ?></td>
                                        <td><strong><?= esc($material['product_name']) ?></strong></td>
                                        <td class="text-end"><?= number_format($material['quantity_used'] ?? 0, 2) ?></td>
                                        <td class="text-end"><?= number_format($material['unit_cost'] ?? 0, 2) ?></td>
                                        <td class="text-end"><?= number_format($lineTotal, 2) ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                        <tfoot class="table-active">
                            <tr>
                                <td colspan="4" class="text-end fw-bold">TOTAL MATERIAL COST:</td>
                                <td class="text-end fw-bold fs-5">
                                    <?= esc($job['currency'] ?? 'LRD') ?> <?= number_format($totalCost ?? 0, 2) ?>
                                </td>
                            </tr>
                        </tfoot>
                    </table>
                </div>

                <!-- Notes -->
                <?php if (!empty($job['notes'])): ?>
                <div class="mt-3">
                    <strong><i class="bi bi-pencil-square"></i> Notes / Instructions:</strong>
                    <div class="border p-3 bg-light rounded mt-2">
                        <?= nl2br(esc($job['notes'])) ?>
                    </div>
                </div>
                <?php endif; ?>

                <!-- Signatures / Footer -->
                <div class="mt-4 pt-3" style="border-top: 1px dashed #dee2e6;">
                    <div class="row mt-4">
                        <div class="col-6 text-start">
                            <p>_________________________<br>
                            <small>Prepared By: <?= esc($job['creator_name'] ?? '_____________') ?></small></p>
                        </div>
                        <div class="col-6 text-end">
                            <p>_________________________<br>
                            <small>Authorized By: _____________</small></p>
                        </div>
                    </div>
                    <div class="text-center mt-3">
                        <p class="text-muted small">
                            <i class="bi bi-qr-code"></i> Generated on: <?= date('Y-m-d H:i:s') ?><br>
                            This is a system-generated production worksheet. Please verify all quantities before production.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
@media print {
    .no-print {
        display: none !important;
    }
    .card {
        border: none !important;
        box-shadow: none !important;
        padding: 0 !important;
        margin: 0 !important;
    }
    .card-body {
        padding: 0 !important;
    }
    .worksheet-header {
        border-bottom: 2px solid #000 !important;
    }
    .btn, .btn-group {
        display: none !important;
    }
    body {
        padding: 0;
        margin: 0;
    }
}
</style>

<script>
// Auto-trigger print dialog when page loads (optional - uncomment if desired)
/*
$(document).ready(function() {
    setTimeout(function() {
        window.print();
    }, 500);
});
*/
</script>

<?= $this->endSection() ?>