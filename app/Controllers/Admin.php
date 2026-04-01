<?php

namespace App\Controllers;

use App\Models\UserModel;
use App\Models\RoleModel;
use App\Models\PermissionModel;
use App\Models\AuditLogModel;
use App\Models\SystemSettingModel;
use App\Models\BackupLogModel;

class Admin extends BaseController
{
    protected $userModel;
    protected $roleModel;
    protected $permissionModel;
    protected $auditLogModel;
    protected $settingsModel;
    protected $backupLogModel;
    
    public function __construct()
    {
        // Load permission helper first
        helper('permission');
        
        $this->userModel = new UserModel();
        $this->roleModel = new RoleModel();
        $this->permissionModel = new PermissionModel();
        $this->auditLogModel = new AuditLogModel();
        $this->settingsModel = new SystemSettingModel();
        $this->backupLogModel = new BackupLogModel();
        $this->permissionModel->syncSystemPermissions();
        
        // Check authentication and administration access
        if (!session()->get('isLoggedIn')) {
            return redirect()->to('/auth/login');
        }
        
        if (!can_access_module('admin')) {
            return redirect()->to('/dashboard')->with('error', 'Access denied. Administration privileges required.');
        }
    }
    
    // ... rest of the controller methods

    
    /**
     * User management index
     */
    public function users()
    {
        $data = [
            'title' => 'User Management',
            'users' => $this->userModel->getAllUsersWithRoles(),
            'roles' => $this->roleModel->findAll(),
            'activePage' => 'settings'
        ];
        
        return view('admin/users', $data);
    }

    /**
     * View user profile
     */
    public function viewUser($id)
    {
        $user = $this->userModel
            ->select('users.*, roles.role_name')
            ->join('roles', 'roles.id = users.role_id', 'left')
            ->where('users.id', $id)
            ->first();

        if (!$user) {
            return redirect()->to('/admin/users')->with('error', 'User not found');
        }

        $data = [
            'title' => 'User Profile',
            'user' => $user,
            'activityLogs' => $this->auditLogModel->getActivityForUserProfile($id, 150),
            'activePage' => 'settings'
        ];

        return view('admin/user_profile', $data);
    }
    
    /**
     * Create user
     */
    public function createUser()
    {
        $rules = [
            'username' => 'required|min_length[3]|max_length[100]|is_unique[users.username]',
            'email' => 'required|valid_email|is_unique[users.email]',
            'password' => 'required|min_length[6]',
            'full_name' => 'required|min_length[3]|max_length[255]',
            'phone' => 'permit_empty|max_length[50]',
            'role_id' => 'required|is_not_unique[roles.id]'
        ];
        
        if (!$this->validate($rules)) {
            if ($this->request->isAJAX()) {
                return $this->response->setJSON([
                    'status' => 'error',
                    'errors' => $this->validator->getErrors()
                ]);
            }
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }
        
        $data = [
            'username' => $this->request->getPost('username'),
            'email' => $this->request->getPost('email'),
            'password' => $this->request->getPost('password'),
            'full_name' => $this->request->getPost('full_name'),
            'phone' => $this->request->getPost('phone'),
            'role_id' => $this->request->getPost('role_id'),
            'is_active' => $this->request->getPost('is_active') ? 1 : 0,
            'created_by' => session()->get('user_id')
        ];
        
        if ($this->userModel->insert($data)) {
            $userId = $this->userModel->getInsertID();
            
            // Log audit
            $this->auditLogModel->log(
                session()->get('user_id'),
                'user_create',
                'User',
                $userId,
                null,
                $data
            );
            
            if ($this->request->isAJAX()) {
                return $this->response->setJSON(['status' => 'success', 'message' => 'User created successfully']);
            }
            return redirect()->to('/admin/users')->with('success', 'User created successfully');
        }
        
        if ($this->request->isAJAX()) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Failed to create user']);
        }
        return redirect()->back()->with('error', 'Failed to create user');
    }
    
    /**
     * Edit user
     */
    public function editUser($id)
    {
        $user = $this->userModel->find($id);
        
        if (!$user) {
            return redirect()->to('/admin/users')->with('error', 'User not found');
        }
        
        $data = [
            'title' => 'Edit User',
            'user' => $user,
            'roles' => $this->roleModel->findAll(),
            'activePage' => 'settings'
        ];
        
        return view('admin/edit_user', $data);
    }
    
    /**
     * Update user
     */
    public function updateUser($id)
    {
        $user = $this->userModel->find($id);
        
        if (!$user) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'User not found']);
        }
        
        $rules = [
            'username' => 'required|min_length[3]|max_length[100]|is_unique[users.username,id,' . $id . ']',
            'email' => 'required|valid_email|is_unique[users.email,id,' . $id . ']',
            'full_name' => 'required|min_length[3]|max_length[255]',
            'phone' => 'permit_empty|max_length[50]',
            'role_id' => 'required|is_not_unique[roles.id]'
        ];
        
        if (!$this->validate($rules)) {
            if ($this->request->isAJAX()) {
                return $this->response->setJSON([
                    'status' => 'error',
                    'errors' => $this->validator->getErrors()
                ]);
            }
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }
        
        $data = [
            'username' => $this->request->getPost('username'),
            'email' => $this->request->getPost('email'),
            'full_name' => $this->request->getPost('full_name'),
            'phone' => $this->request->getPost('phone'),
            'role_id' => $this->request->getPost('role_id'),
            'is_active' => $this->request->getPost('is_active') ? 1 : 0,
            'updated_by' => session()->get('user_id')
        ];
        
        // Update password if provided
        $password = $this->request->getPost('password');
        if (!empty($password)) {
            if (strlen($password) < 6) {
                return $this->response->setJSON(['status' => 'error', 'message' => 'Password must be at least 6 characters']);
            }
            $data['password'] = $password;
        }
        
        // Get old data for audit
        $oldData = $user;
        
        if ($this->userModel->update($id, $data)) {
            // Log audit
            $this->auditLogModel->log(
                session()->get('user_id'),
                'user_update',
                'User',
                $id,
                $oldData,
                $data
            );
            
            if ($this->request->isAJAX()) {
                return $this->response->setJSON(['status' => 'success', 'message' => 'User updated successfully']);
            }
            return redirect()->to('/admin/users')->with('success', 'User updated successfully');
        }
        
        if ($this->request->isAJAX()) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Failed to update user']);
        }
        return redirect()->back()->with('error', 'Failed to update user');
    }
    
    /**
     * Delete user
     */
    public function deleteUser($id)
    {
        // Prevent deleting own account
        if ($id == session()->get('user_id')) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'You cannot delete your own account']);
        }
        
        $user = $this->userModel->find($id);
        
        if (!$user) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'User not found']);
        }
        
        $oldData = $user;
        
        if ($this->userModel->delete($id)) {
            // Log audit
            $this->auditLogModel->log(
                session()->get('user_id'),
                'user_delete',
                'User',
                $id,
                $oldData
            );
            
            return $this->response->setJSON(['status' => 'success', 'message' => 'User deleted successfully']);
        }
        
        return $this->response->setJSON(['status' => 'error', 'message' => 'Failed to delete user']);
    }
    
    /**
     * Role management
     */
    public function roles()
    {
        $data = [
            'title' => 'Role Management',
            'roles' => $this->roleModel->getAllRolesWithPermissionCount(),
            'activePage' => 'settings'
        ];
        
        return view('admin/roles', $data);
    }

    /**
     * Permission matrix for all roles.
     */
    public function permissionMatrix()
    {
        $roles = $this->roleModel->findAll();
        $matrixData = $this->permissionModel->getRolePermissionMatrix($roles);

        $data = [
            'title' => 'Role Permissions',
            'roles' => $roles,
            'groupedPermissions' => $matrixData['groupedPermissions'],
            'rolePermissionMap' => $matrixData['rolePermissionMap'],
            'activePage' => 'settings',
        ];

        return view('admin/permission_matrix', $data);
    }

    /**
     * Save permission matrix.
     */
    public function updatePermissionMatrix()
    {
        $roles = $this->roleModel->findAll();
        $postedMatrix = $this->request->getPost('permissions') ?? [];
        $allPermissions = $this->permissionModel->findAll();
        $allPermissionIds = array_column($allPermissions, 'id');

        foreach ($roles as $role) {
            if (strcasecmp($role['role_name'], 'Admin') === 0) {
                $this->permissionModel->assignToRole($role['id'], $allPermissionIds);
                continue;
            }

            $rolePermissionIds = array_map('intval', $postedMatrix[$role['id']] ?? []);
            $this->permissionModel->assignToRole($role['id'], $rolePermissionIds);
        }

        $this->auditLogModel->log(
            session()->get('user_id'),
            'permission_matrix_update',
            'RolePermission',
            null,
            null,
            ['roles_updated' => array_column($roles, 'role_name')]
        );

        return redirect()->to('/admin/permissions')->with('success', 'Role permissions updated successfully');
    }

    /**
     * Edit role permissions
     */
    public function editRolePermissions($id)
    {
        $role = $this->roleModel->find($id);
        
        if (!$role) {
            return redirect()->to('/admin/roles')->with('error', 'Role not found');
        }
        
        $roles = [$role];
        $matrixData = $this->permissionModel->getRolePermissionMatrix($roles);
        $rolePermissionIds = array_keys($matrixData['rolePermissionMap'][$id] ?? []);

        $data = [
            'title' => 'Edit Role Permissions',
            'role' => $role,
            'groupedPermissions' => $matrixData['groupedPermissions'],
            'rolePermissionIds' => $rolePermissionIds,
            'activePage' => 'settings'
        ];
        
        return view('admin/role_permissions', $data);
    }
    
    /**
     * Update role permissions
     */
    public function updateRolePermissions($id)
    {
        $permissionIds = $this->request->getPost('permissions') ?? [];
        $role = $this->roleModel->find($id);

        if (!$role) {
            return redirect()->to('/admin/roles')->with('error', 'Role not found');
        }

        if (strcasecmp($role['role_name'], 'Admin') === 0) {
            $permissionIds = array_column($this->permissionModel->findAll(), 'id');
        }
        
        if ($this->permissionModel->assignToRole($id, $permissionIds)) {
            // Log audit
            $this->auditLogModel->log(
                session()->get('user_id'),
                'role_permissions_update',
                'Role',
                $id,
                null,
                ['permission_ids' => $permissionIds]
            );
            
            if ($this->request->isAJAX()) {
                return $this->response->setJSON(['status' => 'success', 'message' => 'Role permissions updated successfully']);
            }
            return redirect()->to('/admin/roles')->with('success', 'Role permissions updated successfully');
        }
        
        if ($this->request->isAJAX()) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Failed to update role permissions']);
        }
        return redirect()->back()->with('error', 'Failed to update role permissions');
    }
    
    /**
     * System settings
     */
    public function settings()
    {
        $settingsModel = new \App\Models\SystemSettingModel();
        
        $data = [
            'title' => 'System Settings',
            'settings' => $settingsModel->getAllSettings(),
            'activePage' => 'settings'
        ];
        
        return view('admin/settings', $data);
    }
    
    /**
     * Update system settings
     */
    public function updateSettings()
    {
        $settingsModel = new \App\Models\SystemSettingModel();
        
        $postData = $this->request->getPost();
        
        foreach ($postData as $key => $value) {
            if (strpos($key, 'setting_') === 0) {
                $settingKey = str_replace('setting_', '', $key);
                $settingsModel->set($settingKey, $value);
            }
        }
        
        // Handle logo upload
        $logo = $this->request->getFile('setting_business_logo');
        if ($logo && $logo->isValid() && !$logo->hasMoved()) {
            $newName = 'logo_' . time() . '.' . $logo->getExtension();
            $logo->move('uploads', $newName);
            $settingsModel->set('business_logo', 'uploads/' . $newName);
        }
        
        // Log audit
        $this->auditLogModel->log(
            session()->get('user_id'),
            'settings_update',
            'System',
            null,
            null,
            $postData
        );
        
        if ($this->request->isAJAX()) {
            return $this->response->setJSON(['status' => 'success', 'message' => 'Settings updated successfully']);
        }
        return redirect()->to('/admin/settings')->with('success', 'Settings updated successfully');
    }
    
    /**
     * View audit logs
     */
    public function auditLogs()
    {
        $data = [
            'title' => 'Audit Logs',
            'logs' => $this->auditLogModel->getRecent(100),
            'activePage' => 'settings'
        ];
        
        return view('admin/audit_logs', $data);
    }
    
    /**
     * Export audit logs
     */
    public function exportAuditLogs()
    {
        $startDate = $this->request->getGet('start_date');
        $endDate = $this->request->getGet('end_date');
        
        if ($startDate && $endDate) {
            $logs = $this->auditLogModel->getByDateRange($startDate, $endDate);
        } else {
            $logs = $this->auditLogModel->getRecent(1000);
        }
        
        // Prepare data for export
        $exportData = [];
        foreach ($logs as $log) {
            $exportData[] = [
                'Timestamp' => $log['created_at'],
                'User ID' => $log['user_id'],
                'Action' => $log['action'],
                'Entity' => $log['entity'],
                'Entity ID' => $log['entity_id'],
                'IP Address' => $log['ip_address'],
                'User Agent' => $log['user_agent'],
                'Old Data' => $log['old_data'],
                'New Data' => $log['new_data']
            ];
        }
        
        return $this->exportExcel($exportData, 'Audit Logs', 'audit_logs_' . date('Y-m-d') . '.xlsx');
    }
    
    /**
     * Backup management
     */
    public function backup()
    {
        $backups = $this->backupLogModel->orderBy('created_at', 'DESC')->findAll(50);
        
        $data = [
            'title' => 'Backup Management',
            'backups' => $backups,
            'activePage' => 'settings'
        ];
        
        return view('admin/backup', $data);
    }
    
    /**
     * Create backup
     */
    public function createBackup()
    {
        if (!$this->request->isAJAX()) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Invalid request']);
        }

        $backupType = $this->request->getPost('type') ?: 'Manual';

        try {
            $backupPath = $this->getBackupDirectory();
            $filename = 'backup_' . date('Y-m-d_H-i-s') . '.sql';
            $filepath = $backupPath . $filename;
            $sql = $this->buildDatabaseBackup();

            if (file_put_contents($filepath, $sql) === false) {
                throw new \RuntimeException('Unable to write backup file to disk.');
            }

            $backupSize = filesize($filepath) ?: 0;

            $this->backupLogModel->insert([
                'backup_type' => $backupType,
                'backup_file' => $filename,
                'backup_size' => $backupSize,
                'status' => 'Success',
                'message' => 'Backup created successfully',
                'created_by' => session()->get('user_id')
            ]);

            $this->auditLogModel->log(
                session()->get('user_id'),
                'backup_create',
                'Backup',
                null,
                null,
                ['filename' => $filename, 'size' => $backupSize]
            );

            return $this->response->setJSON([
                'status' => 'success',
                'message' => 'Backup created successfully',
                'filename' => $filename
            ]);
        } catch (\Throwable $e) {
            log_message('error', 'Backup creation failed: ' . $e->getMessage());

            try {
                $this->backupLogModel->insert([
                    'backup_type' => $backupType,
                    'backup_file' => $filename ?? ('failed_backup_' . date('Y-m-d_H-i-s') . '.sql'),
                    'backup_size' => 0,
                    'status' => 'Failed',
                    'message' => $e->getMessage(),
                    'created_by' => session()->get('user_id')
                ]);
            } catch (\Throwable $logException) {
                log_message('error', 'Backup failure log insert failed: ' . $logException->getMessage());
            }

            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'Failed to create backup: ' . $e->getMessage()
            ]);
        }
    }
    
    /**
     * Restore backup
     */
    public function restoreBackup($filename)
    {
        if (!$this->request->isAJAX()) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Invalid request']);
        }

        $safeFilename = $this->sanitizeBackupFilename($filename);
        if (!$safeFilename) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Invalid backup filename']);
        }

        $backupPath = $this->getBackupDirectory() . $safeFilename;

        if (!is_file($backupPath)) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Backup file not found']);
        }

        $sql = file_get_contents($backupPath);
        if ($sql === false) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Unable to read backup file']);
        }

        $db = \Config\Database::connect();

        try {
            $db->query("SET FOREIGN_KEY_CHECKS=0");

            foreach ($this->parseSqlStatements($sql) as $statement) {
                $db->query($statement);
            }

            $db->query("SET FOREIGN_KEY_CHECKS=1");

            $this->backupLogModel->insert([
                'backup_type' => 'Restore',
                'backup_file' => $safeFilename,
                'backup_size' => filesize($backupPath),
                'status' => 'Success',
                'message' => 'Database restored successfully from ' . $safeFilename,
                'created_by' => session()->get('user_id')
            ]);

            $this->auditLogModel->log(
                session()->get('user_id'),
                'backup_restore',
                'Backup',
                null,
                null,
                ['filename' => $safeFilename]
            );

            return $this->response->setJSON([
                'status' => 'success',
                'message' => 'Database restored successfully'
            ]);
        } catch (\Throwable $e) {
            try {
                $db->query("SET FOREIGN_KEY_CHECKS=1");
            } catch (\Throwable $innerException) {
                log_message('error', 'Failed to re-enable foreign key checks after restore error: ' . $innerException->getMessage());
            }

            $this->backupLogModel->insert([
                'backup_type' => 'Restore',
                'backup_file' => $safeFilename,
                'backup_size' => filesize($backupPath) ?: 0,
                'status' => 'Failed',
                'message' => $e->getMessage(),
                'created_by' => session()->get('user_id')
            ]);
            
            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'Failed to restore backup: ' . $e->getMessage()
            ]);
        }
    }
    
    /**
     * Delete backup
     */
    public function deleteBackup($filename)
    {
        if (!$this->request->isAJAX()) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Invalid request']);
        }

        $safeFilename = $this->sanitizeBackupFilename($filename);
        if (!$safeFilename) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Invalid backup filename']);
        }

        $backupPath = $this->getBackupDirectory() . $safeFilename;

        if (!is_file($backupPath)) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Backup file not found']);
        }

        if (unlink($backupPath)) {
            $this->auditLogModel->log(
                session()->get('user_id'),
                'backup_delete',
                'Backup',
                null,
                null,
                ['filename' => $safeFilename]
            );

            return $this->response->setJSON(['status' => 'success', 'message' => 'Backup deleted successfully']);
        }

        return $this->response->setJSON(['status' => 'error', 'message' => 'Failed to delete backup']);
    }

    /**
     * Download backup file
     */
    public function downloadBackup($filename)
    {
        $safeFilename = $this->sanitizeBackupFilename($filename);
        if (!$safeFilename) {
            return redirect()->to('/admin/backup')->with('error', 'Invalid backup filename');
        }

        $backupPath = $this->getBackupDirectory() . $safeFilename;
        if (!is_file($backupPath)) {
            return redirect()->to('/admin/backup')->with('error', 'Backup file not found');
        }

        return $this->response->download($backupPath, null);
    }
    
    /**
     * System Information
     */
    public function systemInfo()
    {
        $db = \Config\Database::connect();
        
        $data = [
            'title' => 'System Information',
            'php_version' => phpversion(),
            'ci_version' => \CodeIgniter\CodeIgniter::CI_VERSION,
            'mysql_version' => $db->getVersion(),
            'server_software' => $_SERVER['SERVER_SOFTWARE'] ?? 'Unknown',
            'upload_max_filesize' => ini_get('upload_max_filesize'),
            'post_max_size' => ini_get('post_max_size'),
            'max_execution_time' => ini_get('max_execution_time'),
            'memory_limit' => ini_get('memory_limit'),
            'activePage' => 'settings'
        ];
        
        return view('admin/system_info', $data);
    }
    
    /**
     * Export to Excel helper
     */
    private function exportExcel($data, $title, $filename)
    {
        // Load PhpSpreadsheet
        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        
        // Set title
        $sheet->setCellValue('A1', $title);
        $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(14);
        
        // Add data
        $row = 3;
        if (!empty($data) && is_array($data[0] ?? null)) {
            // Add headers
            $col = 'A';
            foreach (array_keys($data[0]) as $key) {
                $sheet->setCellValue($col . $row, ucwords(str_replace('_', ' ', $key)));
                $sheet->getStyle($col . $row)->getFont()->setBold(true);
                $col++;
            }
            $row++;
            
            // Add data rows
            foreach ($data as $item) {
                $col = 'A';
                foreach ($item as $value) {
                    $sheet->setCellValue($col . $row, $value);
                    $col++;
                }
                $row++;
            }
        } else {
            $sheet->setCellValue('A3', 'No data available');
        }
        
        // Auto-size columns
        foreach (range('A', $sheet->getHighestColumn()) as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }
        
        // Set headers for download
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . $filename . '"');
        header('Cache-Control: max-age=0');
        
        $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
        $writer->save('php://output');
        exit();
    }

    private function getBackupDirectory()
    {
        $backupPath = WRITEPATH . 'backups' . DIRECTORY_SEPARATOR;

        if (!is_dir($backupPath) && !mkdir($backupPath, 0777, true) && !is_dir($backupPath)) {
            throw new \RuntimeException('Unable to create backup directory.');
        }

        return $backupPath;
    }

    private function sanitizeBackupFilename($filename)
    {
        $safeFilename = basename((string) $filename);

        if (!preg_match('/^[A-Za-z0-9._-]+\.sql$/', $safeFilename)) {
            return null;
        }

        return $safeFilename;
    }

    private function buildDatabaseBackup()
    {
        $db = \Config\Database::connect();
        $tables = $db->listTables();

        $sql = "-- Inventory Management System Backup\n";
        $sql .= "-- Date: " . date('Y-m-d H:i:s') . "\n";
        $sql .= "-- Generated by: " . (session()->get('username') ?: 'system') . "\n\n";
        $sql .= "SET FOREIGN_KEY_CHECKS=0;\n\n";

        foreach ($tables as $table) {
            $query = $db->query("SHOW CREATE TABLE `{$table}`");
            $row = $query->getRowArray();
            $createTableSql = $row['Create Table'] ?? null;

            if (!$createTableSql) {
                throw new \RuntimeException('Unable to build CREATE TABLE statement for ' . $table);
            }

            $sql .= "DROP TABLE IF EXISTS `{$table}`;\n";
            $sql .= $createTableSql . ";\n\n";

            $rows = $db->table($table)->get()->getResultArray();
            if (empty($rows)) {
                continue;
            }

            $columns = array_keys($rows[0]);
            $quotedColumns = array_map(static function ($column) {
                return '`' . $column . '`';
            }, $columns);

            foreach ($rows as $dataRow) {
                $values = [];
                foreach ($columns as $column) {
                    $value = $dataRow[$column];
                    $values[] = $value === null ? 'NULL' : $db->escape($value);
                }

                $sql .= "INSERT INTO `{$table}` (" . implode(', ', $quotedColumns) . ") VALUES (" . implode(', ', $values) . ");\n";
            }

            $sql .= "\n";
        }

        $sql .= "SET FOREIGN_KEY_CHECKS=1;\n";

        return $sql;
    }

    private function parseSqlStatements($sql)
    {
        $statements = [];
        $current = '';
        $inSingleQuote = false;
        $inDoubleQuote = false;
        $inLineComment = false;
        $inBlockComment = false;
        $length = strlen($sql);

        for ($index = 0; $index < $length; $index++) {
            $char = $sql[$index];
            $next = $index + 1 < $length ? $sql[$index + 1] : null;

            if ($inLineComment) {
                if ($char === "\n") {
                    $inLineComment = false;
                }
                continue;
            }

            if ($inBlockComment) {
                if ($char === '*' && $next === '/') {
                    $inBlockComment = false;
                    $index++;
                }
                continue;
            }

            if (!$inSingleQuote && !$inDoubleQuote) {
                if ($char === '-' && $next === '-') {
                    $inLineComment = true;
                    $index++;
                    continue;
                }

                if ($char === '#') {
                    $inLineComment = true;
                    continue;
                }

                if ($char === '/' && $next === '*') {
                    $inBlockComment = true;
                    $index++;
                    continue;
                }
            }

            if ($char === "'" && !$inDoubleQuote) {
                $escaped = $index > 0 && $sql[$index - 1] === '\\';
                if (!$escaped) {
                    $inSingleQuote = !$inSingleQuote;
                }
            } elseif ($char === '"' && !$inSingleQuote) {
                $escaped = $index > 0 && $sql[$index - 1] === '\\';
                if (!$escaped) {
                    $inDoubleQuote = !$inDoubleQuote;
                }
            }

            if ($char === ';' && !$inSingleQuote && !$inDoubleQuote) {
                $statement = trim($current);
                if ($statement !== '') {
                    $statements[] = $statement;
                }
                $current = '';
                continue;
            }

            $current .= $char;
        }

        $tail = trim($current);
        if ($tail !== '') {
            $statements[] = $tail;
        }

        return $statements;
    }
}
