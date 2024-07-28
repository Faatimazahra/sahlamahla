<?php
session_start();

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    // Redirect to login page if not logged in
    header('Location: /SahlaMahla/Home/login.php');
    exit();
}

require_once __DIR__ . '/cnx/cnx.php';

// Check if form data is posted
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Retrieve data from POST
    $employe_id = $_POST['employe_id'];
    $service_id = $_POST['service_id'];
    $client_id = $_SESSION['user_id']; // Assuming client_id is stored in session
    $date_service = date('Y-m-d'); // Current date
    $heure_service = date('H:i:s'); // Current time
    $lieu = ''; // You can set this based on your application logic, e.g., fetch from database or form

    try {
        // Connect to the database using PDO
        $conn = new PDO($dsn, DB_USER, DB_PASSWORD);
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        // SQL query to insert into commandes table
        $sql = "INSERT INTO commandes (service_id, client_id, employé_id, date_service, heure_service, lieu)
                SELECT
                    :service_id AS service_id,
                    :client_id AS client_id,
                    :employe_id AS employé_id,
                    :date_service AS date_service,
                    :heure_service AS heure_service,
                    u.adresse AS lieu
                FROM
                    utilisateurs u
                    JOIN employe_services es ON u.id = es.employe_id
                    JOIN services s ON es.service_id = s.id
                    JOIN horaires_travail ht ON u.id = ht.employé_id
                WHERE
                    u.id = :employe_id
                    AND s.id = :service_id
                    AND ht.jour_semaine = 'Lundi'
                    AND ht.heure_debut = '08:00'
                    AND ht.heure_fin = '17:00'"; // Adjust conditions as needed

        // Prepare the statement
        $stmt = $conn->prepare($sql);

        // Bind parameters
        $stmt->bindParam(':service_id', $service_id, PDO::PARAM_INT);
        $stmt->bindParam(':client_id', $client_id, PDO::PARAM_INT);
        $stmt->bindParam(':employe_id', $employe_id, PDO::PARAM_INT);
        $stmt->bindParam(':date_service', $date_service, PDO::PARAM_STR);
        $stmt->bindParam(':heure_service', $heure_service, PDO::PARAM_STR);

        // Execute the query
        $stmt->execute();

        // Redirect to a success page or back to the main page
        header('Location: /path/to/success_page.php');
        exit();
    } catch (PDOException $e) {
        echo "Connection failed: " . $e->getMessage();
        exit;
    }
}
?>
