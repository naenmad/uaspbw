# 🔐 TUGAS ANGGOTA 1: AUTENTIKASI & MANAJEMEN PENGGUNA

**Nama:** [Isi nama anggota]  
**Tanggung Jawab:** Sistem Login, Register, Manajemen Pengguna, Keamanan  
**Deadline:** [Isi deadline]  

---

## 🎯 TUJUAN

Mengintegrasikan sistem autentikasi yang sudah ada (tampilan statis) dengan database dan menambahkan fitur manajemen pengguna lengkap.

---

## 📋 DAFTAR TUGAS

### **Fase 1: Pemahaman & Setup (Minggu 1)**
- [ ] Setup environment pengembangan (XAMPP/Laragon)
- [ ] Jalankan setup database: `http://localhost/uaspbw/setup/`
- [ ] Pelajari file yang ada: `auth/login.php`, `auth/register.php`
- [ ] Pelajari skema database: tabel `users` di `database/schema.sql`
- [ ] Test halaman login/register saat ini (catatan: masih statis)

### **Fase 2: Integrasi Database (Minggu 2-3)**
- [ ] **Update `auth/login.php`:**
  - [ ] Hubungkan ke database menggunakan `config/database.php`
  - [ ] Validasi username/email dan password terhadap tabel `users`
  - [ ] Implementasi verifikasi password (bcrypt)
  - [ ] Set variabel session saat login berhasil
  - [ ] Redirect ke dashboard setelah login
  - [ ] Tampilkan pesan error untuk kredensial tidak valid

- [ ] **Update `auth/register.php`:**
  - [ ] Validasi form (field wajib, format email, kekuatan password)
  - [ ] Cek apakah username/email sudah ada
  - [ ] Hash password sebelum disimpan (password_hash())
  - [ ] Insert pengguna baru ke database
  - [ ] Auto-login setelah registrasi berhasil
  - [ ] Tampilkan pesan sukses/error

### **Fase 3: Manajemen Session (Minggu 3)**
- [ ] **Buat `config/auth.php`:**
  - [ ] Fungsi `login_user($username, $password)`
  - [ ] Fungsi `register_user($data)`
  - [ ] Fungsi `logout_user()`
  - [ ] Fungsi `is_logged_in()`
  - [ ] Fungsi `get_current_user()`
  - [ ] Fungsi `check_user_role($required_role)`

- [ ] **Tambahkan proteksi session ke dashboard:**
  - [ ] Tambahkan pengecekan login ke semua halaman dashboard
  - [ ] Redirect ke login jika tidak ter-autentikasi
  - [ ] Tambahkan fungsi logout ke dropdown user

### **Fase 4: Profil Pengguna & Pengaturan (Minggu 4)**
- [ ] **Update `dashboard/settings.php`:**
  - [ ] Tampilkan informasi pengguna saat ini
  - [ ] Form update profil (nama, email, telepon)
  - [ ] Form ganti password
  - [ ] Fungsi upload foto profil
  - [ ] Verifikasi email saat ganti email (opsional)

### **Fase 5: Keamanan & Manajemen Role (Minggu 5)**
- [ ] **Peningkatan keamanan:**
  - [ ] Proteksi CSRF untuk form
  - [ ] Rate limiting untuk percobaan login
  - [ ] Timeout session
  - [ ] Konfigurasi session yang aman
  - [ ] Sanitasi input

- [ ] **Akses berbasis role (jika diperlukan):**
  - [ ] Role Admin vs User
  - [ ] Akses dashboard berbasis role
  - [ ] Interface manajemen pengguna untuk admin

---

## 📁 FILE YANG DIKERJAKAN

### **File yang Ada (Modifikasi):**
- `auth/login.php` - Tambahkan integrasi database
- `auth/register.php` - Tambahkan integrasi database  
- `dashboard/settings.php` - Tambahkan manajemen profil pengguna
- Semua halaman dashboard - Tambahkan proteksi login

### **File Baru (Buat):**
- `config/auth.php` - Fungsi helper autentikasi
- `auth/logout.php` - Proses logout
- `auth/forgot-password.php` - (Opsional) Reset password

---

## 🗄️ TABEL DATABASE

### **Tabel Utama: `users`**
```sql
-- Pelajari struktur tabel ini di database/schema.sql
users (
    id, username, email, password_hash, 
    full_name, phone, role, 
    created_at, updated_at, last_login
)
```

---

## 💻 CONTOH KODE

### **Contoh Proses Login:**
```php
<?php
// auth/login.php - Tambahkan logika ini
session_start();
require_once '../config/database.php';
require_once '../config/auth.php';

if ($_POST) {
    $username = $_POST['username'];
    $password = $_POST['password'];
    
    if (login_user($username, $password)) {
        header('Location: ../dashboard/');
        exit;
    } else {
        $error = "Username atau password tidak valid";
    }
}
?>
```

### **Contoh Fungsi Helper Auth:**
```php
<?php
// config/auth.php - Buat file ini
function login_user($username, $password) {
    global $pdo;
    
    $sql = "SELECT * FROM users WHERE username = ? OR email = ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$username, $username]);
    $user = $stmt->fetch();
    
    if ($user && password_verify($password, $user['password_hash'])) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['username'] = $user['username'];
        $_SESSION['role'] = $user['role'];
        
        // Update last login
        $update_sql = "UPDATE users SET last_login = NOW() WHERE id = ?";
        $update_stmt = $pdo->prepare($update_sql);        $update_stmt->execute([$user['id']]);
        
        return true;
    }
    return false;
}
?>
```

---

## 🧪 DAFTAR PENGUJIAN

- [ ] Registrasi pengguna baru berhasil
- [ ] Login dengan kredensial yang benar
- [ ] Login gagal dengan kredensial salah
- [ ] Session bertahan di seluruh halaman dashboard
- [ ] Logout berfungsi dengan benar
- [ ] Halaman yang dilindungi redirect ke login saat tidak ter-autentikasi
- [ ] Update profil berfungsi
- [ ] Ganti password berfungsi
- [ ] Registrasi dengan username/email duplikat dicegah

---

## 🚨 MASALAH UMUM & SOLUSI

### **Masalah:** Error "Headers already sent"
**Solusi:** Pastikan tidak ada output (echo, HTML) sebelum `header()` redirect

### **Masalah:** Session tidak berfungsi
**Solusi:** Pastikan `session_start()` dipanggil sebelum penggunaan session

### **Masalah:** Password tidak cocok
**Solusi:** Gunakan `password_hash()` untuk menyimpan dan `password_verify()` untuk mengecek

### **Masalah:** Error koneksi database
**Solusi:** Periksa kredensial database di `config/database.php`

---

## 📚 SUMBER REFERENSI

- [Dokumentasi PHP Sessions](https://www.php.net/manual/en/book.session.php)
- [Fungsi Password PHP](https://www.php.net/manual/en/ref.password.php)
- [Dokumentasi PDO](https://www.php.net/manual/en/book.pdo.php)
- [Best Practices Keamanan](https://owasp.org/www-project-top-ten/)

---

## 📞 BANTUAN & DUKUNGAN

**Jika mengalami kesulitan:**
1. Cek skema database di `database/README.md`
2. Test koneksi database di `setup/index.php`
3. Konsultasi dengan Anggota 5 (Integrasi Database)
4. Tanya di group chat kelompok

**File yang saling berkaitan:**
- Anggota 5: Fungsi database (`includes/db-functions.php`)
- Anggota 6: Perbaikan UI dan langkah keamanan
- Semua anggota: Proteksi session di halaman masing-masing

---

**Kriteria Sukses:**
✅ Pengguna dapat register dan login dengan database  
✅ Manajemen session berfungsi sempurna  
✅ Dashboard terlindungi dari akses tanpa login  
✅ Pengguna dapat update profil di pengaturan  
✅ Langkah keamanan terimplementasi  

**Semoga berhasil! 🚀**
