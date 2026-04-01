<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Production Report</title>
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
    <h2>Production Report</h2>
    <div class="meta">
        Period:
        <?= esc($startDate ?: 'Beginning') ?> to <?= esc($endDate ?: 'Present') ?><br>
        Generated:
        <?= esc($date ?? date('Y-m-d H:i:s')) ?>
    </div>

    <table>
        <thead>
            <tr>
                <th>Total Jobs</th>
                <th class="text-end">Material Cost LRD</th>
                <th class="text-end">Material Cost USD</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td><?= number_format((int) ($costSummary['job_count'] ?? 0)) ?></td>
                <td class="text-end"><?= number_format((float) ($costSummary['total_cost_lrd'] ?? 0), 2) ?></td>
                <td class="text-end"><?= number_format((float) ($costSummary['total_cost_usd'] ?? 0), 2) ?></td>
            </tr>
        </tbody>
    </table>

    <?php if (!empty($jobs)): ?>
    <table>
        <thead>
            <tr>
                <th>Date</th>
                <th>Job Ref</th>
                <th>Job Name</th>
                <th>Finished Product</th>
                <th>Status</th>
                <th>Currency</th>
                <th class="text-end">Qty Produced</th>
                <th class="text-end">Material Cost</th>
                <th>Recorded By</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($jobs as $job): ?>
            <tr>
                <td><?= esc($job['production_date']) ?></td>
                <td><?= esc($job['job_reference']) ?></td>
                <td><?= esc($job['job_name']) ?></td>
                <td><?= esc($job['finished_product_name'] ?: 'Consumables only') ?></td>
                <td><?= esc($job['status']) ?></td>
                <td><?= esc($job['currency']) ?></td>
                <td class="text-end"><?= number_format((float) ($job['quantity_produced'] ?? 0), 2) ?></td>
                <td class="text-end"><?= number_format((float) ($job['total_material_cost'] ?? 0), 2) ?></td>
                <td><?= esc($job['created_by_name'] ?? 'Unknown') ?></td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
    <?php endif; ?>
</body>
</html>
