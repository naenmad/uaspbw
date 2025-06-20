<?php
// Export Reports - PDF and Excel functionality
session_start();
require_once '../config/database.php';
require_once '../config/auth.php';
require_once '../includes/pdf-exporter.php';

// Require user to be logged in
require_login();

// Get export type and date range
$export_type = $_GET['type'] ?? 'excel';
$date_from = $_GET['date_from'] ?? date('Y-m-01'); // Start of current month
$date_to = $_GET['date_to'] ?? date('Y-m-t'); // End of current month
$report_type = $_GET['report_type'] ?? 'summary';

try {
    // Base query for orders
    $base_query = "FROM orders o LEFT JOIN customers c ON o.customer_id = c.id WHERE 1=1";
    $params = [];

    if (!empty($date_from)) {
        $base_query .= " AND DATE(o.order_date) >= ?";
        $params[] = $date_from;
    }

    if (!empty($date_to)) {
        $base_query .= " AND DATE(o.order_date) <= ?";
        $params[] = $date_to;
    }

    // Get report data based on type
    if ($report_type === 'summary') {
        // Summary Report
        $data = [];

        // Total Statistics
        $total_query = "SELECT 
                            COUNT(*) as total_orders,
                            SUM(CASE WHEN o.status != 'cancelled' THEN o.total_amount ELSE 0 END) as total_revenue,
                            AVG(CASE WHEN o.status != 'cancelled' THEN o.total_amount ELSE NULL END) as avg_order_value,
                            COUNT(CASE WHEN o.status = 'completed' THEN 1 END) as completed_orders,
                            COUNT(CASE WHEN o.status = 'pending' THEN 1 END) as pending_orders,
                            COUNT(CASE WHEN o.status = 'cancelled' THEN 1 END) as cancelled_orders
                        " . $base_query;

        $stmt = $pdo->prepare($total_query);
        $stmt->execute($params);
        $summary = $stmt->fetch(PDO::FETCH_ASSOC);

        // Daily breakdown
        $daily_query = "SELECT 
                            DATE(o.order_date) as order_date,
                            COUNT(*) as total_orders,
                            SUM(CASE WHEN o.status != 'cancelled' THEN o.total_amount ELSE 0 END) as daily_revenue
                        " . $base_query . " 
                        GROUP BY DATE(o.order_date) 
                        ORDER BY order_date DESC";

        $stmt = $pdo->prepare($daily_query);
        $stmt->execute($params);
        $daily_data = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $data = [
            'summary' => $summary,
            'daily' => $daily_data,
            'date_from' => $date_from,
            'date_to' => $date_to
        ];

    } else {
        // Detailed Report
        $detailed_query = "SELECT 
                            o.id,
                            o.order_number,
                            o.order_date,
                            c.name as customer_name,
                            c.email as customer_email,
                            o.total_amount,
                            o.status,
                            o.payment_method,
                            o.payment_status,
                            o.created_at
                        " . $base_query . " 
                        ORDER BY o.order_date DESC";

        $stmt = $pdo->prepare($detailed_query);
        $stmt->execute($params);
        $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
    }    // Export based on type
    if ($export_type === 'excel') {
        exportToExcel($data, $report_type, $date_from, $date_to);
    } elseif ($export_type === 'pdf') {
        ReportPDFExporter::exportToPDF($data, $report_type, $date_from, $date_to);
    }

} catch (PDOException $e) {
    die("Error: " . $e->getMessage());
}

function exportToExcel($data, $report_type, $date_from, $date_to)
{
    // Set headers for Excel download
    header('Content-Type: application/vnd.ms-excel');
    header('Content-Disposition: attachment; filename="report_' . $report_type . '_' . date('Y-m-d') . '.xls"');
    header('Pragma: no-cache');
    header('Expires: 0');

    echo "<html><head><meta charset='UTF-8'></head><body>";

    if ($report_type === 'summary') {
        // Summary Report Excel
        echo "<h2>Laporan Ringkasan Order</h2>";
        echo "<p>Periode: " . date('d/m/Y', strtotime($date_from)) . " - " . date('d/m/Y', strtotime($date_to)) . "</p>";
        echo "<br>";

        // Summary table
        echo "<table border='1' cellpadding='5' cellspacing='0'>";
        echo "<tr style='background-color: #f0f0f0;'>";
        echo "<th>Metrik</th><th>Nilai</th>";
        echo "</tr>";

        $summary = $data['summary'];
        echo "<tr><td>Total Order</td><td>" . number_format($summary['total_orders']) . "</td></tr>";
        echo "<tr><td>Total Revenue</td><td>Rp " . number_format($summary['total_revenue'], 0, ',', '.') . "</td></tr>";
        echo "<tr><td>Rata-rata Nilai Order</td><td>Rp " . number_format($summary['avg_order_value'], 0, ',', '.') . "</td></tr>";
        echo "<tr><td>Order Selesai</td><td>" . number_format($summary['completed_orders']) . "</td></tr>";
        echo "<tr><td>Order Pending</td><td>" . number_format($summary['pending_orders']) . "</td></tr>";
        echo "<tr><td>Order Dibatalkan</td><td>" . number_format($summary['cancelled_orders']) . "</td></tr>";
        echo "</table>";

        echo "<br><br>";
        echo "<h3>Breakdown Harian</h3>";
        echo "<table border='1' cellpadding='5' cellspacing='0'>";
        echo "<tr style='background-color: #f0f0f0;'>";
        echo "<th>Tanggal</th><th>Total Order</th><th>Revenue Harian</th>";
        echo "</tr>";

        foreach ($data['daily'] as $day) {
            echo "<tr>";
            echo "<td>" . date('d/m/Y', strtotime($day['order_date'])) . "</td>";
            echo "<td>" . number_format($day['total_orders']) . "</td>";
            echo "<td>Rp " . number_format($day['daily_revenue'], 0, ',', '.') . "</td>";
            echo "</tr>";
        }
        echo "</table>";

    } else {
        // Detailed Report Excel
        echo "<h2>Laporan Detail Order</h2>";
        echo "<p>Periode: " . date('d/m/Y', strtotime($date_from)) . " - " . date('d/m/Y', strtotime($date_to)) . "</p>";
        echo "<br>";

        echo "<table border='1' cellpadding='5' cellspacing='0'>";
        echo "<tr style='background-color: #f0f0f0;'>";
        echo "<th>ID</th><th>No. Order</th><th>Tanggal</th><th>Customer</th><th>Email</th>";
        echo "<th>Total Amount</th><th>Status</th><th>Payment Method</th><th>Payment Status</th>";
        echo "</tr>";

        foreach ($data as $order) {
            echo "<tr>";
            echo "<td>" . htmlspecialchars($order['id']) . "</td>";
            echo "<td>" . htmlspecialchars($order['order_number']) . "</td>";
            echo "<td>" . date('d/m/Y H:i', strtotime($order['order_date'])) . "</td>";
            echo "<td>" . htmlspecialchars($order['customer_name'] ?? '-') . "</td>";
            echo "<td>" . htmlspecialchars($order['customer_email'] ?? '-') . "</td>";
            echo "<td>Rp " . number_format($order['total_amount'], 0, ',', '.') . "</td>";
            echo "<td>" . ucfirst(htmlspecialchars($order['status'])) . "</td>";
            echo "<td>" . htmlspecialchars($order['payment_method'] ?? '-') . "</td>";
            echo "<td>" . ucfirst(htmlspecialchars($order['payment_status'] ?? '-')) . "</td>";
            echo "</tr>";
        }
        echo "</table>";
    }
    echo "</body></html>";
    exit;
}
?>