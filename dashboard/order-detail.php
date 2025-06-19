<?php
// Order Detail Page with Authentication
session_start();
require_once '../config/database.php';
require_once '../config/auth.php';

// Require user to be logged in
require_login();

// Get current user data
$current_user = get_logged_in_user();

// Helper function for status badge
function getStatusBadgeClass($status) {
    switch (strtolower($status)) {
        case 'pending': return 'bg-warning';
        case 'confirmed': return 'bg-info';
        case 'processing': return 'bg-primary';
        case 'shipped': return 'bg-info';
        case 'delivered': return 'bg-success';
        case 'completed': return 'bg-success';
        case 'cancelled': return 'bg-danger';
        // Legacy support for old status values
        case 'proses': return 'bg-primary';
        case 'selesai': return 'bg-success';
        case 'batal': return 'bg-danger';
        default: return 'bg-secondary';
    }
}

$error = '';
$order = null;
$order_items = [];
$customer = null;

// Get order ID from URL
$order_id = $_GET['id'] ?? '';

if ($order_id) {
    try {
        // First, check if orders table exists and what columns it has
        $stmt = $pdo->query("SHOW TABLES LIKE 'orders'");
        $table_exists = $stmt->rowCount() > 0;
        
        if (!$table_exists) {
            // Create dummy data for demo purposes if table doesn't exist
            $order = [
                'id' => $order_id,
                'customer_id' => 1,
                'customer_name' => 'Demo Customer',
                'customer_email' => 'demo@example.com',
                'customer_phone' => '08123456789',
                'customer_address' => 'Jl. Demo No. 123',
                'customer_company' => 'Demo Company',                'customer_code' => 'CUST001',
                'order_date' => '2025-06-19',
                'status' => 'pending',
                'total_amount' => 150000,
                'notes' => 'Demo order for testing',
                'created_by_name' => 'System Admin',
                'created_at' => '2025-06-19 10:00:00',
                'updated_at' => '2025-06-19 10:00:00'
            ];
            
            $order_items = [
                [
                    'id' => 1,
                    'product_name' => 'Demo Product 1',
                    'quantity' => 2,
                    'unit_price' => 50000,
                    'total_price' => 100000
                ],
                [
                    'id' => 2,
                    'product_name' => 'Demo Product 2',
                    'quantity' => 1,
                    'unit_price' => 50000,
                    'total_price' => 50000
                ]
            ];
        } else {
            // Check columns in orders table
            $stmt = $pdo->query("SHOW COLUMNS FROM orders");
            $columns = $stmt->fetchAll(PDO::FETCH_COLUMN);
            
            // Build query based on available columns
            $order_fields = ['o.id'];
            $available_fields = [
                'customer_id' => 'o.customer_id',
                'order_date' => 'o.order_date',
                'status' => 'o.status',
                'total_amount' => 'o.total_amount',
                'notes' => 'o.notes',
                'created_by' => 'o.created_by',
                'created_at' => 'o.created_at',
                'updated_at' => 'o.updated_at'
            ];
            
            foreach ($available_fields as $field => $sql_field) {
                if (in_array($field, $columns)) {
                    $order_fields[] = $sql_field;
                }
            }
            
            // Customer fields
            $customer_fields = [
                'c.name as customer_name',
                'c.email as customer_email', 
                'c.phone as customer_phone',
                'c.address as customer_address',
                'c.company as customer_company'
            ];
            
            if (in_array('customer_code', $columns)) {
                $customer_fields[] = 'c.customer_code';
            }
            
            $customer_fields[] = 'u.full_name as created_by_name';
            
            $all_fields = array_merge($order_fields, $customer_fields);
              // Get order details with customer info
            // Check if order_id is numeric (ID) or string (order_number)
            if (is_numeric($order_id)) {
                $where_clause = "WHERE o.id = ?";
            } else {
                $where_clause = "WHERE o.order_number = ?";
            }
            
            $sql = "SELECT " . implode(', ', $all_fields) . "
                    FROM orders o 
                    LEFT JOIN customers c ON o.customer_id = c.id 
                    LEFT JOIN users u ON o.created_by = u.id
                    " . $where_clause;
                    
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$order_id]);
            $order = $stmt->fetch(PDO::FETCH_ASSOC);
              if (!$order) {
                $error = "Order tidak ditemukan.";
            } else {
                // Get the actual order ID for order_items query
                $actual_order_id = $order['id'];
                
                // Check if order_items table exists
                $stmt = $pdo->query("SHOW TABLES LIKE 'order_items'");
                $items_table_exists = $stmt->rowCount() > 0;
                
                if ($items_table_exists) {
                    // Get order items using actual order ID
                    $stmt = $pdo->prepare("SELECT * FROM order_items WHERE order_id = ? ORDER BY id");
                    $stmt->execute([$actual_order_id]);
                    $order_items = $stmt->fetchAll(PDO::FETCH_ASSOC);
                } else {
                    // Create dummy order items if table doesn't exist
                    $order_items = [
                        [
                            'id' => 1,
                            'product_name' => 'Sample Product',
                            'quantity' => 1,
                            'unit_price' => $order['total_amount'] ?? 0,
                            'total_price' => $order['total_amount'] ?? 0
                        ]
                    ];
                }
            }
        }    } catch (PDOException $e) {
        $error = "Error loading order: " . $e->getMessage();
        // Create fallback demo data
        $order = [
            'id' => $order_id,
            'customer_name' => 'Demo Customer',
            'order_date' => '2025-06-19',
            'status' => 'pending',
            'total_amount' => 150000,
            'notes' => 'Demo order - database error occurred',
            'created_by_name' => 'System',
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s')
        ];
        $order_items = [];
    }
} else {
    $error = "Order ID tidak valid.";
}

// Handle status update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'update_status' && $order_id && isset($order)) {
    try {
        // Check if orders table exists
        $stmt = $pdo->query("SHOW TABLES LIKE 'orders'");
        if ($stmt->rowCount() > 0) {
            // Use the actual order ID from the fetched order data
            $actual_order_id = $order['id'];
            $stmt = $pdo->prepare("UPDATE orders SET status = ?, updated_at = CURRENT_TIMESTAMP WHERE id = ?");
            $stmt->execute([$_POST['status'], $actual_order_id]);
            $success = "Status order berhasil diperbarui!";
            
            // Update the order status in our data
            if (isset($order) && $order) {
                $order['status'] = $_POST['status'];
            }
        } else {
            $error = "Tabel orders tidak ditemukan. Ini adalah demo mode.";
        }
    } catch (PDOException $e) {
        $error = "Error updating status: " . $e->getMessage();
    }
}
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detail Order - Sistem Pencatatan Order</title>

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

        .status-badge {
            padding: 8px 16px;
            border-radius: 20px;
            font-size: 14px;
            font-weight: 500;
        }

        .order-timeline {
            position: relative;
            padding-left: 30px;
        }

        .order-timeline::before {
            content: '';
            position: absolute;
            left: 10px;
            top: 0;
            bottom: 0;
            width: 2px;
            background-color: #dee2e6;
        }

        .timeline-item {
            position: relative;
            margin-bottom: 20px;
        }

        .timeline-item::before {
            content: '';
            position: absolute;
            left: -25px;
            top: 5px;
            width: 12px;
            height: 12px;
            border-radius: 50%;
            background-color: #007bff;
            border: 2px solid #fff;
            box-shadow: 0 0 0 2px #007bff;
        }

        .info-row {
            border-bottom: 1px solid #f8f9fa;
            padding: 12px 0;
        }

        .info-row:last-child {
            border-bottom: none;
        }

        .info-label {
            font-weight: 600;
            color: #495057;
            min-width: 150px;
        }

        .table th {
            background-color: #f8f9fa;
            font-weight: 600;
            border-top: none;
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
                <a href="orders.php" class="active">
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
            <!-- Alert Messages -->
            <?php if (isset($success)): ?>
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

            <?php if ($order): ?>
            <!-- Page Header -->
            <div class="row mb-4">
                <div class="col-12">
                    <div class="card">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h4 class="card-title mb-1">
                                        <i class="bi bi-receipt me-2"></i>
                                        Detail Order #<?php echo htmlspecialchars($order_id); ?>
                                    </h4>
                                    <p class="text-muted mb-0">Informasi lengkap order dan status terkini</p>
                                </div>
                                <div>
                                    <a href="orders.php" class="btn btn-outline-secondary me-2">
                                        <i class="bi bi-arrow-left me-1"></i>
                                        Kembali ke Daftar Order
                                    </a>
                                    <a href="edit-order.php?id=<?php echo $order_id; ?>" class="btn btn-warning">
                                        <i class="bi bi-pencil me-1"></i>
                                        Edit Order
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row">
                <!-- Order Information -->
                <div class="col-lg-8">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="card-title mb-0">
                                <i class="bi bi-info-circle me-2"></i>
                                Informasi Order
                            </h5>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="info-row d-flex">
                                        <span class="info-label">Order ID:</span>
                                        <span class="ms-auto">#<?php echo htmlspecialchars($order_id); ?></span>
                                    </div>                                    <div class="info-row d-flex">
                                        <span class="info-label">Tanggal Order:</span>
                                        <span class="ms-auto"><?php echo isset($order['order_date']) ? date('d M Y', strtotime($order['order_date'])) : 'N/A'; ?></span>
                                    </div>
                                    <div class="info-row d-flex">
                                        <span class="info-label">Status:</span>
                                        <span class="ms-auto">
                                            <span class="badge status-badge <?php echo getStatusBadgeClass($order['status'] ?? 'pending'); ?>">
                                                <?php echo htmlspecialchars($order['status'] ?? 'Pending'); ?>
                                            </span>
                                        </span>
                                    </div>
                                    <div class="info-row d-flex">
                                        <span class="info-label">Total Amount:</span>
                                        <span class="ms-auto fw-bold text-primary">
                                            Rp <?php echo number_format($order['total_amount'] ?? 0, 0, ',', '.'); ?>
                                        </span>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="info-row d-flex">
                                        <span class="info-label">Dibuat oleh:</span>
                                        <span class="ms-auto"><?php echo htmlspecialchars($order['created_by_name'] ?? 'System'); ?></span>
                                    </div>
                                    <div class="info-row d-flex">
                                        <span class="info-label">Dibuat pada:</span>
                                        <span class="ms-auto"><?php echo isset($order['created_at']) ? date('d M Y H:i', strtotime($order['created_at'])) : 'N/A'; ?></span>
                                    </div>
                                    <div class="info-row d-flex">
                                        <span class="info-label">Terakhir diupdate:</span>
                                        <span class="ms-auto"><?php echo isset($order['updated_at']) ? date('d M Y H:i', strtotime($order['updated_at'])) : 'N/A'; ?></span>
                                    </div>
                                </div>
                            </div>
                            
                            <?php if (!empty($order['notes'])): ?>
                            <hr>
                            <div class="info-row">
                                <span class="info-label d-block mb-2">Catatan:</span>
                                <div class="bg-light p-3 rounded">
                                    <?php echo nl2br(htmlspecialchars($order['notes'])); ?>
                                </div>
                            </div>
                            <?php endif; ?>
                        </div>
                    </div>

                    <!-- Order Items -->
                    <div class="card">
                        <div class="card-header">
                            <h5 class="card-title mb-0">
                                <i class="bi bi-box me-2"></i>
                                Item Order (<?php echo count($order_items); ?> item)
                            </h5>
                        </div>
                        <div class="card-body">
                            <?php if (empty($order_items)): ?>
                                <div class="text-center py-4">
                                    <i class="bi bi-inbox display-4 text-muted"></i>
                                    <p class="text-muted mt-2">Tidak ada item dalam order ini</p>
                                </div>
                            <?php else: ?>
                                <div class="table-responsive">
                                    <table class="table table-hover">
                                        <thead>
                                            <tr>
                                                <th>#</th>
                                                <th>Nama Produk</th>
                                                <th>Jumlah</th>
                                                <th>Harga Satuan</th>
                                                <th>Total</th>
                                            </tr>
                                        </thead>                                        <tbody>
                                            <?php 
                                            $grandTotal = 0;
                                            foreach ($order_items as $index => $item): 
                                                $quantity = isset($item['quantity']) ? (int)$item['quantity'] : 1;
                                                $unit_price = isset($item['unit_price']) ? (float)$item['unit_price'] : 0;
                                                $itemTotal = isset($item['total_price']) ? (float)$item['total_price'] : ($quantity * $unit_price);
                                                $grandTotal += $itemTotal;
                                            ?>
                                            <tr>
                                                <td><?php echo $index + 1; ?></td>
                                                <td><?php echo htmlspecialchars($item['product_name'] ?? 'Product ' . ($index + 1)); ?></td>
                                                <td><?php echo number_format($quantity); ?></td>
                                                <td>Rp <?php echo number_format($unit_price, 0, ',', '.'); ?></td>
                                                <td class="fw-bold">Rp <?php echo number_format($itemTotal, 0, ',', '.'); ?></td>
                                            </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                        <tfoot>
                                            <tr class="table-primary">
                                                <th colspan="4" class="text-end">Grand Total:</th>
                                                <th>Rp <?php echo number_format($grandTotal, 0, ',', '.'); ?></th>
                                            </tr>
                                        </tfoot>
                                    </table>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <!-- Customer Information & Quick Actions -->
                <div class="col-lg-4">
                    <!-- Customer Info -->
                    <div class="card">
                        <div class="card-header">
                            <h5 class="card-title mb-0">
                                <i class="bi bi-person me-2"></i>
                                Informasi Pelanggan
                            </h5>
                        </div>                        <div class="card-body">
                            <?php if (isset($order['customer_name']) && $order['customer_name']): ?>
                                <div class="info-row d-flex">
                                    <span class="info-label">Nama:</span>
                                    <span class="ms-auto fw-bold"><?php echo htmlspecialchars($order['customer_name']); ?></span>
                                </div>
                                <div class="info-row d-flex">
                                    <span class="info-label">Kode:</span>
                                    <span class="ms-auto"><?php echo htmlspecialchars($order['customer_code'] ?? '-'); ?></span>
                                </div>
                                <div class="info-row d-flex">
                                    <span class="info-label">Email:</span>
                                    <span class="ms-auto"><?php echo htmlspecialchars($order['customer_email'] ?? '-'); ?></span>
                                </div>
                                <div class="info-row d-flex">
                                    <span class="info-label">Telepon:</span>
                                    <span class="ms-auto"><?php echo htmlspecialchars($order['customer_phone'] ?? '-'); ?></span>
                                </div>
                                <?php if (isset($order['customer_company']) && $order['customer_company']): ?>
                                <div class="info-row d-flex">
                                    <span class="info-label">Perusahaan:</span>
                                    <span class="ms-auto"><?php echo htmlspecialchars($order['customer_company']); ?></span>
                                </div>
                                <?php endif; ?>
                                <?php if (isset($order['customer_address']) && $order['customer_address']): ?>
                                <div class="info-row">
                                    <span class="info-label d-block mb-2">Alamat:</span>
                                    <div class="text-muted">
                                        <?php echo nl2br(htmlspecialchars($order['customer_address'])); ?>
                                    </div>                                </div>
                                <?php endif; ?>
                            <?php else: ?>
                                <div class="text-center py-3">
                                    <i class="bi bi-person-x display-6 text-muted"></i>
                                    <p class="text-muted mt-2">Pelanggan tidak ditemukan</p>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>

                    <!-- Quick Status Update -->
                    <div class="card">
                        <div class="card-header">
                            <h5 class="card-title mb-0">
                                <i class="bi bi-lightning me-2"></i>
                                Update Status Cepat
                            </h5>
                        </div>
                        <div class="card-body">
                            <form method="POST">
                                <input type="hidden" name="action" value="update_status">                                <div class="mb-3">
                                    <label for="status" class="form-label">Status Baru:</label>
                                    <select name="status" id="status" class="form-select">
                                        <option value="pending" <?= strtolower($order['status']) == 'pending' ? 'selected' : '' ?>>Pending</option>
                                        <option value="confirmed" <?= strtolower($order['status']) == 'confirmed' ? 'selected' : '' ?>>Confirmed</option>
                                        <option value="processing" <?= strtolower($order['status']) == 'processing' ? 'selected' : '' ?>>Processing</option>
                                        <option value="shipped" <?= strtolower($order['status']) == 'shipped' ? 'selected' : '' ?>>Shipped</option>
                                        <option value="delivered" <?= strtolower($order['status']) == 'delivered' ? 'selected' : '' ?>>Delivered</option>
                                        <option value="completed" <?= strtolower($order['status']) == 'completed' ? 'selected' : '' ?>>Completed</option>
                                        <option value="cancelled" <?= strtolower($order['status']) == 'cancelled' ? 'selected' : '' ?>>Cancelled</option>
                                    </select>
                                </div>
                                <button type="submit" class="btn btn-primary w-100">
                                    <i class="bi bi-check-circle me-1"></i>
                                    Update Status
                                </button>
                            </form>
                        </div>
                    </div>

                    <!-- Order Actions -->
                    <div class="card">
                        <div class="card-header">
                            <h5 class="card-title mb-0">
                                <i class="bi bi-gear me-2"></i>
                                Aksi Order
                            </h5>
                        </div>
                        <div class="card-body">
                            <div class="d-grid gap-2">
                                <a href="edit-order.php?id=<?php echo $order_id; ?>" class="btn btn-warning">
                                    <i class="bi bi-pencil me-2"></i>
                                    Edit Order
                                </a>
                                <button class="btn btn-info" onclick="printOrder()">
                                    <i class="bi bi-printer me-2"></i>
                                    Print Order
                                </button>
                                <button class="btn btn-success" onclick="downloadPDF()">
                                    <i class="bi bi-file-pdf me-2"></i>
                                    Download PDF
                                </button>
                                <hr>
                                <button class="btn btn-danger" onclick="deleteOrder()" 
                                        <?= $order['status'] === 'Selesai' ? 'disabled title="Order selesai tidak dapat dihapus"' : '' ?>>
                                    <i class="bi bi-trash me-2"></i>
                                    Hapus Order
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <?php else: ?>
            <!-- No Order Found -->
            <div class="row">
                <div class="col-12">
                    <div class="card text-center">
                        <div class="card-body py-5">
                            <i class="bi bi-search display-1 text-muted"></i>
                            <h4 class="mt-3">Order Tidak Ditemukan</h4>
                            <p class="text-muted">Order dengan ID yang Anda cari tidak ditemukan atau telah dihapus.</p>
                            <a href="orders.php" class="btn btn-primary">
                                <i class="bi bi-arrow-left me-1"></i>
                                Kembali ke Daftar Order
                            </a>
                        </div>
                    </div>
                </div>
            </div>
            <?php endif; ?>
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

        // Print order function
        function printOrder() {
            window.print();
        }

        // Download PDF function (placeholder)
        function downloadPDF() {
            alert('Fitur download PDF akan segera tersedia.\nUntuk sementara, gunakan Print to PDF dari browser.');
        }

        // Delete order function
        function deleteOrder() {
            if (confirm('Apakah Anda yakin ingin menghapus order ini?\n\nTindakan ini tidak dapat dibatalkan!')) {
                // Implement delete functionality
                alert('Fitur hapus order akan diimplementasikan.\nUntuk keamanan, fitur ini memerlukan konfirmasi tambahan.');
            }
        }

        // Close sidebar when clicking outside on mobile
        document.addEventListener('click', function (event) {
            const sidebar = document.getElementById('sidebar');
            const toggleBtn = event.target.closest('.btn');

            if (window.innerWidth <= 768 && !sidebar.contains(event.target) && !toggleBtn) {
                sidebar.classList.remove('show');
            }
        });

        // Auto-hide alerts after 5 seconds
        setTimeout(function() {
            const alerts = document.querySelectorAll('.alert');
            alerts.forEach(function(alert) {
                const bsAlert = new bootstrap.Alert(alert);
                bsAlert.close();
            });
        }, 5000);
    </script>
</body>

</html>
