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
    .step-box {
        background: #f8f9fa;
        border-left: 4px solid #0d6efd;
        padding: 15px;
        margin: 15px 0;
        border-radius: 8px;
    }
    .step-number {
        display: inline-block;
        width: 28px;
        height: 28px;
        background: #0d6efd;
        color: white;
        text-align: center;
        border-radius: 50%;
        line-height: 28px;
        font-size: 14px;
        font-weight: bold;
        margin-right: 10px;
    }
    .tip-box {
        background: #d1ecf1;
        border: 1px solid #bee5eb;
        border-radius: 8px;
        padding: 12px 15px;
        margin: 15px 0;
    }
    .warning-box {
        background: #f8d7da;
        border: 1px solid #f5c6cb;
        border-radius: 8px;
        padding: 12px 15px;
        margin: 15px 0;
    }
    .success-box {
        background: #d4edda;
        border: 1px solid #c3e6cb;
        border-radius: 8px;
        padding: 12px 15px;
        margin: 15px 0;
    }
    .module-card {
        border-left: 4px solid;
        transition: transform 0.2s, box-shadow 0.2s;
        margin-bottom: 20px;
    }
    .module-card:hover {
        transform: translateY(-3px);
        box-shadow: 0 4px 12px rgba(0,0,0,0.1);
    }
    .flow-arrow {
        font-size: 1.5rem;
        color: #6c757d;
    }
    kbd {
        background-color: #f8f9fa;
        border: 1px solid #ccc;
        border-radius: 3px;
        padding: 2px 5px;
        font-family: monospace;
        font-size: 0.85rem;
    }
</style>

<div class="row">
    <!-- Navigation sidebar -->
    <div class="col-lg-3 d-none d-lg-block">
        <nav class="guide-sidebar">
            <strong class="d-block px-3 py-2 text-muted small">📖 TABLE OF CONTENTS</strong>
            <a href="#overview">Overview</a>
            <a href="#how-it-works">How Everything Connects</a>
            <a href="#getting-started">Getting Started (First Time)</a>
            <a href="#module-inventory">📦 Inventory Management</a>
            <a href="#module-inventory-products" class="sub">• Creating Products</a>
            <a href="#module-inventory-categories" class="sub">• Managing Categories</a>
            <a href="#module-inventory-adjustments" class="sub">• Stock Adjustments</a>
            <a href="#module-purchases">🛒 Purchases</a>
            <a href="#module-suppliers">🚚 Suppliers</a>
            <a href="#module-sales">💰 Sales</a>
            <a href="#module-customers">👥 Customers</a>
            <a href="#module-production">⚙️ Production</a>
            <a href="#module-production-jobs" class="sub">• Creating Jobs</a>
            <a href="#module-production-categories" class="sub">• Production Categories</a>
            <a href="#module-production-bom" class="sub">• Bill of Materials (BOM)</a>
            <a href="#module-expenses">💸 Expenses</a>
            <a href="#module-adjustments">📊 Adjustments (Damage/Returns)</a>
            <a href="#module-reports">📈 Reports</a>
            <a href="#module-administration">⚙️ Administration</a>
            <a href="#module-profile">👤 Profile &amp; Security</a>
            <a href="#faq">❓ FAQ</a>
            <a href="#keyboard-shortcuts">⌨️ Keyboard Shortcuts</a>
        </nav>
    </div>

    <!-- Main content -->
    <div class="col-lg-9">
        <h1 class="mb-3"><i class="bi bi-journal-bookmark-fill me-2 text-primary"></i>Complete User Guide</h1>
        <p class="lead text-muted mb-4">Learn how to manage your entire inventory, sales, purchases, production, and finances — all in one place.</p>

        <!-- ========== OVERVIEW ========== -->
        <div id="overview" class="guide-section mb-5">
            <h2 class="border-bottom pb-2 mb-3"><i class="bi bi-info-circle-fill me-2 text-info"></i>What Is This System?</h2>
            <p>This is a complete <strong>Inventory Management System</strong> designed for businesses that sell products, manage stock, track production, and monitor finances. It helps you:</p>
            
            <div class="row g-3 mt-2">
                <div class="col-md-4">
                    <div class="card text-center p-3 h-100">
                        <i class="bi bi-box-seam guide-icon text-primary mx-auto"></i>
                        <h6>Track Inventory</h6>
                        <p class="small mb-0">Know exactly what you have, where it is, and when to reorder</p>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card text-center p-3 h-100">
                        <i class="bi bi-arrow-left-right guide-icon text-success mx-auto"></i>
                        <h6>Manage Workflow</h6>
                        <p class="small mb-0">From purchase to production to sale — everything connected</p>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card text-center p-3 h-100">
                        <i class="bi bi-graph-up-arrow guide-icon text-warning mx-auto"></i>
                        <h6>Analyze Performance</h6>
                        <p class="small mb-0">Generate reports to see profits, losses, and trends</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- ========== HOW IT WORKS ========== -->
        <div id="how-it-works" class="guide-section mb-5">
            <h2 class="border-bottom pb-2 mb-3"><i class="bi bi-diagram-3-fill me-2 text-primary"></i>How Everything Connects</h2>
            <p>All modules work together through your <strong>inventory</strong>. Here's the flow:</p>

            <div class="card bg-light p-4 mt-3">
                <div class="text-center d-flex flex-wrap justify-content-center align-items-center gap-2">
                    <div class="text-center">
                        <span class="badge bg-success p-3 fs-6"><i class="bi bi-truck d-block mb-1"></i> <strong>1. PURCHASE</strong>
                            <small class="d-block">Stock comes IN</small>
                        </span>
                    </div>
                    <span class="flow-arrow">→</span>
                    <div class="text-center">
                        <span class="badge bg-primary p-3 fs-6"><i class="bi bi-box-seam d-block mb-1"></i> <strong>2. INVENTORY</strong>
                            <small class="d-block">Stock INCREASES</small>
                        </span>
                    </div>
                    <span class="flow-arrow">→</span>
                    <div class="text-center">
                        <span class="badge bg-warning text-dark p-3 fs-6"><i class="bi bi-gear d-block mb-1"></i> <strong>3. PRODUCTION</strong>
                            <small class="d-block">Materials CONSUMED</small>
                        </span>
                    </div>
                    <span class="flow-arrow">→</span>
                    <div class="text-center">
                        <span class="badge bg-danger p-3 fs-6"><i class="bi bi-cart-check d-block mb-1"></i> <strong>4. SALE</strong>
                            <small class="d-block">Stock DECREASES</small>
                        </span>
                    </div>
                </div>
                <div class="mt-3 text-center">
                    <small class="text-muted"><i class="bi bi-lightbulb me-1"></i> <strong>Pro Tip:</strong> Expenses and Adjustments affect your financial reports — they don't change stock levels directly.</small>
                </div>
            </div>
        </div>

        <!-- ========== GETTING STARTED ========== -->
        <div id="getting-started" class="guide-section mb-5">
            <h2 class="border-bottom pb-2 mb-3"><i class="bi bi-rocket-takeoff-fill me-2 text-success"></i>Getting Started (First Time Setup)</h2>
            <p>Follow these steps in order to set up your system correctly:</p>

            <div class="step-box">
                <span class="step-number">1</span> <strong>Add Your Products</strong><br>
                Go to <kbd>Inventory → Products</kbd> and click <span class="badge bg-primary">+ Add Product</span>. Create all the items you buy, sell, or use in production.
            </div>

            <div class="step-box">
                <span class="step-number">2</span> <strong>Add Categories (Optional but Recommended)</strong><br>
                Go to <kbd>Inventory → Categories</kbd> to group similar products. This helps with organization and reporting.
            </div>

            <div class="step-box">
                <span class="step-number">3</span> <strong>Add Suppliers</strong><br>
                Go to <kbd>Purchases → Suppliers</kbd> and add the companies/people you buy from.
            </div>

            <div class="step-box">
                <span class="step-number">4</span> <strong>Add Customers</strong><br>
                Go to <kbd>Sales → Customers</kbd> and add the people/businesses you sell to.
            </div>

            <div class="step-box">
                <span class="step-number">5</span> <strong>Record Your First Purchase</strong><br>
                Go to <kbd>Purchases → New Purchase</kbd>. This adds stock to your inventory.
            </div>

            <div class="step-box">
                <span class="step-number">6</span> <strong>Record Your First Sale</strong><br>
                Go to <kbd>Sales → New Sale</kbd>. This removes stock from your inventory.
            </div>

            <div class="tip-box">
                <i class="bi bi-lightbulb-fill me-2 text-warning"></i> <strong>Tip:</strong> Start with a few test transactions before going live. This helps you understand the workflow without affecting real data.
            </div>
        </div>

        <!-- ========== INVENTORY MODULE ========== -->
        <div id="module-inventory" class="guide-section mb-5">
            <h2 class="border-bottom pb-2 mb-3"><i class="bi bi-box-seam-fill me-2 text-primary"></i>📦 Inventory Management</h2>
            <p>The Inventory module is your central hub. Every product you own lives here, and stock levels update automatically whenever you purchase, produce, or sell.</p>

            <!-- Products -->
            <div id="module-inventory-products" class="mb-4">
                <h4 class="mt-3"><i class="bi bi-grid-fill me-2 text-primary"></i>Creating Products</h4>
                <p><strong>What is a product?</strong> Anything you buy, sell, or use — raw materials, finished goods, packaging, or supplies.</p>
                
                <h6>Step-by-Step:</h6>
                <ol>
                    <li>Go to <kbd>Inventory → Products</kbd></li>
                    <li>Click the <span class="badge bg-primary">+ Add Product</span> button</li>
                    <li>Fill in the form:
                        <ul>
                            <li><strong>Product Name</strong> — What is it called? (e.g., "Premium Vinyl Sheet")</li>
                            <li><strong>SKU</strong> — A unique code you create (e.g., "VNL-001")</li>
                            <li><strong>Barcode</strong> — Optional. Scan this later for quick lookup</li>
                            <li><strong>Category</strong> — Group similar items together</li>
                            <li><strong>Unit</strong> — How is it measured? (Pieces, Meters, KG, etc.)</li>
                            <li><strong>Purchase Price</strong> — What you pay to buy it</li>
                            <li><strong>Selling Price</strong> — What you charge customers</li>
                            <li><strong>Initial Quantity</strong> — How many you have right now</li>
                            <li><strong>Minimum Stock</strong> — When stock falls below this, you'll get a low-stock alert</li>
                            <li><strong>Description</strong> — Optional notes about the product</li>
                        </ul>
                    </li>
                    <li>Click <span class="badge bg-success">Save Product</span></li>
                </ol>

                <div class="tip-box">
                    <i class="bi bi-qr-code me-2"></i> <strong>Pro Tip:</strong> Use the <strong>Scan Barcode</strong> feature in the products list to quickly find products by scanning.
                </div>
            </div>

            <!-- Categories -->
            <div id="module-inventory-categories" class="mb-4">
                <h4 class="mt-3"><i class="bi bi-tags-fill me-2 text-primary"></i>Managing Categories</h4>
                <p><strong>Why use categories?</strong> They help you organize products and filter reports.</p>
                
                <h6>Step-by-Step:</h6>
                <ol>
                    <li>Go to <kbd>Inventory → Categories</kbd></li>
                    <li>Click <span class="badge bg-primary">+ Add Category</span></li>
                    <li>Enter a <strong>Category Name</strong> (e.g., "Raw Materials", "Finished Goods")</li>
                    <li>Add an optional <strong>Description</strong></li>
                    <li>Click <span class="badge bg-success">Save</span></li>
                </ol>
            </div>

            <!-- Stock Adjustments -->
            <div id="module-inventory-adjustments" class="mb-4">
                <h4 class="mt-3"><i class="bi bi-arrow-left-right-fill me-2 text-primary"></i>Stock Adjustments</h4>
                <p><strong>When to use?</strong> When you need to manually change stock levels — for physical counts, found items, or corrections.</p>
                
                <h6>Step-by-Step:</h6>
                <ol>
                    <li>Go to <kbd>Inventory → Stock Adjustments</kbd></li>
                    <li>Click the <span class="badge bg-primary">Adjust Stock</span> button</li>
                    <li>Select the <strong>Product</strong></li>
                    <li>Choose <strong>Increase (+)</strong> or <strong>Decrease (-)</strong></li>
                    <li>Enter the <strong>Quantity</strong></li>
                    <li>Select a <strong>Reason</strong> (Stock Count, Damaged, Return, etc.)</li>
                    <li>Add optional <strong>Notes</strong></li>
                    <li>Click <span class="badge bg-success">Apply Adjustment</span></li>
                </ol>

                <div class="warning-box">
                    <i class="bi bi-exclamation-triangle-fill me-2"></i> <strong>Warning:</strong> Decreasing stock below zero is allowed but will show negative inventory. Only do this if you know what you're doing.
                </div>
            </div>
        </div>

        <!-- ========== PURCHASES ========== -->
        <div id="module-purchases" class="guide-section mb-5">
            <h2 class="border-bottom pb-2 mb-3"><i class="bi bi-cart-plus-fill me-2 text-success"></i>🛒 Purchases</h2>
            
            <div class="success-box">
                <i class="bi bi-check-circle-fill me-2"></i> <strong>What happens when you create a purchase?</strong> Stock INCREASES automatically.
            </div>

            <h6>Step-by-Step to Create a Purchase:</h6>
            <ol>
                <li>Go to <kbd>Purchases → New Purchase</kbd></li>
                <li>Select a <strong>Supplier</strong> (if none exists, create one first)</li>
                <li>Add <strong>Products</strong> to the purchase:
                    <ul>
                        <li>Search for a product by name or SKU</li>
                        <li>Enter <strong>Quantity</strong> and <strong>Unit Price</strong></li>
                        <li>Click <span class="badge bg-primary">Add Item</span></li>
                        <li>Repeat for multiple products</li>
                    </ul>
                </li>
                <li>Set the <strong>Purchase Date</strong></li>
                <li>Choose <strong>Currency</strong> (LRD or USD)</li>
                <li>Add optional <strong>Notes</strong></li>
                <li>Click <span class="badge bg-success">Save Purchase</span></li>
            </ol>

            <div class="tip-box mt-3">
                <i class="bi bi-lightbulb-fill me-2"></i> <strong>Tip:</strong> After saving, you can "Receive" the purchase to add stock to inventory. The system can also auto-receive if you prefer.
            </div>
        </div>

        <!-- ========== SUPPLIERS ========== -->
        <div id="module-suppliers" class="guide-section mb-5">
            <h2 class="border-bottom pb-2 mb-3"><i class="bi bi-truck-fill me-2 text-secondary"></i>🚚 Suppliers</h2>
            <p><strong>Who are suppliers?</strong> The businesses or people you buy products from.</p>

            <h6>Step-by-Step to Add a Supplier:</h6>
            <ol>
                <li>Go to <kbd>Purchases → Suppliers</kbd></li>
                <li>Click <span class="badge bg-primary">+ Add Supplier</span></li>
                <li>Fill in:
                    <ul>
                        <li><strong>Supplier Name</strong> — Required</li>
                        <li><strong>Contact Person</strong> — Who you talk to</li>
                        <li><strong>Phone</strong> — Required</li>
                        <li><strong>Email</strong> — Optional</li>
                        <li><strong>Address</strong> — Optional</li>
                        <li><strong>Tax Number</strong> — Optional</li>
                        <li><strong>Payment Terms</strong> — e.g., "Net 30"</li>
                    </ul>
                </li>
                <li>Click <span class="badge bg-success">Save</span></li>
            </ol>
        </div>

        <!-- ========== SALES ========== -->
        <div id="module-sales" class="guide-section mb-5">
            <h2 class="border-bottom pb-2 mb-3"><i class="bi bi-cart-check-fill me-2 text-danger"></i>💰 Sales</h2>
            
            <div class="success-box">
                <i class="bi bi-check-circle-fill me-2"></i> <strong>What happens when you create a sale?</strong> Stock DECREASES automatically.
            </div>

            <h6>Step-by-Step to Create a Sale:</h6>
            <ol>
                <li>Go to <kbd>Sales → New Sale</kbd></li>
                <li>Select a <strong>Customer</strong> (or leave as "Walk-in Customer")</li>
                <li>Add <strong>Products</strong> to the sale:
                    <ul>
                        <li>Search for a product by name, SKU, or barcode</li>
                        <li>Enter <strong>Quantity</strong> (system checks stock availability)</li>
                        <li>Enter <strong>Selling Price</strong> (can override default)</li>
                        <li>Click <span class="badge bg-primary">Add Item</span></li>
                    </ul>
                </li>
                <li>Set <strong>Sale Date</strong> (defaults to today)</li>
                <li>Choose <strong>Payment Method</strong> (Cash, Card, Transfer, etc.)</li>
                <li>Select <strong>Payment Status</strong>:
                    <ul>
                        <li><strong>Paid</strong> — Customer paid in full</li>
                        <li><strong>Partial</strong> — Customer paid partially, enter amount paid</li>
                        <li><strong>Unpaid</strong> — Customer hasn't paid yet (credit sale)</li>
                    </ul>
                </li>
                <li>Add optional <strong>Notes</strong></li>
                <li>Click <span class="badge bg-success">Complete Sale</span></li>
            </ol>

            <div class="tip-box mt-3">
                <i class="bi bi-printer-fill me-2"></i> <strong>Tip:</strong> After saving, you can print an invoice or email it to the customer.
            </div>
        </div>

        <!-- ========== CUSTOMERS ========== -->
        <div id="module-customers" class="guide-section mb-5">
            <h2 class="border-bottom pb-2 mb-3"><i class="bi bi-people-fill me-2 text-info"></i>👥 Customers</h2>

            <h6>Step-by-Step to Add a Customer:</h6>
            <ol>
                <li>Go to <kbd>Sales → Customers</kbd></li>
                <li>Click <span class="badge bg-primary">+ Add Customer</span></li>
                <li>Fill in:
                    <ul>
                        <li><strong>Customer Name</strong> — Required</li>
                        <li><strong>Business Name</strong> — If a company</li>
                        <li><strong>Phone</strong> — Required</li>
                        <li><strong>Email</strong> — Optional</li>
                        <li><strong>Address</strong> — Optional</li>
                        <li><strong>Credit Limit</strong> — Maximum amount they can owe (optional)</li>
                    </ul>
                </li>
                <li>Click <span class="badge bg-success">Save</span></li>
            </ol>

            <div class="tip-box">
                <i class="bi bi-graph-up me-2"></i> <strong>Pro Tip:</strong> Click on a customer's name to see their purchase history and outstanding balance.
            </div>
        </div>

        <!-- ========== PRODUCTION ========== -->
        <div id="module-production" class="guide-section mb-5">
            <h2 class="border-bottom pb-2 mb-3"><i class="bi bi-gear-fill me-2 text-warning"></i>⚙️ Production Module</h2>
            <p>Use Production when you <strong>transform raw materials into finished products</strong>. For example: turning vinyl sheets and ink into printed banners.</p>

            <!-- Production Jobs -->
            <div id="module-production-jobs" class="mb-4">
                <h4 class="mt-3"><i class="bi bi-list-ol-fill me-2 text-warning"></i>Creating Production Jobs</h4>
                
                <h6>Step-by-Step:</h6>
                <ol>
                    <li>Go to <kbd>Production → New Job</kbd></li>
                    <li>Fill in basic info:
                        <ul>
                            <li><strong>Job Name</strong> — What is this job for? (e.g., "50 Custom Banners")</li>
                            <li><strong>Customer</strong> — Who requested this job? (optional)</li>
                            <li><strong>Production Date</strong> — When the job is done</li>
                            <li><strong>Category</strong> — Group similar jobs</li>
                            <li><strong>Quantity Produced</strong> — How many finished units?</li>
                            <li><strong>Currency</strong> — LRD or USD</li>
                        </ul>
                    </li>
                    <li>Add <strong>Materials</strong>:
                        <ul>
                            <li>Select a product (raw material)</li>
                            <li>Enter quantity needed</li>
                            <li>Unit cost auto-fills from product purchase price</li>
                            <li>Click <span class="badge bg-primary">Add Material</span></li>
                        </ul>
                    </li>
                    <li>Choose <strong>Status</strong>:
                        <ul>
                            <li><strong>Draft</strong> — Save without affecting inventory. You can edit later.</li>
                            <li><strong>Completed</strong> — Save AND immediately deduct materials from inventory.</li>
                        </ul>
                    </li>
                    <li>Click <span class="badge bg-success">Save Production Job</span></li>
                </ol>

                <div class="warning-box">
                    <i class="bi bi-exclamation-triangle-fill me-2"></i> <strong>Important:</strong> If you choose "Completed" status, the system checks stock levels. If any material has insufficient stock, the job cannot be saved.
                </div>

                <div class="tip-box mt-2">
                    <i class="bi bi-printer-fill me-2"></i> <strong>Tip:</strong> After saving, you can print a production worksheet to give to your team.
                </div>
            </div>

            <!-- Production Categories -->
            <div id="module-production-categories" class="mb-4">
                <h4 class="mt-3"><i class="bi bi-tags-fill me-2 text-warning"></i>Production Categories</h4>
                <p><strong>Why?</strong> Group similar jobs together (e.g., "Banners", "T-Shirts", "Labels").</p>
                
                <h6>Step-by-Step:</h6>
                <ol>
                    <li>Go to <kbd>Production → Categories</kbd></li>
                    <li>Click <span class="badge bg-primary">+ Add Category</span></li>
                    <li>Enter a <strong>Category Name</strong></li>
                    <li>Click <span class="badge bg-success">Save</span></li>
                </ol>
            </div>

            <!-- Bill of Materials (BOM) -->
            <div id="module-production-bom" class="mb-4">
                <h4 class="mt-3"><i class="bi bi-file-text-fill me-2 text-warning"></i>Bill of Materials (BOM) Templates</h4>
                <p><strong>What is a BOM?</strong> A reusable template that lists all materials needed for a production job. Saves time when you repeat the same job.</p>
                
                <h6>Example:</h6>
                <p>You make custom banners. Each banner needs:</p>
                <ul>
                    <li>Vinyl sheet: 1 meter</li>
                    <li>Ink: 0.5 cartridge</li>
                    <li>Grommets: 4 pieces</li>
                </ul>
                <p>Create a BOM called "Standard Banner". Next time you need 50 banners, load the template and quantities auto-fill.</p>

                <h6>Step-by-Step to Create a BOM:</h6>
                <ol>
                    <li>Go to <kbd>Production → Bill of Materials</kbd></li>
                    <li>Click <span class="badge bg-primary">New Template</span></li>
                    <li>Enter a <strong>Template Name</strong></li>
                    <li>Add <strong>Materials</strong> with standard quantities</li>
                    <li>Click <span class="badge bg-success">Save Template</span></li>
                </ol>

                <h6>How to Use a BOM in a Production Job:</h6>
                <ol>
                    <li>Go to <kbd>Production → New Job</kbd></li>
                    <li>Under "Load from Template", select your BOM</li>
                    <li>Click <span class="badge bg-primary">Load</span></li>
                    <li>Materials auto-fill. Adjust quantities if needed.</li>
                    <li>Complete the job as normal</li>
                </ol>
            </div>
        </div>

        <!-- ========== EXPENSES ========== -->
        <div id="module-expenses" class="guide-section mb-5">
            <h2 class="border-bottom pb-2 mb-3"><i class="bi bi-wallet2-fill me-2 text-secondary"></i>💸 Expenses</h2>
            <p><strong>What are expenses?</strong> Business costs that aren't product purchases — rent, salaries, electricity, internet, etc.</p>

            <h6>Step-by-Step to Add an Expense:</h6>
            <ol>
                <li>Go to <kbd>Expenses → Add Expense</kbd></li>
                <li>Select an <strong>Expense Category</strong> (or create one first)</li>
                <li>Enter the <strong>Amount</strong> and <strong>Currency</strong></li>
                <li>Pick the <strong>Expense Date</strong></li>
                <li>Add a <strong>Description</strong> (e.g., "December rent payment")</li>
                <li>Upload a <strong>Receipt</strong> image (optional but recommended)</li>
                <li>Click <span class="badge bg-success">Save Expense</span></li>
            </ol>

            <div class="tip-box">
                <i class="bi bi-image-fill me-2"></i> <strong>Tip:</strong> Uploading receipts helps with audit trails. You can view them later from the expense list.
            </div>
        </div>

        <!-- ========== ADJUSTMENTS ========== -->
        <div id="module-adjustments" class="guide-section mb-5">
            <h2 class="border-bottom pb-2 mb-3"><i class="bi bi-arrow-down-up-fill me-2 text-danger"></i>📊 Adjustments (Damage/Returns)</h2>
            <p><strong>When to use this module?</strong> When inventory changes due to damage, theft, customer returns, or refunds — outside of normal sales and purchases.</p>

            <h6>Types of Adjustments:</h6>
            <table class="table table-bordered table-sm">
                <thead class="table-light">
                    <tr><th>Type</th><th>When to Use</th><th>Stock Effect</th></tr>
                </thead>
                <tbody>
                    <tr><td>Damage</td><td>Product broken during production or handling</td><td class="text-danger">Decreases</td></tr>
                    <tr><td>Refund</td><td>Customer returns product AND gets money back</td><td class="text-danger">Decreases</td></tr>
                    <tr><td>Return</td><td>Customer returns product (exchange, no refund)</td><td class="text-success">Increases</td></tr>
                    <tr><td>Theft</td><td>Product stolen or missing</td><td class="text-danger">Decreases</td></tr>
                    <tr><td>Other</td><td>Any other reason</td><td>Optional</td></tr>
                </tbody>
            </table>

            <h6>Step-by-Step to Create an Adjustment:</h6>
            <ol>
                <li>Go to <kbd>Adjustments → New Adjustment</kbd></li>
                <li>Select the <strong>Product</strong></li>
                <li>Choose <strong>Event Type</strong> (Damage, Refund, Return, Theft, Other)</li>
                <li>Enter <strong>Quantity</strong></li>
                <li>Set <strong>Unit Cost</strong> (auto-fills from product purchase price)</li>
                <li>Select <strong>Currency</strong></li>
                <li>Choose <strong>Customer</strong> (for returns/refunds — optional)</li>
                <li>Add a <strong>Reference</strong> (e.g., invoice number for a refund)</li>
                <li>Write a <strong>Description</strong> explaining what happened</li>
                <li>Select <strong>Adjust Stock?</strong> — Yes or No</li>
                <li>Click <span class="badge bg-success">Record Adjustment</span></li>
            </ol>

            <div class="success-box mt-3">
                <i class="bi bi-graph-up-fill me-2"></i> <strong>Financial Impact:</strong> Adjustments appear in financial reports, so you can track total losses from damage and theft.
            </div>
        </div>

        <!-- ========== REPORTS ========== -->
        <div id="module-reports" class="guide-section mb-5">
            <h2 class="border-bottom pb-2 mb-3"><i class="bi bi-graph-up-fill me-2 text-primary"></i>📈 Reports</h2>
            <p>Reports help you understand your business performance. All reports can be filtered by date range and exported to Excel or PDF.</p>

            <div class="row">
                <div class="col-md-6">
                    <div class="module-card card p-3 mb-3" style="border-left-color: #0d6efd;">
                        <h6><i class="bi bi-box-seam me-2 text-primary"></i>Inventory Reports</h6>
                        <p class="small">See current stock levels, low-stock items, out-of-stock products, and stock valuation.</p>
                        <p class="small mb-0"><strong>Use case:</strong> "What products need reordering?"</p>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="module-card card p-3 mb-3" style="border-left-color: #198754;">
                        <h6><i class="bi bi-cart me-2 text-success"></i>Sales Reports</h6>
                        <p class="small">View revenue by period, top-selling products, sales by customer, and sales by seller.</p>
                        <p class="small mb-0"><strong>Use case:</strong> "Who are my best customers?"</p>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="module-card card p-3 mb-3" style="border-left-color: #dc3545;">
                        <h6><i class="bi bi-currency-dollar me-2 text-danger"></i>Financial Reports</h6>
                        <p class="small">See profit & loss, revenue, expenses, and adjustment impacts (damage, theft, refunds).</p>
                        <p class="small mb-0"><strong>Use case:</strong> "Am I making a profit?"</p>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="module-card card p-3 mb-3" style="border-left-color: #ffc107;">
                        <h6><i class="bi bi-gear me-2 text-warning"></i>Production Reports</h6>
                        <p class="small">Track material usage, damage rates, job costs, and production efficiency.</p>
                        <p class="small mb-0"><strong>Use case:</strong> "How much material is wasted?"</p>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="module-card card p-3 mb-3" style="border-left-color: #6f42c1;">
                        <h6><i class="bi bi-arrow-left-right me-2 text-purple"></i>Stock Movement Report</h6>
                        <p class="small">See every stock change: purchases, sales, production, adjustments.</p>
                        <p class="small mb-0"><strong>Use case:</strong> "Why did stock change on this date?"</p>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="module-card card p-3 mb-3" style="border-left-color: #fd7e14;">
                        <h6><i class="bi bi-arrow-down-up me-2 text-orange"></i>Adjustments Report</h6>
                        <p class="small">View all damage, refund, return, and theft events with totals.</p>
                        <p class="small mb-0"><strong>Use case:</strong> "How much value was lost to damage?"</p>
                    </div>
                </div>
            </div>

            <h6>How to Use Reports:</h6>
            <ol>
                <li>Go to <kbd>Reports → [Report Type]</kbd></li>
                <li>Set your <strong>Date Range</strong> (or use preset periods like "Last 30 Days")</li>
                <li>Apply optional <strong>Filters</strong> (product, category, customer, etc.)</li>
                <li>View the data in tables and charts</li>
                <li>Click <span class="badge bg-success">Export</span> to download as Excel or PDF</li>
            </ol>

            <div class="tip-box">
                <i class="bi bi-download me-2"></i> <strong>Tip:</strong> Export reports regularly for record-keeping or sharing with accountants.
            </div>
        </div>

        <!-- ========== ADMINISTRATION ========== -->
        <div id="module-administration" class="guide-section mb-5">
            <h2 class="border-bottom pb-2 mb-3"><i class="bi bi-shield-lock-fill me-2 text-secondary"></i>⚙️ Administration</h2>
            <p><strong>Note:</strong> These features are only available to users with <strong>Admin</strong> role.</p>

            <h6>User Management:</h6>
            <ol>
                <li>Go to <kbd>Administration → User Management</kbd></li>
                <li>Click <span class="badge bg-primary">Add User</span></li>
                <li>Fill in username, email, password, full name, phone, and role</li>
                <li>Set <strong>Active</strong> status (inactive users cannot log in)</li>
                <li>Click <span class="badge bg-success">Save</span></li>
            </ol>

            <h6>Role Permissions:</h6>
            <ol>
                <li>Go to <kbd>Administration → Role Permissions</kbd></li>
                <li>Select a role (Admin, Manager, Staff)</li>
                <li>Check/uncheck permissions for each module</li>
                <li>Click <span class="badge bg-success">Save Permissions</span></li>
            </ol>

            <h6>System Settings:</h6>
            <ul>
                <li><strong>Business Information:</strong> Company name, address, phone, email (appears on invoices)</li>
                <li><strong>Currency Settings:</strong> Default currency, exchange rates</li>
                <li><strong>Invoice Settings:</strong> Tax rate, invoice prefix</li>
            </ul>

            <h6>Backup Management:</h6>
            <div class="warning-box">
                <i class="bi bi-database-fill me-2"></i> <strong>Important:</strong> Create regular backups! If your database is lost, backups are the only way to recover.
            </div>
            <ol>
                <li>Go to <kbd>Administration → Backup</kbd></li>
                <li>Click <span class="badge bg-primary">Create Backup</span></li>
                <li>The system creates a .sql file with all your data</li>
                <li>Download and store it safely (external drive, cloud storage)</li>
            </ol>

            <h6>Audit Logs:</h6>
            <p>View a complete history of who did what in the system. Useful for security and troubleshooting.</p>
            <ul>
                <li>See user actions (create, update, delete)</li>
                <li>Track IP addresses and timestamps</li>
                <li>Filter by user, action, or date range</li>
                <li>Export logs for external review</li>
            </ul>
        </div>

        <!-- ========== PROFILE & SECURITY ========== -->
        <div id="module-profile" class="guide-section mb-5">
            <h2 class="border-bottom pb-2 mb-3"><i class="bi bi-person-circle-fill me-2 text-primary"></i>👤 Profile &amp; Security</h2>

            <h6>Updating Your Profile:</h6>
            <ol>
                <li>Click your name in the top-right corner</li>
                <li>Select <strong>My Profile</strong></li>
                <li>Update your name, email, or phone number</li>
                <li>Click <span class="badge bg-success">Update Profile</span></li>
            </ol>

            <h6>Changing Your Password:</h6>
            <ol>
                <li>Go to <kbd>Profile → Change Password</kbd></li>
                <li>Enter your <strong>Current Password</strong></li>
                <li>Enter a <strong>New Password</strong> (minimum 6 characters)</li>
                <li>Confirm the new password</li>
                <li>Click <span class="badge bg-success">Change Password</span></li>
            </ol>

            <h6>Two-Factor Authentication (2FA):</h6>
            <p>Add an extra layer of security. After enabling, you'll need both your password and a code from your phone to log in.</p>
            
            <h6>Step-by-Step to Enable 2FA:</h6>
            <ol>
                <li>Go to <kbd>Profile → Setup 2FA</kbd></li>
                <li>Scan the QR code with Google Authenticator or similar app</li>
                <li>Enter the 6-digit code from your app</li>
                <li>Click <span class="badge bg-success">Enable 2FA</span></li>
            </ol>

            <div class="tip-box">
                <i class="bi bi-shield-check me-2"></i> <strong>Tip:</strong> Store backup codes in a safe place. If you lose your phone, you'll need them to access your account.
            </div>
        </div>

        <!-- ========== FAQ ========== -->
        <div id="faq" class="guide-section mb-5">
            <h2 class="border-bottom pb-2 mb-3"><i class="bi bi-question-circle-fill me-2 text-info"></i>❓ Frequently Asked Questions</h2>

            <div class="accordion" id="faqAccordion">
                <div class="accordion-item">
                    <h3 class="accordion-header">
                        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq1">
                            How do I record a customer return?
                        </button>
                    </h3>
                    <div id="faq1" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                        <div class="accordion-body">
                            Go to <kbd>Adjustments → New Adjustment</kbd>. Select the product, choose <strong>Event Type: Return</strong>, enter the quantity, and set <strong>Adjust Stock: Yes</strong>. This adds the product back to inventory.
                        </div>
                    </div>
                </div>

                <div class="accordion-item">
                    <h3 class="accordion-header">
                        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq2">
                            What's the difference between Stock Adjustment and Adjustments module?
                        </button>
                    </h3>
                    <div id="faq2" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                        <div class="accordion-body">
                            <strong>Stock Adjustment</strong> (Inventory module) is for manual stock corrections like physical counts. <strong>Adjustments</strong> (separate module) is for tracking damage, returns, refunds, and theft — with links to sales and production jobs.
                        </div>
                    </div>
                </div>

                <div class="accordion-item">
                    <h3 class="accordion-header">
                        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq3">
                            Can I edit a completed production job?
                        </button>
                    </h3>
                    <div id="faq3" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                        <div class="accordion-body">
                            No. Once a job is marked <strong>Completed</strong>, materials are deducted from inventory and the job is locked. You can cancel it first, but that doesn't restore inventory.
                        </div>
                    </div>
                </div>

                <div class="accordion-item">
                    <h3 class="accordion-header">
                        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq4">
                            How do I switch between LRD and USD?
                        </button>
                    </h3>
                    <div id="faq4" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                        <div class="accordion-body">
                            Click the currency selector in the top-right corner of the navbar and choose LRD or USD. All amounts will convert using the current exchange rate.
                        </div>
                    </div>
                </div>

                <div class="accordion-item">
                    <h3 class="accordion-header">
                        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq5">
                            Why can't I delete a product?
                        </button>
                    </h3>
                    <div id="faq5" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                        <div class="accordion-body">
                            Products with existing sales, purchases, or production usage cannot be deleted. Instead, you can set them as inactive or adjust stock to zero.
                        </div>
                    </div>
                </div>

                <div class="accordion-item">
                    <h3 class="accordion-header">
                        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq6">
                            How often should I create backups?
                        </button>
                    </h3>
                    <div id="faq6" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                        <div class="accordion-body">
                            For active businesses: <strong>daily</strong>. For smaller operations: at least <strong>weekly</strong>. Always backup before making major changes.
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- ========== KEYBOARD SHORTCUTS ========== -->
        <div id="keyboard-shortcuts" class="guide-section mb-5">
            <h2 class="border-bottom pb-2 mb-3"><i class="bi bi-keyboard-fill me-2 text-secondary"></i>⌨️ Keyboard Shortcuts</h2>
            <div class="table-responsive">
                <table class="table table-bordered">
                    <thead class="table-light">
                        <tr><th>Shortcut</th><th>Action</th></tr>
                    </thead>
                    <tbody>
                        <tr><td><kbd>Ctrl</kbd> + <kbd>N</kbd></td><td>Create new (product, sale, purchase, etc.)</td></tr>
                        <tr><td><kbd>Ctrl</kbd> + <kbd>S</kbd></td><td>Save current form</td></tr>
                        <tr><td><kbd>Ctrl</kbd> + <kbd>F</kbd></td><td>Focus search box</td></tr>
                        <tr><td><kbd>Esc</kbd></td><td>Close modal or cancel</td></tr>
                        <tr><td><kbd>Ctrl</kbd> + <kbd>P</kbd></td><td>Print current page/invoice</td></tr>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Footer -->
        <hr class="mt-5">
        <p class="text-center text-muted small">
            <i class="bi bi-question-circle"></i> Need more help? Contact your system administrator.<br>
            Version 1.0 | Last updated: <?= date('F j, Y') ?>
        </p>
    </div>
</div>

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
$(document).ready(function() {
    // Highlight active sidebar section on scroll
    var navLinks = $('.guide-sidebar a');
    $(window).on('scroll', function() {
        var scrollPos = $(document).scrollTop() + 120;
        navLinks.each(function() {
            var target = $(this.getAttribute('href'));
            if (target.length) {
                if (target.position().top <= scrollPos && target.position().top + target.outerHeight() > scrollPos) {
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
            $('html, body').animate({ scrollTop: target.offset().top - 80 }, 300);
            // Update URL hash without jumping
            history.pushState(null, null, this.getAttribute('href'));
        }
    });
});
</script>
<?= $this->endSection() ?>