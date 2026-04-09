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
                                    <!-- View -->
                                    <a href="<?= base_url('customers/view/' . $customer['id']) ?>" class="btn btn-sm btn-info">
                                        <i class="bi bi-eye"></i>
                                    </a>

                                    <!-- Edit (AJAX MODAL) -->
                                    <button class="btn btn-sm btn-primary"
                                        onclick='editCustomer(<?= htmlspecialchars(json_encode($customer), ENT_QUOTES, "UTF-8") ?>)'>
                                        <i class="bi bi-pencil"></i>
                                    </button>

                                    <!-- Delete -->
                                    <?php if ($customer['sales_count'] == 0): ?>
                                    <button class="btn btn-sm btn-danger"
                                        onclick="deleteCustomer(<?= $customer['id'] ?>, '<?= addslashes($customer['customer_name']) ?>')">
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
                <h5 class="modal-title">Customer</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>

            <form id="customerForm">
                <?= csrf_field() ?>
                <input type="hidden" name="id" id="customerId">

                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Customer Name *</label>
                        <input type="text" class="form-control" id="customer_name" name="customer_name" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Business Name</label>
                        <input type="text" class="form-control" id="business_name" name="business_name">
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Phone *</label>
                        <input type="text" class="form-control" id="phone" name="phone" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Email</label>
                        <input type="email" class="form-control" id="email" name="email">
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Address</label>
                        <textarea class="form-control" id="address" name="address"></textarea>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Credit Limit</label>
                        <input type="number" step="0.01" class="form-control" id="credit_limit" name="credit_limit">
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary" id="saveBtn">Save Customer</button>
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

        let formData = $(this).serialize();
        let customerId = $('#customerId').val();
        let url = customerId ? '/customers/update/' + customerId : '/customers/store';

        $.post(url, formData, function(response) {
            if (response.status === 'success') {
                $('#customerModal').modal('hide');
                showToast('Success', response.message, 'success');
                setTimeout(() => location.reload(), 1200);
            } else {
                showToast('Error', response.message || 'Failed', 'error');
            }
        });
    });
});

/* ADD */
function showCreateModal() {
    $('#customerForm')[0].reset();
    $('#customerId').val('');
    $('#saveBtn').text('Save Customer');
    $('.modal-title').text('Add Customer');
    $('#customerModal').modal('show');
}

/* EDIT */
function editCustomer(customer) {
    $('#customerId').val(customer.id);
    $('#customer_name').val(customer.customer_name);
    $('#business_name').val(customer.business_name);
    $('#phone').val(customer.phone);
    $('#email').val(customer.email);
    $('#address').val(customer.address);
    $('#credit_limit').val(customer.credit_limit);

    $('#saveBtn').text('Update Customer');
    $('.modal-title').text('Edit Customer');

    $('#customerModal').modal('show');
}

/* DELETE */
function deleteCustomer(id, name) {
    Swal.fire({
        title: 'Delete Customer?',
        text: `Delete "${name}"?`,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        confirmButtonText: 'Yes delete'
    }).then((result) => {
        if (result.isConfirmed) {
            $.post('/customers/delete/' + id, {<?= csrf_token() ?>: '<?= csrf_hash() ?>'}, function(res) {
                if (res.status === 'success') {
                    showToast('Success', res.message, 'success');
                    setTimeout(() => location.reload(), 1200);
                }
            });
        }
    });
}
</script>

<?= $this->endSection() ?>