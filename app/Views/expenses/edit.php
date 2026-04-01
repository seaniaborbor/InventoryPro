<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<div class="row justify-content-center">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0"><i class="bi bi-pencil-square me-2"></i>Edit Expense</h5>
            </div>
            <div class="card-body">
                <!-- Audit Information -->
                <div class="alert alert-info">
                    <h6><i class="bi bi-info-circle me-2"></i>Audit Information</h6>
                    <div class="row">
                        <div class="col-md-6">
                            <strong>Created By:</strong> <?= $expense['created_by_name'] ?: 'Unknown' ?><br>
                            <small class="text-muted">Created: <?= date('M d, Y \a\t H:i', strtotime($expense['created_at'])) ?></small>
                        </div>
                        <div class="col-md-6">
                            <?php if ($expense['updated_by_name']): ?>
                                <strong>Last Edited By:</strong> <?= $expense['updated_by_name'] ?><br>
                                <small class="text-muted">Last Edited: <?= date('M d, Y \a\t H:i', strtotime($expense['updated_at'])) ?></small>
                            <?php else: ?>
                                <strong>Last Edited:</strong> Never edited
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                
                <form action="<?= base_url('expenses/update/' . $expense['id']) ?>" method="post" enctype="multipart/form-data">
                    <?= csrf_field() ?>
                    
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="expense_category_id" class="form-label">Category *</label>
                            <select class="form-select" id="expense_category_id" name="expense_category_id" required>
                                <option value="">Select Category</option>
                                <?php foreach ($categories as $cat): ?>
                                <option value="<?= $cat['id'] ?>" <?= $expense['expense_category_id'] == $cat['id'] ? 'selected' : '' ?>>
                                    <?= $cat['category_name'] ?>
                                </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label for="expense_date" class="form-label">Date *</label>
                            <input type="date" class="form-control" id="expense_date" name="expense_date" 
                                   value="<?= $expense['expense_date'] ?>" required>
                        </div>
                    </div>
                    
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="amount" class="form-label">Amount *</label>
                            <input type="number" step="0.01" class="form-control" id="amount" name="amount" 
                                   value="<?= $expense['amount'] ?>" required>
                        </div>
                        <div class="col-md-6">
                            <label for="currency" class="form-label">Currency *</label>
                            <select class="form-select" id="currency" name="currency" required>
                                <option value="LRD" <?= $expense['currency'] == 'LRD' ? 'selected' : '' ?>>LRD - Liberian Dollar</option>
                                <option value="USD" <?= $expense['currency'] == 'USD' ? 'selected' : '' ?>>USD - US Dollar</option>
                            </select>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="description" class="form-label">Description</label>
                        <textarea class="form-control" id="description" name="description" rows="3"><?= $expense['description'] ?></textarea>
                    </div>
                    
                    <div class="mb-3">
                        <label for="receipt" class="form-label">Receipt Image</label>
                        <?php if ($expense['receipt_image']): ?>
                            <div class="mb-2">
                                <img src="<?= base_url($expense['receipt_image']) ?>" alt="Receipt" style="max-width: 200px;" class="img-thumbnail">
                                <br>
                                <small class="text-muted">Current receipt attached</small>
                            </div>
                        <?php endif; ?>
                        <input type="file" class="form-control" id="receipt" name="receipt" accept="image/*,.pdf">
                        <small class="text-muted">Upload a new receipt to replace the existing one (optional)</small>
                    </div>
                    
                    <div class="d-flex justify-content-between">
                        <a href="<?= base_url('expenses') ?>" class="btn btn-secondary">Cancel</a>
                        <button type="submit" class="btn btn-primary">Update Expense</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
<?= $this->endSection() ?>