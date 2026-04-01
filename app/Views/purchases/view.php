<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0"><i class="bi bi-receipt me-2"></i>Purchase #<?= $purchase['purchase_number'] ?></h5>
                <div>
                    <button class="btn btn-primary btn-sm" onclick="window.print()">
                        <i class="bi bi-printer"></i> Print
                    </button>
                    <?php if ($purchase['status'] == 'Draft'): ?>
                        <button class="btn btn-success btn-sm" onclick="receivePurchase()">
                            <i class="bi bi-check-circle"></i> Receive
                        </button>
                    <?php endif; ?>
                </div>
            </div>
            <div class="card-body">
                <div class="row mb-4">
                    <div class="col-md-6">
                        <strong>Purchase Number:</strong> <?= $purchase['purchase_number'] ?><br>
                        <strong>Date:</strong> <?= date('d/m/Y', strtotime($purchase['purchase_date'])) ?><br>
                        <strong>Status:</strong> 
                        <span class="badge <?= $purchase['status'] == 'Received' ? 'bg-success' : 'bg-warning' ?>">
                            <?= $purchase['status'] ?>
                        </span>
                    </div>
                    <div class="col-md-6">
                        <strong>Supplier:</strong><br>
                        <?= $purchase['supplier']['supplier_name'] ?? $purchase['supplier_name'] ?><br>
                        <?php if (isset($purchase['supplier']['phone'])): ?>
                            Phone: <?= $purchase['supplier']['phone'] ?><br>
                        <?php endif; ?>
                        <?php if (isset($purchase['supplier']['email'])): ?>
                            Email: <?= $purchase['supplier']['email'] ?>
                        <?php endif; ?>
                    </div>
                </div>
                
                <div class="table-responsive">
                    <table class="table table-bordered">
                        <thead class="table-light">
                            <tr>
                                <th>#</th>
                                <th>Product</th>
                                <th class="text-center">Quantity</th>
                                <th class="text-end">Unit Price</th>
                                <th class="text-end">Total</th>
                            </thead>
                        <tbody>
                            <?php $counter = 1; foreach ($purchase['items'] as $item): ?>
                            <tr>
                                <td><?= $counter++ ?></td>
                                <td><?= $item['product_name'] ?></td>
                                <td class="text-center"><?= number_format($item['quantity'], 2) ?> <?= $item['unit_symbol'] ?? '' ?></td>
                                <td class="text-end"><?= formatCurrency($item['unit_price'], $purchase['currency']) ?></td>
                                <td class="text-end"><?= formatCurrency($item['total_price'], $purchase['currency']) ?></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                        <tfoot>
                            <tr>
                                <td colspan="4" class="text-end fw-bold">Subtotal:</td>
                                <td class="text-end"><?= formatCurrency($purchase['subtotal'], $purchase['currency']) ?></td>
                            </tr>
                            <tr>
                                <td colspan="4" class="text-end">Tax:</td>
                                <td class="text-end"><?= formatCurrency($purchase['tax'], $purchase['currency']) ?></td>
                            </tr>
                            <tr>
                                <td colspan="4" class="text-end">Discount:</td>
                                <td class="text-end">- <?= formatCurrency($purchase['discount'], $purchase['currency']) ?></td>
                            </tr>
                            <tr class="table-primary">
                                <td colspan="4" class="text-end fw-bold fs-5">Total:</td>
                                <td class="text-end fw-bold fs-5"><?= formatCurrency($purchase['total_amount'], $purchase['currency']) ?></td>
                            </tr>
                            <tr>
                                <td colspan="4" class="text-end">Payment Status:</td>
                                <td class="text-end">
                                    <span class="badge <?= $purchase['payment_status'] == 'Paid' ? 'bg-success' : ($purchase['payment_status'] == 'Partial' ? 'bg-warning' : 'bg-danger') ?>">
                                        <?= $purchase['payment_status'] ?>
                                    </span>
                                </td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
                
                <?php if ($purchase['notes']): ?>
                <div class="mt-3">
                    <strong>Notes:</strong><br>
                    <?= $purchase['notes'] ?>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<script>
function receivePurchase() {
    Swal.fire({
        title: 'Receive Purchase?',
        text: 'This will add the items to inventory. Are you sure?',
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#28a745',
        cancelButtonColor: '#6c757d',
        confirmButtonText: 'Yes, receive!'
    }).then((result) => {
        if (result.isConfirmed) {
            $.ajax({
                url: '<?= base_url('purchases/receive/' . $purchase['id']) ?>',
                type: 'POST',
                data: {<?= csrf_token() ?>: '<?= csrf_hash() ?>'},
                success: function(response) {
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
</script>
<?= $this->endSection() ?>