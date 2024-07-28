<?php
session_start();

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: /SahlaMahla/Home/login.php');
    exit();
}

require_once __DIR__ . '/header.php';
require_once __DIR__ . '/cnx/cnx.php';

try {
    $conn = new PDO($dsn, DB_USER, DB_PASSWORD);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    echo "Connection failed: " . $e->getMessage();
    exit();
}

// Function to count demandes by status
function countDemandesByStatut($conn, $statut) {
    $stmt = $conn->prepare("SELECT COUNT(*) FROM demandes WHERE statut = :statut");
    $stmt->bindParam(':statut', $statut);
    $stmt->execute();
    return $stmt->fetchColumn();
}

// Count demandes by status
$countEnAttente = countDemandesByStatut($conn, 'En attente');
$countTermine = countDemandesByStatut($conn, 'Terminé');
$countAnnule = countDemandesByStatut($conn, 'Annulé');

// Retrieve filter values from GET
$statutFilter = isset($_GET['statut']) ? $_GET['statut'] : '';
$clientNomFilter = isset($_GET['client_nom']) ? $_GET['client_nom'] : '';
$dateServiceFilter = isset($_GET['date_service']) ? $_GET['date_service'] : '';

// Modify the query to include filtering if necessary
$query = "SELECT demandes.*, services.nom AS service_nom, 
          client.nom AS client_nom, employe.nom AS employe_nom, employe.adresse AS employe_adresse
          FROM demandes
          JOIN services ON demandes.service_id = services.id
          JOIN utilisateurs AS client ON demandes.client_id = client.id
          JOIN employe_services ON employe_services.service_id = demandes.service_id
          JOIN utilisateurs AS employe ON employe_services.employe_id = employe.id
          WHERE 1=1";

if ($statutFilter) {
    $query .= " AND demandes.statut = :statut";
}

if ($clientNomFilter) {
    $query .= " AND client.nom LIKE :client_nom";
}

if ($dateServiceFilter) {
    $query .= " AND demandes.date_demande LIKE :date_service";
}

$query .= " ORDER BY demandes.id DESC";
$stmt = $conn->prepare($query);

if ($statutFilter) {
    $stmt->bindParam(':statut', $statutFilter);
}

if ($clientNomFilter) {
    $clientNomFilter = "%" . $clientNomFilter . "%";
    $stmt->bindParam(':client_nom', $clientNomFilter, PDO::PARAM_STR);
}

if ($dateServiceFilter) {
    $dateServiceFilter = "%" . $dateServiceFilter . "%";
    $stmt->bindParam(':date_service', $dateServiceFilter);
}

$stmt->execute();
?>

<!-- row -->
<div class="row tm-content-row tm-mt-big">
    <div class="col-xl-13 col-lg-12 tm-md-12 tm-sm-12 tm-col">
        <div class="bg-white tm-block h-100">
            <div class="legend">
                <span class="legend-item" style="display: inline-block; padding: 2px 10px; margin: 5px; border-radius: 4px; background-color: #ffffe0;">
                    <a href="?statut=En%20attente">En attente (<?= $countEnAttente ?>)</a>
                </span>
                <span class="legend-item" style="display: inline-block; padding: 2px 10px; margin: 5px; border-radius: 4px; background-color: #90ee90;">
                    <a href="?statut=Terminé">Terminé (<?= $countTermine ?>)</a>
                </span>
                <span class="legend-item" style="display: inline-block; padding: 2px 10px; margin: 5px; border-radius: 4px; background-color: #ffcccb;">
                    <a href="?statut=Annulé">Annulé (<?= $countAnnule ?>)</a>
                </span>
                <span class="legend-item" style="display: inline-block; padding: 2px 10px; margin: 5px; border-radius: 4px; background-color: #dcdcdc;">
                    <a href="?statut=">Toutes (<?= ($countEnAttente + $countTermine + $countAnnule) ?>)</a>
                </span>
            </div>

            <!-- Search Form -->
            <form method="GET" action="" class="mb-3">
                <div class="row">
                    <div class="col-md-4">
                        <input type="text" name="client_nom" class="form-control" placeholder="Nom du client" value="<?= htmlspecialchars($clientNomFilter, ENT_QUOTES, 'UTF-8') ?>">
                    </div>
                    <div class="col-md-4">
                        <input type="date" name="date_service" class="form-control" value="<?= htmlspecialchars($dateServiceFilter, ENT_QUOTES, 'UTF-8') ?>">
                    </div>
                    <div class="col-md-4">
                        <button type="submit" class="btn btn-primary">Rechercher</button>
                    </div>
                </div>
            </form>

            <div class="table-responsive">
                <table class="table table-hover table-striped tm-table-striped-even mt-3">
                    <thead>
                        <tr class="tm-bg-gray">
                            <th scope="col">Service</th>
                            <th scope="col" class="text-center">Client</th>
                            <th scope="col" class="text-center">Employé</th>
                            <th scope="col" class="text-center">Lieu</th>
                            <th scope="col" class="text-center">Date & Heure</th>
                            <th scope="col" class="text-center">Statut</th>
                            <th scope="col" class="text-center">Paiement</th>
                            <th scope="col" class="text-center">Mode de paiement</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                            // Determine background color based on status
                            $statutBgColor = '';
                            if ($row['statut'] == 'En attente') {
                                $statutBgColor = 'background-color: #ffffe0;'; // Light yellow
                            } elseif ($row['statut'] == 'Terminé') {
                                $statutBgColor = 'background-color: #90ee90;'; // Light green
                            } elseif ($row['statut'] == 'Annulé') {
                                $statutBgColor = 'background-color: #ffcccb;'; // Light red
                            }

                            // Ensure keys are available before accessing
                            $statutPaiement = isset($row['statut_paiement']) ? htmlspecialchars($row['statut_paiement'], ENT_QUOTES, 'UTF-8') : 'N/A';
                            $modePaiement = isset($row['mode_paiement']) ? htmlspecialchars($row['mode_paiement'], ENT_QUOTES, 'UTF-8') : 'N/A';

                            echo "<tr>";
                            echo "<td>{$row['service_nom']}</td>";
                            echo "<td class='text-center'>{$row['client_nom']}</td>";
                            echo "<td class='text-center'>{$row['employe_nom']}</td>";
                            echo "<td class='text-center'>{$row['employe_adresse']}</td>";
                            echo "<td class='text-center'>{$row['date_demande']}</td>";
                            echo "<td class='text-center' style='$statutBgColor'>{$row['statut']}</td>";
                            echo "<td class='text-center'>
                                    <form method='POST' action='update_paiement.php' class='statut-paiement-form'>
                                        <input type='hidden' name='demande_id' value='{$row['id']}'>
                                        <select name='statut_paiement' class='form-control statut-paiement-select'>
                                            <option value='Non payé' " . ($row['statut_paiement'] == 'Non payé' ? 'selected' : '') . ">Non payé</option>
                                            <option value='Payé' " . ($row['statut_paiement'] == 'Payé' ? 'selected' : '') . ">Payé</option>
                                        </select>
                                    </form>
                                  </td>";
                            echo "<td class='text-center'>$modePaiement</td>";
                            echo "</tr>";
                        }
                        ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
<!-- row end -->

<script>
document.querySelectorAll('.statut-paiement-select').forEach(function(select) {
    select.addEventListener('change', function() {
        var form = select.closest('form');
        var formData = new FormData(form);
        
        fetch('update_paiement.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                console.log('Paiement mis à jour avec succès.');
                // Optionally, refresh the row or part of the page
                // e.g., form.closest('tr').querySelector('.statut-paiement-select').value = data.newStatutPaiement;
            } else {
                console.error('Erreur : ' + data.message);
                // Optionally, handle specific error cases
                // e.g., display an error message in the UI
            }
        })
        .catch(error => {
            console.error('Erreur de réseau.', error);
            // Optionally, handle network errors
            // e.g., display a network error message in the UI
        });
    });
});
</script>
