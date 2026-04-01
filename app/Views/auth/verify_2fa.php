<?= $this->extend('layouts/login') ?>

<?= $this->section('content') ?>
<form action="<?= base_url('auth/verify-2fa-code') ?>" method="post">
    <?= csrf_field() ?>
    
    <div class="text-center mb-4">
        <i class="bi bi-shield-lock" style="font-size: 3rem; color: #0d6efd;"></i>
        <h4 class="mt-2">Two-Factor Authentication</h4>
        <p class="text-muted">Enter the 6-digit code from your authenticator app</p>
    </div>
    
    <div class="mb-3">
        <label for="code" class="form-label">Verification Code</label>
        <input type="text" class="form-control text-center" id="code" name="code" 
               placeholder="000000" maxlength="6" required autofocus
               style="font-size: 1.5rem; letter-spacing: 5px;">
    </div>
    
    <button type="submit" class="btn btn-login">
        <i class="bi bi-check-circle me-2"></i>Verify
    </button>
</form>

<div class="mt-3 text-center">
    <a href="<?= base_url('auth/logout') ?>" class="text-decoration-none">
        <i class="bi bi-box-arrow-right"></i> Cancel and Logout
    </a>
</div>

<script>
// Auto-format code input
$('#code').on('input', function() {
    this.value = this.value.replace(/[^0-9]/g, '').slice(0, 6);
});
</script>
<?= $this->endSection() ?>
