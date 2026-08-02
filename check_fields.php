<?php
include "database.php";

// Get all columns from employees table
$query = "DESCRIBE employees";
$result = mysqli_query($conn, $query);

if ($result) {
    echo "Available fields in employees table:\n\n";
    while ($row = mysqli_fetch_assoc($result)) {
        echo $row['Field'] . " (" . $row['Type'] . ")\n";
    }
} else {
    echo "Error: " . mysqli_error($conn);
}
