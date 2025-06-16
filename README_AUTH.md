# Sistem Autentikasi - Sistem Pencatatan Order

## Deskripsi
Sistem autentikasi lengkap untuk aplikasi Sistem Pencatatan Order berbasis PHP dengan fitur login, register, manajemen session, proteksi halaman, dan update profil.

## Fitur Utama

### 🔐 Autentikasi
- **Login**: Login dengan username/email dan password
- **Register**: Registrasi user baru dengan validasi lengkap
- **Logout**: Logout aman dengan penghapusan session
- **Remember Me**: Opsi untuk mengingat login hingga 30 hari
- **Auto-login**: Otomatis login setelah registrasi berhasil

### 🛡️ Keamanan
- **Password Hashing**: Menggunakan `password_hash()` dan `password_verify()`
- **CSRF Protection**: Token CSRF untuk semua form
- **Session Management**: Pengelolaan session yang aman
- **SQL Injection Protection**: Menggunakan prepared statements
- **XSS Protection**: Sanitasi input dan output dengan `htmlspecialchars()`

### 👤 Manajemen Profil
- **Update Profil**: Edit nama, email, telepon, dan alamat
- **Ganti Password**: Ubah password dengan validasi password lama
- **Validasi Email**: Cek keunikan email saat update
- **Validasi Form**: Validasi client-side dan server-side

### 🔒 Proteksi Halaman
- **Session Check**: Otomatis redirect ke login jika belum login
- **Role-based Access**: Kontrol akses berdasarkan role user
- **Page Protection**: Semua halaman dashboard terlindungi

## Struktur File

```
uaspbw/
├── auth/
│   ├── login.php          # Halaman login
│   ├── register.php       # Halaman register
│   └── logout.php         # Proses logout
├── config/
│   ├── database.php       # Konfigurasi database
│   └── auth.php          # Helper functions autentikasi
├── dashboard/
│   ├── index.php         # Dashboard utama
│   ├── settings.php      # Pengaturan & profil
│   ├── add-order.php     # Tambah order
│   ├── customers.php     # Data pelanggan
│   ├── orders.php        # Daftar order
│   └── reports.php       # Laporan
├── database/
│   └── schema.sql        # Struktur database
└── test_auth.php         # File test autentikasi
```

## Database Schema

### Tabel `users`
```sql
CREATE TABLE users (
    id INT PRIMARY KEY AUTO_INCREMENT,
    username VARCHAR(50) UNIQUE NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    full_name VARCHAR(100) NOT NULL,
    phone VARCHAR(20),
    address TEXT,
    role ENUM('admin', 'user') DEFAULT 'user',
    is_active BOOLEAN DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);
```

## Fungsi Helper Utama

### `config/auth.php`

#### Fungsi Autentikasi
- `login_user($username_or_email, $password, $remember_me = false)` - Login user
- `register_user($data)` - Register user baru
- `logout_user()` - Logout dan hapus session
- `is_logged_in()` - Cek status login
- `get_logged_in_user()` - Ambil data user yang sedang login

#### Fungsi Proteksi
- `require_login($redirect_to = '../auth/login.php')` - Wajib login
- `check_user_role($required_role)` - Cek role user
- `require_role($required_role, $redirect_to = '../auth/login.php')` - Wajib role tertentu

#### Fungsi Profil
- `update_user_profile($user_id, $data)` - Update profil user
- `change_user_password($user_id, $current_password, $new_password)` - Ganti password

#### Fungsi Keamanan
- `generate_csrf_token()` - Generate CSRF token
- `verify_csrf_token($token)` - Verifikasi CSRF token
- `validate_password_strength($password)` - Validasi kekuatan password

#### Fungsi Utilitas
- `logActivity($action, $table, $record_id, $description)` - Log aktivitas user

## Cara Penggunaan

### 1. Proteksi Halaman
```php
<?php
session_start();
require_once '../config/database.php';
require_once '../config/auth.php';

// Wajib login
require_login();

// Ambil data user
$current_user = get_logged_in_user();
?>
```

### 2. Form dengan CSRF Protection
```php
<form method="POST" action="">
    <input type="hidden" name="csrf_token" value="<?php echo generate_csrf_token(); ?>">
    <!-- form fields -->
</form>

<?php
if ($_POST) {
    if (!verify_csrf_token($_POST['csrf_token'])) {
        die('Invalid CSRF token');
    }
    // process form
}
?>
```

### 3. Update Profil
```php
if (isset($_POST['update_profile'])) {
    $profile_data = [
        'full_name' => trim($_POST['full_name']),
        'email' => trim($_POST['email']),
        'phone' => trim($_POST['phone']),
        'address' => trim($_POST['address'])
    ];
    
    $result = update_user_profile($user_id, $profile_data);
    
    if ($result['success']) {
        $success_message = $result['message'];
    } else {
        $error_message = $result['message'];
    }
}
```

## Validasi & Keamanan

### Client-Side Validation
- Validasi form dengan JavaScript
- Cek format email
- Validasi panjang password
- Konfirmasi password matching

### Server-Side Validation
- Sanitasi input dengan `trim()` dan `htmlspecialchars()`
- Validasi email dengan `filter_var()`
- Cek keunikan username dan email
- Validasi kekuatan password
- CSRF token verification

### Password Security
- Hashing dengan `password_hash(PASSWORD_DEFAULT)`
- Verifikasi dengan `password_verify()`
- Minimal 6 karakter
- Wajib berbeda dari password lama

## Pesan & Feedback

### Bahasa Indonesia
Semua pesan error, sukses, dan interface menggunakan bahasa Indonesia:
- "Login berhasil! Selamat datang kembali."
- "Password atau username salah!"
- "Registrasi berhasil! Anda akan diarahkan ke dashboard."
- "Profil berhasil diupdate!"
- "Email sudah digunakan oleh user lain!"

### Alert System
- Success alerts (hijau) untuk operasi berhasil
- Error alerts (merah) untuk kesalahan
- Warning alerts (kuning) untuk peringatan
- Auto-hide alerts setelah 5 detik

## Testing

### File Test
Gunakan `test_auth.php` untuk mengecek:
- Status login user
- Data session
- Fungsi-fungsi autentikasi
- CSRF token generation
- Data user dari database

### Manual Testing
1. **Register**: Buat akun baru
2. **Login**: Login dengan akun yang dibuat
3. **Dashboard**: Akses halaman dashboard
4. **Update Profil**: Edit profil di settings
5. **Ganti Password**: Ubah password
6. **Logout**: Keluar dari sistem
7. **Protection**: Coba akses dashboard tanpa login

## Konfigurasi Database

### Setup
1. Import `database/schema.sql` ke database MySQL
2. Update `config/database.php` dengan kredensial database
3. Pastikan ekstensi PDO MySQL aktif

### Connection String
```php
$host = 'localhost';
$dbname = 'uaspbw_db';
$username = 'root';
$password = '';
```

## Troubleshooting

### Session Issues
- Pastikan `session_start()` dipanggil di awal file
- Cek konfigurasi session di php.ini
- Clear browser cookies jika ada masalah

### Database Connection
- Cek kredensial database di `config/database.php`
- Pastikan MySQL service berjalan
- Verify database dan tabel sudah dibuat

### Permission Issues
- Pastikan file PHP memiliki permission read/write
- Cek permission direktori untuk session storage

## Fitur Tambahan (Opsional)

### Upload Foto Profil
- Input file untuk upload avatar
- Validasi tipe dan ukuran file
- Resize gambar otomatis

### Email Verification
- Kirim email konfirmasi saat register
- Aktivasi akun via link email
- Forgot password via email

### Rate Limiting
- Batas percobaan login
- Temporary account lock
- CAPTCHA setelah beberapa kali gagal

### Two-Factor Authentication
- SMS/Email OTP
- Google Authenticator integration
- Backup codes

## Status Development

### ✅ Completed
- ✅ Database schema dan koneksi
- ✅ Helper functions autentikasi lengkap
- ✅ Halaman login dengan validasi
- ✅ Halaman register dengan validasi
- ✅ Proses logout yang aman
- ✅ Dashboard dengan proteksi session
- ✅ Update profil dan ganti password
- ✅ CSRF protection di semua form
- ✅ Validasi client-side dan server-side
- ✅ Pesan error/sukses dalam bahasa Indonesia
- ✅ Proteksi semua halaman dashboard
- ✅ User dropdown dengan data dinamis

### 🔄 In Progress
- Session management optimization
- Enhanced error handling

### 📋 Future Enhancements
- Upload foto profil
- Email verification
- Rate limiting login attempts
- Two-factor authentication
- Advanced role management

## Kontributor

**Anggota 1** - Sistem Autentikasi  
Mengembangkan dan mengintegrasikan sistem autentikasi lengkap meliputi login, register, session management, proteksi halaman, dan update profil untuk Sistem Pencatatan Order berbasis PHP.

---

*Sistem ini dikembangkan sebagai bagian dari tugas UAS Pemrograman Berbasis Web*
