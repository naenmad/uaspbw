<?php
// File: api/reports.php
require_once __DIR__ . '/../config/database.php';
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405);
    echo json_encode(["error" => "Metode tidak diizinkan"]);
    exit;
}

try {
    // Total orders
    $stmt = $pdo->query("SELECT COUNT(*) as total_orders FROM orders");
    $total_orders = $stmt->fetch()['total_orders'];

    // Total customers
    $stmt = $pdo->query("SELECT COUNT(*) as total_customers FROM customers");
    $total_customers = $stmt->fetch()['total_customers'];

    // Total revenue
    $stmt = $pdo->query("SELECT SUM(total_amount) as total_revenue FROM orders WHERE payment_status = 'paid'");
    $total_revenue = $stmt->fetch()['total_revenue'] ?? 0;

    // Order per status
    $stmt = $pdo->query("SELECT status, COUNT(*) as count FROM orders GROUP BY status");
    $order_status_counts = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode([
        "total_orders" => (int)$total_orders,
        "total_customers" => (int)$total_customers,
        "total_revenue" => (float)$total_revenue,
        "order_status_counts" => $order_status_counts
    ]);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(["error" => "Gagal mengambil data laporan"]);
}
