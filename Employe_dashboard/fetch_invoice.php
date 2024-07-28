<?php
require_once __DIR__ . '/cnx/cnx.php'; // Adjust as per your file structure

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id'])) {
    $id = intval($_POST['id']);

    // Fetch invoice details
    $query = "SELECT * FROM factures WHERE facture_id = :id";
    $stmt = $conn->prepare($query);
    $stmt->bindParam(':id', $id, PDO::PARAM_INT);
    $stmt->execute();
    $invoice = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($invoice) {
        // Output invoice details
        echo "<h4>Invoice ID: " . htmlspecialchars($invoice['facture_id'], ENT_QUOTES, 'UTF-8') . "</h4>";
        echo "<p>Reservation ID: " . htmlspecialchars($invoice['reservation_id'], ENT_QUOTES, 'UTF-8') . "</p>";
        echo "<p>Date: " . htmlspecialchars($invoice['date_facture'], ENT_QUOTES, 'UTF-8') . "</p>";
        echo "<p>Amount: " . htmlspecialchars($invoice['montant'], ENT_QUOTES, 'UTF-8') . "</p>";
    } else {
        echo "<p>No invoice found.</p>";
    }
}
?>
