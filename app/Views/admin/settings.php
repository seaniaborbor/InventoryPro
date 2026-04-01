<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0"><i class="bi bi-sliders2 me-2"></i>System Settings</h5>
            </div>
            <div class="card-body">
                <?php if (session()->getFlashdata('success')): ?>
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        <?= session()->getFlashdata('success') ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>
                
                <form action="<?= base_url('admin/settings/update') ?>" method="post">
                    <?= csrf_field() ?>
                    
                    <ul class="nav nav-tabs" id="settingsTab" role="tablist">
                        <li class="nav-item" role="presentation">
                            <button class="nav-link active" id="general-tab" data-bs-toggle="tab" data-bs-target="#general" type="button" role="tab">General</button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="currency-tab" data-bs-toggle="tab" data-bs-target="#currency" type="button" role="tab">Currency</button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="inventory-tab" data-bs-toggle="tab" data-bs-target="#inventory" type="button" role="tab">Inventory</button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="security-tab" data-bs-toggle="tab" data-bs-target="#security" type="button" role="tab">Security</button>
                        </li>
                    </ul>
                    
                    <div class="tab-content mt-3">
                        <!-- General Settings -->
                        <div class="tab-pane fade show active" id="general" role="tabpanel">
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="setting_business_name" class="form-label">Business Name</label>
                                    <input type="text" class="form-control" id="setting_business_name" 
                                           name="setting_business_name" value="<?= $settings['business_name'] ?? '' ?>">
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="setting_business_phone" class="form-label">Business Phone</label>
                                    <input type="text" class="form-control" id="setting_business_phone" 
                                           name="setting_business_phone" value="<?= $settings['business_phone'] ?? '' ?>">
                                </div>
                                <div class="col-md-12 mb-3">
                                    <label for="setting_business_address" class="form-label">Business Address</label>
                                    <textarea class="form-control" id="setting_business_address" 
                                              name="setting_business_address" rows="3"><?= $settings['business_address'] ?? '' ?></textarea>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="setting_business_email" class="form-label">Business Email</label>
                                    <input type="email" class="form-control" id="setting_business_email" 
                                           name="setting_business_email" value="<?= $settings['business_email'] ?? '' ?>">
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="setting_date_format" class="form-label">Date Format</label>
                                    <select class="form-select" id="setting_date_format" name="setting_date_format">
                                        <option value="Y-m-d" <?= ($settings['date_format'] ?? '') == 'Y-m-d' ? 'selected' : '' ?>>YYYY-MM-DD</option>
                                        <option value="d/m/Y" <?= ($settings['date_format'] ?? '') == 'd/m/Y' ? 'selected' : '' ?>>DD/MM/YYYY</option>
                                        <option value="m/d/Y" <?= ($settings['date_format'] ?? '') == 'm/d/Y' ? 'selected' : '' ?>>MM/DD/YYYY</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Currency Settings -->
                        <div class="tab-pane fade" id="currency" role="tabpanel">
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="setting_default_currency" class="form-label">Default Currency</label>
                                    <select class="form-select" id="setting_default_currency" name="setting_default_currency">
                                        <option value="LRD" <?= ($settings['default_currency'] ?? '') == 'LRD' ? 'selected' : '' ?>>LRD - Liberian Dollar</option>
                                        <option value="USD" <?= ($settings['default_currency'] ?? '') == 'USD' ? 'selected' : '' ?>>USD - US Dollar</option>
                                    </select>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="setting_currency_symbol_lrd" class="form-label">LRD Symbol</label>
                                    <input type="text" class="form-control" id="setting_currency_symbol_lrd" 
                                           name="setting_currency_symbol_lrd" value="<?= $settings['currency_symbol_lrd'] ?? 'L$' ?>">
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="setting_currency_symbol_usd" class="form-label">USD Symbol</label>
                                    <input type="text" class="form-control" id="setting_currency_symbol_usd" 
                                           name="setting_currency_symbol_usd" value="<?= $settings['currency_symbol_usd'] ?? '$' ?>">
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="setting_exchange_rate" class="form-label">Exchange Rate (USD to LRD)</label>
                                    <input type="number" step="0.0001" class="form-control" id="setting_exchange_rate" 
                                           name="setting_exchange_rate" value="<?= $settings['exchange_rate'] ?? '180.00' ?>">
                                </div>
                            </div>
                        </div>
                        
                        <!-- Inventory Settings -->
                        <div class="tab-pane fade" id="inventory" role="tabpanel">
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="setting_low_stock_threshold" class="form-label">Low Stock Threshold</label>
                                    <input type="number" class="form-control" id="setting_low_stock_threshold" 
                                           name="setting_low_stock_threshold" value="<?= $settings['low_stock_threshold'] ?? '10' ?>">
                                    <small class="text-muted">Products with stock below this level will trigger alerts</small>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Security Settings -->
                        <div class="tab-pane fade" id="security" role="tabpanel">
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="setting_session_timeout" class="form-label">Session Timeout (seconds)</label>
                                    <input type="number" class="form-control" id="setting_session_timeout" 
                                           name="setting_session_timeout" value="<?= $settings['session_timeout'] ?? '3600' ?>">
                                    <small class="text-muted">User will be logged out after inactivity</small>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="mt-3">
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-save"></i> Save Settings
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
<?= $this->endSection() ?>