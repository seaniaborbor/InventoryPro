<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;
use CodeIgniter\I18n\Time;

class DatabaseSeeder extends Seeder
{
    public function run()
    {
        echo "\n==========================================\n";
        echo "STARTING AUTHENTICATION SEEDER\n";
        echo "==========================================\n\n";

        // ==================== 1. INSERT ROLES ====================
        echo "📌 Inserting roles...\n";
        
        $roles = [
            [
                'role_name' => 'Admin',
                'description' => 'Full system control and configuration',
                'created_at' => Time::now(),
                'updated_at' => Time::now(),
            ],
            [
                'role_name' => 'Manager',
                'description' => 'Day-to-day operations and reporting',
                'created_at' => Time::now(),
                'updated_at' => Time::now(),
            ],
            [
                'role_name' => 'Staff',
                'description' => 'Basic data entry and viewing',
                'created_at' => Time::now(),
                'updated_at' => Time::now(),
            ],
        ];
        
        foreach ($roles as $role) {
            $exists = $this->db->table('roles')
                ->where('role_name', $role['role_name'])
                ->get()
                ->getRow();
            
            if (!$exists) {
                $this->db->table('roles')->insert($role);
                echo "   ✓ Role '{$role['role_name']}' created.\n";
            } else {
                echo "   • Role '{$role['role_name']}' already exists.\n";
            }
        }

        // Get role IDs
        $adminRole = $this->db->table('roles')->where('role_name', 'Admin')->get()->getRow();
        $managerRole = $this->db->table('roles')->where('role_name', 'Manager')->get()->getRow();
        $staffRole = $this->db->table('roles')->where('role_name', 'Staff')->get()->getRow();

        // ==================== 2. INSERT PERMISSIONS ====================
        echo "\n📌 Inserting permissions...\n";
        
        $permissionModel = new \App\Models\PermissionModel();
        $permissionCatalog = $permissionModel->getPermissionCatalog();
        
        $permissionsInserted = 0;
        $permissionsExisting = 0;
        
        foreach ($permissionCatalog as $groupName => $groupPermissions) {
            foreach ($groupPermissions as $permission) {
                $exists = $this->db->table('permissions')
                    ->where('permission_name', $permission['permission_name'])
                    ->get()
                    ->getRow();
                
                if (!$exists) {
                    $this->db->table('permissions')->insert([
                        'permission_name' => $permission['permission_name'],
                        'description' => $permission['description'],
                        'created_at' => Time::now(),
                        'updated_at' => Time::now(),
                    ]);
                    $permissionsInserted++;
                } else {
                    $permissionsExisting++;
                }
            }
        }
        
        echo "   ✓ {$permissionsInserted} new permissions inserted.\n";
        echo "   • {$permissionsExisting} permissions already exist.\n";

        // ==================== 3. ASSIGN PERMISSIONS TO ROLES ====================
        echo "\n📌 Assigning permissions to roles...\n";
        
        // Get all permissions
        $allPermissions = $this->db->table('permissions')->get()->getResultArray();
        $allPermissionIds = array_column($allPermissions, 'id');
        
        // Admin gets ALL permissions
        $this->assignPermissionsToRole($adminRole->id, $allPermissionIds);
        echo "   ✓ Admin role assigned " . count($allPermissionIds) . " permissions.\n";
        
        // Manager gets specific permissions based on catalog defaults
        $managerPermissions = [];
        foreach ($permissionCatalog as $groupPermissions) {
            foreach ($groupPermissions as $permission) {
                if (in_array('Manager', $permission['default_roles'])) {
                    $perm = $this->db->table('permissions')
                        ->where('permission_name', $permission['permission_name'])
                        ->get()
                        ->getRow();
                    if ($perm) {
                        $managerPermissions[] = $perm->id;
                    }
                }
            }
        }
        $this->assignPermissionsToRole($managerRole->id, $managerPermissions);
        echo "   ✓ Manager role assigned " . count($managerPermissions) . " permissions.\n";
        
        // Staff gets specific permissions based on catalog defaults
        $staffPermissions = [];
        foreach ($permissionCatalog as $groupPermissions) {
            foreach ($groupPermissions as $permission) {
                if (in_array('Staff', $permission['default_roles'])) {
                    $perm = $this->db->table('permissions')
                        ->where('permission_name', $permission['permission_name'])
                        ->get()
                        ->getRow();
                    if ($perm) {
                        $staffPermissions[] = $perm->id;
                    }
                }
            }
        }
        $this->assignPermissionsToRole($staffRole->id, $staffPermissions);
        echo "   ✓ Staff role assigned " . count($staffPermissions) . " permissions.\n";

        // ==================== 4. CREATE USERS ====================
        echo "\n📌 Creating users...\n";
        
        // Admin User
        $adminUser = $this->db->table('users')
            ->where('username', 'admin')
            ->orWhere('email', 'admin@innovativegraphics.com')
            ->get()
            ->getRow();
        
        if (!$adminUser) {
            $this->db->table('users')->insert([
                'username' => 'admin',
                'email' => 'admin@innovativegraphics.com',
                'password' => password_hash('Admin@123', PASSWORD_DEFAULT),
                'full_name' => 'System Administrator',
                'phone' => '+231-778-651-747',
                'role_id' => $adminRole->id,
                'is_active' => 1,
                'created_at' => Time::now(),
                'updated_at' => Time::now(),
            ]);
            echo "   ✓ Admin user created.\n";
        } else {
            echo "   • Admin user already exists.\n";
        }
        
        // Manager User
        $managerUser = $this->db->table('users')
            ->where('username', 'manager')
            ->orWhere('email', 'manager@innovativegraphics.com')
            ->get()
            ->getRow();
        
        if (!$managerUser) {
            $this->db->table('users')->insert([
                'username' => 'manager',
                'email' => 'manager@innovativegraphics.com',
                'password' => password_hash('Manager@123', PASSWORD_DEFAULT),
                'full_name' => 'Operations Manager',
                'phone' => '+231-778-651-748',
                'role_id' => $managerRole->id,
                'is_active' => 1,
                'created_at' => Time::now(),
                'updated_at' => Time::now(),
            ]);
            echo "   ✓ Manager user created.\n";
        } else {
            echo "   • Manager user already exists.\n";
        }
        
        // Staff User
        $staffUser = $this->db->table('users')
            ->where('username', 'staff')
            ->orWhere('email', 'staff@innovativegraphics.com')
            ->get()
            ->getRow();
        
        if (!$staffUser) {
            $this->db->table('users')->insert([
                'username' => 'staff',
                'email' => 'staff@innovativegraphics.com',
                'password' => password_hash('Staff@123', PASSWORD_DEFAULT),
                'full_name' => 'Sales Staff',
                'phone' => '+231-778-651-749',
                'role_id' => $staffRole->id,
                'is_active' => 1,
                'created_at' => Time::now(),
                'updated_at' => Time::now(),
            ]);
            echo "   ✓ Staff user created.\n";
        } else {
            echo "   • Staff user already exists.\n";
        }

        // ==================== 5. INSERT UNITS ====================
        echo "\n📌 Inserting product units...\n";
        
        $units = [
            ['unit_name' => 'Pieces', 'unit_symbol' => 'pcs', 'description' => 'Individual pieces/count'],
            ['unit_name' => 'Carton', 'unit_symbol' => 'ctn', 'description' => 'Carton box'],
            ['unit_name' => 'Pack', 'unit_symbol' => 'pack', 'description' => 'Pack of items'],
            ['unit_name' => 'Sheet', 'unit_symbol' => 'sheet', 'description' => 'Single sheet'],
            ['unit_name' => 'Roll', 'unit_symbol' => 'roll', 'description' => 'Roll of material'],
            ['unit_name' => 'Box', 'unit_symbol' => 'box', 'description' => 'Box container'],
            ['unit_name' => 'Kilogram', 'unit_symbol' => 'kg', 'description' => 'Weight in kilograms'],
            ['unit_name' => 'Gram', 'unit_symbol' => 'g', 'description' => 'Weight in grams'],
            ['unit_name' => 'Liter', 'unit_symbol' => 'L', 'description' => 'Volume in liters'],
            ['unit_name' => 'Milliliter', 'unit_symbol' => 'mL', 'description' => 'Volume in milliliters'],
            ['unit_name' => 'Meter', 'unit_symbol' => 'm', 'description' => 'Length in meters'],
            ['unit_name' => 'Centimeter', 'unit_symbol' => 'cm', 'description' => 'Length in centimeters'],
            ['unit_name' => 'Dozen', 'unit_symbol' => 'doz', 'description' => '12 pieces'],
            ['unit_name' => 'Gross', 'unit_symbol' => 'gr', 'description' => '144 pieces (12 dozen)'],
            ['unit_name' => 'Ream', 'unit_symbol' => 'ream', 'description' => '500 sheets of paper'],
            ['unit_name' => 'Set', 'unit_symbol' => 'set', 'description' => 'Complete set'],
            ['unit_name' => 'Pair', 'unit_symbol' => 'pr', 'description' => '2 pieces'],
            ['unit_name' => 'Bundle', 'unit_symbol' => 'bdl', 'description' => 'Bundle of items'],
            ['unit_name' => 'Bag', 'unit_symbol' => 'bag', 'description' => 'Bag container'],
            ['unit_name' => 'Bottle', 'unit_symbol' => 'btl', 'description' => 'Bottle container'],
        ];
        
        $unitsInserted = 0;
        $unitsExisting = 0;
        
        foreach ($units as $unit) {
            $exists = $this->db->table('units')
                ->where('unit_name', $unit['unit_name'])
                ->get()
                ->getRow();
            
            if (!$exists) {
                $this->db->table('units')->insert([
                    'unit_name' => $unit['unit_name'],
                    'unit_symbol' => $unit['unit_symbol'],
                    'created_at' => Time::now(),
                    'updated_at' => Time::now(),
                ]);
                $unitsInserted++;
            } else {
                $unitsExisting++;
            }
        }
        
        echo "   ✓ {$unitsInserted} new units inserted.\n";
        echo "   • {$unitsExisting} units already exist.\n";

        // ==================== 6. SYSTEM SETTINGS (only essential for login) ====================
        echo "\n📌 Inserting essential system settings...\n";
        
        $essentialSettings = [
            ['setting_key' => 'business_name', 'setting_value' => 'Innovative Graphics', 'setting_type' => 'string', 'group' => 'general'],
            ['setting_key' => 'default_currency', 'setting_value' => 'LRD', 'setting_type' => 'string', 'group' => 'currency'],
            ['setting_key' => 'session_timeout', 'setting_value' => '3600', 'setting_type' => 'integer', 'group' => 'security'],
            ['setting_key' => 'default_unit', 'setting_value' => 'pcs', 'setting_type' => 'string', 'group' => 'inventory'],
        ];
        
        foreach ($essentialSettings as $setting) {
            $exists = $this->db->table('system_settings')
                ->where('setting_key', $setting['setting_key'])
                ->get()
                ->getRow();
            
            if (!$exists) {
                $this->db->table('system_settings')->insert([
                    'setting_key' => $setting['setting_key'],
                    'setting_value' => $setting['setting_value'],
                    'setting_type' => $setting['setting_type'],
                    'group' => $setting['group'],
                    'created_at' => Time::now(),
                    'updated_at' => Time::now(),
                ]);
                echo "   ✓ Setting '{$setting['setting_key']}' created.\n";
            } else {
                echo "   • Setting '{$setting['setting_key']}' already exists.\n";
            }
        }

        // ==================== COMPLETION SUMMARY ====================
        echo "\n==========================================\n";
        echo "✅ DATABASE SEEDER COMPLETED!\n";
        echo "==========================================\n";
        echo "\n📋 LOGIN CREDENTIALS:\n";
        echo "------------------------------------------\n";
        echo "Admin:   admin@innovativegraphics.com / Admin@123\n";
        echo "Manager: manager@innovativegraphics.com / Manager@123\n";
        echo "Staff:   staff@innovativegraphics.com / Staff@123\n";
        echo "------------------------------------------\n";
        echo "\n📊 SUMMARY:\n";
        echo "   • Roles: 3 (Admin, Manager, Staff)\n";
        echo "   • Permissions: " . count($allPermissions) . " total\n";
        echo "   • Users: 3 created\n";
        echo "   • Units: " . count($units) . " units available\n";
        echo "==========================================\n\n";
    }
    
    /**
     * Assign permissions to a role (clears existing first)
     */
    private function assignPermissionsToRole($roleId, array $permissionIds)
    {
        // Remove existing permissions
        $this->db->table('role_permissions')
            ->where('role_id', $roleId)
            ->delete();
        
        // Insert new permissions
        if (!empty($permissionIds)) {
            $data = [];
            foreach ($permissionIds as $permId) {
                $data[] = [
                    'role_id' => $roleId,
                    'permission_id' => $permId,
                    'created_at' => Time::now(),
                ];
            }
            $this->db->table('role_permissions')->insertBatch($data);
        }
    }
}