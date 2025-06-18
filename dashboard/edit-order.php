<?php
function update_order($order_id, $status, $subtotal, $total_amount)
{
    global $conn;
    $stmt = $conn->prepare("UPDATE orders SET status = ?, subtotal = ?, total_amount = ? WHERE id = ?");
    $stmt->bind_param("sdii", $status, $subtotal, $total_amount, $order_id);
    $stmt->execute();
    $stmt->close();
}
