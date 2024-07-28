<?php
// Start output buffering
ob_start();

// Start the session before any HTML output
session_start();

require_once __DIR__ . '/cnx/cnx.php';
require_once __DIR__ . '/header.php';

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Get the logged-in user's ID
$user_id = $_SESSION['user_id'];

// Database connection using PDO
try {
    $conn = new PDO($dsn, DB_USER, DB_PASSWORD);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Fetch user data
    $stmt = $conn->prepare("SELECT * FROM utilisateurs WHERE id = :id");
    $stmt->bindParam(':id', $user_id, PDO::PARAM_INT);
    $stmt->execute();
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    // Check if the user data was fetched successfully
    if (!$user) {
        throw new Exception('User not found');
    }

    // Handle form submission to update profile
    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        $nom = htmlspecialchars($_POST['nom']);
        $email = htmlspecialchars($_POST['email']);
        $phone = htmlspecialchars($_POST['phone']);
        $adresse = htmlspecialchars($_POST['adresse']);
        
        // Update user data
        $stmt = $conn->prepare("UPDATE utilisateurs SET nom = :nom, email = :email, téléphone = :phone, adresse = :adresse WHERE id = :id");
        $stmt->bindParam(':id', $user_id, PDO::PARAM_INT);
        $stmt->bindParam(':nom', $nom, PDO::PARAM_STR);
        $stmt->bindParam(':email', $email, PDO::PARAM_STR);
        $stmt->bindParam(':phone', $phone, PDO::PARAM_STR);
        $stmt->bindParam(':adresse', $adresse, PDO::PARAM_STR);
        $stmt->execute();

        // Check if password field is set and update password
        if (!empty($_POST['password'])) {
            $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
            $stmt = $conn->prepare("UPDATE utilisateurs SET mot_de_passe = :password WHERE id = :id");
            $stmt->bindParam(':id', $user_id, PDO::PARAM_INT);
            $stmt->bindParam(':password', $password, PDO::PARAM_STR);
            $stmt->execute();
        }

        // Check if role field is set and update role (only if user is admin)
        if ($_SESSION['user_role'] === 'admin' && isset($_POST['role'])) {
            $role = $_POST['role'];
            $stmt = $conn->prepare("UPDATE utilisateurs SET rôle = :role WHERE id = :id");
            $stmt->bindParam(':id', $user_id, PDO::PARAM_INT);
            $stmt->bindParam(':role', $role, PDO::PARAM_STR);
            $stmt->execute();
        }

        // Redirect to avoid form resubmission
        header("Location: profile.php?update_success=1");
        exit();
    }
} catch (PDOException $e) {
    echo "Connection failed: " . $e->getMessage();
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}

// End output buffering and flush output
ob_end_flush();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profil</title>
    <link rel="stylesheet" href="path/to/your/css/style.css">
</head>
<body>
<div class="row tm-content-row tm-mt-big">
    <div class="col-xl-13 col-lg-12 tm-md-12 tm-sm-12 tm-col">
        <div class="bg-white tm-block h-100">
            <div class="profile-container">
                <h2 class="tm-block-title d-inline-block">Informations sur le profil</h2>
                <?php if (isset($_GET['update_success'])): ?>
                    <div class="alert alert-success" role="alert">Profil mis à jour avec succès !</div>
                <?php endif; ?>
                <form id="profile-form" action="profile.php" method="POST">
                    <div>
                        
                        <input type="text" class="form-control"id="nom" name="nom" value="<?php echo htmlspecialchars($user['nom']); ?>" required>
                    </div>
                    <div>
                        
                        <input type="email"class="form-control" id="email" name="email" value="<?php echo htmlspecialchars($user['email']); ?>" required>
                    </div>
                    <div>
                        <input type="text" class="form-control" id="phone" name="phone" value="<?php echo htmlspecialchars($user['téléphone']); ?>">
                    </div>
                    <div>
                        <input type="text" class="form-control" id="adresse" name="adresse" value="<?php echo htmlspecialchars($user['adresse']); ?>">
                    </div>
                    <?php if ($_SESSION['user_role'] === 'admin'): ?>
                    <div>
                        
                        <input type="password" class="form-control" id="password"placeholder="Nouveau mot de passe ..." name="password">
                    </div>
                    <div>
                        
                        <select class="form-control" id="role" name="role">
                            <option value="admin" <?php echo ($user['rôle'] === 'admin') ? 'selected' : ''; ?>>Admin</option>
                            <option value="employé" <?php echo ($user['rôle'] === 'employé') ? 'selected' : ''; ?>>Employee</option>
                            <option value="client" <?php echo ($user['rôle'] === 'client') ? 'selected' : ''; ?>>Client</option>
                        </select>
                    </div>
                    <?php endif; ?>
                    <button class="btn btn-primary" type="submit">Mettre à jour le profil</button>
                </form>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/footer.php'; ?>
</body>
</html>
