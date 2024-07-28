<?php
session_start();

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: /SahlaMahla/Home/login.php');
    exit();
}

// Prevent page caching
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");

// Include header and database connection
require_once __DIR__ . '/header.php';
require_once __DIR__ . '/cnx/cnx.php';

// Database connection using PDO
try {
    $conn = new PDO($dsn, DB_USER, DB_PASSWORD);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    echo "Connection failed: " . $e->getMessage();
}

// Fetching statistics
// الحصول على العدد الإجمالي للعملاء
$total_clients = $conn->query("SELECT COUNT(*) FROM utilisateurs WHERE rôle = 'client'")->fetchColumn();
$total_employees = $conn->query("SELECT COUNT(*) FROM utilisateurs WHERE rôle = 'employé'")->fetchColumn();
$total_services = $conn->query("SELECT COUNT(*) FROM services")->fetchColumn();
$total_demandes = $conn->query("SELECT COUNT(*) FROM demandes")->fetchColumn();

// Fetching top employees by demandes handled
$query_top_employees = "SELECT e.nom AS employee_name, COUNT(d.id) AS total_demandes
                        FROM utilisateurs AS e
                        JOIN demandes AS d ON e.id = d.employe_id
                        GROUP BY e.id
                        ORDER BY total_demandes DESC
                        LIMIT 5";
$stmt_top_employees = $conn->prepare($query_top_employees);
$stmt_top_employees->execute();
$top_employees = $stmt_top_employees->fetchAll(PDO::FETCH_ASSOC);


?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>
    <link rel="stylesheet" href="path/to/your/css/style.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        .card-employees {
            background-color: #f8d7da; /* Light red */
            border-color: #f5c6cb;
        }

        .card-clients {
            background-color: #d4edda; /* Light green */
            border-color: #c3e6cb;
        }

        .card-demanades {
            background-color: #d1ecf1; /* Light blue */
            border-color: #bee5eb;
        }
    </style>
</head>

<body>

    <!-- Header and Navigation -->
    <?php require_once __DIR__ . '/header.php'; ?>

    <!-- Main Content -->
    <div class="container-fluid">
        <div class="row tm-content-row tm-mt-big">

            <!-- Statistics Cards -->
            <div class="col-xl-4 col-lg-4 tm-md-6 tm-sm-12 tm-col card-employees">
                <div class="bg-white tm-block h-100">
                    <h2 class="tm-block-title">Employés</h2>
                    <div class="card card-employees">
                        <div class="card-body">                           
                            <p class="card-text"><strong><?= $total_employees ?></strong></p>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-xl-4 col-lg-4 tm-md-6 tm-sm-12 tm-col card-clients">
                <div class="bg-white tm-block h-100">
                    <h2 class="tm-block-title">Clients</h2>
                    <div class="card card-clients">
                        <div class="card-body">          
                            <p class="card-text"><strong><?= $total_clients ?></strong></p>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-xl-4 col-lg-4 tm-md-6 tm-sm-12 tm-col card-demanades">
                <div class="bg-white tm-block h-100">
                    <h2 class="tm-block-title">Demandes</h2>
                    <div class="card card-demanades">
                        <div class="card-body">
                            <p class="card-text"><strong><?= $total_demandes ?></strong></p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Statistics -->
            <div class="col-xl-6 col-lg-6 tm-md-6 tm-sm-12 tm-col">
                <div class="bg-white tm-block h-100">
                    <h2 class="tm-block-title">Statistiques Globales</h2>
                    <canvas id="statsChart"></canvas>
                </div>
            </div>

            <!-- Top Employees by Demandes -->
            <div class="col-xl-6 col-lg-6 tm-md-6 tm-sm-12 tm-col">
                <div class="bg-white tm-block h-100">
                    <h2 class="tm-block-title">Employés les Plus Demandés</h2>
                    <canvas id="topEmployeesChart"></canvas>
                </div>
            </div>

           
            </div>

        </div>
    </div>

    <!-- Footer -->
    <?php require_once __DIR__ . '/footer.php'; ?>

    <!-- Chart.js Scripts -->
    <script>
        // Chart.js configuration for Statistics Bar Chart
        const ctxStats = document.getElementById('statsChart').getContext('2d');
        const statsChart = new Chart(ctxStats, {
            type: 'bar',
            data: {
                labels: ['Clients', 'Employés', 'Services', 'Demandes'],
                datasets: [{
                    label: 'Statistiques',
                    data: [<?= $total_clients ?>, <?= $total_employees ?>, <?= $total_services ?>, <?= $total_demandes ?>],
                    backgroundColor: [
                        'rgba(255, 99, 132, 0.2)',
                        'rgba(54, 162, 235, 0.2)',
                        'rgba(255, 206, 86, 0.2)',
                        'rgba(75, 192, 192, 0.2)'
                    ],
                    borderColor: [
                        'rgba(255, 99, 132, 1)',
                        'rgba(54, 162, 235, 1)',
                        'rgba(255, 206, 86, 1)',
                        'rgba(75, 192, 192, 1)'
                    ],
                    borderWidth: 1
                }]
            },
            options: {
                scales: {
                    y: {
                        beginAtZero: true
                    }
                }
            }
        });

        // Chart.js configuration for Top Employees Bar Chart
        const ctxTopEmployees = document.getElementById('topEmployeesChart').getContext('2d');
        const topEmployeesChart = new Chart(ctxTopEmployees, {
            type: 'bar',
            data: {
                labels: [
                    <?php foreach ($top_employees as $employee): ?>
                        '<?= htmlspecialchars($employee['employee_name']) ?>',
                    <?php endforeach; ?>
                ],
                datasets: [{
                    label: 'Nombre de Demandes Traitées',
                    data: [
                        <?php foreach ($top_employees as $employee): ?>
                            <?= $employee['total_demandes'] ?>,
                        <?php endforeach; ?>
                    ],
                    backgroundColor: 'rgba(54, 162, 235, 0.2)',
                    borderColor: 'rgba(54, 162, 235, 1)',
                    borderWidth: 1
                }]
            },
            options: {
                indexAxis: 'y',
                scales: {
                    x: {
                        beginAtZero: true
                    }
                }
            }
        });
    </script>

</body>

</html>
