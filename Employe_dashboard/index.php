<?php
// Start session and check if user is logged in
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: /SahlaMahla/Home/login.php');
    exit();
}

// Prevent page caching
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");

require_once __DIR__ . '/header.php'; // Adjust as per your file structure
require_once __DIR__ . '/cnx/cnx.php'; // Adjust as per your file structure

try {
    // Establish a connection to the database
    $conn = new PDO($dsn, DB_USER, DB_PASSWORD);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    echo "Échec de la connexion : " . $e->getMessage();
    exit;
}

// Get the employee ID from the session
$employee_id = $_SESSION['user_id']; // Or fetch from URL: $_GET['employe_id']

// Handle status update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_status'])) {
    $demande_id = $_POST['demande_id'];
    $new_status = $_POST['new_status'];

    try {
        $stmt = $conn->prepare("UPDATE demandes SET statut = :statut WHERE id = :demande_id AND employe_id = :employe_id");
        $stmt->execute(['statut' => $new_status, 'demande_id' => $demande_id, 'employe_id' => $employee_id]);
        $successMessage = 'Statut mis à jour avec succès.';
    } catch (PDOException $e) {
        $errorMessage = 'Erreur lors de la mise à jour du statut : ' . $e->getMessage();
    }
}

// Retrieve the service requests for the specific employee
try {
    $stmt = $conn->prepare("
        SELECT d.id AS demande_id, s.nom AS service_nom, s.prix AS service_prix, d.statut AS demande_statut, d.date_demande, d.mode_paiement, u.nom AS client_nom
        FROM demandes d
        JOIN services s ON d.service_id = s.id
        JOIN utilisateurs u ON d.client_id = u.id
        WHERE d.employe_id = :employe_id
    ");
    $stmt->execute(['employe_id' => $employee_id]);
    $demandes = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    echo "Erreur lors de la récupération des demandes : " . $e->getMessage();
    exit;
}
?>

<!-- HTML part for displaying the content -->
<div class="row tm-content-row tm-mt-big">
    <div class="col-xl-12 col-lg-12 tm-md-12 tm-sm-12 tm-col">
        <div class="bg-white tm-block h-100">
            <h2 class="tm-block-title">Demandes de Service pour l'Employé</h2>

            <?php if (!empty($successMessage)) : ?>
                <div class="alert alert-success"><?php echo $successMessage; ?></div>
            <?php endif; ?>
            <?php if (!empty($errorMessage)) : ?>
                <div class="alert alert-danger"><?php echo $errorMessage; ?></div>
            <?php endif; ?>

            <?php if (!empty($demandes)) : ?>
                <?php foreach ($demandes as $demande) : ?>
                    <div class="mb-4" data-demande-id="<?php echo htmlspecialchars($demande['demande_id']); ?>">
                        <p><strong>Type :</strong> <?php echo htmlspecialchars($demande['service_nom']); ?></p>
                        <p><strong>Prix :</strong> <?php echo htmlspecialchars(number_format($demande['service_prix'], 2)); ?> DH</p>
                        <p><strong>Statut :</strong> <?php echo htmlspecialchars($demande['demande_statut']); ?></p>
                        <p><strong>Date de la Demande :</strong> <?php echo htmlspecialchars($demande['date_demande']); ?></p>
                        <p><strong>Mode de Paiement :</strong> <?php echo htmlspecialchars($demande['mode_paiement']); ?></p>
                        <p><strong>Client :</strong> <?php echo htmlspecialchars($demande['client_nom']); ?></p>

                        <!-- Form for updating status -->
                        <?php if ($demande['demande_statut'] != 'Terminé' && $demande['demande_statut'] != 'Annulé') : ?>
                            <form method="POST" action="">
                                <input type="hidden" name="demande_id" value="<?php echo htmlspecialchars($demande['demande_id']); ?>">
                                <label for="new_status">Changer le statut :</label>
                                <select name="new_status" id="new_status">
                                    <option value="En attente" <?php if ($demande['demande_statut'] == 'En attente') echo 'selected'; ?>>En attente</option>
                                    <option value="En cours" <?php if ($demande['demande_statut'] == 'En cours') echo 'selected'; ?>>En cours</option>
                                    <option value="Terminé" <?php if ($demande['demande_statut'] == 'Terminé') echo 'selected'; ?>>Terminé</option>
                                    <option value="Annulé" <?php if ($demande['demande_statut'] == 'Annulé') echo 'selected'; ?>>Annulé</option>
                                </select>
                                <button type="submit" name="update_status" class="btn btn-primary btn-sm">Mettre à jour</button>
                            </form>
                        <?php endif; ?>

                        <!-- Print button -->
                        <button onclick="printInvoice(<?php echo $demande['demande_id']; ?>)" class="btn btn-secondary btn-sm">Imprimer comme facture</button>
                        
                        <hr>
                    </div>
                <?php endforeach; ?>
            <?php else : ?>
                <p>Aucune demande de service trouvée pour cet employé.</p>
            <?php endif; ?>
        </div>
    </div>
</div>

<script>
async function printInvoice(demandeId) {
    const { jsPDF } = window.jspdf;
    const demandeElement = document.querySelector('div[data-demande-id="' + demandeId + '"]');
    const doc = new jsPDF();
    
    // Define colors and fonts
    const headerColor = '#0266c4'; // Dark Blue
    const accentColor = '#26d48c'; // Light Green
    const textColor = '#333333'; // Dark Grey
    const fontSizeTitle = 24;
    const fontSizeHeader = 18;
    const fontSizeBody = 12;
    const rowHeight = 12; // Height for row spacing
    const lineColor = '#cccccc'; // Light Grey for line

    // Title
    doc.setFontSize(fontSizeTitle);
    doc.setFont("helvetica", "bold");

    // Title text segments
    const titleText1 = 'S';
    const titleText2 = 'ahla Mahla';

    // Get the width of the text segments
    const titleWidth1 = doc.getTextWidth(titleText1);
    const titleWidth2 = doc.getTextWidth(titleText2);

    // Left align title
    const margin = 20; // Margin from the left edge

    // Draw the title segments
    doc.setTextColor(headerColor);
    doc.text(titleText1, margin, 15); // Position "S" on the left side
    
    doc.setTextColor(accentColor);
    doc.text(titleText2, margin + titleWidth1, 15); // Position "ahla Mahla" right next to "S"

    // Header
    doc.setTextColor(headerColor);
    doc.setFontSize(fontSizeHeader);
    doc.setFont("helvetica", "bold");
    const headerText = 'Facture de demande';
    
    // Calculate header text width and center it
    const pageWidth = doc.internal.pageSize.width;
    const headerTextWidth = doc.getTextWidth(headerText);
    const headerX = (pageWidth - headerTextWidth) / 2; // Center the header text

    doc.text(headerText, headerX, 30); // Centered header text

    // Content
    doc.setTextColor(textColor);
    doc.setFontSize(fontSizeBody);
    doc.setFont("helvetica", "normal");

    // Extract information from the demandeElement
    const serviceNom = demandeElement.querySelector('p:nth-of-type(1)').innerText.replace('Type :', '').trim();
    const demandeStatut = demandeElement.querySelector('p:nth-of-type(3)').innerText.replace('Statut :', '').trim();
    const dateDemande = demandeElement.querySelector('p:nth-of-type(4)').innerText.replace('Date de la Demande :', '').trim();
    const modePaiement = demandeElement.querySelector('p:nth-of-type(5)').innerText.replace('Mode de Paiement :', '').trim();
    const clientNom = demandeElement.querySelector('p:nth-of-type(6)').innerText.replace('Client :', '').trim();
    const servicePrix = demandeElement.querySelector('p:nth-of-type(2)').innerText.replace('Prix :', '').trim();

    // Add service details
    const tableTop = 60;
    doc.setFontSize(fontSizeHeader);
    doc.text('', margin, tableTop);

    // Date
    doc.setTextColor(headerColor);
    doc.setFontSize(fontSizeBody);
    doc.setFont("helvetica", "normal");
    const date = new Date().toLocaleDateString();
    doc.text(`Date: ${date}`, margin, tableTop);

    doc.setFontSize(fontSizeBody);
    doc.setFillColor('#26d48c'); // Light Grey background for table header
    doc.rect(margin, tableTop + 10, 180, 10, 'F'); // Table header background
    doc.setTextColor('#000000'); // Black text for header
    doc.text('Description', margin + 2, tableTop + 15); // Description column
    doc.text('Information', margin + 140, tableTop + 15, { align: 'right' }); // Information column

    // Draw line after the table header
    doc.setDrawColor(lineColor); // Line color
    doc.setLineWidth(0.5); // Line width
    doc.line(margin, tableTop + 20, margin + 180, tableTop + 20); // Line position

    // Content rows
    let currentY = tableTop + 25; // Start rows below the line

    function addRow(label, value) {
        doc.text(label, margin + 2, currentY);
        doc.text(value, margin + 140, currentY, { align: 'right' });
        currentY += rowHeight; // Increase row height for spacing
    }

    addRow('Type', serviceNom);
    addRow('Statut', demandeStatut);
    addRow('Date de la Demande', dateDemande);
    addRow('Mode de Paiement', modePaiement);
    addRow('Client', clientNom);

    // Total Price
    doc.setTextColor(headerColor);
    const totalPrice = parseFloat(servicePrix.replace(',', '.')); // Convert price to number
    addRow('Total', `${totalPrice.toFixed(2)} DH`); // Show total price with 2 decimal places

    // Footer
    const footerY = 285;
    doc.setTextColor(accentColor);
    doc.setFontSize(10);
    doc.setFont("helvetica", "italic");
    doc.text('Merci pour votre confiance!', margin + 70, footerY, { align: 'center' });

    // Save PDF
    const sanitizedClientName = clientNom.replace(/[^a-zA-Z0-9]/g, '_'); // Sanitize client name for filename
    doc.save(`Facture_${sanitizedClientName}.pdf`);
}

</script>
