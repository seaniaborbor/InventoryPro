<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<div class="row">
    <div class="col-md-8 mx-auto">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0"><i class="bi bi-pencil-square me-2"></i>Edit User: <?= esc($user['full_name']) ?></h5>
                <a href="<?= base_url('admin/users') ?>" class="btn btn-secondary btn-sm">
                    <i class="bi bi-arrow-left"></i> Back to Users
                </a>
            </div>
            <div class="card-body">
                <form id="editUserForm">
                    <?= csrf_field() ?>
                    <input type="hidden" name="id" value="<?= $user['id'] ?>">
                    <input type="hidden" name="is_self_edit" id="is_self_edit" value="<?= session()->get('user_id') == $user['id'] ? '1' : '0' ?>">
                    
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="full_name" class="form-label">Full Name *</label>
                            <input type="text" class="form-control" id="full_name" name="full_name" 
                                   value="<?= esc($user['full_name']) ?>" required>
                        </div>
                        <div class="col-md-6">
                            <label for="username" class="form-label">Username *</label>
                            <input type="text" class="form-control" id="username" name="username" 
                                   value="<?= esc($user['username']) ?>" required>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="email" class="form-label">Email *</label>
                            <input type="email" class="form-control" id="email" name="email" 
                                   value="<?= esc($user['email']) ?>" required>
                        </div>
                        <div class="col-md-6">
                            <label for="phone" class="form-label">Phone</label>
                            <input type="text" class="form-control" id="phone" name="phone" 
                                   value="<?= esc($user['phone']) ?>">
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="role_id" class="form-label">Role *</label>
                            <select class="form-select" id="role_id" name="role_id" required>
                                <option value="">Select Role</option>
                                <?php foreach ($roles as $role): ?>
                                    <option value="<?= $role['id'] ?>" 
                                        <?= $user['role_id'] == $role['id'] ? 'selected' : '' ?>
                                        <?= (session()->get('user_id') == $user['id'] && $role['role_name'] === 'Admin' && session()->get('role_id') != $role['id']) ? 'disabled' : '' ?>>
                                        <?= esc($role['role_name']) ?>
                                        <?= $role['role_name'] === 'Admin' ? ' (Full Access)' : '' ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <small class="text-muted">Users cannot change their own role to a higher level</small>
                        </div>
                        <div class="col-md-6">
                            <label for="is_active" class="form-label">Status</label>
                            <select class="form-select" id="is_active" name="is_active">
                                <option value="1" <?= $user['is_active'] == 1 ? 'selected' : '' ?>>Active</option>
                                <option value="0" <?= $user['is_active'] == 0 ? 'selected' : '' ?>>Inactive</option>
                            </select>
                            <small class="text-muted">Inactive users cannot log in</small>
                        </div>
                    </div>

                    <!-- Password Change Section -->
                    <div class="card bg-light mb-3">
                        <div class="card-header">
                            <h6 class="mb-0"><i class="bi bi-key me-2"></i>Change Password</h6>
                        </div>
                        <div class="card-body">
                            <div class="alert alert-info small">
                                <i class="bi bi-info-circle"></i> 
                                <?php if (session()->get('user_id') == $user['id']): ?>
                                    To change your password, enter your current password and then your new password.
                                <?php else: ?>
                                    As an administrator, you can change this user's password without knowing their current password.
                                <?php endif; ?>
                            </div>
                            
                            <!-- Old Password Field - Only for self edit -->
                            <?php if (session()->get('user_id') == $user['id']): ?>
                            <div class="row mb-3">
                                <div class="col-md-12">
                                    <label for="old_password" class="form-label">Current Password *</label>
                                    <input type="password" class="form-control" id="old_password" name="old_password">
                                    <small class="text-muted">Required to change your own password</small>
                                </div>
                            </div>
                            <?php endif; ?>
                            
                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label for="password" class="form-label">New Password</label>
                                    <input type="password" class="form-control" id="password" name="password">
                                    <small class="text-muted">Minimum 6 characters. Leave blank to keep current.</small>
                                </div>
                                <div class="col-md-6">
                                    <label for="confirm_password" class="form-label">Confirm Password</label>
                                    <input type="password" class="form-control" id="confirm_password" name="confirm_password">
                                </div>
                            </div>
                            
                            <!-- Password strength indicator -->
                            <div id="passwordStrength" class="mt-2" style="display: none;">
                                <div class="progress" style="height: 5px;">
                                    <div id="strengthBar" class="progress-bar" role="progressbar" style="width: 0%"></div>
                                </div>
                                <small id="strengthText" class="text-muted"></small>
                            </div>
                        </div>
                    </div>

                    <!-- User Info Display -->
                    <div class="card bg-light mb-3">
                        <div class="card-header">
                            <h6 class="mb-0"><i class="bi bi-info-circle me-2"></i>Account Information</h6>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-4">
                                    <small class="text-muted">Created:</small><br>
                                    <?= date('M j, Y H:i', strtotime($user['created_at'])) ?>
                                </div>
                                <div class="col-md-4">
                                    <small class="text-muted">Last Login:</small><br>
                                    <?= $user['last_login'] ? date('M j, Y H:i', strtotime($user['last_login'])) : 'Never' ?>
                                </div>
                                <div class="col-md-4">
                                    <small class="text-muted">Last IP:</small><br>
                                    <?= $user['last_ip'] ?? 'Not recorded' ?>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="d-flex justify-content-between">
                        <?php if (session()->get('user_id') != $user['id']): ?>
                            <button type="button" class="btn btn-danger" onclick="deleteUser(<?= $user['id'] ?>)">
                                <i class="bi bi-trash"></i> Delete User
                            </button>
                        <?php else: ?>
                            <div></div>
                        <?php endif; ?>
                        <div>
                            <a href="<?= base_url('admin/users') ?>" class="btn btn-secondary">Cancel</a>
                            <button type="submit" class="btn btn-primary" id="submitBtn">
                                <i class="bi bi-save"></i> Save Changes
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
$(document).ready(function() {
    const isSelfEdit = $('#is_self_edit').val() === '1';
    
    // Password strength checker
    $('#password, #confirm_password').on('keyup', function() {
        const password = $('#password').val();
        const confirm = $('#confirm_password').val();
        
        if (password.length > 0) {
            $('#passwordStrength').show();
            
            let strength = 0;
            if (password.length >= 6) strength++;
            if (password.length >= 8) strength++;
            if (/[A-Z]/.test(password)) strength++;
            if (/[0-9]/.test(password)) strength++;
            if (/[^A-Za-z0-9]/.test(password)) strength++;
            
            let percentage = (strength / 5) * 100;
            let color = '#dc3545';
            let text = 'Weak';
            
            if (strength >= 4) {
                color = '#28a745';
                text = 'Strong';
            } else if (strength >= 2) {
                color = '#ffc107';
                text = 'Medium';
            }
            
            $('#strengthBar').css('width', percentage + '%').css('background-color', color);
            $('#strengthText').text(text + ' password');
            
            if (confirm.length > 0 && password !== confirm) {
                $('#confirm_password').addClass('is-invalid');
                $('#strengthText').append(' <span class="text-danger">| Passwords do not match</span>');
            } else {
                $('#confirm_password').removeClass('is-invalid');
            }
        } else {
            $('#passwordStrength').hide();
        }
    });
    
    // Form submission
    $('#editUserForm').on('submit', function(e) {
        e.preventDefault();
        
        const password = $('#password').val();
        const confirm = $('#confirm_password').val();
        const oldPassword = $('#old_password').val();
        
        // Validate for self edit
        if (isSelfEdit && password.length > 0 && !oldPassword) {
            Swal.fire('Error', 'Current password is required to change your password!', 'error');
            return;
        }
        
        if (password !== confirm) {
            Swal.fire('Error', 'New passwords do not match!', 'error');
            return;
        }
        
        if (password.length > 0 && password.length < 6) {
            Swal.fire('Error', 'Password must be at least 6 characters!', 'error');
            return;
        }
        
        const formData = {
            id: $('input[name="id"]').val(),
            full_name: $('#full_name').val().trim(),
            username: $('#username').val().trim(),
            email: $('#email').val().trim(),
            phone: $('#phone').val().trim(),
            role_id: $('#role_id').val(),
            is_active: $('#is_active').val(),
            password: password,
            old_password: oldPassword
        };
        
        const submitBtn = $('#submitBtn');
        submitBtn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-2"></span>Saving...');
        
        $.ajax({
            url: '<?= base_url('admin/users/update/' . $user['id']) ?>',
            type: 'POST',
            data: formData,
            dataType: 'json',
            success: function(response) {
                submitBtn.prop('disabled', false).html('<i class="bi bi-save"></i> Save Changes');
                
                if (response.status === 'success') {
                    Swal.fire({
                        icon: 'success',
                        title: 'Success!',
                        text: response.message,
                        timer: 1500,
                        showConfirmButton: false
                    });
                    setTimeout(() => {
                        window.location.href = '<?= base_url('admin/users') ?>';
                    }, 1500);
                } else {
                    let errorMsg = response.message || 'Failed to update user';
                    if (response.errors) {
                        errorMsg = Object.values(response.errors).join('<br>');
                    }
                    Swal.fire('Error', errorMsg, 'error');
                }
            },
            error: function(xhr) {
                submitBtn.prop('disabled', false).html('<i class="bi bi-save"></i> Save Changes');
                let errorMsg = 'An error occurred. Please try again.';
                if (xhr.responseJSON && xhr.responseJSON.message) {
                    errorMsg = xhr.responseJSON.message;
                }
                Swal.fire('Error', errorMsg, 'error');
            }
        });
    });
});

function deleteUser(id) {
    Swal.fire({
        title: 'Delete User?',
        text: 'This action cannot be undone. The user will be permanently deleted.',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#3085d6',
        confirmButtonText: 'Yes, delete user'
    }).then((result) => {
        if (result.isConfirmed) {
            $.ajax({
                url: '<?= base_url('admin/users/delete/') ?>' + id,
                type: 'POST',
                data: { '<?= csrf_token() ?>': '<?= csrf_hash() ?>' },
                success: function(response) {
                    if (response.status === 'success') {
                        Swal.fire('Deleted!', response.message, 'success');
                        setTimeout(() => {
                            window.location.href = '<?= base_url('admin/users') ?>';
                        }, 1500);
                    } else {
                        Swal.fire('Error', response.message, 'error');
                    }
                },
                error: function() {
                    Swal.fire('Error', 'Failed to delete user', 'error');
                }
            });
        }
    });
}
</script>
<?= $this->endSection() ?>