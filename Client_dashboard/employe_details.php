<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header('Location: /SahlaMahla/Home/login.php');
    exit();
}

header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");

require_once __DIR__ . '/cnx/cnx.php';

try {
    $conn = new PDO($dsn, DB_USER, DB_PASSWORD);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    echo "Connection failed: " . $e->getMessage();
    exit;
}

if (!isset($_GET['id'])) {
    echo "Aucun employé spécifié.";
    exit;
}

$employeId = $_GET['id'];

$query = "SELECT * FROM utilisateurs WHERE id = :id";
$stmt = $conn->prepare($query);
$stmt->bindValue(':id', $employeId, PDO::PARAM_INT);

try {
    $stmt->execute();
    $employe = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$employe) {
        echo "Aucun employé trouvé.";
        exit;
    }
} catch (PDOException $e) {
    echo "Erreur lors de la récupération des informations de l'employé : " . $e->getMessage();
    exit;
}
?>


<p><strong>Nom:</strong> <?php echo htmlspecialchars($employe['nom']); ?></p>
<p><strong>Email:</strong> <?php echo htmlspecialchars($employe['email']); ?></p>
<p><strong>Numéro de téléphone:</strong> <?php echo htmlspecialchars($employe['téléphone']); ?></p>
<!-- Add more details as needed -->
