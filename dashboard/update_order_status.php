<?php
function update_order_status($order_id, $new_status, $notes, $user_id)
{
    global $conn;
    $stmt = $conn->prepare("CALL UpdateOrderStatus(?, ?, ?, ?)");
    $stmt->bind_param("isis", $order_id, $new_status, $notes, $user_id);
    $stmt->execute();
    $stmt->close();
}
?>
