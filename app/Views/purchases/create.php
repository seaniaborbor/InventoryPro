<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0"><i class="bi bi-plus-circle me-2"></i>New Purchase Order</h5>
            </div>
            <div class="card-body">
                <form id="purchaseForm">
                    <?= csrf_field() ?>

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="supplier_id" class="form-label">Supplier *</label>
                            <div class="input-group">
                                <select class="form-select" id="supplier_id" name="supplier_id" required>
                                    <option value="">Select Supplier</option>
                                    <?php foreach ($suppliers as $supplier): ?>
                                        <option value="<?= $supplier['id'] ?>"><?= $supplier['supplier_name'] ?>
                                            (<?= $supplier['phone'] ?>)</option>
                                    <?php endforeach; ?>
                                </select>
                                <button class="btn btn-outline-primary" type="button" onclick="showNewSupplier()">
                                    <i class="bi bi-plus"></i> New
                                </button>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Purchase Number</label>
                            <input type="text" class="form-control" value="<?= $purchase_number ?>" readonly>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-4">
                            <label for="purchase_date" class="form-label">Purchase Date *</label>
                            <input type="date" class="form-control" id="purchase_date" name="purchase_date"
                                value="<?= date('Y-m-d') ?>" required>
                        </div>
                        <div class="col-md-4">
                            <label for="currency" class="form-label">Currency</label>
                            <select class="form-select" id="currency" name="currency">
                                <option value="LRD">LRD - Liberian Dollar</option>
                                <option value="USD">USD - US Dollar</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label for="payment_status" class="form-label">Payment Status</label>
                            <select class="form-select" id="payment_status" name="payment_status">
                                <option value="Unpaid">Unpaid</option>
                                <option value="Partial">Partial</option>
                                <option value="Paid">Paid</option>
                            </select>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Products</label>
                        <div class="input-group mb-2">
                            <select class="form-select" id="product_select">
                                <option value="">Select Product</option>
                                <?php foreach ($products as $product): ?>
                                    <option value="<?= $product['id'] ?>" data-price="<?= $product['purchase_price'] ?>">
                                        <?= $product['product_name'] ?> (SKU: <?= $product['sku'] ?>)
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <input type="number" step="0.01" class="form-control" id="product_quantity"
                                placeholder="Quantity" style="width: 120px;">
                            <input type="number" step="0.01" class="form-control" id="product_price"
                                placeholder="Unit Price" style="width: 120px;">
                            <button class="btn btn-primary" type="button" onclick="addProduct()">Add</button>
                        </div>

                        <div class="table-responsive">
                            <table class="table table-bordered" id="productsTable">
                                <thead>
                                    <tr>
                                        <th>Product</th>
                                        <th width="120">Quantity</th>
                                        <th width="120">Unit Price</th>
                                        <th width="120">Total</th>
                                        <th width="50">Action</th>
                                    </tr>
                                </thead>
                                <tbody id="productsBody"></tbody>
                                <tfoot>
                                    <tr class="table-active">
                                        <td colspan="3" class="text-end fw-bold">Subtotal:</td>
                                        <td class="fw-bold" id="subtotal">0.00</td>
                                        <td></td>
                                    </tr>
                                    <tr class="table-active">
                                        <td colspan="3" class="text-end">Discount:</td>
                                        <td><input type="number" step="0.01" id="discount" name="discount"
                                                class="form-control form-control-sm" value="0"></td>
                                        <td></td>
                                    </tr>
                                    <tr class="table-active">
                                        <td colspan="3" class="text-end">Tax:</td>
                                        <td><input type="number" step="0.01" id="tax" name="tax"
                                                class="form-control form-control-sm" value="0"></td>
                                        <td></td>
                                    </tr>
                                    <tr class="table-primary">
                                        <td colspan="3" class="text-end fw-bold fs-5">Total:</td>
                                        <td class="fw-bold fs-5 text-primary" id="total">0.00</td>
                                        <td></td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="notes" class="form-label">Notes</label>
                        <textarea class="form-control" id="notes" name="notes" rows="3"></textarea>
                    </div>

                    <div class="text-end">
                        <a href="<?= base_url('purchases') ?>" class="btn btn-secondary">Cancel</a>
                        <button type="submit" class="btn btn-primary">Save Purchase</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- New Supplier Modal -->
<div class="modal fade" id="supplierModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Add New Supplier</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="supplierForm">
                <?= csrf_field() ?>
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="supplier_name" class="form-label">Supplier Name *</label>
                        <input type="text" class="form-control" id="supplier_name" name="supplier_name" required>
                    </div>
                    <div class="mb-3">
                        <label for="contact_person" class="form-label">Contact Person</label>
                        <input type="text" class="form-control" id="contact_person" name="contact_person">
                    </div>
                    <div class="mb-3">
                        <label for="phone" class="form-label">Phone *</label>
                        <input type="text" class="form-control" id="phone" name="phone" required>
                    </div>
                    <div class="mb-3">
                        <label for="email" class="form-label">Email</label>
                        <input type="email" class="form-control" id="email" name="email">
                    </div>
                    <div class="mb-3">
                        <label for="address" class="form-label">Address</label>
                        <textarea class="form-control" id="address" name="address" rows="2"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Save Supplier</button>
                </div>
            </form>
        </div>
    </div>
</div>
<script>
    let items = [];

    $(document).ready(function () {
        // Product selection handler
        $('#product_select').on('change', function () {
            var price = $(this).find(':selected').data('price');
            if (price) {
                $('#product_price').val(price);
            }
        });

        // Calculate totals when discount or tax changes
        $('#discount, #tax').on('change keyup', function () {
            updateTotals();
        });

        // Form submission
        $('#purchaseForm').on('submit', function (e) {
            e.preventDefault();

            if (items.length === 0) {
                showToast('Error', 'Please add at least one product', 'error');
                return;
            }

            // Prepare form data
            var formData = {
                supplier_id: $('#supplier_id').val(),
                purchase_date: $('#purchase_date').val(),
                currency: $('#currency').val(),
                payment_status: $('#payment_status').val(),
                discount: $('#discount').val(),
                tax: $('#tax').val(),
                notes: $('#notes').val(),
                items: items
            };

            // Disable submit button
            var submitBtn = $(this).find('button[type="submit"]');
            submitBtn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-2"></span>Saving...');

            $.ajax({
                url: '<?= base_url('purchases/store') ?>',
                type: 'POST',
                data: JSON.stringify(formData),
                contentType: 'application/json',
                dataType: 'json',
                success: function (response) {
                    submitBtn.prop('disabled', false).html('Save Purchase');

                    if (response.status === 'success') {
                        showToast('Success', response.message, 'success');
                        setTimeout(function () {
                            window.location.href = '<?= base_url('purchases') ?>';
                        }, 1500);
                    } else {
                        showToast('Error', response.message || 'Failed to save purchase', 'error');
                    }
                },
                error: function (xhr) {
                    submitBtn.prop('disabled', false).html('Save Purchase');
                    var errorMsg = 'An error occurred';
                    try {
                        var response = JSON.parse(xhr.responseText);
                        if (response.message) errorMsg = response.message;
                    } catch (e) { }
                    showToast('Error', errorMsg, 'error');
                }
            });
        });

        // New supplier form
        $('#supplierForm').on('submit', function (e) {
            e.preventDefault();
            var formData = $(this).serialize();

            $.ajax({
                url: '<?= base_url('suppliers/store') ?>',
                type: 'POST',
                data: formData,
                success: function (response) {
                    if (response.status === 'success') {
                        $('#supplierModal').modal('hide');
                        // Reload to show new supplier
                        location.reload();
                    } else {
                        showToast('Error', response.message || 'Failed to save supplier', 'error');
                    }
                }
            });
        });
    });

    function addProduct() {
        var productId = $('#product_select').val();
        var productName = $('#product_select option:selected').text().split(' (')[0];
        var quantity = parseFloat($('#product_quantity').val());
        var unitPrice = parseFloat($('#product_price').val());

        if (!productId || quantity <= 0 || unitPrice <= 0) {
            showToast('Error', 'Please select product and enter valid quantity and price', 'error');
            return;
        }

        // Check if product already added
        var existing = items.find(item => item.product_id == productId);
        if (existing) {
            showToast('Warning', 'Product already added. Please remove and re-add if you want to change quantity.', 'warning');
            return;
        }

        items.push({
            product_id: productId,
            product_name: productName,
            quantity: quantity,
            unit_price: unitPrice
        });

        renderItems();
        $('#product_quantity').val('');
        $('#product_price').val('');
        $('#product_select').val('');
        updateTotals();
    }

    function renderItems() {
        var html = '';
        var subtotal = 0;

        items.forEach(function (item, index) {
            var total = item.quantity * item.unit_price;
            subtotal += total;

            html += '<tr>';
            html += '<td><strong>' + escapeHtml(item.product_name) + '</strong></td>';
            html += '<td class="text-center">' + formatNumber(item.quantity) + '</td>';
            html += '<td class="text-end">' + formatNumber(item.unit_price) + '</td>';
            html += '<td class="text-end fw-bold">' + formatNumber(total) + '</td>';
            html += '<td class="text-center"><button class="btn btn-sm btn-danger" onclick="removeItem(' + index + ')"><i class="bi bi-trash"></i></button></td>';
            html += '</tr>';
        });

        if (items.length === 0) {
            html = '<tr><td colspan="5" class="text-center text-muted">No products added</td></tr>';
        }

        $('#productsBody').html(html);
        $('#subtotal').text(formatNumber(subtotal));
    }

    function removeItem(index) {
        items.splice(index, 1);
        renderItems();
        updateTotals();
    }

    function updateTotals() {
        var subtotal = items.reduce((sum, item) => sum + (item.quantity * item.unit_price), 0);
        var discount = parseFloat($('#discount').val()) || 0;
        var tax = parseFloat($('#tax').val()) || 0;
        var total = subtotal + tax - discount;

        $('#subtotal').text(formatNumber(subtotal));
        $('#total').text(formatNumber(total));
    }

    function formatNumber(num) {
        return parseFloat(num).toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
    }

    function escapeHtml(text) {
        if (!text) return '';
        return text.replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#39;');
    }

    function showNewSupplier() {
        $('#supplierForm')[0].reset();
        $('#supplierModal').modal('show');
    }
</script>
<?= $this->endSection() ?>