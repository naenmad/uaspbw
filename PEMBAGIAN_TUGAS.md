# PEMBAGIAN TUGAS PENGEMBANGAN SISTEM PENCATATAN ORDER
**Proyek:** Sistem Pencatatan Order berbasis PHP  
**Kelompok:** 6 Anggota  
**Framework:** PHP Native + MySQL  

---

## 📋 RINGKASAN PROJECT

Sistem pencatatan order dengan fitur:
- ✅ **Landing Page & Authentication** (Sudah dibuat - tampilan statis)
- ✅ **Dashboard Layout & Navigation** (Sudah dibuat - tampilan statis)
- ✅ **Database Schema & Setup** (Sudah dibuat - siap digunakan)
- ❌ **Integrasi Database** (Belum - masih data statis)
- ❌ **CRUD Operations** (Belum - perlu implementasi)
- ❌ **Security & Validation** (Belum - perlu implementasi)

---

## 👥 PEMBAGIAN TUGAS KELOMPOK

### 🔷 **ANGGOTA 1: Authentication & User Management**
**Tanggung Jawab:**
- ✅ Memahami dan melengkapi sistem login/register yang sudah ada
- 🔧 Mengintegrasikan halaman login dengan database (validasi user)
- 🔧 Implementasi session management dan logout
- 🔧 Membuat sistem role-based access (admin/user)
- 🔧 Password hashing dan security measures
- 🔧 User profile management di halaman settings

**File yang dikerjakan:**
- `auth/login.php` - Integrasi database login
- `auth/register.php` - Integrasi database register
- `dashboard/settings.php` - User profile & password change
- `config/auth.php` - Helper functions untuk authentication

**Estimasi:** 2-3 minggu

---

### 🔷 **ANGGOTA 2: Order Management (CRUD)**
**Tanggung Jaweb:**
- 🔧 Mengintegrasikan halaman add-order dengan database
- 🔧 Implementasi CRUD lengkap untuk orders (Create, Read, Update, Delete)
- 🔧 Form validation dan error handling
- 🔧 Status order management (Pending, Processing, Completed, Cancelled)
- 🔧 Order detail view dan edit functionality

**File yang dikerjakan:**
- `dashboard/add-order.php` - Form tambah order + simpan ke DB
- `dashboard/orders.php` - List, edit, delete orders dari DB
- `dashboard/order-detail.php` - (Buat baru) Detail order
- `includes/order-functions.php` - (Buat baru) Helper functions

**Estimasi:** 2-3 minggu

---

### 🔷 **ANGGOTA 3: Customer Management**
**Tanggung Jawab:**
- 🔧 Mengintegrasikan halaman customers dengan database
- 🔧 CRUD customer (tambah, edit, hapus, view customer)
- 🔧 Customer search dan filtering
- 🔧 Customer order history integration
- 🔧 Export customer data (Excel/PDF)

**File yang dikerjakan:**
- `dashboard/customers.php` - List customers dari DB
- `dashboard/customer-add.php` - (Buat baru) Form tambah customer
- `dashboard/customer-edit.php` - (Buat baru) Edit customer
- `dashboard/customer-detail.php` - (Buat baru) Detail & history customer
- `includes/customer-functions.php` - (Buat baru) Helper functions

**Estimasi:** 2-3 minggu

---

### 🔷 **ANGGOTA 4: Reports & Analytics**
**Tanggung Jawab:**
- 🔧 Mengintegrasikan halaman reports dengan database
- 🔧 Implementasi berbagai jenis laporan (harian, bulanan, tahunan)
- 🔧 Charts dan grafik (menggunakan Chart.js/Google Charts)
- 🔧 Export reports (PDF, Excel)
- 🔧 Dashboard statistics dan KPI

**File yang dikerjakan:**
- `dashboard/reports.php` - Laporan lengkap dari DB
- `dashboard/index.php` - Update dashboard dengan data real
- `includes/report-functions.php` - (Buat baru) Helper functions
- `public/js/charts.js` - (Buat baru) JavaScript untuk charts

**Estimasi:** 2-3 minggu

---

### 🔷 **ANGGOTA 5: Database Integration & API**
**Tanggung Jawab:**
- ✅ Memastikan database schema berjalan dengan baik
- 🔧 Membuat helper functions untuk database operations
- 🔧 Implementasi stored procedures dan triggers yang sudah dibuat
- 🔧 Membuat API endpoints untuk AJAX operations
- 🔧 Database optimization dan indexing
- 🔧 Backup dan restore functionality

**File yang dikerjakan:**
- `config/database.php` - Enhance existing database config
- `includes/db-functions.php` - (Buat baru) Database helper functions
- `api/` - (Buat folder baru) API endpoints
- `includes/backup.php` - (Buat baru) Database backup tools
- `setup/migrate.php` - (Buat baru) Database migration tools

**Estimasi:** 2-3 minggu

---

### 🔷 **ANGGOTA 6: UI/UX Enhancement & Security**
**Tanggung Jawab:**
- 🔧 Memperbaiki dan mempercantik tampilan yang sudah ada
- 🔧 Responsive design untuk mobile dan tablet
- 🔧 JavaScript interactivity dan AJAX implementation
- 🔧 Security measures (CSRF protection, XSS prevention)
- 🔧 Form validation dan user experience improvements
- 🔧 Loading states dan error messages

**File yang dikerjakan:**
- `public/css/` - Enhanced styling
- `public/js/` - JavaScript functionality
- `includes/security.php` - (Buat baru) Security functions
- `includes/validation.php` - (Buat baru) Form validation
- Semua file `.php` - UI/UX improvements

**Estimasi:** 2-3 minggu

---

## 🔧 LANGKAH AWAL UNTUK SEMUA ANGGOTA

### 1. **Setup Development Environment**
```bash
# Pastikan XAMPP/Laragon berjalan
# Clone/copy project ke htdocs/www
# Jalankan database setup di: http://localhost/uaspbw/setup/
```

### 2. **Database Setup**
- Akses: `http://localhost/uaspbw/setup/`
- Ikuti wizard setup database
- Test koneksi database

### 3. **Pahami Struktur Project**
- Baca file `database/README.md` untuk memahami database
- Pahami navigasi di dashboard (sidebar & user dropdown)
- Test semua halaman yang sudah ada

---

## 🤝 KOORDINASI & KOMUNIKASI

### **File Sharing:**
- Gunakan Git/GitHub untuk version control
- Atau sharing folder untuk file updates

### **Komunikasi:**
- **Daily standup** via WhatsApp group
- **Weekly meeting** untuk review progress
- **Shared document** untuk tracking issues

### **Naming Convention:**
- File: `kebab-case.php`
- Functions: `camelCase()`
- Variables: `$snake_case`
- Database: `snake_case`

---

## 🚀 DELIVERABLES AKHIR

### **Yang Harus Diserahkan:**
1. **Source code lengkap** dengan dokumentasi
2. **Database schema** dan sample data
3. **User manual** untuk penggunaan sistem
4. **Technical documentation** untuk maintenance
5. **Deployment guide** untuk production
6. **Testing report** dan bug fixes

### **Kriteria Sukses:**
- ✅ Semua halaman terhubung dengan database
- ✅ CRUD operations berfungsi sempurna
- ✅ Authentication dan authorization bekerja
- ✅ Reports menampilkan data real dari database
- ✅ UI responsive dan user-friendly
- ✅ Security measures implemented
- ✅ No critical bugs atau errors

---

## 📞 KONTAK & SUPPORT

**Jika ada masalah:**
1. Cek dokumentasi di `database/README.md`
2. Tanya di group chat
3. Konsultasi dengan project lead
4. Schedule 1-on-1 meeting jika perlu

---

**Good Luck & Happy Coding! 🚀**
