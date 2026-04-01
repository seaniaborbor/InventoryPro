<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0"><i class="bi bi-cart me-2"></i>Sales</h5>
                <a href="<?= base_url('sales/create') ?>" class="btn btn-primary btn-sm">
                    <i class="bi bi-plus-circle"></i> New Sale
                </a>
            </div>
            <div class="card-body">
                <div class="row mb-3">
                    <div class="col-md-4">
                        <input type="text" id="searchInput" class="form-control" placeholder="Search by invoice or customer...">
                    </div>
                    <div class="col-md-3">
                        <select id="statusFilter" class="form-select">
                            <option value="">All Status</option>
                            <option value="Paid">Paid</option>
                            <option value="Unpaid">Unpaid</option>
                            <option value="Partial">Partial</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <select id="currencyFilter" class="form-select">
                            <option value="">All Currencies</option>
                            <option value="LRD">LRD</option>
                            <option value="USD">USD</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <button class="btn btn-secondary w-100" onclick="resetFilters()">Reset</button>
                    </div>
                </div>
                
                <div class="table-responsive">
                    <table class="table table-hover" id="salesTable">
                        <thead>
                            <tr>
                                <th>Invoice #</th>
                                <th>Date</th>
                                <th>Customer</th>
                                <th>Total Amount</th>
                                <th>Paid</th>
                                <th>Balance</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($sales as $sale): ?>
                            <tr>
                                <td><strong><?= $sale['invoice_number'] ?></strong></td>
                                <td><?= date('Y-m-d', strtotime($sale['sale_date'])) ?></td>
                                <td><?= $sale['customer_name'] ?></td>
                                <td><?= formatCurrency($sale['total_amount'], $sale['currency']) ?></td>
                                <td><?= formatCurrency($sale['amount_paid'], $sale['currency']) ?></td>
                                <td class="<?= $sale['balance_due'] > 0 ? 'text-danger' : 'text-success' ?>">
                                    <?= formatCurrency($sale['balance_due'], $sale['currency']) ?>
                                </td>
                                <td>
                                    <?php if ($sale['payment_status'] == 'Paid'): ?>
                                        <span class="badge bg-success">Paid</span>
                                    <?php elseif ($sale['payment_status'] == 'Partial'): ?>
                                        <span class="badge bg-warning">Partial</span>
                                    <?php else: ?>
                                        <span class="badge bg-danger">Unpaid</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <a href="<?= base_url('sales/view/' . $sale['id']) ?>" class="btn btn-sm btn-info">
                                        <i class="bi bi-eye"></i>
                                    </a>
                                    <a href="<?= base_url('sales/invoice/' . $sale['id']) ?>" class="btn btn-sm btn-primary" target="_blank">
                                        <i class="bi bi-printer"></i>
                                    </a>
                                    <?php if ($sale['balance_due'] > 0): ?>
                                        <button class="btn btn-sm btn-warning" onclick="addPayment(<?= $sale['id'] ?>, '<?= $sale['invoice_number'] ?>', <?= $sale['balance_due'] ?>, '<?= $sale['currency'] ?>')">
                                            <i class="bi bi-cash"></i>
                                        </button>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                
                <?= $pager->links() ?>
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
                <input type="hidden" name="sale_id" id="paymentSaleId">
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Invoice Number</label>
                        <p class="fw-bold" id="paymentInvoiceNumber"></p>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Balance Due</label>
                        <p class="fw-bold text-danger" id="paymentBalanceDue"></p>
                    </div>
                    <div class="mb-3">
                        <label for="amount" class="form-label">Payment Amount *</label>
                        <input type="number" step="0.01" class="form-control" id="amount" name="amount" required>
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
$(document).ready(function() {
    var table = $('#salesTable').DataTable({
        pageLength: 20,
        order: [[1, 'desc']],
        searching: false,
        paging: false,
        info: false
    });
    
    $('#searchInput').on('keyup', function() {
        table.search(this.value).draw();
    });
    
    $('#statusFilter').on('change', function() {
        table.column(6).search(this.value).draw();
    });
    
    $('#currencyFilter').on('change', function() {
        table.column(3).search(this.value, true, false).draw();
    });
    
    $('#paymentForm').on('submit', function(e) {
        e.preventDefault();
        var formData = $(this).serialize();
        var saleId = $('#paymentSaleId').val();
        
        $.ajax({
            url: '/sales/payment/' + saleId,
            type: 'POST',
            data: formData,
            success: function(response) {
                if (response.status === 'success') {
                    $('#paymentModal').modal('hide');
                    showToast('Success', response.message, 'success');
                    setTimeout(() => location.reload(), 1500);
                } else {
                    showToast('Error', response.message, 'error');
                }
            }
        });
    });
});

function addPayment(id, invoice, balance, currency) {
    $('#paymentSaleId').val(id);
    $('#paymentInvoiceNumber').text(invoice);
    $('#paymentBalanceDue').text(formatCurrency(balance, currency));
    $('#paymentModal').modal('show');
}

function resetFilters() {
    $('#searchInput').val('');
    $('#statusFilter').val('');
    $('#currencyFilter').val('');
    $('#salesTable').DataTable().search('').draw();
}
</script>
<?= $this->endSection() ?>