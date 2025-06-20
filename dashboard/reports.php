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
$monthly_sales_sql = "SELECT                            YEAR(order_date) as year,
                            MONTH(order_date) as month,
                            SUM(total_amount) as total_revenue
                        FROM orders
                        WHERE status != 'cancelled'
                        GROUP BY YEAR(order_date), MONTH(order_date)
                        ORDER BY year DESC, month DESC";
$monthly_sales_result = $pdo->query($monthly_sales_sql);
$monthly_sales = $monthly_sales_result->fetchAll();

// Handle AJAX requests
if (isset($_GET['ajax']) && $_GET['ajax'] === '1') {
    header('Content-Type: application/json');
    
    try {
        $date_from = $_GET['date_from'] ?? date('Y-m-01');
        $date_to = $_GET['date_to'] ?? date('Y-m-t');
        $report_type = $_GET['report_type'] ?? 'summary';
        
        // Get updated statistics
        $stats_query = "SELECT 
                            COUNT(*) as total_orders,
                            SUM(CASE WHEN status != 'cancelled' THEN total_amount ELSE 0 END) as total_revenue,
                            AVG(CASE WHEN status != 'cancelled' THEN total_amount ELSE NULL END) as avg_order_value,
                            COUNT(CASE WHEN status = 'completed' THEN 1 END) as completed_orders,
                            COUNT(CASE WHEN status = 'pending' THEN 1 END) as pending_orders,
                            COUNT(CASE WHEN status = 'cancelled' THEN 1 END) as cancelled_orders
                        FROM orders 
                        WHERE DATE(order_date) >= ? AND DATE(order_date) <= ?";
        
        $stmt = $pdo->prepare($stats_query);
        $stmt->execute([$date_from, $date_to]);
        $stats = $stmt->fetch(PDO::FETCH_ASSOC);
        
        // Get chart data
        $chart_data = [
            'revenue' => [
                'labels' => [],
                'data' => []
            ],
            'status' => [
                'labels' => [],
                'data' => []
            ]
        ];
        
        // Daily revenue data
        $daily_query = "SELECT 
                            DATE(order_date) as date,
                            SUM(CASE WHEN status != 'cancelled' THEN total_amount ELSE 0 END) as revenue
                        FROM orders 
                        WHERE DATE(order_date) >= ? AND DATE(order_date) <= ?
                        GROUP BY DATE(order_date)
                        ORDER BY date";
        
        $stmt = $pdo->prepare($daily_query);
        $stmt->execute([$date_from, $date_to]);
        $daily_data = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        foreach ($daily_data as $day) {
            $chart_data['revenue']['labels'][] = date('d/m', strtotime($day['date']));
            $chart_data['revenue']['data'][] = (float)$day['revenue'];
        }
        
        // Status distribution data
        $status_query = "SELECT 
                            status,
                            COUNT(*) as count
                        FROM orders 
                        WHERE DATE(order_date) >= ? AND DATE(order_date) <= ?
                        GROUP BY status";
        
        $stmt = $pdo->prepare($status_query);
        $stmt->execute([$date_from, $date_to]);
        $status_data = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        foreach ($status_data as $status) {
            $chart_data['status']['labels'][] = ucfirst($status['status']);
            $chart_data['status']['data'][] = (int)$status['count'];
        }
        
        echo json_encode([
            'success' => true,
            'stats' => $stats,
            'chartData' => $chart_data
        ]);
        exit;
        
    } catch (Exception $e) {
        echo json_encode([
            'success' => false,
            'error' => $e->getMessage()
        ]);
        exit;
    }
}
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
                            <div class="d-flex justify-content-between align-items-center">                                <div>
                                    <h4 class="card-title mb-1">Laporan & Analisis</h4>
                                    <p class="text-muted mb-0">Monitor performa bisnis dan statistik order</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>            <!-- Report Filters -->
            <div class="row mb-4">
                <div class="col-12">
                    <div class="card">
                        <div class="card-body">
                            <h5 class="card-title mb-3">Filter Laporan</h5>
                            <div class="row g-3">
                                <!-- Quick Date Range -->
                                <div class="col-md-3">
                                    <label class="form-label">Quick Select</label>
                                    <select class="form-select" onchange="setQuickDateRange(this.value)">
                                        <option value="">Pilih Range</option>
                                        <option value="today">Hari Ini</option>
                                        <option value="yesterday">Kemarin</option>
                                        <option value="this_week">Minggu Ini</option>
                                        <option value="last_week">Minggu Lalu</option>
                                        <option value="this_month">Bulan Ini</option>
                                        <option value="last_month">Bulan Lalu</option>
                                        <option value="this_year">Tahun Ini</option>
                                    </select>
                                </div>
                                
                                <!-- Date From -->
                                <div class="col-md-2">
                                    <label class="form-label">Dari Tanggal</label>
                                    <input type="date" class="form-control" id="date_from" 
                                           value="<?= $_GET['date_from'] ?? date('Y-m-01') ?>">
                                </div>
                                
                                <!-- Date To -->
                                <div class="col-md-2">
                                    <label class="form-label">Sampai Tanggal</label>
                                    <input type="date" class="form-control" id="date_to" 
                                           value="<?= $_GET['date_to'] ?? date('Y-m-t') ?>">
                                </div>
                                
                                <!-- Report Type -->
                                <div class="col-md-3">
                                    <label class="form-label">Jenis Laporan</label>
                                    <div class="mt-2">
                                        <div class="form-check form-check-inline">
                                            <input class="form-check-input" type="radio" name="report_type" 
                                                   id="summary" value="summary" 
                                                   <?= ($_GET['report_type'] ?? 'summary') === 'summary' ? 'checked' : '' ?>>
                                            <label class="form-check-label" for="summary">Summary</label>
                                        </div>
                                        <div class="form-check form-check-inline">
                                            <input class="form-check-input" type="radio" name="report_type" 
                                                   id="detailed" value="detailed"
                                                   <?= ($_GET['report_type'] ?? 'summary') === 'detailed' ? 'checked' : '' ?>>
                                            <label class="form-check-label" for="detailed">Detail</label>
                                        </div>
                                    </div>
                                </div>
                                
                                <!-- Action Buttons -->
                                <div class="col-md-2">
                                    <label class="form-label">&nbsp;</label>
                                    <div class="d-grid gap-2">
                                        <button class="btn btn-primary" onclick="generateReport()">
                                            <i class="bi bi-bar-chart me-1"></i>Generate
                                        </button>
                                        <button class="btn btn-outline-secondary btn-sm" onclick="resetFilters()">
                                            <i class="bi bi-arrow-clockwise me-1"></i>Reset
                                        </button>
                                    </div>
                                </div>
                            </div>
                              <!-- Additional Action Buttons -->
                            <div class="row mt-3">
                                <div class="col-12">
                                    <div class="btn-group me-2">
                                        <button class="btn btn-success" onclick="exportReportWithFilters('excel')">
                                            <i class="bi bi-file-earmark-excel me-1"></i>Export Excel
                                        </button>
                                        <button class="btn btn-danger" onclick="exportReportWithFilters('pdf')">
                                            <i class="bi bi-file-earmark-pdf me-1"></i>Export PDF
                                        </button>
                                    </div>
                                    <div class="btn-group me-2">
                                        <button class="btn btn-info" onclick="printReport()">
                                            <i class="bi bi-printer me-1"></i>Print
                                        </button>
                                        <button class="btn btn-outline-primary" onclick="refreshData()">
                                            <i class="bi bi-arrow-clockwise me-1"></i>Refresh
                                        </button>
                                    </div>
                                    <div class="btn-group">
                                        <button class="btn btn-outline-success" onclick="toggleAutoRefresh()">
                                            <i class="bi bi-clock me-1"></i>Start Auto-Refresh
                                        </button>
                                    </div>
                                </div>
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
            </div>            <!-- Charts Row -->
            <div class="row mb-4" id="reportContent">
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
        });        function initializeCharts() {
            // Revenue Chart
            const revenueCtx = document.getElementById('revenueChart').getContext('2d');
            window.revenueChart = new Chart(revenueCtx, {
                type: 'line',
                data: {
                    labels: <?php echo json_encode(array_map(function ($sales) {
                        return $sales['month'] . '/' . $sales['year']; }, $monthly_sales)); ?>,
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
                                callback: function (value) {
                                    return 'Rp ' + value.toLocaleString();
                                }
                            }
                        }
                    }
                }
            });            // Status Chart
            const statusCtx = document.getElementById('statusChart').getContext('2d');
            window.statusChart = new Chart(statusCtx, {
                type: 'doughnut',
                data: {
                    labels: <?php echo json_encode(array_map(function ($status) {
                        return ucfirst($status['status']); }, $order_status_data)); ?>,
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

        // Export Report Functions
        function exportReport(type) {
            const validation = validateDateRange();
            if (!validation.valid) {
                alert(validation.message);
                return;
            }
            
            const dateFrom = document.getElementById('date_from')?.value || '';
            const dateTo = document.getElementById('date_to')?.value || '';
            const reportType = document.querySelector('input[name="report_type"]:checked')?.value || 'summary';
            
            // Show loading state
            const button = event.target.closest('button');
            const originalText = button.innerHTML;
            button.disabled = true;
            button.innerHTML = '<i class="bi bi-arrow-clockwise spin"></i> Generating...';
            
            // Build export URL
            const params = new URLSearchParams({
                type: type,
                report_type: reportType,
                date_from: dateFrom,
                date_to: dateTo
            });
            
            const exportUrl = `export-report.php?${params.toString()}`;
            
            // Open in new window for download
            window.open(exportUrl, '_blank');
            
            // Reset button after a short delay
            setTimeout(() => {
                button.disabled = false;
                button.innerHTML = originalText;
            }, 2000);
        }

        // Validation functions
        function validateDateRange() {
            const dateFrom = document.getElementById('date_from')?.value;
            const dateTo = document.getElementById('date_to')?.value;
            
            if (!dateFrom || !dateTo) {
                return { valid: false, message: 'Silakan pilih tanggal mulai dan tanggal akhir.' };
            }
            
            if (new Date(dateFrom) > new Date(dateTo)) {
                return { valid: false, message: 'Tanggal mulai tidak boleh lebih besar dari tanggal akhir.' };
            }
            
            // Check if date range is too large (more than 1 year)
            const diffTime = Math.abs(new Date(dateTo) - new Date(dateFrom));
            const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24));
            
            if (diffDays > 365) {
                return { valid: false, message: 'Range tanggal tidak boleh lebih dari 1 tahun.' };
            }
            
            return { valid: true };
        }

        // Enhanced generate report with validation
        function generateReport() {
            const validation = validateDateRange();
            if (!validation.valid) {
                alert(validation.message);
                return;
            }
            
            const dateFrom = document.getElementById('date_from')?.value;
            const dateTo = document.getElementById('date_to')?.value;
            const reportType = document.querySelector('input[name="report_type"]:checked')?.value || 'summary';
            
            // Show loading
            showReportLoading(true);
            
            // Build URL with parameters
            const params = new URLSearchParams({
                date_from: dateFrom || '',
                date_to: dateTo || '',
                report_type: reportType
            });
            
            // Reload page with new parameters
            window.location.href = `reports.php?${params.toString()}`;
        }

        // Print Report
        function printReport() {
            window.print();
        }

        // Refresh Data
        function refreshData() {
            refreshReportData();
        }

        // AJAX function to refresh report data
        function refreshReportData() {
            const dateFrom = document.getElementById('date_from')?.value || '';
            const dateTo = document.getElementById('date_to')?.value || '';
            const reportType = document.querySelector('input[name="report_type"]:checked')?.value || 'summary';
            
            showReportLoading(true);
            
            // Build parameters
            const params = new URLSearchParams({
                ajax: '1',
                date_from: dateFrom,
                date_to: dateTo,
                report_type: reportType
            });
            
            fetch(`reports.php?${params.toString()}`)
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        updateReportDisplay(data);
                        showReportLoading(false);
                    } else {
                        throw new Error(data.error || 'Unknown error');
                    }
                })
                .catch(error => {
                    console.error('Error refreshing data:', error);
                    alert('Error refreshing report data: ' + error.message);
                    showReportLoading(false);
                });
        }

        // Update report display with new data
        function updateReportDisplay(data) {
            // Update statistics
            updateStatistics(data.stats);
            
            // Update charts
            updateCharts(data.chartData);
            
            // Update tables if available
            if (data.tableData) {
                updateTables(data.tableData);
            }
        }

        // Update statistics cards
        function updateStatistics(stats) {
            if (stats) {
                // Update revenue
                const revenueElement = document.querySelector('.stats-number');
                if (revenueElement && stats.total_revenue !== undefined) {
                    revenueElement.textContent = 'Rp ' + new Intl.NumberFormat('id-ID').format(stats.total_revenue);
                }
                
                // Update other stats as needed
                // You can add more updates here based on your stats structure
            }
        }

        // Update charts with new data
        function updateCharts(chartData) {
            if (chartData && window.revenueChart && window.statusChart) {
                // Update revenue chart
                if (chartData.revenue) {
                    window.revenueChart.data.labels = chartData.revenue.labels;
                    window.revenueChart.data.datasets[0].data = chartData.revenue.data;
                    window.revenueChart.update();
                }
                
                // Update status chart
                if (chartData.status) {
                    window.statusChart.data.labels = chartData.status.labels;
                    window.statusChart.data.datasets[0].data = chartData.status.data;
                    window.statusChart.update();
                }
            }
        }

        // Update tables with new data
        function updateTables(tableData) {
            // Implementation for updating tables if you have them
            console.log('Table data update:', tableData);
        }

        // Auto-refresh functionality
        let autoRefreshInterval;
        
        function startAutoRefresh(intervalMinutes = 5) {
            stopAutoRefresh(); // Clear any existing interval
            autoRefreshInterval = setInterval(() => {
                console.log('Auto-refreshing report data...');
                refreshReportData();
            }, intervalMinutes * 60 * 1000);
        }
        
        function stopAutoRefresh() {
            if (autoRefreshInterval) {
                clearInterval(autoRefreshInterval);
                autoRefreshInterval = null;
            }
        }

        // Toggle auto-refresh
        function toggleAutoRefresh() {
            const button = event.target;
            if (autoRefreshInterval) {
                stopAutoRefresh();
                button.textContent = 'Start Auto-Refresh';
                button.classList.remove('btn-success');
                button.classList.add('btn-outline-success');
            } else {
                startAutoRefresh(5); // 5 minutes
                button.textContent = 'Stop Auto-Refresh';
                button.classList.remove('btn-outline-success');
                button.classList.add('btn-success');
            }
        }

        // Export with current filter settings
        function exportReportWithFilters(type) {
            const dateFrom = document.getElementById('date_from')?.value || '';
            const dateTo = document.getElementById('date_to')?.value || '';
            const reportType = document.querySelector('input[name="report_type"]:checked')?.value || 'summary';
            
            // Show confirmation
            const period = dateFrom && dateTo ? 
                `${dateFrom} to ${dateTo}` : 
                'current period';
            
            if (confirm(`Export ${reportType} report for ${period} as ${type.toUpperCase()}?`)) {
                exportReport(type);
            }
        }

        // Keyboard shortcuts
        document.addEventListener('keydown', function(e) {
            // Ctrl + R for refresh
            if (e.ctrlKey && e.key === 'r') {
                e.preventDefault();
                refreshData();
            }
            
            // Ctrl + P for print
            if (e.ctrlKey && e.key === 'p') {
                e.preventDefault();
                printReport();
            }
            
            // Ctrl + E for Excel export
            if (e.ctrlKey && e.key === 'e') {
                e.preventDefault();
                exportReportWithFilters('excel');
            }
        });

        // Error handling for fetch requests
        function handleFetchError(error) {
            console.error('Fetch error:', error);
            
            let message = 'Terjadi kesalahan saat memuat data.';
            if (error.message.includes('Failed to fetch')) {
                message = 'Tidak dapat terhubung ke server. Periksa koneksi internet Anda.';
            } else if (error.message.includes('404')) {
                message = 'Halaman tidak ditemukan.';
            } else if (error.message.includes('500')) {
                message = 'Terjadi kesalahan server. Silakan coba lagi nanti.';
            }
            
            alert(message);
            showReportLoading(false);
        }

        // Initialize page
        document.addEventListener('DOMContentLoaded', function() {
            // Initialize charts if containers exist
            if (document.getElementById('revenueChart') && document.getElementById('statusChart')) {
                initializeCharts();
            }
            
            // Add tooltips to buttons
            const tooltips = document.querySelectorAll('[title]');
            tooltips.forEach(element => {
                element.setAttribute('data-bs-toggle', 'tooltip');
            });
            
            // Initialize Bootstrap tooltips if available
            if (typeof bootstrap !== 'undefined' && bootstrap.Tooltip) {
                const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
                tooltipTriggerList.map(function (tooltipTriggerEl) {
                    return new bootstrap.Tooltip(tooltipTriggerEl);
                });
            }
            
            console.log('Reports page initialized successfully');
        });

        // Add spinning animation CSS
        const style = document.createElement('style');
        style.textContent = `
            .spin {
                animation: spin 1s linear infinite;
            }
            @keyframes spin {
                from { transform: rotate(0deg); }
                to { transform: rotate(360deg); }
            }
            .loading-overlay {
                position: fixed;
                top: 0;
                left: 0;
                width: 100%;
                height: 100%;
                background: rgba(0,0,0,0.5);
                display: flex;
                justify-content: center;
                align-items: center;
                z-index: 9999;
                display: none;
            }
            .loading-spinner {
                background: white;
                padding: 20px;
                border-radius: 10px;
                text-align: center;
            }
        `;
        document.head.appendChild(style);

        // Add loading overlay to page
        document.addEventListener('DOMContentLoaded', function () {
            const loadingOverlay = document.createElement('div');
            loadingOverlay.id = 'reportLoading';
            loadingOverlay.className = 'loading-overlay';
            loadingOverlay.innerHTML = `
                <div class="loading-spinner">
                    <i class="bi bi-arrow-clockwise spin" style="font-size: 2rem;"></i>
                    <p class="mt-2 mb-0">Generating report...</p>
                </div>
            `;
            document.body.appendChild(loadingOverlay);
        });
    </script>
</body>

</html>