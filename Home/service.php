<?php
require_once __DIR__.'/header.php';  // Include your header file
require_once __DIR__.'/db_connect.php';  // Adjust the path to the database connection file

// Fetch all services for the dropdown
try {
    $servicesQuery = "SELECT id, nom FROM services";
    $servicesStmt = $conn->query($servicesQuery);
    $allServices = $servicesStmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    echo "Error fetching services: " . $e->getMessage();
}

// Handle dropdown selection
$selectedServiceId = isset($_GET['service_id']) ? (int)$_GET['service_id'] : 0;

// Query to fetch services based on selection
$query = "SELECT * FROM services";
if ($selectedServiceId > 0) {
    $query .= " WHERE id = :service_id";
}

try {
    $stmt = $conn->prepare($query);

    if ($selectedServiceId > 0) {
        $stmt->bindValue(':service_id', $selectedServiceId, PDO::PARAM_INT);
    }

    $stmt->execute();
    $services = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
}
?>

<!-- Services Start -->
<div class="container-fluid services py-5 my-5">
    <div class="col-md-3">
    <form action="" method="GET" class="mb-5" id="serviceForm">
        <div class="input-group">
            <select name="service_id" class="form-select" onchange="document.getElementById('serviceForm').submit();">
                <option value="">Sélectionnez un service (tous)...</option>
                <?php foreach ($allServices as $service): ?>
                    <option value="<?php echo htmlspecialchars($service['id']); ?>" <?php echo $selectedServiceId == $service['id'] ? 'selected' : ''; ?>>
                        <?php echo htmlspecialchars($service['nom']); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
    </form>
</div>
    <div class="container py-5">
        <div class="row g-5 services-inner">
            <?php foreach ($services as $service): ?>
                <div class="col-md-6 col-lg-4 wow fadeIn" data-wow-delay=".3s">
                    <div class="services-item bg-light">
                        <a href="details_service.php?id=<?php echo $service['id']; ?>" class="text-decoration-none text-dark">
                            <div class="p-4 text-center services-content">
                                <div class="services-content-icon">
                                    <?php
                                    $imagePath = '../admin_dashboard/images/' . $service['url_image'];
                                    if (file_exists($imagePath)) {
                                        echo '<img src="' . htmlspecialchars($imagePath) . '" alt="' . htmlspecialchars($service['nom']) . '" class="mb-4" style="width: 50px; height: 50px;">';
                                    } else {
                                        echo '<p>Image not found</p>';
                                    }
                                    ?>
                                    <h4 class="mb-3"><?php echo htmlspecialchars($service['nom']); ?></h4>
                                    <a href="details_service.php?id=<?php echo $service['id']; ?>" class="btn btn-secondary text-white px-5 py-3 rounded-pill">Plus</a>
                                </div>
                            </div>
                        </a>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</div>
<!-- Services End -->

<?php require_once __DIR__.'/footer.php';  // Include your footer file ?>
