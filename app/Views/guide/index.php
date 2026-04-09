<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>

<style>
    .guide-sidebar {
        position: sticky;
        top: 100px;
        max-height: calc(100vh - 120px);
        overflow-y: auto;
    }
    .guide-sidebar a {
        display: block;
        padding: 6px 12px;
        color: #495057;
        text-decoration: none;
        font-size: 0.9rem;
        border-left: 2px solid transparent;
        transition: all 0.2s;
    }
    .guide-sidebar a:hover,
    .guide-sidebar a.active {
        color: #0d6efd;
        border-left-color: #0d6efd;
        background: rgba(13, 110, 253, 0.05);
    }
    .guide-sidebar a.sub {
        padding-left: 28px;
        font-size: 0.85rem;
    }
    .guide-section {
        scroll-margin-top: 80px;
    }
    .guide-icon {
        font-size: 2rem;
    }
    .module-card {
        border-left: 4px solid;
        transition: transform 0.2s, box-shadow 0.2s;
    }
    .module-card:hover {
        transform: translateY(-3px);
        box-shadow: 0 4px 12px rgba(0,0,0,0.1);
    }
    .flow-arrow {
        font-size: 1.5rem;
        color: #6c757d;
    }
</style>

<div class="row">
    <!-- Navigation sidebar -->
    <div class="col-lg-3 d-none d-lg-block">
        <nav class="guide-sidebar">
            <strong class="d-block px-3 py-2 text-muted small">CONTENTS</strong>
            <a href="#overview">Overview</a>
            <a href="#how-it-works">How It All Works Together</a>
            <a href="#getting-started">Getting Started</a>
            <a href="#module-inventory">Inventory</a>
            <a href="#module-inventory-products" class="sub">Products</a>
            <a href="#module-inventory-categories" class="sub">Categories</a>
            <a href="#module-inventory-adjustments" class="sub">Stock Adjustments</a>
            <a href="#module-purchases">Purchases</a>
            <a href="#module-suppliers">Suppliers</a>
            <a href="#module-sales">Sales</a>
            <a href="#module-customers">Customers</a>
            <a href="#module-production">Production</a>
            <a href="#module-production-jobs" class="sub">Jobs</a>
            <a href="#module-production-categories" class="sub">Categories</a>
            <a href="#module-production-bom" class="sub">Bill of Materials</a>
            <a href="#module-expenses">Expenses</a>
            <a href="#module-adjustments">Adjustments</a>
            <a href="#module-reports">Reports</a>
            <a href="#module-administration">Administration</a>
            <a href="#module-profile">Profile &amp; Settings</a>
        </nav>
    </div>

    <!-- Main content -->
    <div class="col-lg-9">
        <h2 class="mb-4"><i class="bi bi-journal-bookmark me-2"></i>User Guide</h2>

        <!-- Overview -->
        <div id="overview" class="guide-section mb-5">
            <h4 class="border-bottom pb-2 mb-3"><i class="bi bi-info-circle me-2"></i>Overview</h4>
            <p class="text-muted">This system helps you manage your entire business from one place — products, purchases, sales, production, expenses, and reports.</p>

            <div class="row g-3">
                <div class="col-md-4">
                    <div class="card text-center p-3 h-100">
                        <i class="bi bi-box-seam guide-icon text-primary mx-auto"></i>
                        <p class="mb-0">Track every product and stock level</p>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card text-center p-3 h-100">
                        <i class="bi bi-arrow-left-right guide-icon text-success mx-auto"></i>
                        <p class="mb-0">Manage purchases, production, and sales in one workflow</p>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card text-center p-3 h-100">
                        <i class="bi bi-graph-up-arrow guide-icon text-warning mx-auto"></i>
                        <p class="mb-0">Generate reports to make smarter decisions</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- How It All Works Together -->
        <div id="how-it-works" class="guide-section mb-5">
            <h4 class="border-bottom pb-2 mb-3"><i class="bi bi-diagram-3 me-2"></i>How It All Works Together</h4>
            <p class="text-muted">All modules connect through <strong>Inventory</strong> — every purchase, production, and sale changes your stock levels.</p>

            <div class="card bg-light p-4">
                <div class="text-center d-flex flex-wrap justify-content-center align-items-center gap-2">
                    <span class="badge bg-info p-3 fs-6"><i class="bi bi-truck d-block mb-1"></i> Purchases
                        <small class="d-block">(stock comes in)</small>
                    </span>
                    <span class="flow-arrow">&rarr;</span>
                    <span class="badge bg-primary p-3 fs-6"><i class="bi bi-box-seam d-block mb-1"></i> Inventory
                        <small class="d-block">(your stock)</small>
                    </span>
                    <span class="flow-arrow">&rarr;</span>
                    <span class="badge bg-warning text-dark p-3 fs-6"><i class="bi bi-gear d-block mb-1"></i> Production
                        <small class="d-block">(materials transform)</small>
                    </span>
                    <span class="flow-arrow">&rarr;</span>
                    <span class="badge bg-success p-3 fs-6"><i class="bi bi-cart-check d-block mb-1"></i> Sales
                        <small class="d-block">(stock goes out)</small>
                    </span>
                </div>
                <div class="mt-3 text-center">
                    <small class="text-muted"><i class="bi bi-lightbulb me-1"></i> <strong>Expenses</strong> track your spending. <strong>Reports</strong> show you everything in one place.</small>
                </div>
            </div>
        </div>

        <!-- Getting Started -->
        <div id="getting-started" class="guide-section mb-5">
            <h4 class="border-bottom pb-2 mb-3"><i class="bi bi-rocket-takeoff me-2"></i>Getting Started</h4>
            <p>Follow these steps in order to set up the system for first use:</p>

            <ol>
                <li><strong>Add Products</strong> — Go to <em>Inventory > Products</em> and create your products (raw materials and finished goods).</li>
                <li><strong>Add Customers &amp; Suppliers</strong> — Create records for the people and businesses you work with.</li>
                <li><strong>Record Purchases</strong> — When you buy stock, create a purchase. This adds quantities to inventory automatically.</li>
                <li><strong>Record Sales</strong> — When you sell to a customer, create a sale. Stock is deducted from inventory.</li>
                <li><strong>Record Production</strong> — If you manufacture goods, create a production job to track material usage.</li>
            </ol>

            <div class="alert alert-info">
                <i class="bi bi-info-circle me-1"></i> <strong>Tip:</strong> Start with at least a few products and one supplier before your first purchase.
            </div>
        </div>

        <!-- ========== INVENTORY ========== -->
        <div id="module-inventory" class="guide-section mb-5">
            <h4 class="border-bottom pb-2 mb-3"><i class="bi bi-box-seam me-2 text-primary"></i>Inventory Module</h4>
            <p>The Inventory module is the heart of the system. It holds every product you own, tracks stock levels, and updates automatically whenever you purchase, produce, or sell.</p>

            <div id="module-inventory-products" class="mb-4">
                <h6 class="text-primary"><i class="bi bi-grid me-1"></i> Products</h6>
                <p><strong>What:</strong> A product is anything you buy, sell, or use in production — raw materials, finished goods, or supplies.</p>
                <p><strong>How:</strong></p>
                <ul>
                    <li>Go to <em>Inventory > Products</em> and click <strong>New Product</strong>.</li>
                    <li>Fill in the name, SKU, cost price, and selling price. You can also set a <strong>reorder level</strong> — when stock drops below this, a low-stock alert appears.</li>
                    <li>Each product has a barcode that can be scanned for quick lookups.</li>
                </ul>
                <div class="alert alert-success">
                    <i class="bi bi-check-circle me-1"></i> <strong>Why important:</strong> Without products, you can't make purchases, sales, or production jobs.
                </div>
            </div>

            <div id="module-inventory-categories" class="mb-4">
                <h6 class="text-primary"><i class="bi bi-tags me-1"></i> Categories</h6>
                <p><strong>What:</strong> Categories group similar products together for easier organization and filtering.</p>
                <p><strong>How:</strong> Go to <em>Inventory > Categories</em>, give it a name and optional description, and save.</p>
            </div>

            <div id="module-inventory-adjustments" class="mb-4">
                <h6 class="text-primary"><i class="bi bi-arrow-left-right me-1"></i> Stock Adjustments</h6>
                <p><strong>What:</strong> An adjustment lets you manually increase or decrease stock for any reason — stock counts, damaged items, found items, etc.</p>
                <p><strong>How:</strong> Select the product, choose "Add" or "Subtract", enter the quantity, and give a reason.</p>
                <p><strong>Why:</strong> Purchases and sales update stock automatically, but adjustments cover situations the system can't detect on its own.</p>
            </div>
        </div>

        <!-- ========== PURCHASES ========== -->
        <div id="module-purchases" class="guide-section mb-5">
            <h4 class="border-bottom pb-2 mb-3"><i class="bi bi-cart-plus me-2 text-success"></i>Purchases Module</h4>

            <div class="card mb-3 border-success">
                <div class="card-body">
                    <p><strong>What:</strong> A purchase records a buy from a supplier. When you save it, the system <strong>automatically adds the purchased quantity to inventory</strong>.</p>
                    <p><strong>Why:</strong> This is how stock enters your system. Every purchase updates inventory and creates a financial record of the cost.</p>
                    <p><strong>How:</strong></p>
                    <ol>
                        <li>Go to <em>Purchases > New Purchase</em>.</li>
                        <li>Select a <strong>Supplier</strong> and a <strong>Product</strong>.</li>
                        <li>Enter the <strong>quantity</strong>, <strong>unit cost</strong>, and <strong>purchase date</strong>.</li>
                        <li>Save — inventory increases automatically.</li>
                    </ol>
                </div>
            </div>
        </div>

        <!-- ========== SUPPLIERS ========== -->
        <div id="module-suppliers" class="guide-section mb-5">
            <h4 class="border-bottom pb-2 mb-3"><i class="bi bi-truck me-2 text-dark"></i>Suppliers</h4>
            <p><strong>What:</strong> Suppliers are the businesses or people you buy from.</p>
            <p><strong>How:</strong> Go to <em>Purchases > Suppliers</em>, add their name, phone, email, and address.</p>
            <p><strong>Why:</strong> You need a supplier to create a purchase. Keeping supplier records helps you track who you've bought from and at what prices.</p>
        </div>

        <!-- ========== SALES ========== -->
        <div id="module-sales" class="guide-section mb-5">
            <h4 class="border-bottom pb-2 mb-3"><i class="bi bi-cart-check me-2 text-success"></i>Sales Module</h4>

            <div class="card mb-3 border-success">
                <div class="card-body">
                    <p><strong>What:</strong> A sale records a sell to a customer. When you save it, the system <strong>automatically deducts the sold quantity from inventory</strong>.</p>
                    <p><strong>How:</strong></p>
                    <ol>
                        <li>Go to <em>Sales > New Sale</em>.</li>
                        <li>Select a <strong>Customer</strong> and the <strong>Product</strong> being sold.</li>
                        <li>Enter <strong>quantity</strong>, <strong>selling price</strong>, and <strong>payment details</strong> (paid, partial, or credit).</li>
                        <li>Save — inventory decreases automatically.</li>
                    </ol>
                </div>
            </div>
        </div>

        <!-- ========== CUSTOMERS ========== -->
        <div id="module-customers" class="guide-section mb-5">
            <h4 class="border-bottom pb-2 mb-3"><i class="bi bi-people me-2 text-info"></i>Customers</h4>
            <p><strong>What:</strong> Customers are the people or businesses you sell to.</p>
            <p><strong>How:</strong> Go to <em>Sales > Customers</em>, add their name, phone, and other details. You can also set a <strong>credit limit</strong>.</p>
            <p><strong>Why:</strong> You need a customer to create a sale. Customer records help you track sales history, balances, and credit usage.</p>
        </div>

        <!-- ========== PRODUCTION ========== -->
        <div id="module-production" class="guide-section mb-5">
            <h4 class="border-bottom pb-2 mb-3"><i class="bi bi-gear me-2 text-warning"></i>Production Module</h4>
            <p>This module tracks when you <strong>transform raw materials into finished goods</strong>.</p>

            <div id="module-production-jobs" class="mb-4">
                <h6 class="text-warning"><i class="bi bi-list-ol me-1"></i> Production Jobs</h6>
                <p><strong>What:</strong> A job records material consumption for a task. Materials are deducted from inventory when you mark it as <strong>Completed</strong>.</p>
                <p><strong>How:</strong></p>
                <ol class="mb-2">
                    <li>Go to <em>Production > New Job</em>.</li>
                    <li>Fill in the <strong>Job Name</strong>, pick a <strong>Customer</strong>, set the <strong>date</strong> and <strong>category</strong>.</li>
                    <li>Add <strong>Materials</strong> — select products and quantities you'll be consuming.</li>
                    <li>Set <strong>Status</strong>:
                        <ul>
                            <li><strong>Draft</strong> — saves the job, inventory is <em>not yet affected</em>.</li>
                            <li><strong>Completed</strong> — saves the job and <em>immediately deducts</em> all materials from inventory.</li>
                        </ul>
                    </li>
                </ol>
                <div class="alert alert-warning">
                    <i class="bi bi-exclamation-triangle me-1"></i> <strong>Important:</strong> When you select <strong>Completed</strong>, the system checks stock levels. If any material has insufficient stock, the job cannot be created.
                </div>
                <p><strong>Job Statuses:</strong></p>
                <ul>
                    <li><code>Draft</code> — planned but not yet executed. Can be edited, completed, or deleted.</li>
                    <li><code>Completed</code> — materials deducted. Cannot be edited.</li>
                    <li><code>Cancelled</code> — abandoned. Does not affect inventory.</li>
                </ul>
            </div>

            <div id="module-production-categories" class="mb-4">
                <h6 class="text-warning"><i class="bi bi-tags me-1"></i> Production Categories</h6>
                <p><strong>What:</strong> Group jobs by type — e.g. "Printing", "Assembly", "Packaging". Categories help in reporting and filtering.</p>
                <p><strong>How:</strong> Go to <em>Production > Categories</em>, add a name, and save.</p>
            </div>

            <div id="module-production-bom" class="mb-4">
                <h6 class="text-warning"><i class="bi bi-file-text me-1"></i> Bill of Materials (BOM)</h6>
                <p><strong>What:</strong> A BOM is a reusable template — it defines a standard "recipe" of materials for repeated production jobs.</p>
                <p><strong>Why:</strong> Instead of re-entering the same materials every time, create a BOM template once and load it when creating jobs.</p>
                <p><strong>How:</strong></p>
                <ol>
                    <li>Go to <em>Production > Bill of Materials</em> and click <strong>New Template</strong>.</li>
                    <li>Give it a name, add the materials and their standard quantities.</li>
                    <li>When creating a production job, select the template and click <strong>Load</strong>.</li>
                </ol>
            </div>

            <div class="alert alert-info mt-4">
                <i class="bi bi-lightbulb me-1"></i> <strong>Example:</strong> You make custom banners. Materials: vinyl sheet (1), ink cartridge (0.5), grommets (4). Create a BOM called "Standard Banner". Each time you need a job for 50 banners, load the template — materials auto-fill.
            </div>
        </div>

        <!-- ========== EXPENSES ========== -->
        <div id="module-expenses" class="guide-section mb-5">
            <h4 class="border-bottom pb-2 mb-3"><i class="bi bi-wallet2 me-2"></i>Expenses Module</h4>
            <p><strong>What:</strong> Track business spending that aren't product purchases — rent, utilities, transportation, etc.</p>
            <p><strong>How:</strong></p>
            <ol>
                <li>Go to <em>Expenses > Add Expense</em>.</li>
                <li>Select a <strong>Category</strong>, enter amount, description, and date.</li>
            </ol>
            <p><strong>Why:</strong> Expenses appear in financial reports so you see your full cost picture, not just product costs.</p>
        </div>

        <!-- ========== ADJUSTMENTS ========== -->
        <div id="module-adjustments" class="guide-section mb-5">
            <h4 class="border-bottom pb-2 mb-3"><i class="bi bi-arrow-down-up me-2 text-danger"></i>Adjustments Module</h4>
            <p><strong>What:</strong> Record events that affect inventory outside of normal buying and selling — damaged goods, customer refunds, theft, returns, and other stock losses or gains.</p>
            <p><strong>How:</strong></p>
            <ol>
                <li>Go to <em>Adjustments > New Adjustment</em>.</li>
                <li>Select the <strong>Product</strong> and <strong>Event Type</strong> (Damage, Refund, Return, Theft, Other).</li>
                <li>Enter <strong>Quantity</strong>, <strong>Unit Cost</strong>, and an optional <strong>Customer</strong> (useful for refunds).</li>
                <li>Choose whether to <strong>Adjust Stock</strong>:
                    <ul>
                        <li><strong>Yes</strong> — inventory is deducted (for damage, theft, refunds) or added (for returns).</li>
                        <li><strong>No</strong> — the record is saved for tracking purposes only; stock levels stay the same.</li>
                    </ul>
                </li>
            </ol>
            <p><strong>Why:</strong></p>
            <ul>
                <li>Keeps inventory accurate when things are broken, stolen, or returned.</li>
                <li>Shows who logged the event and when (audit trail).</li>
                <li>Totals appear in <strong>Financial Reports</strong> so you see the true cost of stock losses.</li>
            </ul>
        </div>

        <!-- ========== REPORTS ========== -->
        <div id="module-reports" class="guide-section mb-5">
            <h4 class="border-bottom pb-2 mb-3"><i class="bi bi-graph-up me-2"></i>Reports</h4>
            <p><strong>What:</strong> Reports give you a data-driven view of your business. Filter by date range and export.</p>
            <ul>
                <li><strong>Inventory Reports</strong> — stock on hand, low-stock items, product movement.</li>
                <li><strong>Sales Reports</strong> — revenue by period, by customer, best-sellers.</li>
                <li><strong>Financial Reports</strong> — overview of income, expenses, and profit.</li>
                <li><strong>Production Reports</strong> — jobs completed, material costs, production efficiency.</li>
            </ul>
            <p><strong>Why:</strong> Use these to spot trends, identify problems, and make informed decisions.</p>
        </div>

        <!-- ========== ADMIN ========== -->
        <div id="module-administration" class="guide-section mb-5">
            <h4 class="border-bottom pb-2 mb-3"><i class="bi bi-shield-lock me-2"></i>Administration</h4>
            <p>Admin tools for managing the system itself (only available to users with admin permissions).</p>
            <ul>
                <li><strong>Users</strong> — add, edit, activate, or deactivate user accounts.</li>
                <li><strong>Roles &amp; Permissions</strong> — define what each role can access (e.g. view-only for sales, full access for managers).</li>
                <li><strong>System Settings</strong> — company name, currency defaults, etc.</li>
                <li><strong>Backup</strong> — download a copy of your database. <strong>Do this regularly!</strong></li>
                <li><strong>Audit Logs</strong> — see who did what and when in the system.</li>
            </ul>
        </div>

        <!-- ========== PROFILE ========== -->
        <div id="module-profile" class="guide-section mb-5">
            <h4 class="border-bottom pb-2 mb-3"><i class="bi bi-person-circle me-2"></i>Profile &amp; Settings</h4>
            <p>Click your name in the top-right corner to access:</p>
            <ul>
                <li><strong>My Profile</strong> — update your name, email, and phone.</li>
                <li><strong>Change Password</strong> — update your login password.</li>
                <li><strong>2-Factor Authentication</strong> — add an extra layer of security.</li>
            </ul>
            <p>Use the <strong>currency selector</strong> (top-right) to switch amounts between Liberian Dollars (LRD) and US Dollars (USD).</p>
        </div>

        <!-- Quick Reference -->
        <div class="guide-section mb-5">
            <h4 class="border-bottom pb-2 mb-3"><i class="bi bi-table me-2"></i>Quick Reference</h4>
            <div class="table-responsive">
                <table class="table table-bordered table-sm">
                    <thead class="table-light">
                        <tr><th>Action</th><th>Go To</th><th>Effect on Stock</th></tr>
                    </thead>
                    <tbody>
                        <tr><td>Add a new product</td><td>Inventory > Products > New</td><td>No change</td></tr>
                        <tr><td>Buy materials</td><td>Purchases > New Purchase</td><td><span class="text-success">Stock increases</span></td></tr>
                        <tr><td>Sell to customer</td><td>Sales > New Sale</td><td><span class="text-danger">Stock decreases</span></td></tr>
                        <tr><td>Create production job</td><td>Production > New Job</td><td>No change (Draft)</td></tr>
                        <tr><td>Complete production job</td><td>Job list > Mark as Completed</td><td><span class="text-danger">Materials deducted</span></td></tr>
                        <tr><td>Manual stock adjustment</td><td>Inventory > Stock Adjustments</td><td>Increases or decreases</td></tr>
                        <tr><td>View financial overview</td><td>Reports > Financial</td><td>Read-only</td></tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
$(document).ready(function() {
    // Highlight active sidebar section on scroll
    var navLinks = $('.guide-sidebar a');
    $(window).on('scroll', function() {
        var scrollPos = $(document).scrollTop() + 100;
        navLinks.each(function() {
            var target = $(this.getAttribute('href'));
            if (target.length) {
                if (target.position().top <= scrollPos) {
                    navLinks.removeClass('active');
                    $(this).addClass('active');
                }
            }
        });
    });

    // Smooth scroll for sidebar links
    $('.guide-sidebar a').on('click', function(e) {
        e.preventDefault();
        var target = $(this.getAttribute('href'));
        if (target.length) {
            $('html, body').animate({ scrollTop: target.offset().top - 70 }, 300);
        }
    });
});
</script>
<?= $this->endSection() ?>
