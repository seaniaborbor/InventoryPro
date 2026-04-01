<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $title ?? 'Login' ?> | Innovative Graphics</title>
    
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    
    <style>
        :root {
            --panel-bg: #ffffff;
            --page-bg: #f4f6f8;
            --accent: #1e293b;
            --accent-soft: #334155;
            --muted: #64748b;
            --border: #dbe1e8;
            --primary: #0d6efd;
        }

        body {
            background: var(--page-bg);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            color: #1f2937;
            padding: 1.5rem;
        }
        
        .login-card {
            background: var(--panel-bg);
            border-radius: 16px;
            box-shadow: 0 10px 30px rgba(15, 23, 42, 0.08);
            overflow: hidden;
            width: 100%;
            max-width: 450px;
            border: 1px solid var(--border);
        }
        
        .login-header {
            background: var(--accent);
            color: white;
            padding: 30px;
            text-align: center;
        }
        
        .login-header h3 {
            margin: 0;
            font-size: 1.5rem;
        }
        
        .login-header p {
            margin: 5px 0 0;
            opacity: 0.8;
            font-size: 0.85rem;
        }
        
        .login-body {
            padding: 30px;
        }
        
        .form-control {
            border-radius: 8px;
            border: 1px solid var(--border);
            padding: 12px 15px;
        }
        
        .form-control:focus {
            border-color: var(--primary);
            box-shadow: 0 0 0 0.2rem rgba(13, 110, 253, 0.15);
        }
        
        .btn-login {
            background: var(--primary);
            border: 1px solid var(--primary);
            border-radius: 8px;
            padding: 12px;
            font-weight: 600;
            width: 100%;
            transition: transform 0.3s;
        }
        
        .btn-login:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(13, 110, 253, 0.2);
            background: #0b5ed7;
            border-color: #0b5ed7;
        }
        
        .alert {
            border-radius: 8px;
            margin-bottom: 20px;
        }
        
        .footer {
            text-align: center;
            padding: 20px;
            background: #f8fafc;
            font-size: 0.8rem;
            color: var(--muted);
        }
    </style>
</head>
<body>
    <div class="login-card">
        <div class="login-header">
            <i class="bi bi-printer" style="font-size: 3rem;"></i>
            <h3>Innovative Graphics</h3>
            <p>Inventory Management System</p>
        </div>
        
        <div class="login-body">
            <?php if (session()->getFlashdata('error')): ?>
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <i class="bi bi-exclamation-triangle-fill me-2"></i>
                    <?= session()->getFlashdata('error') ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>
            
            <?php if (session()->getFlashdata('success')): ?>
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <i class="bi bi-check-circle-fill me-2"></i>
                    <?= session()->getFlashdata('success') ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>
            
            <?= $this->renderSection('content') ?>
        </div>
        
        <div class="footer">
            &copy; <?= date('Y') ?> Innovative Graphics Design & Computer Solutions<br>
            Broad & Benson Streets, Metropolitan Building, Monrovia, Liberia
        </div>
    </div>
    
    <!-- Bootstrap JS Bundle -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <?= $this->renderSection('scripts') ?>
</body>
</html>
