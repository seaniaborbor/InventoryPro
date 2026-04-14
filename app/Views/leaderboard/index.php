<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<div class="d-flex flex-column flex-lg-row justify-content-between align-items-lg-center gap-3 mb-4">
    <div>
        <h2 class="mb-1"><i class="bi bi-trophy-fill me-2 text-warning"></i>Leaderboard & Activity Dashboard</h2>
        <p class="text-muted mb-0">Discover your top customers, products, staff, and most active users on the platform.</p>
    </div>

    <form id="leaderboardFilters" class="row g-2 align-items-end">
        <div class="col-md-4 col-lg-3">
            <label for="period" class="form-label mb-1">Period</label>
            <select class="form-select" id="period" name="period">
                <option value="last_7_days" <?= $period === 'last_7_days' ? 'selected' : '' ?>>Last 7 Days</option>
                <option value="last_30_days" <?= $period === 'last_30_days' ? 'selected' : '' ?>>Last 30 Days</option>
                <option value="last_90_days" <?= $period === 'last_90_days' ? 'selected' : '' ?>>Last 90 Days</option>
                <option value="last_year" <?= $period === 'last_year' ? 'selected' : '' ?>>Last 12 Months</option>
                <option value="all" <?= $period === 'all' ? 'selected' : '' ?>>All Time</option>
                <option value="custom" <?= $period === 'custom' ? 'selected' : '' ?>>Custom Range</option>
            </select>
        </div>
        <div class="col-md-3 col-lg-3 custom-date-wrap" style="display: <?= $period === 'custom' ? 'block' : 'none' ?>;">
            <label for="startDate" class="form-label mb-1">Start Date</label>
            <input type="date" class="form-control" id="startDate" name="start_date" value="<?= esc($startDate) ?>">
        </div>
        <div class="col-md-3 col-lg-3 custom-date-wrap" style="display: <?= $period === 'custom' ? 'block' : 'none' ?>;">
            <label for="endDate" class="form-label mb-1">End Date</label>
            <input type="date" class="form-control" id="endDate" name="end_date" value="<?= esc($endDate) ?>">
        </div>
        <div class="col-md-4 col-lg-3">
            <button type="submit" class="btn btn-primary w-100">
                <i class="bi bi-funnel"></i> Apply Filter
            </button>
        </div>
    </form>
</div>

<div class="alert alert-info border mb-4">
    <strong id="periodLabel"><?= esc($periodLabel) ?></strong> 
    <span class="text-muted">| Last updated: <span id="updatedAt">-</span></span>
</div>

<!-- Row 1: Top Customers -->
<div class="row g-3 mb-4">
    <div class="col-12">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white">
                <h5 class="mb-0"><i class="bi bi-people-fill me-2 text-primary"></i>🏆 Top Customers</h5>
                <small class="text-muted">Based on purchase value and production jobs</small>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover align-middle" id="topCustomersTable" width="100%">
                        <thead>
                            <tr>
                                <th>Rank</th>
                                <th>Customer</th>
                                <th>Sales Value</th>
                                <th>Sales Count</th>
                                <th>Production Value</th>
                                <th>Production Count</th>
                                <th>Total Value</th>
                                <th>Total Transactions</th>
                            </thead>
                        <tbody>
                            <tr><td colspan="8" class="text-center">Loading...</td></tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Row 2: Top Products -->
<div class="row g-3 mb-4">
    <div class="col-12">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white">
                <h5 class="mb-0"><i class="bi bi-box-seam-fill me-2 text-success"></i>🏆 Top Products</h5>
                <small class="text-muted">Based on sales quantity and production usage</small>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover align-middle" id="topProductsTable" width="100%">
                        <thead>
                            <tr>
                                <th>Rank</th>
                                <th>Product</th>
                                <th>SKU</th>
                                <th>Sold Qty</th>
                                <th>Sales Value</th>
                                <th>Used Qty</th>
                                <th>Production Value</th>
                                <th>Total Qty</th>
                                <th>Total Value</th>
                            </thead>
                        <tbody>
                            <tr><td colspan="9" class="text-center">Loading...</td></tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Row 3: Top Staff -->
<div class="row g-3 mb-4">
    <div class="col-12">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white">
                <h5 class="mb-0"><i class="bi bi-person-workspace me-2 text-warning"></i>🏆 Top Performing Staff</h5>
                <small class="text-muted">Based on sales and production activities</small>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover align-middle" id="topStaffTable" width="100%">
                        <thead>
                            <tr>
                                <th>Rank</th>
                                <th>Staff</th>
                                <th>Sales Value</th>
                                <th>Sales Count</th>
                                <th>Production Value</th>
                                <th>Production Count</th>
                                <th>Total Value</th>
                                <th>Total Transactions</th>
                            </thead>
                        <tbody>
                            <tr><td colspan="8" class="text-center">Loading...</td></tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Row 4: Top Users by Activity (Audit Logs) -->
<div class="row g-3 mb-4">
    <div class="col-12">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white">
                <h5 class="mb-0"><i class="bi bi-activity me-2 text-danger"></i>🏆 Most Active Users</h5>
                <small class="text-muted">Based on audit log activities (creates, updates, deletes, logins, exports)</small>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover align-middle" id="topUsersTable" width="100%">
                        <thead>
                            <tr>
                                <th>Rank</th>
                                <th>User</th>
                                <th>Total Activities</th>
                                <th>Creates</th>
                                <th>Updates</th>
                                <th>Deletes</th>
                                <th>Logins</th>
                                <th>Exports</th>
                                <th>Activity %</th>
                            </thead>
                        <tbody>
                            <tr><td colspan="9" class="text-center">Loading...</td></tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.rank-1 { background-color: #ffd70020; border-left: 4px solid #ffd700; }
.rank-2 { background-color: #c0c0c020; border-left: 4px solid #c0c0c0; }
.rank-3 { background-color: #cd7f3220; border-left: 4px solid #cd7f32; }
.medal { font-size: 1.3rem; }
</style>

<script>
// Destroy existing DataTables before reinitializing
let topCustomersTable = null;
let topProductsTable = null;
let topStaffTable = null;
let topUsersTable = null;

function formatCurrency(amount, currency = 'LRD') {
    const symbol = currency === 'USD' ? '$' : 'L$';
    return `${symbol} ${parseFloat(amount || 0).toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 })}`;
}

function formatNumber(value) {
    return parseFloat(value || 0).toLocaleString('en-US', { maximumFractionDigits: 2 });
}

function getRankIcon(rank) {
    if (rank === 1) return '🥇';
    if (rank === 2) return '🥈';
    if (rank === 3) return '🥉';
    return `${rank}.`;
}

function getRankClass(rank) {
    if (rank === 1) return 'rank-1';
    if (rank === 2) return 'rank-2';
    if (rank === 3) return 'rank-3';
    return '';
}

function destroyDataTables() {
    if (topCustomersTable) {
        try { topCustomersTable.destroy(); } catch(e) {}
        topCustomersTable = null;
    }
    if (topProductsTable) {
        try { topProductsTable.destroy(); } catch(e) {}
        topProductsTable = null;
    }
    if (topStaffTable) {
        try { topStaffTable.destroy(); } catch(e) {}
        topStaffTable = null;
    }
    if (topUsersTable) {
        try { topUsersTable.destroy(); } catch(e) {}
        topUsersTable = null;
    }
}

function toggleCustomDateInputs() {
    const isCustom = $('#period').val() === 'custom';
    $('.custom-date-wrap').toggle(isCustom);
}

function loadLeaderboardData() {
    $.ajax({
        url: '<?= base_url('leaderboard/get-data') ?>',
        type: 'GET',
        data: {
            period: $('#period').val(),
            start_date: $('#startDate').val(),
            end_date: $('#endDate').val()
        },
        dataType: 'json',
        success: function(response) {
            if (response.status !== 'success') return;
            
            const data = response.data;
            $('#periodLabel').text(data.period.label);
            $('#updatedAt').text(data.updated_at);
            
            // Destroy existing DataTables before updating
            destroyDataTables();
            
            // Update tables
            updateTopCustomers(data.top_customers);
            updateTopProducts(data.top_products);
            updateTopStaff(data.top_staff);
            updateTopUsers(data.top_users_activity);
        },
        error: function(xhr, status, error) {
            console.error('Failed to load leaderboard data:', error);
            $('#topCustomersTable tbody').html('<tr><td colspan="8" class="text-center text-danger">Failed to load data. Please refresh the page.</td></tr>');
            $('#topProductsTable tbody').html('<tr><td colspan="9" class="text-center text-danger">Failed to load data. Please refresh the page.</td></tr>');
            $('#topStaffTable tbody').html('<tr><td colspan="8" class="text-center text-danger">Failed to load data. Please refresh the page.</td></tr>');
            $('#topUsersTable tbody').html('<tr><td colspan="9" class="text-center text-danger">Failed to load data. Please refresh the page.</td></tr>');
        }
    });
}

function updateTopCustomers(customers) {
    const tbody = $('#topCustomersTable tbody');
    tbody.empty();
    
    if (customers && customers.length) {
        customers.forEach((customer, index) => {
            const rank = index + 1;
            const rowClass = getRankClass(rank);
            tbody.append(`
                <tr class="${rowClass}">
                    <td class="text-center"><span class="medal">${getRankIcon(rank)}</span></td>
                    <td>
                        <strong>${escapeHtml(customer.customer_name)}</strong>
                        ${customer.business_name ? `<br><small class="text-muted">${escapeHtml(customer.business_name)}</small>` : ''}
                    </td>
                    <td class="text-end">${formatCurrency(customer.total_sales_value)}</td>
                    <td class="text-center">${formatNumber(customer.sales_count)}</td>
                    <td class="text-end">${formatCurrency(customer.total_production_value)}</td>
                    <td class="text-center">${formatNumber(customer.production_count)}</td>
                    <td class="text-end"><span class="fw-bold text-primary">${formatCurrency(customer.total_value)}</span></td>
                    <td class="text-center"><span class="badge bg-secondary">${formatNumber(customer.total_transactions)}</span></td>
                </tr>
            `);
        });
    } else {
        tbody.append('<tr><td colspan="8" class="text-center text-muted">No customer data found for this period.</td></tr>');
    }
    
    if (topCustomersTable) {
        topCustomersTable.destroy();
    }
    
    topCustomersTable = $('#topCustomersTable').DataTable({
        pageLength: 5,
        searching: false,
        ordering: false,
        info: false,
        paging: false,
        destroy: true
    });
}

function updateTopProducts(products) {
    const tbody = $('#topProductsTable tbody');
    tbody.empty();
    
    if (products && products.length) {
        products.forEach((product, index) => {
            const rank = index + 1;
            const rowClass = getRankClass(rank);
            tbody.append(`
                <tr class="${rowClass}">
                    <td class="text-center"><span class="medal">${getRankIcon(rank)}</span></td>
                    <td><strong>${escapeHtml(product.product_name)}</strong></td>
                    <td><code>${escapeHtml(product.sku)}</code></td>
                    <td class="text-end"><span class="text-success">${formatNumber(product.total_quantity_sold)}</span></td>
                    <td class="text-end">${formatCurrency(product.total_sales_value)}</td>
                    <td class="text-end"><span class="text-warning">${formatNumber(product.total_quantity_used)}</span></td>
                    <td class="text-end">${formatCurrency(product.total_production_value)}</td>
                    <td class="text-end"><span class="fw-bold">${formatNumber(product.total_quantity)}</span></td>
                    <td class="text-end"><span class="fw-bold text-primary">${formatCurrency(product.total_value)}</span></td>
                </tr>
            `);
        });
    } else {
        tbody.append('<tr><td colspan="9" class="text-center text-muted">No product data found for this period.</td></tr>');
    }
    
    if (topProductsTable) {
        topProductsTable.destroy();
    }
    
    topProductsTable = $('#topProductsTable').DataTable({
        pageLength: 5,
        searching: false,
        ordering: false,
        info: false,
        paging: false,
        destroy: true
    });
}

function updateTopStaff(staff) {
    const tbody = $('#topStaffTable tbody');
    tbody.empty();
    
    if (staff && staff.length) {
        staff.forEach((member, index) => {
            const rank = index + 1;
            const rowClass = getRankClass(rank);
            tbody.append(`
                <tr class="${rowClass}">
                    <td class="text-center"><span class="medal">${getRankIcon(rank)}</span></td>
                    <td>
                        <strong>${escapeHtml(member.full_name)}</strong>
                        <br><small class="text-muted">@${escapeHtml(member.username)}</small>
                    </td>
                    <td class="text-end">${formatCurrency(member.total_sales_value)}</td>
                    <td class="text-center">${formatNumber(member.sales_count)}</td>
                    <td class="text-end">${formatCurrency(member.total_production_value)}</td>
                    <td class="text-center">${formatNumber(member.production_count)}</td>
                    <td class="text-end"><span class="fw-bold text-primary">${formatCurrency(member.total_value)}</span></td>
                    <td class="text-center"><span class="badge bg-secondary">${formatNumber(member.total_transactions)}</span></td>
                </tr>
            `);
        });
    } else {
        tbody.append('<tr><td colspan="8" class="text-center text-muted">No staff activity found for this period.</td></tr>');
    }
    
    if (topStaffTable) {
        topStaffTable.destroy();
    }
    
    topStaffTable = $('#topStaffTable').DataTable({
        pageLength: 5,
        searching: false,
        ordering: false,
        info: false,
        paging: false,
        destroy: true
    });
}

function updateTopUsers(users) {
    const tbody = $('#topUsersTable tbody');
    tbody.empty();
    
    // Check if users has a message property (empty state from controller)
    if (users && users.message) {
        tbody.append(`
            <tr>
                <td colspan="9" class="text-center text-muted py-4">
                    <i class="bi bi-info-circle me-2"></i> ${escapeHtml(users.message)}
                </td>
            </tr>
        `);
    } else if (users && users.length) {
        const totalActivities = users.reduce((sum, u) => sum + parseInt(u.activity_count || 0), 0);
        
        users.forEach((user, index) => {
            const rank = index + 1;
            const rowClass = getRankClass(rank);
            const percentage = totalActivities > 0 ? ((user.activity_count / totalActivities) * 100).toFixed(1) : 0;
            
            tbody.append(`
                <tr class="${rowClass}">
                    <td class="text-center"><span class="medal">${getRankIcon(rank)}</span></td>
                    <td>
                        <strong>${escapeHtml(user.full_name || user.username)}</strong>
                        <br><small class="text-muted">@${escapeHtml(user.username)}</small>
                    </td>
                    <td class="text-center"><span class="fw-bold fs-5">${formatNumber(user.activity_count)}</span></td>
                    <td class="text-center"><span class="text-success">${formatNumber(user.create_count || 0)}</span></td>
                    <td class="text-center"><span class="text-warning">${formatNumber(user.update_count || 0)}</span></td>
                    <td class="text-center"><span class="text-danger">${formatNumber(user.delete_count || 0)}</span></td>
                    <td class="text-center"><span class="text-info">${formatNumber(user.login_count || 0)}</span></td>
                    <td class="text-center"><span class="text-secondary">${formatNumber(user.export_count || 0)}</span></td>
                    <td>
                        <div class="d-flex align-items-center gap-2">
                            <div class="progress" style="height: 6px; width: 80px;">
                                <div class="progress-bar bg-primary" style="width: ${percentage}%"></div>
                            </div>
                            <small class="text-muted">${percentage}%</small>
                        </div>
                    </td>
                </tr>
            `);
        });
    } else {
        tbody.append('<tr><td colspan="9" class="text-center text-muted">No user activity found for this period. Try selecting a different date range.</td></tr>');
    }
    
    if (topUsersTable) {
        topUsersTable.destroy();
    }
    
    topUsersTable = $('#topUsersTable').DataTable({
        pageLength: 5,
        searching: false,
        ordering: false,
        info: false,
        paging: false,
        destroy: true
    });
}

function escapeHtml(str) {
    if (!str) return '';
    return str.replace(/[&<>]/g, function(m) {
        if (m === '&') return '&amp;';
        if (m === '<') return '&lt;';
        if (m === '>') return '&gt;';
        return m;
    });
}

$(document).ready(function() {
    toggleCustomDateInputs();
    loadLeaderboardData();
    
    $('#period').on('change', function() {
        toggleCustomDateInputs();
        if ($(this).val() !== 'custom') {
            loadLeaderboardData();
        }
    });
    
    $('#leaderboardFilters').on('submit', function(e) {
        e.preventDefault();
        loadLeaderboardData();
    });
    
    // Auto-refresh every 2 minutes
    setInterval(loadLeaderboardData, 120000);
});
</script>
<?= $this->endSection() ?>