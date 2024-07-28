<?php
session_start();

// Vérification de la connexion de l'utilisateur
if (!isset($_SESSION['user_id'])) {
    header('Location: /SahlaMahla/Home/login.php');
    exit();
}

require_once __DIR__ . '/header.php'; // Inclure votre en-tête
require_once __DIR__ . '/cnx/cnx.php'; // Inclure votre fichier de connexion à la base de données

try {
    $conn = new PDO($dsn, DB_USER, DB_PASSWORD);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    echo "Connexion échouée : " . $e->getMessage();
    exit;
}

$successMessage = '';
$errorMessage = '';

// Gérer l'annulation de la demande
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['annuler_demande'])) {
    $demande_id = $_POST['demande_id'];
    $client_id = $_SESSION['user_id'];

    try {
        // Vérifier si la demande appartient au client
        $stmt = $conn->prepare("SELECT service_id, statut FROM demandes WHERE id = ? AND client_id = ?");
        $stmt->execute([$demande_id, $client_id]);
        $demande = $stmt->fetch();

        if (!$demande) {
            $errorMessage = 'Demande non trouvée ou non autorisée.';
        } elseif ($demande['statut'] != 'En attente') {
            $errorMessage = 'Vous ne pouvez annuler que les demandes en attente.';
        } else {
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

            $successMessage = 'Demande annulée avec succès.';
        }
    } catch (PDOException $e) {
        $errorMessage = 'Erreur lors de l\'annulation de la demande : ' . $e->getMessage();
    }
}

// Récupérer les demandes du client depuis la base de données
try {
    $client_id = $_SESSION['user_id'];
    $stmt = $conn->prepare("SELECT d.id, s.nom AS service_nom, d.statut, d.date_demande
                             FROM demandes d
                             JOIN services s ON d.service_id = s.id
                             WHERE d.client_id = ?");
    $stmt->execute([$client_id]);
    $demandes = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    echo "Erreur lors de la récupération des demandes : " . $e->getMessage();
    exit;
}
?>

<!-- HTML Section to Display Requests -->
<div class="row tm-content-row tm-mt-big">
    <div class="col-xl-13 col-lg-12 tm-md-12 tm-sm-12 tm-col">
        <div class="bg-white tm-block h-100">
            <h2 class="tm-block-title">Mes Demandes</h2>
            <?php if (!empty($successMessage)) : ?>
                <div class="alert alert-success"><?php echo htmlspecialchars($successMessage); ?></div>
            <?php endif; ?>
            <?php if (!empty($errorMessage)) : ?>
                <div class="alert alert-danger"><?php echo htmlspecialchars($errorMessage); ?></div>
            <?php endif; ?>
            <?php if (!empty($demandes)) : ?>
                <?php foreach ($demandes as $demande) : ?>
                    <div class="mb-4">
                        <p><strong>Service :</strong> <?php echo htmlspecialchars($demande['service_nom']); ?></p>
                        <p><strong>Date de Demande :</strong> <?php echo htmlspecialchars($demande['date_demande']); ?></p>
                        <p><strong>Statut :</strong> <?php echo htmlspecialchars($demande['statut']); ?></p>
                        <?php if ($demande['statut'] == 'En attente') : ?>
                            <form method="POST" action="">
                                <input type="hidden" name="demande_id" value="<?php echo htmlspecialchars($demande['id']); ?>">
                                <button type="submit" name="annuler_demande" class="btn btn-danger btn-sm">Annuler</button>
                            </form>
                        <?php endif; ?>
                        <hr>
                    </div>
                <?php endforeach; ?>
            <?php else : ?>
                <p>Vous n'avez fait aucune demande pour le moment.</p>
            <?php endif; ?>
        </div>
    </div>
</div>


<?php require_once __DIR__ . '/footer.php'; // Inclure votre pied de page ?>
