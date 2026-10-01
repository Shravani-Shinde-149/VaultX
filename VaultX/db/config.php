<?php
$host = "localhost";
$user = "root";
$pass = "root"; // If you set a root password in MySQL Workbench, enter it here
$dbname = "ecm"; // Replace with your MySQL database name
$port = 3306; // Default MySQL port

mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

try {
    $conn = new mysqli($host, $user, $pass, $dbname, $port);
    $conn->set_charset("utf8mb4");
} catch (mysqli_sql_exception $e) {
    die("Database Connection Error: " . $e->getMessage());
}
?>