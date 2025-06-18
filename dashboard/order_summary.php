<?php
require_once __DIR__ . '/../config/database.php';

$query = "SELECT * FROM order_summary"; // Use the order_summary view
$result = $conn->query($query);

if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        echo "<tr>";
        echo "<td>" . $row['order_number'] . "</td>";
        echo "<td>" . $row['customer_name'] . "</td>";
        echo "<td>" . $row['order_date'] . "</td>";
        echo "<td>" . $row['status'] . "</td>";
        echo "<td>" . $row['payment_status'] . "</td>";
        echo "<td>" . $row['total_amount'] . "</td>";
        echo "<td>" . $row['total_items'] . "</td>";
        echo "</tr>";
    }
} else {
    echo "No orders found.";
}
?>
