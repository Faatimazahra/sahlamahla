<?php
session_start();

// Vérifier si l'utilisateur est connecté
if (!isset($_SESSION['user_id'])) {
    header('Location: /SahlaMahla/Home/login.php');
    exit();
}

require_once __DIR__ . '/header.php'; // Inclure le fichier d'en-tête
require_once __DIR__ . '/cnx/cnx.php'; // Inclure le fichier de connexion à la base de données
require_once __DIR__ . '/vendor/autoload.php'; // Inclure le fichier autoload de Composer

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

try {
    $conn = new PDO($dsn, DB_USER, DB_PASSWORD);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    echo "Échec de la connexion : " . $e->getMessage();
    exit;
}

// Traitement de la demande de service
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $service_id = $_POST['service_id'];
    $employe_id = $_POST['employe_id'];
    $client_id = $_SESSION['user_id'];
    $mode_paiement = $_POST['mode_paiement'];

    try {
        // Vérifier si l'employé est disponible
        $stmt = $conn->prepare("
            SELECT COUNT(*) 
            FROM demandes 
            WHERE employe_id = ? 
              AND statut = 'En attente'
        ");
        $stmt->execute([$employe_id]);
        $employeBusy = $stmt->fetchColumn();

        if ($employeBusy > 0) {
            echo "Cet employé est déjà demandé par un autre client.";
            exit;
        }

        // Insérer la demande dans la table demandes avec le mode de paiement
        $stmt = $conn->prepare("INSERT INTO demandes (service_id, client_id, employe_id, statut, mode_paiement) VALUES (?, ?, ?, 'En attente', ?)");
        $stmt->execute([$service_id, $client_id, $employe_id, $mode_paiement]);

        if ($mode_paiement === 'Virement') {
            // Envoyer l'email avec PHPMailer
            $mail = new PHPMailer(true);

            try {
                // Configurations du serveur SMTP
                $mail->isSMTP();
                $mail->Host       = 'smtp.gmail.com';
                $mail->SMTPAuth   = true;
                $mail->Username   = 'sahlamahla35@gmail.com';
                $mail->Password   = 'sppw vpje mffj pgev';
                $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
                $mail->Port       = 587;

                // Expéditeur et destinataire
                $mail->setFrom('sahlamahla35@gmail.com', 'Sahla Mahla');
                $mail->addAddress($_SESSION['user_email']); // Assurez-vous que l'email de l'utilisateur est stocké dans la session

                // Contenu de l'email
                $mail->isHTML(true);
                $mail->Subject = 'Informations de paiement pour votre demande de service';
                $mail->Body    = "
                    <html>
                    <head>
                        <title>Informations de paiement</title>
                    </head>
                    <body>
                        <p>Bonjour,</p>
                        <p>Merci pour votre demande de service. Vous avez choisi de payer par virement bancaire.</p>
                        <p>Veuillez utiliser les informations suivantes pour effectuer le transfert :</p>
                        <p><strong>Nom de l'entreprise :</strong> Sahla Mahla</p>
                        <p><strong>Numéro de compte bancaire :</strong> 1234567890</p>
                        <p>Merci de nous contacter si vous avez des questions.</p>
                        <p>Cordialement,</p>
                        <p>Votre équipe de service</p>
                    </body>
                    </html>
                ";

                $mail->send();
                echo "Demande envoyée avec succès et les informations de paiement ont été envoyées à votre email.";
            } catch (Exception $e) {
                echo "Demande envoyée avec succès, mais il y a eu un problème lors de l'envoi des informations de paiement. Erreur: {$mail->ErrorInfo}";
            }
        } else {
            echo "Demande envoyée avec succès.";
        }

    } catch (PDOException $e) {
        echo "Erreur lors de la demande : " . $e->getMessage();
    }
}

// Rechercher des employés et des services en fonction des critères
$search_service = isset($_GET['search_service']) ? $_GET['search_service'] : '';
$search_day = isset($_GET['search_day']) ? $_GET['search_day'] : '';
$search_address = isset($_GET['search_address']) ? $_GET['search_address'] : '';

// Construire la requête SQL avec des filtres
$query = "
    SELECT u.id AS employe_id, u.nom AS employe_nom, u.téléphone AS employe_telephone, u.adresse AS employe_adresse,
           s.id AS service_id, s.nom AS service_nom, s.description AS service_description, 
           s.url_image AS service_url_image, s.prix AS service_prix,
           ht.jour_semaine, ht.heure_debut, ht.heure_fin
    FROM utilisateurs u
    JOIN employe_services es ON u.id = es.employe_id
    JOIN services s ON es.service_id = s.id
    LEFT JOIN horaires_travail ht ON u.id = ht.employé_id
    WHERE s.statut = 'Disponible' AND u.rôle = 'employé'
";

$conditions = [];
$params = [];

if (!empty($search_service)) {
    $conditions[] = "s.nom LIKE ?";
    $params[] = '%' . $search_service . '%';
}

if (!empty($search_day)) {
    $conditions[] = "ht.jour_semaine = ?";
    $params[] = $search_day;
}

if (!empty($search_address)) {
    $conditions[] = "u.adresse LIKE ?";
    $params[] = '%' . $search_address . '%';
}

if (count($conditions) > 0) {
    $query .= " AND " . implode(' AND ', $conditions);
}

try {
    $stmt = $conn->prepare($query);
    $stmt->execute($params);
    $employes = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    echo "Erreur lors de la récupération des données : " . $e->getMessage();
    exit;
}
?>

<div class="row tm-content-row tm-mt-big">
    <div class="bg-white tm-block h-100">
        <!-- Section HTML pour afficher le formulaire de recherche et les résultats -->
        <form action="" method="GET">
            <label for="search_service">Service:</label>
            <input type="text" name="search_service" id="search_service" placeholder="Nom du service" value="<?php echo htmlspecialchars($search_service); ?>">
            OU /
            <label for="search_day">Jour de la semaine:</label>
            <select class="form-select" name="search_day" id="search_day">
                <option value="">Choisir un jour</option>
                <option value="Lundi" <?php echo $search_day === 'Lundi' ? 'selected' : ''; ?>>Lundi</option>
                <option value="Mardi" <?php echo $search_day === 'Mardi' ? 'selected' : ''; ?>>Mardi</option>
                <option value="Mercredi" <?php echo $search_day === 'Mercredi' ? 'selected' : ''; ?>>Mercredi</option>
                <option value="Jeudi" <?php echo $search_day === 'Jeudi' ? 'selected' : ''; ?>>Jeudi</option>
                <option value="Vendredi" <?php echo $search_day === 'Vendredi' ? 'selected' : ''; ?>>Vendredi</option>
                <option value="Samedi" <?php echo $search_day === 'Samedi' ? 'selected' : ''; ?>>Samedi</option>
                <option value="Dimanche" <?php echo $search_day === 'Dimanche' ? 'selected' : ''; ?>>Dimanche</option>
            </select>
            OU /
            <label for="search_address">Adresse:</label>
            <input type="text" name="search_address" id="search_address" placeholder="Adresse de l'employé" value="<?php echo htmlspecialchars($search_address); ?>">
            <button type="submit" class="btn btn-primary">Rechercher</button>
        </form>

        <div class="row">
            <?php foreach ($employes as $employe) : ?>
                <?php
                // Grouper les employés et leurs horaires
                $groupedEmployes[$employe['employe_id']]['employe_nom'] = $employe['employe_nom'];
                $groupedEmployes[$employe['employe_id']]['employe_telephone'] = $employe['employe_telephone'];
                $groupedEmployes[$employe['employe_id']]['employe_adresse'] = $employe['employe_adresse'];
                $groupedEmployes[$employe['employe_id']]['services'][] = [
                    'service_id' => $employe['service_id'],
                    'service_nom' => $employe['service_nom'],
                    'service_description' => $employe['service_description'],
                    'service_url_image' => $employe['service_url_image'],
                    'service_prix' => $employe['service_prix'],
                    'jour_semaine' => $employe['jour_semaine'],
                    'heure_debut' => $employe['heure_debut'],
                    'heure_fin' => $employe['heure_fin'],
                ];
                ?>
            <?php endforeach; ?>
            <?php foreach ($groupedEmployes as $employe_id => $employeData) : ?>
                <div class="col-md-4">
                    <div class="card mb-4">
                        <div class="card-body">
                            <h5 class="card-title  bold-blue-text"><?php echo htmlspecialchars($employeData['employe_nom']); ?></h5>
                            <p class="card-text">Téléphone : <?php echo htmlspecialchars($employeData['employe_telephone']); ?></p>
                            <p class="card-text">Lieu : <?php echo htmlspecialchars($employeData['employe_adresse']); ?></p>
                            <h6>Services proposés :</h6>
                            <?php foreach ($employeData['services'] as $service) : ?>
                                <div class="service-card mb-4">
                                    <div class="card-body">
                                        <h5 class="card-title  bold-blue-text"><?php echo htmlspecialchars($service['service_nom']); ?></h5>
                                        <p class="card-text"><?php echo htmlspecialchars($service['service_description']); ?></p>
                                        <p class="card-text">Prix : <?php echo htmlspecialchars($service['service_prix']); ?> DH</p>
                                        <p class="card-text">Jour : <?php echo htmlspecialchars($service['jour_semaine']); ?></p>
                                        <p class="card-text">Heures : <?php echo htmlspecialchars($service['heure_debut']); ?> - <?php echo htmlspecialchars($service['heure_fin']); ?></p>
                                        <?php
                                        // Vérifier les demandes existantes
                                        $stmt = $conn->prepare("
                                            SELECT COUNT(*) 
                                            FROM demandes 
                                            WHERE service_id = ? 
                                              AND employe_id = ? 
                                              AND client_id = ? 
                                              AND statut = 'En attente'
                                        ");
                                        $stmt->execute([$service['service_id'], $employe_id, $_SESSION['user_id']]);
                                        $existingRequest = $stmt->fetchColumn();

                                        $stmt = $conn->prepare("
                                            SELECT COUNT(*) 
                                            FROM demandes 
                                            WHERE service_id = ? 
                                              AND employe_id = ? 
                                              AND client_id != ? 
                                              AND statut = 'En attente'
                                        ");
                                        $stmt->execute([$service['service_id'], $employe_id, $_SESSION['user_id']]);
                                        $otherRequests = $stmt->fetchColumn();
                                        ?>
                                        <?php if ($existingRequest == 0 && $otherRequests == 0) : ?>
                                            <form action="" method="POST">
                                                <input type="hidden" name="service_id" value="<?php echo htmlspecialchars($service['service_id']); ?>">
                                                <input type="hidden" name="employe_id" value="<?php echo htmlspecialchars($employe_id); ?>">
                                                <div class="form-group">
                                                    <label for="mode_paiement">Mode de paiement :</label>
                                                    <select name="mode_paiement" id="mode_paiement" class="form-control" required>
                                                        <option value="">Choisir un mode de paiement</option>
                                                        <option value="Virement">Virement</option>
                                                        <option value="Espèces">Espèces</option>
                                                        <option value="Chèque">Chèque</option>
                                                    </select>
                                                </div>
                                                <button type="submit" class="btn btn-primary">Demander ce service</button>
                                            </form>
                                        <?php elseif ($existingRequest > 0) : ?>
                                            <p class="text-warning">Vous avez déjà une demande en attente pour ce service.</p>
                                        <?php elseif ($otherRequests > 0) : ?>
                                            <p class="text-danger">Ce service est déjà demandé par un autre client pour cet employé.</p>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</div>


<?php require_once __DIR__ . '/footer.php'; // Inclure le fichier de pied de page ?>
