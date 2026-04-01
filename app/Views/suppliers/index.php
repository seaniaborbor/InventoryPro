<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0"><i class="bi bi-truck me-2"></i>Suppliers</h5>
                <button type="button" class="btn btn-primary btn-sm" onclick="showCreateModal()">
                    <i class="bi bi-plus-circle"></i> Add Supplier
                </button>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover" id="suppliersTable">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Supplier Name</th>
                                <th>Contact Person</th>
                                <th>Phone</th>
                                <th>Email</th>
                                <th>Total Spent</th>
                                <th>Purchases</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($suppliers as $supplier): ?>
                            <tr>
                                <td><?= $supplier['id'] ?></td>
                                <td><strong><?= $supplier['supplier_name'] ?></strong></td>
                                <td><?= $supplier['contact_person'] ?: '-' ?></td>
                                <td><?= $supplier['phone'] ?></td>
                                <td><?= $supplier['email'] ?: '-' ?></td>
                                <td><?= formatCurrency($supplier['total_spent'], 'LRD') ?></td>
                                <td><span class="badge bg-info"><?= $supplier['purchase_count'] ?></span>Non
                                <td>
                                    <a href="<?= base_url('suppliers/view/' . $supplier['id']) ?>" class="btn btn-sm btn-info">
                                        <i class="bi bi-eye"></i>
                                    </a>
                                    <a href="<?= base_url('suppliers/edit/' . $supplier['id']) ?>" class="btn btn-sm btn-primary">
                                        <i class="bi bi-pencil"></i>
                                    </a>
                                    <?php if ($supplier['purchase_count'] == 0): ?>
                                    <button class="btn btn-sm btn-danger" onclick="deleteSupplier(<?= $supplier['id'] ?>, '<?= addslashes($supplier['supplier_name']) ?>')">
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

<!-- Supplier Modal -->
<div class="modal fade" id="supplierModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="supplierModalTitle">Add Supplier</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="supplierForm">
                <?= csrf_field() ?>
                <input type="hidden" name="id" id="supplierId">
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="supplier_name" class="form-label">Supplier Name *</label>
                        <input type="text" class="form-control" id="supplier_name" name="supplier_name" required>
                    </div>
                    <div class="mb-3">
                        <label for="contact_person" class="form-label">Contact Person</label>
                        <input type="text" class="form-control" id="contact_person" name="contact_person">
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
                        <label for="tax_number" class="form-label">Tax Number</label>
                        <input type="text" class="form-control" id="tax_number" name="tax_number">
                    </div>
                    <div class="mb-3">
                        <label for="payment_terms" class="form-label">Payment Terms</label>
                        <input type="text" class="form-control" id="payment_terms" name="payment_terms" placeholder="e.g., Net 30">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Save Supplier</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
$(document).ready(function() {
    $('#suppliersTable').DataTable({
        order: [[0, 'desc']],
        pageLength: 25
    });
    
    $('#supplierForm').on('submit', function(e) {
        e.preventDefault();
        var formData = $(this).serialize();
        var supplierId = $('#supplierId').val();
        var url = supplierId ? '/suppliers/update/' + supplierId : '/suppliers/store';
        
        $.ajax({
            url: url,
            type: 'POST',
            data: formData,
            success: function(response) {
                if (response.status === 'success') {
                    $('#supplierModal').modal('hide');
                    showToast('Success', response.message, 'success');
                    setTimeout(() => location.reload(), 1500);
                } else {
                    showToast('Error', response.message || 'Failed to save supplier', 'error');
                }
            }
        });
    });
});

function showCreateModal() {
    $('#supplierModalTitle').text('Add Supplier');
    $('#supplierForm')[0].reset();
    $('#supplierId').val('');
    $('#supplierModal').modal('show');
}

function deleteSupplier(id, name) {
    Swal.fire({
        title: 'Delete Supplier?',
        text: `Are you sure you want to delete "${name}"?`,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#3085d6',
        confirmButtonText: 'Yes, delete it!'
    }).then((result) => {
        if (result.isConfirmed) {
            $.ajax({
                url: '/suppliers/delete/' + id,
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