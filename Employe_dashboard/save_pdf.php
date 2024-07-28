<?php
require_once __DIR__ . '/cnx/cnx.php'; // Adjust as per your file structure

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['htmlContent'])) {
    $htmlContent = $_POST['htmlContent'];

    // Check if the directory exists, if not, create it
    $pdfDirectory = __DIR__ . '/pdf';
    if (!is_dir($pdfDirectory)) {
        mkdir($pdfDirectory, 0755, true);
    }

    // Include the autoloader from Composer (adjust path as needed)
    require 'vendor/autoload.php';

    use Dompdf\Dompdf;
    use Dompdf\Options;

    // Initialize Dompdf
    $options = new Options();
    $options->set('isRemoteEnabled', true);
    $dompdf = new Dompdf($options);

    // Load HTML content
    $dompdf->loadHtml($htmlContent);

    // (Optional) Set paper size and orientation
    $dompdf->setPaper('A4', 'portrait');

    // Render the HTML as PDF
    $dompdf->render();

    // Get the generated PDF content
    $pdfContent = $dompdf->output();

    // Define the file path
    $pdfFilePath = $pdfDirectory . '/invoice_' . time() . '.pdf';

    // Save the PDF to the server
    file_put_contents($pdfFilePath, $pdfContent);

    // Redirect back to the invoice page or show a success message
    echo 'PDF saved successfully at ' . $pdfFilePath;
} else {
    echo 'Invalid request';
}
?>
