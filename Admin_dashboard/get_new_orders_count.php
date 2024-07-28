<?php
session_start();
require 'db_connection.php'; // Include your database connection

// Fetch the count of new orders
$query = "SELECT COUNT(*) as new_orders_count FROM commandes WHERE status = 'new'";
$result = mysqli_query($conn, $query);
$row = mysqli_fetch_assoc($result);
$new_orders_count = $row['new_orders_count'];

echo json_encode(['new_orders_count' => $new_orders_count]);
?>
