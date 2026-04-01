<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0"><i class="bi bi-database me-2"></i>Backup Management</h5>
                <button type="button" class="btn btn-primary btn-sm" onclick="createBackup()">
                    <i class="bi bi-plus-circle"></i> Create Backup
                </button>
            </div>
            <div class="card-body">
                <div class="alert alert-info">
                    <i class="bi bi-info-circle"></i> 
                    <strong>Backup Information:</strong> Database backups are stored in the <code>writable/backups/</code> directory. 
                    Regular backups help protect your data. You can restore from any backup listed below.
                </div>
                
                <div class="table-responsive">
                    <table class="table table-hover" id="backupsTable">
                        <thead>
                            <tr>
                                <th>Date</th>
                                <th>Backup Type</th>
                                <th>Filename</th>
                                <th>Size</th>
                                <th>Status</th>
                                <th>Actions</th>
                             </thead>
                        <tbody>
                            <?php foreach ($backups as $backup): ?>
                            <?php $isRestoreRow = stripos((string) ($backup['message'] ?? ''), 'restored successfully') !== false; ?>
                             <tr>
                                 <td><?= date('Y-m-d H:i:s', strtotime($backup['created_at'])) ?></td>
                                 <td>
                                    <?php if ($isRestoreRow): ?>
                                        <span class="badge bg-secondary">Restore</span>
                                    <?php elseif ($backup['backup_type'] == 'Manual'): ?>
                                        <span class="badge bg-primary">Manual</span>
                                    <?php elseif ($backup['backup_type'] == 'Automatic'): ?>
                                        <span class="badge bg-info">Automatic</span>
                                    <?php else: ?>
                                        <span class="badge bg-secondary">Restore</span>
                                    <?php endif; ?>
                                 </td>
                                 <td><code><?= $backup['backup_file'] ?></code></td>
                                 <td><?= $backup['backup_size'] ? number_format($backup['backup_size'] / 1024, 2) . ' KB' : '-' ?></td>
                                 <td>
                                    <?php if ($backup['status'] == 'Success'): ?>
                                        <span class="badge bg-success">Success</span>
                                    <?php else: ?>
                                        <span class="badge bg-danger">Failed</span>
                                    <?php endif; ?>
                                 </td>
                                 <td>
                                    <?php if (!$isRestoreRow): ?>
                                        <a href="<?= base_url('admin/backup/download/' . rawurlencode($backup['backup_file'])) ?>" class="btn btn-sm btn-outline-primary">
                                            <i class="bi bi-download"></i> Download
                                        </a>
                                        <button class="btn btn-sm btn-warning" onclick="restoreBackup('<?= $backup['backup_file'] ?>')">
                                            <i class="bi bi-arrow-repeat"></i> Restore
                                        </button>
                                        <button class="btn btn-sm btn-danger" onclick="deleteBackup('<?= $backup['backup_file'] ?>')">
                                            <i class="bi bi-trash"></i> Delete
                                        </button>
                                    <?php endif; ?>
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

<script>
$(document).ready(function() {
    $('#backupsTable').DataTable({
        order: [[0, 'desc']],
        pageLength: 25
    });
});

function createBackup() {
    Swal.fire({
        title: 'Create Backup?',
        text: 'This will create a full database backup. This may take a few moments.',
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#d33',
        confirmButtonText: 'Yes, create backup!'
    }).then((result) => {
        if (result.isConfirmed) {
            Swal.fire({
                title: 'Creating Backup...',
                text: 'Please wait while the backup is being created.',
                allowOutsideClick: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            });
            
            $.ajax({
                url: '<?= base_url('admin/backup/create') ?>',
                type: 'POST',
                dataType: 'json',
                data: {type: 'Manual', <?= csrf_token() ?>: '<?= csrf_hash() ?>'},
                success: function(response) {
                    if (response.status === 'success') {
                        Swal.fire('Success!', response.message, 'success');
                        setTimeout(() => location.reload(), 1500);
                    } else {
                        Swal.fire('Error!', response.message, 'error');
                    }
                },
                error: function() {
                    Swal.fire('Error!', 'Failed to create backup', 'error');
                }
            });
        }
    });
}

function restoreBackup(filename) {
    Swal.fire({
        title: 'Restore Backup?',
        text: 'This will overwrite all current data with the backup data. This action cannot be undone!',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#3085d6',
        confirmButtonText: 'Yes, restore!'
    }).then((result) => {
        if (result.isConfirmed) {
            Swal.fire({
                title: 'Restoring Backup...',
                text: 'Please wait while the database is being restored.',
                allowOutsideClick: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            });
            
            $.ajax({
                url: '<?= base_url('admin/backup/restore') ?>/' + encodeURIComponent(filename),
                type: 'POST',
                dataType: 'json',
                data: {<?= csrf_token() ?>: '<?= csrf_hash() ?>'},
                success: function(response) {
                    if (response.status === 'success') {
                        Swal.fire('Success!', response.message, 'success');
                        setTimeout(() => location.reload(), 2000);
                    } else {
                        Swal.fire('Error!', response.message, 'error');
                    }
                },
                error: function() {
                    Swal.fire('Error!', 'Failed to restore backup', 'error');
                }
            });
        }
    });
}

function deleteBackup(filename) {
    Swal.fire({
        title: 'Delete Backup?',
        text: 'This will permanently delete the backup file. This action cannot be undone.',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#3085d6',
        confirmButtonText: 'Yes, delete!'
    }).then((result) => {
        if (result.isConfirmed) {
            $.ajax({
                url: '<?= base_url('admin/backup/delete') ?>/' + encodeURIComponent(filename),
                type: 'POST',
                dataType: 'json',
                data: {<?= csrf_token() ?>: '<?= csrf_hash() ?>'},
                success: function(response) {
                    if (response.status === 'success') {
                        Swal.fire('Success!', response.message, 'success');
                        setTimeout(() => location.reload(), 1500);
                    } else {
                        Swal.fire('Error!', response.message, 'error');
                    }
                },
                error: function() {
                    Swal.fire('Error!', 'Failed to delete backup', 'error');
                }
            });
        }
    });
}
</script>
<?= $this->endSection() ?>
