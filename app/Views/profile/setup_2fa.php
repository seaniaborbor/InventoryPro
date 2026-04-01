<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<div class="row justify-content-center">
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0"><i class="bi bi-shield-lock me-2"></i>Setup Two-Factor Authentication</h5>
            </div>
            <div class="card-body text-center">
                <div class="mb-4">
                    <i class="bi bi-shield-shield" style="font-size: 4rem; color: #667eea;"></i>
                    <h4 class="mt-2">Enhanced Security</h4>
                    <p class="text-muted">Protect your account with two-factor authentication</p>
                </div>
                
                <div class="alert alert-info">
                    <i class="bi bi-info-circle"></i> Scan the QR code with your authenticator app (Google Authenticator, Authy, etc.)
                </div>
                
                <div class="mb-4">
                    <img src="<?= $qrCodeUrl ?>" alt="QR Code" class="img-fluid border p-3 rounded" style="max-width: 200px;">
                    <div class="form-text mt-2">If the QR image does not load, add the account manually using the secret key below.</div>
                </div>
                
                <div class="mb-3">
                    <label class="form-label">Secret Key (if QR scan fails)</label>
                    <div class="input-group">
                        <input type="text" class="form-control text-center" id="totpSecret" value="<?= $user['two_factor_secret'] ?>" readonly>
                        <button class="btn btn-outline-secondary" type="button" onclick="copySecret()">
                            <i class="bi bi-copy"></i>
                        </button>
                    </div>
                </div>
                
                <form action="<?= base_url('profile/enable-2fa') ?>" method="post">
                    <?= csrf_field() ?>
                    
                    <div class="mb-3">
                        <label for="code" class="form-label">Enter Verification Code</label>
                        <input type="text" class="form-control text-center" id="code" name="code" 
                               placeholder="000000" maxlength="6" required>
                    </div>
                    
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-check-circle"></i> Verify and Enable
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
function copySecret() {
    var secret = document.getElementById('totpSecret');
    secret.select();
    secret.setSelectionRange(0, 99999);

    navigator.clipboard.writeText(secret.value).then(function() {
        Swal.fire('Copied', 'Secret key copied to clipboard', 'success');
    }).catch(function() {
        document.execCommand('copy');
        Swal.fire('Copied', 'Secret key copied to clipboard', 'success');
    });
}

$('#code').on('input', function() {
    this.value = this.value.replace(/[^0-9]/g, '').slice(0, 6);
});
</script>
<?= $this->endSection() ?>
