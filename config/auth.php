<?php
/**
 * Authentication Helper Functions
 * File: config/auth.php
 * Author: Anggota 1 - Authentication Team
 */

// Ensure session is started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/database.php';

/**
 * Login user with username/email and password
 */
function login_user($username, $password)
{
    global $pdo;

    try {
        // Check if input is email or username
        $sql = "SELECT * FROM users WHERE (username = ? OR email = ?) AND is_active = 1";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$username, $username]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        // Verify user exists and password is correct
        if ($user && password_verify($password, $user['password'])) {
            // Set session variables
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['email'] = $user['email'];
            $_SESSION['full_name'] = $user['full_name'];
            $_SESSION['role'] = $user['role'];
            $_SESSION['avatar'] = $user['avatar'];
            $_SESSION['login_time'] = time();

            // Update last login timestamp
            $update_sql = "UPDATE users SET updated_at = CURRENT_TIMESTAMP WHERE id = ?";
            $update_stmt = $pdo->prepare($update_sql);
            $update_stmt->execute([$user['id']]);

            // Log the login activity
            logActivity('login', 'users', $user['id'], 'User logged in successfully');

            return [
                'success' => true,
                'user' => $user,
                'message' => 'Login berhasil!'
            ];
        } else {
            return [
                'success' => false,
                'message' => 'Username/email atau password tidak valid!'
            ];
        }
    } catch (PDOException $e) {
        error_log("Login error: " . $e->getMessage());
        return [
            'success' => false,
            'message' => 'Terjadi kesalahan sistem. Silakan coba lagi.'
        ];
    }
}

/**
 * Register new user
 */
function register_user($data)
{
    global $pdo;

    try {
        // Validate required fields
        $required_fields = ['username', 'email', 'password', 'full_name'];
        foreach ($required_fields as $field) {
            if (empty($data[$field])) {
                return [
                    'success' => false,
                    'message' => 'Field ' . $field . ' wajib diisi!'
                ];
            }
        }

        // Check if username already exists
        $check_username = "SELECT id FROM users WHERE username = ?";
        $stmt = $pdo->prepare($check_username);
        $stmt->execute([$data['username']]);
        if ($stmt->fetch()) {
            return [
                'success' => false,
                'message' => 'Username sudah digunakan!'
            ];
        }

        // Check if email already exists
        $check_email = "SELECT id FROM users WHERE email = ?";
        $stmt = $pdo->prepare($check_email);
        $stmt->execute([$data['email']]);
        if ($stmt->fetch()) {
            return [
                'success' => false,
                'message' => 'Email sudah terdaftar!'
            ];
        }

        // Hash password
        $hashed_password = password_hash($data['password'], PASSWORD_DEFAULT);

        // Insert new user
        $sql = "INSERT INTO users (username, email, password, full_name, phone, role) 
                VALUES (?, ?, ?, ?, ?, ?)";
        $stmt = $pdo->prepare($sql);
        $result = $stmt->execute([
            $data['username'],
            $data['email'],
            $hashed_password,
            $data['full_name'],
            $data['phone'] ?? null,
            $data['role'] ?? 'user'
        ]);

        if ($result) {
            $user_id = $pdo->lastInsertId();

            // Log the registration
            logActivity('register', 'users', $user_id, 'New user registered');

            // Auto login after registration
            $login_result = login_user($data['username'], $data['password']);

            return [
                'success' => true,
                'user_id' => $user_id,
                'message' => 'Registrasi berhasil! Anda akan dialihkan ke dashboard.',
                'auto_login' => $login_result['success'] ?? false
            ];
        } else {
            return [
                'success' => false,
                'message' => 'Gagal mendaftarkan user. Silakan coba lagi.'
            ];
        }

    } catch (PDOException $e) {
        error_log("Registration error: " . $e->getMessage());
        return [
            'success' => false,
            'message' => 'Terjadi kesalahan sistem. Silakan coba lagi.'
        ];
    }
}

/**
 * Logout user
 */
function logout_user()
{
    if (isset($_SESSION['user_id'])) {
        // Log the logout activity
        logActivity('logout', 'users', $_SESSION['user_id'], 'User logged out');
    }

    // Clear all session variables
    $_SESSION = array();

    // Destroy session cookie
    if (ini_get("session.use_cookies")) {
        $params = session_get_cookie_params();
        setcookie(
            session_name(),
            '',
            time() - 42000,
            $params["path"],
            $params["domain"],
            $params["secure"],
            $params["httponly"]
        );
    }

    // Destroy session
    session_destroy();

    return [
        'success' => true,
        'message' => 'Logout berhasil!'
    ];
}

/**
 * Check if user is logged in
 */
function is_logged_in()
{
    return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
}

/**
 * Get current logged in user data
 */
function get_logged_in_user()
{
    if (!is_logged_in()) {
        return null;
    }

    return [
        'id' => $_SESSION['user_id'],
        'username' => $_SESSION['username'] ?? '',
        'email' => $_SESSION['email'] ?? '',
        'full_name' => $_SESSION['full_name'] ?? '',
        'role' => $_SESSION['role'] ?? 'user',
        'avatar' => $_SESSION['avatar'] ?? null,
        'login_time' => $_SESSION['login_time'] ?? null
    ];
}

/**
 * Require user to be logged in (redirect if not)
 */
function require_login($redirect_to = '../auth/login.php')
{
    if (!is_logged_in()) {
        header("Location: $redirect_to");
        exit();
    }
}

/**
 * Check if current user has specific role
 */
function check_user_role($required_role)
{
    if (!is_logged_in()) {
        return false;
    }

    $user_role = $_SESSION['role'] ?? 'user';

    // Admin can access everything
    if ($user_role === 'admin') {
        return true;
    }

    return $user_role === $required_role;
}

/**
 * Require specific role (redirect if not authorized)
 */
function require_role($required_role, $redirect_to = '../auth/login.php')
{
    if (!check_user_role($required_role)) {
        header("Location: $redirect_to");
        exit();
    }
}

/**
 * Generate secure remember token
 */
function generate_remember_token()
{
    return bin2hex(random_bytes(32));
}

/**
 * Validate password strength
 */
function validate_password($password)
{
    $errors = [];

    if (strlen($password) < 8) {
        $errors[] = 'Password minimal 8 karakter';
    }

    if (!preg_match('/[A-Z]/', $password)) {
        $errors[] = 'Password harus mengandung minimal 1 huruf besar';
    }

    if (!preg_match('/[a-z]/', $password)) {
        $errors[] = 'Password harus mengandung minimal 1 huruf kecil';
    }

    if (!preg_match('/[0-9]/', $password)) {
        $errors[] = 'Password harus mengandung minimal 1 angka';
    }

    return [
        'is_valid' => empty($errors),
        'errors' => $errors
    ];
}

/**
 * Generate CSRF token
 */
function generate_csrf_token()
{
    if (!isset($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

/**
 * Verify CSRF token
 */
function verify_csrf_token($token)
{
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

/**
 * Update user profile
 */
function update_user_profile($user_id, $data)
{
    global $pdo;

    try {
        $allowed_fields = ['full_name', 'email', 'phone', 'address'];
        $update_fields = [];
        $params = [];

        foreach ($allowed_fields as $field) {
            if (isset($data[$field])) {
                $update_fields[] = "$field = ?";
                $params[] = $data[$field];
            }
        }

        if (empty($update_fields)) {
            return [
                'success' => false,
                'message' => 'Tidak ada data yang diupdate'
            ];
        }

        // Check if email is being updated and if it's unique
        if (isset($data['email'])) {
            $check_email = "SELECT id FROM users WHERE email = ? AND id != ?";
            $stmt = $pdo->prepare($check_email);
            $stmt->execute([$data['email'], $user_id]);
            if ($stmt->fetch()) {
                return [
                    'success' => false,
                    'message' => 'Email sudah digunakan oleh user lain!'
                ];
            }
        }

        $params[] = $user_id;
        $sql = "UPDATE users SET " . implode(', ', $update_fields) . " WHERE id = ?";

        $stmt = $pdo->prepare($sql);
        $result = $stmt->execute($params);

        if ($result) {
            // Update session data if email or full_name changed
            if (isset($data['email'])) {
                $_SESSION['email'] = $data['email'];
            }
            if (isset($data['full_name'])) {
                $_SESSION['full_name'] = $data['full_name'];
            }

            logActivity('update_profile', 'users', $user_id, 'User profile updated');

            return [
                'success' => true,
                'message' => 'Profil berhasil diupdate!'
            ];
        } else {
            return [
                'success' => false,
                'message' => 'Gagal mengupdate profil'
            ];
        }

    } catch (PDOException $e) {
        error_log("Update profile error: " . $e->getMessage());
        return [
            'success' => false,
            'message' => 'Terjadi kesalahan sistem'
        ];
    }
}

/**
 * Change user password
 */
function change_user_password($user_id, $current_password, $new_password)
{
    global $pdo;

    try {
        // Get current user data
        $sql = "SELECT password FROM users WHERE id = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$user_id]);
        $user = $stmt->fetch();

        if (!$user) {
            return [
                'success' => false,
                'message' => 'User tidak ditemukan'
            ];
        }

        // Verify current password
        if (!password_verify($current_password, $user['password'])) {
            return [
                'success' => false,
                'message' => 'Password lama tidak sesuai'
            ];
        }

        // Validate new password
        $password_validation = validate_password($new_password);
        if (!$password_validation['is_valid']) {
            return [
                'success' => false,
                'message' => implode(', ', $password_validation['errors'])
            ];
        }

        // Update password
        $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
        $update_sql = "UPDATE users SET password = ? WHERE id = ?";
        $update_stmt = $pdo->prepare($update_sql);
        $result = $update_stmt->execute([$hashed_password, $user_id]);

        if ($result) {
            logActivity('change_password', 'users', $user_id, 'User changed password');

            return [
                'success' => true,
                'message' => 'Password berhasil diubah!'
            ];
        } else {
            return [
                'success' => false,
                'message' => 'Gagal mengubah password'
            ];
        }

    } catch (PDOException $e) {
        error_log("Change password error: " . $e->getMessage());
        return [
            'success' => false,
            'message' => 'Terjadi kesalahan sistem'
        ];
    }
}
