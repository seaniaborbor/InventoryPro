<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0"><i class="bi bi-people me-2"></i>Customers</h5>
                <button type="button" class="btn btn-primary btn-sm" onclick="showCreateModal()">
                    <i class="bi bi-plus-circle"></i> Add Customer
                </button>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover" id="customersTable">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Customer Name</th>
                                <th>Business Name</th>
                                <th>Phone</th>
                                <th>Email</th>
                                <th>Total Spent</th>
                                <th>Sales</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($customers as $customer): ?>
                            <tr>
                                <td><?= $customer['id'] ?></td>
                                <td><strong><?= $customer['customer_name'] ?></strong></td>
                                <td><?= $customer['business_name'] ?: '-' ?></td>
                                <td><?= $customer['phone'] ?></td>
                                <td><?= $customer['email'] ?: '-' ?></td>
                                <td><?= formatCurrency($customer['total_spent'], 'LRD') ?></td>
                                <td><span class="badge bg-info"><?= $customer['sales_count'] ?></span></td>
                                <td>
                                    <a href="<?= base_url('customers/view/' . $customer['id']) ?>" class="btn btn-sm btn-info">
                                        <i class="bi bi-eye"></i>
                                    </a>
                                    <a href="<?= base_url('customers/edit/' . $customer['id']) ?>" class="btn btn-sm btn-primary">
                                        <i class="bi bi-pencil"></i>
                                    </a>
                                    <?php if ($customer['sales_count'] == 0): ?>
                                    <button class="btn btn-sm btn-danger" onclick="deleteCustomer(<?= $customer['id'] ?>, '<?= addslashes($customer['customer_name']) ?>')">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Customer Modal -->
<div class="modal fade" id="customerModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="customerModalTitle">Add Customer</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="customerForm">
                <?= csrf_field() ?>
                <input type="hidden" name="id" id="customerId">
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="customer_name" class="form-label">Customer Name *</label>
                        <input type="text" class="form-control" id="customer_name" name="customer_name" required>
                    </div>
                    <div class="mb-3">
                        <label for="business_name" class="form-label">Business Name</label>
                        <input type="text" class="form-control" id="business_name" name="business_name">
                    </div>
                    <div class="mb-3">
                        <label for="phone" class="form-label">Phone *</label>
                        <input type="text" class="form-control" id="phone" name="phone" required>
                    </div>
                    <div class="mb-3">
                        <label for="email" class="form-label">Email</label>
                        <input type="email" class="form-control" id="email" name="email">
                    </div>
                    <div class="mb-3">
                        <label for="address" class="form-label">Address</label>
                        <textarea class="form-control" id="address" name="address" rows="2"></textarea>
                    </div>
                    <div class="mb-3">
                        <label for="credit_limit" class="form-label">Credit Limit</label>
                        <input type="number" step="0.01" class="form-control" id="credit_limit" name="credit_limit" value="0">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Save Customer</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
$(document).ready(function() {
    $('#customersTable').DataTable({
        order: [[0, 'desc']],
        pageLength: 25
    });
    
    $('#customerForm').on('submit', function(e) {
        e.preventDefault();
        var formData = $(this).serialize();
        var customerId = $('#customerId').val();
        var url = customerId ? '/customers/update/' + customerId : '/customers/store';
        
        $.ajax({
            url: url,
            type: 'POST',
            data: formData,
            success: function(response) {
                if (response.status === 'success') {
                    $('#customerModal').modal('hide');
                    showToast('Success', response.message, 'success');
                    setTimeout(() => location.reload(), 1500);
                } else {
                    showToast('Error', response.message || 'Failed to save customer', 'error');
                }
            }
        });
    });
});

function showCreateModal() {
    $('#customerModalTitle').text('Add Customer');
    $('#customerForm')[0].reset();
    $('#customerId').val('');
    $('#customerModal').modal('show');
}

function deleteCustomer(id, name) {
    Swal.fire({
        title: 'Delete Customer?',
        text: `Are you sure you want to delete "${name}"?`,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#3085d6',
        confirmButtonText: 'Yes, delete it!'
    }).then((result) => {
        if (result.isConfirmed) {
            $.ajax({
                url: '/customers/delete/' + id,
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