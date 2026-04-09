<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>

<?php
// Build a quick product lookup map: id => product_name
$productMap = [];
foreach ($products as $p) {
    $productMap[$p['id']] = $p['product_name'];
}
?>

<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0"><i class="bi bi-file-text me-2"></i>Bill of Materials Templates</h5>
                <a href="<?= base_url('production/bom/create') ?>" class="btn btn-primary btn-sm">
                    <i class="bi bi-plus-circle"></i> New Template
                </a>
            </div>
            <div class="card-body">

                <?php if (empty($templates)): ?>
                    <div class="alert alert-info">
                        <i class="bi bi-info-circle"></i> No BOM templates found. 
                        <a href="<?= base_url('production/bom/create') ?>">Create your first template</a> to get started.
                    </div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-hover table-bordered align-middle">
                            <thead class="table-light">
                                <tr>
                                    <th>#</th>
                                    <th>Template Name</th>
                                    <th>Description</th>
                                    <th>Created</th>
                                    <th class="text-center" width="160">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($templates as $i => $template): ?>
                                    <tr>
                                        <td><?= $i + 1 ?></td>
                                        <td>
                                            <strong><?= esc($template['template_name']) ?></strong>
                                        </td>
                                        <td>
                                            <?php if (!empty($template['description'])): ?>
                                                <span class="text-muted small">
                                                    <?= esc(strlen($template['description']) > 80 ? substr($template['description'], 0, 80) . '...' : $template['description']) ?>
                                                </span>
                                            <?php else: ?>
                                                <span class="text-muted small fst-italic">No description</span>
                                            <?php endif; ?>
                                        <td class="small text-muted">
                                            <?= date('M j, Y', strtotime($template['created_at'])) ?>
                                        </td>
                                        <td class="text-center">
                                            <button class="btn btn-info btn-sm" title="View Materials"
                                                    onclick="viewTemplate(<?= $template['id'] ?>, '<?= esc($template['template_name']) ?>')">
                                                <i class="bi bi-eye"></i>
                                            </button>
                                            <a href="<?= base_url('production/bom/edit/' . $template['id']) ?>"
                                               class="btn btn-warning btn-sm" title="Edit">
                                                <i class="bi bi-pencil"></i>
                                            </a>
                                            <button class="btn btn-danger btn-sm" title="Delete"
                                                    onclick="deleteTemplate(<?= $template['id'] ?>, '<?= esc($template['template_name']) ?>')">
                                                <i class="bi bi-trash"></i>
                                            </button>
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

<!-- View Template Modal -->
<div class="modal fade" id="viewTemplateModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="bi bi-file-text me-2"></i><span id="modalTemplateName"></span></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div id="modalBody">
                    <div class="text-center py-4">
                        <div class="spinner-border text-primary" role="status"></div>
                        <p class="mt-2 text-muted">Loading materials...</p>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<script>
function viewTemplate(id, name) {
    $('#modalTemplateName').text(name);
    $('#modalBody').html('<div class="text-center py-4"><div class="spinner-border text-primary" role="status"></div><p class="mt-2 text-muted">Loading materials...</p></div>');
    $('#viewTemplateModal').modal('show');

    $.ajax({
        url: '<?= base_url('production/get-materials/') ?>' + id,
        type: 'GET',
        dataType: 'json',
        headers: { 'X-Requested-With': 'XMLHttpRequest' },
        success: function(response) {
            if (response.status === 'success' && response.data.length > 0) {
                var totalCost = 0;
                var rows = '';

                response.data.forEach(function(item, index) {
                    var unitCost  = parseFloat(item.purchase_price || item.unit_cost || 0);
                    var qty       = parseFloat(item.quantity || 0);
                    var lineTotal = qty * unitCost;
                    totalCost    += lineTotal;

                    rows += '<tr>';
                    rows += '<td>' + (index + 1) + '</td>';
                    rows += '<td><strong>' + escapeHtml(item.product_name) + '</strong>';
                    if (item.sku) rows += '<br><small class="text-muted">SKU: ' + escapeHtml(item.sku) + '</small>';
                    rows += '</td>';
                    rows += '<td class="text-center">' + formatNumber(qty) + ' ' + (item.unit_symbol || '') + '</td>';
                    rows += '<td class="text-end">' + formatNumber(unitCost) + '</td>';
                    rows += '<td class="text-end fw-bold">' + formatNumber(lineTotal) + '</td>';
                    rows += '</tr>';
                });

                $('#modalBody').html(
                    '<div class="table-responsive">' +
                    '<table class="table table-bordered table-hover">' +
                    '<thead class="table-light"><tr>' +
                    '<th>#</th><th>Material</th><th class="text-center">Quantity</th>' +
                    '<th class="text-end">Unit Cost</th><th class="text-end">Total Cost</th>' +
                    '</tr></thead>' +
                    '<tbody>' + rows + '</tbody>' +
                    '<tfoot class="table-active">' +
                    '<tr><td colspan="4" class="text-end fw-bold">Total Material Cost:</td>' +
                    '<td class="text-end fw-bold">' + formatNumber(totalCost) + '</td></tr>' +
                    '</tfoot>' +
                    '</table></div>'
                );
            } else {
                $('#modalBody').html('<div class="alert alert-info"><i class="bi bi-info-circle"></i> No materials found for this template.</div>');
            }
        },
        error: function(xhr) {
            $('#modalBody').html('<div class="alert alert-danger"><i class="bi bi-exclamation-triangle"></i> Failed to load materials.</div>');
            console.error(xhr.responseText);
        }
    });
}

function deleteTemplate(id, name) {
    if (!confirm('🗑️ Delete template "' + name + '"? This will also remove all its material items. This cannot be undone.')) return;

    $.ajax({
        url: '<?= base_url('production/bom/delete/') ?>' + id,
        type: 'POST',
        contentType: 'application/json',
        dataType: 'json',
        headers: { 'X-Requested-With': 'XMLHttpRequest' },
        data: JSON.stringify({}),
        success: function(response) {
            if (response.status === 'success') {
                alert('✅ ' + response.message);
                location.reload();
            } else {
                alert('❌ ' + response.message);
            }
        },
        error: function(xhr) {
            alert('❌ Server error. Check console for details.');
            console.error(xhr.responseText);
        }
    });
}

function formatNumber(num) {
    return parseFloat(num).toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
}

function escapeHtml(text) {
    if (!text) return '';
    return text.replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;').replace(/'/g,'&#39;');
}
</script>

<?= $this->endSection() ?>