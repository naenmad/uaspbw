<?php
// File: api/auth.php
require_once __DIR__ . '/../config/database.php';

header('Content-Type: application/json');

// Ambil data dari JSON body
$input = json_decode(file_get_contents('php://input'), true);

// Hanya izinkan metode POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $input['username'] ?? '';
    $password = $input['password'] ?? '';

    if (empty($username) || empty($password)) {
        http_response_code(400);
        echo json_encode(["error" => "Username dan password wajib diisi"]);
        exit;
    }

    $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ? OR email = ?");
    $stmt->execute([$username, $username]);
    $user = $stmt->fetch();

    if ($user && password_verify($password, $user['password'])) {
        echo json_encode([
            "success" => true,
            "user" => [
                "id" => $user['id'],
                "username" => $user['username'],
                "full_name" => $user['full_name'],
                "email" => $user['email'],
                "role" => $user['role']
            ]
        ]);
    } else {
        http_response_code(401);
        echo json_encode(["error" => "Login gagal. Username atau password salah."]);
    }
} else {
    http_response_code(405);
    echo json_encode(["error" => "Metode tidak diizinkan"]);
}
