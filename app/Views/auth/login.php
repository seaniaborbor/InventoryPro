<?= $this->extend('layouts/login') ?>

<?= $this->section('content') ?>
<form action="<?= base_url('auth/authenticate') ?>" method="post">
    <?= csrf_field() ?>
    
    <div class="mb-3">
        <label for="username" class="form-label">Username or Email</label>
        <div class="input-group">
            <span class="input-group-text"><i class="bi bi-person"></i></span>
            <input type="text" class="form-control" id="username" name="username" 
                   value="<?= old('username') ?>" required autofocus>
        </div>
    </div>
    
    <div class="mb-3">
        <label for="password" class="form-label">Password</label>
        <div class="input-group">
            <span class="input-group-text"><i class="bi bi-lock"></i></span>
            <input type="password" class="form-control" id="password" name="password" required>
            <button class="btn btn-outline-secondary" type="button" id="togglePassword">
                <i class="bi bi-eye"></i>
            </button>
        </div>
    </div>
    
    <div class="mb-3 form-check">
        <input type="checkbox" class="form-check-input" id="remember" name="remember" value="1" <?= old('remember') ? 'checked' : '' ?>>
        <label class="form-check-label" for="remember">Remember me</label>
    </div>
    
    <button type="submit" class="btn btn-login">
        <i class="bi bi-box-arrow-in-right me-2"></i>Login
    </button>
</form>

<div class="mt-3 text-center">
    <a href="<?= base_url('auth/forgot-password') ?>" class="text-decoration-none">Forgot Password?</a>
</div>



<script>
$('#togglePassword').click(function() {
    var passwordInput = $('#password');
    var icon = $(this).find('i');
    
    if (passwordInput.attr('type') === 'password') {
        passwordInput.attr('type', 'text');
        icon.removeClass('bi-eye').addClass('bi-eye-slash');
    } else {
        passwordInput.attr('type', 'password');
        icon.removeClass('bi-eye-slash').addClass('bi-eye');
    }
});
</script>
<?= $this->endSection() ?>
