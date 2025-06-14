# 🔐 TUGAS ANGGOTA 1: AUTHENTICATION & USER MANAGEMENT

**Nama:** [Isi nama anggota]  
**Tanggung Jawab:** Sistem Login, Register, User Management, Security  
**Deadline:** [Isi deadline]  

---

## 🎯 OBJECTIVES

Mengintegrasikan sistem authentication yang sudah ada (tampilan statis) dengan database dan menambahkan fitur user management lengkap.

---

## 📋 TASK CHECKLIST

### **Phase 1: Understanding & Setup (Week 1)**
- [ ] Setup development environment (XAMPP/Laragon)
- [ ] Run database setup: `http://localhost/uaspbw/setup/`
- [ ] Study existing files: `auth/login.php`, `auth/register.php`
- [ ] Study database schema: `users` table in `database/schema.sql`
- [ ] Test current login/register pages (note: currently static)

### **Phase 2: Database Integration (Week 2-3)**
- [ ] **Update `auth/login.php`:**
  - [ ] Connect to database using `config/database.php`
  - [ ] Validate username/email and password against `users` table
  - [ ] Implement password verification (bcrypt)
  - [ ] Set session variables on successful login
  - [ ] Redirect to dashboard after login
  - [ ] Show error messages for invalid credentials

- [ ] **Update `auth/register.php`:**
  - [ ] Form validation (required fields, email format, password strength)
  - [ ] Check if username/email already exists
  - [ ] Hash password before storing (password_hash())
  - [ ] Insert new user to database
  - [ ] Auto-login after successful registration
  - [ ] Show success/error messages

### **Phase 3: Session Management (Week 3)**
- [ ] **Create `config/auth.php`:**
  - [ ] `login_user($username, $password)` function
  - [ ] `register_user($data)` function
  - [ ] `logout_user()` function
  - [ ] `is_logged_in()` function
  - [ ] `get_current_user()` function
  - [ ] `check_user_role($required_role)` function

- [ ] **Add session protection to dashboard:**
  - [ ] Add login check to all dashboard pages
  - [ ] Redirect to login if not authenticated
  - [ ] Add logout functionality to user dropdown

### **Phase 4: User Profile & Settings (Week 4)**
- [ ] **Update `dashboard/settings.php`:**
  - [ ] Display current user information
  - [ ] Update profile form (name, email, phone)
  - [ ] Change password form
  - [ ] Upload profile picture functionality
  - [ ] Email change with verification (optional)

### **Phase 5: Security & Role Management (Week 5)**
- [ ] **Security enhancements:**
  - [ ] CSRF protection for forms
  - [ ] Rate limiting for login attempts
  - [ ] Session timeout
  - [ ] Secure session configuration
  - [ ] Input sanitization

- [ ] **Role-based access (if needed):**
  - [ ] Admin vs User roles
  - [ ] Role-based dashboard access
  - [ ] Admin user management interface

---

## 📁 FILES TO WORK WITH

### **Existing Files (Modify):**
- `auth/login.php` - Add database integration
- `auth/register.php` - Add database integration  
- `dashboard/settings.php` - Add user profile management
- All dashboard pages - Add login protection

### **New Files (Create):**
- `config/auth.php` - Authentication helper functions
- `auth/logout.php` - Logout processing
- `auth/forgot-password.php` - (Optional) Password reset

---

## 🗄️ DATABASE TABLES

### **Primary Table: `users`**
```sql
-- Study this table structure in database/schema.sql
users (
    id, username, email, password_hash, 
    full_name, phone, role, 
    created_at, updated_at, last_login
)
```

---

## 💻 CODE EXAMPLES

### **Login Processing Example:**
```php
<?php
// auth/login.php - Add this logic
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
        $error = "Invalid username or password";
    }
}
?>
```

### **Auth Helper Function Example:**
```php
<?php
// config/auth.php - Create this file
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
        $update_stmt = $pdo->prepare($update_sql);
        $update_stmt->execute([$user['id']]);
        
        return true;
    }
    return false;
}
?>
```

---

## 🧪 TESTING CHECKLIST

- [ ] Register new user successfully
- [ ] Login with correct credentials
- [ ] Login fails with wrong credentials
- [ ] Session persists across dashboard pages
- [ ] Logout works correctly
- [ ] Protected pages redirect to login when not authenticated
- [ ] Profile update works
- [ ] Password change works
- [ ] Duplicate username/email registration prevented

---

## 🚨 COMMON ISSUES & SOLUTIONS

### **Issue:** "Headers already sent" error
**Solution:** Make sure no output (echo, HTML) before `header()` redirects

### **Issue:** Sessions not working
**Solution:** Ensure `session_start()` is called before any session usage

### **Issue:** Password not matching
**Solution:** Use `password_hash()` for storing and `password_verify()` for checking

### **Issue:** Database connection error
**Solution:** Check database credentials in `config/database.php`

---

## 📚 RESOURCES

- [PHP Sessions Documentation](https://www.php.net/manual/en/book.session.php)
- [PHP Password Functions](https://www.php.net/manual/en/ref.password.php)
- [PDO Documentation](https://www.php.net/manual/en/book.pdo.php)
- [Security Best Practices](https://owasp.org/www-project-top-ten/)

---

## 📞 HELP & SUPPORT

**Jika mengalami kesulitan:**
1. Cek database schema di `database/README.md`
2. Test database connection di `setup/index.php`
3. Konsultasi dengan Anggota 5 (Database Integration)
4. Tanya di group chat kelompok

**Files yang saling berkaitan:**
- Anggota 5: Database functions (`includes/db-functions.php`)
- Anggota 6: UI improvements dan security measures
- Semua anggota: Session protection di halaman masing-masing

---

**Success Criteria:**
✅ User dapat register dan login dengan database  
✅ Session management berfungsi sempurna  
✅ Dashboard protected dari akses tanpa login  
✅ User dapat update profile di settings  
✅ Security measures implemented  

**Good luck! 🚀**
