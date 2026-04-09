<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0"><i class="bi bi-pencil-square me-2"></i>Edit Production Job</h5>
                <a href="<?= base_url('production/view/' . $job['id']) ?>" class="btn btn-secondary btn-sm">
                    <i class="bi bi-arrow-left"></i> Back to Job
                </a>
            </div>
            <div class="card-body">

                <form id="productionForm">
                    <?= csrf_field() ?>

                    <div class="row mb-3">
                        <div class="col-md-4">
                            <label class="form-label">Job Reference</label>
                            <input type="text" class="form-control" value="<?= esc($job['job_reference']) ?>" readonly>
                        </div>
                        <div class="col-md-4">
                            <label for="job_name" class="form-label">Job Name *</label>
                            <input type="text" class="form-control" id="job_name" name="job_name"
                                   value="<?= esc($job['job_name']) ?>" required>
                        </div>
                        <div class="col-md-4">
                            <label for="customer_id" class="form-label">Customer</label>
                            <select class="form-select" id="customer_id" name="customer_id">
                                <option value="">Select Customer</option>
                                <?php foreach ($customers as $customer): ?>
                                    <option value="<?= $customer['id'] ?>"
                                        <?= $job['customer_id'] == $customer['id'] ? 'selected' : '' ?>>
                                        <?= esc($customer['customer_name']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-4">
                            <label for="production_date" class="form-label">Production Date *</label>
                            <input type="date" class="form-control" id="production_date" name="production_date"
                                   value="<?= esc(date('Y-m-d', strtotime($job['production_date']))) ?>" required>
                        </div>
                        <div class="col-md-4">
                            <label for="production_category_id" class="form-label">Category</label>
                            <select class="form-select" id="production_category_id" name="production_category_id">
                                <option value="">Select Category</option>
                                <?php foreach ($categories as $category): ?>
                                    <option value="<?= $category['id'] ?>"
                                        <?= ($job['production_category_id'] ?? '') == $category['id'] ? 'selected' : '' ?>>
                                        <?= esc($category['category_name']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label for="quantity_produced" class="form-label">Quantity Produced</label>
                            <input type="number" step="0.01" min="0" class="form-control"
                                   id="quantity_produced" name="quantity_produced"
                                   value="<?= esc($job['quantity_produced']) ?>">
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="currency" class="form-label">Currency</label>
                            <select class="form-select" id="currency" name="currency">
                                <option value="LRD" <?= $job['currency'] === 'LRD' ? 'selected' : '' ?>>LRD - Liberian Dollar</option>
                                <option value="USD" <?= $job['currency'] === 'USD' ? 'selected' : '' ?>>USD - US Dollar</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label for="bom_template" class="form-label">Load from Template</label>
                            <div class="input-group">
                                <select class="form-select" id="bom_template">
                                    <option value="">Select Template</option>
                                    <?php foreach ($bomTemplates as $template): ?>
                                        <option value="<?= $template['id'] ?>"><?= esc($template['template_name']) ?></option>
                                    <?php endforeach; ?>
                                </select>
                                <button class="btn btn-outline-primary" type="button" onclick="loadTemplate()">Load</button>
                            </div>
                        </div>
                    </div>

                    <!-- Materials Section -->
                    <div class="mb-4">
                        <label class="form-label fw-bold">Materials Used</label>
                        <div class="input-group mb-3">
                            <select class="form-select" id="material_select">
                                <option value="">Select Material</option>
                                <?php foreach ($products as $product): ?>
                                    <option value="<?= $product['id'] ?>"
                                            data-price="<?= $product['purchase_price'] ?? 0 ?>">
                                        <?= esc($product['product_name']) ?>
                                        (Stock: <?= number_format($product['quantity'] ?? 0, 2) ?>)
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <input type="number" step="0.01" min="0.01" class="form-control"
                                   id="material_quantity" placeholder="Qty" style="max-width: 110px;">
                            <input type="number" step="0.01" min="0" class="form-control"
                                   id="material_cost" placeholder="Unit Cost" style="max-width: 130px;">
                            <button class="btn btn-primary" type="button" onclick="addMaterial()">Add Material</button>
                        </div>

                        <div class="table-responsive">
                            <table class="table table-bordered" id="materialsTable">
                                <thead class="table-light">
                                    <tr>
                                        <th>Material</th>
                                        <th width="130">Quantity</th>
                                        <th width="130">Unit Cost</th>
                                        <th width="140">Total Cost</th>
                                        <th width="60">Action</th>
                                    </tr>
                                </thead>
                                <tbody id="materialsBody"></tbody>
                                <tfoot>
                                    <tr class="table-active">
                                        <td colspan="3" class="text-end fw-bold">Total Material Cost:</td>
                                        <td class="fw-bold text-end" id="totalCost">0.00</td>
                                        <td></td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="notes" class="form-label">Notes</label>
                        <textarea class="form-control" id="notes" name="notes" rows="3"><?= esc($job['notes']) ?></textarea>
                    </div>

                    <div class="text-end">
                        <a href="<?= base_url('production/view/' . $job['id']) ?>" class="btn btn-secondary">Cancel</a>
                        <button type="submit" class="btn btn-success btn-lg">Update Production Job</button>
                    </div>
                </form>

            </div>
        </div>
    </div>
</div>

<script>
// Pre-load existing materials from PHP
let materials = <?= json_encode(array_map(function($mat) {
    return [
        'product_id'   => (int)    ($mat['product_id']   ?? 0),
        'product_name' =>           ($mat['product_name'] ?? $mat['name'] ?? ''),
        'quantity'     => (float)  ($mat['quantity']     ?? $mat['quantity_used'] ?? $mat['qty'] ?? 0),
        'unit_cost'    => (float)  ($mat['unit_cost']    ?? $mat['cost'] ?? $mat['price'] ?? 0),
    ];
}, $job['materials'] ?? [])) ?>;

$(document).ready(function() {

    // Render existing materials on page load
    renderMaterials();

    // Auto-fill cost when material selected
    $('#material_select').on('change', function() {
        const price = $(this).find(':selected').data('price') || 0;
        $('#material_cost').val(price);
    });

    // FORM SUBMIT
    $('#productionForm').on('submit', function(e) {
        e.preventDefault();

        if (materials.length === 0) {
            alert('❌ Please add at least one material to the production job.');
            return;
        }

        const formData = {
            job_name:            $('#job_name').val().trim(),
            customer_id:         $('#customer_id').val() || null,
            production_date:     $('#production_date').val(),
            quantity_produced:   parseFloat($('#quantity_produced').val()) || 0,
            currency:            $('#currency').val(),
            notes:               $('#notes').val().trim(),
            materials:           materials
        };

        console.log('📤 Sending update:', formData);

        $.ajax({
            url: '<?= base_url('production/update/' . $job['id']) ?>',
            type: 'POST',
            data: JSON.stringify(formData),
            contentType: 'application/json',
            dataType: 'json',
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            },
            success: function(response) {
                console.log('✅ Server Response:', response);
                if (response.status === 'success') {
                    alert('✅ Production job updated successfully!');
                    setTimeout(() => {
                        window.location.href = '<?= base_url('production/view/' . $job['id']) ?>';
                    }, 1000);
                } else {
                    alert('❌ ' + (response.message || 'Failed to update production job'));
                }
            },
            error: function(xhr, status, error) {
                console.error('❌ AJAX Error:', {
                    status: xhr.status,
                    responseText: xhr.responseText,
                    error: error
                });
                alert('❌ Server error occurred.\nCheck browser console (F12) for details.');
            }
        });
    });
});

function addMaterial() {
    const materialId   = $('#material_select').val();
    const materialName = $('#material_select option:selected').text().split(' (')[0].trim();
    const quantity     = parseFloat($('#material_quantity').val());
    const unitCost     = parseFloat($('#material_cost').val());

    if (!materialId || isNaN(quantity) || quantity <= 0 || isNaN(unitCost) || unitCost < 0) {
        alert('❌ Please select a material and enter valid quantity and unit cost.');
        return;
    }

    materials.push({
        product_id:   parseInt(materialId),
        product_name: materialName,
        quantity:     quantity,
        unit_cost:    unitCost
    });

    renderMaterials();
    $('#material_quantity').val('');
    $('#material_cost').val('');
}

function renderMaterials() {
    let html      = '';
    let totalCost = 0;

    materials.forEach((material, index) => {
        const lineTotal = material.quantity * material.unit_cost;
        totalCost += lineTotal;

        html += `<tr>
            <td><strong>${material.product_name}</strong></td>
            <td>${formatNumber(material.quantity)}</td>
            <td>${formatNumber(material.unit_cost)}</td>
            <td class="fw-bold text-end">${formatNumber(lineTotal)}</td>
            <td><button class="btn btn-sm btn-danger" onclick="removeMaterial(${index})"><i class="bi bi-trash"></i></button></td>
        </tr>`;
    });

    if (materials.length === 0) {
        html = `<tr><td colspan="5" class="text-center text-muted py-4">No materials added yet</td></tr>`;
    }

    $('#materialsBody').html(html);
    $('#totalCost').text(formatNumber(totalCost));
}

function removeMaterial(index) {
    materials.splice(index, 1);
    renderMaterials();
}

function loadTemplate() {
    const templateId = $('#bom_template').val();
    if (!templateId) return alert('Please select a template first.');

    if (!confirm('⚠️ Loading a template will replace all current materials. Continue?')) return;

    $.ajax({
        url: '<?= base_url('production/get-materials') ?>/' + templateId,
        type: 'GET',
        success: function(response) {
            if (response.status === 'success' && response.data) {
                materials = [];
                response.data.forEach(item => {
                    materials.push({
                        product_id:   parseInt(item.product_id),
                        product_name: item.product_name,
                        quantity:     parseFloat(item.quantity),
                        unit_cost:    parseFloat(item.unit_cost || 0)
                    });
                });
                renderMaterials();
                alert('✅ Template loaded successfully!');
            } else {
                alert('Failed to load template.');
            }
        }
    });
}

function formatNumber(num) {
    return parseFloat(num).toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2});
}
</script>

<?= $this->endSection() ?>