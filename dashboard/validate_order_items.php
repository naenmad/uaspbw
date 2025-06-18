<?php
function validate_order_items($items)
{
    foreach ($items as $item) {
        if (!is_numeric($item['quantity']) || $item['quantity'] <= 0) {
            return "Quantity must be a positive number.";
        }
        if (!is_numeric($item['unit_price']) || $item['unit_price'] <= 0) {
            return "Unit price must be a positive number.";
        }   
    }
    return true; // All items are valid
}
?>
