<?php
require_once __DIR__.'/header.php';  // Include your header file
require_once __DIR__.'/db_connect.php';  // Adjust the path to the database connection file

// Get the service ID from the URL
$serviceId = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// Prepare the query to fetch the service details
$query = "SELECT * FROM services WHERE id = :id";
$stmt = $conn->prepare($query);
$stmt->bindParam(':id', $serviceId, PDO::PARAM_INT);
$stmt->execute();

// Fetch the service details
$service = $stmt->fetch(PDO::FETCH_ASSOC);

// Check if service exists
if (!$service) {
    echo "<p>Service not found.</p>";
    require_once __DIR__.'/footer.php';  // Include your footer file
    exit;
}
?>

<!-- Service Details Start -->
<div class="container-fluid service-details py-5 my-5">
    <div class="container py-5">
        <div class="row">
            <div class="col-md-12">
                <!-- Go Back Button -->
                <a href="service.php" class="btn btn-primary mb-4"><i class="bi bi-arrow-90deg-left"></i></a>
                
                <div class="service-details-content">
                    <h2 class="mb-4"><?php echo htmlspecialchars($service['nom']); ?></h2>
                    <div class="row">
                        <div class="col-md-4">
                            <?php
                            $imagePath = '../admin_dashboard/images/' . $service['url_image'];
                            $fullImagePath = __DIR__ . '/' . $imagePath;
                            if (file_exists($fullImagePath)) {
                                echo '<img src="' . $imagePath . '" alt="' . htmlspecialchars($service['nom']) . '" class="mb-4 img-fluid">';
                            } else {
                                echo '<p>Image not found at ' . $fullImagePath . '</p>';
                            }
                            ?>
                        </div>
                        <div class="col-md-8">
                            <p class="mb-4"><?php echo htmlspecialchars($service['description']); ?></p>
                            <p>Ne manquez pas cette incroyable opportunité ! Cliquez sur le bouton ci-dessous pour découvrir le meilleur service que nous offrons :</p>
                            <a href="login.php" class="btn btn-success mb-4">Obtenez ce service maintenant !</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<!-- Service Details End -->

<?php require_once __DIR__.'/footer.php';  // Include your footer file ?>
