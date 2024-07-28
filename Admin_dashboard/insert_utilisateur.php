<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header('Location: /SahlaMahla/Home/login.php');
    exit();
}

header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");
?>
<?php 
require_once __DIR__ . '/cnx/cnx.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nom = $_POST['nom'];
    $email = $_POST['email'];
    $mot_de_passe = password_hash($_POST['mot_de_passe'], PASSWORD_DEFAULT); // Hash the password
    $telephone = $_POST['telephone'];
    $adresse = $_POST['adresse'];
    $role = $_POST['role'];
    $jours_semaine = $_POST['jour_semaine'];
    $heure_debut = $_POST['heure_debut'];
    $heure_fin = $_POST['heure_fin'];
    $services = $_POST['services']; // Fetch the services from the form

    try {
        // Créer une nouvelle connexion PDO
        $conn = new PDO($dsn, DB_USER, DB_PASSWORD);
        // Définir le mode des erreurs pour lever des exceptions
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        // Prepare the SQL statement
        $stmt = $conn->prepare("INSERT INTO utilisateurs (nom, email, mot_de_passe, téléphone, adresse, rôle) VALUES (:nom, :email, :mot_de_passe, :telephone, :adresse, :role)");
        
        // Bind parameters
        // ربط المعلمات بالقيم
        $stmt->bindParam(':nom', $nom);
        $stmt->bindParam(':email', $email);
        $stmt->bindParam(':mot_de_passe', $mot_de_passe);
        $stmt->bindParam(':telephone', $telephone);
        $stmt->bindParam(':adresse', $adresse);
        $stmt->bindParam(':role', $role);

        // Execute the statement
        $stmt->execute();
        $employe_id = $conn->lastInsertId();

        // Insert work hours
        $stmt = $conn->prepare("INSERT INTO horaires_travail (employé_id, jour_semaine, heure_debut, heure_fin) VALUES (:employe_id, :jour_semaine, :heure_debut, :heure_fin)");
        $stmt->bindParam(':employe_id', $employe_id);
        foreach ($jours_semaine as $jour) {
            $stmt->bindParam(':jour_semaine', $jour);
            $stmt->bindParam(':heure_debut', $heure_debut);
            $stmt->bindParam(':heure_fin', $heure_fin);
            $stmt->execute();
        }

        // Insert employee services
        $stmt = $conn->prepare("INSERT INTO employe_services (employe_id, service_id) VALUES (:employe_id, :service_id)");
        $stmt->bindParam(':employe_id', $employe_id);
        foreach ($services as $service_id) {
            $stmt->bindParam(':service_id', $service_id);
            $stmt->execute();
        }

        // Redirect with success message
        header("Location: employees.php?insert_success=1");
        exit();
    } catch (PDOException $e) {
        // Redirect with error message
        header("Location: employees.php?insert_error=1");
        exit();
    }
} else {
    // Redirect to the form if not a POST request
    header("Location: employees.php");
    exit();
}
?>
