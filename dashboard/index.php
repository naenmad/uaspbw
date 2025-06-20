<?php
// Dashboard - Main Page with Authentication
session_start();
require_once '../config/database.php';
require_once '../config/auth.php';

// Require user to be logged in
require_login();

// Get current user data
$current_user = get_logged_in_user();

// Helper function for status badge
function getStatusBadgeClass($status)
{
    switch (strtolower($status)) {
        case 'pending':
            return 'bg-warning';
        case 'confirmed':
            return 'bg-info';
        case 'processing':
            return 'bg-primary';
        case 'shipped':
            return 'bg-info';
        case 'delivered':
            return 'bg-success';
        case 'completed':
            return 'bg-success';
        case 'cancelled':
            return 'bg-danger';
        default:
            return 'bg-secondary';
    }
}

// Get dashboard statistics from database
try {
    // Total orders
    $stmt = $pdo->query("SELECT COUNT(*) FROM orders");
    $total_orders = $stmt->fetchColumn() ?: 0;

    // Total customers
    $stmt = $pdo->query("SELECT COUNT(*) FROM customers");
    $total_customers = $stmt->fetchColumn() ?: 0;

    // Monthly revenue (current month)
    $stmt = $pdo->query("SELECT COALESCE(SUM(total_amount), 0) FROM orders 
                         WHERE MONTH(order_date) = MONTH(CURRENT_DATE()) 
                         AND YEAR(order_date) = YEAR(CURRENT_DATE())
                         AND status != 'cancelled'");
    $monthly_revenue = $stmt->fetchColumn() ?: 0;
    // Pending orders
    $stmt = $pdo->query("SELECT COUNT(*) FROM orders WHERE status = 'pending'");
    $pending_orders = $stmt->fetchColumn() ?: 0;
    // Recent orders (last 5 orders)
    $stmt = $pdo->query("SELECT o.id, o.order_number, o.order_date, o.total_amount, o.status, c.name as customer_name
                         FROM orders o 
                         LEFT JOIN customers c ON o.customer_id = c.id 
                         ORDER BY o.created_at DESC 
                         LIMIT 5");
    $recent_orders = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Recent activities (activity logs)
    $stmt = $pdo->query("SELECT al.*, u.full_name as user_name 
                         FROM activity_logs al 
                         LEFT JOIN users u ON al.user_id = u.id 
                         ORDER BY al.created_at DESC 
                         LIMIT 5");
    $recent_activities = $stmt->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    // Fallback to placeholder data if database query fails
    $total_orders = 0;
    $total_customers = 0;
    $monthly_revenue = 0;
    $pending_orders = 0;
    $recent_orders = [];
    $recent_activities = [];
}
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
            margin: 0;
            padding: 0;
            overflow-x: hidden;
        }

        html,
        body {
            width: 100%;
            max-width: 100vw;
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

        .navbar-custom:not(.mobile-only-navbar) {
            background: white;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
            margin-left: 250px;
            position: fixed;
            top: 0;
            left: 250px;
            z-index: 999;
            width: calc(100% - 250px);
            min-height: 60px;
            max-height: 60px;
            border: none;
            outline: none;
        }

        .navbar-custom::before,
        .navbar-custom::after {
            display: none;
        }

        /* Remove any phantom elements */
        .navbar-custom>*:empty {
            display: none !important;
        }

        .navbar-custom .container-fluid {
            padding: 0.5rem 1rem;
            height: 60px;
            display: flex;
            align-items: center;
            justify-content: flex-end;
            width: 100%;
            max-width: 100%;
            overflow: hidden;
        }

        /* On desktop, container should only show right content */
        @media (min-width: 769px) {
            .navbar-custom .container-fluid {
                justify-content: flex-end !important;
                padding-left: 1rem;
                display: flex !important;
                align-items: center !important;
            }

            /* Hide any children that are buttons */
            .navbar-custom .container-fluid>.btn,
            .navbar-custom .container-fluid>button {
                display: none !important;
                width: 0 !important;
                height: 0 !important;
                visibility: hidden !important;
            }
        }

        @media (max-width: 768px) {
            .navbar-custom .container-fluid {
                justify-content: space-between;
                padding: 0.5rem 1rem;
            }
        }

        .navbar-custom .dropdown-menu {
            border: 1px solid #dee2e6;
            box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15);
        }

        .navbar-custom .nav-link {
            color: #495057;
            padding: 0.5rem 1rem;
            white-space: nowrap;
        }

        .navbar-custom .nav-link:hover {
            color: #007bff;
        }

        .navbar-custom .navbar-nav {
            margin-left: auto;
            margin-right: 0;
            padding-right: 0;
        }

        /* Ensure no elements extend beyond navbar */
        .navbar-custom * {
            box-sizing: border-box;
        }

        .navbar-custom .dropdown-menu {
            position: absolute;
            top: 100%;
            right: 0;
            left: auto;
            transform: none;
        }

        /* Hide mobile toggle button on desktop - FORCE HIDE */
        @media (min-width: 769px) {

            .navbar-custom .mobile-toggle,
            .navbar-custom .btn,
            .navbar-custom button,
            .navbar-custom .d-md-none {
                display: none !important;
                visibility: hidden !important;
                opacity: 0 !important;
                width: 0 !important;
                height: 0 !important;
                margin: 0 !important;
                padding: 0 !important;
                border: 0 !important;
                position: absolute !important;
                left: -9999px !important;
            }
        }

        /* Force hide ANY button in navbar on desktop */
        .navbar-custom .btn {
            display: none !important;
        }

        .navbar-custom button {
            display: none !important;
        }

        @media (max-width: 768px) {
            .navbar-custom .mobile-toggle {
                display: inline-block !important;
            }

            .navbar-custom .btn {
                display: inline-block !important;
            }

            .navbar-custom button {
                display: inline-block !important;
            }
        }

        /* Fix dropdown positioning */
        .navbar-custom .dropdown-menu {
            right: 0;
            left: auto;
            min-width: 200px;
        }

        /* Mobile sidebar overlay */
        .sidebar-overlay {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.5);
            z-index: 999;
        }

        .sidebar-overlay.show {
            display: block;
        }

        .content-wrapper {
            margin-top: 80px;
            padding: 0;
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
            transition: all 0.2s ease;
            cursor: pointer;
        }

        .btn-action:hover {
            transform: translateY(-1px);
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.2);
        }

        .btn-action:active {
            transform: translateY(0);
        }

        /* Remove any gaps or spaces in navbar */
        .navbar-custom .container-fluid>* {
            margin: 0;
        }

        .navbar-custom .container-fluid>.btn:not(:last-child) {
            margin-right: auto;
        }

        /* Ensure navbar-nav takes up remaining space correctly */
        .navbar-custom .navbar-nav.ms-auto {
            margin-left: auto !important;
            flex-shrink: 0;
        }

        @media (max-width: 768px) {
            .sidebar {
                margin-left: -250px;
                transition: margin 0.3s ease;
                z-index: 1001;
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
                max-width: 100vw;
            }

            .stats-number {
                font-size: 1.5rem;
            }

            .table-responsive {
                font-size: 0.875rem;
            }
        }

        /* ULTIMATE FIX: Remove button completely from DOM rendering on desktop */
        @media (min-width: 769px) {
            .navbar-custom .btn.d-md-none {
                display: none !important;
                visibility: hidden !important;
                opacity: 0 !important;
                position: absolute !important;
                left: -9999px !important;
                top: -9999px !important;
                width: 0 !important;
                height: 0 !important;
                margin: 0 !important;
                padding: 0 !important;
                border: none !important;
                background: none !important;
                z-index: -1 !important;
            }

            .navbar-custom .container-fluid {
                grid-template-columns: 1fr !important;
            }
        }

        /* Desktop hidden class - absolute hide */
        .desktop-hidden {
            display: none !important;
            visibility: hidden !important;
            opacity: 0 !important;
            position: absolute !important;
            left: -9999px !important;
            top: -9999px !important;
            width: 0 !important;
            height: 0 !important;
            margin: 0 !important;
            padding: 0 !important;
            border: none !important;
            background: none !important;
            z-index: -9999 !important;
        }

        @media (max-width: 768px) {
            .desktop-hidden {
                display: inline-block !important;
                visibility: visible !important;
                opacity: 1 !important;
                position: relative !important;
                left: auto !important;
                top: auto !important;
                width: auto !important;
                height: auto !important;
                z-index: auto !important;
            }
        }

        /* Mobile-only navbar */
        .mobile-only-navbar {
            display: none !important;
        }

        @media (max-width: 768px) {
            .mobile-only-navbar {
                display: block !important;
            }
        }

        /* Mobile-only navbar styling */
        .mobile-only-navbar {
            background: white;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            z-index: 999;
            width: 100%;
            min-height: 60px;
        }

        /* Hide mobile navbar on desktop */
        @media (min-width: 769px) {
            .mobile-only-navbar {
                display: none !important;
            }
        }

        /* Desktop navbar - absolutely clean */
        @media (min-width: 769px) {
            .navbar-custom:not(.mobile-only-navbar) {
                display: block !important;
            }
        }

        /* Mobile - hide desktop navbar and show mobile navbar */
        @media (max-width: 768px) {
            .navbar-custom:not(.mobile-only-navbar) {
                display: none !important;
            }

            .mobile-only-navbar {
                display: block !important;
            }
        }
    </style>
</head>

<body>
    <!-- Mobile Sidebar Overlay -->
    <div class="sidebar-overlay" id="sidebarOverlay" onclick="closeSidebar()"></div>

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
    </div> <!-- Top Navbar -->
    <nav class="navbar navbar-expand-lg navbar-light navbar-custom">
        <div class="container-fluid">
            <!-- User dropdown - ONLY content in desktop -->
            <div class="navbar-nav ms-auto">
                <div class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown"
                        aria-expanded="false">
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

    <!-- Mobile-only Navbar - Hidden on desktop -->
    <nav class="navbar navbar-expand-lg navbar-light navbar-custom d-md-none mobile-only-navbar">
        <div class="container-fluid">
            <button class="btn btn-outline-secondary" type="button" onclick="toggleSidebar()">
                <i class="bi bi-list"></i>
            </button>
            <div class="navbar-nav ms-auto">
                <div class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown"
                        aria-expanded="false">
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
                                        <?php if (empty($recent_orders)): ?>
                                            <tr>
                                                <td colspan="6" class="text-center">Belum ada order</td>
                                            </tr>
                                        <?php else: ?>
                                            <?php foreach ($recent_orders as $order): ?>
                                                <tr>
                                                    <td>#<?= htmlspecialchars($order['order_number'] ?? 'ORD' . str_pad($order['id'], 3, '0', STR_PAD_LEFT)) ?>
                                                    </td>
                                                    <td><?= htmlspecialchars($order['customer_name'] ?? 'Unknown Customer') ?>
                                                    </td>
                                                    <td><?= date('d M Y', strtotime($order['order_date'])) ?></td>
                                                    <td>Rp <?= number_format($order['total_amount'], 0, ',', '.') ?></td>
                                                    <td>
                                                        <span
                                                            class="badge <?= getStatusBadgeClass($order['status']) ?> badge-status">
                                                            <?= ucfirst($order['status']) ?>
                                                        </span>
                                                    </td>
                                                    <td>
                                                        <button class="btn btn-info btn-action" title="Lihat"
                                                            onclick="viewOrder('<?= htmlspecialchars($order['order_number'] ?? $order['id']) ?>')">
                                                            <i class="bi bi-eye"></i>
                                                        </button>
                                                        <button class="btn btn-warning btn-action" title="Edit"
                                                            onclick="editOrder('<?= htmlspecialchars($order['order_number'] ?? $order['id']) ?>')">
                                                            <i class="bi bi-pencil"></i>
                                                        </button>
                                                    </td>
                                                </tr>
                                            <?php endforeach; ?>
                                        <?php endif; ?>
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
                            <?php if (empty($recent_activities)): ?>
                                <div class="text-center text-muted">
                                    <i class="bi bi-clock-history mb-2" style="font-size: 2rem;"></i>
                                    <p>Belum ada aktivitas</p>
                                </div>
                            <?php else: ?>
                                <?php foreach ($recent_activities as $activity): ?>
                                    <div class="activity-item mb-3">
                                        <div class="d-flex">
                                            <div class="flex-shrink-0">
                                                <?php
                                                $icon = 'bi-info-circle';
                                                $color = 'text-info';
                                                switch (strtolower($activity['action'])) {
                                                    case 'create':
                                                        $icon = 'bi-plus-circle';
                                                        $color = 'text-success';
                                                        break;
                                                    case 'update':
                                                        $icon = 'bi-pencil-circle';
                                                        $color = 'text-warning';
                                                        break;
                                                    case 'delete':
                                                        $icon = 'bi-trash-circle';
                                                        $color = 'text-danger';
                                                        break;
                                                }
                                                ?>
                                                <i class="bi <?= $icon ?> <?= $color ?>"></i>
                                            </div>
                                            <div class="flex-grow-1 ms-3">
                                                <p class="mb-1">
                                                    <?= htmlspecialchars($activity['description'] ?? $activity['action'] . ' ' . $activity['model']) ?>
                                                </p>
                                                <small class="text-muted">
                                                    <?= htmlspecialchars($activity['user_name'] ?? 'System') ?> •
                                                    <?= date('d M Y H:i', strtotime($activity['created_at'])) ?>
                                                </small>
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

    <script>        // Toggle sidebar for mobile
        function toggleSidebar() {
            const sidebar = document.getElementById('sidebar');
            const overlay = document.getElementById('sidebarOverlay');

            if (window.innerWidth <= 768) {
                sidebar.classList.toggle('show');
                overlay.classList.toggle('show');
            }
        }

        // Close sidebar
        function closeSidebar() {
            const sidebar = document.getElementById('sidebar');
            const overlay = document.getElementById('sidebarOverlay');

            sidebar.classList.remove('show');
            overlay.classList.remove('show');
        }

        // Close sidebar when clicking outside on mobile
        document.addEventListener('click', function (event) {
            const sidebar = document.getElementById('sidebar');
            const toggleBtn = event.target.closest('.btn');

            if (window.innerWidth <= 768 && !sidebar.contains(event.target) && !toggleBtn) {
                closeSidebar();
            }
        });

        // Auto-refresh stats (simulation)
        function updateStats() {
            // This would normally fetch data from server
            console.log('Stats updated');
        }        // Update stats every 30 seconds
        setInterval(updateStats, 30000);        // Initialize dropdowns and tooltips
        document.addEventListener('DOMContentLoaded', function () {
            // Initialize all dropdowns
            var dropdowns = document.querySelectorAll('.dropdown-toggle');
            dropdowns.forEach(function (dropdown) {
                new bootstrap.Dropdown(dropdown);
            });

            // Initialize tooltips
            var tooltipTriggerList = [].slice.call(document.querySelectorAll('[title]'));
            var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
                return new bootstrap.Tooltip(tooltipTriggerEl);
            });
        });

        // Handle window resize
        window.addEventListener('resize', function () {
            if (window.innerWidth > 768) {
                closeSidebar();
            }
        });// Order action functions
        function viewOrder(orderId) {
            // Redirect to order detail page
            window.location.href = `order-detail.php?id=${orderId}`;
        }

        function editOrder(orderId) {
            // Show confirmation and redirect to edit page
            if (confirm(`Edit order ${orderId}?\n\nNote: Ini akan membuka halaman edit order.`)) {
                window.location.href = `edit-order.php?id=${orderId}`;
            }
        }
    </script>
</body>

</html>