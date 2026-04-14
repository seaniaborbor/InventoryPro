<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0"><i class="bi bi-receipt me-2"></i>Sale Details #<?= $sale['invoice_number'] ?></h5>
                <div>
                    <!-- In the card-footer or action buttons section -->
                    <a href="<?= base_url('sales/edit/' . $sale['id']) ?>" class="btn btn-warning btn-sm">
                        <i class="bi bi-pencil"></i> Edit Sale
                    </a>
                    <a href="<?= base_url('sales/invoice/' . $sale['id']) ?>" class="btn btn-primary btn-sm"
                        target="_blank">
                        <i class="bi bi-printer"></i> Print Invoice
                    </a>
                    <a href="<?= base_url('sales') ?>" class="btn btn-secondary btn-sm">
                        <i class="bi bi-arrow-left"></i> Back
                    </a>
                </div>
            </div>
            <div class="card-body">
                <!-- Sale Information -->
                <div class="row mb-4">
                    <div class="col-md-6">
                        <div class="card">
                            <div class="card-header bg-light">
                                <h6 class="mb-0">Sale Information</h6>
                            </div>
                            <div class="card-body">
                                <table class="table table-sm table-borderless">
                                    <tr>
                                        <th width="35%">Invoice Number:</th>
                                        <td><strong><?= $sale['invoice_number'] ?></strong></td>
                                    </tr>
                                    <tr>
                                        <th>Sale Date:</th>
                                        <td><?= date('Y-m-d H:i', strtotime($sale['sale_date'])) ?></td>
                                    </tr>
                                    <tr>
                                        <th>Currency:</th>
                                        <td><?= $sale['currency'] ?></td>
                                    </tr>
                                    <tr>
                                        <th>Payment Method:</th>
                                        <td><?= $sale['payment_method'] ?></td>
                                    </tr>
                                    <tr>
                                        <th>Payment Status:</th>
                                        <td>
                                            <?php if ($sale['payment_status'] == 'Paid'): ?>
                                                <span class="badge bg-success">Paid</span>
                                            <?php elseif ($sale['payment_status'] == 'Partial'): ?>
                                                <span class="badge bg-warning">Partial</span>
                                            <?php else: ?>
                                                <span class="badge bg-danger">Unpaid</span>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                </table>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="card">
                            <div class="card-header bg-light">
                                <h6 class="mb-0">Customer Information</h6>
                            </div>
                            <div class="card-body">
                                <table class="table table-sm table-borderless">
                                    <tr>
                                        <th width="35%">Customer Name:</th>
                                        <td><?= $sale['customer_name'] ?></td>
                                    </tr>
                                    <?php if ($sale['customer_phone']): ?>
                                        <tr>
                                            <th>Phone:</th>
                                            <td><?= $sale['customer_phone'] ?></td>
                                        </tr>
                                    <?php endif; ?>
                                    <?php if ($sale['customer_email']): ?>
                                        <tr>
                                            <th>Email:</th>
                                            <td><?= $sale['customer_email'] ?></td>
                                        </tr>
                                    <?php endif; ?>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Items Table -->
                <div class="card mb-4">
                    <div class="card-header bg-light">
                        <h6 class="mb-0">Items Sold</h6>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-bordered mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>#</th>
                                        <th>Product</th>
                                        <th class="text-center">Quantity</th>
                                        <th class="text-end">Unit Price</th>
                                        <th class="text-end">Discount</th>
                                        <th class="text-end">Total</th>
                                </thead>
                                <tbody>
                                    <?php $counter = 1;
                                    foreach ($sale['items'] as $item): ?>
                                        <tr>
                                            <td><?= $counter++ ?></td>
                                            <td><?= $item['product_name'] ?>
                                                <br><small class="text-muted">SKU: <?= $item['sku'] ?></small>
                                            </td>
                                            <td class="text-center"><?= number_format($item['quantity'], 2) ?>
                                                <?= $item['unit_symbol'] ?? '' ?>
                                            </td>
                                            <td class="text-end">
                                                <?= formatCurrency($item['unit_price'], $sale['currency']) ?>
                                            </td>
                                            <td class="text-end"><?= formatCurrency($item['discount'], $sale['currency']) ?>
                                            </td>
                                            <td class="text-end fw-bold">
                                                <?= formatCurrency($item['total_price'], $sale['currency']) ?>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                                <tfoot>
                                    <tr>
                                        <td colspan="5" class="text-end fw-bold">Subtotal:</td>
                                        <td class="text-end"><?= formatCurrency($sale['subtotal'], $sale['currency']) ?>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td colspan="5" class="text-end">Tax:</td>
                                        <td class="text-end"><?= formatCurrency($sale['tax'], $sale['currency']) ?></td>
                                    </tr>
                                    <tr>
                                        <td colspan="5" class="text-end">Discount:</td>
                                        <td class="text-end">-
                                            <?= formatCurrency($sale['discount'], $sale['currency']) ?>
                                        </td>
                                    </tr>
                                    <tr class="table-primary">
                                        <td colspan="5" class="text-end fw-bold fs-5">Total:</td>
                                        <td class="text-end fw-bold fs-5">
                                            <?= formatCurrency($sale['total_amount'], $sale['currency']) ?>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td colspan="5" class="text-end">Amount Paid:</td>
                                        <td class="text-end">
                                            <?= formatCurrency($sale['amount_paid'], $sale['currency']) ?>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td colspan="5" class="text-end fw-bold">Balance Due:</td>
                                        <td class="text-end fw-bold text-danger">
                                            <?= formatCurrency($sale['balance_due'], $sale['currency']) ?>
                                        </td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- Adjustments (Refunds / Returns) -->
                <div class="card mt-4">
                    <div class="card-header bg-light d-flex justify-content-between align-items-center">
                        <h6 class="mb-0"><i class="bi bi-arrow-down-up me-2"></i>Refunds &amp; Returns</h6>
                        <!-- In the card-footer or action buttons section -->
                        <a href="<?= base_url('adjustments/create-from-sale/' . $sale['id']) ?>"
                            class="btn btn-warning btn-sm">
                            <i class="bi bi-arrow-return-left"></i> Process Return/Refund
                        </a>
                    </div>
                    <div class="card-body">
                        <div id="adjustmentList" class="text-muted"><em>Loading...</em></div>
                    </div>
                </div>

                <!-- Notes -->
                <?php if ($sale['notes']): ?>
                    <div class="card">
                        <div class="card-header bg-light">
                            <h6 class="mb-0">Notes</h6>
                        </div>
                        <div class="card-body">
                            <p><?= nl2br(esc($sale['notes'])) ?></p>
                        </div>
                    </div>
                <?php endif; ?>

                <!-- Payment Actions -->
                <?php if ($sale['balance_due'] > 0): ?>
                    <div class="card mt-4">
                        <div class="card-header bg-warning">
                            <h6 class="mb-0"><i class="bi bi-cash"></i> Payment Required</h6>
                        </div>
                        <div class="card-body">
                            <p class="text-danger fw-bold">Balance Due:
                                <?= formatCurrency($sale['balance_due'], $sale['currency']) ?>
                            </p>
                            <button class="btn btn-success" onclick="addPayment(<?= $sale['id'] ?>)">
                                <i class="bi bi-cash-stack"></i> Record Payment
                            </button>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- Payment Modal -->
<div class="modal fade" id="paymentModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Add Payment</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="paymentForm">
                <?= csrf_field() ?>
                <input type="hidden" name="sale_id" value="<?= $sale['id'] ?>">
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Invoice Number</label>
                        <p class="fw-bold"><?= $sale['invoice_number'] ?></p>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Balance Due</label>
                        <p class="fw-bold text-danger"><?= formatCurrency($sale['balance_due'], $sale['currency']) ?>
                        </p>
                    </div>
                    <div class="mb-3">
                        <label for="amount" class="form-label">Payment Amount *</label>
                        <input type="number" step="0.01" class="form-control" id="amount" name="amount"
                            max="<?= $sale['balance_due'] ?>" required>
                    </div>
                    <div class="mb-3">
                        <label for="payment_method" class="form-label">Payment Method *</label>
                        <select class="form-select" id="payment_method" name="payment_method" required>
                            <option value="">Select Method</option>
                            <option value="Cash">Cash</option>
                            <option value="Mobile Money">Mobile Money</option>
                            <option value="Bank Transfer">Bank Transfer</option>
                            <option value="Card">Card</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Add Payment</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    function addPayment(id) {
        $('#paymentModal').modal('show');
    }

    $('#paymentForm').on('submit', function (e) {
        e.preventDefault();

        var amount = $('#amount').val();
        var paymentMethod = $('#payment_method').val();

        if (!amount || parseFloat(amount) <= 0) {
            showToast('Error', 'Please enter a valid amount', 'error');
            return;
        }

        if (!paymentMethod) {
            showToast('Error', 'Please select a payment method', 'error');
            return;
        }

        $.ajax({
            url: '<?= base_url('sales/payment/') . $sale['id'] ?>',
            type: 'POST',
            data: $(this).serialize(),
            dataType: 'json',
            success: function (response) {
                if (response.status === 'success') {
                    $('#paymentModal').modal('hide');
                    showToast('Success', response.message, 'success');
                    setTimeout(() => location.reload(), 1500);
                } else {
                    showToast('Error', response.message, 'error');
                }
            },
            error: function () {
                showToast('Error', 'Failed to add payment', 'error');
            }
        });
    });

    // Load adjustments for this sale
    $(document).ready(function () {
        $.ajax({
            url: '<?= base_url('adjustments/api/sale/') ?><?= $sale['id'] ?>',
            type: 'GET',
            dataType: 'json',
            success: function (resp) {
                if (resp.data && resp.data.length > 0) {
                    var html = '<table class="table table-sm table-borderless"><thead><tr>' +
                        '<th>Type</th><th>Product</th><th>Qty</th><th>Value</th><th>Date</th><th>Logged By</th></tr></thead><tbody>';
                    resp.data.forEach(function (e) {
                        var badge = e.event_type === 'Refund' ? 'warning' : (e.event_type === 'Return' ? 'success' : 'secondary');
                        html += '<tr><td><span class="badge bg-' + badge + '">' + e.event_type + '</span></td>' +
                            '<td>' + (e.product_name || '') + '</td><td class="text-end">' + parseFloat(e.quantity).toFixed(2) + '</td>' +
                            '<td class="text-end">' + parseFloat(e.total_value || 0).toFixed(2) + '</td>' +
                            '<td><small>' + (e.event_date ? new Date(e.event_date).toLocaleDateString() : '') + '</small></td>' +
                            '<td><small>' + (e.created_by_name || '') + '</small></td></tr>';
                    });
                    html += '</tbody></table>';
                    $('#adjustmentList').html(html);
                } else {
                    $('#adjustmentList').html('<p class="text-muted mb-0">No refunds or returns recorded.</p>');
                }
            },
            error: function () {
                $('#adjustmentList').html('<p class="text-danger">Could not load adjustments.</p>');
            }
        });
    });
</script>
<?= $this->endSection() ?>