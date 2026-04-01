<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Financial Report</title>
    <style>
        body { font-family: Arial, sans-serif; font-size: 12px; color: #222; }
        h1, h2 { margin: 0 0 10px; }
        .meta { margin-bottom: 20px; color: #555; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        th { background: #f3f3f3; }
        .text-end { text-align: right; }
    </style>
</head>
<body>
    <h1><?= esc($business_name ?? 'Business') ?></h1>
    <h2>Financial Report</h2>
    <div class="meta">
        Period:
        <?= esc($startDate ?: 'Beginning') ?> to <?= esc($endDate ?: 'Present') ?><br>
        Generated:
        <?= esc($date ?? date('Y-m-d H:i:s')) ?>
    </div>

    <table>
        <thead>
            <tr>
                <th>Metric</th>
                <th class="text-end">LRD</th>
                <th class="text-end">USD</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>Revenue</td>
                <td class="text-end"><?= number_format((float) $revenueLRD, 2) ?></td>
                <td class="text-end"><?= number_format((float) $revenueUSD, 2) ?></td>
            </tr>
            <tr>
                <td>Estimated COGS</td>
                <td class="text-end"><?= number_format((float) $cogsLRD, 2) ?></td>
                <td class="text-end"><?= number_format((float) $cogsUSD, 2) ?></td>
            </tr>
            <tr>
                <td>Expenses</td>
                <td class="text-end"><?= number_format((float) $expensesLRD, 2) ?></td>
                <td class="text-end"><?= number_format((float) $expensesUSD, 2) ?></td>
            </tr>
            <tr>
                <td>Gross Profit</td>
                <td class="text-end"><?= number_format((float) $grossProfitLRD, 2) ?></td>
                <td class="text-end"><?= number_format((float) $grossProfitUSD, 2) ?></td>
            </tr>
            <tr>
                <td>Net Profit</td>
                <td class="text-end"><?= number_format((float) $netProfitLRD, 2) ?></td>
                <td class="text-end"><?= number_format((float) $netProfitUSD, 2) ?></td>
            </tr>
        </tbody>
    </table>

    <?php if (!empty($expenseLog)): ?>
    <h2>Expense Log</h2>
    <table>
        <thead>
            <tr>
                <th>Date</th>
                <th>Category</th>
                <th>Description</th>
                <th class="text-end">Amount</th>
                <th>Currency</th>
                <th>Recorded By</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($expenseLog as $expense): ?>
            <tr>
                <td><?= esc($expense['expense_date']) ?></td>
                <td><?= esc($expense['category_name'] ?? 'Uncategorized') ?></td>
                <td><?= esc($expense['description'] ?: '-') ?></td>
                <td class="text-end"><?= number_format((float) $expense['amount'], 2) ?></td>
                <td><?= esc($expense['currency']) ?></td>
                <td><?= esc($expense['created_by_name'] ?? 'Unknown') ?></td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
    <?php endif; ?>
</body>
</html>
