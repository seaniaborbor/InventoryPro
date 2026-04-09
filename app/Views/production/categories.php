<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Production Categories</h5>
                    <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addCategoryModal">
                        <i class="bi bi-plus-circle"></i> Add Category
                    </button>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped table-hover" id="categoriesTable">
                            <thead>
                                <tr>
                                    <th>Category Name</th>
                                    <th>Description</th>
                                    <th>Jobs Count</th>
                                    <th>Created</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($categories as $category): ?>
                                <tr>
                                    <td><?= esc($category['category_name']) ?></td>
                                    <td><?= esc($category['description'] ?? '-') ?></td>
                                    <td>
                                        <span class="badge bg-info"><?= $category['job_count'] ?? 0 ?></span>
                                    </td>
                                    <td><?= date('M d, Y', strtotime($category['created_at'])) ?></td>
                                    <td>
                                        <div class="btn-group" role="group">
                                            <button type="button" class="btn btn-sm btn-outline-primary edit-category"
                                                    data-id="<?= $category['id'] ?>"
                                                    data-name="<?= esc($category['category_name']) ?>"
                                                    data-description="<?= esc($category['description'] ?? '') ?>">
                                                <i class="bi bi-pencil"></i> Edit
                                            </button>
                                            <button type="button" class="btn btn-sm btn-outline-danger delete-category"
                                                    data-id="<?= $category['id'] ?>"
                                                    data-name="<?= esc($category['category_name']) ?>">
                                                <i class="bi bi-trash"></i> Delete
                                            </button>
                                        </div>
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
</div>

<!-- Add Category Modal -->
<div class="modal fade" id="addCategoryModal" tabindex="-1" aria-labelledby="addCategoryModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="addCategoryModalLabel">Add Production Category</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="addCategoryForm">
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="category_name" class="form-label">Category Name <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="category_name" name="category_name" required>
                        <div class="invalid-feedback">
                            Please provide a category name.
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="description" class="form-label">Description</label>
                        <textarea class="form-control" id="description" name="description" rows="3"></textarea>
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

<!-- Edit Category Modal -->
<div class="modal fade" id="editCategoryModal" tabindex="-1" aria-labelledby="editCategoryModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editCategoryModalLabel">Edit Production Category</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="editCategoryForm">
                <input type="hidden" id="edit_category_id" name="category_id">
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="edit_category_name" class="form-label">Category Name <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="edit_category_name" name="category_name" required>
                        <div class="invalid-feedback">
                            Please provide a category name.
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="edit_description" class="form-label">Description</label>
                        <textarea class="form-control" id="edit_description" name="description" rows="3"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Update Category</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
$(document).ready(function() {
    // Initialize DataTable
    $('#categoriesTable').DataTable({
        "order": [[ 0, "asc" ]],
        "pageLength": 25,
        "columnDefs": [
            { "orderable": false, "targets": 4 }
        ]
    });

    // Add Category Form
    $('#addCategoryForm').on('submit', function(e) {
        e.preventDefault();

        const formData = new FormData(this);

        $.ajax({
            url: '<?= base_url('production/store-category') ?>',
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function(response) {
                if (response.status === 'success') {
                    $('#addCategoryModal').modal('hide');
                    $('#addCategoryForm')[0].reset();
                    location.reload();
                } else {
                    if (response.errors) {
                        displayValidationErrors(response.errors);
                    } else {
                        Swal.fire('Error', response.message, 'error');
                    }
                }
            },
            error: function() {
                Swal.fire('Error', 'An error occurred while saving the category.', 'error');
            }
        });
    });

    // Edit Category
    $('.edit-category').on('click', function() {
        const id = $(this).data('id');
        const name = $(this).data('name');
        const description = $(this).data('description');

        $('#edit_category_id').val(id);
        $('#edit_category_name').val(name);
        $('#edit_description').val(description);

        $('#editCategoryModal').modal('show');
    });

    // Edit Category Form
    $('#editCategoryForm').on('submit', function(e) {
        e.preventDefault();

        const categoryId = $('#edit_category_id').val();
        const formData = new FormData(this);

        $.ajax({
            url: '<?= base_url('production/update-category/') ?>' + categoryId,
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function(response) {
                if (response.status === 'success') {
                    $('#editCategoryModal').modal('hide');
                    location.reload();
                } else {
                    if (response.errors) {
                        displayValidationErrors(response.errors, 'edit');
                    } else {
                        Swal.fire('Error', response.message, 'error');
                    }
                }
            },
            error: function() {
                Swal.fire('Error', 'An error occurred while updating the category.', 'error');
            }
        });
    });

    // Delete Category
    $('.delete-category').on('click', function() {
        const id = $(this).data('id');
        const name = $(this).data('name');

        Swal.fire({
            title: 'Delete Category',
            text: `Are you sure you want to delete "${name}"? This action cannot be undone.`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'Yes, delete it!'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: '<?= base_url('production/delete-category/') ?>' + id,
                    type: 'POST',
                    success: function(response) {
                        if (response.status === 'success') {
                            location.reload();
                        } else {
                            Swal.fire('Error', response.message, 'error');
                        }
                    },
                    error: function() {
                        Swal.fire('Error', 'An error occurred while deleting the category.', 'error');
                    }
                });
            }
        });
    });

    function displayValidationErrors(errors, prefix = '') {
        // Clear previous errors
        $('.invalid-feedback').hide();
        $('.form-control').removeClass('is-invalid');

        // Display new errors
        for (const [field, message] of Object.entries(errors)) {
            const fieldName = prefix ? `${prefix}_${field}` : field;
            $(`#${fieldName}`).addClass('is-invalid');
            $(`#${fieldName}`).next('.invalid-feedback').text(message).show();
        }
    }
});
</script>

<?= $this->endSection() ?>
