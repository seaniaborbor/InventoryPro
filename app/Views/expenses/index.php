<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0"><i class="bi bi-wallet2 me-2"></i>Expenses</h5>
                <a href="<?= base_url('expenses/create') ?>" class="btn btn-primary btn-sm">
                    <i class="bi bi-plus-circle"></i> Add Expense
                </a>
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
                        <label class="form-label">Category</label>
                        <select name="category" class="form-select">
                            <option value="">All Categories</option>
                            <?php foreach ($categories as $cat): ?>
                            <option value="<?= $cat['id'] ?>" <?= $selectedCategory == $cat['id'] ? 'selected' : '' ?>>
                                <?= esc($cat['category_name']) ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">Currency</label>
                        <select name="currency" class="form-select">
                            <option value="">All</option>
                            <option value="LRD" <?= $selectedCurrency == 'LRD' ? 'selected' : '' ?>>LRD</option>
                            <option value="USD" <?= $selectedCurrency == 'USD' ? 'selected' : '' ?>>USD</option>
                        </select>
                    </div>
                    <div class="col-md-1">
                        <label class="form-label">&nbsp;</label>
                        <button type="submit" class="btn btn-primary w-100">Filter</button>
                    </div>
                </form>
                
                <!-- Summary Cards - FIXED -->
                <div class="row mb-4">
                    <div class="col-md-4">
                        <div class="card bg-info text-white">
                            <div class="card-body">
                                <h6 class="card-title">Total Expenses (All)</h6>
                                <h3>
                                    <?php 
                                    // Calculate total expenses across all currencies
                                    $totalAll = 0;
                                    foreach ($summary as $s) {
                                        $totalAll += $s['total_amount'];
                                    }
                                    echo formatCurrency($totalAll, 'LRD');
                                    ?>
                                </h3>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card bg-warning text-dark">
                            <div class="card-body">
                                <h6 class="card-title">LRD Expenses</h6>
                                <h3>
                                    <?php 
                                    // Calculate only LRD expenses
                                    $totalLRD = 0;
                                    foreach ($summary as $s) {
                                        if ($s['currency'] == 'LRD') {
                                            $totalLRD += $s['total_amount'];
                                        }
                                    }
                                    echo formatCurrency($totalLRD, 'LRD');
                                    ?>
                                </h3>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card bg-success text-white">
                            <div class="card-body">
                                <h6 class="card-title">USD Expenses</h6>
                                <h3>
                                    <?php 
                                    // Calculate only USD expenses
                                    $totalUSD = 0;
                                    foreach ($summary as $s) {
                                        if ($s['currency'] == 'USD') {
                                            $totalUSD += $s['total_amount'];
                                        }
                                    }
                                    echo formatCurrency($totalUSD, 'USD');
                                    ?>
                                </h3>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Expenses Table -->
                <div class="table-responsive">
                    <table class="table table-hover" id="expensesTable">
                        <thead>
                            <tr>
                                <th>Date</th>
                                <th>Category</th>
                                <th>Description</th>
                                <th>Amount</th>
                                <th>Receipt</th>
                                <th>Created By</th>
                                <th>Last Edited</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($expenses)): ?>
                                <tr>
                                    <td colspan="8" class="text-center text-muted py-4">
                                        <i class="bi bi-inbox" style="font-size: 2rem;"></i>
                                        <p class="mt-2">No expenses found for the selected criteria.</p>
                                    </td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($expenses as $expense): ?>
                                <tr>
                                    <td><?= date('Y-m-d', strtotime($expense['expense_date'])) ?></td>
                                    <td><span class="badge bg-secondary"><?= esc($expense['category_name']) ?></span></td>
                                    <td><?= esc($expense['description'] ?: '-') ?></td>
                                    <td class="fw-bold"><?= formatCurrency($expense['amount'], $expense['currency']) ?></td>
                                    <td>
                                        <?php if ($expense['receipt_image']): ?>
                                            <a href="<?= base_url($expense['receipt_image']) ?>" target="_blank" class="btn btn-sm btn-info">
                                                <i class="bi bi-image"></i> View
                                            </a>
                                        <?php else: ?>
                                            -
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <small class="text-muted">
                                            <?= esc($expense['created_by_name'] ?: 'Unknown') ?><br>
                                            <span class="text-xs"><?= date('M d, Y H:i', strtotime($expense['created_at'])) ?></span>
                                        </small>
                                    </td>
                                    <td>
                                        <?php if ($expense['updated_by_name']): ?>
                                            <small class="text-muted">
                                                <?= esc($expense['updated_by_name']) ?><br>
                                                <span class="text-xs"><?= date('M d, Y H:i', strtotime($expense['updated_at'])) ?></span>
                                            </small>
                                        <?php else: ?>
                                            <small class="text-muted">-</small>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <a href="<?= base_url('expenses/edit/' . $expense['id']) ?>" class="btn btn-sm btn-primary">
                                            <i class="bi bi-pencil"></i>
                                        </a>
                                        <button class="btn btn-sm btn-danger" onclick="deleteExpense(<?= $expense['id'] ?>)">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
                
                <?php if (isset($pager)): ?>
                    <div class="mt-3">
                        <?= $pager->links() ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
$(document).ready(function() {
    // Only initialize DataTable if there are rows
    if ($('#expensesTable tbody tr').length > 1 && $('#expensesTable tbody tr td').length > 1) {
        $('#expensesTable').DataTable({
            pageLength: 20,
            order: [[0, 'desc']],
            searching: true,
            paging: false,
            info: false
        });
    }
});

function deleteExpense(id) {
    Swal.fire({
        title: 'Delete Expense?',
        text: 'This action cannot be undone.',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#3085d6',
        confirmButtonText: 'Yes, delete it!'
    }).then((result) => {
        if (result.isConfirmed) {
            $.ajax({
                url: '<?= base_url('expenses/delete/') ?>' + id,
                type: 'POST',
                data: {'<?= csrf_token() ?>': '<?= csrf_hash() ?>'},
                success: function(response) {
                    if (response.status === 'success') {
                        Swal.fire('Deleted!', response.message, 'success');
                        setTimeout(() => location.reload(), 1500);
                    } else {
                        Swal.fire('Error!', response.message, 'error');
                    }
                },
                error: function() {
                    Swal.fire('Error!', 'Failed to delete expense', 'error');
                }
            });
        }
    });
}

function showToast(title, message, type) {
    // Simple fallback if showToast is not defined
    Swal.fire(title, message, type);
}
</script>
<?= $this->endSection() ?>