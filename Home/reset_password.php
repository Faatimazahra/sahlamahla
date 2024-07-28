<?php
session_start();
require_once __DIR__ . '/header.php'; // Include header if needed

require 'vendor/autoload.php'; // Include Composer autoload

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Database connection
require 'db_connect.php'; // Ensure this file has your database connection setup

if (isset($_GET['token'])) {
    $token = trim($_GET['token']);

    try {
        // Check if the token is valid
        $stmt = $conn->prepare("SELECT email FROM password_resets WHERE token = ? AND expires_at > NOW()");
        $stmt->execute([$token]);
        $reset = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($reset) {
            $email = $reset['email'];

            // Display form to enter a new password
            if (isset($_POST['new_password'])) {
                $new_password = trim($_POST['new_password']);

                // Hash the new password
                $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);

                // Update the password in the database
                $stmt = $conn->prepare("UPDATE utilisateurs SET mot_de_passe = ? WHERE email = ?");
                $stmt->execute([$hashed_password, $email]);

                // Delete the used token
                $stmt = $conn->prepare("DELETE FROM password_resets WHERE token = ?");
                $stmt->execute([$token]);

                $_SESSION['success'] = 'Votre mot de passe a été réinitialisé avec succès.';
                echo '<div class="container py-5"><div class="row justify-content-center"><div class="col-lg-6"><p class="text-success">' . $_SESSION['success'] . '</p></div></div></div>';
            } else {
                // Display the form to enter a new password
                echo '<div class="container py-5">
                        <div class="row justify-content-center">
                            <div class="col-lg-6">
                                <div class="p-5 rounded bg-light">
                                    <h2 class="mb-4">Réinitialiser le mot de passe</h2>
                                    <form method="post">
                                        <div class="mb-4">
                                            <label for="new_password" class="form-label">Nouveau mot de passe:</label>
                                            <input type="password" name="new_password" id="new_password" class="form-control" required>
                                        </div>
                                        <button type="submit" class="btn btn-primary">Réinitialiser le mot de passe</button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>';
            }
        } else {
            echo '<div class="container py-5"><div class="row justify-content-center"><div class="col-lg-6"><p class="text-danger">Token invalide ou expiré.</p></div></div></div>';
        }
    } catch (PDOException $e) {
        echo '<div class="container py-5"><div class="row justify-content-center"><div class="col-lg-6"><p class="text-danger">Erreur de base de données: ' . $e->getMessage() . '</p></div></div></div>';
    }
}
require_once __DIR__ . '/footer.php';
?>
