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

// Include header
require_once __DIR__ . '/header.php';
?>

<!-- HTML for the table -->
<div class="row tm-content-row tm-mt-big">
    <div class="col-xl-13 col-lg-12 tm-md-12 tm-sm-12 tm-col">
        <div class="bg-white tm-block h-100">
            <!-- Table Headers -->
            <table class="table table-hover table-striped tm-table-striped-even mt-3">
                <thead>
                    <tr class="tm-bg-gray">
                        <th scope="col">Name</th>
                        <th scope="col">Email</th>
                        <th scope="col">Message</th>
                        <th scope="col">Submitted At</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    // Construct the query to fetch messages in descending order
                    $query = "SELECT id, name, email, message, submitted_at FROM contact_messages ORDER BY submitted_at DESC";

                    // Prepare the query statement
                    $stmt = $conn->prepare($query);

                    // Execute the query
                    $stmt->execute();

                    // Loop through fetched data and output each row
                    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                        echo "<tr>
                                <td>" . htmlspecialchars($row['name']) . "</td>
                                <td>" . htmlspecialchars($row['email']) . "</td>
                                <td>" . htmlspecialchars($row['message']) . "</td>
                                <td>" . htmlspecialchars($row['submitted_at']) . "</td>
                              </tr>";
                    }
                    ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/footer.php'; ?>
