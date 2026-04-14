<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0"><i class="bi bi-speedometer2 me-2"></i>Reports Summary Dashboard</h5>
            </div>
            <div class="card-body">
                <!-- Filter Form -->
                <form method="get" class="row mb-4">
                    <div class="col-md-3">
                        <label class="form-label">Start Date</label>
                        <input type="date" name="start_date" class="form-control" value="<?= $startDate ?>">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">End Date</label>
                        <input type="date" name="end_date" class="form-control" value="<?= $endDate ?>">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Currency</label>
                        <select name="currency" class="form-select">
                            <option value="LRD" <?= $currency == 'LRD' ? 'selected' : '' ?>>LRD - Liberian Dollar</option>
                            <option value="USD" <?= $currency == 'USD' ? 'selected' : '' ?>>USD - US Dollar</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">&nbsp;</label>
                        <button type="submit" class="btn btn-primary w-100">Refresh Summary</button>
                    </div>
                </form>

                <!-- Summary Cards Row -->
                <div class="row mb-4">
                    <div class="col-md-3">
                        <div class="card bg-primary text-white">
                            <div class="card-body">
                                <h6 class="card-title"><i class="bi bi-cart"></i> Gross Sales</h6>
                                <h2 class="mb-0"><?= $currency == 'LRD' ? 'L$' : '$' ?> <?= number_format($grossSales, 2) ?></h2>
                                <small>Total sales before returns/refunds</small>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card bg-danger text-white">
                            <div class="card-body">
                                <h6 class="card-title"><i class="bi bi-arrow-return-left"></i> Refunds</h6>
                                <h2 class="mb-0"><?= $currency == 'LRD' ? 'L$' : '$' ?> <?= number_format($refunds, 2) ?></h2>
                                <small>Money returned to customers</small>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card bg-info text-white">
                            <div class="card-body">
                                <h6 class="card-title"><i class="bi bi-arrow-repeat"></i> Returns</h6>
                                <h2 class="mb-0"><?= $currency == 'LRD' ? 'L$' : '$' ?> <?= number_format($returns, 2) ?></h2>
                                <small>Products returned (not refunded)</small>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card bg-success text-white">
                            <div class="card-body">
                                <h6 class="card-title"><i class="bi bi-cash-stack"></i> Net Sales</h6>
                                <h2 class="mb-0"><?= $currency == 'LRD' ? 'L$' : '$' ?> <?= number_format($netSales, 2) ?></h2>
                                <small>Gross Sales - Refunds</small>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Second Row -->
                <div class="row mb-4">
                    <div class="col-md-4">
                        <div class="card bg-warning text-dark">
                            <div class="card-body">
                                <h6 class="card-title"><i class="bi bi-gear"></i> Production Cost</h6>
                                <h2 class="mb-0"><?= $currency == 'LRD' ? 'L$' : '$' ?> <?= number_format($productionCost, 2) ?></h2>
                                <small>Total material cost for production</small>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card bg-danger text-white">
                            <div class="card-body">
                                <h6 class="card-title"><i class="bi bi-exclamation-triangle"></i> Damage & Theft</h6>
                                <h2 class="mb-0"><?= $currency == 'LRD' ? 'L$' : '$' ?> <?= number_format($totalLosses, 2) ?></h2>
                                <small>Damage: <?= number_format($damage, 2) ?> | Theft: <?= number_format($theft, 2) ?></small>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card bg-secondary text-white">
                            <div class="card-body">
                                <h6 class="card-title"><i class="bi bi-wallet2"></i> Operating Expenses</h6>
                                <h2 class="mb-0"><?= $currency == 'LRD' ? 'L$' : '$' ?> <?= number_format($expenses, 2) ?></h2>
                                <small>Salaries, utilities, rent, etc.</small>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Profit Row -->
                <div class="row mb-4">
                    <div class="col-md-12">
                        <div class="card <?= $netProfit >= 0 ? 'bg-success' : 'bg-danger' ?> text-white">
                            <div class="card-body text-center">
                                <h5 class="card-title"><i class="bi bi-graph-up"></i> NET PROFIT / LOSS</h5>
                                <h1 class="mb-0 display-4">
                                    <?= $currency == 'LRD' ? 'L$' : '$' ?> <?= number_format($netProfit, 2) ?>
                                </h1>
                                <small>Net Sales - Production Cost - Damage/Theft - Operating Expenses</small>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Detailed Breakdown Table -->
                <div class="card">
                    <div class="card-header">
                        <h6 class="mb-0"><i class="bi bi-table"></i> Detailed Breakdown</h6>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-bordered">
                                <thead class="table-light">
                                    <tr>
                                        <th width="60%">Description</th>
                                        <th width="20%" class="text-end">Amount (<?= $currency ?>)</th>
                                        <th width="20%" class="text-end">% of Net Sales</th>
                                    </thead>
                                <tbody>
                                    <tr>
                                        <td><strong>Gross Sales</strong></td>
                                        <td class="text-end"><?= number_format($grossSales, 2) ?></td>
                                        <td class="text-end">100%</td>
                                    </tr>
                                    <?php if ($refunds > 0): ?>
                                    <tr>
                                        <td class="text-danger">Less: Refunds</td>
                                        <td class="text-end text-danger">- <?= number_format($refunds, 2) ?></td>
                                        <td class="text-end text-danger">- <?= $grossSales > 0 ? number_format(($refunds / $grossSales) * 100, 2) : 0 ?>%</td>
                                    </tr>
                                    <?php endif; ?>
                                    <tr>
                                        <td><strong>Net Sales</strong></td>
                                        <td class="text-end"><strong><?= number_format($netSales, 2) ?></strong></td>
                                        <td class="text-end">100%</td>
                                    </tr>
                                    <tr class="table-secondary">
                                        <td colspan="3"></td>
                                    </tr>
                                    <tr>
                                        <td>Less: Production Material Cost</td>
                                        <td class="text-end">- <?= number_format($productionCost, 2) ?></td>
                                        <td class="text-end">- <?= $netSales > 0 ? number_format(($productionCost / $netSales) * 100, 2) : 0 ?>%</td>
                                    </tr>
                                    <?php if ($damage > 0): ?>
                                    <tr>
                                        <td class="text-danger">Less: Damage/Waste</td>
                                        <td class="text-end text-danger">- <?= number_format($damage, 2) ?></td>
                                        <td class="text-end text-danger">- <?= $netSales > 0 ? number_format(($damage / $netSales) * 100, 2) : 0 ?>%</td>
                                    </tr>
                                    <?php endif; ?>
                                    <?php if ($theft > 0): ?>
                                    <tr>
                                        <td class="text-danger">Less: Theft/Loss</td>
                                        <td class="text-end text-danger">- <?= number_format($theft, 2) ?></td>
                                        <td class="text-end text-danger">- <?= $netSales > 0 ? number_format(($theft / $netSales) * 100, 2) : 0 ?>%</td>
                                    </tr>
                                    <?php endif; ?>
                                    <tr>
                                        <td><strong>Gross Profit</strong></td>
                                        <td class="text-end"><strong><?= number_format($netSales - $productionCost - $totalLosses, 2) ?></strong></td>
                                        <td class="text-end"><strong><?= $netSales > 0 ? number_format((($netSales - $productionCost - $totalLosses) / $netSales) * 100, 2) : 0 ?>%</strong></td>
                                    </tr>
                                    <tr class="table-secondary">
                                        <td colspan="3"></td>
                                    </tr>
                                    <tr>
                                        <td>Less: Operating Expenses</td>
                                        <td class="text-end">- <?= number_format($expenses, 2) ?></td>
                                        <td class="text-end">- <?= $netSales > 0 ? number_format(($expenses / $netSales) * 100, 2) : 0 ?>%</td>
                                    </tr>
                                    <tr>
                                        <td class="fw-bold <?= $netProfit >= 0 ? 'text-success' : 'text-danger' ?>">
                                            <strong><?= $netProfit >= 0 ? 'NET PROFIT' : 'NET LOSS' ?></strong>
                                        </td>
                                        <td class="text-end fw-bold <?= $netProfit >= 0 ? 'text-success' : 'text-danger' ?>">
                                            <?= number_format(abs($netProfit), 2) ?>
                                        </td>
                                        <td class="text-end fw-bold <?= $netProfit >= 0 ? 'text-success' : 'text-danger' ?>">
                                            <?= $netSales > 0 ? number_format(($netProfit / $netSales) * 100, 2) : 0 ?>%
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- Navigation to Detailed Reports -->
                <div class="row mt-4">
                    <div class="col-md-12">
                        <div class="card">
                            <div class="card-header">
                                <h6 class="mb-0"><i class="bi bi-link"></i> Detailed Reports</h6>
                            </div>
                            <div class="card-body">
                                <div class="row text-center">
                                    <div class="col-md-3">
                                        <a href="<?= base_url('reports/sales?start_date=' . $startDate . '&end_date=' . $endDate . '&currency=' . $currency) ?>" class="btn btn-outline-primary w-100">
                                            <i class="bi bi-cart"></i> Sales Report
                                        </a>
                                    </div>
                                    <div class="col-md-3">
                                        <a href="<?= base_url('reports/production?start_date=' . $startDate . '&end_date=' . $endDate . '&currency=' . $currency) ?>" class="btn btn-outline-warning w-100">
                                            <i class="bi bi-gear"></i> Production Report
                                        </a>
                                    </div>
                                    <div class="col-md-3">
                                        <a href="<?= base_url('reports/adjustments?start_date=' . $startDate . '&end_date=' . $endDate . '&currency=' . $currency) ?>" class="btn btn-outline-danger w-100">
                                            <i class="bi bi-arrow-down-up"></i> Adjustments Report
                                        </a>
                                    </div>
                                    <div class="col-md-3">
                                        <a href="<?= base_url('reports/financial?start_date=' . $startDate . '&end_date=' . $endDate . '&currency=' . $currency) ?>" class="btn btn-outline-success w-100">
                                            <i class="bi bi-currency-dollar"></i> Financial Report
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<?= $this->endSection() ?>