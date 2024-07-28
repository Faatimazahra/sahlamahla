<?php
session_start();
require_once __DIR__ . '/header.php'; 
?>

<!-- Login Start -->
<div class="container py-5">
    <div class="contact-detail position-relative p-5">
        <div class="row justify-content-center">
            <div class="col-lg-6 wow fadeIn">
                <div class="p-5 rounded contact-form">
                    <form action="process_login.php" method="post">
                        <div class="mb-4">
                            <input type="email" class="form-control border-0 py-3" name="email" placeholder="Votre email" required>
                        </div>
                        <div class="mb-4">
                            <input type="password" class="form-control border-0 py-3" name="password" placeholder="Mot de passe" required>
                        </div>
                        <div class="text-start mb-4">
                            <button class="btn bg-primary text-white py-3 px-5" type="submit">Connexion</button>
                            <button class="btn bg-primary text-white py-3 px-5" type="button" onclick="redirectToRegister()">Registre</button>
                        </div>
                        <div class="text-start">
                            <a href="/SahlaMahla/Home/forgot_password.php" class="text-decoration-none">Mot de passe oublié ?</a>
                        </div>
                    </form>
                    <?php
                    // Display error message if it exists 
                    if (isset($_SESSION['error'])) {
                        echo '<p class="text-danger">' . $_SESSION['error'] . '</p>';
                        unset($_SESSION['error']); // Clear the error message after displaying
                    }
                    ?>
                </div>
            </div>
        </div>
    </div>
</div>
<!-- Login End -->

<script>
function redirectToRegister() {
    window.location.href = '/SahlaMahla/Home/register.php';
}
</script>

<?php require_once __DIR__ . '/footer.php'; ?>
<style>
.contact-form {
    max-width: 100%;
    margin: auto;
}
</style>
