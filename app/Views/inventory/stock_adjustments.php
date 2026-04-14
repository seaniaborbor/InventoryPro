<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0"><i class="bi bi-arrow-left-right me-2"></i>Stock Adjustments</h5>
            </div>
            <div class="card-body">
                <div class="alert alert-info">
                    <i class="bi bi-info-circle"></i> 
                    <strong>Stock Adjustments:</strong> Use this page to view all stock adjustment history. To make a new adjustment, click the "Adjust Stock" button below.
                </div>
                
                <!-- Button to trigger modal -->
                <div class="mb-3 text-end">
                    <button type="button" class="btn btn-primary" id="openStockModalBtn">
                        <i class="bi bi-plus-circle"></i> Adjust Stock
                    </button>
                </div>
                
                <!-- Filter Bar -->
                <div class="row mb-3">
                    <div class="col-md-3">
                        <select id="filterProduct" class="form-select">
                            <option value="">All Products</option>
                            <?php foreach ($products as $product): ?>
                                <option value="<?= $product['id'] ?>"><?= esc($product['product_name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <select id="filterType" class="form-select">
                            <option value="all">All Adjustments</option>
                            <option value="increase">Increase (+)</option>
                            <option value="decrease">Decrease (-)</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <input type="date" id="filterDate" class="form-control" placeholder="Filter by date">
                    </div>
                    <div class="col-md-3">
                        <button class="btn btn-secondary w-100" onclick="resetFilters()">
                            <i class="bi bi-arrow-repeat"></i> Reset
                        </button>
                    </div>
                </div>
                
                <div class="table-responsive">
                    <table class="table table-hover" id="adjustmentsTable">
                        <thead class="table-light">
                            <tr>
                                <th>Date & Time</th>
                                <th>Product</th>
                                <th>Adjustment Type</th>
                                <th>Quantity</th>
                                <th>Previous Stock</th>
                                <th>New Stock</th>
                                <th>Reason</th>
                                <th>User</th>
                            </thead>
                        <tbody id="adjustmentsBody">
                            <tr>
                                <td colspan="8" class="text-center text-muted py-4">
                                    <div class="py-5">
                                        <i class="bi bi-arrow-repeat" style="font-size: 2rem;"></i>
                                        <p class="mt-2">Loading stock adjustments...</p>
                                    </div>
                                 </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Stock Adjustment Modal -->
<div class="modal fade" id="stockModal" tabindex="-1" data-bs-backdrop="static">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="bi bi-arrow-left-right me-2"></i>Adjust Stock Quantity
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="stockAdjustmentForm">
                <?= csrf_field() ?>
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="modal_product_id" class="form-label">Select Product *</label>
                        <select class="form-select" id="modal_product_id" name="product_id" required>
                            <option value="">-- Select Product --</option>
                            <?php foreach ($products as $product): ?>
                                <option value="<?= $product['id'] ?>" 
                                        data-current-stock="<?= $product['quantity'] ?>"
                                        data-product-name="<?= esc($product['product_name']) ?>"
                                        data-unit="<?= esc($product['unit_symbol'] ?? 'units') ?>">
                                    <?= esc($product['product_name']) ?> 
                                    (SKU: <?= esc($product['sku']) ?>) - 
                                    Stock: <?= number_format($product['quantity'], 2) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="row mb-3">
                        <div class="col-6">
                            <label class="form-label">Current Stock</label>
                            <input type="text" class="form-control bg-light" id="modal_current_stock" readonly>
                        </div>
                        <div class="col-6">
                            <label class="form-label">Unit</label>
                            <input type="text" class="form-control bg-light" id="modal_unit" readonly>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Adjustment Type</label>
                        <div class="d-flex gap-4">
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="adjustment_type" id="type_increase" value="increase" checked>
                                <label class="form-check-label text-success" for="type_increase">
                                    <i class="bi bi-plus-circle"></i> Increase (+)
                                </label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="adjustment_type" id="type_decrease" value="decrease">
                                <label class="form-check-label text-danger" for="type_decrease">
                                    <i class="bi bi-dash-circle"></i> Decrease (-)
                                </label>
                            </div>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="modal_quantity" class="form-label">Quantity *</label>
                        <div class="input-group">
                            <input type="number" step="0.01" min="0.01" class="form-control" 
                                   id="modal_quantity" name="quantity" required>
                            <span class="input-group-text" id="modal_unit_text">units</span>
                        </div>
                        <div class="form-text">Enter the quantity to add or remove from stock</div>
                    </div>
                    
                    <div class="mb-3" id="modal_warning_div" style="display: none;">
                        <div class="alert alert-warning py-2">
                            <i class="bi bi-exclamation-triangle"></i> 
                            <span id="modal_warning_text"></span>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="modal_new_stock" class="form-label">New Stock After Adjustment</label>
                        <input type="text" class="form-control bg-info text-white fw-bold" id="modal_new_stock" readonly>
                    </div>
                    
                    <div class="mb-3">
                        <label for="modal_reason" class="form-label">Reason *</label>
                        <select class="form-select" id="modal_reason" name="reason" required>
                            <option value="">-- Select Reason --</option>
                            <option value="Stock Count Correction">📊 Stock Count Correction</option>
                            <option value="Damaged Goods">💔 Damaged Goods</option>
                            <option value="Expired Products">⏰ Expired Products</option>
                            <option value="Return to Supplier">📦 Return to Supplier</option>
                            <option value="Customer Return">🔄 Customer Return</option>
                            <option value="Theft/Loss">🚨 Theft/Loss</option>
                            <option value="Quality Control">✅ Quality Control Rejection</option>
                            <option value="Donation">🎁 Donation / Giveaway</option>
                            <option value="Other">📝 Other</option>
                        </select>
                    </div>
                    
                    <div class="mb-3">
                        <label for="modal_notes" class="form-label">Notes (Optional)</label>
                        <textarea class="form-control" id="modal_notes" name="notes" rows="2" 
                                  placeholder="Add any additional details about this adjustment..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="bi bi-x-circle"></i> Cancel
                    </button>
                    <button type="submit" class="btn btn-primary" id="modal_submit_btn">
                        <i class="bi bi-check-circle"></i> Apply Adjustment
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
$(document).ready(function() {
    loadAdjustments();
    
    // Open modal button
    $('#openStockModalBtn').on('click', function() {
        resetModalForm();
        $('#stockModal').modal('show');
    });
    
    // When product is selected, update current stock display
    $('#modal_product_id').on('change', function() {
        const selectedOption = $(this).find(':selected');
        const currentStock = selectedOption.data('current-stock') || 0;
        const unit = selectedOption.data('unit') || 'units';
        
        $('#modal_current_stock').val(parseFloat(currentStock).toLocaleString('en-US', {
            minimumFractionDigits: 2, 
            maximumFractionDigits: 2
        }));
        $('#modal_unit').val(unit);
        $('#modal_unit_text').text(unit);
        
        calculateNewStock();
    });
    
    // Calculate new stock when quantity or type changes
    $('#modal_quantity, input[name="adjustment_type"]').on('change keyup', function() {
        calculateNewStock();
    });
    
    function calculateNewStock() {
        const currentStock = parseFloat($('#modal_product_id').find(':selected').data('current-stock')) || 0;
        const quantity = parseFloat($('#modal_quantity').val()) || 0;
        const adjustmentType = $('input[name="adjustment_type"]:checked').val();
        
        let newStock = currentStock;
        let warning = '';
        
        if (adjustmentType === 'increase') {
            newStock = currentStock + quantity;
        } else if (adjustmentType === 'decrease') {
            newStock = currentStock - quantity;
            if (quantity > currentStock && quantity > 0) {
                warning = `Warning: You are trying to decrease by ${quantity.toFixed(2)} but only ${currentStock.toFixed(2)} is available. This will result in negative stock!`;
            }
        }
        
        $('#modal_new_stock').val(newStock.toLocaleString('en-US', {
            minimumFractionDigits: 2, 
            maximumFractionDigits: 2
        }));
        
        if (warning) {
            $('#modal_warning_text').text(warning);
            $('#modal_warning_div').show();
        } else {
            $('#modal_warning_div').hide();
        }
        
        if (newStock < 0) {
            $('#modal_new_stock').removeClass('bg-info text-white').addClass('bg-danger text-white');
        } else if (newStock === 0) {
            $('#modal_new_stock').removeClass('bg-info text-white').addClass('bg-warning text-dark');
        } else {
            $('#modal_new_stock').removeClass('bg-danger text-white bg-warning text-dark').addClass('bg-info text-white');
        }
    }
    
    function resetModalForm() {
        $('#modal_product_id').val('');
        $('#modal_current_stock').val('');
        $('#modal_unit').val('');
        $('#modal_quantity').val('');
        $('#modal_reason').val('');
        $('#modal_notes').val('');
        $('#modal_new_stock').val('');
        $('#modal_warning_div').hide();
        $('input[name="adjustment_type"][value="increase"]').prop('checked', true);
    }
    
    // Form submission
    $('#stockAdjustmentForm').on('submit', function(e) {
        e.preventDefault();
        
        const productId = $('#modal_product_id').val();
        const adjustmentType = $('input[name="adjustment_type"]:checked').val();
        const quantity = parseFloat($('#modal_quantity').val());
        const reason = $('#modal_reason').val();
        const notes = $('#modal_notes').val();
        const currentStock = parseFloat($('#modal_product_id').find(':selected').data('current-stock')) || 0;
        
        if (!productId) {
            Swal.fire('Error', 'Please select a product', 'error');
            return;
        }
        
        if (!quantity || quantity <= 0) {
            Swal.fire('Error', 'Please enter a valid quantity', 'error');
            return;
        }
        
        if (!reason) {
            Swal.fire('Error', 'Please select a reason for the adjustment', 'error');
            return;
        }
        
        const submitBtn = $('#modal_submit_btn');
        submitBtn.prop('disabled', true).html('<i class="bi bi-hourglass-split"></i> Processing...');
        
        function doSubmit() {
            $.ajax({
                url: '<?= base_url('inventory/stock-adjustments/adjust') ?>',
                type: 'POST',
                data: {
                    product_id: productId,
                    adjustment_type: adjustmentType,
                    quantity: quantity,
                    reason: reason,
                    notes: notes,
                    '<?= csrf_token() ?>': '<?= csrf_hash() ?>'
                },
                dataType: 'json',
                success: function(response) {
                    if (response.status === 'success') {
                        Swal.fire({
                            icon: 'success',
                            title: 'Success!',
                            text: response.message,
                            timer: 1500,
                            showConfirmButton: false
                        });
                        
                        $('#stockModal').modal('hide');
                        loadAdjustments();
                    } else {
                        Swal.fire('Error', response.message || 'Failed to adjust stock', 'error');
                    }
                },
                error: function(xhr) {
                    console.error(xhr.responseText);
                    Swal.fire('Error', 'An error occurred. Please try again.', 'error');
                },
                complete: function() {
                    submitBtn.prop('disabled', false).html('<i class="bi bi-check-circle"></i> Apply Adjustment');
                }
            });
        }
        
        if (adjustmentType === 'decrease' && quantity > currentStock) {
            Swal.fire({
                title: 'Warning!',
                text: `You are about to decrease stock by ${quantity.toFixed(2)} but only ${currentStock.toFixed(2)} is available. This will result in negative stock. Continue anyway?`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'Yes, continue'
            }).then((result) => {
                if (result.isConfirmed) {
                    doSubmit();
                } else {
                    submitBtn.prop('disabled', false).html('<i class="bi bi-check-circle"></i> Apply Adjustment');
                }
            });
        } else {
            doSubmit();
        }
    });
    
    // Filter by product
    $('#filterProduct').on('change', function() {
        loadAdjustments();
    });
    
    // Filter by type
    $('#filterType').on('change', function() {
        filterTable();
    });
    
    // Filter by date
    $('#filterDate').on('change', function() {
        filterTable();
    });
});

function loadAdjustments() {
    const productId = $('#filterProduct').val();
    let url = '<?= base_url('inventory/get-stock-adjustments') ?>';
    if (productId) {
        url += '?product_id=' + productId;
    }
    
    $.ajax({
        url: url,
        type: 'GET',
        dataType: 'json',
        success: function(response) {
            if (response.status === 'success') {
                const data = response.data;
                
                if (data.length === 0) {
                    $('#adjustmentsBody').html('<tr><td colspan="8" class="text-center text-muted py-4"><div class="py-5"><i class="bi bi-inbox" style="font-size: 2rem;"></i><p class="mt-2">No stock adjustments found. Click "Adjust Stock" to make your first adjustment.</p></div></td></tr>');
                    return;
                }
                
                let html = '';
                data.forEach(function(adj) {
                    const isIncrease = adj.adjustment_type === 'increase';
                    const badgeClass = isIncrease ? 'bg-success' : 'bg-danger';
                    const sign = isIncrease ? '+' : '-';
                    
                    html += `<tr>
                        <td><span class="text-nowrap">${adj.created_date || '-'}</span><br><small class="text-muted">${adj.created_time || ''}</small></td>
                        <td><strong>${escapeHtml(adj.product_name || 'Unknown')}</strong></td>
                        <td><span class="badge ${badgeClass}">${isIncrease ? 'Increase (+)' : 'Decrease (-)'}</span></td>
                        <td class="${isIncrease ? 'text-success' : 'text-danger'} fw-bold">${sign} ${parseFloat(adj.quantity).toLocaleString('en-US', {minimumFractionDigits: 2})}</td>
                        <td>${parseFloat(adj.previous_quantity).toLocaleString('en-US', {minimumFractionDigits: 2})}</td>
                        <td class="fw-bold">${parseFloat(adj.new_quantity).toLocaleString('en-US', {minimumFractionDigits: 2})}</td>
                        <td>${escapeHtml(adj.reason || 'Stock Adjustment')}</td>
                        <td>${escapeHtml(adj.created_by_name || 'System')}</td>
                    </tr>`;
                });
                
                $('#adjustmentsBody').html(html);
                filterTable();
            } else {
                $('#adjustmentsBody').html('<tr><td colspan="8" class="text-center text-danger">Error loading data: ' + (response.message || 'Unknown error') + '</td></tr>');
            }
        },
        error: function(xhr, status, error) {
            console.error('AJAX Error:', error);
            $('#adjustmentsBody').html('<tr><td colspan="8" class="text-center text-danger">Failed to load stock adjustments. Please refresh the page.</td></tr>');
        }
    });
}

function filterTable() {
    const filterType = $('#filterType').val();
    const filterDate = $('#filterDate').val();
    
    $('#adjustmentsBody tr').each(function() {
        let show = true;
        const row = $(this);
        const typeCell = row.find('td:eq(2)').text().toLowerCase();
        const dateCell = row.find('td:eq(0)').text();
        
        if (filterType !== 'all') {
            const isIncrease = typeCell.includes('increase');
            if ((filterType === 'increase' && !isIncrease) || (filterType === 'decrease' && isIncrease)) {
                show = false;
            }
        }
        
        if (filterDate && dateCell) {
            const rowDate = dateCell.split(' ')[0];
            if (rowDate !== filterDate) {
                show = false;
            }
        }
        
        row.toggle(show);
    });
}

function resetFilters() {
    $('#filterProduct').val('');
    $('#filterType').val('all');
    $('#filterDate').val('');
    loadAdjustments();
}

function escapeHtml(str) {
    if (!str) return '';
    return str.replace(/[&<>]/g, function(m) {
        if (m === '&') return '&amp;';
        if (m === '<') return '&lt;';
        if (m === '>') return '&gt;';
        return m;
    });
}
</script>

<style>

#modal_new_stock {
    font-weight: bold;
    font-size: 1.1rem;
}
.badge {
    font-size: 0.8rem;
    padding: 5px 10px;
}
.text-nowrap {
    white-space: nowrap;
}
</style>

<?= $this->endSection() ?>