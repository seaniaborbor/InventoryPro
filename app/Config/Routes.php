<?php

namespace Config;

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */

// Default route
$routes->get('/', 'Auth::login');

// ==================== PUBLIC ROUTES (No Authentication) ====================
$routes->group('auth', function ($routes) {
    $routes->get('login', 'Auth::login');
    $routes->post('authenticate', 'Auth::authenticate');
    $routes->get('logout', 'Auth::logout');
    $routes->get('forgot-password', 'Auth::forgotPassword');
    $routes->post('send-reset-link', 'Auth::sendResetLink');
    $routes->get('reset-password/(:any)', 'Auth::resetPassword/$1');
    $routes->post('update-password', 'Auth::updatePassword');
    $routes->get('verify-2fa', 'Auth::verify2fa');
    $routes->post('verify-2fa-code', 'Auth::verify2faCode');
});

// ==================== PROTECTED ROUTES (Authentication Required) ====================
$routes->group('', ['filter' => 'auth'], function ($routes) {

    // Dashboard
    $routes->get('dashboard', 'Dashboard::index', ['filter' => 'permission:access_dashboard']);
    $routes->get('dashboard/get-data', 'Dashboard::getData', ['filter' => 'permission:access_dashboard']);

    // Profile
    $routes->group('profile', function ($routes) {
        $routes->get('/', 'Profile::index');
        $routes->post('update', 'Profile::update');
        $routes->get('change-password', 'Profile::changePassword');
        $routes->post('update-password', 'Profile::updatePassword');
        $routes->get('setup-2fa', 'Profile::setup2fa');
        $routes->post('enable-2fa', 'Profile::enable2fa');
        $routes->post('disable-2fa', 'Profile::disable2fa');
    });

    // Settings
    $routes->post('settings/set-currency', 'Settings::setCurrency');

    // User Guide
    $routes->get('guide', 'Guide::index');

    // Notifications
    $routes->group('notifications', function ($routes) {
        $routes->get('/', 'Notifications::index');
        $routes->get('get-unread', 'Notifications::getUnread');
        $routes->post('mark-read', 'Notifications::markRead');
    });

    // ==================== INVENTORY MODULE ====================
    $routes->group('inventory', ['filter' => 'permission:access_inventory,view_inventory,manage_products,manage_categories,manage_units,adjust_stock'], function ($routes) {
        $routes->get('products', 'Inventory::products');
        $routes->get('products/create', 'Inventory::createProduct', ['filter' => 'permission:manage_products']);
        $routes->get('products/view/(:num)', 'Inventory::view/$1');
        $routes->post('products/store', 'Inventory::storeProduct', ['filter' => 'permission:manage_products']);
        $routes->get('products/edit/(:num)', 'Inventory::editProduct/$1', ['filter' => 'permission:manage_products']);
        $routes->post('products/update/(:num)', 'Inventory::updateProduct/$1', ['filter' => 'permission:manage_products']);
        $routes->post('products/delete/(:num)', 'Inventory::deleteProduct/$1', ['filter' => 'permission:delete_records']);
        $routes->get('products/get-by-barcode/(:any)', 'Inventory::getProductByBarcode/$1');

        $routes->get('categories', 'Inventory::categories');
        $routes->post('categories/store', 'Inventory::storeCategory', ['filter' => 'permission:manage_categories']);
        $routes->post('categories/update/(:num)', 'Inventory::updateCategory/$1', ['filter' => 'permission:manage_categories']);
        $routes->post('categories/delete/(:num)', 'Inventory::deleteCategory/$1', ['filter' => 'permission:delete_records']);

        $routes->get('stock-adjustments', 'Inventory::stockAdjustments', ['filter' => 'permission:adjust_stock']);
        $routes->get('get-stock-adjustments', 'Inventory::getStockAdjustments', ['filter' => 'permission:adjust_stock']);
        $routes->post('stock-adjustments/adjust', 'Inventory::adjustStock', ['filter' => 'permission:adjust_stock']);
        $routes->get('stock-movements/(:num)', 'Inventory::stockMovements/$1');

        $routes->get('low-stock', 'Inventory::lowStock');

        $routes->get('units', 'Inventory::units');
        $routes->post('units/store', 'Inventory::storeUnit', ['filter' => 'permission:manage_units']);
        $routes->post('units/update/(:num)', 'Inventory::updateUnit/$1', ['filter' => 'permission:manage_units']);
        $routes->post('units/delete/(:num)', 'Inventory::deleteUnit/$1', ['filter' => 'permission:delete_records']);
    });

    // ==================== SALES MODULE ====================
    $routes->group('sales', ['filter' => 'permission:access_sales,manage_sales,create_sales'], function ($routes) {
        $routes->get('/', 'Sales::index');
        $routes->get('create', 'Sales::create', ['filter' => 'permission:create_sales,manage_sales']);
        $routes->post('store', 'Sales::store', ['filter' => 'permission:create_sales,manage_sales']);
        $routes->get('view/(:num)', 'Sales::view/$1');
        $routes->get('invoice/(:num)', 'Sales::invoice/$1');
        $routes->post('print/(:num)', 'Sales::print/$1');
        $routes->post('email/(:num)', 'Sales::email/$1');
        $routes->post('payment/(:num)', 'Sales::addPayment/$1');
        $routes->get('get-products', 'Sales::getProducts');
        $routes->post('calculate-total', 'Sales::calculateTotal');
    });

    // ==================== CUSTOMERS MODULE ====================
    $routes->group('customers', ['filter' => 'permission:access_customers,view_customers,manage_customers'], function ($routes) {
        $routes->get('/', 'Customers::index');
        $routes->post('store', 'Customers::store', ['filter' => 'permission:manage_customers']);
        $routes->get('edit/(:num)', 'Customers::edit/$1');
        $routes->post('update/(:num)', 'Customers::update/$1', ['filter' => 'permission:manage_customers']);
        $routes->post('delete/(:num)', 'Customers::delete/$1', ['filter' => 'permission:delete_records']);
        $routes->get('view/(:num)', 'Customers::view/$1');
        $routes->get('search', 'Customers::search');
    });

    // ==================== PURCHASES MODULE ====================
    $routes->group('purchases', ['filter' => 'permission:access_purchases,manage_purchases'], function ($routes) {
        $routes->get('/', 'Purchases::index');
        $routes->get('create', 'Purchases::create', ['filter' => 'permission:manage_purchases']);
        $routes->post('store', 'Purchases::store', ['filter' => 'permission:manage_purchases']);
        $routes->get('view/(:num)', 'Purchases::view/$1');
        $routes->post('receive/(:num)', 'Purchases::receive/$1', ['filter' => 'permission:manage_purchases']);
        $routes->post('payment/(:num)', 'Purchases::addPayment/$1', ['filter' => 'permission:manage_purchases']);
        $routes->get('get-products', 'Purchases::getProducts');
    });

    // ==================== SUPPLIERS MODULE ====================
    $routes->group('suppliers', ['filter' => 'permission:access_suppliers,manage_suppliers'], function ($routes) {
        $routes->get('/', 'Suppliers::index');
        $routes->post('store', 'Suppliers::store', ['filter' => 'permission:manage_suppliers']);
        $routes->get('edit/(:num)', 'Suppliers::edit/$1');
        $routes->post('update/(:num)', 'Suppliers::update/$1', ['filter' => 'permission:manage_suppliers']);
        $routes->post('delete/(:num)', 'Suppliers::delete/$1', ['filter' => 'permission:delete_records']);
        $routes->get('view/(:num)', 'Suppliers::view/$1');
        $routes->get('search', 'Suppliers::search');
    });

    // ==================== EXPENSES MODULE ====================
    $routes->group('expenses', ['filter' => 'permission:access_expenses,manage_expenses,manage_expense_categories'], function ($routes) {
        $routes->get('/', 'Expenses::index');
        $routes->get('create', 'Expenses::create', ['filter' => 'permission:manage_expenses']);
        $routes->post('store', 'Expenses::store', ['filter' => 'permission:manage_expenses']);
        $routes->get('edit/(:num)', 'Expenses::edit/$1', ['filter' => 'permission:manage_expenses']);
        $routes->post('update/(:num)', 'Expenses::update/$1', ['filter' => 'permission:manage_expenses']);
        $routes->post('delete/(:num)', 'Expenses::delete/$1', ['filter' => 'permission:delete_records']);
        $routes->get('categories', 'Expenses::categories');
        $routes->post('categories/store', 'Expenses::storeCategory', ['filter' => 'permission:manage_expense_categories']);
        $routes->post('categories/update/(:num)', 'Expenses::updateCategory/$1', ['filter' => 'permission:manage_expense_categories']);
        $routes->post('categories/delete/(:num)', 'Expenses::deleteCategory/$1', ['filter' => 'permission:delete_records']);
    });

    // ==================== PRODUCTION MODULE ====================
    $routes->group('production', ['filter' => 'permission:access_production,manage_production,manage_bom,record_stock_usage'], function ($routes) {
        $routes->get('jobs', 'Production::jobs');
        $routes->get('create', 'Production::create', ['filter' => 'permission:manage_production']);
        $routes->post('store', 'Production::store', ['filter' => 'permission:manage_production']);
        
        $routes->get('view/(:num)', 'Production::view/$1');
        $routes->get('edit/(:num)', 'Production::edit/$1');       // ← ADD THIS
        $routes->post('update/(:num)', 'Production::update/$1');  // ← ADD THIS
        $routes->post('complete/(:num)', 'Production::complete/$1');
        $routes->post('delete/(:num)', 'Production::delete/$1');
        $routes->post('cancel/(:num)', 'Production::cancel/$1');

        $routes->get('bom', 'Production::bom', ['filter' => 'permission:manage_bom']);
        $routes->get('bom/create', 'Production::createBom', ['filter' => 'permission:manage_bom']);
        $routes->post('bom/store', 'Production::storeBom', ['filter' => 'permission:manage_bom']);
        $routes->get('bom/edit/(:num)', 'Production::editBom/$1', ['filter' => 'permission:manage_bom']);
        $routes->post('bom/update/(:num)', 'Production::updateBom/$1', ['filter' => 'permission:manage_bom']);
        $routes->post('bom/delete/(:num)', 'Production::deleteBom/$1', ['filter' => 'permission:delete_records']);

        $routes->get('categories', 'Production::categories', ['filter' => 'permission:manage_production']);
        $routes->post('store-category', 'Production::storeCategory', ['filter' => 'permission:manage_production']);
        $routes->post('update-category/(:num)', 'Production::updateCategory/$1', ['filter' => 'permission:manage_production']);
        $routes->post('delete-category/(:num)', 'Production::deleteCategory/$1', ['filter' => 'permission:manage_production']);

        $routes->get('get-materials/(:num)', 'Production::getMaterials/$1');
    });

    // ==================== ADJUSTMENTS MODULE ====================
    $routes->group('adjustments', function ($routes) {
        $routes->get('/', 'Adjustments::index');
        $routes->get('create', 'Adjustments::create');
        $routes->post('store', 'Adjustments::store');
        $routes->get('view/(:num)', 'Adjustments::view/$1');
        $routes->get('edit/(:num)', 'Adjustments::edit/$1');
        $routes->post('update/(:num)', 'Adjustments::update/$1');
        $routes->post('delete/(:num)', 'Adjustments::delete/$1');
    });

    // ==================== REPORTS MODULE ====================
    $routes->group('reports', ['filter' => 'permission:view_reports'], function ($routes) {
        $routes->get('inventory', 'Reports::inventory');
        $routes->get('generate-inventory', 'Reports::generateInventory');
        $routes->get('inventory/export', 'Reports::exportInventory');

        $routes->get('sales', 'Reports::sales');
        $routes->get('sales/export', 'Reports::exportSales');
        $routes->get('customerHistory/(:num)', 'Reports::customerHistory/$1');
        $routes->get('sellerHistory/(:num)', 'Reports::sellerHistory/$1');
        $routes->get('productHistory/(:num)', 'Reports::productHistory/$1');

        $routes->get('financial', 'Reports::financial');
        $routes->get('financial/export', 'Reports::exportFinancial');

        $routes->get('production', 'Reports::production');
        $routes->get('production/export', 'Reports::exportProduction');

        $routes->get('stock-movement', 'Reports::stockMovement');
        $routes->get('profit-loss', 'Reports::profitLoss');

        $routes->post('export', 'Reports::export');
    });

    // ==================== ADMIN MODULE (Admin Only) ====================
    $routes->group('admin', ['filter' => 'permission:access_admin,manage_users,manage_roles,manage_settings,view_audit_logs,manage_backup,view_system_info'], function ($routes) {
        // User Management
        $routes->get('users', 'Admin::users', ['filter' => 'permission:manage_users']);
        $routes->get('users/view/(:num)', 'Admin::viewUser/$1', ['filter' => 'permission:manage_users']);
        $routes->post('users/create', 'Admin::createUser', ['filter' => 'permission:manage_users']);
        $routes->get('users/edit/(:num)', 'Admin::editUser/$1', ['filter' => 'permission:manage_users']);
        $routes->post('users/update/(:num)', 'Admin::updateUser/$1', ['filter' => 'permission:manage_users']);
        $routes->post('users/delete/(:num)', 'Admin::deleteUser/$1', ['filter' => 'permission:all:manage_users,delete_records']);

        // Role Management
        $routes->get('roles', 'Admin::roles', ['filter' => 'permission:manage_roles']);
        $routes->get('permissions', 'Admin::permissionMatrix', ['filter' => 'permission:manage_roles']);
        $routes->post('permissions', 'Admin::updatePermissionMatrix', ['filter' => 'permission:manage_roles']);
        $routes->get('roles/permissions/(:num)', 'Admin::editRolePermissions/$1', ['filter' => 'permission:manage_roles']);
        $routes->post('roles/permissions/(:num)', 'Admin::updateRolePermissions/$1', ['filter' => 'permission:manage_roles']);

        // System Settings
        $routes->get('settings', 'Admin::settings', ['filter' => 'permission:manage_settings']);
        $routes->post('settings/update', 'Admin::updateSettings', ['filter' => 'permission:manage_settings']);

        // Backup Management
        $routes->get('backup', 'Admin::backup', ['filter' => 'permission:manage_backup']);
        $routes->get('backup/download/(:any)', 'Admin::downloadBackup/$1', ['filter' => 'permission:manage_backup']);
        $routes->post('backup/create', 'Admin::createBackup', ['filter' => 'permission:manage_backup']);
        $routes->post('backup/restore/(:any)', 'Admin::restoreBackup/$1', ['filter' => 'permission:manage_backup']);
        $routes->post('backup/delete/(:any)', 'Admin::deleteBackup/$1', ['filter' => 'permission:all:manage_backup,delete_records']);

        // Audit Logs
        $routes->get('audit-logs', 'Admin::auditLogs', ['filter' => 'permission:view_audit_logs']);
        $routes->get('audit-logs/export', 'Admin::exportAuditLogs', ['filter' => 'permission:view_audit_logs']);

        // System Info
        $routes->get('system-info', 'Admin::systemInfo', ['filter' => 'permission:view_system_info']);
    });
});

// ==================== API ROUTES (AJAX Endpoints) ====================
$routes->group('api', ['filter' => 'auth'], function ($routes) {
    // Products API
    $routes->get('products', 'Api\Products::index');
    $routes->get('products/(:num)', 'Api\Products::show/$1');
    $routes->post('products/search', 'Api\Products::search');

    // Sales API
    $routes->get('sales/today', 'Api\Sales::today');
    $routes->get('sales/monthly', 'Api\Sales::monthly');

    // Dashboard API
    $routes->get('dashboard/stats', 'Api\Dashboard::stats');
    $routes->get('dashboard/charts', 'Api\Dashboard::charts');
});

// ==================== ERROR HANDLING ====================
$routes->set404Override(function () {
    return view('errors/404');
});

// ==================== TESTING ROUTES (Remove in production) ====================
if (ENVIRONMENT === 'development') {
    $routes->get('test/db', 'Test::database');
    $routes->get('test/email', 'Test::email');
}
