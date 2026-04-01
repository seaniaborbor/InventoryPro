<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<div class="row justify-content-center">
    <div class="col-md-10">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0"><i class="bi bi-pencil-square me-2"></i>Edit Product</h5>
            </div>
            <div class="card-body">
                <form action="<?= base_url('inventory/products/update/' . $product['id']) ?>" method="post" enctype="multipart/form-data">
                    <?= csrf_field() ?>
                    
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="product_name" class="form-label">Product Name *</label>
                            <input type="text" class="form-control" id="product_name" name="product_name" 
                                   value="<?= old('product_name', $product['product_name']) ?>" required>
                        </div>
                        <div class="col-md-6">
                            <label for="sku" class="form-label">SKU *</label>
                            <input type="text" class="form-control" id="sku" name="sku" 
                                   value="<?= old('sku', $product['sku']) ?>" required>
                            <small class="text-muted">Unique product code</small>
                        </div>
                    </div>
                    
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="barcode" class="form-label">Barcode</label>
                            <input type="text" class="form-control" id="barcode" name="barcode" 
                                   value="<?= old('barcode', $product['barcode']) ?>">
                        </div>
                        <div class="col-md-6">
                            <label for="category_id" class="form-label">Category *</label>
                            <select class="form-select" id="category_id" name="category_id" required>
                                <option value="">Select Category</option>
                                <?php foreach ($categories as $cat): ?>
                                <option value="<?= $cat['id'] ?>" <?= $product['category_id'] == $cat['id'] ? 'selected' : '' ?>>
                                    <?= $cat['category_name'] ?>
                                </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="unit_id" class="form-label">Unit *</label>
                            <select class="form-select" id="unit_id" name="unit_id" required>
                                <option value="">Select Unit</option>
                                <?php foreach ($units as $unit): ?>
                                <option value="<?= $unit['id'] ?>" <?= $product['unit_id'] == $unit['id'] ? 'selected' : '' ?>>
                                    <?= $unit['unit_name'] ?> (<?= $unit['unit_symbol'] ?>)
                                </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label for="supplier_id" class="form-label">Supplier</label>
                            <select class="form-select" id="supplier_id" name="supplier_id">
                                <option value="">Select Supplier</option>
                                <?php foreach ($suppliers as $sup): ?>
                                <option value="<?= $sup['id'] ?>" <?= $product['supplier_id'] == $sup['id'] ? 'selected' : '' ?>>
                                    <?= $sup['supplier_name'] ?>
                                </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="purchase_price" class="form-label">Purchase Price *</label>
                            <input type="number" step="0.01" class="form-control" id="purchase_price" name="purchase_price" 
                                   value="<?= old('purchase_price', $product['purchase_price']) ?>" required>
                        </div>
                        <div class="col-md-6">
                            <label for="selling_price" class="form-label">Selling Price *</label>
                            <input type="number" step="0.01" class="form-control" id="selling_price" name="selling_price" 
                                   value="<?= old('selling_price', $product['selling_price']) ?>" required>
                        </div>
                    </div>
                    
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="quantity" class="form-label">Current Quantity</label>
                            <input type="number" step="0.01" class="form-control" id="quantity" name="quantity" 
                                   value="<?= old('quantity', $product['quantity']) ?>" readonly>
                            <small class="text-muted">Use Stock Adjustment to change quantity</small>
                        </div>
                        <div class="col-md-6">
                            <label for="minimum_stock" class="form-label">Minimum Stock Level</label>
                            <input type="number" step="0.01" class="form-control" id="minimum_stock" name="minimum_stock" 
                                   value="<?= old('minimum_stock', $product['minimum_stock']) ?>">
                            <small class="text-muted">Alert when stock falls below this level</small>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="description" class="form-label">Description</label>
                        <textarea class="form-control" id="description" name="description" rows="3"><?= old('description', $product['description']) ?></textarea>
                    </div>
                    
                    <div class="mb-3">
                        <label for="product_image" class="form-label">Product Image</label>
                        <?php if ($product['product_image']): ?>
                            <div class="mb-2">
                                <img src="<?= base_url($product['product_image']) ?>" alt="Current Image" class="img-thumbnail" style="max-height: 100px;">
                                <br>
                                <small class="text-muted">Current image</small>
                            </div>
                        <?php endif; ?>
                        <input type="file" class="form-control" id="product_image" name="product_image" accept="image/*">
                        <small class="text-muted">Upload a new image to replace the current one (optional)</small>
                    </div>
                    
                    <div class="alert alert-warning">
                        <i class="bi bi-exclamation-triangle"></i> 
                        <strong>Note:</strong> To adjust the current stock quantity, please use the 
                        <a href="<?= base_url('inventory/stock-adjustments') ?>">Stock Adjustments</a> page.
                    </div>
                    
                    <div class="d-flex justify-content-between">
                        <a href="<?= base_url('inventory/products') ?>" class="btn btn-secondary">
                            <i class="bi bi-arrow-left"></i> Cancel
                        </a>
                        <div>
                            <?php if ($product['quantity'] <= $product['minimum_stock']): ?>
                            <a href="<?= base_url('purchases/create?product=' . $product['id']) ?>" class="btn btn-warning">
                                <i class="bi bi-cart-plus"></i> Restock
                            </a>
                            <?php endif; ?>
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-save"></i> Update Product
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
// Auto-calculate profit margin preview (optional)
$(document).ready(function() {
    $('#purchase_price, #selling_price').on('keyup change', function() {
        var purchase = parseFloat($('#purchase_price').val()) || 0;
        var selling = parseFloat($('#selling_price').val()) || 0;
        var margin = 0;
        var marginClass = '';
        
        if (purchase > 0) {
            margin = ((selling - purchase) / purchase) * 100;
            if (margin > 30) {
                marginClass = 'text-success';
            } else if (margin > 10) {
                marginClass = 'text-warning';
            } else {
                marginClass = 'text-danger';
            }
        }
        
        // Optional: Display margin preview
        if ($('#margin_preview').length === 0) {
            $('<div id="margin_preview" class="mt-2 small"></div>').insertAfter('#selling_price');
        }
        $('#margin_preview').html('Profit Margin: <span class="' + marginClass + '">' + margin.toFixed(2) + '%</span>');
    });
    
    // Trigger on load
    $('#purchase_price').trigger('keyup');
});
</script>
<?= $this->endSection() ?>