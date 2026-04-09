<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<div class="row">
    <div class="col-lg-8 mx-auto">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">
                    <i class="bi bi-plus-circle me-2"></i>Record Adjustment
                    <?php if (!empty($source)): ?>
                        <?php if ($source === 'sale'): ?>
                            — Sale <?= esc($sale['invoice_number'] ?? '') ?>
                        <?php elseif ($source === 'production'): ?>
                            — Job <?= esc($job['job_reference'] ?? '') ?>
                        <?php endif; ?>
                    <?php endif; ?>
                </h5>
                <?php if (!empty($sourceId)): ?>
                    <?php if ($source === 'sale'): ?>
                        <a href="<?= base_url('sales/view/' . $sourceId) ?>" class="btn btn-sm btn-secondary"><i class="bi bi-arrow-left"></i> Back to Sale</a>
                    <?php elseif ($source === 'production'): ?>
                        <a href="<?= base_url('production/view/' . $sourceId) ?>" class="btn btn-sm btn-secondary"><i class="bi bi-arrow-left"></i> Back to Job</a>
                    <?php endif; ?>
                <?php endif; ?>
            </div>
            <div class="card-body">
                <?php if ($source === 'sale'): ?>
                    <div class="alert alert-warning">
                        <i class="bi bi-exclamation-triangle me-1"></i>
                        Recording a <strong>refund</strong> or <strong>return</strong> for this sale. Product quantities will be adjusted accordingly.
                    </div>
                <?php elseif ($source === 'production'): ?>
                    <div class="alert alert-danger">
                        <i class="bi bi-exclamation-triangle me-1"></i>
                        Recording <strong>damaged material</strong> from this production job. Product quantities will be deducted.
                    </div>
                <?php endif; ?>

                <form id="adjustmentForm">
                    <?= csrf_field() ?>
                    <input type="hidden" id="source" value="<?= $source ?? '' ?>">
                    <input type="hidden" id="related_sale_id" value="<?= isset($sourceId) && $source === 'sale' ? $sourceId : '' ?>">
                    <input type="hidden" id="related_production_job_id" value="<?= isset($sourceId) && $source === 'production' ? $sourceId : '' ?>">
                    <?php if (!empty($sale['customer_id'])): ?>
                    <input type="hidden" id="sale_customer_id" value="<?= $sale['customer_id'] ?>">
                    <?php endif; ?>

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="product_id" class="form-label">Product *</label>
                            <select class="form-select" id="product_id" required>
                                <option value="">Select Product</option>
                                <?php foreach ($products as $p): ?>
                                    <option value="<?= $p['id'] ?>" data-cost="<?= $p['purchase_price'] ?? 0 ?>">
                                        <?= esc($p['product_name']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label for="event_type" class="form-label">Event Type *</label>
                            <select class="form-select" id="event_type" required>
                                <option value="">Select Type</option>
                                <?php if ($source === 'production'): ?>
                                    <option value="Damage">Damage</option>
                                <?php endif; ?>
                                <?php if ($source === 'sale'): ?>
                                    <option value="Refund">Refund</option>
                                    <option value="Return">Return</option>
                                <?php endif; ?>
                                <option value="Other">Other</option>
                            </select>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-4">
                            <label for="quantity" class="form-label">Quantity *</label>
                            <input type="number" step="0.01" min="0.01" class="form-control" id="quantity" required>
                        </div>
                        <div class="col-md-4">
                            <label for="unit_cost" class="form-label">Unit Cost</label>
                            <input type="number" step="0.01" min="0" class="form-control" id="unit_cost" value="0">
                        </div>
                        <div class="col-md-4">
                            <label for="currency" class="form-label">Currency</label>
                            <select class="form-select" id="currency">
                                <option value="LRD">LRD</option>
                                <option value="USD">USD</option>
                            </select>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="description" class="form-label">Description</label>
                        <textarea class="form-control" id="description" rows="3" placeholder="Describe what happened..."></textarea>
                    </div>

                    <div class="card bg-light mb-3">
                        <div class="card-body">
                            <strong>Estimated Value:</strong>
                            <span id="previewValue">0.00</span>
                        </div>
                    </div>

                    <div class="text-end">
                        <?php if ($source === 'sale'): ?>
                            <a href="<?= base_url('sales/view/' . $sourceId) ?>" class="btn btn-secondary">Cancel</a>
                        <?php elseif ($source === 'production'): ?>
                            <a href="<?= base_url('production/view/' . $sourceId) ?>" class="btn btn-secondary">Cancel</a>
                        <?php else: ?>
                            <a href="<?= base_url('adjustments') ?>" class="btn btn-secondary">Cancel</a>
                        <?php endif; ?>
                        <button type="submit" class="btn btn-success btn-lg">Record Adjustment</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
$(document).ready(function() {
    $('#product_id').on('change', function() {
        var cost = $(this).find(':selected').data('cost') || 0;
        $('#unit_cost').val(cost);
        updatePreview();
    });

    $('#quantity, #unit_cost').on('input', updatePreview);

    function updatePreview() {
        var qty = parseFloat($('#quantity').val()) || 0;
        var cost = parseFloat($('#unit_cost').val()) || 0;
        $('#previewValue').text((qty * cost).toFixed(2));
    }

    $('#adjustmentForm').on('submit', function(e) {
        e.preventDefault();

        if (!$('#product_id').val() || !$('#event_type').val() || !$('#quantity').val()) {
            alert('Please fill all required fields.');
            return;
        }

        var data = {
            product_id:            parseInt($('#product_id').val()),
            event_type:            $('#event_type').val(),
            quantity:              parseFloat($('#quantity').val()),
            unit_cost:             parseFloat($('#unit_cost').val()) || 0,
            currency:              $('#currency').val(),
            description:           $('#description').val().trim(),
            source:                $('#source').val(),
            related_sale_id:       $('#related_sale_id').val() || null,
            related_production_job_id: $('#related_production_job_id').val() || null,
            sale_customer_id:      $('#sale_customer_id').val() || null,
        };

        $.ajax({
            url: '<?= base_url('adjustments/store') ?>',
            type: 'POST',
            data: JSON.stringify(data),
            contentType: 'application/json',
            dataType: 'json',
            headers: { 'X-Requested-With': 'XMLHttpRequest' },
            success: function(resp) {
                if (resp.status === 'success') {
                    alert('Adjustment recorded!');
                    // Redirect back to the source
                    var src = $('#source').val();
                    var srcId = src === 'sale' ? $('#related_sale_id').val() : $('#related_production_job_id').val();
                    if (src === 'sale') window.location.href = '<?= base_url('sales/view/') ?>' + srcId;
                    else if (src === 'production') window.location.href = '<?= base_url('production/view/') ?>' + srcId;
                    else window.location.href = '<?= base_url('adjustments') ?>';
                } else {
                    alert(resp.message || 'Error recording adjustment.');
                }
            },
            error: function(xhr) {
                alert('Server error. Check console.');
                console.error(xhr.responseText);
            }
        });
    });
});
</script>

<?= $this->endSection() ?>
