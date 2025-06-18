<?php
require_once __DIR__ . '/../config/database.php';

function create_order($order_data, $items)
{
    global $pdo;

    try {
        // Begin transaction
        $pdo->beginTransaction();

        $order_number = generateOrderNumber();

        // Insert main order
        $order_number = generateOrderNumber(); // ← Tambahkan ini

        $stmt = $pdo->prepare("INSERT INTO orders (order_number, customer_id, order_date, status, notes) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([
            $order_number,
            $order_data['customer_id'],
            $order_data['order_date'],
            $order_data['status'],
            $order_data['notes']
        ]);


        $order_id = $pdo->lastInsertId();

        // Insert order items
        $stmt_item = $pdo->prepare("INSERT INTO order_items (order_id, product_name, quantity, unit_price, total_price) VALUES (?, ?, ?, ?, ?)");

        foreach ($items as $item) {
            $total_price = $item['quantity'] * $item['unit_price'];
            $stmt_item->execute([
                $order_id,
                $item['product_name'],
                $item['quantity'],
                $item['unit_price'],
                $total_price
            ]);
        }

        // Commit transaction
        $pdo->commit();
        return true;
    } catch (Exception $e) {
        $pdo->rollBack();
    // Tambahkan ini untuk sementara menampilkan pesan error
        echo "<pre>Error: " . $e->getMessage() . "</pre>";
        return false;
    }
}
?>
