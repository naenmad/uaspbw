<?php
// Settings Page with Authentication and Profile Management
session_start();
require_once '../config/database.php';
require_once '../config/auth.php';

// Require user to be logged in
require_login();

// Get current user data
$current_user = get_logged_in_user();
$user_id = $current_user['id'];

$success_message = '';
$error_message = '';

// Handle profile update
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['update_profile'])) {
        $csrf_token = $_POST['csrf_token'] ?? '';

        if (!verify_csrf_token($csrf_token)) {
            $error_message = 'Token keamanan tidak valid.';
        } else {
            $profile_data = [
                'full_name' => trim($_POST['full_name'] ?? ''),
                'email' => trim($_POST['email'] ?? ''),
                'phone' => trim($_POST['phone'] ?? ''),
                'address' => trim($_POST['address'] ?? '')
            ];

            $update_result = update_user_profile($user_id, $profile_data);

            if ($update_result['success']) {
                $success_message = $update_result['message'];                // Refresh current user data
                $current_user = get_logged_in_user();
            } else {
                $error_message = $update_result['message'];
            }
        }
    }

    // Handle password change
    if (isset($_POST['change_password'])) {
        $csrf_token = $_POST['csrf_token_password'] ?? '';

        if (!verify_csrf_token($csrf_token)) {
            $error_message = 'Token keamanan tidak valid.';
        } else {
            $current_password = $_POST['current_password'] ?? '';
            $new_password = $_POST['new_password'] ?? '';
            $confirm_password = $_POST['confirm_password'] ?? '';

            if ($new_password !== $confirm_password) {
                $error_message = 'Konfirmasi password baru tidak cocok!';
            } else {
                $password_result = change_user_password($user_id, $current_password, $new_password);

                if ($password_result['success']) {
                    $success_message = $password_result['message'];
                } else {
                    $error_message = $password_result['message'];
                }
            }
        }
    }
}

// Get updated user info from database
try {
    $sql = "SELECT * FROM users WHERE id = ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$user_id]);
    $user_info = $stmt->fetch(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $error_message = 'Gagal memuat data pengguna.';
    $user_info = $current_user;
}

// Generate CSRF tokens
$csrf_token = generate_csrf_token();
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pengaturan - Sistem Pencatatan Order</title>

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-icons/1.11.0/font/bootstrap-icons.min.css"
        rel="stylesheet">

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

        .settings-nav {
            background: #f8f9fa;
            border-radius: 8px;
            padding: 0;
        }

        .settings-nav .nav-link {
            border-radius: 8px;
            color: #495057;
            padding: 12px 16px;
            margin: 4px;
        }

        .settings-nav .nav-link.active {
            background: white;
            color: #007bff;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
        }

        .form-control,
        .form-select {
            border-radius: 8px;
            border: 1px solid #ddd;
            padding: 12px 15px;
        }

        .form-control:focus,
        .form-select:focus {
            border-color: #007bff;
            box-shadow: 0 0 0 0.2rem rgba(0, 123, 255, 0.25);
        }

        .profile-avatar {
            width: 100px;
            height: 100px;
            border-radius: 50%;
            background: linear-gradient(45deg, #007bff, #0056b3);
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 36px;
            font-weight: bold;
            margin: 0 auto 20px;
        }

        .switch {
            position: relative;
            display: inline-block;
            width: 50px;
            height: 24px;
        }

        .switch input {
            opacity: 0;
            width: 0;
            height: 0;
        }

        .slider {
            position: absolute;
            cursor: pointer;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background-color: #ccc;
            transition: .4s;
            border-radius: 24px;
        }

        .slider:before {
            position: absolute;
            content: "";
            height: 18px;
            width: 18px;
            left: 3px;
            bottom: 3px;
            background-color: white;
            transition: .4s;
            border-radius: 50%;
        }

        input:checked+.slider {
            background-color: #007bff;
        }

        input:checked+.slider:before {
            transform: translateX(26px);
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
                <a href="reports.php">
                    <i class="bi bi-graph-up me-2"></i>
                    Laporan
                </a>
            </li>
            <li>
                <a href="settings.php" class="active">
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
                            <h4 class="card-title mb-1">Pengaturan Sistem</h4>
                            <p class="text-muted mb-0">Kelola pengaturan aplikasi dan profil pengguna</p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row">
                <!-- Settings Navigation -->
                <div class="col-lg-3">
                    <div class="card">
                        <div class="card-body">
                            <ul class="nav nav-pills flex-column settings-nav" id="settingsTab" role="tablist">
                                <li class="nav-item">
                                    <a class="nav-link active" id="profile-tab" data-bs-toggle="pill" href="#profile"
                                        role="tab">
                                        <i class="bi bi-person me-2"></i>
                                        Profil
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link" id="security-tab" data-bs-toggle="pill" href="#security"
                                        role="tab">
                                        <i class="bi bi-shield-check me-2"></i>
                                        Keamanan
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link" id="notifications-tab" data-bs-toggle="pill"
                                        href="#notifications" role="tab">
                                        <i class="bi bi-bell me-2"></i>
                                        Notifikasi
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link" id="system-tab" data-bs-toggle="pill" href="#system" role="tab">
                                        <i class="bi bi-gear me-2"></i>
                                        Sistem
                                    </a>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>

                <!-- Settings Content -->
                <div class="col-lg-9">
                    <div class="tab-content" id="settingsTabContent"> <!-- Profile Settings -->
                        <div class="tab-pane fade show active" id="profile" role="tabpanel">
                            <div class="card">
                                <div class="card-header">
                                    <h5 class="mb-0">
                                        <i class="bi bi-person me-2"></i>
                                        Pengaturan Profil
                                    </h5>
                                </div>
                                <div class="card-body">
                                    <?php if ($success_message): ?>
                                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                                            <i class="bi bi-check-circle me-2"></i>
                                            <?php echo htmlspecialchars($success_message); ?>
                                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                                        </div>
                                    <?php endif; ?>

                                    <?php if ($error_message): ?>
                                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                            <i class="bi bi-exclamation-triangle me-2"></i>
                                            <?php echo htmlspecialchars($error_message); ?>
                                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                                        </div>
                                    <?php endif; ?>

                                    <div class="text-center mb-4">
                                        <div class="profile-avatar">
                                            <?php echo strtoupper(substr($user_info['full_name'], 0, 1)); ?>
                                        </div>
                                        <button class="btn btn-outline-primary btn-sm">
                                            <i class="bi bi-camera me-1"></i>
                                            Ubah Foto
                                        </button>
                                    </div>
                                    <form method="POST" action="" name="update_profile">
                                        <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
                                        <input type="hidden" name="update_profile" value="1">

                                        <div class="row">
                                            <div class="col-md-6 mb-3">
                                                <label class="form-label">Nama Lengkap <span
                                                        class="text-danger">*</span></label>
                                                <input type="text" name="full_name" class="form-control"
                                                    value="<?php echo htmlspecialchars($user_info['full_name']); ?>"
                                                    required>
                                            </div>
                                            <div class="col-md-6 mb-3">
                                                <label class="form-label">Email <span
                                                        class="text-danger">*</span></label>
                                                <input type="email" name="email" class="form-control"
                                                    value="<?php echo htmlspecialchars($user_info['email']); ?>"
                                                    required>
                                            </div>
                                        </div>

                                        <div class="row">
                                            <div class="col-md-6 mb-3">
                                                <label class="form-label">Nomor Telepon</label>
                                                <input type="tel" name="phone" class="form-control"
                                                    value="<?php echo htmlspecialchars($user_info['phone'] ?? ''); ?>">
                                            </div>
                                            <div class="col-md-6 mb-3">
                                                <label class="form-label">Username</label>
                                                <input type="text" class="form-control"
                                                    value="<?php echo htmlspecialchars($user_info['username']); ?>"
                                                    readonly disabled>
                                                <small class="text-muted">Username tidak dapat diubah</small>
                                            </div>
                                        </div>

                                        <div class="mb-3">
                                            <label class="form-label">Alamat</label>
                                            <textarea name="address" class="form-control"
                                                rows="3"><?php echo htmlspecialchars($user_info['address'] ?? ''); ?></textarea>
                                        </div>

                                        <div class="text-end">
                                            <button type="submit" class="btn btn-primary">
                                                <i class="bi bi-check me-1"></i>
                                                Simpan Perubahan
                                            </button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div> <!-- Security Settings -->
                        <div class="tab-pane fade" id="security" role="tabpanel">
                            <div class="card">
                                <div class="card-header">
                                    <h5 class="mb-0">
                                        <i class="bi bi-shield-check me-2"></i>
                                        Pengaturan Keamanan
                                    </h5>
                                </div>
                                <div class="card-body">
                                    <?php if ($success_message): ?>
                                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                                            <i class="bi bi-check-circle me-2"></i>
                                            <?php echo htmlspecialchars($success_message); ?>
                                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                                        </div>
                                    <?php endif; ?>

                                    <?php if ($error_message): ?>
                                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                            <i class="bi bi-exclamation-triangle me-2"></i>
                                            <?php echo htmlspecialchars($error_message); ?>
                                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                                        </div>
                                    <?php endif; ?>
                                    <form method="POST" action="" name="change_password">
                                        <input type="hidden" name="csrf_token_password"
                                            value="<?php echo $csrf_token; ?>">
                                        <input type="hidden" name="change_password" value="1">

                                        <h6 class="mb-3">Ubah Password</h6>

                                        <div class="mb-3">
                                            <label class="form-label">Password Lama <span
                                                    class="text-danger">*</span></label>
                                            <input type="password" name="current_password" class="form-control"
                                                required>
                                        </div>

                                        <div class="row">
                                            <div class="col-md-6 mb-3">
                                                <label class="form-label">Password Baru <span
                                                        class="text-danger">*</span></label>
                                                <input type="password" name="new_password" class="form-control"
                                                    minlength="6" required>
                                                <small class="text-muted">Minimal 6 karakter</small>
                                            </div>
                                            <div class="col-md-6 mb-3">
                                                <label class="form-label">Konfirmasi Password Baru <span
                                                        class="text-danger">*</span></label>
                                                <input type="password" name="confirm_password" class="form-control"
                                                    minlength="6" required>
                                            </div>
                                        </div>

                                        <div class="mb-4">
                                            <button type="submit" class="btn btn-primary">
                                                <i class="bi bi-key me-1"></i>
                                                Ubah Password
                                            </button>
                                        </div>
                                    </form>

                                    <hr>

                                    <h6 class="mb-3">Pengaturan Login</h6>
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="d-flex justify-content-between align-items-center mb-3">
                                                <div>
                                                    <strong>Two-Factor Authentication</strong><br>
                                                    <small class="text-muted">Keamanan tambahan saat login</small>
                                                </div>
                                                <label class="switch">
                                                    <input type="checkbox">
                                                    <span class="slider"></span>
                                                </label>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="d-flex justify-content-between align-items-center mb-3">
                                                <div>
                                                    <strong>Remember Login</strong><br>
                                                    <small class="text-muted">Ingat login selama 30 hari</small>
                                                </div>
                                                <label class="switch">
                                                    <input type="checkbox" checked>
                                                    <span class="slider"></span>
                                                </label>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Notification Settings -->
                        <div class="tab-pane fade" id="notifications" role="tabpanel">
                            <div class="card">
                                <div class="card-header">
                                    <h5 class="mb-0">
                                        <i class="bi bi-bell me-2"></i>
                                        Pengaturan Notifikasi
                                    </h5>
                                </div>
                                <div class="card-body">
                                    <h6 class="mb-3">Email Notifications</h6>

                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="d-flex justify-content-between align-items-center mb-3">
                                                <div>
                                                    <strong>Order Baru</strong><br>
                                                    <small class="text-muted">Notifikasi saat ada order baru</small>
                                                </div>
                                                <label class="switch">
                                                    <input type="checkbox" checked>
                                                    <span class="slider"></span>
                                                </label>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="d-flex justify-content-between align-items-center mb-3">
                                                <div>
                                                    <strong>Order Selesai</strong><br>
                                                    <small class="text-muted">Notifikasi saat order selesai</small>
                                                </div>
                                                <label class="switch">
                                                    <input type="checkbox" checked>
                                                    <span class="slider"></span>
                                                </label>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="d-flex justify-content-between align-items-center mb-3">
                                                <div>
                                                    <strong>Pelanggan Baru</strong><br>
                                                    <small class="text-muted">Notifikasi pelanggan terdaftar</small>
                                                </div>
                                                <label class="switch">
                                                    <input type="checkbox">
                                                    <span class="slider"></span>
                                                </label>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="d-flex justify-content-between align-items-center mb-3">
                                                <div>
                                                    <strong>Laporan Harian</strong><br>
                                                    <small class="text-muted">Summary harian via email</small>
                                                </div>
                                                <label class="switch">
                                                    <input type="checkbox" checked>
                                                    <span class="slider"></span>
                                                </label>
                                            </div>
                                        </div>
                                    </div>

                                    <hr>

                                    <h6 class="mb-3">Push Notifications</h6>

                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="d-flex justify-content-between align-items-center mb-3">
                                                <div>
                                                    <strong>Browser Notifications</strong><br>
                                                    <small class="text-muted">Notifikasi di browser</small>
                                                </div>
                                                <label class="switch">
                                                    <input type="checkbox" checked>
                                                    <span class="slider"></span>
                                                </label>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="d-flex justify-content-between align-items-center mb-3">
                                                <div>
                                                    <strong>Sound Alerts</strong><br>
                                                    <small class="text-muted">Suara notifikasi</small>
                                                </div>
                                                <label class="switch">
                                                    <input type="checkbox">
                                                    <span class="slider"></span>
                                                </label>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- System Settings -->
                        <div class="tab-pane fade" id="system" role="tabpanel">
                            <div class="card">
                                <div class="card-header">
                                    <h5 class="mb-0">
                                        <i class="bi bi-gear me-2"></i>
                                        Pengaturan Sistem
                                    </h5>
                                </div>
                                <div class="card-body">
                                    <form id="systemForm">
                                        <div class="row">
                                            <div class="col-md-6 mb-3">
                                                <label class="form-label">Nama Aplikasi</label>
                                                <input type="text" class="form-control" value="Sistem Pencatatan Order">
                                            </div>
                                            <div class="col-md-6 mb-3">
                                                <label class="form-label">Timezone</label>
                                                <select class="form-select">
                                                    <option value="Asia/Jakarta" selected>Asia/Jakarta (WIB)</option>
                                                    <option value="Asia/Makassar">Asia/Makassar (WITA)</option>
                                                    <option value="Asia/Jayapura">Asia/Jayapura (WIT)</option>
                                                </select>
                                            </div>
                                        </div>

                                        <div class="row">
                                            <div class="col-md-6 mb-3">
                                                <label class="form-label">Format Tanggal</label>
                                                <select class="form-select">
                                                    <option value="d/m/Y" selected>DD/MM/YYYY</option>
                                                    <option value="m/d/Y">MM/DD/YYYY</option>
                                                    <option value="Y-m-d">YYYY-MM-DD</option>
                                                </select>
                                            </div>
                                            <div class="col-md-6 mb-3">
                                                <label class="form-label">Mata Uang</label>
                                                <select class="form-select">
                                                    <option value="IDR" selected>Indonesian Rupiah (IDR)</option>
                                                    <option value="USD">US Dollar (USD)</option>
                                                    <option value="EUR">Euro (EUR)</option>
                                                </select>
                                            </div>
                                        </div>

                                        <div class="row">
                                            <div class="col-md-6 mb-3">
                                                <label class="form-label">Items Per Page</label>
                                                <select class="form-select">
                                                    <option value="10">10</option>
                                                    <option value="25" selected>25</option>
                                                    <option value="50">50</option>
                                                    <option value="100">100</option>
                                                </select>
                                            </div>
                                            <div class="col-md-6 mb-3">
                                                <label class="form-label">Auto Backup</label>
                                                <select class="form-select">
                                                    <option value="daily" selected>Harian</option>
                                                    <option value="weekly">Mingguan</option>
                                                    <option value="monthly">Bulanan</option>
                                                    <option value="disabled">Nonaktif</option>
                                                </select>
                                            </div>
                                        </div>

                                        <div class="text-end">
                                            <button type="submit" class="btn btn-primary">
                                                <i class="bi bi-check me-1"></i>
                                                Simpan Pengaturan
                                            </button>
                                        </div>
                                    </form>
                                </div>
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
        // Toggle sidebar for mobile
        function toggleSidebar() {
            const sidebar = document.getElementById('sidebar');
            sidebar.classList.toggle('show');
        }

        // Auto-hide alerts after 5 seconds
        setTimeout(function () {
            const alerts = document.querySelectorAll('.alert');
            alerts.forEach(function (alert) {
                const bsAlert = new bootstrap.Alert(alert);
                bsAlert.close();
            });
        }, 5000);

        // Form validation
        document.addEventListener('DOMContentLoaded', function () {
            // Profile form validation
            const profileForm = document.querySelector('form[name="update_profile"]');
            if (profileForm) {
                profileForm.addEventListener('submit', function (e) {
                    const fullName = profileForm.querySelector('input[name="full_name"]');
                    const email = profileForm.querySelector('input[name="email"]');

                    if (fullName.value.trim().length < 2) {
                        e.preventDefault();
                        alert('Nama lengkap minimal 2 karakter!');
                        fullName.focus();
                        return false;
                    }

                    // Simple email validation
                    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
                    if (!emailRegex.test(email.value)) {
                        e.preventDefault();
                        alert('Format email tidak valid!');
                        email.focus();
                        return false;
                    }
                });
            }

            // Password form validation
            const passwordForm = document.querySelector('form[name="change_password"]');
            if (passwordForm) {
                passwordForm.addEventListener('submit', function (e) {
                    const currentPassword = passwordForm.querySelector('input[name="current_password"]');
                    const newPassword = passwordForm.querySelector('input[name="new_password"]');
                    const confirmPassword = passwordForm.querySelector('input[name="confirm_password"]');

                    if (currentPassword.value.length < 1) {
                        e.preventDefault();
                        alert('Password lama harus diisi!');
                        currentPassword.focus();
                        return false;
                    }

                    if (newPassword.value.length < 6) {
                        e.preventDefault();
                        alert('Password baru minimal 6 karakter!');
                        newPassword.focus();
                        return false;
                    }

                    if (newPassword.value !== confirmPassword.value) {
                        e.preventDefault();
                        alert('Password baru dan konfirmasi password tidak cocok!');
                        confirmPassword.focus();
                        return false;
                    }

                    if (currentPassword.value === newPassword.value) {
                        e.preventDefault();
                        alert('Password baru harus berbeda dari password lama!');
                        newPassword.focus();
                        return false;
                    }
                });
            }
        });
    </script>
</body>

</html>