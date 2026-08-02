<?php
require_once "auth.php";
require_post_authorization();
include "database.php";
require_role_access($conn, 'modify');

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['id']) && isset($_POST['status'])) {
    $eid = intval($_POST['id']);
    $status = intval($_POST['status']);

    // Validate status value (should be 0 or 1)
    if ($status != 0 && $status != 1) {
        echo json_encode(['success' => false, 'message' => 'Invalid status value']);
        exit;
    }

    // Update employee status
    $query = "UPDATE employees SET eServiceNature = $status WHERE eid = $eid";
    $result = mysqli_query($conn, $query);

    if ($result) {
        echo json_encode(['success' => true, 'message' => 'Employee status updated successfully']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Error updating employee: ' . mysqli_error($conn)]);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request']);
}
