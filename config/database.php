<?php
// Database Configuration for Order Management System
$host = 'localhost';
$username = 'root';
$password = '';
$database = 'uaspbw_db';
$charset = 'utf8mb4';

// PDO options
$options = [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES => false,
    PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES {$charset}"
];

try {
    $dsn = "mysql:host={$host};dbname={$database};charset={$charset}";
    $pdo = new PDO($dsn, $username, $password, $options);
} catch (PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}

// Start session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Helper function to check if user is logged in
function isLoggedIn()
{
    return isset($_SESSION['user_id']);
}

// Helper function to redirect if not logged in
function requireLogin()
{
    if (!isLoggedIn()) {
        header('Location: ../auth/login.php');
        exit();
    }
}

// Helper function to get current user
function getCurrentUser()
{
    if (isLoggedIn()) {
        return [
            'id' => $_SESSION['user_id'],
            'username' => $_SESSION['username'] ?? 'User',
            'full_name' => $_SESSION['full_name'] ?? 'User',
            'email' => $_SESSION['email'] ?? '',
            'role' => $_SESSION['role'] ?? 'user'
        ];
    }
    return null;
}

// Helper function to log activity
function logActivity($action, $model = null, $model_id = null, $description = null)
{
    global $pdo;

    if (!isLoggedIn())
        return;

    $user_id = $_SESSION['user_id'];
    $ip_address = $_SERVER['REMOTE_ADDR'] ?? null;
    $user_agent = $_SERVER['HTTP_USER_AGENT'] ?? null;

    $sql = "INSERT INTO activity_logs (user_id, action, model, model_id, description, ip_address, user_agent) 
            VALUES (?, ?, ?, ?, ?, ?, ?)";

    try {
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$user_id, $action, $model, $model_id, $description, $ip_address, $user_agent]);
    } catch (PDOException $e) {
        error_log("Failed to log activity: " . $e->getMessage());
    }
}

// Helper function to get system setting
function getSystemSetting($key, $default = null)
{
    global $pdo;

    $sql = "SELECT setting_value, setting_type FROM system_settings WHERE setting_key = ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$key]);
    $result = $stmt->fetch();

    if (!$result) {
        return $default;
    }

    $value = $result['setting_value'];

    // Convert based on type
    switch ($result['setting_type']) {
        case 'boolean':
            return filter_var($value, FILTER_VALIDATE_BOOLEAN);
        case 'number':
            return is_numeric($value) ? (float) $value : $default;
        case 'json':
            return json_decode($value, true) ?: $default;
        default:
            return $value;
    }
}

// Helper function to generate next order number
function generateOrderNumber()
{
    global $pdo;

    try {
        $stmt = $pdo->prepare("CALL GetNextOrderNumber()");
        $stmt->execute();
        $result = $stmt->fetch();
        return $result['next_order_number'];
    } catch (PDOException $e) {
        // Fallback method
        $prefix = getSystemSetting('order_prefix', 'ORD');
        $sql = "SELECT MAX(CAST(SUBSTRING(order_number, LENGTH(?) + 1) AS UNSIGNED)) as max_num 
                FROM orders 
                WHERE order_number LIKE CONCAT(?, '%')";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$prefix, $prefix]);
        $result = $stmt->fetch();
        $next_num = ($result['max_num'] ?? 0) + 1;
        return $prefix . str_pad($next_num, 3, '0', STR_PAD_LEFT);
    }
}

// Helper function to format currency
function formatCurrency($amount, $currency = null)
{
    if ($currency === null) {
        $currency = getSystemSetting('default_currency', 'IDR');
    }

    switch ($currency) {
        case 'IDR':
            return 'Rp ' . number_format($amount, 0, ',', '.');
        case 'USD':
            return '$' . number_format($amount, 2, '.', ',');
        default:
            return $currency . ' ' . number_format($amount, 2, '.', ',');
    }
}
?>