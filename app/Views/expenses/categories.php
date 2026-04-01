<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0"><i class="bi bi-tags me-2"></i>Expense Categories</h5>
                <button type="button" class="btn btn-primary btn-sm" onclick="showCreateModal()">
                    <i class="bi bi-plus-circle"></i> Add Category
                </button>
            </div>
            <div class="card-body">
                <?php if (empty($categories)): ?>
                    <div class="alert alert-info text-center">
                        <i class="bi bi-info-circle"></i> No expense categories found.
                        <button class="btn btn-link" onclick="showCreateModal()">Create your first category</button>
                    </div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-hover" id="categoriesTable">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Category Name</th>
                                    <th>Description</th>
                                    <th>Expenses Count</th>
                                    <th>Actions</th>
                                </thead>
                            <tbody>
                                <?php foreach ($categories as $category): ?>
                                <tr>
                                    <td><?= $category['id'] ?></td>
                                    <td><strong><?= $category['category_name'] ?></strong></td>
                                    <td><?= $category['description'] ?: '-' ?></td>
                                    <td>
                                        <span class="badge bg-info"><?= $category['expense_count'] ?? 0 ?> expenses</span>
                                    </td>
                                    <td>
                                        <button class="btn btn-sm btn-primary" onclick="editCategory(<?= $category['id'] ?>, '<?= addslashes($category['category_name']) ?>', '<?= addslashes($category['description']) ?>')">
                                            <i class="bi bi-pencil"></i>
                                        </button>
                                        <?php if (($category['expense_count'] ?? 0) == 0): ?>
                                        <button class="btn btn-sm btn-danger" onclick="deleteCategory(<?= $category['id'] ?>, '<?= addslashes($category['category_name']) ?>')">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- Category Modal -->
<div class="modal fade" id="categoryModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="categoryModalTitle">Add Category</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="categoryForm">
                <?= csrf_field() ?>
                <input type="hidden" name="id" id="categoryId">
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="category_name" class="form-label">Category Name *</label>
                        <input type="text" class="form-control" id="category_name" name="category_name" required>
                        <small class="text-muted">e.g., Electricity, Internet, Rent, Salaries</small>
                    </div>
                    <div class="mb-3">
                        <label for="description" class="form-label">Description</label>
                        <textarea class="form-control" id="description" name="description" rows="3" 
                                  placeholder="Optional description of this expense category"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Save Category</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
$(document).ready(function() {
    $('#categoriesTable').DataTable({
        pageLength: 25,
        order: [[0, 'desc']]
    });
    
    $('#categoryForm').on('submit', function(e) {
        e.preventDefault();
        
        var categoryId = $('#categoryId').val();
        var formData = $(this).serialize();
        var url = categoryId ? '<?= base_url('expenses/categories/update') ?>/' + categoryId : '<?= base_url('expenses/categories/store') ?>';
        
        $.ajax({
            url: url,
            type: 'POST',
            data: formData,
            success: function(response) {
                if (response.status === 'success') {
                    $('#categoryModal').modal('hide');
                    showToast('Success', response.message, 'success');
                    setTimeout(() => location.reload(), 1500);
                } else {
                    showToast('Error', response.message || 'Failed to save category', 'error');
                }
            },
            error: function(xhr) {
                var message = 'Failed to save category';
                if (xhr.responseJSON && xhr.responseJSON.message) {
                    message = xhr.responseJSON.message;
                }
                showToast('Error', message, 'error');
            }
        });
    });
});

function showCreateModal() {
    $('#categoryModalTitle').text('Add Expense Category');
    $('#categoryForm')[0].reset();
    $('#categoryId').val('');
    $('#categoryModal').modal('show');
}

function editCategory(id, name, description) {
    $('#categoryModalTitle').text('Edit Expense Category');
    $('#categoryId').val(id);
    $('#category_name').val(name);
    $('#description').val(description || '');
    $('#categoryModal').modal('show');
}

function deleteCategory(id, name) {
    Swal.fire({
        title: 'Delete Category?',
        text: `Are you sure you want to delete "${name}"? This action cannot be undone.`,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#3085d6',
        confirmButtonText: 'Yes, delete it!'
    }).then((result) => {
        if (result.isConfirmed) {
            $.ajax({
                url: '<?= base_url('expenses/categories/delete') ?>/' + id,
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