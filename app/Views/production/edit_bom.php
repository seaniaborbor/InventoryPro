<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0"><i class="bi bi-pencil-square me-2"></i>Edit BOM Template</h5>
                <a href="<?= base_url('production/bom') ?>" class="btn btn-secondary btn-sm">
                    <i class="bi bi-arrow-left"></i> Back to Templates
                </a>
            </div>
            <div class="card-body">

                <form id="bomForm">
                    <?= csrf_field() ?>

                    <div class="row mb-3">
                        <div class="col-md-8">
                            <label for="template_name" class="form-label">Template Name *</label>
                            <input type="text" class="form-control" id="template_name" name="template_name"
                                value="<?= esc($template['template_name']) ?>" required>
                        </div>
                        <div class="col-md-4">
                            <label for="finished_product_id" class="form-label">Finished Product (Optional)</label>
                            <select class="form-select" id="finished_product_id" name="finished_product_id">
                                <option value="">None - Materials Only</option>
                                <?php foreach ($products as $product): ?>
                                    <option value="<?= $product['id'] ?>"
                                        <?= $template['finished_product_id'] == $product['id'] ? 'selected' : '' ?>>
                                        <?= esc($product['product_name']) ?> (SKU: <?= esc($product['sku']) ?>)
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="description" class="form-label">Description</label>
                        <textarea class="form-control" id="description" name="description" rows="2"><?= esc($template['description']) ?></textarea>
                    </div>

                    <hr class="my-4">

                    <div class="mb-3">
                        <label class="form-label fw-bold">Materials List</label>
                        <div class="alert alert-secondary">
                            <i class="bi bi-info-circle"></i> Add or remove materials for this template.
                        </div>

                        <div class="row g-2 mb-3">
                            <div class="col-md-5">
                                <label class="form-label small">Product</label>
                                <select class="form-select" id="material_select">
                                    <option value="">Select Material</option>
                                    <?php foreach ($products as $product): ?>
                                        <option value="<?= $product['id'] ?>"
                                            data-price="<?= $product['purchase_price'] ?>"
                                            data-name="<?= esc($product['product_name']) ?>"
                                            data-unit="<?= $product['unit_symbol'] ?? '' ?>">
                                            <?= esc($product['product_name']) ?> (SKU: <?= esc($product['sku']) ?>)
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
                                <tbody id="materialsBody"></tbody>
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
                        <strong>Note:</strong> Saving will replace all existing materials for this template.
                    </div>

                    <div class="text-end mt-4">
                        <a href="<?= base_url('production/bom') ?>" class="btn btn-secondary">
                            <i class="bi bi-x-circle"></i> Cancel
                        </a>
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-save"></i> Update Template
                        </button>
                    </div>
                </form>

            </div>
        </div>
    </div>
</div>

<script>
// Build a product lookup for unit cost auto-fill
// Pre-load existing items from PHP — quantity is the only column in bom_template_items
// unit_cost is derived from products.purchase_price via getItemsWithProducts()
let materials = <?= json_encode(array_map(function($item) {
    return [
        'product_id'   => (int)   ($item['product_id']   ?? 0),
        'product_name' =>          ($item['product_name'] ?? ''),
        'quantity'     => (float) ($item['quantity']     ?? 0),
        'unit_cost'    => (float) ($item['purchase_price'] ?? $item['unit_cost'] ?? 0),
    ];
}, $template['items'] ?? [])) ?>;

let nextId = materials.length + 1;

$(document).ready(function () {

    // Render existing items on load
    renderMaterials();

    // Auto-fill unit cost when product selected
    $('#material_select').on('change', function () {
        var price = $(this).find(':selected').data('price');
        $('#material_cost').val(price || '');
    });

    // Form submission
    $('#bomForm').on('submit', function (e) {
        e.preventDefault();

        var templateName = $('#template_name').val().trim();
        if (!templateName) {
            showToast('Error', 'Please enter a template name', 'error');
            $('#template_name').focus();
            return;
        }

        if (materials.length === 0) {
            showToast('Error', 'Please add at least one material to the template', 'error');
            return;
        }

        var formData = {
            template_name:       templateName,
            description:         $('#description').val(),
            finished_product_id: $('#finished_product_id').val(),
            items:               materials
        };

        Swal.fire({
            title: 'Updating Template...',
            text: 'Please wait',
            allowOutsideClick: false,
            didOpen: () => { Swal.showLoading(); }
        });

        $.ajax({
            url: '<?= base_url('production/bom/update/' . $template['id']) ?>',
            type: 'POST',
            data: JSON.stringify(formData),
            contentType: 'application/json',
            dataType: 'json',
            headers: { 'X-Requested-With': 'XMLHttpRequest' },
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
                        text: response.message || 'Failed to update template'
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
    var productId   = $('#material_select').val();
    var productName = $('#material_select option:selected').data('name');
    var quantity    = parseFloat($('#material_quantity').val());
    var unitCost    = parseFloat($('#material_cost').val());

    if (!productId) {
        showToast('Error', 'Please select a material', 'error');
        return;
    }
    if (!quantity || quantity <= 0) {
        showToast('Error', 'Please enter a valid quantity greater than 0', 'error');
        return;
    }
    if (!unitCost || unitCost <= 0) {
        showToast('Error', 'Please enter a valid unit cost', 'error');
        return;
    }

    // If already in list, offer to update
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
                existing.quantity  = quantity;
                existing.unit_cost = unitCost;
                renderMaterials();
                showToast('Success', 'Material quantity updated', 'success');
            }
        });
        return;
    }

    materials.push({
        product_id:   parseInt(productId),
        product_name: productName,
        quantity:     quantity,
        unit_cost:    unitCost
    });

    $('#material_quantity').val('');
    $('#material_select').val('');
    $('#material_cost').val('');

    renderMaterials();
    showToast('Success', 'Material added successfully', 'success');
}

function renderMaterials() {
    var html      = '';
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
        text: 'Are you sure you want to remove this material?',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#3085d6',
        confirmButtonText: 'Yes, remove it!'
    }).then((result) => {
        if (result.isConfirmed) {
            materials.splice(index, 1);
            renderMaterials();
            showToast('Success', 'Material removed', 'success');
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

$(document).on('keypress', function (e) {
    if (e.which === 13 && document.activeElement === $('#material_quantity')[0]) {
        addMaterial();
    }
});
</script>

<style>
    #materialsTable tbody tr:hover { background-color: #f8f9fa; }
    #materialsTable .table-light th { background-color: #e9ecef; }
</style>

<?= $this->endSection() ?>