<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0"><i class="bi bi-bell me-2"></i>All Notifications</h5>
                <button class="btn btn-sm btn-primary" onclick="markAllRead()">
                    <i class="bi bi-check-all"></i> Mark All as Read
                </button>
            </div>
            <div class="card-body">
                <?php if (empty($notifications)): ?>
                    <div class="text-center py-5">
                        <i class="bi bi-bell-slash" style="font-size: 3rem;"></i>
                        <p class="mt-2 text-muted">No notifications</p>
                    </div>
                <?php else: ?>
                    <div class="list-group">
                        <?php foreach ($notifications as $notif): ?>
                        <div class="list-group-item <?= $notif['is_read'] ? '' : 'list-group-item-primary' ?>">
                            <div class="d-flex justify-content-between align-items-start">
                                <div class="flex-grow-1">
                                    <h6 class="mb-1"><?= $notif['title'] ?></h6>
                                    <p class="mb-1"><?= $notif['message'] ?></p>
                                    <small class="text-muted"><?= date('Y-m-d H:i', strtotime($notif['created_at'])) ?></small>
                                </div>
                                <?php if (!$notif['is_read']): ?>
                                    <button class="btn btn-sm btn-link" onclick="markRead(<?= $notif['id'] ?>)">
                                        Mark as read
                                    </button>
                                <?php endif; ?>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<script>
function markRead(id) {
    $.ajax({
        url: '<?= base_url('notifications/mark-read') ?>',
        type: 'POST',
        data: {id: id, <?= csrf_token() ?>: '<?= csrf_hash() ?>'},
        success: function(response) {
            if (response.status === 'success') {
                location.reload();
            }
        }
    });
}

function markAllRead() {
    // Implement mark all as read
    location.reload();
}
</script>
<?= $this->endSection() ?>