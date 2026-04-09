<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=yes">
    <meta name="description" content="Innovative Graphics Inventory Management System">
    <title><?= $title ?? 'Inventory Management System' ?> | Innovative Graphics</title>
    
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
    <!-- DataTables -->
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>
    <!-- SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <!-- HTML5 QR Code Scanner -->
    <script src="https://unpkg.com/html5-qrcode@2.3.8/html5-qrcode.min.js"></script>
    
    <!-- Custom CSS -->
    <link rel="stylesheet" href="<?= base_url('assets/css/main.css') ?>">
    
    <style>
        :root {
            --primary-color: #0d6efd;
            --secondary-color: #6c757d;
            --success-color: #198754;
            --danger-color: #dc3545;
            --warning-color: #ffc107;
            --info-color: #0dcaf0;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f8f9fa;
        }
        
        .wrapper {
            display: flex;
            width: 100%;
            align-items: stretch;
        }
        
        /* Sidebar Styles */
        #sidebar {
            min-width: 250px;
            max-width: 250px;
            background: #1e293b;
            color: #fff;
            transition: all 0.3s;
            position: fixed;
            height: 100vh;
            z-index: 1000;
        }
        
        #sidebar.active {
            margin-left: -250px;
        }
        
        #sidebar .sidebar-header {
            padding: 20px;
            background: rgba(0, 0, 0, 0.2);
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        }
        
        #sidebar .sidebar-header h3 {
            font-size: 1.2rem;
            margin-bottom: 0;
        }
        
        #sidebar .sidebar-header p {
            font-size: 0.8rem;
            margin-bottom: 0;
            opacity: 0.8;
        }
        
        #sidebar ul.components {
            padding: 20px 0;
        }
        
        #sidebar ul li a {
            padding: 12px 20px;
            font-size: 1rem;
            display: block;
            color: #fff;
            text-decoration: none;
            transition: all 0.3s;
        }
        
        #sidebar ul li a:hover {
            background: rgba(255, 255, 255, 0.2);
            padding-left: 25px;
        }
        
        #sidebar ul li.active > a {
            background: rgba(255, 255, 255, 0.3);
            border-left: 4px solid #fff;
        }
        
        #sidebar ul li ul li.active > a {
            background: rgba(255, 255, 255, 0.2);
            border-left: 4px solid rgba(255, 255, 255, 0.7);
        }
        
        #sidebar ul li a i {
            margin-right: 10px;
            width: 20px;
        }
        
        /* Sub-menu indentation */
        #sidebar ul li ul li a {
            padding-left: 40px;
            font-size: 0.9rem;
        }
        
        #sidebar ul li ul li a:hover {
            padding-left: 45px;
        }
        
        /* Content Styles */
        #content {
            width: 100%;
            margin-left: 250px;
            transition: all 0.3s;
            min-height: 100vh;
        }
        
        #content.active {
            margin-left: 0;
        }
        
        /* Navbar Styles */
        .navbar {
            padding: 15px 20px;
            background: #fff;
            border-bottom: 1px solid #e0e0e0;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        
        .navbar .btn-toggle {
            background: none;
            border: none;
            font-size: 1.5rem;
            color: #333;
        }
        
        /* Card Styles */
        .card {
            border: none;
            border-radius: 10px;
            box-shadow: 0 0 20px rgba(0,0,0,0.08);
            transition: transform 0.3s;
        }
        
        .card:hover {
            transform: translateY(-5px);
            box-shadow: 0 5px 25px rgba(0,0,0,0.1);
        }
        
        .card-stat {
            background: #0078d4;
            color: white;
        }
        
        /* Dashboard Widgets */
        .dashboard-widget {
            padding: 20px;
            border-radius: 10px;
            margin-bottom: 20px;
        }
        
        .widget-icon {
            font-size: 2.5rem;
            opacity: 0.7;
        }
        
        /* Table Styles */
        .table-responsive {
            border-radius: 10px;
            overflow: hidden;
        }
        
        .dataTables_wrapper .dataTables_paginate .paginate_button {
            padding: 0.5rem 1rem;
            margin: 0 0.2rem;
            border-radius: 5px;
        }
        
        /* Notification Styles */
        .notification-dropdown {
            width: 350px;
            max-height: 500px;
            overflow-y: auto;
        }
        
        .notification-item {
            padding: 10px 15px;
            border-bottom: 1px solid #e0e0e0;
            transition: background 0.3s;
        }
        
        .notification-item:hover {
            background: #f8f9fa;
        }
        
        .notification-item.unread {
            background: #e7f3ff;
        }
        
        .notification-item .notification-title {
            font-weight: 600;
            margin-bottom: 5px;
        }
        
        .notification-item .notification-message {
            font-size: 0.85rem;
            color: #666;
        }
        
        .notification-item .notification-time {
            font-size: 0.7rem;
            color: #999;
        }
        
        /* Responsive */
        @media (max-width: 768px) {
            #sidebar {
                margin-left: -250px;
            }
            #sidebar.active {
                margin-left: 0;
            }
            #content {
                margin-left: 0;
            }
            #content.active {
                margin-left: 250px;
            }
        }
        
        /* Loading Spinner */
        .loader {
            display: inline-block;
            width: 20px;
            height: 20px;
            border: 3px solid #f3f3f3;
            border-top: 3px solid #3498db;
            border-radius: 50%;
            animation: spin 1s linear infinite;
        }
        
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
        
        /* Toast Notifications */
        .toast-container {
            position: fixed;
            top: 20px;
            right: 20px;
            z-index: 1050;
        }
        
        /* Print Styles */
        @media print {
            #sidebar, .navbar, .btn, .no-print {
                display: none !important;
            }
            #content {
                margin-left: 0 !important;
                padding: 0 !important;
            }
            .card {
                box-shadow: none !important;
                border: 1px solid #ddd !important;
            }
        }
    </style>
</head>
<body>
    <div class="wrapper">
        <!-- Sidebar -->
        <nav id="sidebar">
            <div class="sidebar-header">
                <h3>Innovative Graphics</h3>
                <p>Inventory Management System</p>
            </div>
            
            <ul class="list-unstyled components">
                <?php if (can_access_module('dashboard')): ?>
                <li class="<?= $activePage == 'dashboard' ? 'active' : '' ?>">
                    <a href="<?= base_url('dashboard') ?>">
                        <i class="bi bi-speedometer2"></i> Dashboard
                    </a>
                </li>
                <?php endif; ?>
                
                <?php if (can_access_module('inventory')): ?>
                <li class="<?= $activePage == 'inventory' ? 'active' : '' ?>">
                    <a href="#inventorySubmenu" data-bs-toggle="collapse" aria-expanded="<?= $activePage == 'inventory' ? 'true' : 'false' ?>" class="dropdown-toggle">
                        <i class="bi bi-box-seam"></i> Inventory
                    </a>
                    <ul class="collapse list-unstyled <?= $activePage == 'inventory' ? 'show' : '' ?>" id="inventorySubmenu">
                        <li class="<?= ($activeSubPage ?? '') == 'products' ? 'active' : '' ?>"><a href="<?= base_url('inventory/products') ?>"><i class="bi bi-grid"></i> Products</a></li>
                        <?php if (has_permission('manage_categories')): ?>
                        <li class="<?= ($activeSubPage ?? '') == 'categories' ? 'active' : '' ?>"><a href="<?= base_url('inventory/categories') ?>"><i class="bi bi-tags"></i> Categories</a></li>
                        <?php endif; ?>
                        <?php if (has_permission('adjust_stock')): ?>
                        <li class="<?= ($activeSubPage ?? '') == 'stock-adjustments' ? 'active' : '' ?>"><a href="<?= base_url('inventory/stock-adjustments') ?>"><i class="bi bi-arrow-left-right"></i> Stock Adjustments</a></li>
                        <?php endif; ?>
                        <li class="<?= ($activeSubPage ?? '') == 'low-stock' ? 'active' : '' ?>"><a href="<?= base_url('inventory/low-stock') ?>"><i class="bi bi-exclamation-triangle"></i> Low Stock Alerts</a></li>
                    </ul>
                </li>
                <?php endif; ?>
                
                <?php if (can_access_module('purchases') || can_access_module('suppliers')): ?>
                <li class="<?= $activePage == 'purchases' ? 'active' : '' ?>">
                    <a href="#purchaseSubmenu" data-bs-toggle="collapse" aria-expanded="<?= $activePage == 'purchases' ? 'true' : 'false' ?>" class="dropdown-toggle">
                        <i class="bi bi-cart-plus"></i> Purchases
                    </a>
                    <ul class="collapse list-unstyled <?= $activePage == 'purchases' ? 'show' : '' ?>" id="purchaseSubmenu">
                        <?php if (can_access_module('purchases')): ?>
                        <li class="<?= ($activeSubPage ?? '') == 'purchases' ? 'active' : '' ?>"><a href="<?= base_url('purchases') ?>"><i class="bi bi-list"></i> All Purchases</a></li>
                        <?php endif; ?>
                        <?php if (has_permission('manage_purchases')): ?>
                        <li class="<?= ($activeSubPage ?? '') == 'create' ? 'active' : '' ?>"><a href="<?= base_url('purchases/create') ?>"><i class="bi bi-plus-circle"></i> New Purchase</a></li>
                        <?php endif; ?>
                        <?php if (can_access_module('suppliers')): ?>
                        <li class="<?= ($activeSubPage ?? '') == 'suppliers' ? 'active' : '' ?>"><a href="<?= base_url('suppliers') ?>"><i class="bi bi-truck"></i> Suppliers</a></li>
                        <?php endif; ?>
                    </ul>
                </li>
                <?php endif; ?>
                
                <?php if (can_access_module('sales') || can_access_module('customers')): ?>
                <li class="<?= $activePage == 'sales' ? 'active' : '' ?>">
                    <a href="#salesSubmenu" data-bs-toggle="collapse" aria-expanded="<?= $activePage == 'sales' ? 'true' : 'false' ?>" class="dropdown-toggle">
                        <i class="bi bi-cart"></i> Sales
                    </a>
                    <ul class="collapse list-unstyled <?= $activePage == 'sales' ? 'show' : '' ?>" id="salesSubmenu">
                        <?php if (can_access_module('sales')): ?>
                        <li class="<?= ($activeSubPage ?? '') == 'sales' ? 'active' : '' ?>"><a href="<?= base_url('sales') ?>"><i class="bi bi-list"></i> All Sales</a></li>
                        <?php endif; ?>
                        <?php if (has_permission('create_sales') || has_permission('manage_sales')): ?>
                        <li class="<?= ($activeSubPage ?? '') == 'create' ? 'active' : '' ?>"><a href="<?= base_url('sales/create') ?>"><i class="bi bi-plus-circle"></i> New Sale</a></li>
                        <?php endif; ?>
                        <?php if (can_access_module('customers')): ?>
                        <li class="<?= ($activeSubPage ?? '') == 'customers' ? 'active' : '' ?>"><a href="<?= base_url('customers') ?>"><i class="bi bi-people"></i> Customers</a></li>
                        <?php endif; ?>
                    </ul>
                </li>
                <?php endif; ?>
                
                <?php if (can_access_module('production')): ?>
                <li class="<?= $activePage == 'production' ? 'active' : '' ?>">
                    <a href="#productionSubmenu" data-bs-toggle="collapse" aria-expanded="<?= $activePage == 'production' ? 'true' : 'false' ?>" class="dropdown-toggle">
                        <i class="bi bi-gear"></i> Production
                    </a>
                    <ul class="collapse list-unstyled <?= $activePage == 'production' ? 'show' : '' ?>" id="productionSubmenu">
                        <li class="<?= ($activeSubPage ?? '') == 'jobs' ? 'active' : '' ?>"><a href="<?= base_url('production/jobs') ?>"><i class="bi bi-list"></i> Production Jobs</a></li>
                        <?php if (has_permission('manage_production')): ?>
                        <li class="<?= ($activeSubPage ?? '') == 'create' ? 'active' : '' ?>"><a href="<?= base_url('production/create') ?>"><i class="bi bi-plus-circle"></i> New Job</a></li>
                        <li class="<?= ($activeSubPage ?? '') == 'categories' ? 'active' : '' ?>"><a href="<?= base_url('production/categories') ?>"><i class="bi bi-tags"></i> Categories</a></li>
                        <?php endif; ?>
                        <?php if (has_permission('manage_bom')): ?>
                        <li class="<?= ($activeSubPage ?? '') == 'bom' ? 'active' : '' ?>"><a href="<?= base_url('production/bom') ?>"><i class="bi bi-file-text"></i> Bill of Materials</a></li>
                        <?php endif; ?>
                    </ul>
                </li>
                <?php endif; ?>

                <?php if (can_access_module('adjustments') || can_access_module('inventory')): ?>
                <li class="<?= $activePage == 'adjustments' ? 'active' : '' ?>">
                    <a href="<?= base_url('adjustments') ?>"><i class="bi bi-arrow-down-up"></i> Adjustments</a>
                </li>
                <?php endif; ?>

                <?php if (can_access_module('expenses')): ?>
                <li class="<?= $activePage == 'expenses' ? 'active' : '' ?>">
                    <a href="#expenseSubmenu" data-bs-toggle="collapse" aria-expanded="<?= $activePage == 'expenses' ? 'true' : 'false' ?>" class="dropdown-toggle">
                        <i class="bi bi-wallet2"></i> Expenses
                    </a>
                    <ul class="collapse list-unstyled <?= $activePage == 'expenses' ? 'show' : '' ?>" id="expenseSubmenu">
                        <li class="<?= ($activeSubPage ?? '') == 'expenses' ? 'active' : '' ?>"><a href="<?= base_url('expenses') ?>"><i class="bi bi-list"></i> All Expenses</a></li>
                        <?php if (has_permission('manage_expenses')): ?>
                        <li class="<?= ($activeSubPage ?? '') == 'create' ? 'active' : '' ?>"><a href="<?= base_url('expenses/create') ?>"><i class="bi bi-plus-circle"></i> Add Expense</a></li>
                        <?php endif; ?>
                        <?php if (has_permission('manage_expense_categories')): ?>
                        <li class="<?= ($activeSubPage ?? '') == 'categories' ? 'active' : '' ?>"><a href="<?= base_url('expenses/categories') ?>"><i class="bi bi-tag"></i> Categories</a></li>
                        <?php endif; ?>
                    </ul>
                </li>
                <?php endif; ?>
                
                <?php if (can_access_module('reports')): ?>
                <li class="<?= $activePage == 'reports' ? 'active' : '' ?>">
                    <a href="#reportsSubmenu" data-bs-toggle="collapse" aria-expanded="<?= $activePage == 'reports' ? 'true' : 'false' ?>" class="dropdown-toggle">
                        <i class="bi bi-graph-up"></i> Reports
                    </a>
                    <ul class="collapse list-unstyled <?= $activePage == 'reports' ? 'show' : '' ?>" id="reportsSubmenu">
                        <li class="<?= ($activeSubPage ?? '') == 'inventory' ? 'active' : '' ?>"><a href="<?= base_url('reports/inventory') ?>"><i class="bi bi-box"></i> Inventory Reports</a></li>
                        <li class="<?= ($activeSubPage ?? '') == 'sales' ? 'active' : '' ?>"><a href="<?= base_url('reports/sales') ?>"><i class="bi bi-cart"></i> Sales Reports</a></li>
                        <li class="<?= ($activeSubPage ?? '') == 'financial' ? 'active' : '' ?>"><a href="<?= base_url('reports/financial') ?>"><i class="bi bi-currency-dollar"></i> Financial Reports</a></li>
                        <li class="<?= ($activeSubPage ?? '') == 'production' ? 'active' : '' ?>"><a href="<?= base_url('reports/production') ?>"><i class="bi bi-gear"></i> Production Reports</a></li>
                    </ul>
                </li>
                <?php endif; ?>
                
                <?php if (can_access_module('admin')): ?>
                <li class="<?= $activePage == 'settings' ? 'active' : '' ?>">
                    <a href="#settingsSubmenu" data-bs-toggle="collapse" aria-expanded="false" class="dropdown-toggle">
                        <i class="bi bi-gear"></i> Administration
                    </a>
                    <ul class="collapse list-unstyled" id="settingsSubmenu">
                        <?php if (has_permission('manage_users')): ?>
                        <li><a href="<?= base_url('admin/users') ?>"><i class="bi bi-people"></i> User Management</a></li>
                        <?php endif; ?>
                        <?php if (has_permission('manage_roles')): ?>
                        <li><a href="<?= base_url('admin/permissions') ?>"><i class="bi bi-shield-lock"></i> Role Permissions</a></li>
                        <?php endif; ?>
                        <?php if (has_permission('manage_settings')): ?>
                        <li><a href="<?= base_url('admin/settings') ?>"><i class="bi bi-sliders2"></i> System Settings</a></li>
                        <?php endif; ?>
                        <?php if (has_permission('manage_backup')): ?>
                        <li><a href="<?= base_url('admin/backup') ?>"><i class="bi bi-database"></i> Backup</a></li>
                        <?php endif; ?>
                        <?php if (has_permission('view_audit_logs')): ?>
                        <li><a href="<?= base_url('admin/audit-logs') ?>"><i class="bi bi-journal"></i> Audit Logs</a></li>
                        <?php endif; ?>
                        <?php if (has_permission('view_system_info')): ?>
                        <li><a href="<?= base_url('admin/system-info') ?>"><i class="bi bi-pc-display"></i> System Info</a></li>
                        <?php endif; ?>
                    </ul>
                </li>
                <?php endif; ?>

                <li class="<?= $activePage == 'guide' ? 'active' : '' ?>">
                    <a href="<?= base_url('guide') ?>"><i class="bi bi-journal-bookmark"></i> User Guide</a>
                </li>
            </ul>
        </nav>
        
        <!-- Page Content -->
        <div id="content">
            <!-- Navbar -->
            <nav class="navbar navbar-expand-lg">
                <div class="container-fluid">
                    <button type="button" id="sidebarCollapse" class="btn btn-toggle">
                        <i class="bi bi-list"></i>
                    </button>
                    
                    <div class="ms-auto d-flex align-items-center">
                        <!-- Currency Selector -->
                        <div class="dropdown me-3">
                            <button class="btn btn-outline-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown">
                                <i class="bi bi-currency-exchange"></i> 
                                <?= session()->get('display_currency') ?? 'LRD' ?>
                            </button>
                            <ul class="dropdown-menu">
                                <li><a class="dropdown-item currency-selector" href="#" data-currency="LRD">LRD (Liberian Dollar)</a></li>
                                <li><a class="dropdown-item currency-selector" href="#" data-currency="USD">USD (US Dollar)</a></li>
                            </ul>
                        </div>
                        
                        <!-- Notifications -->
                        <div class="dropdown me-3">
                            <button class="btn btn-outline-secondary position-relative" type="button" data-bs-toggle="dropdown">
                                <i class="bi bi-bell"></i>
                                <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger" id="notificationBadge">
                                    0
                                </span>
                            </button>
                            <div class="dropdown-menu dropdown-menu-end notification-dropdown" id="notificationDropdown">
                                <h6 class="dropdown-header">Notifications</h6>
                                <div id="notificationList">
                                    <div class="text-center p-3">Loading...</div>
                                </div>
                                <div class="dropdown-divider"></div>
                                <a class="dropdown-item text-center" href="<?= base_url('notifications') ?>">View All</a>
                            </div>
                        </div>
                        
                        <!-- User Menu -->
                        <div class="dropdown">
                            <button class="btn btn-outline-primary dropdown-toggle" type="button" data-bs-toggle="dropdown">
                                <i class="bi bi-person-circle"></i> <?= session()->get('full_name') ?? 'User' ?>
                            </button>
                            <ul class="dropdown-menu dropdown-menu-end">
                                <li><a class="dropdown-item" href="<?= base_url('profile') ?>"><i class="bi bi-person"></i> My Profile</a></li>
                                <li><a class="dropdown-item" href="<?= base_url('profile/change-password') ?>"><i class="bi bi-key"></i> Change Password</a></li>
                                <li><hr class="dropdown-divider"></li>
                                <li><a class="dropdown-item text-danger" href="<?= base_url('auth/logout') ?>"><i class="bi bi-box-arrow-right"></i> Logout</a></li>
                            </ul>
                        </div>
                    </div>
                </div>
            </nav>
            
            <!-- Main Content -->
            <div class="container-fluid p-4">
                <?= $this->renderSection('content') ?>
            </div>
        </div>
    </div>
    
    <!-- Toast Container -->
    <div class="toast-container"></div>
    
    <!-- Bootstrap JS Bundle -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- Custom JS -->
    <script src="<?= base_url('assets/js/custom.js') ?>"></script>
    
    <script>
        $(document).ready(function() {
            // Sidebar Toggle
            $('#sidebarCollapse').on('click', function() {
                $('#sidebar').toggleClass('active');
                $('#content').toggleClass('active');
            });
            
            // Currency Selector
            $('.currency-selector').on('click', function(e) {
                e.preventDefault();
                var currency = $(this).data('currency');
                $.ajax({
                    url: '<?= base_url('settings/set-currency') ?>',
                    type: 'POST',
                    data: {currency: currency},
                    success: function(response) {
                        if (response.status === 'success') {
                            location.reload();
                        }
                    }
                });
            });
            
            // Load Notifications
            loadNotifications();
            
            // Auto-refresh notifications every 30 seconds
            setInterval(loadNotifications, 30000);
        });
        
        function loadNotifications() {
            $.ajax({
                url: '<?= base_url('notifications/get-unread') ?>',
                type: 'GET',
                dataType: 'json',
                success: function(response) {
                    if (response.status === 'success') {
                        var count = response.data.count;
                        var notifications = response.data.notifications;
                        
                        // Update badge
                        $('#notificationBadge').text(count);
                        if (count > 0) {
                            $('#notificationBadge').show();
                        } else {
                            $('#notificationBadge').hide();
                        }
                        
                        // Update dropdown
                        var html = '';
                        if (notifications.length > 0) {
                            $.each(notifications, function(i, notif) {
                                html += `
                                    <div class="notification-item ${notif.is_read ? '' : 'unread'}" data-id="${notif.id}">
                                        <div class="notification-title">${notif.title}</div>
                                        <div class="notification-message">${notif.message}</div>
                                        <div class="notification-time">${timeAgo(notif.created_at)}</div>
                                    </div>
                                `;
                            });
                        } else {
                            html = '<div class="text-center p-3">No new notifications</div>';
                        }
                        $('#notificationList').html(html);
                        
                        // Add click handler for notifications
                        $('.notification-item').on('click', function() {
                            var id = $(this).data('id');
                            markAsRead(id);
                        });
                    }
                }
            });
        }
        
        function markAsRead(id) {
            $.ajax({
                url: '<?= base_url('notifications/mark-read') ?>',
                type: 'POST',
                data: {id: id},
                success: function(response) {
                    if (response.status === 'success') {
                        loadNotifications();
                    }
                }
            });
        }
        
        function timeAgo(date) {
            var seconds = Math.floor((new Date() - new Date(date)) / 1000);
            var intervals = [
                {label: 'year', seconds: 31536000},
                {label: 'month', seconds: 2592000},
                {label: 'week', seconds: 604800},
                {label: 'day', seconds: 86400},
                {label: 'hour', seconds: 3600},
                {label: 'minute', seconds: 60},
                {label: 'second', seconds: 1}
            ];
            
            for (var i = 0; i < intervals.length; i++) {
                var interval = intervals[i];
                var count = Math.floor(seconds / interval.seconds);
                if (count > 0) {
                    return count + ' ' + interval.label + (count > 1 ? 's' : '') + ' ago';
                }
            }
            return 'just now';
        }
        
        // Global AJAX error handler
        $(document).ajaxError(function(event, xhr, settings, error) {
            if (xhr.status === 401) {
                window.location.href = '<?= base_url('auth/login') ?>';
            } else {
                showToast('Error', 'An error occurred. Please try again.', 'error');
            }
        });
        
        // Toast notification function
        function showToast(title, message, type = 'success') {
            var bgColor = type === 'success' ? '#28a745' : type === 'error' ? '#dc3545' : '#ffc107';
            var icon = type === 'success' ? 'check-circle' : type === 'error' ? 'exclamation-circle' : 'info-circle';
            
            var toast = `
                <div class="toast align-items-center text-white border-0 mb-2" role="alert" aria-live="assertive" aria-atomic="true" style="background-color: ${bgColor}">
                    <div class="d-flex">
                        <div class="toast-body">
                            <i class="bi bi-${icon} me-2"></i>
                            <strong>${title}</strong><br>
                            ${message}
                        </div>
                        <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
                    </div>
                </div>
            `;
            
            $('.toast-container').append(toast);
            var toastElement = $('.toast').last();
            var bsToast = new bootstrap.Toast(toastElement[0], {autohide: true, delay: 3000});
            bsToast.show();
            
            toastElement.on('hidden.bs.toast', function() {
                $(this).remove();
            });
        }
        
        // Confirmation dialog
        function confirmAction(message, callback) {
            Swal.fire({
                title: 'Are you sure?',
                text: message,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Yes, proceed!'
            }).then((result) => {
                if (result.isConfirmed) {
                    callback();
                }
            });
        }
    </script>
    
    <?= $this->renderSection('scripts') ?>
</body>
</html>
