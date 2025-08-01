<?php
function log_activity($mysqli, $product_id, $activity_type, $quantity_change, $previous_quantity, $new_quantity, $staff_id, $notes = '', $reference_code = '') {
    $query = "INSERT INTO rpos_inventory_logs 
              (product_id, activity_type, quantity_change, previous_quantity, new_quantity, staff_id, notes, reference_code, activity_date) 
              VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW())";
    
    $stmt = $mysqli->prepare($query);
    $stmt->bind_param(
        'ssiiiiss',  // Changed first parameter from 'i' to 's' for string
        $product_id, 
        $activity_type, 
        $quantity_change, 
        $previous_quantity, 
        $new_quantity, 
        $staff_id,
        $notes, 
        $reference_code
    );
    
    return $stmt->execute();
}

function log_supplier_activity($mysqli, $activity_type, $staff_id, $notes = '', $reference_code = '') {
    $query = "INSERT INTO rpos_inventory_logs 
              (activity_type, staff_id, notes, reference_code, activity_date) 
              VALUES (?, ?, ?, ?, NOW())";
    
    $stmt = $mysqli->prepare($query);
    $stmt->bind_param(
        'siss', 
        $activity_type, 
        $staff_id,
        $notes, 
        $reference_code
    );
    
    return $stmt->execute();
}
?>