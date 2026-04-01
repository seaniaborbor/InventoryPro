<?= $this->extend('layouts/login') ?>

<?= $this->section('content') ?>
<form action="<?= base_url('auth/update-password') ?>" method="post">
    <?= csrf_field() ?>
    <input type="hidden" name="token" value="<?= $token ?>">
    
    <div class="mb-4">
        <p class="text-muted">Create a new password for your account.</p>
    </div>
    
    <div class="mb-3">
        <label for="password" class="form-label">New Password</label>
        <div class="input-group">
            <span class="input-group-text"><i class="bi bi-lock"></i></span>
            <input type="password" class="form-control" id="password" name="password" required>
            <button class="btn btn-outline-secondary" type="button" onclick="togglePassword('password', this)">
                <i class="bi bi-eye"></i>
            </button>
        </div>
        <small class="text-muted">Password must be at least 6 characters</small>
    </div>
    
    <div class="mb-3">
        <label for="confirm_password" class="form-label">Confirm Password</label>
        <div class="input-group">
            <span class="input-group-text"><i class="bi bi-lock"></i></span>
            <input type="password" class="form-control" id="confirm_password" name="confirm_password" required>
            <button class="btn btn-outline-secondary" type="button" onclick="togglePassword('confirm_password', this)">
                <i class="bi bi-eye"></i>
            </button>
        </div>
    </div>
    
    <button type="submit" class="btn btn-login">
        <i class="bi bi-check-circle me-2"></i>Reset Password
    </button>
</form>

<script>
function togglePassword(fieldId, button) {
    var field = document.getElementById(fieldId);
    var icon = button.querySelector('i');
    
    if (field.type === 'password') {
        field.type = 'text';
        icon.classList.remove('bi-eye');
        icon.classList.add('bi-eye-slash');
    } else {
        field.type = 'password';
        icon.classList.remove('bi-eye-slash');
        icon.classList.add('bi-eye');
    }
}
</script>
<?= $this->endSection() ?>
