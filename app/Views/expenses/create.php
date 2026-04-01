<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<div class="row justify-content-center">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0"><i class="bi bi-plus-circle me-2"></i>Add Expense</h5>
            </div>
            <div class="card-body">
                <!-- Audit Information -->
                <div class="alert alert-info">
                    <i class="bi bi-info-circle me-2"></i>
                    This expense will be recorded as created by <strong><?= session()->get('username') ?: 'you' ?></strong>.
                </div>
                
                <form action="<?= base_url('expenses/store') ?>" method="post" enctype="multipart/form-data">
                    <?= csrf_field() ?>
                    
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="expense_category_id" class="form-label">Category *</label>
                            <select class="form-select" id="expense_category_id" name="expense_category_id" required>
                                <option value="">Select Category</option>
                                <?php foreach ($categories as $cat): ?>
                                <option value="<?= $cat['id'] ?>"><?= $cat['category_name'] ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label for="expense_date" class="form-label">Date *</label>
                            <input type="date" class="form-control" id="expense_date" name="expense_date" value="<?= date('Y-m-d') ?>" required>
                        </div>
                    </div>
                    
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="amount" class="form-label">Amount *</label>
                            <input type="number" step="0.01" class="form-control" id="amount" name="amount" required>
                        </div>
                        <div class="col-md-6">
                            <label for="currency" class="form-label">Currency *</label>
                            <select class="form-select" id="currency" name="currency" required>
                                <option value="LRD">LRD - Liberian Dollar</option>
                                <option value="USD">USD - US Dollar</option>
                            </select>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="description" class="form-label">Description</label>
                        <textarea class="form-control" id="description" name="description" rows="3" placeholder="Enter expense details..."></textarea>
                    </div>
                    
                    <div class="mb-3">
                        <label for="receipt" class="form-label">Receipt Image</label>
                        <input type="file" class="form-control" id="receipt" name="receipt" accept="image/*,.pdf">
                        <small class="text-muted">Upload receipt or supporting document (optional)</small>
                    </div>
                    
                    <div class="d-flex justify-content-between">
                        <a href="<?= base_url('expenses') ?>" class="btn btn-secondary">Cancel</a>
                        <button type="submit" class="btn btn-primary">Save Expense</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
<?= $this->endSection() ?>