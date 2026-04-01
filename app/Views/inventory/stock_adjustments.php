<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0"><i class="bi bi-arrow-left-right me-2"></i>Stock Adjustments</h5>
            </div>
            <div class="card-body">
                <div class="alert alert-info">
                    <i class="bi bi-info-circle"></i> 
                    <strong>Stock Adjustments History:</strong> This page shows all stock adjustments made to products including initial stock entries, manual adjustments, and corrections.
                </div>
                
                <!-- Filter Bar -->
                <div class="row mb-3">
                    <div class="col-md-3">
                        <input type="text" id="searchProduct" class="form-control" placeholder="Search by product...">
                    </div>
                    <div class="col-md-3">
                        <select id="filterType" class="form-select">
                            <option value="all">All Adjustments</option>
                            <option value="increase">Increase (+)</option>
                            <option value="decrease">Decrease (-)</option>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <input type="text" id="dateRange" class="form-control" placeholder="Date range (optional)">
                    </div>
                    <div class="col-md-2">
                        <button class="btn btn-secondary w-100" onclick="resetFilters()">Reset</button>
                    </div>
                </div>
                
                <div class="table-responsive">
                    <table class="table table-hover" id="adjustmentsTable">
                        <thead>
                            <tr>
                                <th>Date & Time</th>
                                <th>Product</th>
                                <th>Adjustment Type</th>
                                <th>Quantity</th>
                                <th>Previous Stock</th>
                                <th>New Stock</th>
                                <th>Reason</th>
                                <th>User</th>
                             </thead>
                        <tbody>
                            <tr>
                                <td colspan="8" class="text-center text-muted">
                                    <div class="py-5">
                                        <i class="bi bi-arrow-repeat" style="font-size: 2rem;"></i>
                                        <p class="mt-2">Loading stock adjustments...</p>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                     </table>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
$(document).ready(function() {
    loadAdjustments();
});

var adjustmentsTable = null;

function loadAdjustments() {
    $.ajax({
        url: '<?= base_url('inventory/get-stock-adjustments') ?>',
        type: 'GET',
        dataType: 'json',
        success: function(response) {
            if (response.status === 'success') {
                var data = response.data;
                
                if (adjustmentsTable) {
                    adjustmentsTable.clear().destroy();
                }
                
                if (data.length === 0) {
                    $('#adjustmentsTable tbody').html('<tr><td colspan="8" class="text-center text-muted"><div class="py-5"><i class="bi bi-inbox" style="font-size: 2rem;"></i><p class="mt-2">No stock adjustments found. Make your first adjustment from the Products page.</p></div></td></tr>');
                    return;
                }
                
                adjustmentsTable = $('#adjustmentsTable').DataTable({
                    data: data,
                    columns: [
                        { 
                            data: 'created_at',
                            render: function(data) {
                                if (!data) return '-';
                                var date = new Date(data);
                                return date.toLocaleString();
                            }
                        },
                        { data: 'product_name', defaultContent: 'Unknown Product' },
                        { 
                            data: 'adjustment_type',
                            render: function(data) {
                                if (data === 'increase') {
                                    return '<span class="badge bg-success"><i class="bi bi-plus-circle"></i> Increase</span>';
                                } else {
                                    return '<span class="badge bg-danger"><i class="bi bi-dash-circle"></i> Decrease</span>';
                                }
                            }
                        },
                        { 
                            data: 'quantity',
                            render: function(data) {
                                var num = parseFloat(data);
                                return num.toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2});
                            }
                        },
                        { 
                            data: 'previous_quantity',
                            render: function(data) {
                                var num = parseFloat(data);
                                return num.toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2});
                            }
                        },
                        { 
                            data: 'new_quantity',
                            render: function(data) {
                                var num = parseFloat(data);
                                return '<strong>' + num.toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2}) + '</strong>';
                            }
                        },
                        { data: 'reason', defaultContent: 'Stock Adjustment' },
                        { data: 'created_by_name', defaultContent: 'System' }
                    ],
                    order: [[0, 'desc']],
                    pageLength: 25,
                    language: {
                        emptyTable: "No stock adjustments found"
                    }
                });
                
                // Apply search filter
                $('#searchProduct').on('keyup', function() {
                    adjustmentsTable.column(1).search(this.value).draw();
                });
                
                // Apply type filter
                $('#filterType').on('change', function() {
                    var type = this.value;
                    if (type === 'increase') {
                        adjustmentsTable.column(2).search('Increase').draw();
                    } else if (type === 'decrease') {
                        adjustmentsTable.column(2).search('Decrease').draw();
                    } else {
                        adjustmentsTable.column(2).search('').draw();
                    }
                });
                
            } else {
                $('#adjustmentsTable tbody').html('<tr><td colspan="8" class="text-center text-danger">Error loading data: ' + (response.message || 'Unknown error') + '</td></tr>');
                showToast('Error', response.message || 'Failed to load stock adjustments', 'error');
            }
        },
        error: function(xhr, status, error) {
            console.error('AJAX Error:', error);
            $('#adjustmentsTable tbody').html('<tr><td colspan="8" class="text-center text-danger">Failed to load stock adjustments. Please refresh the page.</td></tr>');
            showToast('Error', 'Failed to load stock adjustments', 'error');
        }
    });
}

function resetFilters() {
    $('#searchProduct').val('');
    $('#filterType').val('all');
    $('#dateRange').val('');
    if (adjustmentsTable) {
        adjustmentsTable.column(1).search('').draw();
        adjustmentsTable.column(2).search('').draw();
    }
}

// Auto-refresh every 30 seconds (optional)
// setInterval(loadAdjustments, 30000);
</script>
<?= $this->endSection() ?>