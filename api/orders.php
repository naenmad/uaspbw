<?php
// File: api/customers.php
require_once __DIR__ . '/../config/database.php';
header('Content-Type: application/json');

// Ambil method dan data dari body (jika ada)
$method = $_SERVER['REQUEST_METHOD'];
$input = json_decode(file_get_contents('php://input'), true);

// GET: Ambil semua pelanggan
if ($method === 'GET') {
    try {
        $stmt = $pdo->query("SELECT * FROM customers ORDER BY created_at DESC");
        $customers = $stmt->fetchAll(PDO::FETCH_ASSOC);
        echo json_encode($customers);
    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode(["error" => "Gagal mengambil data pelanggan"]);
    }
    exit;
}

// POST: Tambah pelanggan baru
if ($method === 'POST') {
    $required = ['name', 'email'];
    foreach ($required as $field) {
        if (empty($input[$field])) {
            http_response_code(400);
            echo json_encode(["error" => "$field wajib diisi"]);
            exit;
        }
    }

    try {
        $sql = "INSERT INTO customers (customer_code, name, email, phone, address, city, postal_code, company, customer_type, status, notes, created_at)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())";

        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            $input['customer_code'] ?? uniqid('CUST'),
            $input['name'],
            $input['email'],
            $input['phone'] ?? null,
            $input['address'] ?? null,
            $input['city'] ?? null,
            $input['postal_code'] ?? null,
            $input['company'] ?? null,
            $input['customer_type'] ?? 'regular',
            $input['status'] ?? 'active',
            $input['notes'] ?? null
        ]);

        echo json_encode(["success" => true, "message" => "Pelanggan berhasil ditambahkan"]);
    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode(["error" => "Gagal menambahkan pelanggan"]);
    }
    exit;
}

// Jika method tidak diizinkan
http_response_code(405);
echo json_encode(["error" => "Metode tidak diizinkan"]);
