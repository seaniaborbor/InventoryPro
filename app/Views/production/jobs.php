<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">
                    <i class="bi bi-gear me-2"></i>Production Jobs
                </h5>
                <a href="<?= base_url('production/create') ?>" class="btn btn-primary btn-sm">
                    <i class="bi bi-plus-circle"></i> New Production Job
                </a>
            </div>
            
            <div class="card-body">
                <!-- Flash Messages -->
                <?php if (session()->getFlashdata('success')): ?>
                    <div class="alert alert-success alert-dismissible fade show">
                        <?= session()->getFlashdata('success') ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>

                <?php if (session()->getFlashdata('error')): ?>
                    <div class="alert alert-danger alert-dismissible fade show">
                        <?= session()->getFlashdata('error') ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>

                <div class="table-responsive">
                    <table class="table table-hover align-middle" id="jobsTable">
                        <thead class="table-light">
                            <tr>
                                <th>Job #</th>
                                <th>Job Name</th>
                                <th>Production Date</th>
                                <th>Finished Product</th>
                                <th>Qty Produced</th>
                                <th>Total Material Cost</th>
                                <th>Status</th>
                                <th class="text-center">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($jobs)): ?>
                                <tr>
                                    <td colspan="8" class="text-center text-muted py-5">
                                        No production jobs found yet.<br><br>
                                        <a href="<?= base_url('production/create') ?>" class="btn btn-outline-primary">
                                            <i class="bi bi-plus-circle"></i> Create First Production Job
                                        </a>
                                    </td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($jobs as $job): ?>
                                    <tr>
                                        <td><strong><?= esc($job['job_reference'] ?? '') ?></strong></td>
                                        <td><?= esc($job['job_name'] ?? '') ?></td>
                                        <td><?= !empty($job['production_date']) ? date('M j, Y', strtotime($job['production_date'])) : '—' ?></td>
                                        <td>
                                            <?= !empty($job['finished_product_name']) 
                                                ? esc($job['finished_product_name']) 
                                                : '<span class="text-muted">Consumables only</span>' ?>
                                        </td>
                                        <td class="text-end">
                                            <?= number_format($job['quantity_produced'] ?? 0, 2) ?>
                                        </td>
                                        <td class="text-end fw-medium">
                                            <?= esc($job['currency'] ?? 'LRD') ?> 
                                            <?= number_format($job['total_material_cost'] ?? 0, 2) ?>
                                        </td>
                                        <td>
                                            <span class="badge bg-<?= strtolower($job['status'] ?? 'draft') === 'completed' ? 'success' : 
                                                (strtolower($job['status'] ?? 'draft') === 'in progress' ? 'warning' : 'secondary') ?>">
                                                <?= esc($job['status'] ?? 'Draft') ?>
                                            </span>
                                        </td>
                                        <td class="text-center">
                                            <div class="btn-group btn-group-sm">
                                                <a href="<?= base_url('production/view/' . ($job['id'] ?? 0)) ?>" 
                                                   class="btn btn-outline-info" title="View">
                                                    <i class="bi bi-eye"></i>
                                                </a>
                                                <?php if (strtolower($job['status'] ?? 'draft') !== 'completed'): ?>
                                                    <a href="<?= base_url('production/edit/' . ($job['id'] ?? 0)) ?>" 
                                                       class="btn btn-outline-warning" title="Edit">
                                                        <i class="bi bi-pencil"></i>
                                                    </a>
                                                <?php endif; ?>
                                            </div>
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
    // Enable DataTables for better UX
    if ($.fn.DataTable) {
        $('#jobsTable').DataTable({
            "pageLength": 25,
            "order": [[2, "desc"]],   // Sort by production date
            "language": {
                "search": "Search jobs:",
                "lengthMenu": "Show _MENU_ entries"
            }
        });
    }
});
</script>

<?= $this->endSection() ?>