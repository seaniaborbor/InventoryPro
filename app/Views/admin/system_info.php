<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<div class="row">
    <div class="col-md-6">
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0"><i class="bi bi-info-circle me-2"></i>System Information</h5>
            </div>
            <div class="card-body">
                <table class="table table-bordered">
                    <tr>
                        <th width="40%">PHP Version</th>
                        <td><?= $php_version ?></td>
                    </tr>
                    <tr>
                        <th>CodeIgniter Version</th>
                        <td><?= $ci_version ?></td>
                    </tr>
                    <tr>
                        <th>MySQL Version</th>
                        <td><?= $mysql_version ?></td>
                    </tr>
                    <tr>
                        <th>Server Software</th>
                        <td><?= $server_software ?></td>
                    </tr>
                    <tr>
                        <th>Environment</th>
                        <td>
                            <?php if (ENVIRONMENT === 'production'): ?>
                                <span class="badge bg-success">Production</span>
                            <?php else: ?>
                                <span class="badge bg-warning">Development</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                </table>
            </div>
        </div>
    </div>
    
    <div class="col-md-6">
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0"><i class="bi bi-sliders2 me-2"></i>PHP Configuration</h5>
            </div>
            <div class="card-body">
                <table class="table table-bordered">
                    <tr>
                        <th width="40%">Upload Max Filesize</th>
                        <td><?= $upload_max_filesize ?></td>
                    </tr>
                    <tr>
                        <th>Post Max Size</th>
                        <td><?= $post_max_size ?></td>
                    </tr>
                    <tr>
                        <th>Max Execution Time</th>
                        <td><?= $max_execution_time ?> seconds</td>
                    </tr>
                    <tr>
                        <th>Memory Limit</th>
                        <td><?= $memory_limit ?></td>
                    </tr>
                </table>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0"><i class="bi bi-folder me-2"></i>Directory Permissions</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered">
                        <thead>
                            <tr><th>Directory</th><th>Status</th><th>Required</th> </thead>
                        <tbody>
                            <tr>
                                <td><code>writable/</code></td>
                                <td>
                                    <?php if (is_writable(WRITEPATH)): ?>
                                        <span class="badge bg-success">Writable</span>
                                    <?php else: ?>
                                        <span class="badge bg-danger">Not Writable</span>
                                    <?php endif; ?>
                                </td>
                                <td>Required for logs, cache, and session</td>
                            </tr>
                            <tr>
                                <td><code>writable/backups/</code></td>
                                <td>
                                    <?php if (is_writable(WRITEPATH . 'backups/')): ?>
                                        <span class="badge bg-success">Writable</span>
                                    <?php else: ?>
                                        <span class="badge bg-danger">Not Writable</span>
                                    <?php endif; ?>
                                </td>
                                <td>Required for database backups</td>
                            </tr>
                            <tr>
                                <td><code>public/uploads/</code></td>
                                <td>
                                    <?php if (is_writable(FCPATH . 'uploads/')): ?>
                                        <span class="badge bg-success">Writable</span>
                                    <?php else: ?>
                                        <span class="badge bg-danger">Not Writable</span>
                                    <?php endif; ?>
                                </td>
                                <td>Required for product images and receipts</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row mt-4">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0"><i class="bi bi-database me-2"></i>Database Information</h5>
            </div>
            <div class="card-body">
                <?php
                $db = \Config\Database::connect();
                $tables = $db->listTables();
                $totalSize = 0;
                ?>
                <div class="table-responsive">
                    <table class="table table-sm">
                        <thead>
                            <tr><th>Table Name</th><th>Records</th><th>Size</th> </thead>
                        <tbody>
                            <?php foreach ($tables as $table): ?>
                            <?php
                            $result = $db->query("SHOW TABLE STATUS LIKE '$table'")->getRow();
                            $records = $result->Rows ?? 0;
                            $size = ($result->Data_length + $result->Index_length) / 1024;
                            $totalSize += $size;
                            ?>
                            <tr>
                                <td><code><?= $table ?></code></td>
                                <td><?= number_format($records) ?></td>
                                <td><?= number_format($size, 2) ?> KB</td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                        <tfoot class="table-active">
                            <tr>
                                <td><strong>Total</strong></td>
                                <td><strong><?= number_format(array_sum(array_column($tables, 'records') ?? [])) ?></strong></td>
                                <td><strong><?= number_format($totalSize, 2) ?> KB</strong></td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
<?= $this->endSection() ?>