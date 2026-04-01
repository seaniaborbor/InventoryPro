<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>404 - Page Not Found | Innovative Graphics</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <style>
        body {
            min-height: 100vh;
            margin: 0;
            display: flex;
            align-items: center;
            justify-content: center;
            background: #f4f6f8;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            color: #1f2937;
            padding: 1.5rem;
        }

        .error-card {
            max-width: 560px;
            width: 100%;
            background: #ffffff;
            border: 1px solid #dbe1e8;
            border-radius: 18px;
            box-shadow: 0 12px 28px rgba(15, 23, 42, 0.08);
            overflow: hidden;
        }

        .error-header {
            background: #1e293b;
            color: #ffffff;
            text-align: center;
            padding: 2rem;
        }

        .error-body {
            padding: 2rem;
            text-align: center;
        }

        .error-code {
            font-size: 5rem;
            line-height: 1;
            font-weight: 700;
            color: #0d6efd;
            margin-bottom: 1rem;
        }

        .btn-home {
            min-width: 220px;
        }
    </style>
</head>
<body>
    <div class="error-card">
        <div class="error-header">
            <i class="bi bi-compass" style="font-size: 2.5rem;"></i>
            <h2 class="mt-3 mb-1">Innovative Graphics</h2>
            <p class="mb-0 opacity-75">Inventory Management System</p>
        </div>

        <div class="error-body">
            <div class="error-code">404</div>
            <h4 class="mb-3">Page Not Found</h4>
            <p class="text-muted mb-4">
                The page you requested could not be found. It may have been moved, deleted, or the address may be incorrect.
            </p>
            <a href="<?= base_url('dashboard') ?>" class="btn btn-primary btn-home">
                <i class="bi bi-house-door me-2"></i>Return to Dashboard
            </a>
        </div>
    </div>
</body>
</html>
