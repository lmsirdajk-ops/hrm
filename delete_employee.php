<?php
require_once "auth.php";
require_post_authorization();
include "database.php";
require_role_access($conn, 'modify');

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['id'])) {
    $eid = intval($_POST['id']);

    // Delete employee record
    $query = "DELETE FROM employees WHERE eid = $eid";
    $result = mysqli_query($conn, $query);

    if ($result) {
        echo json_encode(['success' => true, 'message' => 'Employee deleted successfully']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Error deleting employee: ' . mysqli_error($conn)]);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request']);
}
