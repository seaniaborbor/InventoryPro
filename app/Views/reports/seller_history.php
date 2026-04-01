<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0"><i class="bi bi-person-badge me-2"></i>Sales History - <?= $user['full_name'] ?></h5>
                <a href="<?= base_url('reports/sales') ?>" class="btn btn-secondary btn-sm">
                    <i class="bi bi-arrow-left"></i> Back to Reports
                </a>
            </div>
            <div class="card-body">
                <!-- Seller Info -->
                <div class="row mb-4">
                    <div class="col-md-6">
                        <div class="card bg-light">
                            <div class="card-body">
                                <h6 class="card-title">Seller Information</h6>
                                <p class="mb-1"><strong>Name:</strong> <?= $user['full_name'] ?></p>
                                <p class="mb-1"><strong>Username:</strong> <?= $user['username'] ?></p>
                                <p class="mb-1"><strong>Email:</strong> <?= $user['email'] ?></p>
                                <?php if ($user['phone']): ?>
                                    <p class="mb-1"><strong>Phone:</strong> <?= $user['phone'] ?></p>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="card bg-light">
                            <div class="card-body">
                                <h6 class="card-title">Sales Summary (<?= date('M Y', strtotime($startDate)) ?> - <?= date('M Y', strtotime($endDate)) ?>)</h6>
                                <?php
                                $totalLRD = 0;
                                $totalUSD = 0;
                                $totalTransactions = count($sales);
                                foreach ($sales as $sale) {
                                    if ($sale['currency'] == 'LRD') {
                                        $totalLRD += $sale['total_amount'];
                                    } else {
                                        $totalUSD += $sale['total_amount'];
                                    }
                                }
                                ?>
                                <p class="mb-1"><strong>Total LRD:</strong> <?= formatCurrency($totalLRD, 'LRD') ?></p>
                                <p class="mb-1"><strong>Total USD:</strong> <?= formatCurrency($totalUSD, 'USD') ?></p>
                                <p class="mb-1"><strong>Total Combined:</strong> <?= formatCurrency($totalLRD + $totalUSD, 'LRD') ?></p>
                                <p class="mb-1"><strong>Transactions:</strong> <?= $totalTransactions ?></p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Filter Form -->
                <form method="get" class="row g-3 mb-4">
                    <div class="col-md-3">
                        <label class="form-label">Start Date</label>
                        <input type="date" name="start_date" class="form-control" value="<?= $startDate ?>">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">End Date</label>
                        <input type="date" name="end_date" class="form-control" value="<?= $endDate ?>">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">&nbsp;</label>
                        <button type="submit" class="btn btn-primary">Apply Filters</button>
                    </div>
                </form>

                <!-- Sales History Table -->
                <div class="table-responsive">
                    <table class="table table-hover table-sm sortable-table" id="sellerSalesTable">
                        <thead>
                            <tr>
                                <th data-sort="string">Date</th>
                                <th data-sort="string">Invoice #</th>
                                <th data-sort="string">Customer</th>
                                <th data-sort="int" class="text-end">Items</th>
                                <th data-sort="float" class="text-end">Total Amount</th>
                                <th data-sort="string">Payment Method</th>
                                <th data-sort="float" class="text-end">Amount Paid</th>
                                <th data-sort="float" class="text-end">Balance</th>
                                <th data-sort="string">Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($sales as $sale): ?>
                            <tr>
                                <td>
                                    <small class="text-muted">
                                        <?= date('M d, Y', strtotime($sale['sale_date'])) ?><br>
                                        <?= date('H:i', strtotime($sale['created_at'])) ?>
                                    </small>
                                </td>
                                <td>
                                    <a href="<?= base_url('sales/view/' . $sale['id']) ?>" class="text-decoration-none">
                                        #<?= str_pad($sale['id'], 6, '0', STR_PAD_LEFT) ?>
                                    </a>
                                </td>
                                <td>
                                    <strong><?= $sale['customer_name'] ?: 'Walk-in Customer' ?></strong>
                                </td>
                                <td class="text-end">
                                    <?php
                                    $db = \Config\Database::connect();
                                    $itemsCount = $db->table('sale_items')->where('sale_id', $sale['id'])->countAllResults();
                                    echo $itemsCount;
                                    ?>
                                </td>
                                <td class="text-end fw-bold">
                                    <?= formatCurrency($sale['total_amount'], $sale['currency']) ?>
                                </td>
                                <td>
                                    <span class="badge bg-secondary"><?= $sale['payment_method'] ?></span>
                                </td>
                                <td class="text-end">
                                    <?= formatCurrency($sale['amount_paid'] ?? 0, $sale['currency']) ?>
                                </td>
                                <td class="text-end">
                                    <?php
                                    $balance = ($sale['total_amount'] ?? 0) - ($sale['amount_paid'] ?? 0);
                                    $balanceClass = $balance > 0 ? 'text-danger' : 'text-success';
                                    ?>
                                    <span class="<?= $balanceClass ?> fw-bold">
                                        <?= formatCurrency($balance, $sale['currency']) ?>
                                    </span>
                                </td>
                                <td>
                                    <?php
                                    $status = $sale['payment_status'] ?? 'Unpaid';
                                    $statusClass = $status == 'Paid' ? 'bg-success' : ($status == 'Partial' ? 'bg-warning' : 'bg-danger');
                                    ?>
                                    <span class="badge <?= $statusClass ?>"><?= $status ?></span>
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

                <?php if (empty($sales)): ?>
                    <div class="text-center text-muted py-4">
                        <i class="bi bi-receipt fs-1"></i>
                        <p>No sales found for the selected period.</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<script>
$(document).ready(function() {
    // Initialize sortable table
    const table = $('#sellerSalesTable');
    const tbody = table.find('tbody');
    const headers = table.find('th');
    
    headers.each(function(index) {
        const header = $(this);
        const sortType = header.data('sort');
        
        if (sortType) {
            header.addClass('sortable').css('cursor', 'pointer');
            header.append(' <i class="bi bi-chevron-expand sort-icon"></i>');
            
            header.on('click', function() {
                const sortIcon = header.find('.sort-icon');
                const isAscending = sortIcon.hasClass('bi-chevron-up');
                
                // Reset all sort icons
                table.find('.sort-icon').removeClass('bi-chevron-up bi-chevron-down').addClass('bi-chevron-expand');
                
                // Set current sort icon
                if (isAscending) {
                    sortIcon.removeClass('bi-chevron-up').addClass('bi-chevron-down');
                } else {
                    sortIcon.removeClass('bi-chevron-expand').addClass('bi-chevron-up');
                }
                
                sortTable(tbody, index, sortType, !isAscending);
            });
        }
    });
});

function sortTable(tbody, columnIndex, sortType, ascending) {
    const rows = tbody.find('tr').toArray();
    
    rows.sort(function(a, b) {
        const aVal = $(a).find('td').eq(columnIndex).text().trim();
        const bVal = $(b).find('td').eq(columnIndex).text().trim();
        
        let comparison = 0;
        
        switch (sortType) {
            case 'int':
                comparison = parseInt(aVal.replace(/[^\d]/g, '')) - parseInt(bVal.replace(/[^\d]/g, ''));
                break;
            case 'float':
                comparison = parseFloat(aVal.replace(/[^\d.-]/g, '')) - parseFloat(bVal.replace(/[^\d.-]/g, ''));
                break;
            case 'string':
            default:
                comparison = aVal.localeCompare(bVal);
                break;
        }
        
        return ascending ? comparison : -comparison;
    });
    
    tbody.append(rows);
}
</script>
<?= $this->endSection() ?>