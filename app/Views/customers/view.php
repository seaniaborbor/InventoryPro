<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0"><i class="bi bi-person me-2"></i><?= $customer['customer_name'] ?></h5>
                <div>
                    <a href="<?= base_url('customers/edit/' . $customer['id']) ?>" class="btn btn-warning btn-sm">
                        <i class="bi bi-pencil"></i> Edit
                    </a>
                    <a href="<?= base_url('customers') ?>" class="btn btn-secondary btn-sm">
                        <i class="bi bi-arrow-left"></i> Back
                    </a>
                </div>
            </div>
            <div class="card-body">
                <!-- Customer Information -->
                <div class="row mb-4">
                    <div class="col-md-6">
                        <div class="card">
                            <div class="card-header bg-light">
                                <h6 class="mb-0">Contact Information</h6>
                            </div>
                            <div class="card-body">
                                <table class="table table-sm table-borderless">
                                    <tr>
                                        <th width="35%">Customer Name:</th>
                                        <td><strong><?= esc($customer['customer_name']) ?></strong></td>
                                    </tr>
                                    <?php if ($customer['business_name']): ?>
                                    <tr>
                                        <th>Business Name:</th>
                                        <td><?= esc($customer['business_name']) ?></td>
                                    </tr>
                                    <?php endif; ?>
                                    <tr>
                                        <th>Phone:</th>
                                        <td><?= esc($customer['phone']) ?></td>
                                    </tr>
                                    <?php if ($customer['email']): ?>
                                    <tr>
                                        <th>Email:</th>
                                        <td><?= esc($customer['email']) ?></td>
                                    </tr>
                                    <?php endif; ?>
                                    <?php if ($customer['address']): ?>
                                    <tr>
                                        <th>Address:</th>
                                        <td><?= nl2br(esc($customer['address'])) ?></td>
                                    </tr>
                                    <?php endif; ?>
                                </table>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="card">
                            <div class="card-header bg-light">
                                <h6 class="mb-0">Account Summary</h6>
                            </div>
                            <div class="card-body">
                                <table class="table table-sm table-borderless">
                                    <tr>
                                        <th width="35%">Credit Limit:</th>
                                        <td><?= formatCurrency($customer['credit_limit'] ?? 0) ?></td>
                                    </tr>
                                    <tr>
                                        <th>Total Sales:</th>
                                        <td><?= isset($customer['total_sales']) ? formatCurrency($customer['total_sales']) : formatCurrency(0) ?></td>
                                    </tr>
                                    <tr>
                                        <th>Total Paid:</th>
                                        <td><?= isset($customer['total_paid']) ? formatCurrency($customer['total_paid']) : formatCurrency(0) ?></td>
                                    </tr>
                                    <tr>
                                        <th>Outstanding:</th>
                                        <td class="<?= (isset($customer['total_sales']) && isset($customer['total_paid']) && ($customer['total_sales'] - $customer['total_paid']) > 0) ? 'text-danger fw-bold' : '' ?>">
                                            <?= isset($customer['total_sales']) && isset($customer['total_paid']) ? formatCurrency($customer['total_sales'] - $customer['total_paid']) : formatCurrency(0) ?>
                                        </td>
                                    </tr>
                                    <tr>
                                        <th>Order Count:</th>
                                        <td><?= $customer['order_count'] ?? 0 ?></td>
                                    </tr>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Recent Sales -->
                <?php if (!empty($customer['sales'])): ?>
                <div class="card">
                    <div class="card-header bg-light">
                        <h6 class="mb-0">Recent Sales</h6>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-bordered mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>Invoice #</th>
                                        <th>Date</th>
                                        <th class="text-end">Total</th>
                                        <th class="text-end">Paid</th>
                                        <th class="text-end">Balance</th>
                                        <th>Status</th>
                                        <th></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($customer['sales'] as $sale): ?>
                                    <tr>
                                        <td><?= $sale['invoice_number'] ?></td>
                                        <td><?= date('Y-m-d', strtotime($sale['sale_date'])) ?></td>
                                        <td class="text-end"><?= formatCurrency($sale['total_amount']) ?></td>
                                        <td class="text-end"><?= formatCurrency($sale['amount_paid']) ?></td>
                                        <td class="text-end <?= $sale['balance_due'] > 0 ? 'text-danger fw-bold' : '' ?>">
                                            <?= formatCurrency($sale['balance_due']) ?>
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
                                            <a href="<?= base_url('sales/view/' . $sale['id']) ?>" class="btn btn-sm btn-outline-primary">
                                                <i class="bi bi-eye"></i> View
                                            </a>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
                <?php else: ?>
                <div class="alert alert-info">
                    <i class="bi bi-info-circle me-2"></i> No sales history found for this customer.
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>
<?= $this->endSection() ?>