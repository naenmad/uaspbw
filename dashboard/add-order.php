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

        .card {
            border: none;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            margin-bottom: 20px;
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

        .btn-custom {
            border-radius: 8px;
            padding: 10px 20px;
            font-weight: 500;
        }

        .item-row {
            background: #f8f9fa;
            border-radius: 8px;
            padding: 15px;
            margin-bottom: 10px;
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
                                    <h4 class="card-title mb-1">Tambah Order Baru</h4>
                                    <p class="text-muted mb-0">Buat order baru untuk pelanggan</p>
                                </div>
                                <a href="orders.php" class="btn btn-outline-secondary">
                                    <i class="bi bi-list-ul me-1"></i>
                                    Lihat Semua Order
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Order Form -->
            <form id="orderForm">
                <div class="row">
                    <!-- Customer Information -->
                    <div class="col-lg-6">
                        <div class="card">
                            <div class="card-header">
                                <h5 class="card-title mb-0">
                                    <i class="bi bi-person me-2"></i>
                                    Informasi Pelanggan
                                </h5>
                            </div>
                            <div class="card-body">
                                <div class="mb-3">
                                    <label class="form-label">Pilih Pelanggan</label>
                                    <select class="form-select" id="customer_id" name="customer_id" required>
                                        <option value="">-- Pilih Pelanggan --</option>
                                        <option value="1">John Doe - john@email.com</option>
                                        <option value="2">Jane Smith - jane@email.com</option>
                                        <option value="3">Michael Johnson - michael@email.com</option>
                                        <option value="new">+ Tambah Pelanggan Baru</option>
                                    </select>
                                </div>

                                <!-- New Customer Form (Hidden by default) -->
                                <div id="newCustomerForm" style="display: none;">
                                    <hr>
                                    <h6>Data Pelanggan Baru</h6>
                                    <div class="mb-3">
                                        <label class="form-label">Nama Lengkap</label>
                                        <input type="text" class="form-control" name="customer_name"
                                            placeholder="Masukkan nama lengkap">
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label">Email</label>
                                        <input type="email" class="form-control" name="customer_email"
                                            placeholder="Masukkan email">
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label">Nomor Telepon</label>
                                        <input type="tel" class="form-control" name="customer_phone"
                                            placeholder="Masukkan nomor telepon">
                                    </div>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label">Alamat Pengiriman</label>
                                    <textarea class="form-control" name="shipping_address" rows="3"
                                        placeholder="Masukkan alamat lengkap pengiriman" required></textarea>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Order Information -->
                    <div class="col-lg-6">
                        <div class="card">
                            <div class="card-header">
                                <h5 class="card-title mb-0">
                                    <i class="bi bi-clipboard-data me-2"></i>
                                    Informasi Order
                                </h5>
                            </div>
                            <div class="card-body">
                                <div class="mb-3">
                                    <label class="form-label">Tanggal Order</label>
                                    <input type="date" class="form-control" name="order_date" value="2025-06-14"
                                        required>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label">Status Order</label>
                                    <select class="form-select" name="order_status" required>
                                        <option value="pending">Pending</option>
                                        <option value="processing">Proses</option>
                                        <option value="completed">Selesai</option>
                                    </select>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label">Catatan</label>
                                    <textarea class="form-control" name="notes" rows="3"
                                        placeholder="Catatan tambahan (opsional)"></textarea>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Order Items -->
                <div class="row mt-4">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-header d-flex justify-content-between align-items-center">
                                <h5 class="card-title mb-0">
                                    <i class="bi bi-cart me-2"></i>
                                    Item Order
                                </h5>
                                <button type="button" class="btn btn-success btn-sm" onclick="addOrderItem()">
                                    <i class="bi bi-plus me-1"></i>
                                    Tambah Item
                                </button>
                            </div>
                            <div class="card-body">
                                <div id="orderItems">
                                    <!-- Item akan ditambahkan di sini -->
                                </div>

                                <!-- Total Section -->
                                <div class="row mt-4">
                                    <div class="col-md-6 ms-auto">
                                        <div class="table-responsive">
                                            <table class="table">
                                                <tr>
                                                    <td><strong>Subtotal:</strong></td>
                                                    <td class="text-end"><strong id="subtotal">Rp 0</strong></td>
                                                </tr>
                                                <tr>
                                                    <td>Diskon:</td>
                                                    <td class="text-end">
                                                        <input type="number"
                                                            class="form-control form-control-sm text-end"
                                                            name="discount" id="discount" value="0" min="0"
                                                            onchange="calculateTotal()">
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <td>Ongkir:</td>
                                                    <td class="text-end">
                                                        <input type="number"
                                                            class="form-control form-control-sm text-end"
                                                            name="shipping_cost" id="shipping_cost" value="0" min="0"
                                                            onchange="calculateTotal()">
                                                    </td>
                                                </tr>
                                                <tr class="table-active">
                                                    <td><strong>Total:</strong></td>
                                                    <td class="text-end"><strong id="total">Rp 0</strong></td>
                                                </tr>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Action Buttons -->
                <div class="row mt-4">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-body text-center">
                                <button type="submit" class="btn btn-primary btn-custom me-2">
                                    <i class="bi bi-check-circle me-1"></i>
                                    Simpan Order
                                </button>
                                <button type="button" class="btn btn-secondary btn-custom me-2" onclick="resetForm()">
                                    <i class="bi bi-arrow-clockwise me-1"></i>
                                    Reset Form
                                </button>
                                <a href="orders.php" class="btn btn-outline-secondary btn-custom">
                                    <i class="bi bi-x-circle me-1"></i>
                                    Batal
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

    <script>
        let itemCounter = 0;

        // Toggle sidebar for mobile
        function toggleSidebar() {
            const sidebar = document.getElementById('sidebar');
            sidebar.classList.toggle('show');
        }

        // Show/hide new customer form
        document.getElementById('customer_id').addEventListener('change', function () {
            const newCustomerForm = document.getElementById('newCustomerForm');
            if (this.value === 'new') {
                newCustomerForm.style.display = 'block';
            } else {
                newCustomerForm.style.display = 'none';
            }
        });

        // Add new order item
        function addOrderItem() {
            itemCounter++;
            const itemsContainer = document.getElementById('orderItems');
            const itemHtml = `
                <div class="item-row" id="item-${itemCounter}">
                    <div class="row align-items-end">
                        <div class="col-md-4">
                            <label class="form-label">Nama Item</label>
                            <input type="text" class="form-control" name="item_name[]" placeholder="Nama produk/layanan" required>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">Qty</label>
                            <input type="number" class="form-control" name="item_qty[]" value="1" min="1" onchange="calculateItemTotal(${itemCounter})" required>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">Harga</label>
                            <input type="number" class="form-control" name="item_price[]" placeholder="0" min="0" onchange="calculateItemTotal(${itemCounter})" required>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">Total</label>
                            <input type="text" class="form-control" id="item-total-${itemCounter}" readonly>
                        </div>
                        <div class="col-md-2">
                            <button type="button" class="btn btn-danger btn-sm" onclick="removeOrderItem(${itemCounter})">
                                <i class="bi bi-trash"></i>
                            </button>
                        </div>
                    </div>
                </div>
            `;
            itemsContainer.insertAdjacentHTML('beforeend', itemHtml);
        }

        // Remove order item
        function removeOrderItem(itemId) {
            document.getElementById(`item-${itemId}`).remove();
            calculateTotal();
        }

        // Calculate item total
        function calculateItemTotal(itemId) {
            const itemRow = document.getElementById(`item-${itemId}`);
            const qty = itemRow.querySelector('input[name="item_qty[]"]').value || 0;
            const price = itemRow.querySelector('input[name="item_price[]"]').value || 0;
            const total = qty * price;

            itemRow.querySelector(`#item-total-${itemId}`).value = formatCurrency(total);
            calculateTotal();
        }

        // Calculate grand total
        function calculateTotal() {
            let subtotal = 0;

            // Calculate subtotal from all items
            document.querySelectorAll('.item-row').forEach(row => {
                const qty = row.querySelector('input[name="item_qty[]"]').value || 0;
                const price = row.querySelector('input[name="item_price[]"]').value || 0;
                subtotal += qty * price;
            });

            const discount = parseFloat(document.getElementById('discount').value) || 0;
            const shippingCost = parseFloat(document.getElementById('shipping_cost').value) || 0;
            const total = subtotal - discount + shippingCost;

            document.getElementById('subtotal').textContent = formatCurrency(subtotal);
            document.getElementById('total').textContent = formatCurrency(total);
        }

        // Format currency
        function formatCurrency(amount) {
            return 'Rp ' + new Intl.NumberFormat('id-ID').format(amount);
        }

        // Reset form
        function resetForm() {
            if (confirm('Yakin ingin mereset form? Semua data akan hilang.')) {
                document.getElementById('orderForm').reset();
                document.getElementById('orderItems').innerHTML = '';
                document.getElementById('newCustomerForm').style.display = 'none';
                calculateTotal();
            }
        }

        // Form submission
        document.getElementById('orderForm').addEventListener('submit', function (e) {
            e.preventDefault();

            // Validate items
            const items = document.querySelectorAll('.item-row');
            if (items.length === 0) {
                alert('Tambahkan minimal satu item order!');
                return;
            }

            // Show success message
            alert('Order berhasil disimpan!');

            // In real application, you would send data to server here
            console.log('Order data would be sent to server');
        });

        // Add first item on page load
        document.addEventListener('DOMContentLoaded', function () {
            addOrderItem();
        });
    </script>
</body>

</html>