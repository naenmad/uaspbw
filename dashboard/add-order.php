<?php
// Add Order Page with Authentication
session_start();
require_once '../config/database.php';
require_once '../config/auth.php';
require_once '../includes/order-functions.php';

// Require user to be logged in
require_login();

// Get current user data
$current_user = get_logged_in_user();

$success = '';
$error = '';

// Get customers for dropdown
$stmt = $pdo->query("SELECT id, name FROM customers ORDER BY name");
$customers = $stmt->fetchAll(PDO::FETCH_ASSOC);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // Validasi input
        if (empty($_POST['customer_id'])) {
            throw new Exception("Silakan pilih pelanggan");
        }
        if (empty($_POST['order_date'])) {
            throw new Exception("Tanggal order harus diisi");
        }
        if (!isset($_POST['product_name']) || empty(array_filter($_POST['product_name']))) {
            throw new Exception("Minimal harus ada satu item produk");
        }

        $pdo->beginTransaction();

        // Calculate total amount
        $total_amount = 0;
        $items = [];
        if (isset($_POST['product_name'])) {
            for ($i = 0; $i < count($_POST['product_name']); $i++) {
                if (!empty($_POST['product_name'][$i])) {
                    $quantity = (int) $_POST['quantity'][$i];
                    $unit_price = (float) $_POST['unit_price'][$i];

                    if ($quantity <= 0) {
                        throw new Exception("Jumlah produk harus lebih dari 0");
                    }
                    if ($unit_price <= 0) {
                        throw new Exception("Harga satuan harus lebih dari 0");
                    }

                    $item_total = $quantity * $unit_price;
                    $total_amount += $item_total;

                    $items[] = [
                        'product_name' => $_POST['product_name'][$i],
                        'quantity' => $quantity,
                        'unit_price' => $unit_price,
                        'total_price' => $item_total
                    ];
                }
            }
        }

        // Generate order number
        $stmt = $pdo->query("SELECT MAX(id) FROM orders");
        $max_id = $stmt->fetchColumn() ?: 0;
        $order_number = 'ORD' . str_pad($max_id + 1, 3, '0', STR_PAD_LEFT);

        // Insert order
        $stmt = $pdo->prepare("INSERT INTO orders (order_number, customer_id, order_date, status, notes, total_amount, created_by, created_at) 
                               VALUES (?, ?, ?, ?, ?, ?, ?, NOW())");
        $stmt->execute([
            $order_number,
            $_POST['customer_id'],
            $_POST['order_date'],
            'pending',
            $_POST['notes'] ?? '',
            $total_amount,
            $current_user['id']
        ]);

        $order_id = $pdo->lastInsertId();        // Insert order items
        if (!empty($items)) {
            $stmt = $pdo->prepare("INSERT INTO order_items (order_id, product_id, product_name, quantity, unit_price, total_price) 
                                   VALUES (?, NULL, ?, ?, ?, ?)");
            foreach ($items as $item) {
                $stmt->execute([
                    $order_id,
                    $item['product_name'],
                    $item['quantity'],
                    $item['unit_price'],
                    $item['total_price']
                ]);
            }
        }

        // Log activity
        $stmt = $pdo->prepare("INSERT INTO activity_logs (user_id, action, model, model_id, description, created_at) 
                               VALUES (?, 'CREATE', 'Order', ?, ?, NOW())");
        $stmt->execute([
            $current_user['id'],
            $order_id,
            "Order {$order_number} berhasil dibuat"
        ]);
        $pdo->commit();
        $success = "Order {$order_number} berhasil ditambahkan!";

    } catch (Exception $e) {
        $pdo->rollback();
        $error = $e->getMessage();
    } catch (PDOException $e) {
        $pdo->rollback();
        // Handle specific database errors
        if (strpos($e->getMessage(), "doesn't have a default value") !== false) {
            $error = "Error database: Silakan jalankan update schema terlebih dahulu (update_schema.sql)";
        } else {
            $error = "Database error: " . $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tambah Order - Sistem Pencatatan Order</title>

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

        .form-control,
        .form-select {
            border-radius: 8px;
            border: 1px solid #dee2e6;
            padding: 10px 15px;
        }

        .form-control:focus,
        .form-select:focus {
            border-color: #007bff;
            box-shadow: 0 0 0 0.2rem rgba(0, 123, 255, 0.25);
        }

        .btn {
            border-radius: 8px;
            padding: 10px 20px;
            font-weight: 500;
        }

        .item-row {
            border: 1px solid #dee2e6;
            border-radius: 8px;
            padding: 15px;
            margin-bottom: 15px;
            background-color: #f8f9fa;
        }

        .remove-item {
            position: absolute;
            top: 10px;
            right: 10px;
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
                <a href="add-order.php" class="active">
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
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h4 class="card-title mb-1">
                                        <i class="bi bi-plus-circle me-2"></i>
                                        Tambah Order Baru
                                    </h4>
                                    <p class="text-muted mb-0">Buat order baru untuk pelanggan</p>
                                </div>
                                <a href="orders.php" class="btn btn-outline-secondary">
                                    <i class="bi bi-arrow-left me-1"></i>
                                    Kembali ke Daftar Order
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Alert Messages -->
            <?php if ($success): ?>
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <i class="bi bi-check-circle me-2"></i>
                    <?= htmlspecialchars($success) ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>

            <?php if ($error): ?>
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <i class="bi bi-exclamation-triangle me-2"></i>
                    <?= htmlspecialchars($error) ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>

            <!-- Add Order Form -->
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="card-title mb-0">
                                <i class="bi bi-clipboard-data me-2"></i>
                                Informasi Order
                            </h5>
                        </div>
                        <div class="card-body">
                            <form method="POST" id="orderForm">
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="customer_id" class="form-label">
                                                <i class="bi bi-person me-1"></i>
                                                Pelanggan <span class="text-danger">*</span>
                                            </label>
                                            <select name="customer_id" id="customer_id" class="form-select" required>
                                                <option value="">-- Pilih Pelanggan --</option>
                                                <?php foreach ($customers as $customer): ?>
                                                    <option value="<?= htmlspecialchars($customer['id']) ?>">
                                                        <?= htmlspecialchars($customer['name']) ?>
                                                    </option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="order_date" class="form-label">
                                                <i class="bi bi-calendar me-1"></i>
                                                Tanggal Order <span class="text-danger">*</span>
                                            </label>
                                            <input type="date" name="order_date" id="order_date" class="form-control"
                                                value="<?= date('Y-m-d') ?>" required>
                                        </div>
                                    </div>
                                </div>

                                <div class="mb-3">
                                    <label for="notes" class="form-label">
                                        <i class="bi bi-chat-text me-1"></i>
                                        Catatan
                                    </label>
                                    <textarea name="notes" id="notes" class="form-control" rows="3"
                                        placeholder="Tambahkan catatan untuk order ini..."></textarea>
                                </div>

                                <hr>

                                <!-- Order Items -->
                                <div class="d-flex justify-content-between align-items-center mb-3">
                                    <h5 class="mb-0">
                                        <i class="bi bi-box me-2"></i>
                                        Item Produk
                                    </h5>
                                    <button type="button" class="btn btn-success btn-sm" onclick="addItem()">
                                        <i class="bi bi-plus me-1"></i>
                                        Tambah Item
                                    </button>
                                </div>

                                <div id="items-container">
                                    <div class="item-row position-relative">
                                        <div class="row">
                                            <div class="col-md-4">
                                                <div class="mb-3">
                                                    <label class="form-label">
                                                        Nama Produk <span class="text-danger">*</span>
                                                    </label>
                                                    <input type="text" name="product_name[]" class="form-control"
                                                        placeholder="Masukkan nama produk" required>
                                                </div>
                                            </div>
                                            <div class="col-md-3">
                                                <div class="mb-3">
                                                    <label class="form-label">
                                                        Jumlah <span class="text-danger">*</span>
                                                    </label>
                                                    <input type="number" name="quantity[]" class="form-control" min="1"
                                                        placeholder="0" required>
                                                </div>
                                            </div>
                                            <div class="col-md-3">
                                                <div class="mb-3">
                                                    <label class="form-label">
                                                        Harga Satuan <span class="text-danger">*</span>
                                                    </label>
                                                    <input type="number" name="unit_price[]" class="form-control"
                                                        min="0" step="0.01" placeholder="0.00" required>
                                                </div>
                                            </div>
                                            <div class="col-md-2">
                                                <div class="mb-3">
                                                    <label class="form-label">Total</label>
                                                    <input type="text" class="form-control total-price" readonly
                                                        placeholder="0.00">
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="row mt-4">
                                    <div class="col-md-6">
                                        <div class="card bg-light">
                                            <div class="card-body">
                                                <h6 class="card-title">
                                                    <i class="bi bi-calculator me-1"></i>
                                                    Total Order
                                                </h6>
                                                <h4 class="text-primary" id="grand-total">Rp 0</h4>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="d-flex justify-content-end gap-2 mt-4">
                                    <a href="orders.php" class="btn btn-secondary">
                                        <i class="bi bi-x-circle me-1"></i>
                                        Batal
                                    </a>
                                    <button type="submit" class="btn btn-primary">
                                        <i class="bi bi-check-circle me-1"></i>
                                        Simpan Order
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

    <script>
        let itemCount = 1;

        // Toggle sidebar for mobile
        function toggleSidebar() {
            const sidebar = document.getElementById('sidebar');
            sidebar.classList.toggle('show');
        }

        // Add new item row
        function addItem() {
            itemCount++;
            const container = document.getElementById('items-container');
            const newItem = document.createElement('div');
            newItem.className = 'item-row position-relative';
            newItem.innerHTML = `
                <button type="button" class="btn btn-danger btn-sm remove-item" onclick="removeItem(this)">
                    <i class="bi bi-trash"></i>
                </button>
                <div class="row">
                    <div class="col-md-4">
                        <div class="mb-3">
                            <label class="form-label">
                                Nama Produk <span class="text-danger">*</span>
                            </label>
                            <input type="text" name="product_name[]" class="form-control" 
                                   placeholder="Masukkan nama produk" required>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="mb-3">
                            <label class="form-label">
                                Jumlah <span class="text-danger">*</span>
                            </label>
                            <input type="number" name="quantity[]" class="form-control" 
                                   min="1" placeholder="0" required>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="mb-3">
                            <label class="form-label">
                                Harga Satuan <span class="text-danger">*</span>
                            </label>
                            <input type="number" name="unit_price[]" class="form-control" 
                                   min="0" step="0.01" placeholder="0.00" required>
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="mb-3">
                            <label class="form-label">Total</label>
                            <input type="text" class="form-control total-price" readonly 
                                   placeholder="0.00">
                        </div>
                    </div>
                </div>
            `;
            container.appendChild(newItem);
            updateCalculations();
        }

        // Remove item row
        function removeItem(button) {
            if (document.querySelectorAll('.item-row').length > 1) {
                button.closest('.item-row').remove();
                updateCalculations();
            } else {
                alert('Minimal harus ada satu item produk!');
            }
        }

        // Update calculations
        function updateCalculations() {
            let grandTotal = 0;

            document.querySelectorAll('.item-row').forEach(row => {
                const quantity = parseFloat(row.querySelector('input[name="quantity[]"]').value) || 0;
                const unitPrice = parseFloat(row.querySelector('input[name="unit_price[]"]').value) || 0;
                const total = quantity * unitPrice;

                row.querySelector('.total-price').value = total.toLocaleString('id-ID');
                grandTotal += total;
            });

            document.getElementById('grand-total').textContent =
                'Rp ' + grandTotal.toLocaleString('id-ID');
        }

        // Add event listeners for calculations
        document.addEventListener('input', function (e) {
            if (e.target.name === 'quantity[]' || e.target.name === 'unit_price[]') {
                updateCalculations();
            }
        });

        // Close sidebar when clicking outside on mobile
        document.addEventListener('click', function (event) {
            const sidebar = document.getElementById('sidebar');
            const toggleBtn = event.target.closest('.btn');

            if (window.innerWidth <= 768 && !sidebar.contains(event.target) && !toggleBtn) {
                sidebar.classList.remove('show');
            }
        });

        // Initial calculation
        updateCalculations();
    </script>
</body>

</html>