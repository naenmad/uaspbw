<?php
/**
 * Logout Handler
 * File: auth/logout.php
 * Author: Anggota 1 - Authentication Team
 */

session_start();
require_once '../config/database.php';
require_once '../config/auth.php';

// Check if user is logged in
if (!is_logged_in()) {
    header('Location: login.php');
    exit();
}

// Perform logout
$logout_result = logout_user();

// Redirect to login page with message
$message = $logout_result['success'] ? 'logout_success' : 'logout_error';
header("Location: login.php?message=$message");
exit();
?>