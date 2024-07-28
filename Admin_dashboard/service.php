<?php
session_start();

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    // Redirect to login page if not logged in
    header('Location: /SahlaMahla/Home/login.php');
    exit();
}

// Prevent page caching
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");

// Include necessary files
require_once __DIR__ . '/header.php'; 
require_once __DIR__ . '/cnx/cnx.php';

// Database connection using PDO
try {
    $conn = new PDO($dsn, DB_USER, DB_PASSWORD);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    echo "Connection failed: " . $e->getMessage();
}

// Handle search functionality
$search = isset($_GET['search']) ? $_GET['search'] : '';

// Initialize query parameters
$whereClause = "";
$bindings = [];

if (!empty($search)) {
    $whereClause = "WHERE nom LIKE :search";
    $bindings['search'] = '%' . $search . '%';
}

// Query to fetch services with search condition
$query = "SELECT * FROM services $whereClause ORDER BY id DESC";
$stmt = $conn->prepare($query);

// Bind parameters if search is used
foreach ($bindings as $key => $value) {
    $stmt->bindValue(":$key", $value, PDO::PARAM_STR);
}

$stmt->execute();
?>

<!-- HTML Markup -->
<div class="row tm-content-row tm-mt-big">
    <div class="col-xl-8 col-lg-12 tm-md-12 tm-sm-12 tm-col">
        <div class="bg-white tm-block h-100">
            <!-- Search Form -->
            <form action="" method="get" class="input-group mb-3">
                <input class="form-control mr-sm-2" type="search" name="search" placeholder="Rechercher par nom de service" aria-label="Search" value="<?php echo htmlspecialchars($search); ?>">
                <button class="btn btn-primary" type="submit">Rechercher</button>
            </form>

            <!-- Display success or error messages -->
            <?php if (isset($_GET['delete_success'])): ?>
                <div class="alert alert-success" role="alert">Service supprimé avec succès !</div>
            <?php elseif (isset($_GET['delete_error'])): ?>
                <div class="alert alert-danger" role="alert">Une erreur s’est produite lors de la suppression du service.</div>
            <?php elseif (isset($_GET['insert_success'])): ?>
                <div class="alert alert-success" role="alert">Nouveau service ajouté avec succès !</div>
            <?php elseif (isset($_GET['insert_error'])): ?>
                <div class="alert alert-danger" role="alert">Une erreur s’est produite lors de l’ajout du service.</div>
            <?php endif; ?>

            <div class="table-responsive">
                <!-- Form for deleting multiple services -->
                <form id="delete-form" action="delete_multiple_services.php" method="post">
                    <table class="table table-hover table-striped tm-table-striped-even mt-3">
                        <thead>
                            <tr class="tm-bg-gray">
                                <th scope="col">Select</th>
                                <th scope="col">Nom</th>
                                <th scope="col" class="text-center">Description</th>
                                <th scope="col" class="text-center">Image</th>
                                <th scope="col" class="text-center">Prix</th>
                                <th scope="col">Delete</th>
                                <th scope="col">Edit</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            // Display services based on query results
                            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                                echo "<tr>
                                        <th scope='row'>
                                            <input type='checkbox' name='ids[]' value='{$row['id']}' aria-label='Checkbox'>
                                        </th>
                                        <td class='tm-service-name'>{$row['nom']}</td>
                                        <td class='text-center'>{$row['description']}</td>
                                        <td class='text-center'><img src='images/{$row['url_image']}' alt='Image' style='width: 50px; height: 50px;'></td>
                                        <td class='text-center'>{$row['prix']} DH</td>
                                        <td class='text-center'>
                                            <form action='delete_service.php' method='post' style='display:inline-block'>
                                                <input type='hidden' name='id' value='{$row['id']}'>
                                                <button type='submit' style='border:none;background:none;color:red;cursor:pointer;'><i class='fas fa-trash-alt tm-trash-icon'></i></button>
                                            </form>
                                        </td>
                                        <td class='text-center'><a href='edit_service.php?id={$row['id']}'><i class='fas fa-edit tm-edit-icon'></i></a></td>
                                      </tr>";
                            }
                            ?>
                        </tbody>
                    </table>
                </form>
            </div>

            <!-- Delete selected services button -->
            <div class="tm-table-mt tm-table-actions-row">
                <div class="tm-table-actions-col-left">
                    <button class="btn btn-danger" onclick="document.getElementById('delete-form').submit();">Supprimer les éléments sélectionnés</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Form for adding a new service -->
    <div class="col-xl-4 col-lg-12 tm-md-12 tm-sm-12 tm-col">
        <div class="bg-white tm-block h-100">
            <h2 class="tm-block-title d-inline-block">Ajouter un nouveau service</h2>
            <form action="insert_service.php" method="post" enctype="multipart/form-data" class="mt-3">
                <div class="form-group">
                    <label for="nom">Nom</label>
                    <input type="text" class="form-control" id="nom" name="nom" placeholder="Entrez le nom du service" required>

                    <label for="description">Description</label>
                    <input type="text" class="form-control" id="description" name="description" placeholder="Entrez la description du service" required>

                    <label for="image">Image</label>
                    <input type="file" class="form-control" id="image" name="image" required>

                    <label for="prix">Prix</label>
                    <input type="text" class="form-control" id="prix" name="prix" placeholder="Entrez le prix du service" required>
                </div>
                <button type="submit" class="btn btn-primary btn-block">Ajouter un service</button>
            </form>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/footer.php'; ?>
