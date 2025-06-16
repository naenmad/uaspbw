<?php
// Process registration form
session_start();
require_once '../config/database.php';
require_once '../config/auth.php';

$error_message = '';
$success_message = '';

// Check if user is already logged in
if (is_logged_in()) {
    header('Location: ../dashboard/index.php');
    exit();
}

// Process registration form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $csrf_token = $_POST['csrf_token'] ?? '';

    // Verify CSRF token
    if (!verify_csrf_token($csrf_token)) {
        $error_message = 'Token keamanan tidak valid. Silakan refresh halaman.';
    } else {
        $data = [
            'full_name' => trim($_POST['fullname'] ?? ''),
            'email' => trim($_POST['email'] ?? ''),
            'username' => trim($_POST['username'] ?? ''),
            'password' => $_POST['password'] ?? '',
            'confirm_password' => $_POST['confirm_password'] ?? '',
            'phone' => trim($_POST['phone'] ?? ''),
            'terms' => isset($_POST['terms'])
        ];

        // Validation
        if (empty($data['full_name']) || empty($data['email']) || empty($data['username']) || empty($data['password'])) {
            $error_message = 'Semua field wajib diisi!';
        } elseif (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            $error_message = 'Format email tidak valid!';
        } elseif (strlen($data['username']) < 3) {
            $error_message = 'Username minimal 3 karakter!';
        } elseif (!preg_match('/^[a-zA-Z0-9_]+$/', $data['username'])) {
            $error_message = 'Username hanya boleh mengandung huruf, angka, dan underscore!';
        } elseif ($data['password'] !== $data['confirm_password']) {
            $error_message = 'Konfirmasi password tidak cocok!';
        } elseif (!$data['terms']) {
            $error_message = 'Anda harus menyetujui syarat dan ketentuan!';
        } else {
            // Validate password strength
            $password_validation = validate_password($data['password']);
            if (!$password_validation['is_valid']) {
                $error_message = implode(', ', $password_validation['errors']);
            } else {
                // Attempt registration
                $register_result = register_user($data);

                if ($register_result['success']) {
                    $success_message = $register_result['message'];

                    // Redirect after short delay if auto-login successful
                    if ($register_result['auto_login']) {
                        header('refresh:2;url=../dashboard/index.php');
                    }
                } else {
                    $error_message = $register_result['message'];
                }
            }
        }
    }
}

// Generate CSRF token
$csrf_token = generate_csrf_token();
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daftar - Sistem Pencatatan Order</title>

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

    <style>
        body {
            background-color: #f8f9fa;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        .container-main {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px 0;
        }

        .card-register {
            background: white;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            padding: 40px;
            max-width: 450px;
            width: 100%;
        }

        .form-control {
            border-radius: 8px;
            padding: 12px 15px;
            border: 1px solid #ddd;
            margin-bottom: 15px;
        }

        .form-control:focus {
            border-color: #28a745;
            box-shadow: 0 0 0 0.2rem rgba(40, 167, 69, 0.25);
        }

        .btn-register {
            background-color: #28a745;
            border-color: #28a745;
            padding: 12px;
            font-weight: 500;
            border-radius: 8px;
            width: 100%;
        }

        .btn-register:hover {
            background-color: #218838;
            border-color: #218838;
        }

        .btn-back {
            background-color: #6c757d;
            border-color: #6c757d;
            padding: 8px 20px;
            font-size: 14px;
            border-radius: 8px;
        }

        .btn-back:hover {
            background-color: #545b62;
            border-color: #545b62;
        }

        h2 {
            color: #333;
            margin-bottom: 30px;
            font-weight: 600;
        }

        .form-label {
            color: #555;
            font-weight: 500;
            margin-bottom: 8px;
        }

        .text-center a {
            color: #28a745;
            text-decoration: none;
        }

        .text-center a:hover {
            text-decoration: underline;
        }

        .alert {
            border-radius: 8px;
            margin-bottom: 20px;
        }

        .password-requirements {
            font-size: 12px;
            color: #666;
            margin-top: 5px;
        }

        .requirement-item {
            display: block;
            margin-bottom: 2px;
        }

        .requirement-item.valid {
            color: #28a745;
        }

        .requirement-item.invalid {
            color: #dc3545;
        }
    </style>
</head>

<body>
    <div class="container-main">
        <div class="card-register">
            <div class="text-center mb-3">
                <a href="../index.html" class="btn btn-secondary btn-back">
                    ← Kembali ke Beranda
                </a>
            </div>
            <h2 class="text-center">Daftar Akun</h2>

            <!-- Display success/error messages -->
            <?php if (!empty($error_message)): ?>
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <?php echo htmlspecialchars($error_message); ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>

            <?php if (!empty($success_message)): ?>
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <?php echo htmlspecialchars($success_message); ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>

            <form id="registerForm" method="POST" action="">
                <!-- CSRF Token -->
                <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
                <div class="mb-3">
                    <label for="fullname" class="form-label">Nama Lengkap</label>
                    <input type="text" class="form-control" id="fullname" name="fullname"
                        placeholder="Masukkan nama lengkap"
                        value="<?php echo htmlspecialchars($_POST['fullname'] ?? ''); ?>" required>
                </div>

                <div class="mb-3">
                    <label for="email" class="form-label">Email</label>
                    <input type="email" class="form-control" id="email" name="email" placeholder="Masukkan email Anda"
                        value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>" required>
                </div>

                <div class="mb-3">
                    <label for="username" class="form-label">Username</label>
                    <input type="text" class="form-control" id="username" name="username"
                        placeholder="Masukkan username"
                        value="<?php echo htmlspecialchars($_POST['username'] ?? ''); ?>" required>
                    <small class="text-muted">Username minimal 3 karakter, hanya huruf, angka, dan underscore</small>
                </div>

                <div class="mb-3">
                    <label for="password" class="form-label">Password</label>
                    <input type="password" class="form-control" id="password" name="password"
                        placeholder="Masukkan password" required>
                    <div class="password-requirements">
                        <small class="requirement-item" id="req-length">• Minimal 8 karakter</small>
                        <small class="requirement-item" id="req-uppercase">• Minimal 1 huruf besar</small>
                        <small class="requirement-item" id="req-lowercase">• Minimal 1 huruf kecil</small>
                        <small class="requirement-item" id="req-number">• Minimal 1 angka</small>
                    </div>
                </div>

                <div class="mb-3">
                    <label for="confirm_password" class="form-label">Konfirmasi Password</label>
                    <input type="password" class="form-control" id="confirm_password" name="confirm_password"
                        placeholder="Konfirmasi password" required>
                </div>
                <div class="mb-3">
                    <label for="phone" class="form-label">Nomor Telepon (Opsional)</label>
                    <input type="tel" class="form-control" id="phone" name="phone" placeholder="Masukkan nomor telepon"
                        value="<?php echo htmlspecialchars($_POST['phone'] ?? ''); ?>">
                </div>

                <div class="mb-3 form-check">
                    <input type="checkbox" class="form-check-input" id="terms" name="terms" required>
                    <label class="form-check-label" for="terms">
                        Saya setuju dengan <a href="#"
                            onclick="alert('Terms & Conditions akan segera tersedia!')">syarat dan ketentuan</a>
                    </label>
                </div>

                <button type="submit" class="btn btn-success btn-register">
                    Daftar Sekarang
                </button>
            </form>

            <hr class="my-4">

            <div class="text-center">
                <p class="mb-2">Sudah punya akun?</p>
                <a href="login.php" class="btn btn-outline-primary">
                    Masuk Sekarang
                </a>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

    <script>
        // Password validation requirements
        const passwordInput = document.getElementById('password');
        const requirements = {
            length: document.getElementById('req-length'),
            uppercase: document.getElementById('req-uppercase'),
            lowercase: document.getElementById('req-lowercase'),
            number: document.getElementById('req-number')
        };

        // Real-time password validation
        passwordInput.addEventListener('input', function () {
            const password = this.value;

            // Check length
            if (password.length >= 8) {
                requirements.length.classList.add('valid');
                requirements.length.classList.remove('invalid');
            } else {
                requirements.length.classList.add('invalid');
                requirements.length.classList.remove('valid');
            }

            // Check uppercase
            if (/[A-Z]/.test(password)) {
                requirements.uppercase.classList.add('valid');
                requirements.uppercase.classList.remove('invalid');
            } else {
                requirements.uppercase.classList.add('invalid');
                requirements.uppercase.classList.remove('valid');
            }

            // Check lowercase
            if (/[a-z]/.test(password)) {
                requirements.lowercase.classList.add('valid');
                requirements.lowercase.classList.remove('invalid');
            } else {
                requirements.lowercase.classList.add('invalid');
                requirements.lowercase.classList.remove('valid');
            }

            // Check number
            if (/[0-9]/.test(password)) {
                requirements.number.classList.add('valid');
                requirements.number.classList.remove('invalid');
            } else {
                requirements.number.classList.add('invalid');
                requirements.number.classList.remove('valid');
            }
        });        // Form submission
        document.getElementById('registerForm').addEventListener('submit', function (e) {
            const formData = {
                fullname: document.getElementById('fullname').value.trim(),
                email: document.getElementById('email').value.trim(),
                username: document.getElementById('username').value.trim(),
                password: document.getElementById('password').value,
                confirm_password: document.getElementById('confirm_password').value,
                phone: document.getElementById('phone').value.trim(),
                terms: document.getElementById('terms').checked
            };

            // Client-side validation
            if (!validateForm(formData)) {
                e.preventDefault();
                return false;
            }

            // Show loading state
            const submitBtn = this.querySelector('button[type="submit"]');
            const originalText = submitBtn.innerHTML;
            submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Memproses...';
            submitBtn.disabled = true;

            // Re-enable button after 10 seconds (in case of network issues)
            setTimeout(() => {
                submitBtn.innerHTML = originalText;
                submitBtn.disabled = false;
            }, 10000);
        });

        function validateForm(data) {
            // Check required fields
            if (!data.fullname || !data.email || !data.username || !data.password || !data.confirm_password) {
                showAlert('Mohon lengkapi semua field yang wajib diisi!', 'danger');
                return false;
            }

            // Check email format
            if (!isValidEmail(data.email)) {
                showAlert('Format email tidak valid!', 'danger');
                return false;
            }

            // Check username
            if (data.username.length < 3) {
                showAlert('Username minimal 3 karakter!', 'danger');
                return false;
            }

            if (!/^[a-zA-Z0-9_]+$/.test(data.username)) {
                showAlert('Username hanya boleh mengandung huruf, angka, dan underscore!', 'danger');
                return false;
            }

            // Check password strength
            if (!isStrongPassword(data.password)) {
                showAlert('Password harus memenuhi semua persyaratan!', 'danger');
                return false;
            }

            // Check password confirmation
            if (data.password !== data.confirm_password) {
                showAlert('Konfirmasi password tidak cocok!', 'danger');
                return false;
            }

            // Check terms agreement
            if (!data.terms) {
                showAlert('Anda harus menyetujui syarat dan ketentuan!', 'danger');
                return false;
            }

            return true;
        }

        function isStrongPassword(password) {
            return password.length >= 8 &&
                /[A-Z]/.test(password) &&
                /[a-z]/.test(password) &&
                /[0-9]/.test(password);
        } function showAlert(message, type) {
            // This function is kept for potential client-side validation
            // Server-side validation is now primary
            console.log(`${type}: ${message}`);
        }

        // Auto-dismiss alerts after 5 seconds
        setTimeout(() => {
            const alerts = document.querySelectorAll('.alert');
            alerts.forEach(alert => {
                if (alert.classList.contains('alert-success')) {
                    alert.style.opacity = '0';
                    setTimeout(() => alert.remove(), 300);
                }
            });
        }, 5000);

        function isValidEmail(email) {
            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            return emailRegex.test(email);
        }
    </script>
</body>

</html>