<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0"><i class="bi bi-pencil me-2"></i>Edit Adjustment #<?= esc($event['id']) ?></h5>
                <a href="<?= base_url('adjustments/view/' . $event['id']) ?>" class="btn btn-secondary btn-sm">Back</a>
            </div>
            <div class="card-body">
                <form id="adjustmentEditForm">
                    <?= csrf_field() ?>

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="product_id" class="form-label">Product *</label>
                            <select class="form-select" id="product_id" required>
                                <option value="">Select Product</option>
                                <?php foreach ($products as $p): ?>
                                    <option value="<?= $p['id'] ?>" <?= $event['product_id'] == $p['id'] ? 'selected' : '' ?>>
                                        <?= esc($p['product_name']) ?> (Stock: <?= number_format($p['quantity'] ?? 0, 2) ?>)
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label for="event_type" class="form-label">Event Type *</label>
                            <select class="form-select" id="event_type" required>
                                <?php foreach (['Damage', 'Refund', 'Return', 'Theft', 'Other'] as $t): ?>
                                    <option value="<?= $t ?>" <?= $event['event_type'] === $t ? 'selected' : '' ?>><?= $t ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-3">
                            <label for="quantity" class="form-label">Quantity *</label>
                            <input type="number" step="0.01" min="0.01" class="form-control" id="quantity" value="<?= esc($event['quantity']) ?>" required>
                        </div>
                        <div class="col-md-3">
                            <label for="unit_cost" class="form-label">Unit Cost</label>
                            <input type="number" step="0.01" min="0" class="form-control" id="unit_cost" value="<?= esc($event['unit_cost'] ?? 0) ?>">
                        </div>
                        <div class="col-md-3">
                            <label for="currency" class="form-label">Currency</label>
                            <select class="form-select" id="currency">
                                <option value="LRD" <?= $event['currency'] === 'LRD' ? 'selected' : '' ?>>LRD</option>
                                <option value="USD" <?= $event['currency'] === 'USD' ? 'selected' : '' ?>>USD</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label for="event_date" class="form-label">Date/Time</label>
                            <input type="datetime-local" class="form-control" id="event_date" value="<?= date('Y-m-d\TH:i', strtotime($event['event_date'])) ?>">
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="customer_id" class="form-label">Customer</label>
                            <select class="form-select" id="customer_id">
                                <option value="">None</option>
                                <?php foreach ($customers as $c): ?>
                                    <option value="<?= $c['id'] ?>" <?= $event['customer_id'] == $c['id'] ? 'selected' : '' ?>><?= esc($c['customer_name']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label for="reference" class="form-label">Reference</label>
                            <input type="text" class="form-control" id="reference" value="<?= esc($event['reference'] ?? '') ?>">
                        </div>
                        <div class="col-md-3">
                            <label for="adjust_stock" class="form-label">Adjust Stock?</label>
                            <select class="form-select" id="adjust_stock">
                                <option value="1" <?= $event['adjust_stock'] ? 'selected' : '' ?>>Yes</option>
                                <option value="0" <?= !$event['adjust_stock'] ? 'selected' : '' ?>>No (record only)</option>
                            </select>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="description" class="form-label">Description</label>
                        <textarea class="form-control" id="description" rows="3"><?= esc($event['description'] ?? '') ?></textarea>
                    </div>

                    <div class="text-end">
                        <a href="<?= base_url('adjustments/view/' . $event['id']) ?>" class="btn btn-secondary">Cancel</a>
                        <button type="submit" class="btn btn-success btn-lg">Update Adjustment</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
$(document).ready(function() {
    $('#adjustmentEditForm').on('submit', function(e) {
        e.preventDefault();

        if (!$('#product_id').val() || !$('#event_type').val() || !$('#quantity').val()) {
            alert('Please fill all required fields.');
            return;
        }

        var data = {
            product_id:    parseInt($('#product_id').val()),
            event_type:    $('#event_type').val(),
            quantity:      parseFloat($('#quantity').val()),
            unit_cost:     parseFloat($('#unit_cost').val()) || 0,
            currency:      $('#currency').val(),
            event_date:    $('#event_date').val() || null,
            customer_id:   $('#customer_id').val() || null,
            reference:     $('#reference').val().trim() || null,
            adjust_stock:  parseInt($('#adjust_stock').val()),
            description:   $('#description').val().trim(),
        };

        $.ajax({
            url: '<?= base_url('adjustments/update/' . $event['id']) ?>',
            type: 'POST',
            data: JSON.stringify(data),
            contentType: 'application/json',
            dataType: 'json',
            headers: { 'X-Requested-With': 'XMLHttpRequest' },
            success: function(resp) {
                if (resp.status === 'success') {
                    alert('Adjustment updated!');
                    window.location.href = '<?= base_url('adjustments/view/' . $event['id']) ?>';
                } else {
                    alert(resp.message || 'Error updating adjustment.');
                }
            },
            error: function(xhr) {
                alert('Server error.');
                console.error(xhr.responseText);
            }
        });
    });
});
</script>

<?= $this->endSection() ?>
