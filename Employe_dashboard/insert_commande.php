<?php
session_start();
require_once __DIR__ . '/cnx/cnx.php';

// Enable error reporting
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Check if the form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Retrieve form data
    $service_id = $_POST['service_id'];
    $employé_id = $_POST['employé_id'];
    $client_id = $_POST['client_id'];  // Retrieve the client ID from the form
    $date_service = $_POST['date_service'];
    $heure_service = $_POST['heure_service'];
    $lieu = $_POST['lieu'];
    $mode_paiement = $_POST['mode_paiement'];

    // Insert into the commandes table
    $stmt = $conn->prepare("INSERT INTO commandes (service_id, client_id, employé_id, date_service, heure_service, lieu, mode_paiement) VALUES (?, ?, ?, ?, ?, ?, ?)");
    
    if ($stmt === false) {
        die('Prepare failed: ' . htmlspecialchars($conn->error));
    }

    $stmt->bind_param("iiissss", $service_id, $client_id, $employé_id, $date_service, $heure_service, $lieu, $mode_paiement);

    if ($stmt->execute()) {
        $_SESSION['success_message'] = "New command added successfully";
    } else {
        $_SESSION['success_message'] = "Error: " . $stmt->error;
    }

    // Close the statement and connection
    $stmt->close();
    $conn->close();

    // Redirect back to the form page
    header("Location: commandes.php");
    exit();
} else {
    // Redirect to the form page if the request method is not POST
    header("Location: commandes.php");
    exit();
}
?>
