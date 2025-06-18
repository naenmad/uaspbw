<?php
// Customers Page with Authentication
session_start();
require_once '../config/database.php';
require_once '../config/auth.php';

// Require user to be logged in
require_login();

// Get current user data
$current_user = get_logged_in_user();

// Database connection object (assuming it's called $pdo from database.php)
if (!isset($pdo)) {
    // Fallback in case database.php does not instantiate $pdo
    try {
        $pdo = new PDO("mysql:host=localhost;dbname=uaspbw_db;charset=utf8mb4", 'root', '');
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        die("Error: Could not connect. " . $e->getMessage());
    }
}

// Function to generate the next customer code
function get_next_customer_code($pdo) {
    $prefix = 'CUST';
    $stmt = $pdo->prepare("SELECT customer_code FROM customers WHERE customer_code LIKE ? ORDER BY id DESC LIMIT 1");
    $stmt->execute([$prefix . '%']);
    $last_code = $stmt->fetchColumn();

    if ($last_code) {
        $number = (int) substr($last_code, strlen($prefix)) + 1;
    } else {
        $number = 1;
    }
    return $prefix . str_pad($number, 3, '0', STR_PAD_LEFT);
}

// Handle POST requests for Add/Edit/Delete
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    try {
        if ($action === 'add_customer') {
            $customer_code = get_next_customer_code($pdo);
            $stmt = $pdo->prepare(
                "INSERT INTO customers (customer_code, name, email, phone, address, city, company, customer_type, status, created_by) 
                 VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)"
            );
            $stmt->execute([
                $customer_code,
                $_POST['name'],
                $_POST['email'],
                $_POST['phone'],
                $_POST['address'],
                $_POST['city'],
                $_POST['company'],
                $_POST['customer_type'],
                $_POST['status'],
                $current_user['id']
            ]);
            $_SESSION['success_message'] = "Pelanggan baru berhasil ditambahkan.";
        }
        
        elseif ($action === 'edit_customer') {
            $stmt = $pdo->prepare(
                "UPDATE customers SET name=?, email=?, phone=?, address=?, city=?, company=?, customer_type=?, status=? 
                 WHERE id = ?"
            );
            $stmt->execute([
                $_POST['name'],
                $_POST['email'],
                $_POST['phone'],
                $_POST['address'],
                $_POST['city'],
                $_POST['company'],
                $_POST['customer_type'],
                $_POST['status'],
                $_POST['customer_id']
            ]);
            $_SESSION['success_message'] = "Data pelanggan berhasil diperbarui.";
        }

        elseif ($action === 'delete_customer') {
            $stmt = $pdo->prepare("DELETE FROM customers WHERE id = ?");
            $stmt->execute([$_POST['customer_id']]);
            $_SESSION['success_message'] = "Pelanggan berhasil dihapus.";
        }

    } catch (PDOException $e) {
        $_SESSION['error_message'] = "Terjadi kesalahan: " . $e->getMessage();
    }

    // Redirect to avoid form resubmission
    header("Location: customers.php");
    exit();
}

// Fetching statistics
$total_customers = $pdo->query("SELECT COUNT(*) FROM customers")->fetchColumn();
$active_customers = $pdo->query("SELECT COUNT(*) FROM customers WHERE status = 'active'")->fetchColumn();
$new_customers = $pdo->query("SELECT COUNT(*) FROM customers WHERE created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)")->fetchColumn();
$total_orders = $pdo->query("SELECT COUNT(*) FROM orders")->fetchColumn();

// Pagination, Search, and Filter Logic
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$items_per_page = 9; // 3x3 grid
$offset = ($page - 1) * $items_per_page;

$search_term = $_GET['search'] ?? '';
$status_filter = $_GET['status'] ?? '';

// CORRECTED QUERY: Start from 'customers' table and JOIN 'customer_stats'
$base_query = "FROM customers c LEFT JOIN customer_stats cs ON c.id = cs.id WHERE 1=1";
$params = [];

if (!empty($search_term)) {
    $base_query .= " AND (c.name LIKE ? OR c.email LIKE ? OR c.customer_code LIKE ?)";
    $params[] = "%$search_term%";
    $params[] = "%$search_term%";
    $params[] = "%$search_term%";
}

if (!empty($status_filter)) {
    $base_query .= " AND c.status = ?";
    $params[] = $status_filter;
}

// Get total count for pagination
$total_query = "SELECT COUNT(c.id) " . $base_query;
$stmt_total = $pdo->prepare($total_query);
$stmt_total->execute($params);
$total_filtered_customers = $stmt_total->fetchColumn();
$total_pages = ceil($total_filtered_customers / $items_per_page);

// Get data for the current page. Select all from customers (c.) and stats from the view (cs.)
$data_query = "SELECT c.*, cs.total_orders, cs.total_spent, cs.last_order_date " . $base_query . " ORDER BY c.created_at DESC LIMIT ? OFFSET ?";

// Re-build params for the final query with limit and offset
$final_params = $params;
$final_params[] = $items_per_page;
$final_params[] = $offset;

$stmt_data = $pdo->prepare($data_query);
// Use prepared statements for LIMIT and OFFSET by binding them as integers
$stmt_data->bindValue(count($final_params) - 1, $items_per_page, PDO::PARAM_INT);
$stmt_data->bindValue(count($final_params), $offset, PDO::PARAM_INT);
// Bind the other parameters
$p_idx = 1;
foreach ($params as $param) {
    $stmt_data->bindValue($p_idx++, $param);
}

$stmt_data->execute();
$customers = $stmt_data->fetchAll(PDO::FETCH_ASSOC);

?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pelanggan - Sistem Pencatatan Order</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-icons/1.11.0/font/bootstrap-icons.min.css" rel="stylesheet">
    <style>
        body { background-color: #f8f9fa; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; }
        .sidebar { min-height: 100vh; background: white; box-shadow: 2px 0 5px rgba(0,0,0,0.1); position: fixed; width: 250px; z-index: 1000; }
        .main-content { margin-left: 250px; padding: 20px; }
        .navbar-custom { background: white; box-shadow: 0 2px 5px rgba(0,0,0,0.1); margin-left: 250px; position: fixed; top: 0; right: 0; left: 250px; z-index: 999; width: calc(100% - 250px); }
        .content-wrapper { margin-top: 80px; }
        .sidebar-brand { padding: 20px; border-bottom: 1px solid #e9ecef; }
        .sidebar-nav { padding: 0; list-style: none; }
        .sidebar-nav a { display: block; padding: 15px 20px; color: #495057; text-decoration: none; transition: all 0.3s ease; }
        .sidebar-nav a:hover { background-color: #e9ecef; color: #007bff; }
        .sidebar-nav a.active { background-color: #007bff; color: white; }
        .sidebar-nav a.text-danger:hover { background-color: #dc3545; color: white !important; }
        .card { border: none; border-radius: 10px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); margin-bottom: 20px; }
        .customer-card { transition: transform 0.2s ease; }
        .customer-card:hover { transform: translateY(-2px); }
        .customer-avatar { width: 60px; height: 60px; border-radius: 50%; background: linear-gradient(45deg, #007bff, #0056b3); display: flex; align-items: center; justify-content: center; color: white; font-size: 24px; font-weight: bold; }
        .search-box { position: relative; }
        .search-box input { padding-left: 40px; }
        .search-box i { position: absolute; left: 12px; top: 50%; transform: translateY(-50%); color: #6c757d; }
        @media (max-width: 768px) {
            .sidebar { margin-left: -250px; transition: margin 0.3s ease; }
            .sidebar.show { margin-left: 0; }
            .main-content, .navbar-custom { margin-left: 0; width: 100%; }
        }
    </style>
</head>

<body>
    <div class="sidebar" id="sidebar">
        <div class="sidebar-brand"><h5 class="mb-0"><i class="bi bi-clipboard-data me-2"></i> Order System</h5></div>
        <ul class="sidebar-nav">
              <li><a href="index.php"><i class="bi bi-house me-2"></i>Dashboard</a></li>
              <li><a href="add-order.php"><i class="bi bi-plus-circle me-2"></i>Tambah Order</a></li>
              <li><a href="orders.php"><i class="bi bi-list-ul me-2"></i>Daftar Order</a></li>
              <li><a href="customers.php" class="active"><i class="bi bi-people me-2"></i>Pelanggan</a></li>
              <li><a href="reports.php"><i class="bi bi-graph-up me-2"></i>Laporan</a></li>
              <li><a href="settings.php"><i class="bi bi-gear me-2"></i>Pengaturan</a></li>
              <li><a href="../auth/logout.php" class="text-danger" onclick="return confirm('Yakin ingin logout?')"><i class="bi bi-box-arrow-right me-2"></i>Logout</a></li>
        </ul>
    </div>

    <nav class="navbar navbar-expand-lg navbar-light navbar-custom">
        <div class="container-fluid">
            <button class="btn btn-outline-secondary d-md-none" type="button" onclick="toggleSidebar()"><i class="bi bi-list"></i></button>
            <div class="ms-auto">
                <div class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">
                        <i class="bi bi-person-circle me-1"></i> <?php echo htmlspecialchars($current_user['full_name']); ?>
                    </a>
                    <ul class="dropdown-menu dropdown-menu-end">
                        <li><a class="dropdown-item" href="settings.php">Profile</a></li>
                        <li><hr class="dropdown-divider"></li>
                        <li><a class="dropdown-item" href="../auth/logout.php">Logout</a></li>
                    </ul>
                </div>
            </div>
        </div>
    </nav>

    <div class="main-content">
        <div class="content-wrapper">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h4 class="mb-0">Kelola Pelanggan</h4>
                <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#customerModal">
                    <i class="bi bi-person-plus me-1"></i> Tambah Pelanggan
                </button>
            </div>
            
            <?php if (isset($_SESSION['success_message'])): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <?php echo $_SESSION['success_message']; unset($_SESSION['success_message']); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
            <?php endif; ?>

            <div class="card mb-4">
                <div class="card-body">
                    <div class="row mb-4 text-center">
                        <div class="col-6 col-md-3"><h3 class="mb-1"><?php echo $total_customers; ?></h3><p class="text-muted mb-0">Total Pelanggan</p></div>
                        <div class="col-6 col-md-3"><h3 class="mb-1"><?php echo $active_customers; ?></h3><p class="text-muted mb-0">Pelanggan Aktif</p></div>
                        <div class="col-6 col-md-3 mt-3 mt-md-0"><h3 class="mb-1"><?php echo $new_customers; ?></h3><p class="text-muted mb-0">Baru (30 hari)</p></div>
                        <div class="col-6 col-md-3 mt-3 mt-md-0"><h3 class="mb-1"><?php echo $total_orders; ?></h3><p class="text-muted mb-0">Total Order</p></div>
                    </div>
                    <hr>
                    <form method="GET" action="customers.php">
                        <div class="row g-2 align-items-center">
                            <div class="col-md-6">
                                <div class="search-box">
                                    <i class="bi bi-search"></i>
                                    <input type="text" class="form-control" name="search" placeholder="Cari (nama, email, kode)..." value="<?php echo htmlspecialchars($search_term); ?>">
                                </div>
                            </div>
                            <div class="col-md-3">
                                <select class="form-select" name="status">
                                    <option value="">Semua Status</option>
                                    <option value="active" <?php if ($status_filter === 'active') echo 'selected'; ?>>Aktif</option>
                                    <option value="inactive" <?php if ($status_filter === 'inactive') echo 'selected'; ?>>Tidak Aktif</option>
                                </select>
                            </div>
                            <div class="col-md-3 d-flex">
                               <button type="submit" class="btn btn-primary w-100 me-2">Filter</button>
                               <a href="customers.php" class="btn btn-outline-secondary"><i class="bi bi-arrow-clockwise"></i></a>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            <div class="row">
                <?php if (empty($customers)): ?>
                    <div class="col-12"><div class="card"><div class="card-body text-center"><p class="mb-0">Tidak ada pelanggan yang ditemukan.</p></div></div></div>
                <?php else: ?>
                    <?php foreach ($customers as $customer):
                        $name_parts = explode(' ', trim($customer['name']));
                        $initials = strtoupper(count($name_parts) > 1 ? substr($name_parts[0], 0, 1) . substr(end($name_parts), 0, 1) : substr($name_parts[0], 0, 2));
                    ?>
                    <div class="col-xl-4 col-md-6 mb-4">
                        <div class="card customer-card h-100">
                            <div class="card-body d-flex flex-column">
                                <div class="d-flex align-items-center mb-3">
                                    <div class="customer-avatar me-3"><?php echo htmlspecialchars($initials); ?></div>
                                    <div class="flex-grow-1">
                                        <h5 class="mb-1"><?php echo htmlspecialchars($customer['name']); ?></h5>
                                        <p class="text-muted mb-0 small"><?php echo htmlspecialchars($customer['email']); ?></p>
                                    </div>
                                    <div class="dropdown">
                                        <button class="btn btn-light btn-sm" data-bs-toggle="dropdown"><i class="bi bi-three-dots-vertical"></i></button>
                                        <ul class="dropdown-menu dropdown-menu-end">
                                            <li><a class="dropdown-item" href="#" onclick='editCustomer(<?php echo json_encode($customer, JSON_HEX_APOS | JSON_HEX_QUOT); ?>)'><i class="bi bi-pencil me-2"></i>Edit</a></li>
                                            <li><hr class="dropdown-divider"></li>
                                            <li><a class="dropdown-item text-danger" href="#" onclick="deleteCustomer('<?php echo $customer['id']; ?>', '<?php echo htmlspecialchars($customer['name']); ?>')"><i class="bi bi-trash me-2"></i>Hapus</a></li>
                                        </ul>
                                    </div>
                                </div>
                                <div class="row text-center">
                                    <div class="col-4"><strong><?php echo $customer['total_orders'] ?? 0; ?></strong><br><small class="text-muted">Order</small></div>
                                    <div class="col-4"><strong>Rp<?php echo number_format(($customer['total_spent'] ?? 0)/1000, 0); ?>K</strong><br><small class="text-muted">Total</small></div>
                                    <div class="col-4">
                                        <span class="badge bg-<?php echo $customer['status'] === 'active' ? 'success' : 'secondary'; ?>"><?php echo ucfirst($customer['status']); ?></span><br>
                                        <small class="text-muted">Status</small>
                                    </div>
                                </div>
                                <hr>
                                <div class="d-flex justify-content-between text-muted mt-auto small">
                                    <span><i class="bi bi-telephone me-1"></i><?php echo htmlspecialchars($customer['phone'] ?? '-'); ?></span>
                                    <span><i class="bi bi-calendar-check me-1"></i><?php echo $customer['last_order_date'] ? date('M Y', strtotime($customer['last_order_date'])) : 'N/A'; ?></span>
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>

            <nav class="mt-3"><ul class="pagination justify-content-center">
                <li class="page-item <?php if($page <= 1){ echo 'disabled'; } ?>"><a class="page-link" href="?page=<?php echo $page-1; ?>&search=<?php echo urlencode($search_term); ?>&status=<?php echo urlencode($status_filter); ?>">Previous</a></li>
                <?php for($i=1; $i<=$total_pages; $i++): ?>
                <li class="page-item <?php if($i == $page){ echo 'active'; } ?>"><a class="page-link" href="?page=<?php echo $i; ?>&search=<?php echo urlencode($search_term); ?>&status=<?php echo urlencode($status_filter); ?>"><?php echo $i; ?></a></li>
                <?php endfor; ?>
                <li class="page-item <?php if($page >= $total_pages){ echo 'disabled'; } ?>"><a class="page-link" href="?page=<?php echo $page+1; ?>&search=<?php echo urlencode($search_term); ?>&status=<?php echo urlencode($status_filter); ?>">Next</a></li>
            </ul></nav>
        </div>
    </div>
    
    <div class="modal fade" id="customerModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <form id="customerForm" method="POST" action="customers.php">
                <div class="modal-content">
                    <input type="hidden" name="action" id="formAction" value="add_customer">
                    <input type="hidden" name="customer_id" id="customerId">
                    <div class="modal-header">
                        <h5 class="modal-title" id="modalTitle">Tambah Pelanggan Baru</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-6 mb-3"><label class="form-label">Nama Lengkap</label><input type="text" class="form-control" name="name" id="customerName" required></div>
                            <div class="col-md-6 mb-3"><label class="form-label">Email</label><input type="email" class="form-control" name="email" id="customerEmail" required></div>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3"><label class="form-label">Nomor Telepon</label><input type="tel" class="form-control" name="phone" id="customerPhone" required></div>
                            <div class="col-md-6 mb-3"><label class="form-label">Perusahaan</label><input type="text" class="form-control" name="company" id="customerCompany"></div>
                        </div>
                         <div class="mb-3"><label class="form-label">Alamat</label><textarea class="form-control" name="address" id="customerAddress" rows="2"></textarea></div>
                        <div class="row">
                            <div class="col-md-4 mb-3"><label class="form-label">Kota</label><input type="text" class="form-control" name="city" id="customerCity"></div>
                            <div class="col-md-4 mb-3"><label class="form-label">Tipe</label><select class="form-select" name="customer_type" id="customerType"><option value="individual">Individual</option><option value="company">Perusahaan</option></select></div>
                            <div class="col-md-4 mb-3"><label class="form-label">Status</label><select class="form-select" name="status" id="customerStatus"><option value="active">Aktif</option><option value="inactive">Tidak Aktif</option></select></div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-primary" id="saveButton">Simpan</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
    
    <form id="deleteCustomerForm" method="POST" action="customers.php" class="d-none">
        <input type="hidden" name="action" value="delete_customer">
        <input type="hidden" name="customer_id" id="deleteCustomerId">
    </form>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // --- KODE JAVASCRIPT YANG DIPERBAIKI ---

        const customerModalEl = document.getElementById('customerModal');
        const customerModal = new bootstrap.Modal(customerModalEl);
        const customerForm = document.getElementById('customerForm');

        // Fungsi ini sekarang HANYA untuk membuka modal dalam mode "Add"
        function openAddModal() {
            customerForm.reset();
            document.getElementById('modalTitle').textContent = 'Tambah Pelanggan Baru';
            document.getElementById('formAction').value = 'add_customer';
            document.getElementById('customerId').value = '';
            document.getElementById('saveButton').textContent = 'Simpan';
            customerModal.show();
        }
        
        // Modifikasi tombol "Tambah Pelanggan" secara dinamis
        // untuk memanggil fungsi openAddModal()
        const addButton = document.querySelector('button[data-bs-target="#customerModal"]');
        if (addButton) {
            addButton.setAttribute('onclick', 'openAddModal()');
            addButton.removeAttribute('data-bs-toggle');
            addButton.removeAttribute('data-bs-target');
        }

        function toggleSidebar() { 
            document.getElementById('sidebar').classList.toggle('show'); 
        }

        function editCustomer(customerData) {
            customerForm.reset();
            
            document.getElementById('modalTitle').textContent = 'Edit Data Pelanggan';
            document.getElementById('formAction').value = 'edit_customer';
            document.getElementById('saveButton').textContent = 'Perbarui';
            
            // Mengisi form dengan data customer yang dipilih
            document.getElementById('customerId').value = customerData.id;
            document.getElementById('customerName').value = customerData.name;
            document.getElementById('customerEmail').value = customerData.email;
            document.getElementById('customerPhone').value = customerData.phone;
            document.getElementById('customerCompany').value = customerData.company;
            document.getElementById('customerAddress').value = customerData.address;
            document.getElementById('customerCity').value = customerData.city;
            document.getElementById('customerType').value = customerData.customer_type;
            document.getElementById('customerStatus').value = customerData.status;

            customerModal.show();
        }

        function deleteCustomer(id, name) {
    if (confirm(`Yakin ingin menghapus pelanggan "${name}"? Tindakan ini tidak bisa dibatalkan.`)) {
        document.getElementById('deleteCustomerId').value = id;
        document.getElementById('deleteCustomerForm').submit();
    }
}
    </script>
</body>
</html>