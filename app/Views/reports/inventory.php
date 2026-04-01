<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0"><i class="bi bi-box-seam me-2"></i>Inventory Report</h5>
                <div class="d-flex gap-2">
                    <button type="button" class="btn btn-sm btn-danger" onclick="exportInventoryReport('pdf')">
                        <i class="bi bi-file-earmark-pdf"></i> Export PDF
                    </button>
                    <button type="button" class="btn btn-sm btn-success" onclick="exportInventoryReport('excel')">
                        <i class="bi bi-file-earmark-excel"></i> Export Excel
                    </button>
                </div>
            </div>
            <div class="card-body">
                <form method="get" class="row g-3 mb-4">
                    <div class="col-md-4">
                        <label class="form-label">Category</label>
                        <select name="category" class="form-select">
                            <option value="">All Categories</option>
                            <?php foreach ($categories as $cat): ?>
                            <option value="<?= $cat['id'] ?>" <?= ($categoryId ?? '') == $cat['id'] ? 'selected' : '' ?>>
                                <?= esc($cat['category_name']) ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Stock Status</label>
                        <select name="stock_status" class="form-select">
                            <option value="">All Products</option>
                            <option value="normal" <?= ($stockStatus ?? '') === 'normal' ? 'selected' : '' ?>>In Stock Only</option>
                            <option value="low" <?= ($stockStatus ?? '') === 'low' ? 'selected' : '' ?>>Low Stock Only</option>
                            <option value="out" <?= ($stockStatus ?? '') === 'out' ? 'selected' : '' ?>>Out of Stock Only</option>
                        </select>
                    </div>
                    <div class="col-md-4 d-flex align-items-end gap-2">
                        <button type="submit" class="btn btn-primary">Apply Filters</button>
                        <a href="<?= base_url('reports/inventory') ?>" class="btn btn-outline-secondary">Reset</a>
                    </div>
                </form>

                <div class="row mb-4">
                    <div class="col-md-3 mb-3">
                        <div class="card bg-primary text-white h-100">
                            <div class="card-body text-center">
                                <h4><?= number_format($summary['total_products']) ?></h4>
                                <small>Total Products</small>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3 mb-3">
                        <div class="card bg-success text-white h-100">
                            <div class="card-body text-center">
                                <h4><?= number_format($summary['total_stock'], 2) ?></h4>
                                <small>Total Stock Units</small>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3 mb-3">
                        <div class="card bg-warning text-dark h-100">
                            <div class="card-body text-center">
                                <h4><?= number_format($summary['low_stock_count']) ?></h4>
                                <small>Low Stock Items</small>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3 mb-3">
                        <div class="card bg-danger text-white h-100">
                            <div class="card-body text-center">
                                <h4><?= number_format($summary['out_of_stock_count']) ?></h4>
                                <small>Out of Stock</small>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4 mb-3">
                        <div class="card border-0 bg-light h-100">
                            <div class="card-body text-center">
                                <h5><?= formatCurrency($summary['purchase_value'], 'LRD') ?></h5>
                                <small>Stock Value at Purchase Cost</small>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4 mb-3">
                        <div class="card border-0 bg-light h-100">
                            <div class="card-body text-center">
                                <h5><?= formatCurrency($summary['selling_value'], 'LRD') ?></h5>
                                <small>Stock Value at Selling Price</small>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4 mb-3">
                        <div class="card border-0 bg-light h-100">
                            <div class="card-body text-center">
                                <h5><?= formatCurrency($summary['potential_margin'], 'LRD') ?></h5>
                                <small>Potential Gross Margin</small>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row mb-4">
                    <div class="col-lg-6 mb-4">
                        <div class="card h-100">
                            <div class="card-header">
                                <h6 class="mb-0">Inventory By Category</h6>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table table-hover table-sm" id="inventoryCategoryTable">
                                        <thead>
                                            <tr>
                                                <th>Category</th>
                                                <th class="text-end">Products</th>
                                                <th class="text-end">Stock</th>
                                                <th class="text-end">Purchase Value</th>
                                                <th class="text-end">Selling Value</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($categorySummary as $item): ?>
                                            <tr>
                                                <td><?= esc($item['category_name']) ?></td>
                                                <td class="text-end"><?= number_format($item['product_count']) ?></td>
                                                <td class="text-end"><?= number_format($item['total_stock'], 2) ?></td>
                                                <td class="text-end"><?= formatCurrency($item['purchase_value'], 'LRD') ?></td>
                                                <td class="text-end fw-bold"><?= formatCurrency($item['selling_value'], 'LRD') ?></td>
                                            </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-6 mb-4">
                        <div class="card h-100">
                            <div class="card-header">
                                <h6 class="mb-0">Recent Stock Movements</h6>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table table-hover table-sm" id="inventoryMovementTable">
                                        <thead>
                                            <tr>
                                                <th>Date</th>
                                                <th>Product</th>
                                                <th>Type</th>
                                                <th class="text-end">Qty</th>
                                                <th>User</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($recentMovements as $movement): ?>
                                            <tr>
                                                <td><?= date('Y-m-d H:i', strtotime($movement['created_at'])) ?></td>
                                                <td><?= esc($movement['product_name'] ?: 'Unknown') ?></td>
                                                <td><?= esc($movement['movement_type']) ?></td>
                                                <td class="text-end"><?= number_format($movement['quantity'], 2) ?></td>
                                                <td><?= esc($movement['created_by_name'] ?: 'System') ?></td>
                                            </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card">
                    <div class="card-header">
                        <h6 class="mb-0">Inventory Stock Snapshot</h6>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover table-sm" id="inventoryTable">
                                <thead>
                                    <tr>
                                        <th>Product Name</th>
                                        <th>SKU</th>
                                        <th>Category</th>
                                        <th class="text-end">Current Stock</th>
                                        <th class="text-end">Min Stock</th>
                                        <th class="text-end">Purchase Price</th>
                                        <th class="text-end">Selling Price</th>
                                        <th class="text-end">Purchase Value</th>
                                        <th class="text-end">Selling Value</th>
                                        <th>Status</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($products as $product): ?>
                                    <tr>
                                        <td>
                                            <strong><?= esc($product['product_name']) ?></strong>
                                            <?php if (!empty($product['description'])): ?>
                                                <br><small class="text-muted"><?= esc(mb_strimwidth($product['description'], 0, 60, '...')) ?></small>
                                            <?php endif; ?>
                                        </td>
                                        <td><code><?= esc($product['sku']) ?></code></td>
                                        <td><?= esc($product['category_name'] ?: 'Uncategorized') ?></td>
                                        <td class="text-end"><?= number_format($product['quantity'], 2) ?> <?= esc($product['unit_symbol'] ?? $product['unit_name'] ?? '') ?></td>
                                        <td class="text-end"><?= number_format($product['minimum_stock'], 2) ?> <?= esc($product['unit_symbol'] ?? $product['unit_name'] ?? '') ?></td>
                                        <td class="text-end"><?= formatCurrency($product['purchase_price'], 'LRD') ?></td>
                                        <td class="text-end"><?= formatCurrency($product['selling_price'], 'LRD') ?></td>
                                        <td class="text-end"><?= formatCurrency($product['stock_value_purchase'], 'LRD') ?></td>
                                        <td class="text-end fw-bold"><?= formatCurrency($product['stock_value_selling'], 'LRD') ?></td>
                                        <td>
                                            <span class="badge <?= $product['stock_status'] === 'out' ? 'bg-danger' : ($product['stock_status'] === 'low' ? 'bg-warning text-dark' : 'bg-success') ?>">
                                                <?= esc($product['stock_status_label']) ?>
                                            </span>
                                        </td>
                                        <td>
                                            <a href="<?= base_url('inventory/products/view/' . $product['id']) ?>" class="btn btn-sm btn-outline-primary">
                                                <i class="bi bi-eye"></i> View
                                            </a>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>

                        <?php if (empty($products)): ?>
                            <div class="text-center text-muted py-4">
                                <i class="bi bi-box-seam fs-1"></i>
                                <p>No inventory items match the selected filters.</p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
$(document).ready(function() {
    initInventoryTable('#inventoryCategoryTable', [[4, 'desc']]);
    initInventoryTable('#inventoryMovementTable', [[0, 'desc']]);
    initInventoryTable('#inventoryTable', [[0, 'asc']]);
});

function initInventoryTable(selector, order) {
    if (!$.fn.DataTable.isDataTable(selector)) {
        $(selector).DataTable({
            pageLength: 20,
            lengthChange: false,
            ordering: true,
            searching: true,
            info: true,
            order: order
        });
    }
}

function exportInventoryReport(type) {
    const params = new URLSearchParams(window.location.search);
    params.set('type', type);

    const url = '<?= base_url('reports/inventory/export') ?>?' + params.toString();
    if (type === 'pdf') {
        window.open(url, '_blank');
    } else {
        window.location.href = url;
    }
}
</script>
<?= $this->endSection() ?>
