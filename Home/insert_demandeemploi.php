<?php

// Include database connection
require_once __DIR__ . '/cnx/cnx.php';

// Database connection using PDO
try {
    $conn = new PDO($dsn, DB_USER, DB_PASSWORD);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    echo "Connection failed: " . $e->getMessage();
    exit();
}

// Process form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validate and sanitize inputs
    $nom = filter_var($_POST['nom'], FILTER_SANITIZE_STRING);
    $email = filter_var($_POST['email'], FILTER_SANITIZE_EMAIL);
    $téléphone = filter_var($_POST['téléphone'], FILTER_SANITIZE_STRING);
    $adresse = filter_var($_POST['adresse'], FILTER_SANITIZE_STRING);
    $service = filter_var($_POST['service'], FILTER_SANITIZE_STRING);

    // File upload handling for CV PDF
    if ($_FILES['cv_pdf']['error'] === UPLOAD_ERR_OK) {
        $cv_pdf_name = $_FILES['cv_pdf']['name'];
        $cv_pdf_tmp_name = $_FILES['cv_pdf']['tmp_name'];
        $target_dir = __DIR__ . '/cv_pdfs/'; // Directory where PDFs will be stored
        $cv_pdf_relative_path = 'cv_pdfs/' . basename($cv_pdf_name);

        // Create target directory if it doesn't exist
        if (!is_dir($target_dir)) {
            mkdir($target_dir, 0777, true);
        }

        // Move uploaded file to desired directory
        if (move_uploaded_file($cv_pdf_tmp_name, $target_dir . basename($cv_pdf_name))) {
            try {
                // SQL query to insert into `demandeemploi` table
                $query = "INSERT INTO demandeemploi (nom, email, téléphone, adresse, service, cv_pdf) VALUES (?, ?, ?, ?, ?, ?)";
                $stmt = $conn->prepare($query);
                $stmt->execute([$nom, $email, $téléphone, $adresse, $service, $cv_pdf_relative_path]);
        
                // Redirect with success message
                header('Location: /SahlaMahla/Home/offreEmploi.php?insert_success=1');
                exit();
            } catch (PDOException $e) {
                // Redirect with error message if insertion fails
                header('Location: /SahlaMahla/Home/offreEmploi.php?insert_error=1&error_message=' . urlencode($e->getMessage()));
                exit();
            }
        }
         else {
            // Redirect with error message if file upload fails
            header('Location: /SahlaMahla/Home/offreEmploi.php?file_upload_error=1');
            exit();
        }
    } 
} else {
    // If someone tries to access this page directly without POST method, redirect to home
    header('Location: /SahlaMahla/Home/offreEmploi.php');
    exit();
}
?>
