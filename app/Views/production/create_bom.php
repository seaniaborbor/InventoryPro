<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0"><i class="bi bi-file-text me-2"></i>Create Bill of Materials Template</h5>
            </div>
            <div class="card-body">
                <div class="alert alert-info">
                    <i class="bi bi-info-circle"></i>
                    <strong>BOM Template:</strong> Create templates for standard production recipes.
                    This will help you quickly create production jobs with predefined materials.
                </div>

                <form id="bomForm">
                    <?= csrf_field() ?>

                    <div class="row mb-3">
                        <div class="col-md-12">
                            <label for="template_name" class="form-label">Template Name *</label>
                            <input type="text" class="form-control" id="template_name" name="template_name"
                                placeholder="e.g., Standard ID Card (100 pcs)" required>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="description" class="form-label">Description</label>
                        <textarea class="form-control" id="description" name="description" rows="2"
                            placeholder="Describe what this template is used for..."></textarea>
                    </div>

                    <hr class="my-4">

                    <div class="mb-3">
                        <label class="form-label fw-bold">Materials List</label>
                        <div class="alert alert-secondary">
                            <i class="bi bi-info-circle"></i> Add the materials needed for this production template.
                        </div>

                        <div class="row g-2 mb-3">
                            <div class="col-md-5">
                                <label class="form-label small">Product</label>
                                <select class="form-select" id="material_select">
                                    <option value="">Select Material</option>
                                    <?php foreach ($products as $product): ?>
                                        <option value="<?= $product['id'] ?>" data-price="<?= $product['purchase_price'] ?>"
                                            data-name="<?= $product['product_name'] ?>"
                                            data-unit="<?= $product['unit_symbol'] ?? '' ?>">
                                            <?= $product['product_name'] ?> (SKU: <?= $product['sku'] ?>)
                                            - Stock: <?= number_format($product['quantity'], 2) ?>
                                            <?= $product['unit_symbol'] ?? '' ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label small">Quantity</label>
                                <input type="number" step="0.01" class="form-control" id="material_quantity"
                                    placeholder="Quantity">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label small">Unit Cost</label>
                                <input type="number" step="0.01" class="form-control" id="material_cost"
                                    placeholder="Auto-filled" readonly>
                            </div>
                            <div class="col-md-1">
                                <label class="form-label small">&nbsp;</label>
                                <button class="btn btn-primary w-100" type="button" onclick="addMaterial()">
                                    <i class="bi bi-plus"></i>
                                </button>
                            </div>
                        </div>

                        <div class="table-responsive">
                            <table class="table table-bordered" id="materialsTable">
                                <thead class="table-light">
                                    <tr>
                                        <th>#</th>
                                        <th>Material</th>
                                        <th class="text-center" width="120">Quantity</th>
                                        <th class="text-end" width="120">Unit Cost</th>
                                        <th class="text-end" width="120">Total</th>
                                        <th class="text-center" width="50">Action</th>
                                    </tr>
                                </thead>
                                <tbody id="materialsBody">
                                    <tr>
                                        <td colspan="6" class="text-center text-muted">No materials added yet</td>
                                    </tr>
                                </tbody>
                                <tfoot class="table-active">
                                    <tr>
                                        <td colspan="4" class="text-end fw-bold">Total Material Cost:</td>
                                        <td class="text-end fw-bold" id="totalCost">0.00</td>
                                        <td></td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>

                    <div class="alert alert-warning mt-3">
                        <i class="bi bi-exclamation-triangle"></i>
                        <strong>Note:</strong> When you create a production job from this template,
                        the system will check if you have sufficient stock of these materials.
                    </div>

                    <div class="text-end mt-4">
                        <a href="<?= base_url('production/bom') ?>" class="btn btn-secondary">
                            <i class="bi bi-x-circle"></i> Cancel
                        </a>
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-save"></i> Save Template
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
    let materials = [];
    let nextId = 1;

    $(document).ready(function () {
        // Auto-fill unit cost when product is selected
        $('#material_select').on('change', function () {
            var price = $(this).find(':selected').data('price');
            if (price) {
                $('#material_cost').val(price);
            } else {
                $('#material_cost').val('');
            }
        });

        // Form submission
        $('#bomForm').on('submit', function (e) {
            e.preventDefault();

            // Validate template name
            var templateName = $('#template_name').val().trim();
            if (!templateName) {
                showToast('Error', 'Please enter a template name', 'error');
                $('#template_name').focus();
                return;
            }

            // Validate materials
            if (materials.length === 0) {
                showToast('Error', 'Please add at least one material to the template', 'error');
                return;
            }

            // Prepare form data
            var formData = {
                template_name: templateName,
                description: $('#description').val(),
                items: materials
            };

            // Show loading
            Swal.fire({
                title: 'Saving Template...',
                text: 'Please wait',
                allowOutsideClick: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            });

            // Submit AJAX
            $.ajax({
                url: '<?= base_url('production/bom/store') ?>',
                type: 'POST',
                data: JSON.stringify(formData),
                contentType: 'application/json',
                dataType: 'json',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                },
                success: function (response) {
                    if (response.status === 'success') {
                        Swal.fire({
                            icon: 'success',
                            title: 'Success!',
                            text: response.message,
                            timer: 2000,
                            showConfirmButton: false
                        });
                        setTimeout(function () {
                            window.location.href = '<?= base_url('production/bom') ?>';
                        }, 2000);
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error!',
                            text: response.message || 'Failed to save template'
                        });
                    }
                },
                error: function (xhr) {
                    console.error('Error:', xhr);
                    Swal.fire({
                        icon: 'error',
                        title: 'Error!',
                        text: 'An error occurred: ' + (xhr.responseText || 'Unknown error')
                    });
                }
            });
        });
    });

    function addMaterial() {
        var productId = $('#material_select').val();
        var productName = $('#material_select option:selected').data('name');
        var quantity = parseFloat($('#material_quantity').val());
        var unitCost = parseFloat($('#material_cost').val());

        // Validate
        if (!productId) {
            showToast('Error', 'Please select a material', 'error');
            $('#material_select').focus();
            return;
        }

        if (!quantity || quantity <= 0) {
            showToast('Error', 'Please enter a valid quantity greater than 0', 'error');
            $('#material_quantity').focus();
            return;
        }

        if (!unitCost || unitCost <= 0) {
            showToast('Error', 'Please enter a valid unit cost', 'error');
            $('#material_cost').focus();
            return;
        }

        // Check if material already added
        var existing = materials.find(m => m.product_id == productId);
        if (existing) {
            Swal.fire({
                title: 'Material Already Added',
                text: 'This material is already in the list. Do you want to update the quantity?',
                icon: 'question',
                showCancelButton: true,
                confirmButtonText: 'Yes, update it',
                cancelButtonText: 'No, cancel'
            }).then((result) => {
                if (result.isConfirmed) {
                    existing.quantity = quantity;
                    existing.unit_cost = unitCost;
                    renderMaterials();
                    showToast('Success', 'Material quantity updated', 'success');
                }
            });
            return;
        }

        // Add new material
        materials.push({
            id: nextId++,
            product_id: productId,
            product_name: productName,
            quantity: quantity,
            unit_cost: unitCost
        });

        // Clear inputs
        $('#material_quantity').val('');
        $('#material_select').val('');
        $('#material_cost').val('');

        // Re-render table
        renderMaterials();
        showToast('Success', 'Material added successfully', 'success');
    }

    function renderMaterials() {
        var html = '';
        var totalCost = 0;

        if (materials.length === 0) {
            html = '<tr><td colspan="6" class="text-center text-muted">No materials added yet</td></tr>';
        } else {
            materials.forEach(function (material, index) {
                var total = material.quantity * material.unit_cost;
                totalCost += total;

                html += '<tr>';
                html += '<td>' + (index + 1) + '</td>';
                html += '<td><strong>' + escapeHtml(material.product_name) + '</strong></td>';
                html += '<td class="text-center">' + formatNumber(material.quantity) + '</td>';
                html += '<td class="text-end">' + formatNumber(material.unit_cost) + '</td>';
                html += '<td class="text-end fw-bold">' + formatNumber(total) + '</td>';
                html += '<td class="text-center">';
                html += '<button class="btn btn-sm btn-danger" onclick="removeMaterial(' + index + ')">';
                html += '<i class="bi bi-trash"></i>';
                html += '</button>';
                html += '</td>';
                html += '</tr>';
            });
        }

        $('#materialsBody').html(html);
        $('#totalCost').text(formatNumber(totalCost));
    }

    function removeMaterial(index) {
        Swal.fire({
            title: 'Remove Material?',
            text: 'Are you sure you want to remove this material from the template?',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'Yes, remove it!'
        }).then((result) => {
            if (result.isConfirmed) {
                materials.splice(index, 1);
                renderMaterials();
                showToast('Success', 'Material removed successfully', 'success');
            }
        });
    }

    function formatNumber(num) {
        return parseFloat(num).toLocaleString('en-US', {
            minimumFractionDigits: 2,
            maximumFractionDigits: 2
        });
    }

    function escapeHtml(text) {
        if (!text) return '';
        return text
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#39;');
    }

    // Keyboard shortcuts
    $(document).on('keypress', function (e) {
        if (e.which === 13 && document.activeElement === $('#material_quantity')[0]) {
            addMaterial();
        }
    });
</script>

<style>
    #materialsTable tbody tr:hover {
        background-color: #f8f9fa;
    }

    #materialsTable .table-light th {
        background-color: #e9ecef;
    }
</style>
<?= $this->endSection() ?>