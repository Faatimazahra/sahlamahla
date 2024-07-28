<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header('Location: /SahlaMahla/Home/login.php');
    exit();
}

require_once __DIR__ . '/cnx/cnx.php';

try {
    $conn = new PDO($dsn, DB_USER, DB_PASSWORD);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    echo "Connexion échouée : " . $e->getMessage();
    exit;
}

// Récupérer l'ID de la facture depuis les paramètres GET
$factureId = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($factureId > 0) {
    $query = "SELECT commandes.*, services.nom AS service_nom, 
              utilisateurs.nom AS employe_nom, horaires_travail.jour_semaine,
              horaires_travail.heure_debut, horaires_travail.heure_fin
              FROM commandes
              JOIN services ON commandes.service_id = services.id
              JOIN utilisateurs ON commandes.employé_id = utilisateurs.id
              JOIN horaires_travail ON utilisateurs.id = horaires_travail.employé_id
              WHERE commandes.id = :id";

    $stmt = $conn->prepare($query);
    $stmt->bindValue(':id', $factureId, PDO::PARAM_INT);
    $stmt->execute();

    $facture = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($facture) {
        // Afficher les détails de la facture
        echo "<h1>Détails de la Facture</h1>";
        echo "<p><strong>Service:</strong> " . htmlspecialchars($facture['service_nom']) . "</p>";
        echo "<p><strong>Employé:</strong> " . htmlspecialchars($facture['employe_nom']) . "</p>";
        echo "<p><strong>Jour de la semaine:</strong> " . htmlspecialchars($facture['jour_semaine']) . " " . htmlspecialchars($facture['heure_debut']) . " - " . htmlspecialchars($facture['heure_fin']) . "</p>";
        echo "<p><strong>Lieu:</strong> " . htmlspecialchars($facture['lieu']) . "</p>";
        echo "<p><strong>Mode paiement:</strong> " . htmlspecialchars($facture['mode_paiement']) . "</p>";
    } else {
        echo "<p>Facture non trouvée.</p>";
    }
} else {
    echo "<p>ID de facture invalide.</p>";
}
?>
