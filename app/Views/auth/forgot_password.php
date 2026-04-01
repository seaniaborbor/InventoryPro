<?= $this->extend('layouts/login') ?>

<?= $this->section('content') ?>
<form action="<?= base_url('auth/send-reset-link') ?>" method="post">
    <?= csrf_field() ?>
    
    <div class="mb-4">
        <p class="text-muted">Enter your email address and we'll send you a link to reset your password.</p>
    </div>
    
    <div class="mb-3">
        <label for="email" class="form-label">Email Address</label>
        <div class="input-group">
            <span class="input-group-text"><i class="bi bi-envelope"></i></span>
            <input type="email" class="form-control" id="email" name="email" 
                   value="<?= old('email') ?>" required autofocus>
        </div>
    </div>
    
    <button type="submit" class="btn btn-login">
        <i class="bi bi-send me-2"></i>Send Reset Link
    </button>
</form>

<div class="mt-3 text-center">
    <a href="<?= base_url('auth/login') ?>" class="text-decoration-none">
        <i class="bi bi-arrow-left"></i> Back to Login
    </a>
</div>
<?= $this->endSection() ?>