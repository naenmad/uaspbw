# Database Documentation - Order Management System

## Overview
Dokumentasi ini menjelaskan struktur database untuk sistem pencatatan order (Order Management System) yang dibuat untuk UAS Pemrograman Berbasis Web.

## Database Structure

### 1. Users Table
Tabel untuk menyimpan informasi pengguna sistem.

```sql
CREATE TABLE users (
    id INT PRIMARY KEY AUTO_INCREMENT,
    username VARCHAR(50) UNIQUE NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    full_name VARCHAR(100) NOT NULL,
    role ENUM('admin', 'user') DEFAULT 'user',
    ...
);
```

**Fields:**
- `id`: Primary key
- `username`: Username unik untuk login
- `email`: Email address unik
- `password`: Password yang sudah di-hash
- `full_name`: Nama lengkap pengguna
- `role`: Peran pengguna (admin/user)
- `avatar`: Path ke file avatar (optional)
- `phone`: Nomor telepon (optional)
- `address`: Alamat (optional)
- `is_active`: Status aktif pengguna
- `email_verified_at`: Timestamp verifikasi email
- `remember_token`: Token untuk remember me
- `created_at`, `updated_at`: Timestamps

### 2. Customers Table
Tabel untuk menyimpan data pelanggan.

```sql
CREATE TABLE customers (
    id INT PRIMARY KEY AUTO_INCREMENT,
    customer_code VARCHAR(20) UNIQUE NOT NULL,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100) NULL,
    phone VARCHAR(20) NULL,
    ...
);
```

**Fields:**
- `id`: Primary key
- `customer_code`: Kode unik pelanggan (CUST001, CUST002, dll)
- `name`: Nama pelanggan
- `email`: Email pelanggan
- `phone`: Nomor telepon
- `address`: Alamat lengkap
- `city`: Kota
- `postal_code`: Kode pos
- `company`: Nama perusahaan (jika pelanggan korporat)
- `customer_type`: Tipe pelanggan (individual/company)
- `status`: Status pelanggan (active/inactive)
- `notes`: Catatan tambahan
- `created_by`: ID user yang membuat
- `created_at`, `updated_at`: Timestamps

### 3. Products Table
Tabel untuk menyimpan data produk/item.

```sql
CREATE TABLE products (
    id INT PRIMARY KEY AUTO_INCREMENT,
    product_code VARCHAR(50) UNIQUE NOT NULL,
    name VARCHAR(100) NOT NULL,
    description TEXT NULL,
    ...
);
```

**Fields:**
- `id`: Primary key
- `product_code`: Kode unik produk (PRD001, PRD002, dll)
- `name`: Nama produk
- `description`: Deskripsi produk
- `category`: Kategori produk
- `unit`: Satuan (pcs, kg, liter, dll)
- `price`: Harga satuan
- `stock_quantity`: Jumlah stok
- `min_stock`: Minimum stok untuk alert
- `image`: Path ke gambar produk
- `status`: Status produk (active/inactive)
- `created_by`: ID user yang membuat
- `created_at`, `updated_at`: Timestamps

### 4. Orders Table
Tabel utama untuk menyimpan data order.

```sql
CREATE TABLE orders (
    id INT PRIMARY KEY AUTO_INCREMENT,
    order_number VARCHAR(50) UNIQUE NOT NULL,
    customer_id INT NOT NULL,
    order_date DATE NOT NULL,
    ...
);
```

**Fields:**
- `id`: Primary key
- `order_number`: Nomor order unik (ORD001, ORD002, dll)
- `customer_id`: Foreign key ke tabel customers
- `order_date`: Tanggal order
- `due_date`: Tanggal jatuh tempo
- `status`: Status order (pending, confirmed, processing, shipped, delivered, completed, cancelled)
- `payment_status`: Status pembayaran (unpaid, partial, paid, refunded)
- `payment_method`: Metode pembayaran (cash, transfer, credit_card, e_wallet)
- `subtotal`: Subtotal sebelum tax dan discount
- `tax_amount`: Jumlah pajak
- `discount_amount`: Jumlah diskon
- `shipping_cost`: Biaya pengiriman
- `total_amount`: Total akhir
- `notes`: Catatan order
- `shipping_address`: Alamat pengiriman
- `created_by`: ID user yang membuat
- `created_at`, `updated_at`: Timestamps

### 5. Order Items Table
Tabel untuk menyimpan detail item dalam setiap order.

```sql
CREATE TABLE order_items (
    id INT PRIMARY KEY AUTO_INCREMENT,
    order_id INT NOT NULL,
    product_id INT NOT NULL,
    product_name VARCHAR(100) NOT NULL,
    ...
);
```

**Fields:**
- `id`: Primary key
- `order_id`: Foreign key ke tabel orders
- `product_id`: Foreign key ke tabel products
- `product_name`: Nama produk (disimpan untuk data historis)
- `quantity`: Jumlah item
- `unit_price`: Harga satuan saat order
- `total_price`: Total harga item (quantity × unit_price)
- `notes`: Catatan khusus untuk item
- `created_at`, `updated_at`: Timestamps

### 6. Order Status History Table
Tabel untuk menyimpan riwayat perubahan status order.

```sql
CREATE TABLE order_status_history (
    id INT PRIMARY KEY AUTO_INCREMENT,
    order_id INT NOT NULL,
    old_status VARCHAR(20) NULL,
    new_status VARCHAR(20) NOT NULL,
    ...
);
```

**Fields:**
- `id`: Primary key
- `order_id`: Foreign key ke tabel orders
- `old_status`: Status sebelumnya
- `new_status`: Status baru
- `notes`: Catatan perubahan
- `changed_by`: ID user yang melakukan perubahan
- `changed_at`: Timestamp perubahan

### 7. Payments Table
Tabel untuk menyimpan data pembayaran.

```sql
CREATE TABLE payments (
    id INT PRIMARY KEY AUTO_INCREMENT,
    payment_code VARCHAR(50) UNIQUE NOT NULL,
    order_id INT NOT NULL,
    payment_date DATE NOT NULL,
    ...
);
```

**Fields:**
- `id`: Primary key
- `payment_code`: Kode unik pembayaran (PAY001, PAY002, dll)
- `order_id`: Foreign key ke tabel orders
- `payment_date`: Tanggal pembayaran
- `amount`: Jumlah pembayaran
- `payment_method`: Metode pembayaran
- `reference_number`: Nomor referensi pembayaran
- `notes`: Catatan pembayaran
- `status`: Status pembayaran (pending, confirmed, failed, cancelled)
- `created_by`: ID user yang mencatat
- `created_at`, `updated_at`: Timestamps

### 8. System Settings Table
Tabel untuk menyimpan pengaturan sistem.

```sql
CREATE TABLE system_settings (
    id INT PRIMARY KEY AUTO_INCREMENT,
    setting_key VARCHAR(100) UNIQUE NOT NULL,
    setting_value TEXT NULL,
    setting_type ENUM('string', 'number', 'boolean', 'json') DEFAULT 'string',
    ...
);
```

**Fields:**
- `id`: Primary key
- `setting_key`: Kunci pengaturan (unik)
- `setting_value`: Nilai pengaturan
- `setting_type`: Tipe data pengaturan
- `description`: Deskripsi pengaturan
- `created_at`, `updated_at`: Timestamps

### 9. User Sessions Table
Tabel untuk menyimpan sesi pengguna (optional).

### 10. Activity Logs Table
Tabel untuk menyimpan log aktivitas sistem.

```sql
CREATE TABLE activity_logs (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NULL,
    action VARCHAR(100) NOT NULL,
    model VARCHAR(50) NULL,
    model_id INT NULL,
    ...
);
```

## Views

### 1. order_summary
View untuk menampilkan ringkasan order dengan informasi pelanggan.

### 2. customer_stats
View untuk menampilkan statistik pelanggan.

### 3. product_sales_stats
View untuk menampilkan statistik penjualan produk.

### 4. monthly_sales
View untuk laporan penjualan bulanan.

## Stored Procedures

### 1. GetNextOrderNumber()
Procedure untuk generate nomor order berikutnya.

### 2. UpdateOrderStatus()
Procedure untuk update status order dengan logging.

## Triggers

### 1. update_order_total_*
Trigger untuk otomatis update total order saat order items berubah.

### 2. log_order_activity
Trigger untuk otomatis log aktivitas saat order dibuat.

## Indexes

Database dilengkapi dengan indexes untuk meningkatkan performa query:
- Index pada foreign keys
- Index pada field yang sering dicari (email, username, order_number, dll)
- Index pada field tanggal untuk reporting

## Default Data

Database akan terisi dengan data default:
- Admin user (username: admin, password: password)
- System settings dasar
- Sample customers, products, dan orders

## Security Considerations

1. **Password Hashing**: Password di-hash menggunakan bcrypt
2. **SQL Injection Prevention**: Menggunakan prepared statements
3. **Session Management**: Session handling yang aman
4. **Activity Logging**: Log semua aktivitas penting
5. **Role-based Access**: Sistem role untuk kontrol akses

## Usage Examples

### Create New Order
```php
// Generate order number
$orderNumber = generateOrderNumber();

// Insert order
$orderId = DB::insert('orders', [
    'order_number' => $orderNumber,
    'customer_id' => $customerId,
    'order_date' => date('Y-m-d'),
    'status' => 'pending',
    'created_by' => $_SESSION['user_id']
]);

// Insert order items
foreach ($items as $item) {
    DB::insert('order_items', [
        'order_id' => $orderId,
        'product_id' => $item['product_id'],
        'product_name' => $item['name'],
        'quantity' => $item['quantity'],
        'unit_price' => $item['price'],
        'total_price' => $item['quantity'] * $item['price']
    ]);
}
```

### Get Order Summary
```php
$orders = DB::select("
    SELECT * FROM order_summary 
    WHERE status = ? 
    ORDER BY order_date DESC
", ['pending']);
```

### Update Order Status
```php
$pdo->prepare("CALL UpdateOrderStatus(?, ?, ?, ?)")
   ->execute([$orderId, 'confirmed', 'Order confirmed', $_SESSION['user_id']]);
```

## Backup and Maintenance

1. **Regular Backups**: Setup automated database backups
2. **Log Rotation**: Rotate activity logs to prevent table bloat
3. **Index Maintenance**: Regular index optimization
4. **Statistics Update**: Update table statistics for query optimization

## Version History

- **v1.0.0**: Initial database schema
- Includes all core tables, views, procedures, and triggers
- Sample data for testing
- Complete indexing for performance

---

*Dokumentasi ini akan diupdate seiring dengan perkembangan sistem.*
