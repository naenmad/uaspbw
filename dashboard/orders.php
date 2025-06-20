<?php
// Orders Page with Authentication
session_start();
require_once '../config/database.php';
require_once '../config/auth.php';

// Require user to be logged in
require_login();

// Get current user data
$current_user = get_logged_in_user();

// Helper function for status badge
function getStatusBadgeClass($status)
{
    switch (strtolower($status)) {
        case 'pending':
            return 'bg-warning';
        case 'confirmed':
            return 'bg-info';
        case 'processing':
            return 'bg-primary';
        case 'shipped':
            return 'bg-info';
        case 'delivered':
            return 'bg-success';
        case 'completed':
            return 'bg-success';
        case 'cancelled':
            return 'bg-danger';
        default:
            return 'bg-secondary';
    }
}

// Handle POST actions (delete, bulk actions)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    try {
        if ($action === 'delete_order') {
            $order_id = $_POST['order_id'];

            // Delete order items first
            $stmt = $pdo->prepare("DELETE FROM order_items WHERE order_id = ?");
            $stmt->execute([$order_id]);

            // Delete order
            $stmt = $pdo->prepare("DELETE FROM orders WHERE id = ?");
            $stmt->execute([$order_id]);

            $_SESSION['success_message'] = "Order berhasil dihapus.";

        } elseif ($action === 'bulk_delete') {
            $order_ids = json_decode($_POST['order_ids'], true);

            if (is_array($order_ids) && !empty($order_ids)) {
                $placeholders = str_repeat('?,', count($order_ids) - 1) . '?';

                // Delete order items first
                $stmt = $pdo->prepare("DELETE FROM order_items WHERE order_id IN ($placeholders)");
                $stmt->execute($order_ids);

                // Delete orders
                $stmt = $pdo->prepare("DELETE FROM orders WHERE id IN ($placeholders)");
                $stmt->execute($order_ids);

                $_SESSION['success_message'] = "Berhasil menghapus " . count($order_ids) . " order.";
            }

        } elseif ($action === 'bulk_update_status') {
            $order_ids = json_decode($_POST['order_ids'], true);
            $new_status = $_POST['new_status'];

            if (is_array($order_ids) && !empty($order_ids) && !empty($new_status)) {
                $placeholders = str_repeat('?,', count($order_ids) - 1) . '?';
                $params = array_merge([$new_status], $order_ids);

                $stmt = $pdo->prepare("UPDATE orders SET status = ?, updated_at = NOW() WHERE id IN ($placeholders)");
                $stmt->execute($params);

                $_SESSION['success_message'] = "Berhasil mengupdate status " . count($order_ids) . " order ke " . ucfirst($new_status) . ".";
            }
        }

    } catch (PDOException $e) {
        $_SESSION['error_message'] = "Error: " . $e->getMessage();
    }

    // Redirect to avoid form resubmission
    header("Location: orders.php");
    exit();
}

// Pagination and filtering
$page = isset($_GET['page']) ? (int) $_GET['page'] : 1;
$items_per_page = 10;
$offset = ($page - 1) * $items_per_page;

$search_term = $_GET['search'] ?? '';
$status_filter = $_GET['status'] ?? '';
$date_from = $_GET['date_from'] ?? '';
$date_to = $_GET['date_to'] ?? '';

// Build query
$base_query = "FROM orders o LEFT JOIN customers c ON o.customer_id = c.id WHERE 1=1";
$params = [];

if (!empty($search_term)) {
    $base_query .= " AND (o.order_number LIKE ? OR c.name LIKE ?)";
    $params[] = "%$search_term%";
    $params[] = "%$search_term%";
}

if (!empty($status_filter)) {
    $base_query .= " AND o.status = ?";
    $params[] = $status_filter;
}

if (!empty($date_from)) {
    $base_query .= " AND o.order_date >= ?";
    $params[] = $date_from;
}

if (!empty($date_to)) {
    $base_query .= " AND o.order_date <= ?";
    $params[] = $date_to;
}

// Get total count
$total_query = "SELECT COUNT(o.id) " . $base_query;
$stmt_total = $pdo->prepare($total_query);
$stmt_total->execute($params);
$total_orders = $stmt_total->fetchColumn();
$total_pages = ceil($total_orders / $items_per_page);

// Get orders data
$data_query = "SELECT o.*, c.name as customer_name, 
               (SELECT COUNT(*) FROM order_items oi WHERE oi.order_id = o.id) as total_items " .
    $base_query . " ORDER BY o.created_at DESC LIMIT ? OFFSET ?";

$stmt_data = $pdo->prepare($data_query);

// Bind parameters
$param_index = 1;
foreach ($params as $param) {
    $stmt_data->bindValue($param_index++, $param);
}
$stmt_data->bindValue($param_index++, $items_per_page, PDO::PARAM_INT);
$stmt_data->bindValue($param_index, $offset, PDO::PARAM_INT);

$stmt_data->execute();
$orders = $stmt_data->fetchAll(PDO::FETCH_ASSOC);

// Get statistics
$stats = [
    'total' => $pdo->query("SELECT COUNT(*) FROM orders")->fetchColumn(),
    'pending' => $pdo->query("SELECT COUNT(*) FROM orders WHERE status = 'pending'")->fetchColumn(),
    'processing' => $pdo->query("SELECT COUNT(*) FROM orders WHERE status = 'processing'")->fetchColumn(),
    'completed' => $pdo->query("SELECT COUNT(*) FROM orders WHERE status = 'completed'")->fetchColumn()
];
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daftar Order - Sistem Pencatatan Order</title>

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

        .table th {
            border-top: none;
            font-weight: 600;
            color: #495057;
        }

        .badge-status {
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 500;
        }

        .btn-action {
            padding: 5px 8px;
            font-size: 12px;
            border-radius: 5px;
            margin: 0 2px;
        }

        .search-box {
            position: relative;
        }

        .search-box input {
            padding-left: 40px;
        }

        .search-box i {
            position: absolute;
            left: 12px;
            top: 50%;
            transform: translateY(-50%);
            color: #6c757d;
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
    </nav> <!-- Main Content -->
    <div class="main-content">
        <div class="content-wrapper">
            <!-- Alert Messages -->
            <?php if (isset($_SESSION['success_message'])): ?>
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <i class="bi bi-check-circle me-2"></i>
                    <?= htmlspecialchars($_SESSION['success_message']) ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
                <?php unset($_SESSION['success_message']); ?>
            <?php endif; ?>

            <?php if (isset($_SESSION['error_message'])): ?>
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <i class="bi bi-exclamation-circle me-2"></i>
                    <?= htmlspecialchars($_SESSION['error_message']) ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
                <?php unset($_SESSION['error_message']); ?>
            <?php endif; ?>

            <!-- Page Header -->
            <div class="row mb-4">
                <div class="col-12">
                    <div class="card">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h4 class="card-title mb-1">Daftar Order</h4>
                                    <p class="text-muted mb-0">Kelola semua order pelanggan</p>
                                </div>
                                <a href="add-order.php" class="btn btn-primary">
                                    <i class="bi bi-plus-circle me-1"></i>
                                    Tambah Order
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Filters and Search -->
            <div class="row mb-4">
                <div class="col-12">
                    <div class="card">
                        <div class="card-body">
                            <div class="row g-3">
                                <div class="col-md-4">
                                    <div class="search-box">
                                        <i class="bi bi-search"></i>
                                        <input type="text" class="form-control" id="searchInput"
                                            placeholder="Cari order...">
                                    </div>
                                </div>
                                <div class="col-md-2">
                                    <select class="form-select" id="statusFilter">
                                        <option value="">Semua Status</option>
                                        <option value="pending">Pending</option>
                                        <option value="processing">Proses</option>
                                        <option value="completed">Selesai</option>
                                        <option value="cancelled">Dibatal</option>
                                    </select>
                                </div>
                                <div class="col-md-2">
                                    <input type="date" class="form-control" id="dateFrom" placeholder="Dari tanggal">
                                </div>
                                <div class="col-md-2">
                                    <input type="date" class="form-control" id="dateTo" placeholder="Sampai tanggal">
                                </div>
                                <div class="col-md-2">
                                    <button class="btn btn-outline-secondary w-100" onclick="resetFilters()">
                                        <i class="bi bi-arrow-clockwise me-1"></i>
                                        Reset
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Orders Table -->
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <h5 class="card-title mb-0">
                                <i class="bi bi-list-ul me-2"></i>
                                Daftar Order (25 order)
                            </h5>
                            <div class="btn-group" role="group">
                                <button class="btn btn-outline-success btn-sm" onclick="exportToExcel()">
                                    <i class="bi bi-file-earmark-excel me-1"></i>
                                    Excel
                                </button>
                                <button class="btn btn-outline-danger btn-sm" onclick="exportToPDF()">
                                    <i class="bi bi-file-earmark-pdf me-1"></i>
                                    PDF
                                </button>
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-hover">
                                    <thead>
                                        <tr>
                                            <th>
                                                <input type="checkbox" class="form-check-input" id="selectAll">
                                            </th>
                                            <th>ID Order</th>
                                            <th>Pelanggan</th>
                                            <th>Tanggal</th>
                                            <th>Total</th>
                                            <th>Status</th>
                                            <th>Aksi</th>
                                        </tr>
                                    </thead>
                                    <tbody id="ordersTableBody">
                                        <?php if (empty($orders)): ?>
                                            <tr>
                                                <td colspan="7" class="text-center">Tidak ada order ditemukan</td>
                                            </tr>
                                        <?php else: ?>
                                            <?php foreach ($orders as $order): ?>
                                                <tr>
                                                    <td><input type="checkbox" class="form-check-input order-checkbox"
                                                            value="<?= $order['id'] ?>"></td>
                                                    <td><strong>#<?= htmlspecialchars($order['order_number'] ?? 'ORD' . str_pad($order['id'], 3, '0', STR_PAD_LEFT)) ?></strong>
                                                    </td>
                                                    <td>
                                                        <div>
                                                            <strong><?= htmlspecialchars($order['customer_name'] ?? 'Unknown Customer') ?></strong><br>
                                                            <small class="text-muted"><?= $order['total_items'] ?? 0 ?>
                                                                item(s)</small>
                                                        </div>
                                                    </td>
                                                    <td><?= date('d M Y', strtotime($order['order_date'])) ?></td>
                                                    <td><strong>Rp
                                                            <?= number_format($order['total_amount'], 0, ',', '.') ?></strong>
                                                    </td>
                                                    <td>
                                                        <span
                                                            class="badge <?= getStatusBadgeClass($order['status']) ?> badge-status">
                                                            <?= ucfirst($order['status']) ?>
                                                        </span>
                                                    </td>
                                                    <td>
                                                        <button class="btn btn-info btn-action" title="Lihat Detail"
                                                            onclick="viewOrder('<?= htmlspecialchars($order['order_number'] ?? $order['id']) ?>')">
                                                            <i class="bi bi-eye"></i>
                                                        </button>
                                                        <button class="btn btn-warning btn-action" title="Edit"
                                                            onclick="editOrder('<?= htmlspecialchars($order['order_number'] ?? $order['id']) ?>')">
                                                            <i class="bi bi-pencil"></i>
                                                        </button>
                                                        <button class="btn btn-success btn-action" title="Update Status"
                                                            onclick="updateOrderStatus('<?= $order['id'] ?>', '<?= htmlspecialchars($order['status']) ?>')">
                                                            <i class="bi bi-arrow-repeat"></i>
                                                        </button>
                                                        <button class="btn btn-danger btn-action" title="Hapus"
                                                            onclick="deleteOrder('<?= $order['id'] ?>')">
                                                            <i class="bi bi-trash"></i>
                                                        </button>
                                                    </td>
                                                </tr>
                                            <?php endforeach; ?>
                                        <?php endif; ?>
                                    </tbody>
                                </table>
                            </div>

                            <!-- Bulk Actions -->
                            <div class="row mt-3">
                                <div class="col-md-6">
                                    <div class="btn-group" role="group">
                                        <button class="btn btn-outline-danger btn-sm" onclick="bulkDelete()" disabled
                                            id="bulkDeleteBtn">
                                            <i class="bi bi-trash me-1"></i>
                                            Hapus Terpilih
                                        </button>
                                        <button class="btn btn-outline-primary btn-sm" onclick="bulkUpdateStatus()"
                                            disabled id="bulkStatusBtn">
                                            <i class="bi bi-arrow-repeat me-1"></i>
                                            Update Status
                                        </button>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <!-- Pagination -->
                                    <nav>
                                        <ul class="pagination pagination-sm justify-content-end">
                                            <li class="page-item disabled">
                                                <a class="page-link" href="#">Previous</a>
                                            </li>
                                            <li class="page-item active">
                                                <a class="page-link" href="#">1</a>
                                            </li>
                                            <li class="page-item">
                                                <a class="page-link" href="#">2</a>
                                            </li>
                                            <li class="page-item">
                                                <a class="page-link" href="#">3</a>
                                            </li>
                                            <li class="page-item">
                                                <a class="page-link" href="#">Next</a>
                                            </li>
                                        </ul>
                                    </nav>
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

        // Select all checkboxes
        document.getElementById('selectAll').addEventListener('change', function () {
            const checkboxes = document.querySelectorAll('.order-checkbox');
            checkboxes.forEach(checkbox => {
                checkbox.checked = this.checked;
            });
            updateBulkButtons();
        });

        // Update bulk action buttons
        document.querySelectorAll('.order-checkbox').forEach(checkbox => {
            checkbox.addEventListener('change', updateBulkButtons);
        });

        function updateBulkButtons() {
            const checkedBoxes = document.querySelectorAll('.order-checkbox:checked');
            const bulkDeleteBtn = document.getElementById('bulkDeleteBtn');
            const bulkStatusBtn = document.getElementById('bulkStatusBtn');

            if (checkedBoxes.length > 0) {
                bulkDeleteBtn.disabled = false;
                bulkStatusBtn.disabled = false;
            } else {
                bulkDeleteBtn.disabled = true;
                bulkStatusBtn.disabled = true;
            }
        }        // View order details
        function viewOrder(orderId) {
            // Navigate to order detail page
            window.location.href = `order-detail.php?id=${orderId}`;
        }

        // Edit order
        function editOrder(orderId) {
            // Navigate to edit order page
            window.location.href = `edit-order.php?id=${orderId}`;
        }        // Delete order
        function deleteOrder(orderId) {
            if (confirm(`Yakin ingin menghapus order ${orderId}? Tindakan ini tidak dapat dibatalkan.`)) {
                // Clear unsaved changes flag
                clearUnsavedChanges();

                // Create form and submit for deletion
                const form = document.createElement('form');
                form.method = 'POST';
                form.action = 'orders.php';

                const actionInput = document.createElement('input');
                actionInput.type = 'hidden';
                actionInput.name = 'action';
                actionInput.value = 'delete_order';

                const orderIdInput = document.createElement('input');
                orderIdInput.type = 'hidden';
                orderIdInput.name = 'order_id';
                orderIdInput.value = orderId;

                form.appendChild(actionInput);
                form.appendChild(orderIdInput);
                document.body.appendChild(form);
                form.submit();
            }
        }        // Bulk delete
        function bulkDelete() {
            const checkedBoxes = document.querySelectorAll('.order-checkbox:checked');
            if (checkedBoxes.length > 0) {
                if (confirm(`Yakin ingin menghapus ${checkedBoxes.length} order terpilih? Tindakan ini tidak dapat dibatalkan.`)) {
                    const orderIds = Array.from(checkedBoxes).map(cb => cb.value);

                    // Clear unsaved changes flag
                    clearUnsavedChanges();

                    // Create form for bulk delete
                    const form = document.createElement('form');
                    form.method = 'POST';
                    form.action = 'orders.php';

                    const actionInput = document.createElement('input');
                    actionInput.type = 'hidden';
                    actionInput.name = 'action';
                    actionInput.value = 'bulk_delete';

                    const orderIdsInput = document.createElement('input');
                    orderIdsInput.type = 'hidden';
                    orderIdsInput.name = 'order_ids';
                    orderIdsInput.value = JSON.stringify(orderIds);

                    form.appendChild(actionInput);
                    form.appendChild(orderIdsInput);
                    document.body.appendChild(form);
                    form.submit();
                }
            } else {
                alert('Pilih minimal satu order untuk dihapus.');
            }
        }

        // Bulk update status
        function bulkUpdateStatus() {
            const checkedBoxes = document.querySelectorAll('.order-checkbox:checked');
            if (checkedBoxes.length > 0) {
                const statusSelect = document.createElement('select');
                statusSelect.className = 'form-select';
                statusSelect.innerHTML = `
                    <option value="">Pilih Status</option>
                    <option value="pending">Pending</option>
                    <option value="confirmed">Confirmed</option>
                    <option value="processing">Processing</option>
                    <option value="shipped">Shipped</option>
                    <option value="delivered">Delivered</option>
                    <option value="completed">Completed</option>
                    <option value="cancelled">Cancelled</option>
                `;

                // Create modal for status selection
                const modal = document.createElement('div');
                modal.className = 'modal fade';
                modal.innerHTML = `
                    <div class="modal-dialog">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title">Update Status</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                            </div>
                            <div class="modal-body">
                                <p>Pilih status baru untuk ${checkedBoxes.length} order terpilih:</p>
                                <div id="statusSelectContainer"></div>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                                <button type="button" class="btn btn-primary" onclick="submitBulkStatusUpdate()">Update Status</button>
                            </div>
                        </div>
                    </div>
                `;

                document.body.appendChild(modal);
                document.getElementById('statusSelectContainer').appendChild(statusSelect);

                const bootstrapModal = new bootstrap.Modal(modal);
                bootstrapModal.show();

                // Store the modal reference for cleanup
                window.currentStatusModal = {
                    modal: bootstrapModal,
                    element: modal,
                    statusSelect: statusSelect
                };
            } else {
                alert('Pilih minimal satu order untuk diupdate.');
            }
        }

        // Submit bulk status update
        function submitBulkStatusUpdate() {
            const newStatus = window.currentStatusModal.statusSelect.value;
            if (!newStatus) {
                alert('Pilih status yang valid.');
                return;
            }

            const checkedBoxes = document.querySelectorAll('.order-checkbox:checked');
            const orderIds = Array.from(checkedBoxes).map(cb => cb.value);

            // Create form for bulk status update
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = 'orders.php';

            const actionInput = document.createElement('input');
            actionInput.type = 'hidden';
            actionInput.name = 'action';
            actionInput.value = 'bulk_update_status';

            const orderIdsInput = document.createElement('input');
            orderIdsInput.type = 'hidden';
            orderIdsInput.name = 'order_ids';
            orderIdsInput.value = JSON.stringify(orderIds);

            const statusInput = document.createElement('input');
            statusInput.type = 'hidden';
            statusInput.name = 'new_status';
            statusInput.value = newStatus;

            form.appendChild(actionInput);
            form.appendChild(orderIdsInput);
            form.appendChild(statusInput);
            document.body.appendChild(form);

            // Hide modal and submit
            window.currentStatusModal.modal.hide();
            form.submit();
        }

        // Cleanup modal after use
        function cleanupStatusModal() {
            if (window.currentStatusModal) {
                document.body.removeChild(window.currentStatusModal.element);
                window.currentStatusModal = null;
            }
        }

        // Event listener untuk cleanup modal saat ditutup
        document.addEventListener('hidden.bs.modal', function (event) {
            if (event.target.classList.contains('modal')) {
                setTimeout(cleanupStatusModal, 100);
            }
        });

        // Update order status individually
        function updateOrderStatus(orderId, currentStatus) {
            const statusOptions = [
                { value: 'pending', label: 'Pending' },
                { value: 'confirmed', label: 'Confirmed' },
                { value: 'processing', label: 'Processing' },
                { value: 'shipped', label: 'Shipped' },
                { value: 'delivered', label: 'Delivered' },
                { value: 'completed', label: 'Completed' },
                { value: 'cancelled', label: 'Cancelled' }
            ];

            let optionsHtml = '';
            statusOptions.forEach(option => {
                const selected = option.value === currentStatus ? 'selected' : '';
                optionsHtml += `<option value="${option.value}" ${selected}>${option.label}</option>`;
            });

            const modal = document.createElement('div');
            modal.className = 'modal fade';
            modal.innerHTML = `
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title">Update Status Order</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>
                        <div class="modal-body">
                            <p>Update status untuk order #${orderId}:</p>
                            <select class="form-select" id="singleStatusSelect">
                                ${optionsHtml}
                            </select>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                            <button type="button" class="btn btn-primary" onclick="submitSingleStatusUpdate(${orderId})">Update Status</button>
                        </div>
                    </div>
                </div>
            `;

            document.body.appendChild(modal);
            const bootstrapModal = new bootstrap.Modal(modal);
            bootstrapModal.show();

            window.currentSingleStatusModal = {
                modal: bootstrapModal,
                element: modal
            };
        }

        // Submit single status update
        function submitSingleStatusUpdate(orderId) {
            const newStatus = document.getElementById('singleStatusSelect').value;

            const form = document.createElement('form');
            form.method = 'POST';
            form.action = 'orders.php';

            const actionInput = document.createElement('input');
            actionInput.type = 'hidden';
            actionInput.name = 'action';
            actionInput.value = 'bulk_update_status';

            const orderIdsInput = document.createElement('input');
            orderIdsInput.type = 'hidden';
            orderIdsInput.name = 'order_ids';
            orderIdsInput.value = JSON.stringify([orderId.toString()]);

            const statusInput = document.createElement('input');
            statusInput.type = 'hidden';
            statusInput.name = 'new_status';
            statusInput.value = newStatus;

            form.appendChild(actionInput);
            form.appendChild(orderIdsInput);
            form.appendChild(statusInput);
            document.body.appendChild(form);

            window.currentSingleStatusModal.modal.hide();
            form.submit();
        }

        // Reset filters
        function resetFilters() {
            document.getElementById('searchInput').value = '';
            document.getElementById('statusFilter').value = '';
            document.getElementById('dateFrom').value = '';
            document.getElementById('dateTo').value = '';
            // In real application, this would trigger table refresh
        }

        // Export to Excel
        function exportToExcel() {
            alert('Mengekspor data ke Excel...');
            // In real application, this would generate and download Excel file
        }

        // Export to PDF
        function exportToPDF() {
            alert('Mengekspor data ke PDF...');
            // In real application, this would generate and download PDF file
        }

        // Search functionality
        document.getElementById('searchInput').addEventListener('input', function () {
            const searchTerm = this.value.toLowerCase();
            const rows = document.querySelectorAll('#ordersTableBody tr');

            rows.forEach(row => {
                const text = row.textContent.toLowerCase();
                if (text.includes(searchTerm)) {
                    row.style.display = '';
                } else {
                    row.style.display = 'none';
                }
            });
        });

        // Status filter functionality
        document.getElementById('statusFilter').addEventListener('change', function () {
            const statusFilter = this.value.toLowerCase();
            const rows = document.querySelectorAll('#ordersTableBody tr');

            rows.forEach(row => {
                if (statusFilter === '') {
                    row.style.display = '';
                } else {
                    const statusBadge = row.querySelector('.badge-status');
                    const statusText = statusBadge.textContent.toLowerCase();

                    if (statusText.includes(statusFilter)) {
                        row.style.display = '';
                    } else {
                        row.style.display = 'none';
                    }
                }
            });
        });

        // Show loading state
        function showLoading(button) {
            const originalText = button.innerHTML;
            button.disabled = true;
            button.innerHTML = '<i class="bi bi-arrow-clockwise spin"></i>';
            return originalText;
        }

        // Hide loading state
        function hideLoading(button, originalText) {
            button.disabled = false;
            button.innerHTML = originalText;
        }

        // Add CSS for spinning animation
        const style = document.createElement('style');
        style.textContent = `
            .spin {
                animation: spin 1s linear infinite;
            }
            @keyframes spin {
                from { transform: rotate(0deg); }
                to { transform: rotate(360deg); }
            }
            .fade-in {
                animation: fadeIn 0.3s ease-in;
            }
            @keyframes fadeIn {
                from { opacity: 0; transform: translateY(-10px); }
                to { opacity: 1; transform: translateY(0); }
            }
        `;
        document.head.appendChild(style);

        // Auto-hide alerts after 5 seconds
        document.addEventListener('DOMContentLoaded', function () {
            const alerts = document.querySelectorAll('.alert');
            alerts.forEach(alert => {
                setTimeout(() => {
                    if (alert.parentNode) {
                        alert.classList.remove('show');
                        setTimeout(() => {
                            if (alert.parentNode) {
                                alert.parentNode.removeChild(alert);
                            }
                        }, 150);
                    }
                }, 5000);
            });
        });

        // Confirm before leaving page if there are unsaved changes
        let hasUnsavedChanges = false;

        // Mark as having unsaved changes when checkboxes are checked
        document.addEventListener('change', function (e) {
            if (e.target.classList.contains('order-checkbox')) {
                const checkedBoxes = document.querySelectorAll('.order-checkbox:checked');
                hasUnsavedChanges = checkedBoxes.length > 0;
            }
        });

        // Warn before leaving if there are checked items
        window.addEventListener('beforeunload', function (e) {
            if (hasUnsavedChanges) {
                e.preventDefault();
                e.returnValue = 'Ada order yang dipilih tapi belum diproses. Yakin ingin meninggalkan halaman?';
                return e.returnValue;
            }
        });

        // Clear unsaved changes flag when actions are performed
        function clearUnsavedChanges() {
            hasUnsavedChanges = false;
        }
    </script>
</body>

</html>