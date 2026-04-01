<?php

/**
 * Permission Helper Functions
 * These functions are available globally in views and controllers
 */

if (!function_exists('has_permission')) {
    /**
     * Check if user has permission
     */
    function has_permission($permissionName)
    {
        // Check if user is logged in
        $session = \Config\Services::session();
        
        if (!$session->get('isLoggedIn')) {
            return false;
        }
        
        // For admin users, grant all permissions
        if (is_admin()) {
            return true;
        }
        
        try {
            $permissionModel = new \App\Models\PermissionModel();
            $roleId = $session->get('role_id');
            
            if ($roleId) {
                static $loadedPermissions = [];

                if (!isset($loadedPermissions[$roleId])) {
                    $permissionModel->syncSystemPermissions();
                    $loadedPermissions[$roleId] = $permissionModel->getRolePermissionNames($roleId);
                }

                return in_array($permissionName, $loadedPermissions[$roleId], true);
            }
        } catch (\Exception $e) {
            // If database error, return false
            return false;
        }
        
        return false;
    }
}

if (!function_exists('is_admin')) {
    /**
     * Check if user is admin
     */
    function is_admin()
    {
        $session = \Config\Services::session();
        
        if (!$session->get('isLoggedIn')) {
            return false;
        }
        
        $roleId = $session->get('role_id');
        
        // Role ID 1 is Admin (based on seeder)
        return $roleId == 1;
    }
}

if (!function_exists('is_manager')) {
    /**
     * Check if user is manager
     */
    function is_manager()
    {
        $session = \Config\Services::session();
        
        if (!$session->get('isLoggedIn')) {
            return false;
        }
        
        $roleId = $session->get('role_id');
        
        // Role ID 2 is Manager (based on seeder)
        return $roleId == 2;
    }
}

if (!function_exists('is_staff')) {
    /**
     * Check if user is staff
     */
    function is_staff()
    {
        $session = \Config\Services::session();
        
        if (!$session->get('isLoggedIn')) {
            return false;
        }
        
        $roleId = $session->get('role_id');
        
        // Role ID 3 is Staff (based on seeder)
        return $roleId == 3;
    }
}

if (!function_exists('can_access_module')) {
    /**
     * Check if user can access a module
     */
    function can_access_module($module)
    {
        $modulePermissions = [
            'dashboard' => ['access_dashboard'],
            'inventory' => ['access_inventory', 'view_inventory', 'manage_products', 'manage_categories', 'manage_units', 'adjust_stock'],
            'sales' => ['access_sales', 'manage_sales', 'create_sales'],
            'customers' => ['access_customers', 'view_customers', 'manage_customers'],
            'purchases' => ['access_purchases', 'manage_purchases'],
            'suppliers' => ['access_suppliers', 'manage_suppliers'],
            'expenses' => ['access_expenses', 'manage_expenses', 'manage_expense_categories'],
            'production' => ['access_production', 'manage_production', 'manage_bom', 'record_stock_usage'],
            'reports' => ['view_reports'],
            'admin' => ['access_admin', 'manage_users', 'manage_roles', 'manage_settings', 'view_audit_logs', 'manage_backup', 'view_system_info'],
        ];

        if (!isset($modulePermissions[$module])) {
            return false;
        }

        foreach ($modulePermissions[$module] as $permissionName) {
            if (has_permission($permissionName)) {
                return true;
            }
        }

        return false;
    }
}

if (!function_exists('get_user_role')) {
    /**
     * Get current user role name
     */
    function get_user_role()
    {
        $session = \Config\Services::session();
        
        if (!$session->get('isLoggedIn')) {
            return null;
        }
        
        $roleId = $session->get('role_id');
        
        try {
            $roleModel = new \App\Models\RoleModel();
            $role = $roleModel->find($roleId);
            
            return $role ? $role['role_name'] : null;
        } catch (\Exception $e) {
            return null;
        }
    }
}
