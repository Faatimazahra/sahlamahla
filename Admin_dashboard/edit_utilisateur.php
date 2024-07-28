<?php
session_start();

// تحقق من إذا كان المستخدم قد سجل الدخول
if (!isset($_SESSION['user_id'])) {
    // إذا لم يكن مسجل الدخول، قم بإعادة التوجيه إلى صفحة تسجيل الدخول
    header('Location: /SahlaMahla/Home/login.php');
    exit();
}

// منع التخزين المؤقت للصفحات
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");
?>
<?php
// Prevent output before headers are sent
ob_start();

require_once __DIR__ . '/header.php';
require_once __DIR__ . '/cnx/cnx.php';

// Initialize variables
$user = [];
$working_hours = [];
$services = [];
$employee_services = [];

if (isset($_GET['id'])) {
    $id = $_GET['id'];

    try {
        $conn = new PDO($dsn, DB_USER, DB_PASSWORD);
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        // Fetch user details
        $stmt = $conn->prepare("SELECT * FROM utilisateurs WHERE id = :id");
        $stmt->bindParam(':id', $id);
        $stmt->execute();
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        // Fetch the existing working hours for the user
        $stmt = $conn->prepare("SELECT * FROM horaires_travail WHERE employé_id = :id");
        $stmt->bindParam(':id', $id);
        $stmt->execute();
        $working_hours = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Fetch all available services
        $stmt = $conn->prepare("SELECT id, nom FROM services");
        $stmt->execute();
        $services = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Fetch the services associated with the employee
        $stmt = $conn->prepare("SELECT service_id FROM employe_services WHERE employe_id = :id");
        $stmt->bindParam(':id', $id);
        $stmt->execute();
        $employee_services = $stmt->fetchAll(PDO::FETCH_COLUMN);

    } catch (PDOException $e) {
        echo "Error: " . $e->getMessage();
        exit(); // Exit on error to prevent further execution
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = $_POST['id'];
    $nom = $_POST['nom'];
    $email = $_POST['email'];
    $telephone = $_POST['telephone'];
    $adresse = $_POST['adresse'];
    $role = $_POST['role'];
    $selected_services = $_POST['services'] ?? [];

    try {
        // Update user details
        $stmt = $conn->prepare("UPDATE utilisateurs SET nom = :nom, email = :email, téléphone = :telephone, adresse = :adresse, rôle = :role WHERE id = :id");
        $stmt->bindParam(':id', $id);
        $stmt->bindParam(':nom', $nom);
        $stmt->bindParam(':email', $email);
        $stmt->bindParam(':telephone', $telephone);
        $stmt->bindParam(':adresse', $adresse);
        $stmt->bindParam(':role', $role);
        $stmt->execute();

        // Delete existing working hours
        $stmt = $conn->prepare("DELETE FROM horaires_travail WHERE employé_id = :id");
        $stmt->bindParam(':id', $id);
        $stmt->execute();

        // Insert new working hours
        $stmt = $conn->prepare("INSERT INTO horaires_travail (employé_id, jour_semaine, heure_debut, heure_fin) VALUES (:employe_id, :jour_semaine, :heure_debut, :heure_fin)");
        $stmt->bindParam(':employe_id', $id);

        foreach ($_POST['jour_semaine'] as $key => $jour) {
            $heure_debut = $_POST['heure_debut'][$key];
            $heure_fin = $_POST['heure_fin'][$key];

            $stmt->bindParam(':jour_semaine', $jour);
            $stmt->bindParam(':heure_debut', $heure_debut);
            $stmt->bindParam(':heure_fin', $heure_fin);
            $stmt->execute();
        }

        // Delete existing employee services
        $stmt = $conn->prepare("DELETE FROM employe_services WHERE employe_id = :id");
        $stmt->bindParam(':id', $id);
        $stmt->execute();

        // Insert new employee services
        $stmt = $conn->prepare("INSERT INTO employe_services (employe_id, service_id) VALUES (:employe_id, :service_id)");
        $stmt->bindParam(':employe_id', $id);
        foreach ($selected_services as $service_id) {
            $stmt->bindParam(':service_id', $service_id);
            $stmt->execute();
        }

        // Redirect after successful update
        header("Location: employees.php?update_success=1");
        exit(); // Exit to prevent further execution after redirect
    } catch (PDOException $e) {
        echo "Error: " . $e->getMessage();
        exit(); // Exit on error to prevent further execution
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="styles.css">
    <title>Modifier utilisateur</title>
</head>
<body>

<div class="row tm-content-row tm-mt-big">
    <div class="col-xl-13 col-lg-12 tm-md-12 tm-sm-12 tm-col">
        <div class="bg-white tm-block h-100">
            <a href="employees.php" class="btn btn-secondary btn-back">
                <i class="fas fa-arrow-left"></i> Retour
            </a>
            <h2 class="tm-block-title d-inline-block">Modifier utilisateur</h2>

            <form action="" method="post" class="mt-3">
                <input type="hidden" name="id" value="<?php echo htmlspecialchars($user['id']); ?>">
                <div class="form-group">
                    <label for="nom">Nom</label>
                    <input type="text" class="form-control" id="nom" name="nom" value="<?php echo htmlspecialchars($user['nom']); ?>" required>

                    <label for="email">Email</label>
                    <input type="email" class="form-control" id="email" name="email" value="<?php echo htmlspecialchars($user['email']); ?>" required>

                    <label for="telephone">Téléphone</label>
                    <input type="text" class="form-control" id="telephone" name="telephone" value="<?php echo htmlspecialchars($user['téléphone']); ?>" required>

                    <label for="adresse">Adresse</label>
                    <input type="text" class="form-control" id="adresse" name="adresse" value="<?php echo htmlspecialchars($user['adresse']); ?>" required>

                    <label for="role">Rôle</label>
                    <select class="form-control" id="role" name="role" required>
                        <option value="client" <?php if ($user['rôle'] == 'client') echo 'selected'; ?>>Client</option>
                        <option value="employé" <?php if ($user['rôle'] == 'employé') echo 'selected'; ?>>Employé</option>
                        <option value="admin" <?php if ($user['rôle'] == 'admin') echo 'selected'; ?>>Admin</option>
                    </select>

                    <label for="services">Services</label>
                    <br>
                    <select class="form-control" id="services" name="services[]" multiple>
                        <?php
                        foreach ($services as $service) {
                            $selected = in_array($service['id'], $employee_services) ? 'selected' : '';
                            echo "<option value='{$service['id']}' $selected>{$service['nom']}</option>";
                        }
                        ?>
                    </select>
                    <br>

                    <label for="jour_semaine">Jour de la semaine</label>
                    <br>
                    <?php
                    $jours_semaine = array('Lundi', 'Mardi', 'Mercredi', 'Jeudi', 'Vendredi', 'Samedi', 'Dimanche');
                    foreach ($jours_semaine as $jour) {
                        $checked = '';
                        foreach ($working_hours as $wh) {
                            if ($wh['jour_semaine'] == $jour) {
                                $checked = 'checked';
                                break;
                            }
                        }
                        echo "<label><input type='checkbox' name='jour_semaine[]' value='$jour' $checked> $jour</label><br>";
                    }
                    ?>
                    <br>

                    <label for="heure_debut">Heure de début</label>
                    <br>
                    <?php
                    foreach ($working_hours as $wh) {
                        $jour = $wh['jour_semaine'];
                        $heure_debut = $wh['heure_debut'];
                        echo "<input type='time' name='heure_debut[]' value='$heure_debut' required> pour $jour<br>";
                    }
                    ?>
                    <br>

                    <label for="heure_fin">Heure de fin</label>
                    <br>
                    <?php
                    foreach ($working_hours as $wh) {
                        $heure_fin = $wh['heure_fin'];
                        echo "<input type='time' name='heure_fin[]' value='$heure_fin' required><br>";
                    }
                    ?>
                    <br>
                </div>
                <button type="submit" class="btn btn-primary btn-block">Mettre à jour</button>
            </form>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/footer.php'; ?>

</body>
</html>

<?php
// Flush buffer and turn off output buffering
ob_end_flush();
?>
