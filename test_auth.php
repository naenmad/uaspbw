<?php
/**
 * Test file untuk mengecek si                                <?php 
                                $current_user = get_logged_in_user();em otentikasi
 * Akses: http://localhost/uaspbw/test_auth.php
 */

session_start();
require_once 'config/database.php';
require_once 'config/auth.php';

?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Test Sistem Autentikasi</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body>
    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header">
                        <h3>Test Sistem Autentikasi</h3>
                    </div>
                    <div class="card-body">
                        <h5>Status Login:</h5>
                        <?php if (is_logged_in()): ?>
                            <div class="alert alert-success">
                                <h6>✅ User sudah login!</h6>
                                <p><strong>ID:</strong> <?php echo $_SESSION['user_id']; ?></p>
                                <p><strong>Username:</strong> <?php echo $_SESSION['username']; ?></p>
                                <p><strong>Full Name:</strong> <?php echo $_SESSION['full_name']; ?></p>
                                <p><strong>Email:</strong> <?php echo $_SESSION['email']; ?></p>
                                <p><strong>Role:</strong> <?php echo $_SESSION['role']; ?></p>

                                <hr>
                                <h6>Data dari Database:</h6>
                                <?php
                                $current_user = get_logged_in_user();
                                if ($current_user):
                                    ?>
                                    <p><strong>Full Name:</strong> <?php echo htmlspecialchars($current_user['full_name']); ?>
                                    </p>
                                    <p><strong>Email:</strong> <?php echo htmlspecialchars($current_user['email']); ?></p>
                                    <p><strong>Phone:</strong>
                                        <?php echo htmlspecialchars($current_user['phone'] ?? 'Tidak ada'); ?></p>
                                    <p><strong>Address:</strong>
                                        <?php echo htmlspecialchars($current_user['address'] ?? 'Tidak ada'); ?></p>
                                    <p><strong>Created:</strong> <?php echo htmlspecialchars($current_user['created_at']); ?>
                                    </p>
                                <?php else: ?>
                                    <div class="alert alert-warning">Gagal mengambil data user dari database</div>
                                <?php endif; ?>
                            </div>

                            <div class="d-flex gap-2">
                                <a href="dashboard/index.php" class="btn btn-primary">Ke Dashboard</a>
                                <a href="auth/logout.php" class="btn btn-danger">Logout</a>
                            </div>
                        <?php else: ?>
                            <div class="alert alert-warning">
                                <h6>❌ User belum login</h6>
                                <p>Silakan login terlebih dahulu untuk mengakses sistem.</p>
                            </div>

                            <div class="d-flex gap-2">
                                <a href="auth/login.php" class="btn btn-primary">Login</a>
                                <a href="auth/register.php" class="btn btn-success">Register</a>
                            </div>
                        <?php endif; ?>

                        <hr>

                        <h5>Test Fungsi Autentikasi:</h5>
                        <div class="table-responsive">
                            <table class="table table-sm">
                                <tr>
                                    <td><code>is_logged_in()</code></td>
                                    <td><?php echo is_logged_in() ? '✅ true' : '❌ false'; ?></td>
                                </tr>
                                <tr>
                                    <td><code>get_logged_in_user()</code></td>
                                    <td><?php echo get_logged_in_user() ? '✅ berhasil' : '❌ gagal'; ?></td>
                                </tr>
                                <tr>
                                    <td><code>check_user_role('admin')</code></td>
                                    <td><?php echo check_user_role('admin') ? '✅ true' : '❌ false'; ?></td>
                                </tr>
                                <tr>
                                    <td><code>check_user_role('user')</code></td>
                                    <td><?php echo check_user_role('user') ? '✅ true' : '❌ false'; ?></td>
                                </tr>
                            </table>
                        </div>

                        <hr>

                        <h5>Test CSRF Token:</h5>
                        <?php $csrf_token = generate_csrf_token(); ?>
                        <p><strong>CSRF Token:</strong> <code><?php echo $csrf_token; ?></code></p>
                        <p><strong>Verifikasi:</strong>
                            <?php echo verify_csrf_token($csrf_token) ? '✅ valid' : '❌ invalid'; ?></p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>

</html>