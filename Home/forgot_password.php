<?php
session_start();
require_once __DIR__ . '/header.php';
?>

<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-lg-6">
            <div class="p-5 rounded contact-form">
                <h2 class="mb-4">Mot de passe oublié</h2>
                <div id="message">
                    <?php
                    // Display success or error messages from the session
                    if (isset($_SESSION['success'])) {
                        echo '<p class="text-success">' . $_SESSION['success'] . '</p>';
                        unset($_SESSION['success']);
                    }
                    if (isset($_SESSION['error'])) {
                        echo '<p class="text-danger">' . $_SESSION['error'] . '</p>';
                        unset($_SESSION['error']);
                    }
                    ?>
                </div>
                <form id="forgotPasswordForm" action="process_forgot_password.php" method="post">
                    <div class="mb-4">
                        <input type="email" class="form-control border-0 py-3" name="email" placeholder="Votre email" required>
                    </div>
                    <button class="btn bg-primary text-white py-3 px-5" name="pwdrst" type="submit">Réinitialiser le mot de passe</button>
                </form>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/footer.php'; ?>

<style>
.contact-form {
    max-width: 100%;
    margin: auto;
}
</style>
