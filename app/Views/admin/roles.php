<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0"><i class="bi bi-shield-lock me-2"></i>Role Management</h5>
                <a href="<?= base_url('admin/permissions') ?>" class="btn btn-primary btn-sm">
                    <i class="bi bi-grid-3x3-gap me-1"></i> Permission Matrix
                </a>
            </div>
            <div class="card-body">
                <?php if (session()->getFlashdata('success')): ?>
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        <?= session()->getFlashdata('success') ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>
                
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Role Name</th>
                                <th>Description</th>
                                <th>Permissions</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($roles as $role): ?>
                            <tr>
                                <td><?= $role['id'] ?></td>
                                <td><strong><?= $role['role_name'] ?></strong></td>
                                <td><?= $role['description'] ?></td>
                                <td>
                                    <span class="badge bg-info"><?= $role['permission_count'] ?> Permissions</span>
                                </td>
                                <td>
                                    <a href="<?= base_url('admin/roles/permissions/' . $role['id']) ?>" class="btn btn-sm btn-primary">
                                        <i class="bi bi-gear"></i> Manage Permissions
                                    </a>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
<?= $this->endSection() ?>
