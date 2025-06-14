<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pelanggan - Sistem Pencatatan Order</title>

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

        .card {
            border: none;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            margin-bottom: 20px;
        }

        .customer-card {
            transition: transform 0.2s ease;
        }

        .customer-card:hover {
            transform: translateY(-2px);
        }

        .customer-avatar {
            width: 60px;
            height: 60px;
            border-radius: 50%;
            background: linear-gradient(45deg, #007bff, #0056b3);
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 24px;
            font-weight: bold;
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
                <a href="orders.php">
                    <i class="bi bi-list-ul me-2"></i>
                    Daftar Order
                </a>
            </li>
            <li>
                <a href="customers.php" class="active">
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
                        Admin User
                    </a>
                    <ul class="dropdown-menu dropdown-menu-end">
                        <li><a class="dropdown-item" href="settings.php"><i class="bi bi-person me-2"></i>Profile</a>
                        </li>
                        <li><a class="dropdown-item" href="settings.php"><i class="bi bi-gear me-2"></i>Settings</a>
                        </li>
                        <li>
                            <hr class="dropdown-divider">
                        </li>
                        <li><a class="dropdown-item" href="../auth/login.php"><i
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
                                    <h4 class="card-title mb-1">Kelola Pelanggan</h4>
                                    <p class="text-muted mb-0">Daftar semua pelanggan dan informasi mereka</p>
                                </div>
                                <button class="btn btn-primary" data-bs-toggle="modal"
                                    data-bs-target="#addCustomerModal">
                                    <i class="bi bi-person-plus me-1"></i>
                                    Tambah Pelanggan
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Statistics Cards -->
            <div class="row mb-4">
                <div class="col-xl-3 col-md-6">
                    <div class="card">
                        <div class="card-body text-center">
                            <div class="customer-avatar bg-primary mx-auto mb-3">
                                <i class="bi bi-people"></i>
                            </div>
                            <h3 class="mb-1">45</h3>
                            <p class="text-muted mb-0">Total Pelanggan</p>
                        </div>
                    </div>
                </div>
                <div class="col-xl-3 col-md-6">
                    <div class="card">
                        <div class="card-body text-center">
                            <div class="customer-avatar bg-success mx-auto mb-3">
                                <i class="bi bi-person-check"></i>
                            </div>
                            <h3 class="mb-1">38</h3>
                            <p class="text-muted mb-0">Pelanggan Aktif</p>
                        </div>
                    </div>
                </div>
                <div class="col-xl-3 col-md-6">
                    <div class="card">
                        <div class="card-body text-center">
                            <div class="customer-avatar bg-warning mx-auto mb-3">
                                <i class="bi bi-person-plus"></i>
                            </div>
                            <h3 class="mb-1">7</h3>
                            <p class="text-muted mb-0">Pelanggan Baru</p>
                        </div>
                    </div>
                </div>
                <div class="col-xl-3 col-md-6">
                    <div class="card">
                        <div class="card-body text-center">
                            <div class="customer-avatar bg-info mx-auto mb-3">
                                <i class="bi bi-cart3"></i>
                            </div>
                            <h3 class="mb-1">156</h3>
                            <p class="text-muted mb-0">Total Order</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Search and Filter -->
            <div class="row mb-4">
                <div class="col-12">
                    <div class="card">
                        <div class="card-body">
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <div class="search-box">
                                        <i class="bi bi-search"></i>
                                        <input type="text" class="form-control" id="searchInput"
                                            placeholder="Cari pelanggan...">
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <select class="form-select" id="statusFilter">
                                        <option value="">Semua Status</option>
                                        <option value="active">Aktif</option>
                                        <option value="inactive">Tidak Aktif</option>
                                    </select>
                                </div>
                                <div class="col-md-3">
                                    <div class="btn-group w-100">
                                        <button class="btn btn-outline-secondary" onclick="resetFilters()">
                                            <i class="bi bi-arrow-clockwise me-1"></i>
                                            Reset
                                        </button>
                                        <button class="btn btn-outline-success" onclick="exportCustomers()">
                                            <i class="bi bi-download me-1"></i>
                                            Export
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Customers Grid -->
            <div class="row" id="customersGrid">
                <!-- Customer Card 1 -->
                <div class="col-xl-4 col-md-6 mb-4">
                    <div class="card customer-card">
                        <div class="card-body">
                            <div class="d-flex align-items-center mb-3">
                                <div class="customer-avatar me-3">
                                    JD
                                </div>
                                <div class="flex-grow-1">
                                    <h5 class="mb-1">John Doe</h5>
                                    <p class="text-muted mb-0">john@email.com</p>
                                </div>
                                <div class="dropdown">
                                    <button class="btn btn-outline-secondary btn-sm" data-bs-toggle="dropdown">
                                        <i class="bi bi-three-dots-vertical"></i>
                                    </button>
                                    <ul class="dropdown-menu">
                                        <li><a class="dropdown-item" href="#" onclick="viewCustomer('1')"><i
                                                    class="bi bi-eye me-2"></i>Lihat Detail</a></li>
                                        <li><a class="dropdown-item" href="#" onclick="editCustomer('1')"><i
                                                    class="bi bi-pencil me-2"></i>Edit</a></li>
                                        <li>
                                            <hr class="dropdown-divider">
                                        </li>
                                        <li><a class="dropdown-item text-danger" href="#"
                                                onclick="deleteCustomer('1')"><i class="bi bi-trash me-2"></i>Hapus</a>
                                        </li>
                                    </ul>
                                </div>
                            </div>
                            <div class="row text-center">
                                <div class="col-4">
                                    <strong>5</strong><br>
                                    <small class="text-muted">Order</small>
                                </div>
                                <div class="col-4">
                                    <strong>Rp 750K</strong><br>
                                    <small class="text-muted">Total</small>
                                </div>
                                <div class="col-4">
                                    <span class="badge bg-success">Aktif</span><br>
                                    <small class="text-muted">Status</small>
                                </div>
                            </div>
                            <hr>
                            <div class="d-flex justify-content-between text-muted">
                                <small><i class="bi bi-telephone me-1"></i>+62 812 3456 7890</small>
                                <small><i class="bi bi-calendar me-1"></i>Jan 2025</small>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Customer Card 2 -->
                <div class="col-xl-4 col-md-6 mb-4">
                    <div class="card customer-card">
                        <div class="card-body">
                            <div class="d-flex align-items-center mb-3">
                                <div class="customer-avatar me-3"
                                    style="background: linear-gradient(45deg, #28a745, #1e7e34);">
                                    JS
                                </div>
                                <div class="flex-grow-1">
                                    <h5 class="mb-1">Jane Smith</h5>
                                    <p class="text-muted mb-0">jane@email.com</p>
                                </div>
                                <div class="dropdown">
                                    <button class="btn btn-outline-secondary btn-sm" data-bs-toggle="dropdown">
                                        <i class="bi bi-three-dots-vertical"></i>
                                    </button>
                                    <ul class="dropdown-menu">
                                        <li><a class="dropdown-item" href="#" onclick="viewCustomer('2')"><i
                                                    class="bi bi-eye me-2"></i>Lihat Detail</a></li>
                                        <li><a class="dropdown-item" href="#" onclick="editCustomer('2')"><i
                                                    class="bi bi-pencil me-2"></i>Edit</a></li>
                                        <li>
                                            <hr class="dropdown-divider">
                                        </li>
                                        <li><a class="dropdown-item text-danger" href="#"
                                                onclick="deleteCustomer('2')"><i class="bi bi-trash me-2"></i>Hapus</a>
                                        </li>
                                    </ul>
                                </div>
                            </div>
                            <div class="row text-center">
                                <div class="col-4">
                                    <strong>3</strong><br>
                                    <small class="text-muted">Order</small>
                                </div>
                                <div class="col-4">
                                    <strong>Rp 450K</strong><br>
                                    <small class="text-muted">Total</small>
                                </div>
                                <div class="col-4">
                                    <span class="badge bg-success">Aktif</span><br>
                                    <small class="text-muted">Status</small>
                                </div>
                            </div>
                            <hr>
                            <div class="d-flex justify-content-between text-muted">
                                <small><i class="bi bi-telephone me-1"></i>+62 813 5678 9012</small>
                                <small><i class="bi bi-calendar me-1"></i>Feb 2025</small>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Customer Card 3 -->
                <div class="col-xl-4 col-md-6 mb-4">
                    <div class="card customer-card">
                        <div class="card-body">
                            <div class="d-flex align-items-center mb-3">
                                <div class="customer-avatar me-3"
                                    style="background: linear-gradient(45deg, #ffc107, #e0a800);">
                                    MJ
                                </div>
                                <div class="flex-grow-1">
                                    <h5 class="mb-1">Michael Johnson</h5>
                                    <p class="text-muted mb-0">michael@email.com</p>
                                </div>
                                <div class="dropdown">
                                    <button class="btn btn-outline-secondary btn-sm" data-bs-toggle="dropdown">
                                        <i class="bi bi-three-dots-vertical"></i>
                                    </button>
                                    <ul class="dropdown-menu">
                                        <li><a class="dropdown-item" href="#" onclick="viewCustomer('3')"><i
                                                    class="bi bi-eye me-2"></i>Lihat Detail</a></li>
                                        <li><a class="dropdown-item" href="#" onclick="editCustomer('3')"><i
                                                    class="bi bi-pencil me-2"></i>Edit</a></li>
                                        <li>
                                            <hr class="dropdown-divider">
                                        </li>
                                        <li><a class="dropdown-item text-danger" href="#"
                                                onclick="deleteCustomer('3')"><i class="bi bi-trash me-2"></i>Hapus</a>
                                        </li>
                                    </ul>
                                </div>
                            </div>
                            <div class="row text-center">
                                <div class="col-4">
                                    <strong>1</strong><br>
                                    <small class="text-muted">Order</small>
                                </div>
                                <div class="col-4">
                                    <strong>Rp 75K</strong><br>
                                    <small class="text-muted">Total</small>
                                </div>
                                <div class="col-4">
                                    <span class="badge bg-warning">Baru</span><br>
                                    <small class="text-muted">Status</small>
                                </div>
                            </div>
                            <hr>
                            <div class="d-flex justify-content-between text-muted">
                                <small><i class="bi bi-telephone me-1"></i>+62 814 9012 3456</small>
                                <small><i class="bi bi-calendar me-1"></i>Jun 2025</small>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- More customer cards would go here -->
            </div>

            <!-- Pagination -->
            <div class="row">
                <div class="col-12">
                    <nav>
                        <ul class="pagination justify-content-center">
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

    <!-- Add Customer Modal -->
    <div class="modal fade" id="addCustomerModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Tambah Pelanggan Baru</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form id="addCustomerForm">
                        <div class="mb-3">
                            <label class="form-label">Nama Lengkap</label>
                            <input type="text" class="form-control" name="name" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Email</label>
                            <input type="email" class="form-control" name="email" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Nomor Telepon</label>
                            <input type="tel" class="form-control" name="phone" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Alamat</label>
                            <textarea class="form-control" name="address" rows="3"></textarea>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Status</label>
                            <select class="form-select" name="status">
                                <option value="active">Aktif</option>
                                <option value="inactive">Tidak Aktif</option>
                            </select>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="button" class="btn btn-primary" onclick="saveCustomer()">Simpan</button>
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

        // View customer details
        function viewCustomer(customerId) {
            alert(`Melihat detail pelanggan ID: ${customerId}`);
            // In real application, this would open a detail modal or page
        }

        // Edit customer
        function editCustomer(customerId) {
            alert(`Mengedit pelanggan ID: ${customerId}`);
            // In real application, this would open edit modal or page
        }

        // Delete customer
        function deleteCustomer(customerId) {
            if (confirm('Yakin ingin menghapus pelanggan ini?')) {
                alert(`Pelanggan ID: ${customerId} berhasil dihapus!`);
                // In real application, this would send delete request
            }
        }

        // Save new customer
        function saveCustomer() {
            const form = document.getElementById('addCustomerForm');
            const formData = new FormData(form);

            // Basic validation
            if (!formData.get('name') || !formData.get('email') || !formData.get('phone')) {
                alert('Mohon lengkapi semua field yang wajib diisi!');
                return;
            }

            alert('Pelanggan baru berhasil ditambahkan!');

            // Close modal and reset form
            const modal = bootstrap.Modal.getInstance(document.getElementById('addCustomerModal'));
            modal.hide();
            form.reset();

            // In real application, this would send data to server
        }

        // Reset filters
        function resetFilters() {
            document.getElementById('searchInput').value = '';
            document.getElementById('statusFilter').value = '';
            // In real application, this would refresh the grid
        }

        // Export customers
        function exportCustomers() {
            alert('Mengekspor data pelanggan...');
            // In real application, this would generate and download file
        }

        // Search functionality
        document.getElementById('searchInput').addEventListener('input', function () {
            const searchTerm = this.value.toLowerCase();
            const customerCards = document.querySelectorAll('.customer-card');

            customerCards.forEach(card => {
                const text = card.textContent.toLowerCase();
                const parentCol = card.closest('.col-xl-4');

                if (text.includes(searchTerm)) {
                    parentCol.style.display = '';
                } else {
                    parentCol.style.display = 'none';
                }
            });
        });
    </script>
</body>

</html>