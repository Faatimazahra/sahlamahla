<?php
session_start();

// Check if the user is not logged in
if (!isset($_SESSION['user_id'])) {
    // Redirect to the login page if not logged in
    header('Location: /SahlaMahla/Home/login.php');
    exit();
}

// Prevent caching of the page
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");

// Include database connection
require_once __DIR__ . '/cnx/cnx.php';

// Database connection using PDO
try {
    $conn = new PDO($dsn, DB_USER, DB_PASSWORD);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    echo "Connection failed: " . $e->getMessage();
    exit();
}

// Initialize variables for search
$service_search = '';

// Check if a search term is provided
if (isset($_GET['service_search']) && !empty($_GET['service_search'])) {
    $service_search = $_GET['service_search'];
}

// Fetch services from the database
try {
    $stmt = $conn->prepare("SELECT id, nom FROM services");
    $stmt->execute();
    $services = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    echo "Error fetching services: " . $e->getMessage();
    exit();
}

// Include header
require_once __DIR__ . '/header.php';
?>

<!-- HTML for the search form and table -->
<div class="row tm-content-row tm-mt-big">
    <div class="col-xl-13 col-lg-12 tm-md-12 tm-sm-12 tm-col">
        <div class="bg-white tm-block h-100">
            <!-- Search Form -->
            <form method="GET" action="">
                <div class="input-group mb-3">
                    <select class="form-control" name="service_search">
                        <option value="">Choisissez un service</option>
                        <?php foreach ($services as $service): ?>
                            <option value="<?php echo htmlspecialchars($service['nom']); ?>" <?php if ($service['nom'] == $service_search) echo 'selected'; ?>>
                                <?php echo htmlspecialchars($service['nom']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <div class="input-group-append">
                        <button class="btn btn-outline-secondary" type="submit">Search</button>
                    </div>
                </div>
            </form>

            <!-- Table Headers -->
            <table class="table table-hover table-striped tm-table-striped-even mt-3">
                <thead>
                    <tr class="tm-bg-gray">
                        <th scope="col">Nom</th>
                        <th scope="col">Email</th>
                        <th scope="col">Téléphone</th>
                        <th scope="col">Adresse</th>
                        <th scope="col">Service</th>
                        <th scope="col">CV PDF</th>
                        <th scope="col">Créé le</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    // Construct the base query
                    $query = "SELECT id, nom, email, téléphone, adresse, service, cv_pdf, créé_le FROM demandeemploi";

                    // Add WHERE clause if a search term is provided
                    if (!empty($service_search)) {
                        $query .= " WHERE service LIKE :service_search";
                    }

                    // Prepare the query statement
                    $stmt = $conn->prepare($query);

                    // Bind parameters if search term is provided
                    if (!empty($service_search)) {
                        $stmt->bindValue(':service_search', "%$service_search%");
                    }

                    // Execute the query
                    $stmt->execute();

                    // Loop through fetched data and output each row
                    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                        echo "<tr>
                                <td>{$row['nom']}</td>
                                <td>{$row['email']}</td>
                                <td>{$row['téléphone']}</td>
                                <td>{$row['adresse']}</td>
                                <td>{$row['service']}</td>
                                <td><a href='/SahlaMahla/Home/{$row['cv_pdf']}' target='_blank'>CV PDF</a></td>
                                <td>{$row['créé_le']}</td>
                              </tr>";
                    }
                    ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/footer.php'; ?>
