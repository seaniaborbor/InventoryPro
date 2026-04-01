<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0"><i class="bi bi-box-seam me-2"></i>Products</h5>
                <div>
                    <button type="button" class="btn btn-success btn-sm me-2" onclick="scanBarcode()">
                        <i class="bi bi-upc-scan"></i> Scan Barcode
                    </button>
                    <button type="button" class="btn btn-primary btn-sm" onclick="showCreateModal()">
                        <i class="bi bi-plus-circle"></i> Add Product
                    </button>
                </div>
            </div>
            <div class="card-body">
                <!-- Search and Filter -->
                <div class="row mb-3">
                    <div class="col-md-4">
                        <input type="text" id="searchInput" class="form-control"
                            placeholder="Search by name, SKU, or barcode...">
                    </div>
                    <div class="col-md-3">
                        <select id="categoryFilter" class="form-select">
                            <option value="">All Categories</option>
                            <?php foreach ($categories as $cat): ?>
                                <option value="<?= $cat['id'] ?>"><?= $cat['category_name'] ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <select id="stockFilter" class="form-select">
                            <option value="">All Stock</option>
                            <option value="low">Low Stock</option>
                            <option value="out">Out of Stock</option>
                            <option value="instock">In Stock</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <button class="btn btn-secondary w-100" onclick="resetFilters()">
                            <i class="bi bi-arrow-repeat"></i> Reset
                        </button>
                    </div>
                </div>

                <div class="table-responsive">
                    <table class="table table-hover" id="productsTable">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Image</th>
                                <th>Name / SKU</th>
                                <th>Category</th>
                                <th>Stock</th>
                                <th>Unit</th>
                                <th>Purchase Price</th>
                                <th>Selling Price</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($products as $product): ?>
                                <tr>
                                    <td><?= $product['id'] ?></td>
                                    <td>
                                        <?php if ($product['product_image']): ?>
                                            <img src="<?= base_url($product['product_image']) ?>" width="40" height="40"
                                                class="rounded">
                                        <?php else: ?>
                                            <i class="bi bi-box" style="font-size: 2rem;"></i>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <strong><?= $product['product_name'] ?></strong><br>
                                        <small class="text-muted">SKU: <?= $product['sku'] ?></small>
                                        <?php if ($product['barcode']): ?>
                                            <br><small class="text-muted">Barcode: <?= $product['barcode'] ?></small>
                                        <?php endif; ?>
                                    </td>
                                    <td><?= $product['category_name'] ?? '-' ?></td>
                                    <td>
                                        <span
                                            class="fw-bold <?= $product['quantity'] <= $product['minimum_stock'] ? 'text-danger' : 'text-success' ?>">
                                            <?= number_format($product['quantity'], 2) ?>
                                        </span>
                                        <br>
                                        <small class="text-muted">Min:
                                            <?= number_format($product['minimum_stock'], 2) ?></small>
                                    </td>
                                    <td><?= $product['unit_symbol'] ?? '-' ?></td>
                                    <td><?= formatCurrency($product['purchase_price'], 'LRD') ?></td>
                                    <td><?= formatCurrency($product['selling_price'], 'LRD') ?></td>
                                    <td>
                                        <?php if ($product['quantity'] <= 0): ?>
                                            <span class="badge bg-danger">Out of Stock</span>
                                        <?php elseif ($product['quantity'] <= $product['minimum_stock']): ?>
                                            <span class="badge bg-warning text-dark">Low Stock</span>
                                        <?php else: ?>
                                            <span class="badge bg-success">In Stock</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <button class="btn btn-sm btn-info" onclick="viewProduct(<?= $product['id'] ?>)">
                                            <i class="bi bi-eye"></i>
                                        </button>
                                        <button class="btn btn-sm btn-primary" onclick="editProduct(<?= $product['id'] ?>)">
                                            <i class="bi bi-pencil"></i>
                                        </button>
                                        <button class="btn btn-sm btn-warning"
                                            onclick="adjustStock(<?= $product['id'] ?>, '<?= addslashes($product['product_name']) ?>')">
                                            <i class="bi bi-arrow-left-right"></i>
                                        </button>
                                        <button class="btn btn-sm btn-danger"
                                            onclick="deleteProduct(<?= $product['id'] ?>, '<?= addslashes($product['product_name']) ?>')">
                                            <i class="bi bi-trash"></i>
                                        </button>
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

<!-- Create/Edit Product Modal -->
<div class="modal fade" id="productModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="productModalTitle">Add Product</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="productForm" enctype="multipart/form-data">
                <?= csrf_field() ?>
                <input type="hidden" name="id" id="productId">
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="product_name" class="form-label">Product Name *</label>
                            <input type="text" class="form-control" id="product_name" name="product_name" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="sku" class="form-label">SKU *</label>
                            <input type="text" class="form-control" id="sku" name="sku" required>
                            <small class="text-muted">Unique product code</small>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="barcode" class="form-label">Barcode</label>
                            <input type="text" class="form-control" id="barcode" name="barcode">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="category_id" class="form-label">Category *</label>
                            <select class="form-select" id="category_id" name="category_id" required>
                                <option value="">Select Category</option>
                                <?php foreach ($categories as $cat): ?>
                                    <option value="<?= $cat['id'] ?>"><?= $cat['category_name'] ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="unit_id" class="form-label">Unit *</label>
                            <select class="form-select" id="unit_id" name="unit_id" required>
                                <option value="">Select Unit</option>
                                <?php foreach ($units as $unit): ?>
                                    <option value="<?= $unit['id'] ?>"><?= $unit['unit_name'] ?>
                                        (<?= $unit['unit_symbol'] ?>)</option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="supplier_id" class="form-label">Supplier</label>
                            <select class="form-select" id="supplier_id" name="supplier_id">
                                <option value="">Select Supplier</option>
                                <?php foreach ($suppliers as $sup): ?>
                                    <option value="<?= $sup['id'] ?>"><?= $sup['supplier_name'] ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="purchase_price" class="form-label">Purchase Price *</label>
                            <input type="number" step="0.01" class="form-control" id="purchase_price"
                                name="purchase_price" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="selling_price" class="form-label">Selling Price *</label>
                            <input type="number" step="0.01" class="form-control" id="selling_price"
                                name="selling_price" required>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="quantity" class="form-label">Initial Quantity</label>
                            <input type="number" step="0.01" class="form-control" id="quantity" name="quantity"
                                value="0">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="minimum_stock" class="form-label">Minimum Stock Level</label>
                            <input type="number" step="0.01" class="form-control" id="minimum_stock"
                                name="minimum_stock" value="0">
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="description" class="form-label">Description</label>
                        <textarea class="form-control" id="description" name="description" rows="3"></textarea>
                    </div>
                    <div class="mb-3">
                        <label for="product_image" class="form-label">Product Image</label>
                        <input type="file" class="form-control" id="product_image" name="product_image"
                            accept="image/*">
                        <div id="currentImage" style="display: none;">
                            <img id="productImagePreview" src="" width="100" class="mt-2">
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Save Product</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Stock Adjustment Modal -->
<div class="modal fade" id="stockModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Adjust Stock</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="stockForm">
                <?= csrf_field() ?>
                <input type="hidden" name="product_id" id="stockProductId">
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Product</label>
                        <p class="fw-bold" id="stockProductName"></p>
                    </div>
                    <div class="mb-3">
                        <label for="current_stock" class="form-label">Current Stock</label>
                        <input type="text" class="form-control" id="current_stock" readonly>
                    </div>
                    <div class="mb-3">
                        <label for="adjustment_type" class="form-label">Adjustment Type</label>
                        <select class="form-select" id="adjustment_type" name="adjustment_type" required>
                            <option value="increase">Increase Stock (+)</option>
                            <option value="decrease">Decrease Stock (-)</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="quantity" class="form-label">Quantity</label>
                        <input type="number" step="0.01" class="form-control" id="quantity" name="quantity" required>
                    </div>
                    <div class="mb-3">
                        <label for="reason" class="form-label">Reason</label>
                        <select class="form-select" id="reason" name="reason" required>
                            <option value="">Select Reason</option>
                            <option value="Physical Count">Physical Count</option>
                            <option value="Damaged">Damaged Goods</option>
                            <option value="Return">Customer Return</option>
                            <option value="Correction">Correction</option>
                            <option value="Other">Other</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="notes" class="form-label">Notes</label>
                        <textarea class="form-control" id="notes" name="notes" rows="2"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-warning">Apply Adjustment</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Barcode Scanner Modal -->
<div class="modal fade" id="scannerModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Scan Barcode</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div id="qr-reader" style="width: 100%;"></div>
                <div class="mt-3">
                    <label class="form-label">Or enter barcode manually:</label>
                    <div class="input-group">
                        <input type="text" class="form-control" id="manualBarcode" placeholder="Enter barcode number">
                        <button class="btn btn-primary" onclick="searchByBarcode()">Search</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    $(document).ready(function () {
        // Initialize DataTable
        var table = $('#productsTable').DataTable({
            pageLength: 25,
            order: [[0, 'desc']],
            columnDefs: [
                { orderable: false, targets: [1, 9] }
            ]
        });

        // Search functionality
        $('#searchInput').on('keyup', function () {
            table.search(this.value).draw();
        });

        // Category filter
        $('#categoryFilter').on('change', function () {
            var category = this.value;
            if (category) {
                table.column(3).search(category).draw();
            } else {
                table.column(3).search('').draw();
            }
        });

        // Stock filter
        $('#stockFilter').on('change', function () {
            var filter = this.value;
            if (filter === 'low') {
                table.column(4).search('Low Stock', true, false).draw();
            } else if (filter === 'out') {
                table.column(4).search('Out of Stock', true, false).draw();
            } else if (filter === 'instock') {
                table.column(4).search('In Stock', true, false).draw();
            } else {
                table.column(4).search('').draw();
            }
        });

        // Product form submission
        $('#productForm').on('submit', function (e) {
            e.preventDefault();
            var formData = new FormData(this);
            var productId = $('#productId').val();
            var url = productId ? '/inventory/products/update/' + productId : '/inventory/products/store';

            // Add CSRF token to FormData
            formData.append('<?= csrf_token() ?>', '<?= csrf_hash() ?>');

            $.ajax({
                url: url,
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                dataType: 'json',
                success: function (response) {
                    console.log('Response:', response); // Debug log
                    if (response.status === 'success') {
                        $('#productModal').modal('hide');
                        showToast('Success', response.message, 'success');
                        setTimeout(() => location.reload(), 1500);
                    } else {
                        let errorMsg = response.message || 'Failed to save product';
                        if (response.errors) {
                            errorMsg = Object.values(response.errors).join(', ');
                            console.log(errorMsg);
                        }
                        showToast('Error', errorMsg, 'error');
                    }
                },
                error: function (xhr, status, error) {
                    console.error('AJAX Error:', xhr.responseText);
                    showToast('Error', 'An error occurred: ' + error, 'error');
                }
            });
        });

        // Stock form submission
        $('#stockForm').on('submit', function (e) {
            e.preventDefault();
            var formData = $(this).serialize();

            $.ajax({
                url: '/inventory/stock-adjustments/adjust',
                type: 'POST',
                data: formData,
                success: function (response) {
                    if (response.status === 'success') {
                        $('#stockModal').modal('hide');
                        showToast('Success', response.message, 'success');
                        setTimeout(() => location.reload(), 1500);
                    } else {
                        console.log(response);
                        showToast('Error', response.message, 'error');
                    }
                },
                error: function () {
                    showToast('Error', 'An error occurred', 'error');
                }
            });
        });
    });

    function showCreateModal() {
        $('#productModalTitle').text('Add Product');
        $('#productForm')[0].reset();
        $('#productId').val('');
        $('#currentImage').hide();
        $('#productModal').modal('show');
    }

    function editProduct(id) {
        $.ajax({
            url: '/inventory/products/edit/' + id,
            type: 'GET',
            success: function (response) {
                if (response.status === 'success') {
                    $('#productModalTitle').text('Edit Product');
                    $('#productId').val(response.product.id);
                    $('#product_name').val(response.product.product_name);
                    $('#sku').val(response.product.sku);
                    $('#barcode').val(response.product.barcode);
                    $('#category_id').val(response.product.category_id);
                    $('#unit_id').val(response.product.unit_id);
                    $('#supplier_id').val(response.product.supplier_id);
                    $('#purchase_price').val(response.product.purchase_price);
                    $('#selling_price').val(response.product.selling_price);
                    $('#minimum_stock').val(response.product.minimum_stock);
                    $('#description').val(response.product.description);

                    if (response.product.product_image) {
                        $('#productImagePreview').attr('src', response.product.product_image);
                        $('#currentImage').show();
                    } else {
                        $('#currentImage').hide();
                    }

                    $('#productModal').modal('show');
                }else{
                    // console.log(response);
                }
            }
        });
    }

    function viewProduct(id) {
        // Implement view product details
        window.open('/inventory/products/view/' + id, '_blank');
    }

    function adjustStock(id, name) {
        $.ajax({
            url: '/inventory/products/edit/' + id,
            type: 'GET',
            success: function (response) {
                if (response.status === 'success') {
                    $('#stockProductId').val(id);
                    $('#stockProductName').text(name);
                    $('#current_stock').val(response.product.quantity);
                    $('#stockModal').modal('show');
                }
            }
        });
    }

    function deleteProduct(id, name) {
        Swal.fire({
            title: 'Delete Product?',
            text: `Are you sure you want to delete "${name}"? This action cannot be undone.`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'Yes, delete it!'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: '/inventory/products/delete/' + id,
                    type: 'POST',
                    data: { <?= csrf_token() ?>: '<?= csrf_hash() ?>' },
                    success: function (response) {
                        if (response.status === 'success') {
                            showToast('Success', response.message, 'success');
                            setTimeout(() => location.reload(), 1500);
                        } else {
                            showToast('Error', response.message, 'error');
                        }
                    }
                });
            }
        });
    }

    function scanBarcode() {
        $('#scannerModal').modal('show');

        // Initialize QR scanner
        const html5QrCode = new Html5Qrcode("qr-reader");
        const qrCodeSuccessCallback = (decodedText, decodedResult) => {
            html5QrCode.stop();
            $('#manualBarcode').val(decodedText);
            searchByBarcode();
        };

        const config = { fps: 10, qrbox: { width: 250, height: 250 } };
        html5QrCode.start({ facingMode: "environment" }, config, qrCodeSuccessCallback);

        // Store scanner instance to stop later
        window.scanner = html5QrCode;
    }

    function searchByBarcode() {
        var barcode = $('#manualBarcode').val();
        if (barcode) {
            $.ajax({
                url: '/inventory/products/get-by-barcode/' + barcode,
                type: 'GET',
                success: function (response) {
                    if (response.status === 'success') {
                        $('#scannerModal').modal('hide');
                        if (window.scanner) {
                            window.scanner.stop();
                        }
                        editProduct(response.product.id);
                    } else {
                        showToast('Not Found', 'Product not found with this barcode', 'warning');
                    }
                }
            });
        }
    }

    function resetFilters() {
        $('#searchInput').val('');
        $('#categoryFilter').val('');
        $('#stockFilter').val('');
        $('#productsTable').DataTable().search('').draw();
        $('#productsTable').DataTable().column(3).search('').draw();
    }

    function formatCurrency(amount, currency) {
        if (currency === 'USD') {
            return '$ ' + parseFloat(amount).toLocaleString('en-US', { minimumFractionDigits: 2 });
        } else {
            return 'L$ ' + parseFloat(amount).toLocaleString('en-US', { minimumFractionDigits: 2 });
        }
    }
</script>
<?= $this->endSection() ?>