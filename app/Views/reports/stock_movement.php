<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0"><i class="bi bi-arrow-left-right me-2"></i>Stock Movement Report</h5>
            </div>
            <div class="card-body">
                <!-- Filter Form -->
                <form method="get" class="row g-3 mb-4">
                    <div class="col-md-3">
                        <label class="form-label">Start Date</label>
                        <input type="date" name="start_date" class="form-control" value="<?= $startDate ?>">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">End Date</label>
                        <input type="date" name="end_date" class="form-control" value="<?= $endDate ?>">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Product</label>
                        <select name="product_id" class="form-select">
                            <option value="">All Products</option>
                            <?php foreach ($products as $product): ?>
                            <option value="<?= $product['id'] ?>" <?= $selectedProduct == $product['id'] ? 'selected' : '' ?>>
                                <?= $product['product_name'] ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">&nbsp;</label>
                        <button type="submit" class="btn btn-primary w-100">Apply Filters</button>
                    </div>
                </form>
                
                <div class="table-responsive">
                    <table class="table table-hover" id="movementsTable">
                        <thead>
                            <tr>
                                <th>Date</th>
                                <th>Product</th>
                                <th>Type</th>
                                <th>Quantity</th>
                                <th>Previous Stock</th>
                                <th>New Stock</th>
                                <th>Reference</th>
                             </thead>
                        <tbody>
                            <?php foreach ($movements as $movement): ?>
                            <tr>
                                <td><?= date('Y-m-d H:i', strtotime($movement['created_at'])) ?></td>
                                <td><?= $movement['product_name'] ?? 'N/A' ?></td>
                                <td>
                                    <?php if ($movement['movement_type'] == 'Purchase'): ?>
                                        <span class="badge bg-success">+ Purchase</span>
                                    <?php elseif ($movement['movement_type'] == 'Sale'): ?>
                                        <span class="badge bg-danger">- Sale</span>
                                    <?php elseif ($movement['movement_type'] == 'Production'): ?>
                                        <span class="badge bg-info">Production</span>
                                    <?php else: ?>
                                        <span class="badge bg-warning">Adjustment</span>
                                    <?php endif; ?>
                                </td>
                                <td class="<?= $movement['quantity'] >= 0 ? 'text-success' : 'text-danger' ?>">
                                    <?= number_format($movement['quantity'], 2) ?>
                                </td>
                                <td><?= number_format($movement['previous_quantity'], 2) ?></td>
                                <td><?= number_format($movement['new_quantity'], 2) ?></td>
                                <td>
                                    <?= $movement['reference_type'] ?> #<?= $movement['reference_id'] ?>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
$(document).ready(function() {
    $('#movementsTable').DataTable({
        order: [[0, 'desc']],
        pageLength: 50
    });
});
</script>
<?= $this->endSection() ?>