<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Database Setup - Order Management System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-icons/1.11.0/font/bootstrap-icons.min.css"
        rel="stylesheet">
    <style>
        body {
            background-color: #f8f9fa;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        .setup-container {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }

        .setup-card {
            background: white;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
            padding: 40px;
            max-width: 600px;
            width: 100%;
        }

        .step-header {
            text-align: center;
            margin-bottom: 30px;
        }

        .step-icon {
            width: 80px;
            height: 80px;
            background: linear-gradient(45deg, #007bff, #0056b3);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 20px;
            color: white;
            font-size: 36px;
        }

        .form-control {
            border-radius: 8px;
            padding: 12px 15px;
            border: 1px solid #ddd;
            margin-bottom: 15px;
        }

        .btn-setup {
            background: linear-gradient(45deg, #007bff, #0056b3);
            border: none;
            padding: 12px 30px;
            border-radius: 8px;
            font-weight: 500;
            width: 100%;
        }

        .progress-step {
            display: flex;
            justify-content: space-between;
            margin-bottom: 30px;
        }

        .step {
            flex: 1;
            text-align: center;
            position: relative;
        }

        .step.active .step-number {
            background: #007bff;
            color: white;
        }

        .step.completed .step-number {
            background: #28a745;
            color: white;
        }

        .step-number {
            width: 30px;
            height: 30px;
            background: #e9ecef;
            border-radius: 50%;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
            margin-bottom: 8px;
        }

        .step-line {
            position: absolute;
            top: 15px;
            left: 50%;
            width: 100%;
            height: 2px;
            background: #e9ecef;
            z-index: -1;
        }

        .step:last-child .step-line {
            display: none;
        }

        .alert {
            border-radius: 8px;
            margin-bottom: 20px;
        }
    </style>
</head>

<body>
    <div class="setup-container">
        <div class="setup-card">
            <div class="step-header">
                <div class="step-icon">
                    <i class="bi bi-database"></i>
                </div>
                <h2>Database Setup</h2>
                <p class="text-muted">Setup database untuk Order Management System</p>
            </div>

            <!-- Progress Steps -->
            <div class="progress-step">
                <div class="step active" id="step1">
                    <div class="step-number">1</div>
                    <div class="step-line"></div>
                    <small>Koneksi</small>
                </div>
                <div class="step" id="step2">
                    <div class="step-number">2</div>
                    <div class="step-line"></div>
                    <small>Database</small>
                </div>
                <div class="step" id="step3">
                    <div class="step-number">3</div>
                    <small>Selesai</small>
                </div>
            </div>

            <!-- Alert Area -->
            <div id="alertArea"></div>

            <!-- Step 1: Database Connection -->
            <div id="connectionStep">
                <h4 class="mb-3">Pengaturan Koneksi Database</h4>
                <form id="connectionForm">
                    <div class="mb-3">
                        <label for="host" class="form-label">Host Database</label>
                        <input type="text" class="form-control" id="host" name="host" value="localhost" required>
                    </div>

                    <div class="mb-3">
                        <label for="username" class="form-label">Username</label>
                        <input type="text" class="form-control" id="username" name="username" value="root" required>
                    </div>

                    <div class="mb-3">
                        <label for="password" class="form-label">Password</label>
                        <input type="password" class="form-control" id="password" name="password">
                    </div>

                    <div class="mb-3">
                        <label for="database" class="form-label">Nama Database</label>
                        <input type="text" class="form-control" id="database" name="database" value="uaspbw_db"
                            required>
                    </div>

                    <button type="submit" class="btn btn-primary btn-setup">
                        <i class="bi bi-arrow-right me-2"></i>
                        Test Koneksi & Lanjutkan
                    </button>
                </form>
            </div>

            <!-- Step 2: Database Installation (Hidden) -->
            <div id="installStep" style="display: none;">
                <h4 class="mb-3">Instalasi Database</h4>
                <div class="text-center">
                    <div class="spinner-border text-primary mb-3" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                    <p>Sedang membuat database dan tabel...</p>
                    <div id="installProgress"></div>
                </div>
            </div>

            <!-- Step 3: Complete (Hidden) -->
            <div id="completeStep" style="display: none;">
                <div class="text-center">
                    <div class="step-icon bg-success">
                        <i class="bi bi-check-lg"></i>
                    </div>
                    <h4 class="text-success">Setup Berhasil!</h4>
                    <p class="text-muted mb-4">Database telah berhasil dibuat dan dikonfigurasi.</p>

                    <div class="row text-start">
                        <div class="col-12">
                            <div class="alert alert-info">
                                <h6><i class="bi bi-info-circle me-2"></i>Informasi Login Default:</h6>
                                <p class="mb-1"><strong>Username:</strong> admin</p>
                                <p class="mb-1"><strong>Email:</strong> admin@orderSystem.com</p>
                                <p class="mb-0"><strong>Password:</strong> password</p>
                            </div>
                        </div>
                    </div>

                    <div class="d-grid gap-2">
                        <a href="../auth/login.php" class="btn btn-success btn-setup">
                            <i class="bi bi-box-arrow-in-right me-2"></i>
                            Masuk ke Sistem
                        </a>
                        <a href="../index.html" class="btn btn-outline-secondary">
                            <i class="bi bi-house me-2"></i>
                            Kembali ke Beranda
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.getElementById('connectionForm').addEventListener('submit', function (e) {
            e.preventDefault();

            const formData = new FormData(this);

            // Show loading
            showAlert('Menguji koneksi database...', 'info');

            // Test connection
            fetch('setup_process.php', {
                method: 'POST',
                body: formData
            })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        showAlert('Koneksi berhasil! Memulai instalasi...', 'success');

                        // Move to step 2
                        setTimeout(() => {
                            document.getElementById('step1').classList.add('completed');
                            document.getElementById('step1').classList.remove('active');
                            document.getElementById('step2').classList.add('active');

                            document.getElementById('connectionStep').style.display = 'none';
                            document.getElementById('installStep').style.display = 'block';

                            // Start installation
                            installDatabase(formData);
                        }, 1000);
                    } else {
                        showAlert('Koneksi gagal: ' + data.message, 'danger');
                    }
                })
                .catch(error => {
                    showAlert('Error: ' + error.message, 'danger');
                });
        });

        function installDatabase(formData) {
            formData.append('action', 'install');

            fetch('setup_process.php', {
                method: 'POST',
                body: formData
            })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        // Move to step 3
                        document.getElementById('step2').classList.add('completed');
                        document.getElementById('step2').classList.remove('active');
                        document.getElementById('step3').classList.add('active');

                        document.getElementById('installStep').style.display = 'none';
                        document.getElementById('completeStep').style.display = 'block';
                    } else {
                        showAlert('Instalasi gagal: ' + data.message, 'danger');
                        // Go back to step 1
                        document.getElementById('step2').classList.remove('active');
                        document.getElementById('step1').classList.add('active');
                        document.getElementById('step1').classList.remove('completed');

                        document.getElementById('installStep').style.display = 'none';
                        document.getElementById('connectionStep').style.display = 'block';
                    }
                })
                .catch(error => {
                    showAlert('Error: ' + error.message, 'danger');
                });
        }

        function showAlert(message, type) {
            const alertArea = document.getElementById('alertArea');
            alertArea.innerHTML = `
                <div class="alert alert-${type} alert-dismissible fade show" role="alert">
                    ${message}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            `;
        }
    </script>
</body>

</html>