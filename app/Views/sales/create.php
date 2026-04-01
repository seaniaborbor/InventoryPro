<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0"><i class="bi bi-plus-circle me-2"></i>New Sale</h5>
            </div>
            <div class="card-body">
                <form id="saleForm">
                    <?= csrf_field() ?>
                    
                    <!-- Customer & Basic Info -->
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <label class="form-label">Customer</label>
                            <div class="input-group">
                                <select class="form-select" id="customer_id" name="customer_id">
                                    <option value="">Walk-in Customer</option>
                                    <?php foreach ($customers as $customer): ?>
                                        <option value="<?= $customer['id'] ?>">
                                            <?= esc($customer['customer_name']) ?> (<?= esc($customer['phone']) ?>)
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                                <button class="btn btn-outline-primary" type="button" onclick="showNewCustomer()">
                                    <i class="bi bi-plus"></i> New
                                </button>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Invoice Number</label>
                            <input type="text" class="form-control" value="<?= esc($invoice_number) ?>" readonly>
                        </div>
                    </div>

                    <div class="row mb-4">
                        <div class="col-md-6">
                            <label class="form-label">Currency</label>
                            <select class="form-select" id="currency" name="currency">
                                <option value="LRD">LRD - Liberian Dollar</option>
                                <option value="USD">USD - US Dollar</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Payment Method</label>
                            <select class="form-select" id="payment_method" name="payment_method" required>
                                <option value="">Select Method</option>
                                <option value="Cash">Cash</option>
                                <option value="Mobile Money">Mobile Money</option>
                                <option value="Bank Transfer">Bank Transfer</option>
                                <option value="Card">Card</option>
                            </select>
                        </div>
                    </div>

                    <!-- Add Product Section -->
                    <div class="mb-4">
                        <label class="form-label fw-bold">Add Products</label>
                        <div class="row g-2">
                            <!-- Dropdown -->
                            <div class="col-md-5">
                                <select id="productDropdown" class="form-select" onchange="addProductFromDropdown()">
                                    <option value="">-- Select Product --</option>
                                    <?php foreach ($products as $product): ?>
                                        <option value="<?= $product['id'] ?>" 
                                                data-name="<?= esc($product['product_name']) ?>"
                                                data-price="<?= $product['selling_price'] ?>"
                                                data-stock="<?= $product['quantity'] ?>">
                                            <?= esc($product['product_name']) ?> (Stock: <?= $product['quantity'] ?>)
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <!-- Search + Scan -->
                            <div class="col-md-7">
                                <div class="input-group">
                                    <input type="text" id="productSearch" class="form-control" 
                                           placeholder="Search by name, SKU or barcode...">
                                    <button class="btn btn-primary" type="button" onclick="scanBarcode()">
                                        <i class="bi bi-upc-scan"></i> Scan
                                    </button>
                                </div>
                                <div id="productSuggestions" class="list-group mt-2" style="display: none; max-height: 280px; overflow-y: auto;"></div>
                            </div>
                        </div>
                    </div>

                    <!-- Items Table -->
                    <div class="table-responsive mb-4">
                        <table class="table table-bordered table-hover" id="itemsTable">
                            <thead class="table-light">
                                <tr>
                                    <th>Product</th>
                                    <th width="130">Quantity</th>
                                    <th width="130">Unit Price</th>
                                    <th width="110">Discount</th>
                                    <th width="140">Line Total</th>
                                    <th width="60">Action</th>
                                </tr>
                            </thead>
                            <tbody id="itemsBody"></tbody>
                            <tfoot>
                                <tr class="table-active">
                                    <td colspan="4" class="text-end fw-bold">Subtotal:</td>
                                    <td class="fw-bold text-end" id="subtotal">0.00</td>
                                    <td></td>
                                </tr>
                                <tr class="table-active">
                                    <td colspan="4" class="text-end">Tax (<?= $tax_rate ?? 0 ?>%):</td>
                                    <td class="text-end" id="tax">0.00</td>
                                    <td></td>
                                </tr>
                                <tr class="table-active">
                                    <td colspan="4" class="text-end">
                                        Discount:
                                        <input type="number" step="0.01" id="discount" name="discount" 
                                               class="form-control form-control-sm d-inline-block w-50" value="0">
                                    </td>
                                    <td class="text-end" id="discountAmount">0.00</td>
                                    <td></td>
                                </tr>
                                <tr class="table-primary">
                                    <td colspan="4" class="text-end fw-bold fs-5">Grand Total:</td>
                                    <td class="fw-bold fs-5 text-primary text-end" id="total">0.00</td>
                                    <td></td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>

                    <!-- Payment Section -->
                    <div class="row mt-4">
                        <div class="col-md-4">
                            <label class="form-label">Payment Status</label>
                            <select class="form-select" id="payment_status" name="payment_status">
                                <option value="Unpaid">Unpaid</option>
                                <option value="Partial">Partial</option>
                                <option value="Paid">Paid</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Amount Paid</label>
                            <input type="number" step="0.01" class="form-control" id="amount_paid" name="amount_paid" value="0">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Balance Due</label>
                            <input type="text" class="form-control bg-light" id="balance_due" readonly>
                        </div>
                    </div>

                    <div class="mb-3 mt-3">
                        <label class="form-label">Notes</label>
                        <textarea class="form-control" name="notes" rows="2"></textarea>
                    </div>

                    <div class="text-end">
                        <a href="<?= base_url('sales') ?>" class="btn btn-secondary">Cancel</a>
                        <button type="submit" class="btn btn-success btn-lg" id="submitBtn">Create Sale</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- New Customer Modal -->
<div class="modal fade" id="customerModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Add New Customer</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="customerForm">
                <?= csrf_field() ?>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Customer Name *</label>
                        <input type="text" class="form-control" id="customer_name" name="customer_name" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Business Name</label>
                        <input type="text" class="form-control" id="business_name" name="business_name">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Phone *</label>
                        <input type="text" class="form-control" id="phone" name="phone" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Email</label>
                        <input type="email" class="form-control" id="email" name="email">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Address</label>
                        <textarea class="form-control" id="address" name="address" rows="2"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Save Customer</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
let items = [];
let productSearchTimeout;

// ====================== ADD PRODUCT ======================
function addProduct(id, name, price, stock) {
    console.log('Adding product:', {id, name, price, stock}); // Debug log
    
    stock = parseFloat(stock);
    price = parseFloat(price);
    
    if (isNaN(stock) || stock <= 0) {
        showToast('Error', 'This product is out of stock!', 'error');
        return false;
    }
    
    if (isNaN(price) || price <= 0) {
        showToast('Error', 'Invalid product price!', 'error');
        return false;
    }

    const existingIndex = items.findIndex(item => item.product_id === parseInt(id));

    if (existingIndex !== -1) {
        // Product exists, increase quantity
        const newQty = items[existingIndex].quantity + 1;
        if (newQty > stock) {
            showToast('Error', `Only ${stock} units available in stock!`, 'error');
            return false;
        }
        items[existingIndex].quantity = newQty;
    } else {
        // Add new product
        items.push({
            product_id: parseInt(id),
            product_name: name,
            quantity: 1,
            unit_price: price,
            discount: 0,
            stock: stock
        });
    }

    renderItems();
    $('#productSearch').val('');
    $('#productSuggestions').hide();
    $('#productDropdown').val('');
    updateTotals();
    
    showToast('Success', `${name} added to cart`, 'success');
    return true;
}

// Add from Dropdown
function addProductFromDropdown() {
    const select = document.getElementById('productDropdown');
    const value = select.value;
    
    console.log('Dropdown selected value:', value); // Debug log
    
    if (!value) {
        showToast('Info', 'Please select a product', 'info');
        return;
    }

    const option = select.options[select.selectedIndex];
    const id = value;
    const name = option.getAttribute('data-name');
    const price = option.getAttribute('data-price');
    const stock = option.getAttribute('data-stock');
    
    console.log('Product data:', {id, name, price, stock}); // Debug log
    
    if (!name || !price) {
        showToast('Error', 'Invalid product data', 'error');
        return;
    }
    
    addProduct(id, name, price, stock);
}

// ====================== SEARCH ======================
function searchProducts(search) {
    if (!search || search.length < 2) {
        $('#productSuggestions').hide();
        return;
    }
    
    console.log('Searching for:', search); // Debug log
    
    $.ajax({
        url: '<?= base_url('sales/get-products') ?>',
        type: 'GET',
        data: { search: search },
        dataType: 'json',
        success: function(response) {
            console.log('Search response:', response); // Debug log
            
            let html = '';
            if (response.status === 'success' && response.data && response.data.length > 0) {
                response.data.forEach(product => {
                    const stockStatus = product.quantity > 0 ? 
                        `<span class="text-success">Stock: ${product.quantity}</span>` : 
                        `<span class="text-danger">Out of Stock</span>`;
                    
                    html += `<a href="#" class="list-group-item list-group-item-action" 
                               onclick="addProduct(${product.id}, '${product.product_name.replace(/'/g, "\\'")}', ${product.selling_price}, ${product.quantity}); return false;">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <strong>${escapeHtml(product.product_name)}</strong><br>
                                        <small class="text-muted">SKU: ${product.sku} | ${stockStatus}</small>
                                    </div>
                                    <div class="text-end">
                                        <strong>${parseFloat(product.selling_price).toFixed(2)}</strong>
                                    </div>
                                </div>
                             </a>`;
                });
            } else {
                html = '<div class="list-group-item text-muted">No products found</div>';
            }
            $('#productSuggestions').html(html).show();
        },
        error: function(xhr) {
            console.error('Search error:', xhr);
            $('#productSuggestions').html('<div class="list-group-item text-danger">Error loading products</div>').show();
        }
    });
}

// ====================== RENDER TABLE ======================
function renderItems() {
    let html = '';
    
    if (items.length === 0) {
        html = `<tr><td colspan="6" class="text-center text-muted py-4">No products added yet</td></tr>`;
        $('#itemsBody').html(html);
        return;
    }
    
    items.forEach((item, index) => {
        const lineTotal = (item.quantity * item.unit_price) - item.discount;
        html += `<tr>
            <td><strong>${escapeHtml(item.product_name)}</strong><br>
                    <small class="text-muted">Stock: ${item.stock}</small></td>
            <td>
                <input type="number" min="1" max="${item.stock}" step="1" class="form-control form-control-sm" 
                       value="${item.quantity}" onchange="updateItemQuantity(${index}, this.value)">
            </td>
            <td>
                <input type="number" step="0.01" class="form-control form-control-sm" 
                       value="${item.unit_price}" onchange="updateItemPrice(${index}, this.value)">
            </td>
            <td>
                <input type="number" step="0.01" class="form-control form-control-sm" 
                       value="${item.discount}" onchange="updateItemDiscount(${index}, this.value)">
            </td>
            <td class="fw-bold text-end">${formatNumber(lineTotal)}</td>
            <td class="text-center">
                <button class="btn btn-sm btn-danger" onclick="removeItem(${index})">
                    <i class="bi bi-trash"></i>
                </button>
            </td>
        </tr>`;
    });
    
    $('#itemsBody').html(html);
    updateTotals();
}

function updateItemQuantity(index, qty) {
    qty = parseFloat(qty) || 1;
    const item = items[index];
    
    if (qty > item.stock) {
        showToast('Error', `Maximum available stock is ${item.stock}`, 'error');
        qty = item.stock;
    }
    if (qty < 1) qty = 1;
    
    item.quantity = qty;
    renderItems();
}

function updateItemPrice(index, price) {
    items[index].unit_price = parseFloat(price) || 0;
    renderItems();
}

function updateItemDiscount(index, discount) {
    items[index].discount = parseFloat(discount) || 0;
    renderItems();
}

function removeItem(index) {
    const productName = items[index].product_name;
    items.splice(index, 1);
    renderItems();
    showToast('Info', `${productName} removed from cart`, 'info');
}

// ====================== TOTALS ======================
function updateTotals() {
    const subtotal = items.reduce((sum, item) => sum + (item.quantity * item.unit_price - item.discount), 0);
    const taxRate = <?= $tax_rate ?? 0 ?>;
    const tax = subtotal * (taxRate / 100);
    const discount = parseFloat($('#discount').val()) || 0;
    let total = subtotal + tax - discount;

    const paymentStatus = $('#payment_status').val();
    let amountPaid = parseFloat($('#amount_paid').val()) || 0;
    let balanceDue = total - amountPaid;

    if (paymentStatus === 'Paid') {
        amountPaid = total;
        balanceDue = 0;
        $('#amount_paid').val(amountPaid.toFixed(2));
    } else if (paymentStatus === 'Unpaid') {
        amountPaid = 0;
        balanceDue = total;
        $('#amount_paid').val(0);
    }

    $('#subtotal').text(formatNumber(subtotal));
    $('#tax').text(formatNumber(tax));
    $('#discountAmount').text(formatNumber(discount));
    $('#total').text(formatNumber(total));
    $('#balance_due').val(formatNumber(balanceDue));
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

function scanBarcode() {
    const barcode = prompt("Enter barcode or scan:");
    if (barcode && barcode.trim() !== '') {
        $('#productSearch').val(barcode.trim());
        searchProducts(barcode.trim());
    }
}

function showNewCustomer() {
    $('#customerForm')[0].reset();
    $('#customerModal').modal('show');
}

// ====================== FORM SUBMIT ======================
$(document).ready(function() {
    console.log('Sales create page loaded'); // Debug log
    console.log('Products available:', <?= json_encode(array_column($products, 'product_name')) ?>); // Debug log
    
    // Search with debounce
    $('#productSearch').on('keyup', function() {
        clearTimeout(productSearchTimeout);
        const search = $(this).val().trim();
        if (search.length > 1) {
            productSearchTimeout = setTimeout(() => searchProducts(search), 300);
        } else {
            $('#productSuggestions').hide();
        }
    });

    // Hide suggestions when clicking outside
    $(document).on('click', function(e) {
        if (!$(e.target).closest('#productSearch, #productSuggestions').length) {
            $('#productSuggestions').hide();
        }
    });

    $('#discount, #payment_status, #amount_paid').on('change keyup', updateTotals);
    
    // Test dropdown on change
    $('#productDropdown').on('change', function() {
        console.log('Dropdown changed to:', $(this).val());
    });

    // Main Form Submit
    $('#saleForm').on('submit', function(e) {
        e.preventDefault();

        console.log('Form submitted, items count:', items.length); // Debug log

        if (items.length === 0) {
            showToast('Error', 'Please add at least one product to the sale.', 'error');
            return;
        }

        const paymentMethod = $('#payment_method').val();
        if (!paymentMethod) {
            showToast('Error', 'Please select a payment method.', 'error');
            return;
        }

        const formData = {
            items: items.map(item => ({
                product_id: item.product_id,
                quantity: item.quantity,
                unit_price: item.unit_price,
                discount: item.discount
            })),
            customer_id: $('#customer_id').val() || null,
            currency: $('#currency').val(),
            payment_method: paymentMethod,
            payment_status: $('#payment_status').val(),
            amount_paid: parseFloat($('#amount_paid').val()) || 0,
            discount: parseFloat($('#discount').val()) || 0,
            notes: $('textarea[name="notes"]').val() || ''
        };

        console.log('Form data being sent:', formData); // Debug log

        const submitBtn = $('#submitBtn');
        submitBtn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-2"></span>Saving...');

        $.ajax({
            url: '<?= base_url('sales/store') ?>',
            type: 'POST',
            data: JSON.stringify(formData),
            contentType: 'application/json',
            dataType: 'json',
            success: function(response) {
                submitBtn.prop('disabled', false).html('Create Sale');
                console.log('Server response:', response); // Debug log
                
                if (response.status === 'success') {
                    showToast('Success', 'Sale created successfully!', 'success');
                    setTimeout(() => {
                        window.location.href = '<?= base_url('sales/invoice/') ?>' + response.sale_id;
                    }, 1500);
                } else {
                    showToast('Error', response.message || 'Failed to create sale', 'error');
                }
            },
            error: function(xhr) {
                submitBtn.prop('disabled', false).html('Create Sale');
                console.error('AJAX Error:', xhr);
                console.error('Response Text:', xhr.responseText);
                
                let errorMsg = 'Server error. Please try again.';
                try {
                    const response = JSON.parse(xhr.responseText);
                    if (response.message) errorMsg = response.message;
                } catch(e) {}
                showToast('Error', errorMsg, 'error');
            }
        });
    });

    // Customer Modal Form
    $('#customerForm').on('submit', function(e) {
        e.preventDefault();
        const submitBtn = $(this).find('button[type="submit"]');
        submitBtn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-2"></span>Saving...');
        
        $.ajax({
            url: '<?= base_url('customers/store') ?>',
            type: 'POST',
            data: $(this).serialize(),
            dataType: 'json',
            success: function(response) {
                submitBtn.prop('disabled', false).html('Save Customer');
                if (response.status === 'success') {
                    $('#customerModal').modal('hide');
                    showToast('Success', 'Customer added successfully!', 'success');
                    setTimeout(() => location.reload(), 1500);
                } else {
                    showToast('Error', response.message || 'Failed to add customer', 'error');
                }
            },
            error: function() {
                submitBtn.prop('disabled', false).html('Save Customer');
                showToast('Error', 'Failed to add customer', 'error');
            }
        });
    });
});

// Toast notification function
function showToast(title, message, type = 'success') {
    const bgColor = type === 'success' ? '#28a745' : type === 'error' ? '#dc3545' : '#17a2b8';
    const icon = type === 'success' ? 'check-circle' : type === 'error' ? 'exclamation-circle' : 'info-circle';
    
    const toast = $(`
        <div class="toast align-items-center text-white border-0 mb-2" role="alert" style="background-color: ${bgColor}; min-width: 300px;">
            <div class="d-flex">
                <div class="toast-body">
                    <i class="bi bi-${icon} me-2"></i>
                    <strong>${title}</strong><br>
                    <small>${message}</small>
                </div>
                <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
            </div>
        </div>
    `);
    
    $('.toast-container').append(toast);
    const bsToast = new bootstrap.Toast(toast[0], { autohide: true, delay: 3000 });
    bsToast.show();
    
    toast.on('hidden.bs.toast', function() {
        $(this).remove();
    });
}
</script>

<!-- Add toast container if not present -->

<?= $this->endSection() ?>