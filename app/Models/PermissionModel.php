<?php

namespace App\Models;

use CodeIgniter\Model;

class PermissionModel extends Model
{
    protected $table            = 'permissions';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = ['permission_name', 'description'];

    // Dates
    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    // Validation
    protected $validationRules = [
        'permission_name' => 'required|min_length[3]|max_length[100]|is_unique[permissions.permission_name,id,{id}]',
        'description' => 'permit_empty|max_length[500]'
    ];

    /**
     * System permission catalog used for syncing and matrix rendering.
     */
    public function getPermissionCatalog()
    {
        return [
            'general' => [
                [
                    'permission_name' => 'access_dashboard',
                    'label' => 'Dashboard',
                    'description' => 'Allow users to open the dashboard.',
                    'default_roles' => ['Admin', 'Manager', 'Staff'],
                ],
                [
                    'permission_name' => 'delete_records',
                    'label' => 'Delete Records',
                    'description' => 'Allow deletion across modules that support record removal.',
                    'default_roles' => ['Admin', 'Manager'],
                ],
            ],
            'inventory' => [
                [
                    'permission_name' => 'access_inventory',
                    'label' => 'Inventory Module',
                    'description' => 'Allow access to inventory screens and navigation.',
                    'default_roles' => ['Admin', 'Manager', 'Staff'],
                ],
                [
                    'permission_name' => 'view_inventory',
                    'label' => 'View Inventory',
                    'description' => 'Allow users to view inventory records.',
                    'default_roles' => ['Admin', 'Manager', 'Staff'],
                ],
                [
                    'permission_name' => 'manage_products',
                    'label' => 'Manage Products',
                    'description' => 'Create and update products.',
                    'default_roles' => ['Admin', 'Manager'],
                ],
                [
                    'permission_name' => 'manage_categories',
                    'label' => 'Manage Categories',
                    'description' => 'Create and update inventory categories.',
                    'default_roles' => ['Admin', 'Manager'],
                ],
                [
                    'permission_name' => 'manage_units',
                    'label' => 'Manage Units',
                    'description' => 'Create and update inventory units.',
                    'default_roles' => ['Admin', 'Manager'],
                ],
                [
                    'permission_name' => 'adjust_stock',
                    'label' => 'Adjust Stock',
                    'description' => 'Record stock adjustments and movement changes.',
                    'default_roles' => ['Admin', 'Manager'],
                ],
            ],
            'sales' => [
                [
                    'permission_name' => 'access_sales',
                    'label' => 'Sales Module',
                    'description' => 'Allow access to sales screens and navigation.',
                    'default_roles' => ['Admin', 'Manager', 'Staff'],
                ],
                [
                    'permission_name' => 'create_sales',
                    'label' => 'Create Sales',
                    'description' => 'Create new sales transactions.',
                    'default_roles' => ['Admin', 'Manager', 'Staff'],
                ],
                [
                    'permission_name' => 'manage_sales',
                    'label' => 'Manage Sales',
                    'description' => 'Manage, review, and update sales workflows.',
                    'default_roles' => ['Admin', 'Manager'],
                ],
                [
                    'permission_name' => 'access_customers',
                    'label' => 'Customers Module',
                    'description' => 'Allow access to customers screens and navigation.',
                    'default_roles' => ['Admin', 'Manager', 'Staff'],
                ],
                [
                    'permission_name' => 'view_customers',
                    'label' => 'View Customers',
                    'description' => 'View customer information and history.',
                    'default_roles' => ['Admin', 'Manager', 'Staff'],
                ],
                [
                    'permission_name' => 'manage_customers',
                    'label' => 'Manage Customers',
                    'description' => 'Create and update customer records.',
                    'default_roles' => ['Admin', 'Manager'],
                ],
            ],
            'purchases' => [
                [
                    'permission_name' => 'access_purchases',
                    'label' => 'Purchases Module',
                    'description' => 'Allow access to purchases screens and navigation.',
                    'default_roles' => ['Admin', 'Manager'],
                ],
                [
                    'permission_name' => 'manage_purchases',
                    'label' => 'Manage Purchases',
                    'description' => 'Create purchases, receive items, and add purchase payments.',
                    'default_roles' => ['Admin', 'Manager'],
                ],
                [
                    'permission_name' => 'access_suppliers',
                    'label' => 'Suppliers Module',
                    'description' => 'Allow access to suppliers screens and navigation.',
                    'default_roles' => ['Admin', 'Manager'],
                ],
                [
                    'permission_name' => 'manage_suppliers',
                    'label' => 'Manage Suppliers',
                    'description' => 'Create and update supplier records.',
                    'default_roles' => ['Admin', 'Manager'],
                ],
            ],
            'expenses' => [
                [
                    'permission_name' => 'access_expenses',
                    'label' => 'Expenses Module',
                    'description' => 'Allow access to expense screens and navigation.',
                    'default_roles' => ['Admin', 'Manager'],
                ],
                [
                    'permission_name' => 'manage_expenses',
                    'label' => 'Manage Expenses',
                    'description' => 'Create and update expense records.',
                    'default_roles' => ['Admin', 'Manager'],
                ],
                [
                    'permission_name' => 'manage_expense_categories',
                    'label' => 'Manage Expense Categories',
                    'description' => 'Create and update expense categories.',
                    'default_roles' => ['Admin', 'Manager'],
                ],
            ],
            'production' => [
                [
                    'permission_name' => 'access_production',
                    'label' => 'Production Module',
                    'description' => 'Allow access to production screens and navigation.',
                    'default_roles' => ['Admin', 'Manager'],
                ],
                [
                    'permission_name' => 'manage_production',
                    'label' => 'Manage Production',
                    'description' => 'Create, update, complete, and cancel production jobs.',
                    'default_roles' => ['Admin', 'Manager'],
                ],
                [
                    'permission_name' => 'manage_bom',
                    'label' => 'Manage Bill of Materials',
                    'description' => 'Create and maintain BOM templates.',
                    'default_roles' => ['Admin', 'Manager'],
                ],
                [
                    'permission_name' => 'record_stock_usage',
                    'label' => 'Record Stock Usage',
                    'description' => 'Record material usage in production workflows.',
                    'default_roles' => ['Admin', 'Manager', 'Staff'],
                ],
            ],
            'reports' => [
                [
                    'permission_name' => 'view_reports',
                    'label' => 'Reports Module',
                    'description' => 'View reports and export report data.',
                    'default_roles' => ['Admin', 'Manager'],
                ],
            ],
            'administration' => [
                [
                    'permission_name' => 'access_admin',
                    'label' => 'Administration Module',
                    'description' => 'Allow access to administration screens and navigation.',
                    'default_roles' => ['Admin'],
                ],
                [
                    'permission_name' => 'manage_users',
                    'label' => 'Manage Users',
                    'description' => 'Create, update, and review user accounts.',
                    'default_roles' => ['Admin'],
                ],
                [
                    'permission_name' => 'manage_roles',
                    'label' => 'Manage Roles and Permissions',
                    'description' => 'Configure role permissions and access rules.',
                    'default_roles' => ['Admin'],
                ],
                [
                    'permission_name' => 'manage_settings',
                    'label' => 'Manage System Settings',
                    'description' => 'Update system-wide business and application settings.',
                    'default_roles' => ['Admin'],
                ],
                [
                    'permission_name' => 'view_audit_logs',
                    'label' => 'View Audit Logs',
                    'description' => 'Review audit trails and export audit logs.',
                    'default_roles' => ['Admin'],
                ],
                [
                    'permission_name' => 'manage_backup',
                    'label' => 'Manage Backups',
                    'description' => 'Create, restore, and delete backups.',
                    'default_roles' => ['Admin'],
                ],
                [
                    'permission_name' => 'view_system_info',
                    'label' => 'View System Info',
                    'description' => 'View technical system information.',
                    'default_roles' => ['Admin'],
                ],
            ],
        ];
    }

    /**
     * Ensure all system permissions exist in the database.
     */
    public function syncSystemPermissions()
    {
        $catalog = $this->getPermissionCatalog();
        $existingPermissions = $this->findAll();
        $existingMap = [];

        foreach ($existingPermissions as $permission) {
            $existingMap[$permission['permission_name']] = $permission;
        }

        foreach ($catalog as $groupPermissions) {
            foreach ($groupPermissions as $permission) {
                $name = $permission['permission_name'];
                $description = $permission['description'] ?? null;

                if (!isset($existingMap[$name])) {
                    $permissionId = $this->insert([
                        'permission_name' => $name,
                        'description' => $description,
                    ]);

                    if ($permissionId) {
                        $roleRows = $this->db->table('roles')
                            ->select('id, role_name')
                            ->whereIn('role_name', $permission['default_roles'] ?? [])
                            ->get()
                            ->getResultArray();

                        $rolePermissionRows = [];
                        foreach ($roleRows as $role) {
                            $rolePermissionRows[] = [
                                'role_id' => $role['id'],
                                'permission_id' => $permissionId,
                                'created_at' => date('Y-m-d H:i:s'),
                            ];
                        }

                        if (!empty($rolePermissionRows)) {
                            $this->db->table('role_permissions')->insertBatch($rolePermissionRows);
                        }
                    }

                    continue;
                }

                if (($existingMap[$name]['description'] ?? null) !== $description) {
                    $this->update($existingMap[$name]['id'], [
                        'permission_name' => $name,
                        'description' => $description,
                    ]);
                }
            }
        }
    }

    /**
     * Get permissions by role ID
     */
    public function getPermissionsByRole($roleId)
    {
        $builder = $this->db->table('permissions');
        $builder->select('permissions.*');
        $builder->join('role_permissions', 'role_permissions.permission_id = permissions.id');
        $builder->where('role_permissions.role_id', $roleId);
        
        return $builder->get()->getResultArray();
    }

    /**
     * Get permission count by role
     */
    public function getPermissionCountByRole($roleId)
    {
        $builder = $this->db->table('role_permissions');
        $builder->where('role_id', $roleId);
        
        return $builder->countAllResults();
    }

    /**
     * Assign permissions to role
     */
    public function assignToRole($roleId, array $permissionIds)
    {
        $builder = $this->db->table('role_permissions');
        
        // Remove existing
        $builder->where('role_id', $roleId)->delete();
        
        // Add new
        $data = [];
        foreach ($permissionIds as $permId) {
            $data[] = [
                'role_id' => $roleId,
                'permission_id' => $permId,
                'created_at' => date('Y-m-d H:i:s')
            ];
        }
        
        if (!empty($data)) {
            return $builder->insertBatch($data);
        }
        
        return true;
    }

    /**
     * Assign permissions to role by permission names.
     */
    public function assignNamesToRole($roleId, array $permissionNames)
    {
        $permissionNames = array_values(array_unique(array_filter($permissionNames)));

        if (empty($permissionNames)) {
            return $this->assignToRole($roleId, []);
        }

        $permissions = $this->whereIn('permission_name', $permissionNames)->findAll();
        $permissionIds = array_column($permissions, 'id');

        return $this->assignToRole($roleId, $permissionIds);
    }

    /**
     * Check if role has permission
     */
    public function roleHasPermission($roleId, $permissionName)
    {
        $builder = $this->db->table('role_permissions');
        $builder->select('role_permissions.id');
        $builder->join('permissions', 'permissions.id = role_permissions.permission_id');
        $builder->where('role_permissions.role_id', $roleId);
        $builder->where('permissions.permission_name', $permissionName);

        return $builder->get()->getRow() !== null;
    }

    /**
     * Get permission names assigned to a role.
     */
    public function getRolePermissionNames($roleId)
    {
        return array_column($this->getPermissionsByRole($roleId), 'permission_name');
    }

    /**
     * Build role-permission matrix data for administration.
     */
    public function getRolePermissionMatrix($roles)
    {
        $this->syncSystemPermissions();

        $permissions = $this->findAll();
        $permissionMap = [];
        foreach ($permissions as $permission) {
            $permissionMap[$permission['permission_name']] = $permission;
        }

        $matrix = [];
        foreach ($roles as $role) {
            $matrix[$role['id']] = array_flip($this->getRolePermissionNames($role['id']));
        }

        $groupedPermissions = [];
        foreach ($this->getPermissionCatalog() as $groupKey => $groupPermissions) {
            $groupedPermissions[$groupKey] = [];
            foreach ($groupPermissions as $definition) {
                if (!isset($permissionMap[$definition['permission_name']])) {
                    continue;
                }

                $groupedPermissions[$groupKey][] = array_merge($permissionMap[$definition['permission_name']], [
                    'label' => $definition['label'] ?? $definition['permission_name'],
                    'group' => $groupKey,
                ]);
            }
        }

        return [
            'groupedPermissions' => $groupedPermissions,
            'rolePermissionMap' => $matrix,
        ];
    }

    /**
     * Apply default permissions to the known system roles.
     */
    public function applyDefaultRolePermissions($roles)
    {
        $this->syncSystemPermissions();

        $catalog = $this->getPermissionCatalog();
        $allPermissionNames = [];
        foreach ($catalog as $groupPermissions) {
            foreach ($groupPermissions as $permission) {
                $allPermissionNames[] = $permission['permission_name'];
            }
        }

        foreach ($roles as $role) {
            if (strcasecmp($role['role_name'], 'Admin') === 0) {
                $this->assignNamesToRole($role['id'], $allPermissionNames);
                continue;
            }

            $defaults = [];
            foreach ($catalog as $groupPermissions) {
                foreach ($groupPermissions as $permission) {
                    if (in_array($role['role_name'], $permission['default_roles'], true)) {
                        $defaults[] = $permission['permission_name'];
                    }
                }
            }

            $this->assignNamesToRole($role['id'], $defaults);
        }
    }
}
