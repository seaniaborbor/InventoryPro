<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0"><i class="bi bi-receipt me-2"></i>Invoice #<?= $sale['invoice_number'] ?></h5>
                <div>
                    <button class="btn btn-primary btn-sm me-2" onclick="window.print()">
                        <i class="bi bi-printer"></i> Print
                    </button>
                    <?php if ($sale['customer_email']): ?>
                    <button class="btn btn-info btn-sm" onclick="emailInvoice()">
                        <i class="bi bi-envelope"></i> Email
                    </button>
                    <?php endif; ?>
                </div>
            </div>
            <div class="card-body" id="invoiceContent">
                <!-- Invoice Content -->
                <div class="invoice-container">
                    <div class="invoice-header">
                        <?php if ($business['logo']): ?>
                            <img src="<?= base_url($business['logo']) ?>" alt="Logo" style="max-height: 80px;">
                        <?php endif; ?>
                        <h2 class="mt-3"><?= $business['name'] ?></h2>
                        <p><?= $business['address'] ?><br>Phone: <?= $business['phone'] ?> | Email: <?= $business['email'] ?></p>
                        <hr>
                        <h3>INVOICE</h3>
                    </div>
                    
                    <div class="row mb-4">
                        <div class="col-6">
                            <strong>Invoice Number:</strong> <?= $sale['invoice_number'] ?><br>
                            <strong>Date:</strong> <?= date('d/m/Y', strtotime($sale['sale_date'])) ?><br>
                            <strong>Status:</strong> 
                            <span class="badge <?= $sale['payment_status'] == 'Paid' ? 'bg-success' : ($sale['payment_status'] == 'Partial' ? 'bg-warning' : 'bg-danger') ?>">
                                <?= $sale['payment_status'] ?>
                            </span>
                        </div>
                        <div class="col-6">
                            <strong>Bill To:</strong><br>
                            <?= $sale['customer_name'] ?><br>
                            <?php if ($sale['customer_phone']): ?>Phone: <?= $sale['customer_phone'] ?><br><?php endif; ?>
                            <?php if ($sale['customer_email']): ?>Email: <?= $sale['customer_email'] ?><?php endif; ?>
                        </div>
                    </div>
                    
                    <table class="table table-bordered">
                        <thead class="table-light">
                            <tr>
                                <th>#</th>
                                <th>Item</th>
                                <th class="text-center">Quantity</th>
                                <th class="text-end">Unit Price</th>
                                <th class="text-end">Discount</th>
                                <th class="text-end">Total</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php $counter = 1; foreach ($sale['items'] as $item): ?>
                            <tr>
                                <td><?= $counter++ ?></td>
                                <td><?= $item['product_name'] ?></td>
                                <td class="text-center"><?= number_format($item['quantity'], 2) ?> <?= $item['unit_symbol'] ?? '' ?></td>
                                <td class="text-end"><?= formatCurrency($item['unit_price'], $sale['currency']) ?></td>
                                <td class="text-end"><?= formatCurrency($item['discount'], $sale['currency']) ?></td>
                                <td class="text-end"><?= formatCurrency($item['total_price'], $sale['currency']) ?></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                        <tfoot>
                            <tr>
                                <td colspan="5" class="text-end fw-bold">Subtotal:</td>
                                <td class="text-end"><?= formatCurrency($sale['subtotal'], $sale['currency']) ?></td>
                            </tr>
                            <tr>
                                <td colspan="5" class="text-end">Tax:</td>
                                <td class="text-end"><?= formatCurrency($sale['tax'], $sale['currency']) ?></td>
                            </tr>
                            <tr>
                                <td colspan="5" class="text-end">Discount:</td>
                                <td class="text-end">- <?= formatCurrency($sale['discount'], $sale['currency']) ?></td>
                            </tr>
                            <tr class="table-primary">
                                <td colspan="5" class="text-end fw-bold fs-5">Total:</td>
                                <td class="text-end fw-bold fs-5"><?= formatCurrency($sale['total_amount'], $sale['currency']) ?></td>
                            </tr>
                            <tr>
                                <td colspan="5" class="text-end">Amount Paid:</td>
                                <td class="text-end"><?= formatCurrency($sale['amount_paid'], $sale['currency']) ?></td>
                            </tr>
                            <tr>
                                <td colspan="5" class="text-end fw-bold">Balance Due:</td>
                                <td class="text-end fw-bold text-danger"><?= formatCurrency($sale['balance_due'], $sale['currency']) ?></td>
                            </tr>
                        </tfoot>
                    </table>
                    
                    <div class="invoice-footer mt-4">
                        <?php if ($sale['qr_code']): ?>
                            <img src="<?= $sale['qr_code'] ?>" alt="QR Code" style="max-width: 100px;">
                        <?php endif; ?>
                        <p class="mt-3">Thank you for your business!</p>
                        <p class="small text-muted"><?= $business['name'] ?> - <?= $business['phone'] ?></p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
@media print {
    .navbar, #sidebar, .card-header .btn, .card-footer {
        display: none !important;
    }
    #sidebar, #content {
        margin: 0 !important;
        padding: 0 !important;
    }
    .card {
        border: none !important;
        box-shadow: none !important;
    }
    .invoice-container {
        padding: 0 !important;
    }
}
</style>

<script>
function emailInvoice() {
    $.ajax({
        url: '/sales/email/<?= $sale['id'] ?>',
        type: 'POST',
        data: {<?= csrf_token() ?>: '<?= csrf_hash() ?>'},
        success: function(response) {
            if (response.status === 'success') {
                showToast('Success', response.message, 'success');
            } else {
                showToast('Error', response.message, 'error');
            }
        }
    });
}
</script>
<?= $this->endSection() ?>