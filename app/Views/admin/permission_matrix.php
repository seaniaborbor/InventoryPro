<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
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
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <div>
                    <h5 class="mb-0"><i class="bi bi-shield-lock me-2"></i>Role Permissions</h5>
                    <small class="text-muted">Control which modules and actions each role can access. Admin stays fully enabled to prevent lockout.</small>
                </div>
                <a href="<?= base_url('admin/roles') ?>" class="btn btn-outline-secondary btn-sm">
                    <i class="bi bi-arrow-left me-1"></i> Back to Roles
                </a>
            </div>
            <div class="card-body">
                <?php if (session()->getFlashdata('success')): ?>
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        <?= session()->getFlashdata('success') ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>

                <?php if (session()->getFlashdata('error')): ?>
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <?= session()->getFlashdata('error') ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>

                <div class="alert alert-info">
                    Use the module access rows to decide what appears in navigation. Uncheck `Delete Records` for roles like `Staff` if they should never be allowed to delete anything.
                </div>

                <form action="<?= base_url('admin/permissions') ?>" method="post">
                    <?= csrf_field() ?>

                    <div class="table-responsive">
                        <table class="table table-bordered align-middle permission-matrix-table">
                            <thead class="table-light">
                                <tr>
                                    <th style="min-width: 280px;">Permission</th>
                                    <?php foreach ($roles as $role): ?>
                                        <th class="text-center" style="min-width: 130px;"><?= esc(strtoupper($role['role_name'])) ?></th>
                                    <?php endforeach; ?>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($groupedPermissions as $groupKey => $permissions): ?>
                                    <?php if (empty($permissions)): ?>
                                        <?php continue; ?>
                                    <?php endif; ?>
                                    <tr class="table-secondary">
                                        <td colspan="<?= count($roles) + 1 ?>">
                                            <strong><?= esc($groupTitles[$groupKey] ?? ucfirst($groupKey)) ?></strong>
                                        </td>
                                    </tr>
                                    <?php foreach ($permissions as $permission): ?>
                                        <tr>
                                            <td>
                                                <div class="fw-semibold"><?= esc($permission['label'] ?? $permission['permission_name']) ?></div>
                                                <small class="text-muted"><?= esc($permission['description'] ?? '') ?></small>
                                            </td>
                                            <?php foreach ($roles as $role): ?>
                                                <?php
                                                $isAdminRole = strcasecmp($role['role_name'], 'Admin') === 0;
                                                $isChecked = $isAdminRole || isset($rolePermissionMap[$role['id']][$permission['permission_name']]);
                                                ?>
                                                <td class="text-center">
                                                    <input
                                                        type="checkbox"
                                                        class="form-check-input"
                                                        name="permissions[<?= $role['id'] ?>][]"
                                                        value="<?= $permission['id'] ?>"
                                                        <?= $isChecked ? 'checked' : '' ?>
                                                        <?= $isAdminRole ? 'disabled' : '' ?>
                                                    >
                                                    <?php if ($isAdminRole): ?>
                                                        <input type="hidden" name="permissions[<?= $role['id'] ?>][]" value="<?= $permission['id'] ?>">
                                                    <?php endif; ?>
                                                </td>
                                            <?php endforeach; ?>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>

                    <div class="d-flex justify-content-between align-items-center mt-3">
                        <small class="text-muted">Changes take effect on the next request for affected users.</small>
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-save me-1"></i> Save Permissions
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<style>
.permission-matrix-table td,
.permission-matrix-table th {
    vertical-align: middle;
}
</style>
<?= $this->endSection() ?>
