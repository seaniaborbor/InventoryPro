<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<div class="row">
    <div class="col-md-4 mb-4">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0"><i class="bi bi-person-circle me-2"></i>Profile Information</h5>
            </div>
            <div class="card-body text-center">
                <div class="mb-3">
                    <div class="avatar-circle bg-primary text-white mx-auto mb-3" style="width: 100px; height: 100px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 3rem;">
                        <?= strtoupper(substr($user['full_name'], 0, 1)) ?>
                    </div>
                    <h4><?= $user['full_name'] ?></h4>
                    <p class="text-muted"><?= $user['username'] ?></p>
                </div>
                
                <div class="list-group list-group-flush text-start">
                    <div class="list-group-item d-flex justify-content-between">
                        <strong>Role:</strong>
                        <span><?= $user['role_id'] == 1 ? 'Administrator' : ($user['role_id'] == 2 ? 'Manager' : 'Staff') ?></span>
                    </div>
                    <div class="list-group-item d-flex justify-content-between">
                        <strong>Status:</strong>
                        <span class="badge bg-success">Active</span>
                    </div>
                    <div class="list-group-item d-flex justify-content-between">
                        <strong>2FA Status:</strong>
                        <span class="badge <?= $user['two_factor_enabled'] ? 'bg-success' : 'bg-secondary' ?>">
                            <?= $user['two_factor_enabled'] ? 'Enabled' : 'Disabled' ?>
                        </span>
                    </div>
                    <div class="list-group-item d-flex justify-content-between">
                        <strong>Last Login:</strong>
                        <span><?= $user['last_login'] ? date('Y-m-d H:i', strtotime($user['last_login'])) : 'Never' ?></span>
                    </div>
                </div>
                
                <div class="mt-3">
                    <?php if (!$user['two_factor_enabled']): ?>
                        <a href="<?= base_url('profile/setup-2fa') ?>" class="btn btn-primary btn-sm">
                            <i class="bi bi-shield-lock"></i> Setup 2FA
                        </a>
                    <?php else: ?>
                        <button class="btn btn-warning btn-sm" onclick="disable2FA()">
                            <i class="bi bi-shield-lock"></i> Disable 2FA
                        </button>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-md-8 mb-4">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0"><i class="bi bi-pencil-square me-2"></i>Edit Profile</h5>
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
                
                <form action="<?= base_url('profile/update') ?>" method="post">
                    <?= csrf_field() ?>
                    
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="full_name" class="form-label">Full Name *</label>
                            <input type="text" class="form-control" id="full_name" name="full_name" 
                                   value="<?= old('full_name', $user['full_name']) ?>" required>
                        </div>
                        <div class="col-md-6">
                            <label for="username" class="form-label">Username *</label>
                            <input type="text" class="form-control" id="username" name="username" 
                                   value="<?= $user['username'] ?>" readonly disabled>
                        </div>
                    </div>
                    
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="email" class="form-label">Email *</label>
                            <input type="email" class="form-control" id="email" name="email" 
                                   value="<?= old('email', $user['email']) ?>" required>
                        </div>
                        <div class="col-md-6">
                            <label for="phone" class="form-label">Phone</label>
                            <input type="text" class="form-control" id="phone" name="phone" 
                                   value="<?= old('phone', $user['phone']) ?>">
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-save"></i> Update Profile
                        </button>
                        <a href="<?= base_url('profile/change-password') ?>" class="btn btn-outline-secondary">
                            <i class="bi bi-key"></i> Change Password
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
function disable2FA() {
    Swal.fire({
        title: 'Disable 2FA?',
        text: 'Are you sure you want to disable two-factor authentication? This will make your account less secure.',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#3085d6',
        confirmButtonText: 'Yes, disable it!'
    }).then((result) => {
        if (result.isConfirmed) {
            $.ajax({
                url: '<?= base_url('profile/disable-2fa') ?>',
                type: 'POST',
                data: {<?= csrf_token() ?>: '<?= csrf_hash() ?>'},
                success: function(response) {
                    window.location.reload();
                }
            });
        }
    });
}
</script>
<?= $this->endSection() ?>