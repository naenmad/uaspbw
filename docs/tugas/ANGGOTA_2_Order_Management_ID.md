# 📦 TUGAS ANGGOTA 2: MANAJEMEN ORDER (CRUD)

**Nama:** [Isi nama anggota]  
**Tanggung Jawab:** Operasi CRUD Order, Sistem Manajemen Order  
**Deadline:** [Isi deadline]  

---

## 🎯 TUJUAN

Mengintegrasikan halaman manajemen order yang sudah ada (tampilan statis) dengan database dan membuat sistem CRUD lengkap untuk mengelola order.

---

## 📋 DAFTAR TUGAS

### **Fase 1: Pemahaman & Setup (Minggu 1)**
- [ ] Setup environment pengembangan (XAMPP/Laragon)
- [ ] Jalankan setup database: `http://localhost/uaspbw/setup/`
- [ ] Pelajari file yang ada: `dashboard/add-order.php`, `dashboard/orders.php`
- [ ] Pelajari skema database: tabel `orders`, `order_items`, `customers`
- [ ] Test halaman order saat ini (catatan: masih data statis)

### **Fase 2: Integrasi Tambah Order (Minggu 2)**
- [ ] **Update `dashboard/add-order.php`:**
  - [ ] Hubungkan ke database menggunakan `config/database.php`
  - [ ] Muat customer dari database untuk dropdown
  - [ ] Buat pemilihan produk dinamis (jika tabel produk ada)
  - [ ] Proses submit form untuk simpan order ke database
  - [ ] Hitung total (subtotal, pajak, grand total)
  - [ ] Validasi input form (field wajib, nilai numerik)
  - [ ] Tampilkan pesan sukses/error
  - [ ] Generate nomor order otomatis

### **Fase 3: Daftar Order & Manajemen (Minggu 2-3)**
- [ ] **Update `dashboard/orders.php`:**
  - [ ] Muat order dari database dengan pagination
  - [ ] Tampilkan data order dalam tabel (nomor_order, customer, tanggal, status, total)
  - [ ] Tambahkan fungsi pencarian (berdasarkan nomor order, nama customer)
  - [ ] Tambahkan filter berdasarkan status (Pending, Processing, Completed, Cancelled)
  - [ ] Tambahkan opsi sorting (tanggal, total, status)
  - [ ] Tambahkan tombol aksi (Lihat, Edit, Hapus, Ubah Status)

### **Fase 4: Detail Order & Edit (Minggu 3)**
- [ ] **Buat `dashboard/order-detail.php`:**
  - [ ] Tampilkan informasi order lengkap
  - [ ] Tampilkan detail customer
  - [ ] Daftar semua item order dengan kuantitas dan harga
  - [ ] Tampilkan total order dan kalkulasi
  - [ ] Tampilkan riwayat order/perubahan status
  - [ ] Tambahkan fungsi print/export

- [ ] **Buat `dashboard/order-edit.php`:**
  - [ ] Muat data order yang ada untuk editing
  - [ ] Izinkan modifikasi item order
  - [ ] Update informasi customer
  - [ ] Hitung ulang total saat item berubah
  - [ ] Simpan perubahan ke database
  - [ ] Validasi izin (hanya order pending yang bisa diedit)

### **Fase 5: Manajemen Status Order (Minggu 4)**
- [ ] **Alur status order:**
  - [ ] Fungsi ubah status (Pending → Processing → Completed)
  - [ ] Fungsi batal order
  - [ ] Tracking riwayat status
  - [ ] Notifikasi email saat status berubah (opsional)
  - [ ] Update status massal untuk multiple order

### **Fase 6: Fungsi Helper & Validasi (Minggu 4-5)**
- [ ] **Buat `includes/order-functions.php`:**
  - [ ] Fungsi `create_order($order_data, $items)`
  - [ ] Fungsi `get_order_by_id($order_id)`
  - [ ] Fungsi `update_order($order_id, $data)`
  - [ ] Fungsi `delete_order($order_id)`
  - [ ] Fungsi `get_orders_list($filters, $pagination)`
  - [ ] Fungsi `change_order_status($order_id, $status)`
  - [ ] Fungsi `calculate_order_total($items)`
  - [ ] Fungsi `generate_order_number()`

---

## 📁 FILE YANG DIKERJAKAN

### **File yang Ada (Modifikasi):**
- `dashboard/add-order.php` - Tambahkan integrasi database
- `dashboard/orders.php` - Tambahkan integrasi database dengan CRUD

### **File Baru (Buat):**
- `dashboard/order-detail.php` - Tampilan detail order
- `dashboard/order-edit.php` - Form edit order
- `includes/order-functions.php` - Fungsi helper
- `api/orders.php` - (Opsional) Endpoint AJAX

---

## 🗄️ TABEL DATABASE

### **Tabel Utama:**
```sql
-- Pelajari struktur tabel ini di database/schema.sql

orders (
    id, order_number, customer_id, 
    order_date, status, subtotal, tax_amount, 
    total_amount, notes, created_at, updated_at
)

order_items (
    id, order_id, product_name, 
    quantity, unit_price, total_price
)

customers (
    id, name, email, phone, address, 
    created_at, updated_at
)
```

---

## 💻 CONTOH KODE

### **Contoh Proses Tambah Order:**
```php
<?php
// dashboard/add-order.php - Tambahkan logika ini
require_once '../config/database.php';
require_once '../includes/order-functions.php';

if ($_POST) {
    $order_data = [
        'customer_id' => $_POST['customer_id'],
        'order_date' => $_POST['order_date'],
        'status' => 'Pending',
        'notes' => $_POST['notes']
    ];
    
    $items = [];
    for ($i = 0; $i < count($_POST['product_name']); $i++) {
        $items[] = [
            'product_name' => $_POST['product_name'][$i],
            'quantity' => $_POST['quantity'][$i],
            'unit_price' => $_POST['unit_price'][$i]
        ];
    }
    
    if (create_order($order_data, $items)) {
        $success = "Order berhasil dibuat!";
    } else {
        $error = "Gagal membuat order.";
    }
}

// Muat customer untuk dropdown
$customers_sql = "SELECT id, name FROM customers ORDER BY name";
$customers = $pdo->query($customers_sql)->fetchAll();
?>
```

### **Contoh Fungsi Order:**
```php
<?php
// includes/order-functions.php - Buat file ini
function create_order($order_data, $items) {
    global $pdo;
    
    try {
        $pdo->beginTransaction();
        
        // Hitung total
        $subtotal = 0;
        foreach ($items as $item) {
            $subtotal += $item['quantity'] * $item['unit_price'];
        }
        $tax_amount = $subtotal * 0.1; // Pajak 10%
        $total_amount = $subtotal + $tax_amount;
        
        // Generate nomor order
        $order_number = generate_order_number();
        
        // Insert order
        $order_sql = "INSERT INTO orders (order_number, customer_id, order_date, status, subtotal, tax_amount, total_amount, notes) 
                      VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
        $order_stmt = $pdo->prepare($order_sql);
        $order_stmt->execute([
            $order_number,
            $order_data['customer_id'],
            $order_data['order_date'],
            $order_data['status'],
            $subtotal,
            $tax_amount,
            $total_amount,
            $order_data['notes']
        ]);
        
        $order_id = $pdo->lastInsertId();
        
        // Insert item order
        $item_sql = "INSERT INTO order_items (order_id, product_name, quantity, unit_price, total_price) 
                     VALUES (?, ?, ?, ?, ?)";
        $item_stmt = $pdo->prepare($item_sql);
        
        foreach ($items as $item) {
            $total_price = $item['quantity'] * $item['unit_price'];
            $item_stmt->execute([
                $order_id,
                $item['product_name'],
                $item['quantity'],
                $item['unit_price'],
                $total_price
            ]);
        }
        
        $pdo->commit();
        return true;
        
    } catch (Exception $e) {
        $pdo->rollback();
        return false;
    }
}
?>
```

---

## 📊 ALUR STATUS ORDER

```
Pending → Processing → Completed
   ↓
Cancelled (bisa dibatalkan dari status manapun)
```

### **Aturan Status:**
- **Pending:** Order baru, bisa diedit/dibatalkan
- **Processing:** Order sedang diproses, hanya bisa diselesaikan/dibatalkan
- **Completed:** Order selesai, tidak bisa dimodifikasi
- **Cancelled:** Order dibatalkan, tidak bisa dimodifikasi

---

## 🧪 DAFTAR PENGUJIAN

- [ ] Buat order baru dengan multiple item
- [ ] Kalkulasi total order sudah benar
- [ ] Daftar order tampil dengan data yang benar
- [ ] Pencarian order berdasarkan nomor/customer berfungsi
- [ ] Filter order berdasarkan status berfungsi
- [ ] Fungsi edit order berfungsi
- [ ] Halaman detail order menampilkan informasi lengkap
- [ ] Ubah status order berfungsi
- [ ] Hapus order berfungsi (dengan konfirmasi)
- [ ] Pagination berfungsi untuk daftar order yang banyak

---

## 🚨 MASALAH UMUM & SOLUSI

### **Masalah:** Kalkulasi total order salah
**Solusi:** Periksa kalkulasi JavaScript cocok dengan kalkulasi PHP

### **Masalah:** Item order tidak tersimpan
**Solusi:** Pastikan penanganan array yang tepat dalam proses form

### **Masalah:** Error foreign key constraint
**Solusi:** Verifikasi customer_id ada sebelum membuat order

### **Masalah:** Status order tidak terupdate
**Solusi:** Periksa apakah nilai enum status cocok dengan database

---

## 📱 PERTIMBANGAN UI/UX

### **Form Tambah Order:**
- Baris item dinamis (tambah/hapus)
- Auto-kalkulasi total dengan JavaScript
- Pencarian/autocomplete customer
- Validasi form sebelum submit

### **Daftar Order:**
- Badge status dengan warna
- Aksi cepat (ubah status)
- Fungsi export
- Desain tabel responsive

### **Detail Order:**
- Layout yang ramah cetak
- Timeline order/riwayat
- Info kontak customer
- Tombol aksi berdasarkan status

---

## 🔗 TITIK INTEGRASI

**Dengan anggota tim lain:**
- **Anggota 1:** Autentikasi pengguna untuk kontrol akses order
- **Anggota 3:** Integrasi data customer
- **Anggota 4:** Data order untuk laporan dan analytics
- **Anggota 5:** Fungsi database dan endpoint API
- **Anggota 6:** Perbaikan UI dan fungsi JavaScript

---

## 📚 SUMBER REFERENSI

- [Transaksi PHP PDO](https://www.php.net/manual/en/pdo.transactions.php)
- [Foreign Keys MySQL](https://dev.mysql.com/doc/refman/8.0/en/create-table-foreign-keys.html)
- [Best Practices Validasi Form](https://developer.mozilla.org/en-US/docs/Learn/Forms/Form_validation)

---

## 📞 BANTUAN & DUKUNGAN

**Jika mengalami kesulitan:**
1. Cek skema database di `database/README.md`
2. Test dengan data sample yang sudah ada
3. Konsultasi dengan Anggota 5 (Integrasi Database)
4. Koordinasi dengan Anggota 3 untuk data customer

---

**Kriteria Sukses:**
✅ Buat order dengan multiple item berhasil  
✅ Daftar order menampilkan data dari database  
✅ Edit dan delete order berfungsi  
✅ Manajemen status order bekerja  
✅ Kalkulasi dan validasi akurat  
✅ UI responsive dan user-friendly  

**Semoga berhasil! 🚀**
