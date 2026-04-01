<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;
use CodeIgniter\I18n\Time;

class DatabaseSeeder extends Seeder
{
    public function run()
    {
        // Insert default roles
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
        $this->db->table('roles')->insertBatch($roles);

        // Get role IDs
        $adminRole = $this->db->table('roles')->where('role_name', 'Admin')->get()->getRow();
        $managerRole = $this->db->table('roles')->where('role_name', 'Manager')->get()->getRow();
        $staffRole = $this->db->table('roles')->where('role_name', 'Staff')->get()->getRow();

        // Insert default permissions
        $permissionModel = new \App\Models\PermissionModel();
        $permissionCatalog = $permissionModel->getPermissionCatalog();
        $permissions = [];
        foreach ($permissionCatalog as $groupPermissions) {
            foreach ($groupPermissions as $permission) {
                $permissions[] = [
                    'permission_name' => $permission['permission_name'],
                    'description' => $permission['description'],
                ];
            }
        }
        $this->db->table('permissions')->insertBatch($permissions);

        // Assign permissions to roles
        $permissionModel->applyDefaultRolePermissions([
            ['id' => $managerRole->id, 'role_name' => 'Manager'],
            ['id' => $staffRole->id, 'role_name' => 'Staff'],
            ['id' => $adminRole->id, 'role_name' => 'Admin'],
        ]);

        // Create admin user
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

        // Insert default units
        $units = [
            ['unit_name' => 'Pieces', 'unit_symbol' => 'pcs'],
            ['unit_name' => 'Pack', 'unit_symbol' => 'pack'],
            ['unit_name' => 'Sheet', 'unit_symbol' => 'sheet'],
            ['unit_name' => 'Roll', 'unit_symbol' => 'roll'],
            ['unit_name' => 'Box', 'unit_symbol' => 'box'],
            ['unit_name' => 'Kilogram', 'unit_symbol' => 'kg'],
            ['unit_name' => 'Liter', 'unit_symbol' => 'L'],
            ['unit_name' => 'Meter', 'unit_symbol' => 'm'],
        ];
        foreach ($units as $unit) {
            $this->db->table('units')->insert([
                'unit_name' => $unit['unit_name'],
                'unit_symbol' => $unit['unit_symbol'],
                'created_at' => Time::now(),
                'updated_at' => Time::now(),
            ]);
        }

        // Insert default categories
        $categories = [
            ['category_name' => 'ID Card Materials', 'description' => 'PVC cards, ribbons, laminates'],
            ['category_name' => 'Printer Parts', 'description' => 'Print heads, rollers, maintenance kits'],
            ['category_name' => 'Printing Materials', 'description' => 'Inks, toners, paper, card stock'],
            ['category_name' => 'Laminating Materials', 'description' => 'Laminating sheets, pouches'],
            ['category_name' => 'Office Supplies', 'description' => 'Stationery, labels, envelopes'],
            ['category_name' => 'Equipment', 'description' => 'Printers, laminators'],
        ];
        foreach ($categories as $cat) {
            $this->db->table('categories')->insert([
                'category_name' => $cat['category_name'],
                'description' => $cat['description'],
                'created_at' => Time::now(),
                'updated_at' => Time::now(),
            ]);
        }

        // Insert default expense categories
        $expenseCategories = [
            ['category_name' => 'Electricity', 'description' => 'Power bills'],
            ['category_name' => 'Internet', 'description' => 'Internet service'],
            ['category_name' => 'Staff Salary', 'description' => 'Employee salaries'],
            ['category_name' => 'Maintenance', 'description' => 'Equipment maintenance'],
            ['category_name' => 'Transport', 'description' => 'Transportation costs'],
            ['category_name' => 'Equipment Repair', 'description' => 'Repair costs'],
            ['category_name' => 'Rent', 'description' => 'Office/Shop rent'],
            ['category_name' => 'Consumables', 'description' => 'Office consumables'],
            ['category_name' => 'Other', 'description' => 'Miscellaneous expenses'],
        ];
        foreach ($expenseCategories as $cat) {
            $this->db->table('expense_categories')->insert([
                'category_name' => $cat['category_name'],
                'description' => $cat['description'],
                'created_at' => Time::now(),
                'updated_at' => Time::now(),
            ]);
        }

        // Insert default system settings
        $settings = [
            ['setting_key' => 'business_name', 'setting_value' => 'Innovative Graphics Design & Computer Solutions', 'setting_type' => 'string', 'group' => 'general'],
            ['setting_key' => 'business_address', 'setting_value' => 'Broad & Benson Streets, Metropolitan Building, Monrovia, Liberia', 'setting_type' => 'text', 'group' => 'general'],
            ['setting_key' => 'business_phone', 'setting_value' => '+231-778-651-747 / 880-770-689', 'setting_type' => 'string', 'group' => 'general'],
            ['setting_key' => 'business_email', 'setting_value' => 'info@innovativegraphics.com', 'setting_type' => 'string', 'group' => 'general'],
            ['setting_key' => 'default_currency', 'setting_value' => 'LRD', 'setting_type' => 'string', 'group' => 'currency'],
            ['setting_key' => 'currency_symbol_lrd', 'setting_value' => 'L$', 'setting_type' => 'string', 'group' => 'currency'],
            ['setting_key' => 'currency_symbol_usd', 'setting_value' => '$', 'setting_type' => 'string', 'group' => 'currency'],
            ['setting_key' => 'exchange_rate', 'setting_value' => '1.0', 'setting_type' => 'decimal', 'group' => 'currency'],
            ['setting_key' => 'low_stock_threshold', 'setting_value' => '10', 'setting_type' => 'integer', 'group' => 'inventory'],
            ['setting_key' => 'date_format', 'setting_value' => 'Y-m-d', 'setting_type' => 'string', 'group' => 'general'],
            ['setting_key' => 'time_format', 'setting_value' => 'H:i:s', 'setting_type' => 'string', 'group' => 'general'],
            ['setting_key' => 'session_timeout', 'setting_value' => '3600', 'setting_type' => 'integer', 'group' => 'security'],
        ];
        foreach ($settings as $setting) {
            $this->db->table('system_settings')->insert([
                'setting_key' => $setting['setting_key'],
                'setting_value' => $setting['setting_value'],
                'setting_type' => $setting['setting_type'],
                'group' => $setting['group'],
                'created_at' => Time::now(),
                'updated_at' => Time::now(),
            ]);
        }

        // Insert initial currency rate
        $this->db->table('currency_rates')->insert([
            'base_currency' => 'USD',
            'target_currency' => 'LRD',
            'rate' => '180.00',
            'date' => Time::now()->toDateString(),
            'created_at' => Time::now(),
        ]);
    }
}
