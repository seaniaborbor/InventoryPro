<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0"><i class="bi bi-journal me-2"></i>Audit Logs</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover" id="auditLogsTable">
                        <thead>
                            <tr>
                                <th>Timestamp</th>
                                <th>User</th>
                                <th>Action</th>
                                <th>Entity</th>
                                <th>Entity ID</th>
                                <th>IP Address</th>
                                <th>Details</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($logs as $log): ?>
                            <tr>
                                <td><?= date('Y-m-d H:i:s', strtotime($log['created_at'])) ?></td>
                                <td>
                                    <?php if ($log['user_id']): ?>
                                        <?= $log['user_id'] ?>
                                    <?php else: ?>
                                        <span class="text-muted">System</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <span class="badge bg-info"><?= $log['action'] ?></span>
                                </td>
                                <td><?= $log['entity'] ?></td>
                                <td><?= $log['entity_id'] ?? '-' ?></td>
                                <td><?= $log['ip_address'] ?></td>
                                <td>
                                    <button class="btn btn-sm btn-outline-info" onclick="viewDetails(<?= $log['id'] ?>)">
                                        <i class="bi bi-eye"></i> View
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

<!-- Details Modal -->
<div class="modal fade" id="detailsModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Log Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <pre id="logDetails" style="white-space: pre-wrap; word-wrap: break-word;"></pre>
            </div>
        </div>
    </div>
</div>

<script>
$(document).ready(function() {
    $('#auditLogsTable').DataTable({
        order: [[0, 'desc']],
        pageLength: 25,
        orderCellsTop: true,
        columnDefs: [
            { width: '150px', targets: 0 },
            { width: '100px', targets: 1 }
        ]
    });
});

function viewDetails(id) {
    // Find the log data
    <?php foreach ($logs as $log): ?>
    if (id == <?= $log['id'] ?>) {
        var details = {
            'Action': '<?= $log['action'] ?>',
            'Entity': '<?= $log['entity'] ?>',
            'Entity ID': '<?= $log['entity_id'] ?>',
            'User ID': '<?= $log['user_id'] ?>',
            'IP Address': '<?= $log['ip_address'] ?>',
            'User Agent': '<?= addslashes($log['user_agent']) ?>',
            'Timestamp': '<?= $log['created_at'] ?>',
            'Old Data': <?= $log['old_data'] ?: 'null' ?>,
            'New Data': <?= $log['new_data'] ?: 'null' ?>
        };
        $('#logDetails').text(JSON.stringify(details, null, 2));
        $('#detailsModal').modal('show');
    }
    <?php endforeach; ?>
}
</script>
<?= $this->endSection() ?>