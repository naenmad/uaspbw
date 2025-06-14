<?php
// Simple routing for the application
$request = $_SERVER['REQUEST_URI'];
$path = parse_url($request, PHP_URL_PATH);

// Remove the base path if running in subdirectory
$basePath = '/uaspbw';
if (strpos($path, $basePath) === 0) {
    $path = substr($path, strlen($basePath));
}

// Route handling
switch ($path) {
    case '/':
    case '/index.php':
        include 'index.html';
        break;

    case '/login':
    case '/login.php':
        include 'auth/login.php';
        break;

    case '/register':
    case '/register.php':
        include 'auth/register.php';
        break;

    case '/dashboard':
    case '/dashboard/':
    case '/dashboard/index.php':
        include 'dashboard/index.php';
        break;

    case '/dashboard/add-order':
    case '/dashboard/add-order.php':
        include 'dashboard/add-order.php';
        break;

    case '/dashboard/orders':
    case '/dashboard/orders.php':
        include 'dashboard/orders.php';
        break;

    case '/dashboard/customers':
    case '/dashboard/customers.php':
        include 'dashboard/customers.php';
        break;

    case '/dashboard/reports':
    case '/dashboard/reports.php':
        include 'dashboard/reports.php';
        break;

    case '/dashboard/settings':
    case '/dashboard/settings.php':
        include 'dashboard/settings.php';
        break;

    default:
        http_response_code(404);
        echo "Page not found";
        break;
}
?>