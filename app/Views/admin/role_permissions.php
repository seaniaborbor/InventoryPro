<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0"><i class="bi bi-shield-lock me-2"></i>Role Permissions: <?= $role['role_name'] ?></h5>
                <a href="<?= base_url('admin/permissions') ?>" class="btn btn-outline-primary btn-sm">
                    <i class="bi bi-grid-3x3-gap me-1"></i> Open Matrix View
                </a>
            </div>
            <div class="card-body">
                <?php
                $groupTitles = [
                    'general' => 'General',
                    'inventory' => 'Inventory',
                    'sales' => 'Sales & Customers',
                    'purchases' => 'Purchases & Suppliers',
                    'expenses' => 'Expenses',
                    'production' => 'Production',
                    'reports' => 'Reports',
                    'administration' => 'Administration',
                ];
                ?>
                <form action="<?= base_url('admin/roles/permissions/' . $role['id']) ?>" method="post">
                    <?= csrf_field() ?>

                    <?php foreach ($groupedPermissions as $groupKey => $permissions): ?>
                        <?php if (empty($permissions)): ?>
                            <?php continue; ?>
                        <?php endif; ?>
                        <div class="mb-4">
                            <h6 class="border-bottom pb-2"><?= esc($groupTitles[$groupKey] ?? ucfirst($groupKey)) ?></h6>
                            <div class="row">
                                <?php foreach ($permissions as $permission): ?>
                                <div class="col-md-6 col-lg-4 mb-2">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="permissions[]"
                                               value="<?= $permission['id'] ?>" id="perm_<?= $permission['id'] ?>"
                                               <?= in_array($permission['permission_name'], $rolePermissionIds, true) ? 'checked' : '' ?>
                                               <?= strcasecmp($role['role_name'], 'Admin') === 0 ? 'checked disabled' : '' ?>>
                                        <label class="form-check-label" for="perm_<?= $permission['id'] ?>">
                                            <strong><?= esc($permission['label'] ?? ucfirst(str_replace('_', ' ', $permission['permission_name']))) ?></strong>
                                            <br><small class="text-muted"><?= esc($permission['description']) ?></small>
                                        </label>
                                    </div>
                                </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                    
                    <div class="mt-3">
                        <a href="<?= base_url('admin/roles') ?>" class="btn btn-secondary">
                            <i class="bi bi-arrow-left"></i> Back
                        </a>
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-save"></i> Save Permissions
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
<?= $this->endSection() ?>
