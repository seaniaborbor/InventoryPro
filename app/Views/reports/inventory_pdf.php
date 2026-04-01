<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Inventory Report - <?= $business_name ?></title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
            line-height: 1.4;
            margin: 20px;
        }
        .header {
            text-align: center;
            margin-bottom: 30px;
            border-bottom: 2px solid #333;
            padding-bottom: 10px;
        }
        .header h1 {
            margin: 0;
            font-size: 24px;
            color: #333;
        }
        .header p {
            margin: 5px 0;
            color: #666;
        }
        .summary {
            display: table;
            width: 100%;
            margin-bottom: 20px;
        }
        .summary-item {
            display: table-cell;
            width: 25%;
            text-align: center;
            padding: 10px;
            background: #f8f8f8;
            border: 1px solid #ddd;
        }
        .summary-item h3 {
            margin: 0;
            font-size: 18px;
            color: #333;
        }
        .summary-item p {
            margin: 5px 0 0 0;
            color: #666;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        th, td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
        }
        th {
            background-color: #f2f2f2;
            font-weight: bold;
        }
        tr:nth-child(even) {
            background-color: #f9f9f9;
        }
        .text-center {
            text-align: center;
        }
        .text-right {
            text-align: right;
        }
        .badge {
            display: inline-block;
            padding: 3px 8px;
            font-size: 11px;
            font-weight: bold;
            border-radius: 3px;
            text-transform: uppercase;
        }
        .badge-success { background: #d4edda; color: #155724; }
        .badge-warning { background: #fff3cd; color: #856404; }
        .badge-danger { background: #f8d7da; color: #721c24; }
        .total-row {
            background-color: #e9ecef !important;
            font-weight: bold;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>Inventory Report</h1>
        <p><strong>Business:</strong> <?= $business_name ?></p>
        <p><strong>Report Date:</strong> <?= date('F d, Y H:i', strtotime($date)) ?></p>
        <?php if ($categoryId): ?>
            <p><strong>Category:</strong>
                <?php
                $cat = array_filter($categories, function($c) use ($categoryId) { return $c['id'] == $categoryId; });
                echo !empty($cat) ? reset($cat)['category_name'] : 'Unknown';
                ?>
            </p>
        <?php endif; ?>
        <?php if ($stockStatus): ?>
            <p><strong>Stock Status:</strong>
                <?= $stockStatus == 'low' ? 'Low Stock Only' : ($stockStatus == 'out' ? 'Out of Stock Only' : 'All') ?>
            </p>
        <?php endif; ?>
    </div>

    <div class="summary">
        <div class="summary-item">
            <h3><?= number_format($summary['total_products'] ?? count($products)) ?></h3>
            <p>Total Products</p>
        </div>
        <div class="summary-item">
            <h3><?= number_format($summary['total_stock'] ?? array_sum(array_column($products, 'quantity')), 2) ?></h3>
            <p>Total Stock</p>
        </div>
        <div class="summary-item">
            <h3><?= number_format($summary['low_stock_count'] ?? 0) ?></h3>
            <p>Low Stock Items</p>
        </div>
        <div class="summary-item">
            <h3><?= number_format($summary['out_of_stock_count'] ?? 0) ?></h3>
            <p>Out of Stock</p>
        </div>
    </div>

    <table>
        <thead>
            <tr>
                <th>Product Name</th>
                <th>SKU</th>
                <th>Category</th>
                <th>Current Stock</th>
                <th>Min Stock</th>
                <th>Unit Price</th>
                <th>Total Value</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            <?php
            $totalValue = 0;
            foreach ($products as $product):
                $productValue = $product['quantity'] * $product['selling_price'];
                $totalValue += $productValue;

                $status = $product['stock_status'] ?? 'normal';
                $statusClass = $status === 'out' ? 'danger' : ($status === 'low' ? 'warning' : 'success');
            ?>
            <tr>
                <td>
                    <strong><?= $product['product_name'] ?></strong>
                    <?php if ($product['description']): ?>
                        <br><small style="color: #666;"><?= substr($product['description'], 0, 50) ?>...</small>
                    <?php endif; ?>
                </td>
                <td><code><?= $product['sku'] ?></code></td>
                <td><span class="badge badge-secondary"><?= $product['category_name'] ?></span></td>
                <td class="text-center">
                    <span class="badge badge-<?= $statusClass ?>">
                        <?= $product['quantity'] ?> <?= $product['unit_symbol'] ?? $product['unit_name'] ?? '' ?>
                    </span>
                </td>
                <td class="text-center"><?= $product['minimum_stock'] ?> <?= $product['unit_symbol'] ?? $product['unit_name'] ?? '' ?></td>
                <td class="text-right"><?= formatCurrency($product['selling_price'], 'LRD') ?></td>
                <td class="text-right"><?= formatCurrency($productValue, 'LRD') ?></td>
                <td>
                    <?php if ($status == 'out'): ?>
                        <span class="badge badge-danger">Out of Stock</span>
                    <?php elseif ($status == 'low'): ?>
                        <span class="badge badge-warning">Low Stock</span>
                    <?php else: ?>
                        <span class="badge badge-success">In Stock</span>
                    <?php endif; ?>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
        <tfoot>
            <tr class="total-row">
                <td colspan="6" class="text-right"><strong>Total Inventory Value:</strong></td>
                <td class="text-right"><strong><?= formatCurrency($summary['selling_value'] ?? $totalValue, 'LRD') ?></strong></td>
                <td></td>
            </tr>
        </tfoot>
    </table>
</body>
</html>
