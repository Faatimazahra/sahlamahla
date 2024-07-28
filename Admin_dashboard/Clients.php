<?php
session_start();

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    // If not logged in, redirect to the login page
    header('Location: /SahlaMahla/Home/login.php');
    exit();
}

// Prevent caching of pages
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");

require_once __DIR__ . '/header.php'; 
require_once __DIR__ . '/cnx/cnx.php';

// Initialize search variables
$search = isset($_GET['search']) ? $_GET['search'] : '';
?>

<!-- row -->
<div class="row tm-content-row tm-mt-big">
    <div class="col-xl-13 col-lg-12 tm-md-12 tm-sm-12 tm-col">
        <div class="bg-white tm-block h-100">
            <div class="table-responsive">
                <!-- Search form -->
                <form id="search-form" action="" method="get" class="mb-3">
                    <div class="input-group">
                        <input type="text" class="form-control" placeholder="Rechercher..." name="search" value="<?php echo htmlspecialchars($search); ?>">
                        <div class="input-group-append">
                            <button class="btn btn-primary" type="submit">Rechercher</button>
                        </div>
                    </div>
                </form>

                <!-- Table -->
                <table class="table table-hover table-striped tm-table-striped-even mt-3">
                    <thead>
                        <tr class="tm-bg-gray">
                            <th scope="col">Nom</th>
                            <th scope="col" class="text-center">Email</th>
                            <th scope="col" class="text-center">Téléphone</th>
                            <th scope="col" class="text-center">Adresse</th>
                            <th scope="col" class="text-center">Nombre de demandes</th>
                            <th scope="col" class="text-center">Employé(e)</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        // SQL query with search condition
                        $query = "SELECT u.id, u.nom, u.email, u.téléphone, u.adresse,
                                  COUNT(d.id) AS total_demandes,
                                  GROUP_CONCAT(CONCAT(e.nom, ' (', s.nom, ')') SEPARATOR '<br>') AS employe
                           FROM utilisateurs u
                           LEFT JOIN demandes d ON u.id = d.client_id
                           LEFT JOIN services s ON d.service_id = s.id
                           LEFT JOIN utilisateurs e ON d.employe_id = e.id
                           WHERE u.rôle = 'client'";

                        // Initialize array to store conditions
                        $conditions = [];

                        // Prepare search condition based on input fields
                        if (!empty($search)) {
                            $conditions[] = "u.nom LIKE :search";
                            $conditions[] = "u.email LIKE :search";
                            $conditions[] = "u.téléphone LIKE :search";
                            $conditions[] = "u.adresse LIKE :search";
                        }

                        // Combine conditions with OR for searching
                        if (!empty($conditions)) {
                            $query .= " AND (" . implode(" OR ", $conditions) . ")";
                        }

                        $query .= " GROUP BY u.id
                                   ORDER BY u.id DESC";
                        
                        // Prepare statement
                        $stmt = $conn->prepare($query);

                        // Bind search parameter if it exists
                        if (!empty($search)) {
                            $searchParam = "%$search%";
                            $stmt->bindParam(':search', $searchParam, PDO::PARAM_STR);
                        }

                        // Execute statement
                        $stmt->execute();

                        // Fetch and display results
                        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                            echo "<tr>
                                    <td class='tm-employees-name'>{$row['nom']}</td>
                                    <td class='text-center'>{$row['email']}</td>
                                    <td class='text-center'>{$row['téléphone']}</td>
                                    <td class='text-center'>{$row['adresse']}</td>
                                    <td class='text-center'>" . (isset($row['total_demandes']) ? $row['total_demandes'] : '') . "</td>
                                    <td class='text-center'>{$row['employe']}</td>
                                  </tr>";
                        }
                        ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/footer.php'; ?>
