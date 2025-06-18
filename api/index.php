<?php
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

$routes = [
    '/api/auth' => 'auth.php',
    '/api/orders' => 'orders.php',
    '/api/customers' => 'customers.php',
    '/api/reports' => 'reports.php'
];

$path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

if (isset($routes[$path])) {
    require_once __DIR__ . '/' . $routes[$path];
} else {
    http_response_code(404);
    echo json_encode(["error" => "API endpoint not found"]);
}
