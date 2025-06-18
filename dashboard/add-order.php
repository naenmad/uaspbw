<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();
require_once '../config/database.php';
require_once '../config/auth.php';
require_once '../includes/order-functions.php';
require_login();

$success = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $order_data = [
        'customer_id' => $_POST['customer_id'],
        'order_date' => $_POST['order_date'],
        'status' => 'Pending',
        'notes' => $_POST['notes'] ?? ''
    ];

    $items = [];
    if (isset($_POST['product_name'])) {
        for ($i = 0; $i < count($_POST['product_name']); $i++) {
            $items[] = [
                'product_name' => $_POST['product_name'][$i],
                'quantity' => (int)$_POST['quantity'][$i],
                'unit_price' => (float)$_POST['unit_price'][$i]
            ];
        }
    }

    if (create_order($order_data, $items)) {
        $success = "Order berhasil ditambahkan.";
    } else {
        $error = "Gagal menambahkan order.";
    }
}
?>

<!DOCTYPE html>
<?php
require_once __DIR__ . '/../config/database.php';

$stmt = $pdo->query("SELECT id, name FROM customers");
$customers = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<html>
<head>
    <meta charset="UTF-8">
    <title>Tambah Order</title>
    <style>
        body { font-family: Arial, sans-serif; padding: 20px; }
        .form-group { margin-bottom: 15px; }
        label { display: block; font-weight: bold; }
        input, textarea { width: 100%; padding: 8px; }
        .items-container { margin-top: 20px; }
        .success { color: green; }
        .error { color: red; }
        button { padding: 10px 15px; }
    </style>
</head>

<body>

<h2>Tambah Order</h2>

<?php if ($success): ?>
    <p class="success"><?= $success ?></p>
<?php elseif ($error): ?>
    <p class="error"><?= $error ?></p>
<?php endif; ?>

<form method="POST">
    <div class="form-group">
    <label for="customer_id">Customer</label>
    <select name="customer_id" id="customer_id" required>
        <option value="">-- Pilih Customer --</option>
        <?php foreach ($customers as $customer): ?>
            <option value="<?= htmlspecialchars($customer['id']) ?>">
                <?= htmlspecialchars($customer['id']) ?> - <?= htmlspecialchars($customer['name']) ?>
            </option>
        <?php endforeach; ?>
    </select>
</div>

    <div class="form-group">
        <label for="order_date">Tanggal Order</label>
        <input type="date" name="order_date" id="order_date" required>
    </div>

    <div class="form-group">
        <label for="notes">Catatan</label>
        <textarea name="notes" id="notes"></textarea>
    </div>

    <div class="items-container">
        <h3>Item Produk</h3>
        <div class="form-group">
            <label>Nama Produk</label>
            <input type="text" name="product_name[]" required>

            <label>Jumlah</label>
            <input type="number" name="quantity[]" required>

            <label>Harga Satuan</label>
            <input type="number" name="unit_price[]" step="0.01" required>
        </div>
        <!-- Bisa dikembangkan agar item bisa bertambah dinamis via JavaScript -->
    </div>

    <button type="submit">Tambah Order</button>
</form>

</body>

</html>
