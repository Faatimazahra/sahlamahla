<?php
const DB_USER = 'root';         // Database username
const DB_PASSWORD = '';         // Database password
const DB_HOST = 'localhost';    // Database host
const DB_NAME = 'sahla';        // Database name

// Assign values of constants to variables to build the DSN (Data Source Name)
$dbhost = DB_HOST;              // Database host
$dbname = DB_NAME;              // Database name
$dsn = "mysql:host=$dbhost;dbname=$dbname";

try {
    // PDO connection
    $conn = new PDO($dsn, DB_USER, DB_PASSWORD);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    echo "Connection failed: " . $e->getMessage();
    exit();
}
?>
