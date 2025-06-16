<?php
// Dashboard - Main Page with Authentication
session_start();
require_once '../config/database.php';
require_once '../config/auth.php';

// Require user to be logged in
require_login();

// Get current user data
$current_user = get_logged_in_user();

// Get dashboard statistics (placeholder - will be replaced with real data later)
$total_orders = 15; // This will be replaced with database query
$total_customers = 8; // This will be replaced with database query  
$monthly_revenue = 12500000; // This will be replaced with database query
$pending_orders = 3; // This will be replaced with database query
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Sistem Pencatatan Order</title>

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-icons/1.11.0/font/bootstrap-icons.min.css"
        rel="stylesheet">

    <style>
        body {
            background-color: #f8f9fa;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        .sidebar {
            min-height: 100vh;
            background: white;
            box-shadow: 2px 0 5px rgba(0, 0, 0, 0.1);
            position: fixed;
            width: 250px;
            z-index: 1000;
        }

        .main-content {
            margin-left: 250px;
            padding: 20px;
        }

        .navbar-custom {
            background: white;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
            margin-left: 250px;
            position: fixed;
            top: 0;
            right: 0;
            left: 250px;
            z-index: 999;
            width: calc(100% - 250px);
        }

        .content-wrapper {
            margin-top: 80px;
        }

        .sidebar-brand {
            padding: 20px;
            border-bottom: 1px solid #e9ecef;
        }

        .sidebar-nav {
            padding: 0;
            list-style: none;
        }

        .sidebar-nav li {
            border-bottom: 1px solid #f8f9fa;
        }

        .sidebar-nav a {
            display: block;
            padding: 15px 20px;
            color: #495057;
            text-decoration: none;
            transition: all 0.3s ease;
        }

        .sidebar-nav a:hover {
            background-color: #e9ecef;
            color: #007bff;
        }

        .sidebar-nav a.active {
            background-color: #007bff;
            color: white;
        }

        .sidebar-nav a.text-danger:hover {
            background-color: #dc3545;
            color: white !important;
        }

        .card {
            border: none;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            margin-bottom: 20px;
        }

        .card-stats {
            padding: 20px;
            text-align: center;
        }

        .stats-icon {
            width: 60px;
            height: 60px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 15px;
            font-size: 24px;
            color: white;
        }

        .stats-number {
            font-size: 2rem;
            font-weight: bold;
            margin: 10px 0 5px;
        }

        .stats-label {
            color: #6c757d;
            font-size: 14px;
        }

        .bg-primary-custom {
            background: linear-gradient(45deg, #007bff, #0056b3);
        }

        .bg-success-custom {
            background: linear-gradient(45deg, #28a745, #1e7e34);
        }

        .bg-warning-custom {
            background: linear-gradient(45deg, #ffc107, #e0a800);
        }

        .bg-danger-custom {
            background: linear-gradient(45deg, #dc3545, #c82333);
        }

        .table th {
            border-top: none;
            font-weight: 600;
            color: #495057;
        }

        .badge-status {
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 500;
        }

        .btn-action {
            padding: 5px 10px;
            font-size: 12px;
            border-radius: 5px;
            margin: 0 2px;
        }

        @media (max-width: 768px) {
            .sidebar {
                margin-left: -250px;
                transition: margin 0.3s ease;
            }

            .sidebar.show {
                margin-left: 0;
            }

            .main-content,
            .navbar-custom {
                margin-left: 0;
            }

            .navbar-custom {
                left: 0;
                width: 100%;
            }
        }
    </style>
</head>

<body>
    <!-- Sidebar -->
    <div class="sidebar" id="sidebar">
        <div class="sidebar-brand">
            <h5 class="mb-0">
                <i class="bi bi-clipboard-data me-2"></i>
                Order System
            </h5>
        </div>
        <ul class="sidebar-nav">
            <li>
                <a href="index.php" class="active">
                    <i class="bi bi-house me-2"></i>
                    Dashboard
                </a>
            </li>
            <li>
                <a href="add-order.php">
                    <i class="bi bi-plus-circle me-2"></i>
                    Tambah Order
                </a>
            </li>
            <li>
                <a href="orders.php">
                    <i class="bi bi-list-ul me-2"></i>
                    Daftar Order
                </a>
            </li>
            <li>
                <a href="customers.php">
                    <i class="bi bi-people me-2"></i>
                    Pelanggan
                </a>
            </li>
            <li>
                <a href="reports.php">
                    <i class="bi bi-graph-up me-2"></i>
                    Laporan
                </a>
            </li>
            <li>
                <a href="settings.php">
                    <i class="bi bi-gear me-2"></i>
                    Pengaturan
                </a>
            </li>
            <li>
                <a href="../auth/logout.php" class="text-danger"
                    onclick="return confirm('Apakah Anda yakin ingin logout?')">
                    <i class="bi bi-box-arrow-right me-2"></i>
                    Logout
                </a>
            </li>
        </ul>
    </div>

    <!-- Top Navbar -->
    <nav class="navbar navbar-expand-lg navbar-light navbar-custom">
        <div class="container-fluid">
            <button class="btn btn-outline-secondary d-md-none" type="button" onclick="toggleSidebar()">
                <i class="bi bi-list"></i>
            </button>
            <div class="navbar-nav ms-auto">
                <div class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">
                        <i class="bi bi-person-circle me-1"></i>
                        <?php echo htmlspecialchars($current_user['full_name']); ?>
                    </a>
                    <ul class="dropdown-menu dropdown-menu-end">
                        <li class="dropdown-header">
                            <small class="text-muted"><?php echo htmlspecialchars($current_user['email']); ?></small>
                        </li>
                        <li>
                            <hr class="dropdown-divider">
                        </li>
                        <li><a class="dropdown-item" href="settings.php"><i class="bi bi-person me-2"></i>Profile</a>
                        </li>
                        <li><a class="dropdown-item" href="settings.php"><i class="bi bi-gear me-2"></i>Settings</a>
                        </li>
                        <li>
                            <hr class="dropdown-divider">
                        </li>
                        <li><a class="dropdown-item" href="../auth/logout.php"><i
                                    class="bi bi-box-arrow-right me-2"></i>Logout</a></li>
                    </ul>
                </div>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <div class="main-content">
        <div class="content-wrapper">
            <!-- Welcome Section -->
            <div class="row mb-4">
                <div class="col-12">
                    <div class="card">
                        <div class="card-body">
                            <h4 class="card-title mb-1">Selamat Datang,
                                <?php echo htmlspecialchars($current_user['full_name']); ?>!
                            </h4>
                            <p class="text-muted mb-0">Kelola semua order dan pelanggan Anda dengan mudah</p>
                            <small class="text-muted">
                                <i class="bi bi-person-badge me-1"></i>
                                Login sebagai: <?php echo ucfirst($current_user['role']); ?>
                                <?php if (isset($_SESSION['login_time'])): ?>
                                    | Login: <?php echo date('d/m/Y H:i', $_SESSION['login_time']); ?>
                                <?php endif; ?>
                            </small>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Statistics Cards -->
            <div class="row mb-4">
                <div class="col-xl-3 col-md-6">
                    <div class="card">
                        <div class="card-stats">
                            <div class="stats-icon bg-primary-custom">
                                <i class="bi bi-cart-plus"></i>
                            </div>
                            <div class="stats-number"><?php echo number_format($total_orders); ?></div>
                            <div class="stats-label">Total Order</div>
                        </div>
                    </div>
                </div>

                <div class="col-xl-3 col-md-6">
                    <div class="card">
                        <div class="card-stats">
                            <div class="stats-icon bg-success-custom">
                                <i class="bi bi-check-circle"></i>
                            </div>
                            <div class="stats-number"><?php echo number_format($total_orders - $pending_orders); ?>
                            </div>
                            <div class="stats-label">Order Selesai</div>
                        </div>
                    </div>
                </div>

                <div class="col-xl-3 col-md-6">
                    <div class="card">
                        <div class="card-stats">
                            <div class="stats-icon bg-warning-custom">
                                <i class="bi bi-clock"></i>
                            </div>
                            <div class="stats-number"><?php echo number_format($pending_orders); ?></div>
                            <div class="stats-label">Order Pending</div>
                        </div>
                    </div>
                </div>

                <div class="col-xl-3 col-md-6">
                    <div class="card">
                        <div class="card-stats">
                            <div class="stats-icon bg-danger-custom">
                                <i class="bi bi-people"></i>
                            </div>
                            <div class="stats-number"><?php echo number_format($total_customers); ?></div>
                            <div class="stats-label">Total Pelanggan</div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Recent Orders -->
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <h5 class="card-title mb-0">Order Terbaru</h5> <a href="add-order.php"
                                class="btn btn-primary btn-sm">
                                <i class="bi bi-plus me-1"></i>
                                Tambah Order
                            </a>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-hover">
                                    <thead>
                                        <tr>
                                            <th>ID Order</th>
                                            <th>Pelanggan</th>
                                            <th>Tanggal</th>
                                            <th>Total</th>
                                            <th>Status</th>
                                            <th>Aksi</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <td>#ORD-001</td>
                                            <td>John Doe</td>
                                            <td>13 Jun 2025</td>
                                            <td>Rp 150,000</td>
                                            <td><span class="badge bg-success badge-status">Selesai</span></td>
                                            <td>
                                                <button class="btn btn-info btn-action" title="Lihat">
                                                    <i class="bi bi-eye"></i>
                                                </button>
                                                <button class="btn btn-warning btn-action" title="Edit">
                                                    <i class="bi bi-pencil"></i>
                                                </button>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td>#ORD-002</td>
                                            <td>Jane Smith</td>
                                            <td>12 Jun 2025</td>
                                            <td>Rp 200,000</td>
                                            <td><span class="badge bg-warning badge-status">Pending</span></td>
                                            <td>
                                                <button class="btn btn-info btn-action" title="Lihat">
                                                    <i class="bi bi-eye"></i>
                                                </button>
                                                <button class="btn btn-warning btn-action" title="Edit">
                                                    <i class="bi bi-pencil"></i>
                                                </button>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td>#ORD-003</td>
                                            <td>Michael Johnson</td>
                                            <td>12 Jun 2025</td>
                                            <td>Rp 75,000</td>
                                            <td><span class="badge bg-primary badge-status">Proses</span></td>
                                            <td>
                                                <button class="btn btn-info btn-action" title="Lihat">
                                                    <i class="bi bi-eye"></i>
                                                </button>
                                                <button class="btn btn-warning btn-action" title="Edit">
                                                    <i class="bi bi-pencil"></i>
                                                </button>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td>#ORD-004</td>
                                            <td>Sarah Wilson</td>
                                            <td>11 Jun 2025</td>
                                            <td>Rp 120,000</td>
                                            <td><span class="badge bg-success badge-status">Selesai</span></td>
                                            <td>
                                                <button class="btn btn-info btn-action" title="Lihat">
                                                    <i class="bi bi-eye"></i>
                                                </button>
                                                <button class="btn btn-warning btn-action" title="Edit">
                                                    <i class="bi bi-pencil"></i>
                                                </button>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td>#ORD-005</td>
                                            <td>David Brown</td>
                                            <td>11 Jun 2025</td>
                                            <td>Rp 300,000</td>
                                            <td><span class="badge bg-warning badge-status">Pending</span></td>
                                            <td>
                                                <button class="btn btn-info btn-action" title="Lihat">
                                                    <i class="bi bi-eye"></i>
                                                </button>
                                                <button class="btn btn-warning btn-action" title="Edit">
                                                    <i class="bi bi-pencil"></i>
                                                </button>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Quick Actions -->
            <div class="row mt-4">
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="card-title mb-0">Aksi Cepat</h5>
                        </div>
                        <div class="card-body">
                            <div class="d-grid gap-2">
                                <a href="add-order.php" class="btn btn-primary">
                                    <i class="bi bi-plus-circle me-2"></i>
                                    Tambah Order Baru
                                </a>
                                <a href="customers.php" class="btn btn-success">
                                    <i class="bi bi-person-plus me-2"></i>
                                    Tambah Pelanggan Baru
                                </a>
                                <a href="reports.php" class="btn btn-info">
                                    <i class="bi bi-file-earmark-text me-2"></i>
                                    Buat Laporan
                                </a>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="card-title mb-0">Aktivitas Terbaru</h5>
                        </div>
                        <div class="card-body">
                            <div class="activity-item mb-3">
                                <div class="d-flex">
                                    <div class="flex-shrink-0">
                                        <i class="bi bi-check-circle text-success"></i>
                                    </div>
                                    <div class="flex-grow-1 ms-3">
                                        <p class="mb-1"><strong>Order #ORD-001</strong> telah diselesaikan</p>
                                        <small class="text-muted">2 jam yang lalu</small>
                                    </div>
                                </div>
                            </div>

                            <div class="activity-item mb-3">
                                <div class="d-flex">
                                    <div class="flex-shrink-0">
                                        <i class="bi bi-person-plus text-primary"></i>
                                    </div>
                                    <div class="flex-grow-1 ms-3">
                                        <p class="mb-1">Pelanggan baru <strong>John Doe</strong> terdaftar</p>
                                        <small class="text-muted">4 jam yang lalu</small>
                                    </div>
                                </div>
                            </div>

                            <div class="activity-item">
                                <div class="d-flex">
                                    <div class="flex-shrink-0">
                                        <i class="bi bi-cart-plus text-warning"></i>
                                    </div>
                                    <div class="flex-grow-1 ms-3">
                                        <p class="mb-1">Order baru <strong>#ORD-002</strong> dibuat</p>
                                        <small class="text-muted">6 jam yang lalu</small>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

    <script>
        // Toggle sidebar for mobile
        function toggleSidebar() {
            const sidebar = document.getElementById('sidebar');
            sidebar.classList.toggle('show');
        }

        // Close sidebar when clicking outside on mobile
        document.addEventListener('click', function (event) {
            const sidebar = document.getElementById('sidebar');
            const toggleBtn = event.target.closest('.btn');

            if (window.innerWidth <= 768 && !sidebar.contains(event.target) && !toggleBtn) {
                sidebar.classList.remove('show');
            }
        });

        // Auto-refresh stats (simulation)
        function updateStats() {
            // This would normally fetch data from server
            console.log('Stats updated');
        }

        // Update stats every 30 seconds
        setInterval(updateStats, 30000);

        // Initialize tooltips
        var tooltipTriggerList = [].slice.call(document.querySelectorAll('[title]'));
        var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl);
        });
    </script>
</body>

</html>