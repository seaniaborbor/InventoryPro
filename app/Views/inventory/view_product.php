<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<div class="row">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0"><i class="bi bi-box-seam me-2"></i>Product Details</h5>
                <div>
                    <a href="<?= base_url('inventory/products/edit/' . $product['id']) ?>"
                        class="btn btn-primary btn-sm">
                        <i class="bi bi-pencil"></i> Edit
                    </a>
                    <a href="<?= base_url('inventory/products') ?>" class="btn btn-secondary btn-sm">
                        <i class="bi bi-arrow-left"></i> Back
                    </a>
                </div>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-4 text-center">
                        <?php if ($product['product_image']): ?>
                            <img src="<?= base_url($product['product_image']) ?>" alt="<?= $product['product_name'] ?>"
                                class="img-fluid rounded" style="max-height: 200px;">
                        <?php else: ?>
                            <div class="bg-light p-5 rounded text-center">
                                <i class="bi bi-image" style="font-size: 4rem;"></i>
                                <p class="text-muted mt-2">No image available</p>
                            </div>
                        <?php endif; ?>

                        <?php if ($product['barcode']): ?>
                            <div class="mt-3">
                                <small class="text-muted">Barcode:</small>
                                <div class="fw-bold"><?= $product['barcode'] ?></div>
                            </div>
                        <?php endif; ?>

                        <?php if ($product['qr_code']): ?>
                            <div class="mt-3">
                                <img src="<?= $product['qr_code'] ?>" alt="QR Code" style="max-width: 100px;">
                            </div>
                        <?php endif; ?>
                    </div>

                    <div class="col-md-8">
                        <table class="table table-bordered">
                            <tr>
                                <th width="35%">Product Name</th>
                                <td><strong><?= $product['product_name'] ?></strong></td>
                            </tr>
                            <tr>
                                <th>SKU</th>
                                <td><code><?= $product['sku'] ?></code></td>
                            </tr>
                            <tr>
                                <th>Category</th>
                                <td><?= $product['category_name'] ?? '-' ?></td>
                            </tr>
                            <tr>
                                <th>Unit</th>
                                <td><?= $product['unit_name'] ?> (<?= $product['unit_symbol'] ?>)</td>
                            </tr>
                            <tr>
                                <th>Supplier</th>
                                <td><?= $product['supplier_name'] ?? '-' ?></td>
                            </tr>
                            <tr>
                                <th>Description</th>
                                <td><?= $product['description'] ?: '-' ?></td>
                            </tr>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-4">
        <!-- Stock Information Card -->
        <div class="card mb-3">
            <div class="card-header bg-primary ">
                <h6 class="mb-0"><i class="bi bi-box-seam me-2"></i>Stock Information</h6>
            </div>
            <div class="card-body">
                <div class="row mb-2">
                    <div class="col-6 fw-bold">Current Stock:</div>
                    <div
                        class="col-6 <?= $product['quantity'] <= $product['minimum_stock'] ? 'text-danger fw-bold' : '' ?>">
                        <?= number_format($product['quantity'], 2) ?> <?= $product['unit_symbol'] ?>
                    </div>
                </div>
                <div class="row mb-2">
                    <div class="col-6 fw-bold">Minimum Stock:</div>
                    <div class="col-6"><?= number_format($product['minimum_stock'], 2) ?> <?= $product['unit_symbol'] ?>
                    </div>
                </div>
                <div class="row mb-2">
                    <div class="col-6 fw-bold">Status:</div>
                    <div class="col-6">
                        <?php if ($product['quantity'] <= 0): ?>
                            <span class="badge bg-danger">Out of Stock</span>
                        <?php elseif ($product['quantity'] <= $product['minimum_stock']): ?>
                            <span class="badge bg-warning text-dark">Low Stock</span>
                        <?php else: ?>
                            <span class="badge bg-success">In Stock</span>
                        <?php endif; ?>
                    </div>
                </div>
                <div class="row">
                    <div class="col-6 fw-bold">Reorder Level:</div>
                    <div class="col-6">
                        <?php if ($product['quantity'] <= $product['minimum_stock']): ?>
                            <button class="btn btn-sm btn-warning" onclick="restock()">Restock Now</button>
                        <?php else: ?>
                            <span class="text-success">✓ Adequate</span>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- Pricing Information Card -->
        <div class="card mb-3">
            <div class="card-header bg-success ">
                <h6 class="mb-0"><i class="bi bi-cash-stack me-2"></i>Pricing Information</h6>
            </div>
            <div class="card-body">
                <div class="row mb-2">
                    <div class="col-6 fw-bold">Purchase Price:</div>
                    <div class="col-6"><?= formatCurrency($product['purchase_price'], 'LRD') ?></div>
                </div>
                <div class="row mb-2">
                    <div class="col-6 fw-bold">Selling Price:</div>
                    <div class="col-6"><?= formatCurrency($product['selling_price'], 'LRD') ?></div>
                </div>
                <div class="row mb-2">
                    <div class="col-6 fw-bold">Profit Margin:</div>
                    <div class="col-6">
                        <?php
                        $margin = (($product['selling_price'] - $product['purchase_price']) / $product['purchase_price']) * 100;
                        $marginClass = $margin > 30 ? 'text-success' : ($margin > 10 ? 'text-warning' : 'text-danger');
                        ?>
                        <span class="<?= $marginClass ?>"><?= number_format($margin, 2) ?>%</span>
                    </div>
                </div>
                <div class="row">
                    <div class="col-6 fw-bold">Stock Value:</div>
                    <div class="col-6"><?= formatCurrency($product['quantity'] * $product['purchase_price'], 'LRD') ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="card mt-3">
        <div class="card-header bg-warning text-dark">
            <h6 class="mb-0"><i class="bi bi-clock-history me-2"></i>Stock Adjustment History</h6>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-sm table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Date & Time</th>
                            <th>Type</th>
                            <th>Quantity</th>
                            <th>Previous Stock</th>
                            <th>New Stock</th>
                            <th>Reason</th>
                    </thead>
                    <tbody id="productAdjustmentHistory">
                        <tr>
                            <td colspan="6" class="text-center text-muted py-3">
                                <i class="bi bi-arrow-repeat"></i> Loading adjustment history...
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
        <div class="card-footer">
            <a href="<?= base_url('inventory/stock-adjustments') ?>?product=<?= $product['id'] ?>"
                class="btn btn-warning">
                <i class="bi bi-arrow-left-right"></i> Adjust Stock
            </a>
            <a href="<?= base_url('sales/create') ?>?product=<?= $product['id'] ?>" class="btn btn-success">
                <i class="bi bi-cart"></i> Sell Product
            </a>
            <a href="<?= base_url('purchases/create') ?>?product=<?= $product['id'] ?>" class="btn btn-primary">
                <i class="bi bi-cart-plus"></i> Purchase More
            </a>
            <?php if ($product['barcode']): ?>
                <button class="btn btn-secondary" onclick="printBarcode()">
                    <i class="bi bi-upc-scan"></i> Print Barcode
                </button>
            <?php endif; ?>
        </div>
    </div>

    <script>
        // Load product-specific adjustment history
        function loadProductAdjustmentHistory() {
            const productId = <?= $product['id'] ?>;

            $.ajax({
                url: '<?= base_url('inventory/get-stock-adjustments') ?>?product_id=' + productId,
                type: 'GET',
                dataType: 'json',
                success: function (response) {
                    if (response.status === 'success' && response.data.length > 0) {
                        let html = '';
                        response.data.forEach(function (adj) {
                            const isIncrease = adj.adjustment_type === 'increase';
                            const badgeClass = isIncrease ? 'bg-success' : 'bg-danger';
                            const sign = isIncrease ? '+' : '-';

                            html += `<tr>
                        <td><small>${adj.created_date || '-'}<br><span class="text-muted">${adj.created_time || ''}</span></small></td>
                        <td><span class="badge ${badgeClass}">${isIncrease ? 'Increase' : 'Decrease'}</span></td>
                        <td class="${isIncrease ? 'text-success' : 'text-danger'} fw-bold">${sign} ${parseFloat(adj.quantity).toLocaleString('en-US', { minimumFractionDigits: 2 })}</td>
                        <td>${parseFloat(adj.previous_quantity).toLocaleString('en-US', { minimumFractionDigits: 2 })}</td>
                        <td class="fw-bold">${parseFloat(adj.new_quantity).toLocaleString('en-US', { minimumFractionDigits: 2 })}</td>
                        <td><small>${escapeHtml(adj.reason || 'Stock Adjustment')}</small></td>
                    </tr>`;
                        });
                        $('#productAdjustmentHistory').html(html);
                    } else {
                        $('#productAdjustmentHistory').html('<tr><td colspan="6" class="text-center text-muted py-3">No adjustment history for this product.</td></tr>');
                    }
                },
                error: function () {
                    $('#productAdjustmentHistory').html('<tr><td colspan="6" class="text-center text-danger py-3">Failed to load adjustment history.</td></tr>');
                }
            });
        }

        // Call the function when page loads
        $(document).ready(function () {
            loadProductAdjustmentHistory();
        });

        function escapeHtml(str) {
            if (!str) return '';
            return str.replace(/[&<>]/g, function (m) {
                if (m === '&') return '&amp;';
                if (m === '<') return '&lt;';
                if (m === '>') return '&gt;';
                return m;
            });
        }
    </script>
</div>

<?php if (!empty($product['stock_movements'] ?? [])): ?>
    <div class="row mt-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h6 class="mb-0"><i class="bi bi-clock-history me-2"></i>Stock Movement History</h6>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-sm">
                            <thead>
                                <tr>
                                    <th>Date</th>
                                    <th>Type</th>
                                    <th>Quantity</th>
                                    <th>Previous Stock</th>
                                    <th>New Stock</th>
                                    <th>Reference</th>
                            </thead>
                            <tbody>
                                <?php foreach ($product['stock_movements'] as $movement): ?>
                                    <tr>
                                        <td><?= date('Y-m-d H:i', strtotime($movement['created_at'])) ?></td>
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
                                            <?= $movement['quantity'] >= 0 ? '+' : '' ?>        <?= number_format($movement['quantity'], 2) ?>
                                        </td>
                                        <td><?= number_format($movement['previous_quantity'], 2) ?></td>
                                        <td><?= number_format($movement['new_quantity'], 2) ?></td>
                                        <td><?= $movement['reference_type'] ?> #<?= $movement['reference_id'] ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
<?php endif; ?>

<script>
    function restock() {
        window.location.href = '<?= base_url('purchases/create?product=' . $product['id']) ?>';
    }

    function printBarcode() {
        // Implement barcode printing
        var barcode = '<?= $product['barcode'] ?>';
        var name = '<?= addslashes($product['product_name']) ?>';
        var price = '<?= $product['selling_price'] ?>';

        var printWindow = window.open('', '_blank');
        printWindow.document.write(`
        <html>
        <head>
            <title>Barcode - <?= $product['product_name'] ?></title>
            <style>
                body { font-family: Arial, sans-serif; text-align: center; padding: 50px; }
                .barcode { font-size: 24px; font-family: monospace; margin: 20px 0; }
                .name { font-size: 14px; margin: 10px 0; }
                .price { font-size: 16px; font-weight: bold; color: green; }
            </style>
        </head>
        <body>
            <div class="barcode">*${barcode}*</div>
            <div class="name">${name}</div>
            <div class="price"><?= formatCurrency($product['selling_price'], 'LRD') ?></div>
            <script>window.print();setTimeout(window.close, 500);<\/script>
        </body>
        </html>
    `);
        printWindow.document.close();
    }
</script>
<?= $this->endSection() ?>