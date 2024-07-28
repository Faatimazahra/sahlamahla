<?php
session_start();
require_once __DIR__ . '/cnx/cnx.php'; // Adjust as per your file structure

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['reservation_id'])) {
    $reservationId = intval($_POST['reservation_id']);

    // Fetch reservation details
    $query = "SELECT reservation.*, services.nom as service_nom, 
              utilisateurs.nom as employe_nom, 
              horaires_travail.jour_semaine,
              horaires_travail.heure_debut, horaires_travail.heure_fin,
              clients.nom as client_nom, clients.téléphone as client_téléphone
              FROM reservation
              JOIN services ON reservation.service_id = services.id
              JOIN utilisateurs ON reservation.employee_id = utilisateurs.id
              JOIN horaires_travail ON reservation.employee_id = horaires_travail.employé_id
              JOIN utilisateurs as clients ON reservation.client_id = clients.id
              WHERE reservation.reservation_id = :reservation_id";

    $stmt = $conn->prepare($query);
    $stmt->bindParam(':reservation_id', $reservationId, PDO::PARAM_INT);
    $stmt->execute();
    $row = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($row) {
        echo "<p><strong>Service:</strong> " . htmlspecialchars($row['service_nom'], ENT_QUOTES, 'UTF-8') . "</p>
              <p><strong>Employé:</strong> " . htmlspecialchars($row['employe_nom'], ENT_QUOTES, 'UTF-8') . "</p>
              <p><strong>Horaires de travail:</strong> " . htmlspecialchars($row['jour_semaine'], ENT_QUOTES, 'UTF-8') . " " . htmlspecialchars($row['heure_debut'], ENT_QUOTES, 'UTF-8') . " - " . htmlspecialchars($row['heure_fin'], ENT_QUOTES, 'UTF-8') . "</p>
              <p><strong>Lieu:</strong> " . htmlspecialchars($row['lieu'], ENT_QUOTES, 'UTF-8') . "</p>
              <p><strong>Mode de paiement:</strong> " . htmlspecialchars($row['mode_paiement'], ENT_QUOTES, 'UTF-8') . "</p>
              <p><strong>Client:</strong> " . htmlspecialchars($row['client_nom'], ENT_QUOTES, 'UTF-8') . " (" . htmlspecialchars($row['client_téléphone'], ENT_QUOTES, 'UTF-8') . ")</p>";
    } else {
        echo "<p>Aucune réservation trouvée.</p>";
    }
} else {
    echo "<p>Invalid request.</p>";
}
?>
