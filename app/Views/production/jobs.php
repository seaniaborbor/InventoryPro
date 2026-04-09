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

               
                <div class="bg-light rounded p-3 mb-4">
                    <form method="get" action="<?= base_url('production/jobs') ?>" class="row g-3">
                        <div class="col-12 col-md-2">
                            <label class="form-label small fw-semibold">Search</label>
                            <input type="text" name="search" class="form-control form-control-sm"
                                   placeholder="Job # or name..."
                                   value="<?= esc($filters['search'] ?? '') ?>">
                        </div>
                        <div class="col-6 col-md-2">
                            <label class="form-label small fw-semibold">Status</label>
                            <select name="status" class="form-select form-select-sm">
                                <option value="all">All Statuses</option>
                                <option value="Draft" <?= ($filters['status'] ?? '') === 'Draft' ? 'selected' : '' ?>>Draft</option>
                                <option value="In Progress" <?= ($filters['status'] ?? '') === 'In Progress' ? 'selected' : '' ?>>In Progress</option>
                                <option value="Completed" <?= ($filters['status'] ?? '') === 'Completed' ? 'selected' : '' ?>>Completed</option>
                            </select>
                        </div>
                        <div class="col-6 col-md-2">
                            <label class="form-label small fw-semibold">Customer</label>
                            <select name="customer_id" class="form-select form-select-sm">
                                <option value="">All Customers</option>
                                <?php if (!empty($customers)): ?>
                                    <?php foreach ($customers as $customer): ?>
                                        <option value="<?= $customer['id'] ?>"
                                            <?= ($filters['customer_id'] ?? '') == $customer['id'] ? 'selected' : '' ?>>
                                            <?= esc($customer['customer_name']) ?>
                                        </option>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </select>
                        </div>
                        <div class="col-6 col-md-2">
                            <label class="form-label small fw-semibold">Category</label>
                            <select name="category_id" class="form-select form-select-sm">
                                <option value="">All Categories</option>
                                <?php if (!empty($categories)): ?>
                                    <?php foreach ($categories as $category): ?>
                                        <option value="<?= $category['id'] ?>"
                                            <?= ($filters['category_id'] ?? '') == $category['id'] ? 'selected' : '' ?>>
                                            <?= esc($category['category_name']) ?>
                                        </option>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </select>
                        </div>
                        <div class="col-6 col-md-2">
                            <label class="form-label small fw-semibold">Date From</label>
                            <input type="date" name="date_from" class="form-control form-control-sm"
                                   value="<?= esc($filters['date_from'] ?? '') ?>">
                        </div>
                        <div class="col-6 col-md-2">
                            <label class="form-label small fw-semibold">Date To</label>
                            <input type="date" name="date_to" class="form-control form-control-sm"
                                   value="<?= esc($filters['date_to'] ?? '') ?>">
                        </div>
                        <div class="col-12 d-flex gap-2">
                            <button type="submit" class="btn btn-primary btn-sm">
                                <i class="bi bi-funnel"></i> Apply Filters
                            </button>
                            <a href="<?= base_url('production/jobs') ?>" class="btn btn-outline-secondary btn-sm">
                                <i class="bi bi-x-circle"></i> Clear Filters
                            </a>
                        </div>
                    </form>
                </div>

              
                <?php if (!empty($filters['search']) || !empty($filters['status']) || !empty($filters['customer_id']) || !empty($filters['category_id']) || !empty($filters['date_from']) || !empty($filters['date_to'])): ?>
                    <div class="mb-3 d-flex flex-wrap gap-1 align-items-center">
                        <small class="text-muted me-2">Active filters:</small>
                        <?php if (!empty($filters['search'])): ?>
                            <span class="badge bg-primary">Search: <?= esc($filters['search']) ?></span>
                        <?php endif; ?>
                        <?php if (!empty($filters['status']) && $filters['status'] !== 'all'): ?>
                            <span class="badge bg-info">Status: <?= esc($filters['status']) ?></span>
                        <?php endif; ?>
                        <?php if (!empty($filters['customer_id'])): ?>
                            <?php $custName = array_filter($customers, fn($c) => $c['id'] == $filters['customer_id']); ?>
                            <span class="badge bg-info">Customer: <?= esc(reset($custName)['customer_name'] ?? 'Unknown') ?></span>
                        <?php endif; ?>
                        <?php if (!empty($filters['category_id'])): ?>
                            <?php $catName = array_filter($categories, fn($c) => $c['id'] == $filters['category_id']); ?>
                            <span class="badge bg-info">Category: <?= esc(reset($catName)['category_name'] ?? 'Unknown') ?></span>
                        <?php endif; ?>
                        <?php if (!empty($filters['date_from'])): ?>
                            <span class="badge bg-info">From: <?= esc($filters['date_from']) ?></span>
                        <?php endif; ?>
                        <?php if (!empty($filters['date_to'])): ?>
                            <span class="badge bg-info">To: <?= esc($filters['date_to']) ?></span>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>

                
                <div class="table-responsive d-none d-xl-block">
                    <table class="table table-hover align-middle" id="jobsTable">
                        <thead class="table-light">
                            <tr>
                                <th>Job #</th>
                                <th>Job Name</th>
                                <th>Customer</th>
                                <th>Category</th>
                                <th>Production Date</th>
                                <th>Total Cost</th>
                                <th>Pmt Status</th>
                                <th>Status</th>
                                <th>Last Edited</th>
                                <th class="text-center">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($jobs)): ?>
                                <tr>
                                    <td colspan="9" class="text-center text-muted py-5">
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
                                        <td>
                                            <?php if (!empty($job['customer_id'])): ?>
                                                <?= esc($job['customer_name'] ?? '—') ?>
                                            <?php else: ?>
                                                <span class="text-muted">—</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <?php if (!empty($job['production_category_id'])): ?>
                                                <span class="badge bg-secondary"><?= esc($job['category_name'] ?? '—') ?></span>
                                            <?php else: ?>
                                                <span class="text-muted">—</span>
                                            <?php endif; ?>
                                        </td>
                                        <td><?= !empty($job['production_date']) ? date('M j, Y', strtotime($job['production_date'])) : '—' ?></td>
                                        <td class="text-end fw-medium">
                                            <?= esc($job['currency'] ?? 'LRD') ?>
                                            <?= number_format($job['total_material_cost'] ?? 0, 2) ?>
                                        </td>
                                        <td>
                                            <span class="badge bg-<?=
                                                strtolower(trim($job['payment_status'] ?? 'unpaid')) === 'paid' ? 'success' :
                                                (strtolower(trim($job['payment_status'] ?? 'unpaid')) === 'partially paid' ? 'warning' :
                                                (strtolower(trim($job['payment_status'] ?? 'unpaid')) === 'pid' ? 'success' :
                                                (strtolower(trim($job['payment_status'] ?? 'unpaid')) === 'unpaid' ? 'danger' : 'secondary')))
                                            ?>">
                                                <?= esc($job['payment_status'] ?? 'Unpaid') ?>
                                            </span>
                                        </td>
                                          <td>
                                            <span class="badge bg-<?= strtolower($job['status'] ?? 'draft') === 'completed' ? 'success' :
                                                (strtolower($job['status'] ?? 'draft') === 'in progress' ? 'warning' : 'secondary') ?>">
                                                <?= esc($job['status'] ?? 'Draft') ?>
                                            </span>
                                        </td>
                                        <td>
                                            <?php if (!empty($job['updated_at'])): ?>
                                                <small title="<?= esc($job['updated_at_display'] ?? '') ?>">
                                                    <?= esc($job['updater_name'] ?? 'Unknown') ?>
                                                </small>
                                            <?php else: ?>
                                                <small class="text-muted">Not edited</small>
                                            <?php endif; ?>
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

               
                <div class="d-xl-none">
                    <?php if (empty($jobs)): ?>
                        <div class="text-center text-muted py-5">
                            No production jobs found yet.<br><br>
                            <a href="<?= base_url('production/create') ?>" class="btn btn-outline-primary">
                                <i class="bi bi-plus-circle"></i> Create First Production Job
                            </a>
                        </div>
                    <?php else: ?>
                        <?php foreach ($jobs as $job): ?>
                            <div class="card mb-2 border shadow-sm">
                                <div class="card-body py-2">
                                    <div class="d-flex justify-content-between align-items-start mb-1">
                                        <div>
                                            <strong><?= esc($job['job_reference'] ?? '') ?></strong>
                                            <span class="text-muted ms-1">— <?= esc($job['job_name'] ?? '') ?></span>
                                        </div>
                                        <span class="badge bg-<?= strtolower($job['status'] ?? 'draft') === 'completed' ? 'success' :
                                            (strtolower($job['status'] ?? 'draft') === 'in progress' ? 'warning' : 'secondary') ?>">
                                            <?= esc($job['status'] ?? 'Draft') ?>
                                        </span>
                                    </div>
                                    <div class="text-muted small">
                                        <?php if (!empty($job['customer_id'])): ?>
                                            Customer: <?= esc($job['customer_name'] ?? '—') ?>
                                            <?php if (!empty($job['production_category_id'])): ?>
                                                <span class="ms-2">|</span>
                                            <?php endif; ?>
                                        <?php endif; ?>
                                        <?php if (!empty($job['production_category_id'])): ?>
                                            <span class="badge bg-secondary"><?= esc($job['category_name'] ?? '—') ?></span>
                                        <?php endif; ?>
                                    </div>
                                    <div class="text-muted small mt-1">
                                        <?= !empty($job['production_date']) ? date('M j, Y', strtotime($job['production_date'])) : '—' ?>
                                        <span class="ms-2">|</span>
                                        <?= esc($job['currency'] ?? 'LRD') ?> <?= number_format($job['total_material_cost'] ?? 0, 2) ?>
                                        <?php if (!empty($job['updated_at'])): ?>
                                            <span class="ms-2">|</span>
                                            Edited by <?= esc($job['updater_name'] ?? 'Unknown') ?>
                                        <?php endif; ?>
                                    </div>
                                    <div class="text-end mt-1">
                                        <div class="btn-group btn-group-sm">
                                            <a href="<?= base_url('production/view/' . ($job['id'] ?? 0)) ?>"
                                               class="btn btn-outline-info">
                                                <i class="bi bi-eye"></i> View
                                            </a>
                                            <?php if (strtolower($job['status'] ?? 'draft') !== 'completed'): ?>
                                                <a href="<?= base_url('production/edit/' . ($job['id'] ?? 0)) ?>"
                                                   class="btn btn-outline-warning">
                                                    <i class="bi bi-pencil"></i> Edit
                                                </a>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
$(document).ready(function() {
    if ($.fn.DataTable) {
        $('#jobsTable').DataTable({
            "pageLength": 25,
            "order": [[4, "desc"]],
            "language": {
                "search": "Search jobs:",
                "lengthMenu": "Show _MENU_ entries"
            }
        });
    }
});
</script>

<?= $this->endSection() ?>
