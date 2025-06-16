<?php
// Process login form
session_start();
require_once '../config/database.php';
require_once '../config/auth.php';

$error_message = '';
$success_message = '';

// Handle logout message
if (isset($_GET['message'])) {
    switch ($_GET['message']) {
        case 'logout_success':
            $success_message = 'Anda telah berhasil logout!';
            break;
        case 'logout_error':
            $error_message = 'Terjadi kesalahan saat logout.';
            break;
        case 'session_expired':
            $error_message = 'Session Anda telah berakhir. Silakan login kembali.';
            break;
        case 'access_denied':
            $error_message = 'Akses ditolak. Silakan login terlebih dahulu.';
            break;
    }
}

// Check if user is already logged in
if (is_logged_in()) {
    header('Location: ../dashboard/index.php');
    exit();
}

// Process login form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $csrf_token = $_POST['csrf_token'] ?? '';

    // Verify CSRF token
    if (!verify_csrf_token($csrf_token)) {
        $error_message = 'Token keamanan tidak valid. Silakan refresh halaman.';
    } else {
        $email = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';
        $remember = isset($_POST['remember']);

        if (empty($email) || empty($password)) {
            $error_message = 'Email dan password wajib diisi!';
        } else {
            $login_result = login_user($email, $password);

            if ($login_result['success']) {
                // Set remember me cookie if requested
                if ($remember) {
                    $remember_token = generate_remember_token();
                    setcookie('remember_token', $remember_token, time() + (30 * 24 * 60 * 60), '/'); // 30 days

                    // Save remember token to database
                    $update_sql = "UPDATE users SET remember_token = ? WHERE id = ?";
                    $stmt = $pdo->prepare($update_sql);
                    $stmt->execute([$remember_token, $_SESSION['user_id']]);
                }

                $success_message = $login_result['message'];

                // Redirect after short delay
                header('refresh:2;url=../dashboard/index.php');
            } else {
                $error_message = $login_result['message'];
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
    <title>Login - Sistem Pencatatan Order</title>

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
        }

        .card-login {
            background: white;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            padding: 40px;
            max-width: 400px;
            width: 100%;
        }

        .form-control {
            border-radius: 8px;
            padding: 12px 15px;
            border: 1px solid #ddd;
            margin-bottom: 15px;
        }

        .form-control:focus {
            border-color: #007bff;
            box-shadow: 0 0 0 0.2rem rgba(0, 123, 255, 0.25);
        }

        .btn-login {
            background-color: #007bff;
            border-color: #007bff;
            padding: 12px;
            font-weight: 500;
            border-radius: 8px;
            width: 100%;
        }

        .btn-login:hover {
            background-color: #0056b3;
            border-color: #0056b3;
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
            color: #007bff;
            text-decoration: none;
        }

        .text-center a:hover {
            text-decoration: underline;
        }

        .alert {
            border-radius: 8px;
            margin-bottom: 20px;
        }
    </style>
</head>

<body>
    <div class="container-main">
        <div class="card-login">
            <div class="text-center mb-3">
                <a href="../index.html" class="btn btn-secondary btn-back">
                    ← Kembali ke Beranda
                </a>
            </div>
            <h2 class="text-center">Masuk</h2>

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

            <form id="loginForm" method="POST" action="">
                <!-- CSRF Token -->
                <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
                <div class="mb-3">
                    <label for="email" class="form-label">Email atau Username</label>
                    <input type="text" class="form-control" id="email" name="email"
                        placeholder="Masukkan email atau username Anda"
                        value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>" required>
                </div>

                <div class="mb-3">
                    <label for="password" class="form-label">Password</label>
                    <input type="password" class="form-control" id="password" name="password"
                        placeholder="Masukkan password Anda" required>
                </div>

                <div class="mb-3 form-check">
                    <input type="checkbox" class="form-check-input" id="remember" name="remember">
                    <label class="form-check-label" for="remember">
                        Ingat saya
                    </label>
                </div>

                <button type="submit" class="btn btn-primary btn-login">
                    Masuk
                </button>
            </form>

            <hr class="my-4">

            <div class="text-center">
                <p class="mb-2">Belum punya akun?</p>
                <a href="register.php" class="btn btn-outline-success">
                    Daftar Sekarang
                </a>
            </div>

            <div class="text-center mt-3">
                <small class="text-muted">
                    <a href="#" onclick="alert('Fitur lupa password akan segera tersedia!')">
                        Lupa password?
                    </a>
                </small>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Enhanced form validation
        document.getElementById('loginForm').addEventListener('submit', function (e) {
            const email = document.getElementById('email').value.trim();
            const password = document.getElementById('password').value;

            // Basic validation
            if (!email || !password) {
                e.preventDefault();
                showAlert('Mohon lengkapi semua field!', 'danger');
                return false;
            }

            // Show loading state
            const submitBtn = this.querySelector('button[type="submit"]');
            const originalText = submitBtn.innerHTML;
            submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Memproses...';
            submitBtn.disabled = true;

            // Re-enable button after 5 seconds (in case of network issues)
            setTimeout(() => {
                submitBtn.innerHTML = originalText;
                submitBtn.disabled = false;
            }, 5000);
        });

        function showAlert(message, type) {
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
    </script>
</body>

</html>