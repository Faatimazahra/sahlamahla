<?php
ob_start(); // Start output buffering
session_start();
require_once __DIR__ . '/header.php'; 
require_once 'db_connect.php';  // Include database connection

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['register'])) {
    // Sanitize and validate inputs
    $nom = htmlspecialchars(trim($_POST['nom']));
    $email = htmlspecialchars(trim($_POST['email']));
    $mot_de_passe = htmlspecialchars(trim($_POST['mot_de_passe']));
    $telephone = htmlspecialchars(trim($_POST['telephone']));
    $adresse = htmlspecialchars(trim($_POST['adresse']));
    $role = 'client';  // Assuming default role for new users

    // Hash the password
    $hashed_password = password_hash($mot_de_passe, PASSWORD_DEFAULT);

    try {
        // Prepare and execute SQL statement to insert new user
        $sql = "INSERT INTO utilisateurs (nom, email, mot_de_passe, téléphone, adresse, rôle) 
                VALUES (?, ?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->execute([$nom, $email, $hashed_password, $telephone, $adresse, $role]);

        // Check if registration was successful
        if ($stmt->rowCount() > 0) {
            $_SESSION['success'] = 'Inscription réussie. Vous pouvez maintenant vous connecter.';
            header('Location: /SahlaMahla/Home/register.php');
            exit(); // Ensure no further output after header redirect
        } else {
            $_SESSION['error'] = 'L’enregistrement a échoué. Veuillez réessayer.';
        }
    } catch (PDOException $e) {
        // Handle database errors
        $_SESSION['error'] = "Registration failed: " . $e->getMessage();
    }
}
?>

<!-- Registration Form -->
<div class="container py-5">
    <div class="contact-detail position-relative p-5">
        <div class="row justify-content-center">
            <div class="col-lg-6 wow fadeIn">
                <div class="p-5 rounded contact-form">
                    <form action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>" method="post">
                   <?php if (isset($_SESSION['success'])) {
    echo '<div class="alert alert-success">' . $_SESSION['success'] . '</div>';
    unset($_SESSION['success']); // Clear the success message
} elseif (isset($_SESSION['error'])) {
    echo '<div class="alert alert-danger">' . $_SESSION['error'] . '</div>';
    unset($_SESSION['error']); // Clear the error message
}
?>
                        <div class="mb-4">
                            <input type="text" class="form-control border-0 py-3" name="nom" placeholder="Votre nom" required>
                        </div>
                        <div class="mb-4">
                            <input type="email" class="form-control border-0 py-3" name="email" placeholder="Votre email" required>
                        </div>
                        <div class="mb-4">
                            <input type="password" class="form-control border-0 py-3" name="mot_de_passe" placeholder="Mot de passe" required>
                        </div>
                        <div class="mb-4">
                            <input type="text" class="form-control border-0 py-3" name="telephone" placeholder="Votre numéro de téléphone">
                        </div>
                        <div class="mb-4">
                            <input type="text" class="form-control border-0 py-3" name="adresse" placeholder="Votre lieu">
                        </div>
                        <div class="text-start">
                            <button class="btn bg-primary text-white py-3 px-5" type="submit" name="register">Registre</button>
                            <button class="btn bg-primary text-white py-3 px-5" type="button" onclick="redirectToLogin()">Connexion</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function redirectToLogin() {
    window.location.href = '/SahlaMahla/Home/login.php';
}
</script>

<?php require_once __DIR__ . '/footer.php'; ?>
<style>
    .contact-form {
        max-width: 100%;
        margin: auto;
    }
</style>

<?php
ob_end_flush(); // End output buffering and flush buffer

// Display success or error message if set

