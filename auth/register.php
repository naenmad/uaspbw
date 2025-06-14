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

            <!-- Alert placeholder untuk pesan error/success -->
            <div id="alert-container"></div>

            <form id="registerForm" method="POST" action="">
                <div class="mb-3">
                    <label for="fullname" class="form-label">Nama Lengkap</label>
                    <input type="text" class="form-control" id="fullname" name="fullname"
                        placeholder="Masukkan nama lengkap" required>
                </div>

                <div class="mb-3">
                    <label for="email" class="form-label">Email</label>
                    <input type="email" class="form-control" id="email" name="email" placeholder="Masukkan email Anda"
                        required>
                </div>

                <div class="mb-3">
                    <label for="username" class="form-label">Username</label>
                    <input type="text" class="form-control" id="username" name="username"
                        placeholder="Masukkan username" required>
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
                    <input type="tel" class="form-control" id="phone" name="phone" placeholder="Masukkan nomor telepon">
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
        });

        // Form submission
        document.getElementById('registerForm').addEventListener('submit', function (e) {
            e.preventDefault();

            const formData = {
                fullname: document.getElementById('fullname').value,
                email: document.getElementById('email').value,
                username: document.getElementById('username').value,
                password: document.getElementById('password').value,
                confirm_password: document.getElementById('confirm_password').value,
                phone: document.getElementById('phone').value,
                terms: document.getElementById('terms').checked
            };

            // Validation
            if (!validateForm(formData)) {
                return;
            }

            // Simulate registration process
            showAlert('Sedang memproses pendaftaran...', 'info');

            // Here you would normally send data to server
            // For now, just show success message
            setTimeout(() => {
                showAlert('Pendaftaran berhasil! Mengalihkan ke halaman login...', 'success');
                // Redirect to login after 2 seconds
                setTimeout(() => {
                    window.location.href = 'login.php';
                }, 2000);
            }, 1000);
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
        }

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