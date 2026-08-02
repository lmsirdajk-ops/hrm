<?php

$hostName = "localhost";
$dbUser = "root";
$dbPassword = "";
$dbName = "ird_hrmisa_be";
$conn = mysqli_connect($hostName, $dbUser, $dbPassword, $dbName);
if (!$conn) {
    die("Something went wrong;");
}
