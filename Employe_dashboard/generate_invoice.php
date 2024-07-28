<?php
// Include database connection
require_once __DIR__ . '/cnx/cnx.php'; 

// Get reservation ID from the query parameter
$reservationId = isset($_GET['reservation_id']) ? intval($_GET['reservation_id']) : 0;

// Fetch the reservation details
$query = "
    SELECT r.*, s.nom as service_nom, s.prix as service_prix, 
           u.nom as employe_nom, 
           h.jour_semaine, h.heure_debut, h.heure_fin,
           c.nom as client_nom, c.téléphone as client_téléphone
    FROM reservation r
    JOIN services s ON r.service_id = s.id
    JOIN utilisateurs u ON r.employee_id = u.id
    JOIN horaires_travail h ON r.employee_id = h.employé_id
    JOIN utilisateurs c ON r.client_id = c.id
    WHERE r.reservation_id = :reservation_id
";

$stmt = $conn->prepare($query);
$stmt->bindParam(':reservation_id', $reservationId, PDO::PARAM_INT);
$stmt->execute();
$reservation = $stmt->fetch(PDO::FETCH_ASSOC);

if ($reservation) {
    // Fetch all reservations for the same client
    $clientId = $reservation['client_id'];
    $query = "
        SELECT r.*, s.nom as service_nom, s.prix as service_prix, 
               u.nom as employe_nom, 
               h.jour_semaine, h.heure_debut, h.heure_fin
        FROM reservation r
        JOIN services s ON r.service_id = s.id
        JOIN utilisateurs u ON r.employee_id = u.id
        JOIN horaires_travail h ON r.employee_id = h.employé_id
        WHERE r.client_id = :client_id
    ";

    $stmt = $conn->prepare($query);
    $stmt->bindParam(':client_id', $clientId, PDO::PARAM_INT);
    $stmt->execute();
    $reservations = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Calculate total price
    $totalPrice = 0;

    // Generate the invoice
    $invoiceHtml = "<!DOCTYPE html>
<html lang='fr'>
<head>
    <meta charset='UTF-8'>
    <meta name='viewport' content='width=device-width, initial-scale=1.0'>
    <title>Facture</title>
    <link rel='stylesheet' href='https://stackpath.bootstrapcdn.com/bootstrap/5.3.0/css/bootstrap.min.css'>
    <script src='https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.6.0/jspdf.umd.min.js'></script>
</head>
<body>
    <div class='container mt-5'>
        <div class='row'>
            <div class='col-12'>
                <div class='border p-4 rounded bg-light'>
                    <h1 class='text-center mb-4' style='color: blue; font-weight: bold;'>S<span style='color: #26d48c;'>ahla Mahla</span></h1>
                    <hr>
                    <h2 class='mb-4'>Client: " . htmlspecialchars($reservation['client_nom'], ENT_QUOTES, 'UTF-8') . "</h2>
                    <table class='table table-bordered'>
                        <thead style='background: #26d48c; color: white;'>
                            <tr>
                                <th>Service</th>
                                <th>Employé</th>
                                <th>Horaires de travail</th>
                                <th>Prix</th>
                            </tr>
                        </thead>
                        <tbody>";

    foreach ($reservations as $r) {
        $invoiceHtml .= "<tr>
                            <td>" . htmlspecialchars($r['service_nom'], ENT_QUOTES, 'UTF-8') . "</td>
                            <td>" . htmlspecialchars($r['employe_nom'], ENT_QUOTES, 'UTF-8') . "</td>
                            <td>" . htmlspecialchars($r['jour_semaine'], ENT_QUOTES, 'UTF-8') . " " . htmlspecialchars($r['heure_debut'], ENT_QUOTES, 'UTF-8') . " - " . htmlspecialchars($r['heure_fin'], ENT_QUOTES, 'UTF-8') . "</td>
                            <td>" . htmlspecialchars(number_format($r['service_prix'], 2, ',', ' '), ENT_QUOTES, 'UTF-8') . " DH</td>
                         </tr>";
        $totalPrice += $r['service_prix'];
    }

    $invoiceHtml .= "</tbody>
                     <tfoot>
                         <tr>
                             <td colspan='3' class='text-end fw-bold'>Total</td>
                             <td class='fw-bold'>" . htmlspecialchars(number_format($totalPrice, 2, ',', ' '), ENT_QUOTES, 'UTF-8') . " DH</td>
                         </tr>
                     </tfoot>
                 </table>
                </div>
                <button class='btn btn-primary mt-4' onclick='saveAsPDF()'>Save as PDF</button>
            </div>
        </div>
    </div>
    <script src='https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.6.0/jspdf.umd.min.js'></script>
    <script>
        async function saveAsPDF() {
            const { jsPDF } = window.jspdf;
            const doc = new jsPDF();
            
            // Get the HTML content
            const invoiceContent = document.querySelector('.invoice-container').innerHTML;
            
            doc.html(invoiceContent, {
                callback: function (doc) {
                    doc.save('invoice.pdf');
                },
                x: 10,
                y: 10
            });
        }
    </script>
</body>
</html>";

    echo $invoiceHtml;
} else {
    echo "<p>Réservation non trouvée.</p>";
}
