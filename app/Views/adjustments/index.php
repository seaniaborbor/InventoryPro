<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0"><i class="bi bi-arrow-down-up me-2"></i>Adjustments / Events</h5>
                <a href="<?= base_url('adjustments/create') ?>" class="btn btn-primary btn-sm">
                    <i class="bi bi-plus-circle"></i> New Adjustment
                </a>
            </div>
            <div class="card-body">

                <!-- Filters -->
                <form method="get" class="row g-2 mb-3 align-items-end">
                    <div class="col-md-3">
                        <label class="form-label small">Product</label>
                        <select name="product_id" class="form-select form-select-sm">
                            <option value="">All Products</option>
                            <?php foreach ($products as $p): ?>
                                <option value="<?= $p['id'] ?>" <?= $filters['product_id'] == $p['id'] ? 'selected' : '' ?>><?= esc($p['product_name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label small">Type</label>
                        <select name="event_type" class="form-select form-select-sm">
                            <option value="">All</option>
                            <?php foreach (['Damage', 'Refund', 'Theft', 'Return', 'Other'] as $t): ?>
                                <option value="<?= $t ?>" <?= $filters['event_type'] === $t ? 'selected' : '' ?>><?= $t ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label small">From</label>
                        <input type="date" name="start_date" class="form-control form-control-sm" value="<?= esc($filters['start_date']) ?>">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label small">To</label>
                        <input type="date" name="end_date" class="form-control form-control-sm" value="<?= esc($filters['end_date']) ?>">
                    </div>
                    <div class="col-md-1">
                        <button class="btn btn-sm btn-primary w-100">Filter</button>
                    </div>
                </form>

                <!-- Summary cards -->
                <?php if (!empty($summary)): ?>
                <div class="row g-2 mb-3">
                    <?php foreach ($summary as $type => $info): ?>
                    <div class="col-md-auto">
                        <div class="card border-0 shadow-sm p-2 text-center" style="min-width:140px">
                            <small class="text-muted"><?= esc($type) ?></small>
                            <strong><?= $info['event_count'] ?> events</strong>
                            <small><?= number_format($info['total_value'], 2) ?></small>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
                <?php endif; ?>

                <!-- Table -->
                <div class="table-responsive">
                    <table class="table table-hover align-middle" id="adjustmentsTable">
                        <thead class="table-light">
                            <tr>
                                <th>#</th>
                                <th>Type</th>
                                <th>Product</th>
                                <th>Qty</th>
                                <th>Value</th>
                                <th>Date</th>
                                <th>Logged By</th>
                                <th class="text-center">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($events)): ?>
                                <tr><td colspan="8" class="text-center text-muted py-4">No adjustments found.</td></tr>
                            <?php else: ?>
                                <?php foreach ($events as $e): ?>
                                <tr>
                                    <td><?= esc($e['id']) ?></td>
                                    <td>
                                        <?php
                                            $colors = ['Damage' => 'danger', 'Refund' => 'warning', 'Theft' => 'dark', 'Return' => 'success', 'Other' => 'secondary'];
                                            $c = $colors[$e['event_type']] ?? 'secondary';
                                        ?>
                                        <span class="badge bg-<?= $c ?>"><?= esc($e['event_type']) ?></span>
                                    </td>
                                    <td><?= esc($e['product_name'] ?? '—') ?></td>
                                    <td class="text-end"><?= number_format($e['quantity'], 2) ?></td>
                                    <td class="text-end"><?= esc($e['currency'] ?? 'LRD') ?> <?= number_format($e['total_value'], 2) ?></td>
                                    <td><small><?= date('M j, Y H:i', strtotime($e['event_date'])) ?></small></td>
                                    <td><small><?= esc($e['created_by_name'] ?? '—') ?></small></td>
                                    <td class="text-center">
                                        <div class="btn-group btn-group-sm">
                                            <a href="<?= base_url('adjustments/view/' . $e['id']) ?>" class="btn btn-outline-info" title="View"><i class="bi bi-eye"></i></a>
                                            <a href="<?= base_url('adjustments/edit/' . $e['id']) ?>" class="btn btn-outline-warning" title="Edit"><i class="bi bi-pencil"></i></a>
                                        </div>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>

                <?= $pager->links() ?>
            </div>
        </div>
    </div>
</div>

<script>
$(document).ready(function() {
    if ($.fn.DataTable) {
        $('#adjustmentsTable').DataTable({
            pageLength: 20,
            order: [[5, 'desc']],
            columnDefs: [{ orderable: false, targets: [7] }],
            language: { search: 'Search:', lengthMenu: 'Show _MENU_' }
        });
    }
});
</script>

<?= $this->endSection() ?>
