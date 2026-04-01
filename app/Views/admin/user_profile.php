<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<?php
$logPayload = [];

foreach ($activityLogs as $log) {
    $logPayload[] = [
        'id' => $log['id'],
        'user_id' => $log['user_id'] ?? null,
        'action' => $log['action'],
        'entity' => $log['entity'],
        'entity_id' => $log['entity_id'],
        'actor_name' => $log['actor_name'] ?? null,
        'actor_username' => $log['actor_username'] ?? null,
        'ip_address' => $log['ip_address'] ?? null,
        'user_agent' => $log['user_agent'] ?? null,
        'created_at' => $log['created_at'],
        'old_data' => $log['old_data'],
        'new_data' => $log['new_data'],
    ];
}
?>

<div class="row g-4">
    <div class="col-lg-4">
        <div class="card h-100">
            <div class="card-body">
                <div class="d-flex align-items-center gap-3 mb-4">
                    <div class="rounded-circle bg-primary text-white d-flex align-items-center justify-content-center" style="width: 64px; height: 64px; font-size: 1.5rem;">
                        <?= esc(strtoupper(substr($user['full_name'] ?: $user['username'], 0, 1))) ?>
                    </div>
                    <div>
                        <h4 class="mb-1"><?= esc($user['full_name']) ?></h4>
                        <div class="text-muted">@<?= esc($user['username']) ?></div>
                    </div>
                </div>

                <div class="d-grid gap-2 mb-4">
                    <a href="<?= base_url('admin/users/edit/' . $user['id']) ?>" class="btn btn-primary">
                        <i class="bi bi-pencil-square me-2"></i>Edit User
                    </a>
                    <a href="<?= base_url('admin/users') ?>" class="btn btn-outline-secondary">
                        <i class="bi bi-arrow-left me-2"></i>Back to Users
                    </a>
                </div>

                <div class="border rounded p-3 bg-light">
                    <div class="row gy-3">
                        <div class="col-12">
                            <div class="small text-muted text-uppercase">Role</div>
                            <div>
                                <span class="badge <?= ($user['role_name'] ?? '') === 'Admin' ? 'bg-danger' : ((($user['role_name'] ?? '') === 'Manager') ? 'bg-warning text-dark' : 'bg-info text-dark') ?>">
                                    <?= esc($user['role_name'] ?? 'No Role') ?>
                                </span>
                            </div>
                        </div>
                        <div class="col-12">
                            <div class="small text-muted text-uppercase">Status</div>
                            <div>
                                <span class="badge <?= !empty($user['is_active']) ? 'bg-success' : 'bg-secondary' ?>">
                                    <?= !empty($user['is_active']) ? 'Active' : 'Inactive' ?>
                                </span>
                            </div>
                        </div>
                        <div class="col-12">
                            <div class="small text-muted text-uppercase">Email</div>
                            <div><?= esc($user['email'] ?: 'N/A') ?></div>
                        </div>
                        <div class="col-12">
                            <div class="small text-muted text-uppercase">Phone</div>
                            <div><?= esc($user['phone'] ?: 'N/A') ?></div>
                        </div>
                        <div class="col-12">
                            <div class="small text-muted text-uppercase">Last Login</div>
                            <div><?= !empty($user['last_login']) ? esc(date('Y-m-d H:i', strtotime($user['last_login']))) : 'Never' ?></div>
                        </div>
                        <div class="col-12">
                            <div class="small text-muted text-uppercase">Created</div>
                            <div><?= !empty($user['created_at']) ? esc(date('Y-m-d H:i', strtotime($user['created_at']))) : 'N/A' ?></div>
                        </div>
                        <div class="col-12">
                            <div class="small text-muted text-uppercase">Updated</div>
                            <div><?= !empty($user['updated_at']) ? esc(date('Y-m-d H:i', strtotime($user['updated_at']))) : 'N/A' ?></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-lg-8">
        <div class="card h-100">
            <div class="card-header d-flex justify-content-between align-items-center">
                <div>
                    <h5 class="mb-0"><i class="bi bi-clock-history me-2"></i>Activity Log</h5>
                    <small class="text-muted">Recent actions performed by or recorded against this user.</small>
                </div>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover align-middle" id="userActivityTable">
                        <thead>
                            <tr>
                                <th>Date</th>
                                <th>Action</th>
                                <th>Entity</th>
                                <th>Performed By</th>
                                <th>IP Address</th>
                                <th>Details</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($activityLogs as $log): ?>
                                <tr>
                                    <td><?= esc(date('Y-m-d H:i:s', strtotime($log['created_at']))) ?></td>
                                    <td>
                                        <span class="badge bg-light text-dark border">
                                            <?= esc(str_replace('_', ' ', ucwords($log['action'], '_'))) ?>
                                        </span>
                                    </td>
                                    <td>
                                        <div><?= esc($log['entity'] ?: 'N/A') ?></div>
                                        <small class="text-muted">ID: <?= esc($log['entity_id'] ?: '-') ?></small>
                                    </td>
                                    <td>
                                        <?php if (!empty($log['actor_name']) || !empty($log['actor_username'])): ?>
                                            <div><?= esc($log['actor_name'] ?: $log['actor_username']) ?></div>
                                            <?php if (!empty($log['actor_username'])): ?>
                                                <small class="text-muted">@<?= esc($log['actor_username']) ?></small>
                                            <?php endif; ?>
                                        <?php elseif (!empty($log['user_id'])): ?>
                                            <span class="text-muted">User #<?= esc($log['user_id']) ?></span>
                                        <?php else: ?>
                                            <span class="text-muted">System</span>
                                        <?php endif; ?>
                                    </td>
                                    <td><?= esc($log['ip_address'] ?: '-') ?></td>
                                    <td>
                                        <button type="button" class="btn btn-sm btn-outline-primary" onclick="viewLogDetails(<?= (int) $log['id'] ?>)">
                                            <i class="bi bi-eye me-1"></i>View
                                        </button>
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

<div class="modal fade" id="activityDetailsModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Activity Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="row g-3 mb-4">
                    <div class="col-md-6">
                        <div class="small text-muted text-uppercase">Action</div>
                        <div id="activityAction" class="fw-semibold"></div>
                    </div>
                    <div class="col-md-6">
                        <div class="small text-muted text-uppercase">When</div>
                        <div id="activityTimestamp" class="fw-semibold"></div>
                    </div>
                    <div class="col-md-6">
                        <div class="small text-muted text-uppercase">Entity</div>
                        <div id="activityEntity"></div>
                    </div>
                    <div class="col-md-6">
                        <div class="small text-muted text-uppercase">Performed By</div>
                        <div id="activityActor"></div>
                    </div>
                    <div class="col-md-6">
                        <div class="small text-muted text-uppercase">IP Address</div>
                        <div id="activityIp"></div>
                    </div>
                    <div class="col-md-6">
                        <div class="small text-muted text-uppercase">User Agent</div>
                        <div id="activityUserAgent" class="small text-break"></div>
                    </div>
                </div>

                <div class="row g-3">
                    <div class="col-lg-6">
                        <label class="form-label fw-semibold">Old Data</label>
                        <pre class="bg-light border rounded p-3 small mb-0" id="activityOldData" style="min-height: 180px; white-space: pre-wrap; word-break: break-word;"></pre>
                    </div>
                    <div class="col-lg-6">
                        <label class="form-label fw-semibold">New Data</label>
                        <pre class="bg-light border rounded p-3 small mb-0" id="activityNewData" style="min-height: 180px; white-space: pre-wrap; word-break: break-word;"></pre>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
const userActivityLogs = <?= json_encode($logPayload, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT) ?>;

function formatAuditPayload(payload) {
    if (!payload) {
        return 'No data recorded';
    }

    try {
        return JSON.stringify(JSON.parse(payload), null, 2);
    } catch (error) {
        return String(payload);
    }
}

function viewLogDetails(logId) {
    const log = userActivityLogs.find((item) => Number(item.id) === Number(logId));
    if (!log) {
        return;
    }

    const actorLabel = log.actor_name || log.actor_username
        ? `${log.actor_name || log.actor_username}${log.actor_username ? ' (@' + log.actor_username + ')' : ''}`
        : (log.user_id ? `User #${log.user_id}` : 'System');

    document.getElementById('activityAction').textContent = String(log.action || '').replace(/_/g, ' ');
    document.getElementById('activityTimestamp').textContent = log.created_at || '-';
    document.getElementById('activityEntity').textContent = `${log.entity || 'N/A'}${log.entity_id ? ' #' + log.entity_id : ''}`;
    document.getElementById('activityActor').textContent = actorLabel;
    document.getElementById('activityIp').textContent = log.ip_address || '-';
    document.getElementById('activityUserAgent').textContent = log.user_agent || '-';
    document.getElementById('activityOldData').textContent = formatAuditPayload(log.old_data);
    document.getElementById('activityNewData').textContent = formatAuditPayload(log.new_data);

    const modal = new bootstrap.Modal(document.getElementById('activityDetailsModal'));
    modal.show();
}

$(document).ready(function() {
    $('#userActivityTable').DataTable({
        order: [[0, 'desc']],
        pageLength: 20
    });
});
</script>
<?= $this->endSection() ?>
