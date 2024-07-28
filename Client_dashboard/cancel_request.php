<?php
session_start();

// Vérification de la connexion de l'utilisateur
if (!isset($_SESSION['user_id'])) {
    header('Location: /SahlaMahla/Home/login.php');
    exit();
}

require_once __DIR__ . '/cnx/cnx.php'; // Inclure votre fichier de connexion à la base de données
 
try {
    $conn = new PDO($dsn, DB_USER, DB_PASSWORD);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    echo "Connexion échouée : " . $e->getMessage();
    exit;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $demande_id = $_POST['demande_id'];
    $client_id = $_SESSION['user_id'];

    try {
        // Vérifier si la demande appartient au client
        $stmt = $conn->prepare("SELECT service_id, statut FROM demandes WHERE id = ? AND client_id = ?");
        $stmt->execute([$demande_id, $client_id]);
        $demande = $stmt->fetch();

        if (!$demande) {
            echo "Demande non trouvée ou non autorisée.";
            exit;
        }

        if ($demande['statut'] != 'En attente') {
            echo "Vous ne pouvez annuler que les demandes en attente.";
            exit;
        }

        // Annuler la demande
        $stmt = $conn->prepare("UPDATE demandes SET statut = 'Annulé' WHERE id = ?");
        $stmt->execute([$demande_id]);

        // Mettre à jour le statut du service à 'Disponible' si nécessaire
        $stmt = $conn->prepare("SELECT COUNT(*) AS count FROM demandes WHERE service_id = ? AND statut = 'En attente'");
        $stmt->execute([$demande['service_id']]);
        $count = $stmt->fetchColumn();

        if ($count == 0) {
            $stmt = $conn->prepare("UPDATE services SET statut = 'Disponible' WHERE id = ?");
            $stmt->execute([$demande['service_id']]);
        }

        echo "Demande annulée avec succès.";
    } catch (PDOException $e) {
        echo "Erreur lors de l'annulation de la demande : " . $e->getMessage();
    }
}
?>
