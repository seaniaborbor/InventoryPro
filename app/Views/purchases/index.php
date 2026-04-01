<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">
                    <i class="bi bi-cart-plus me-2"></i>Purchases
                </h5>
                <a href="<?= base_url('purchases/create') ?>" class="btn btn-primary btn-sm">
                    <i class="bi bi-plus-circle"></i> New Purchase
                </a>
            </div>

            <div class="card-body">

                <!-- Flash Messages -->
                <?php if (session()->getFlashdata('success')): ?>
                    <div class="alert alert-success alert-dismissible fade show">
                        <?= session()->getFlashdata('success') ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>

                <?php if (session()->getFlashdata('error')): ?>
                    <div class="alert alert-danger alert-dismissible fade show">
                        <?= session()->getFlashdata('error') ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>

                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead class="table-light">
                            <tr>
                                <th>Purchase #</th>
                                <th>Date</th>
                                <th>Supplier</th>
                                <th class="text-end">Total</th>
                                <th>Payment Status</th>
                                <th>Status</th>
                                <th class="text-center">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($purchases)): ?>
                                <tr>
                                    <td colspan="7" class="text-center text-muted py-5">
                                        No purchases found
                                    </td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($purchases as $purchase): ?>
                                    <tr>
                                        <td>
                                            <a href="<?= base_url('purchases/view/' . $purchase['id']) ?>" 
                                               class="fw-medium text-decoration-none">
                                                <?= esc($purchase['purchase_number']) ?>
                                            </a>
                                        </td>
                                        <td><?= date('M j, Y', strtotime($purchase['purchase_date'])) ?></td>
                                        <td><?= esc($purchase['supplier_name']) ?></td>
                                        <td class="text-end fw-medium">
                                            <?= esc($purchase['currency']) ?> 
                                            <?= number_format($purchase['total_amount'], 2) ?>
                                        </td>
                                        <td>
                                            <span class="badge bg-<?= $purchase['payment_status'] === 'Paid' ? 'success' : 'warning' ?>">
                                                <?= esc($purchase['payment_status']) ?>
                                            </span>
                                        </td>
                                        <td>
                                            <span class="badge bg-<?= $purchase['status'] === 'Received' ? 'success' : 
                                                ($purchase['status'] === 'Draft' ? 'secondary' : 'primary') ?>">
                                                <?= esc($purchase['status']) ?>
                                            </span>
                                        </td>
                                        <td class="text-center">
                                            <div class="btn-group btn-group-sm" role="group">
                                                <a href="<?= base_url('purchases/view/' . $purchase['id']) ?>" 
                                                   class="btn btn-outline-info" title="View">
                                                    <i class="bi bi-eye"></i>
                                                </a>
                                                
                                                <?php if ($purchase['status'] === 'Draft'): ?>
                                                    <button onclick="receivePurchase(<?= $purchase['id'] ?>)" 
                                                            class="btn btn-outline-success" title="Receive Purchase">
                                                        <i class="bi bi-box-seam"></i>
                                                    </button>
                                                <?php endif; ?>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <?php if (!empty($purchases)): ?>
                    <div class="d-flex justify-content-between align-items-center mt-4">
                        <?= $pager->links() ?>
                    </div>
                <?php endif; ?>

            </div>
        </div>
    </div>
</div>

<script>
function receivePurchase(id) {
    if (!confirm('Are you sure you want to receive this purchase?\n\nThis action will update stock inventory.')) {
        return;
    }

    fetch('<?= base_url('purchases/receive/') ?>' + id, {
        method: 'POST',
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'Content-Type': 'application/x-www-form-urlencoded'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.status === 'success') {
            alert(data.message || 'Purchase received successfully!');
            location.reload();
        } else {
            alert(data.message || 'Failed to receive purchase');
        }
    })
    .catch(() => {
        alert('An error occurred while processing the request');
    });
}
</script>

<?= $this->endSection() ?>