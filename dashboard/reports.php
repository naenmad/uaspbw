<?php
// Reports Page with Authentication
session_start();
require_once '../config/database.php';
require_once '../config/auth.php';

// Require user to be logged in
require_login();

// Get current user data
$current_user = get_logged_in_user();

// Get total revenue
$total_revenue_sql = "SELECT SUM(total_amount) as total_revenue FROM orders WHERE status != 'cancelled'";
$total_revenue_result = $pdo->query($total_revenue_sql);
$total_revenue = $total_revenue_result->fetchColumn() ?: 0;

// Get total orders
$total_orders_sql = "SELECT COUNT(*) as total_orders FROM orders WHERE status != 'cancelled'";
$total_orders_result = $pdo->query($total_orders_sql);
$total_orders = $total_orders_result->fetchColumn() ?: 0;

// Get order status distribution
$order_status_sql = "SELECT status, COUNT(*) as count FROM orders GROUP BY status";
$order_status_result = $pdo->query($order_status_sql);
$order_status_data = $order_status_result->fetchAll();

// Get monthly sales data
$monthly_sales_sql = "SELECT 
                            YEAR(order_date) as year,
                            MONTH(order_date) as month,
                            SUM(total_amount) as total_revenue
                        FROM orders
                        WHERE status != 'cancelled'
                        GROUP BY YEAR(order_date), MONTH(order_date)
                        ORDER BY year DESC, month DESC";
$monthly_sales_result = $pdo->query($monthly_sales_sql);
$monthly_sales = $monthly_sales_result->fetchAll();
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan - Sistem Pencatatan Order</title>

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-icons/1.11.0/font/bootstrap-icons.min.css"
        rel="stylesheet">
    <!-- Chart.js -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/3.9.1/chart.min.js"></script>

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

        .stats-card {
            text-align: center;
            padding: 20px;
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

        .chart-container {
            position: relative;
            height: 300px;
        }

        .report-filter {
            background: #f8f9fa;
            border-radius: 8px;
            padding: 15px;
            margin-bottom: 20px;
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
                <a href="index.php">
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
                <a href="reports.php" class="active">
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
            <!-- Page Header -->
            <div class="row mb-4">
                <div class="col-12">
                    <div class="card">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h4 class="card-title mb-1">Laporan & Analisis</h4>
                                    <p class="text-muted mb-0">Monitor performa bisnis dan statistik order</p>
                                </div>
                                <div class="btn-group">
                                    <button class="btn btn-success" onclick="exportReport('excel')">
                                        <i class="bi bi-file-earmark-excel me-1"></i>
                                        Export Excel
                                    </button>
                                    <button class="btn btn-danger" onclick="exportReport('pdf')">
                                        <i class="bi bi-file-earmark-pdf me-1"></i>
                                        Export PDF
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Report Filters -->
            <div class="row mb-4">
                <div class="col-12">
                    <div class="report-filter">
                        <div class="row g-3">
                            <div class="col-md-3">
                                <label class="form-label">Periode</label>
                                <select class="form-select" id="periodFilter" onchange="updateReports()">
                                    <option value="today">Hari Ini</option>
                                    <option value="week" selected>Minggu Ini</option>
                                    <option value="month">Bulan Ini</option>
                                    <option value="year">Tahun Ini</option>
                                    <option value="custom">Custom</option>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Dari Tanggal</label>
                                <input type="date" class="form-control" id="dateFrom" value="2025-06-08">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Sampai Tanggal</label>
                                <input type="date" class="form-control" id="dateTo" value="2025-06-14">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">&nbsp;</label>
                                <button class="btn btn-primary w-100" onclick="generateReport()">
                                    <i class="bi bi-bar-chart me-1"></i>
                                    Generate Report
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Summary Statistics -->
            <div class="row mb-4">
                <div class="col-xl-3 col-md-6">
                    <div class="card">
                        <div class="stats-card">
                            <div class="stats-icon" style="background: linear-gradient(45deg, #007bff, #0056b3);">
                                <i class="bi bi-currency-dollar"></i>
                            </div>
                            <div class="stats-number">Rp <?php echo number_format($total_revenue, 2, ',', '.'); ?></div>
                            <div class="stats-label">Total Pendapatan</div>
                            <small class="text-success">
                                <i class="bi bi-arrow-up"></i> +12% dari periode sebelumnya
                            </small>
                        </div>
                    </div>
                </div>

                <div class="col-xl-3 col-md-6">
                    <div class="card">
                        <div class="stats-card">
                            <div class="stats-icon" style="background: linear-gradient(45deg, #28a745, #1e7e34);">
                                <i class="bi bi-cart-check"></i>
                            </div>
                            <div class="stats-number"><?php echo $total_orders; ?></div>
                            <div class="stats-label">Total Order</div>
                            <small class="text-success">
                                <i class="bi bi-arrow-up"></i> +8% dari periode sebelumnya
                            </small>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Charts Row -->
            <div class="row mb-4">
                <!-- Revenue Chart -->
                <div class="col-xl-8">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="card-title mb-0">
                                <i class="bi bi-bar-chart me-2"></i>
                                Grafik Pendapatan
                            </h5>
                        </div>
                        <div class="card-body">
                            <div class="chart-container">
                                <canvas id="revenueChart"></canvas>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Order Status Chart -->
                <div class="col-xl-4">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="card-title mb-0">
                                <i class="bi bi-pie-chart me-2"></i>
                                Status Order
                            </h5>
                        </div>
                        <div class="card-body">
                            <div class="chart-container">
                                <canvas id="statusChart"></canvas>
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
        // Initialize charts
        let revenueChart;
        let statusChart;

        document.addEventListener('DOMContentLoaded', function () {
            initializeCharts();
        });

        function initializeCharts() {
            // Revenue Chart
            const revenueCtx = document.getElementById('revenueChart').getContext('2d');
            revenueChart = new Chart(revenueCtx, {
                type: 'line',
                data: {
                    labels: <?php echo json_encode(array_map(function($sales) { return $sales['month'] . '/' . $sales['year']; }, $monthly_sales)); ?>,
                    datasets: [{
                        label: 'Pendapatan',
                        data: <?php echo json_encode(array_column($monthly_sales, 'total_revenue')); ?>,
                        borderColor: '#007bff',
                        backgroundColor: 'rgba(0, 123, 255, 0.1)',
                        tension: 0.4,
                        fill: true
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            display: false
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: {
                                callback: function(value) {
                                    return 'Rp ' + value.toLocaleString();
                                }
                            }
                        }
                    }
                }
            });

            // Status Chart
            const statusCtx = document.getElementById('statusChart').getContext('2d');
            statusChart = new Chart(statusCtx, {
                type: 'doughnut',
                data: {
                    labels: <?php echo json_encode(array_map(function($status) { return ucfirst($status['status']); }, $order_status_data)); ?>,
                    datasets: [{
                        data: <?php echo json_encode(array_column($order_status_data, 'count')); ?>,
                        backgroundColor: ['#28a745', '#007bff', '#ffc107', '#dc3545']
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            position: 'bottom'
                        }
                    }
                }
            });
        }
    </script>
</body>

</html>
 