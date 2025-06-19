# Fix untuk Error "Field 'product_id' doesn't have a default value"

## Masalah
Error ini terjadi karena schema database awal mengharuskan field `product_id` di tabel `order_items`, tapi aplikasi saat ini menggunakan input manual product name tanpa referensi ke tabel products.

## Solusi

### 1. Jalankan Update Schema
Buka MySQL/phpMyAdmin dan jalankan script berikut:

```sql
USE uaspbw_db;

-- Hapus foreign key constraint terlebih dahulu
ALTER TABLE order_items DROP FOREIGN KEY order_items_ibfk_2;

-- Ubah product_id menjadi nullable
ALTER TABLE order_items MODIFY COLUMN product_id INT NULL;

-- Tambahkan kembali foreign key constraint
ALTER TABLE order_items ADD CONSTRAINT order_items_ibfk_2 
FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE RESTRICT;
```

### 2. Atau jalankan file update_schema.sql
File `database/update_schema.sql` sudah dibuat dengan script yang sama.

### 3. Verifikasi Perubahan
Jalankan query berikut untuk memastikan perubahan berhasil:
```sql
DESCRIBE order_items;
```

Field `product_id` seharusnya sekarang menunjukkan `YES` pada kolom `Null`.

## Perubahan yang Dibuat
1. **Schema Database**: `product_id` di `order_items` sekarang nullable
2. **add-order.php**: Insert dengan `product_id = NULL` untuk manual entries
3. **edit-order.php**: Insert dengan `product_id = NULL` untuk manual entries
4. **Validasi**: Ditambah validasi input yang lebih baik

## Catatan
- Sistem sekarang mendukung dua cara: manual product entry (product_id = NULL) dan referensi ke products table
- Untuk implementasi masa depan, bisa ditambahkan dropdown product dari tabel products
- Data historis tetap terjaga dengan menyimpan product_name
