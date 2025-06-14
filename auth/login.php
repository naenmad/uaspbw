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

            <!-- Alert placeholder untuk pesan error/success -->
            <div id="alert-container"></div>

            <form id="loginForm" method="POST" action="">
                <div class="mb-3">
                    <label for="email" class="form-label">Email</label>
                    <input type="email" class="form-control" id="email" name="email" placeholder="Masukkan email Anda"
                        required>
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
        // Simple form validation
        document.getElementById('loginForm').addEventListener('submit', function (e) {
            e.preventDefault();

            const email = document.getElementById('email').value;
            const password = document.getElementById('password').value;

            // Simple validation
            if (!email || !password) {
                showAlert('Mohon lengkapi semua field!', 'danger');
                return;
            }

            if (!isValidEmail(email)) {
                showAlert('Format email tidak valid!', 'danger');
                return;
            }

            // Simulate login process
            showAlert('Sedang memproses login...', 'info');

            // Here you would normally send data to server
            // For now, just show success message
            setTimeout(() => {
                showAlert('Login berhasil! Mengalihkan ke dashboard...', 'success');
                // Redirect to dashboard after 2 seconds
                setTimeout(() => {
                    window.location.href = '../dashboard/index.php';
                }, 2000);
            }, 1000);
        });

        function showAlert(message, type) {
            const alertContainer = document.getElementById('alert-container');
            alertContainer.innerHTML = `
                <div class="alert alert-${type} alert-dismissible fade show" role="alert">
                    ${message}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            `;
        }

        function isValidEmail(email) {
            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            return emailRegex.test(email);
        }
    </script>
</body>

</html>