<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($title ?? 'CitizenReport Hub') ?> - CitizenReport Hub</title>
    
    <!-- Leaflet CSS -->
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" 
          integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" 
          crossorigin=""/>
    
    <style>
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, sans-serif;
            line-height: 1.6;
            color: #333;
            background-color: #f5f5f5;
        }

        /* Header */
        .header {
            background: linear-gradient(135deg, #1a73e8 0%, #0d47a1 100%);
            color: white;
            padding: 1rem 2rem;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }

        .header-content {
            max-width: 1200px;
            margin: 0 auto;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .logo {
            font-size: 1.5rem;
            font-weight: bold;
            text-decoration: none;
            color: white;
        }

        .nav {
            display: flex;
            gap: 1rem;
            align-items: center;
        }

        .nav a {
            color: white;
            text-decoration: none;
            padding: 0.5rem 1rem;
            border-radius: 4px;
            transition: background-color 0.2s;
        }

        .nav a:hover {
            background-color: rgba(255,255,255,0.1);
        }

        .nav .user-info {
            background: rgba(255,255,255,0.1);
            padding: 0.5rem 1rem;
            border-radius: 4px;
        }

        .btn {
            display: inline-block;
            padding: 0.75rem 1.5rem;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            text-decoration: none;
            font-size: 1rem;
            transition: all 0.2s;
        }

        .btn-primary {
            background-color: #1a73e8;
            color: white;
        }

        .btn-primary:hover {
            background-color: #1557b0;
        }

        .btn-secondary {
            background-color: #6c757d;
            color: white;
        }

        .btn-secondary:hover {
            background-color: #545b62;
        }

        .btn-danger {
            background-color: #dc3545;
            color: white;
        }

        .btn-danger:hover {
            background-color: #c82333;
        }

        .btn-success {
            background-color: #28a745;
            color: white;
        }

        .btn-success:hover {
            background-color: #218838;
        }

        /* Main content */
        .main {
            max-width: 1200px;
            margin: 0 auto;
            padding: 2rem;
            min-height: calc(100vh - 200px);
        }

        /* Flash messages */
        .flash-messages {
            margin-bottom: 1.5rem;
        }

        .flash {
            padding: 1rem;
            border-radius: 4px;
            margin-bottom: 0.5rem;
        }

        .flash-success {
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }

        .flash-error {
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }

        .flash-warning {
            background-color: #fff3cd;
            color: #856404;
            border: 1px solid #ffeeba;
        }

        /* Cards */
        .card {
            background: white;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            padding: 1.5rem;
            margin-bottom: 1.5rem;
        }

        .card-title {
            font-size: 1.25rem;
            margin-bottom: 1rem;
            color: #1a73e8;
        }

        /* Forms */
        .form-group {
            margin-bottom: 1.5rem;
        }

        .form-label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: 600;
            color: #333;
        }

        .form-control {
            width: 100%;
            padding: 0.75rem;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 1rem;
            transition: border-color 0.2s;
        }

        .form-control:focus {
            outline: none;
            border-color: #1a73e8;
            box-shadow: 0 0 0 3px rgba(26,115,232,0.1);
        }

        .form-control.is-invalid {
            border-color: #dc3545;
        }

        .invalid-feedback {
            color: #dc3545;
            font-size: 0.875rem;
            margin-top: 0.25rem;
        }

        textarea.form-control {
            min-height: 120px;
            resize: vertical;
        }

        select.form-control {
            cursor: pointer;
        }

        /* Tables */
        .table {
            width: 100%;
            border-collapse: collapse;
            background: white;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }

        .table th,
        .table td {
            padding: 1rem;
            text-align: left;
            border-bottom: 1px solid #eee;
        }

        .table th {
            background-color: #f8f9fa;
            font-weight: 600;
            color: #333;
        }

        .table tr:hover {
            background-color: #f8f9fa;
        }

        .table tr:last-child td {
            border-bottom: none;
        }

        /* Status badges */
        .badge {
            display: inline-block;
            padding: 0.25rem 0.75rem;
            border-radius: 50px;
            font-size: 0.875rem;
            font-weight: 500;
        }

        .badge-pending {
            background-color: #ffc107;
            color: #000;
        }

        .badge-in_progress {
            background-color: #17a2b8;
            color: white;
        }

        .badge-resolved {
            background-color: #28a745;
            color: white;
        }

        .badge-rejected {
            background-color: #dc3545;
            color: white;
        }

        /* Footer */
        .footer {
            background-color: #333;
            color: #aaa;
            padding: 1.5rem 2rem;
            text-align: center;
        }

        /* Map container */
        #map {
            height: 400px;
            width: 100%;
            border-radius: 8px;
            border: 2px solid #ddd;
        }

        /* Utility classes */
        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .mb-1 { margin-bottom: 0.5rem; }
        .mb-2 { margin-bottom: 1rem; }
        .mb-3 { margin-bottom: 1.5rem; }
        .mt-1 { margin-top: 0.5rem; }
        .mt-2 { margin-top: 1rem; }
        .mt-3 { margin-top: 1.5rem; }

        .d-flex { display: flex; }
        .justify-between { justify-content: space-between; }
        .align-center { align-items: center; }
        .gap-1 { gap: 0.5rem; }
        .gap-2 { gap: 1rem; }

        .hidden { display: none; }

        /* Responsive */
        @media (max-width: 768px) {
            .header-content {
                flex-direction: column;
                gap: 1rem;
            }

            .nav {
                flex-wrap: wrap;
                justify-content: center;
            }

            .main {
                padding: 1rem;
            }

            .table {
                font-size: 0.875rem;
            }

            .table th,
            .table td {
                padding: 0.5rem;
            }
        }
    </style>
</head>
<body>
    <header class="header">
        <div class="header-content">
            <a href="<?= BASE_URL ?>/" class="logo">🏛️ CitizenReport Hub</a>
            <nav class="nav">
                <?php if (isset($_SESSION['user'])): ?>
                    <span class="user-info">
                        👤 <?= htmlspecialchars($_SESSION['user']['cin']) ?>
                        (<?= ucfirst($_SESSION['user']['role']) ?>)
                    </span>
                    <?php if ($_SESSION['user']['role'] === 'worker'): ?>
                        <a href="<?= BASE_URL ?>/admin/dashboard">Dashboard</a>
                        <a href="<?= BASE_URL ?>/admin/categories">Categories</a>
                        <a href="<?= BASE_URL ?>/admin/branches">Branches</a>
                        <a href="<?= BASE_URL ?>/admin/broadcasts" style="position:relative;">
                            📢 Broadcasts
                            <?php 
                                $bModel = new Broadcast();
                                $bCount = $bModel->getCount();
                                if ($bCount > 0): 
                            ?>
                                <span style="position:absolute; top:-6px; right:-8px; background:#dc3545; color:white; border-radius:50%; width:18px; height:18px; font-size:0.7rem; display:flex; align-items:center; justify-content:center; font-weight:bold;">
                                    <?= $bCount ?>
                                </span>
                            <?php endif; ?>
                        </a>
                    <?php else: ?>
                        <a href="<?= BASE_URL ?>/dashboard">Dashboard</a>
                        <a href="<?= BASE_URL ?>/report/create">New Report</a>
                        <a href="<?= BASE_URL ?>/broadcasts" style="position:relative;">
                            📢 Broadcasts
                            <?php 
                                $bModel = new Broadcast();
                                $bCount = $bModel->getCount();
                                if ($bCount > 0): 
                            ?>
                                <span style="position:absolute; top:-6px; right:-8px; background:#dc3545; color:white; border-radius:50%; width:18px; height:18px; font-size:0.7rem; display:flex; align-items:center; justify-content:center; font-weight:bold;">
                                    <?= $bCount ?>
                                </span>
                            <?php endif; ?>
                        </a>
                    <?php endif; ?>
                    <a href="<?= BASE_URL ?>/auth/logout">Logout</a>
                <?php else: ?>
                    <a href="<?= BASE_URL ?>/auth/login">Login</a>
                    <a href="<?= BASE_URL ?>/auth/register">Register</a>
                <?php endif; ?>
            </nav>
        </div>
    </header>

    <main class="main">
        <?php if (isset($_SESSION['flash'])): ?>
            <div class="flash-messages">
                <?php if (isset($_SESSION['flash']['success'])): ?>
                    <div class="flash flash-success">
                        <?= $_SESSION['flash']['success'] ?>
                    </div>
                <?php endif; ?>
                <?php if (isset($_SESSION['flash']['error'])): ?>
                    <div class="flash flash-error">
                        <?= $_SESSION['flash']['error'] ?>
                    </div>
                <?php endif; ?>
                <?php if (isset($_SESSION['flash']['warning'])): ?>
                    <div class="flash flash-warning">
                        <?= $_SESSION['flash']['warning'] ?>
                    </div>
                <?php endif; ?>
            </div>
            <?php unset($_SESSION['flash']); ?>
        <?php endif; ?>

        <?= $content ?>
    </main>

    <footer class="footer">
        <p>&copy; <?= date('Y') ?> CitizenReport Hub. All rights reserved.</p>
    </footer>

    <!-- Leaflet JS -->
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" 
            integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" 
            crossorigin=""></script>
</body>
</html>
