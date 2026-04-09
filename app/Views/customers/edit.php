<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>

<div class="row">
    <div class="col-md-10 mx-auto">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Customer Details</h5>
            </div>

            <div class="card-body">
                <!-- Customer Info -->
                <div class="mb-4">
                    <h6><?= esc($customer['customer_name']) ?></h6>
                    <p class="mb-1"><strong>Business:</strong> <?= esc($customer['business_name']) ?></p>
                    <p class="mb-1"><strong>Phone:</strong> <?= esc($customer['phone']) ?></p>
                    <p class="mb-1"><strong>Email:</strong> <?= esc($customer['email']) ?></p>
                    <p class="mb-1"><strong>Total Spent:</strong> $<?= number_format($customer['total_spent'], 2) ?></p>
                </div>

                <!-- Sales Table -->
                <h6 class="mb-3">Sales History</h6>

                <div class="table-responsive">
                    <table class="table table-bordered">
                        <thead>
                            <tr>
                                <th>Invoice</th>
                                <th>Date</th>
                                <th>Total</th>
                                <th>Paid</th>
                                <th>Balance</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!empty($customer['sales'])): ?>
                                <?php foreach ($customer['sales'] as $sale): ?>
                                    <tr>
                                        <td><?= esc($sale['invoice_number']) ?></td>
                                        <td><?= date('M d, Y', strtotime($sale['sale_date'])) ?></td>
                                        <td>$<?= number_format($sale['total_amount'], 2) ?></td>
                                        <td>$<?= number_format($sale['amount_paid'], 2) ?></td>
                                        <td>$<?= number_format($sale['balance_due'], 2) ?></td>
                                        <td>
                                            <span class="badge bg-<?=
                                                strtolower($sale['payment_status']) === 'paid' ? 'success' :
                                                (strtolower($sale['payment_status']) === 'partially paid' ? 'warning' :
                                                (strtolower($sale['payment_status']) === 'unpaid' ? 'danger' : 'secondary'))
                                            ?>">
                                                <?= esc($sale['payment_status']) ?>
                                            </span>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="6" class="text-center">No sales found</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>

            </div>
        </div>
    </div>
</div>

<?= $this->endSection() ?>