<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header bg-warning">
                <h5 class="mb-0"><i class="bi bi-exclamation-triangle me-2"></i>Low Stock Alerts</h5>
            </div>
            <div class="card-body">
                <?php if (empty($products)): ?>
                    <div class="alert alert-success">
                        <i class="bi bi-check-circle"></i> No low stock items found. All products are above minimum stock levels.
                    </div>
                <?php else: ?>
                    <div class="alert alert-warning">
                        <i class="bi bi-exclamation-triangle"></i> The following <?= count($products) ?> product(s) are below their minimum stock levels.
                    </div>
                    
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Product Name</th>
                                    <th>SKU</th>
                                    <th>Current Stock</th>
                                    <th>Minimum Stock</th>
                                    <th>Shortage</th>
                                    <th>Unit</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($products as $product): ?>
                                <tr class="table-warning">
                                    <td><strong><?= $product['product_name'] ?></strong></td>
                                    <td><?= $product['sku'] ?></td>
                                    <td class="text-danger fw-bold"><?= number_format($product['quantity'], 2) ?></td>
                                    <td><?= number_format($product['minimum_stock'], 2) ?></td>
                                    <td><?= number_format($product['minimum_stock'] - $product['quantity'], 2) ?></td>
                                    <td><?= $product['unit_name'] ?? '-' ?></td>
                                    <td>
                                        <button class="btn btn-sm btn-primary" onclick="editProduct(<?= $product['id'] ?>)">
                                            <i class="bi bi-pencil"></i> Edit
                                        </button>
                                        <button class="btn btn-sm btn-success" onclick="restock(<?= $product['id'] ?>)">
                                            <i class="bi bi-cart-plus"></i> Restock
                                        </button>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<script>
function editProduct(id) {
    window.location.href = '/inventory/products/edit/' + id;
}

function restock(id) {
    window.location.href = '/purchases/create?product=' + id;
}
</script>
<?= $this->endSection() ?>