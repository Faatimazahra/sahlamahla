<?php



// Include database connection
require_once __DIR__ . '/cnx/cnx.php';

// Include header
require_once __DIR__ . '/header.php';
?>

<!-- Display success message if form was successfully submitted -->
<?php if (isset($_GET['insert_success'])): ?>
    <div class="alert alert-success" role="alert">
        Merci ! Votre demande a été soumise avec succès.
    </div>
<?php endif; ?>

<!-- Display error message if there was an error -->
<?php if (isset($_GET['insert_error'])): ?>
    <div class="alert alert-danger" role="alert">
        Erreur lors de la soumission de la demande : <?php echo htmlspecialchars($_GET['error_message']); ?>
    </div>
<?php endif; ?>

<?php if (isset($_GET['file_upload_error'])): ?>
    <div class="alert alert-danger" role="alert">
        Erreur lors du téléchargement du fichier. Veuillez réessayer.
    </div>
<?php endif; ?>

<?php
// Database connection using PDO
try {
    $conn = new PDO($dsn, DB_USER, DB_PASSWORD);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    echo "Connection failed: " . $e->getMessage();
    exit();
}

// Fetch services from the database
try {
    $stmt = $conn->prepare("SELECT id, nom FROM services");
    $stmt->execute();
    $services = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    echo "Error fetching services: " . $e->getMessage();
    exit();
}
?>

<!-- Contact Start -->
<div class="container py-5">
    <div class="contact-detail position-relative p-5">
        <h2 class="text-center mb-5">Postulez Maintenant si vous souhaitez travailler avec nous</h2>
        <div class="row justify-content-center">
            <div class="col-lg-6 wow fadeIn" data-wow-delay=".5s">
                <div class="p-5 rounded contact-form">
                    <form action="insert_demandeemploi.php" method="post" enctype="multipart/form-data">
                        <div class="mb-4">
                            <input type="text" class="form-control border-0 py-3" name="nom" placeholder="Votre Nom" required>
                        </div>
                        <div class="mb-4">
                            <input type="email" class="form-control border-0 py-3" name="email" placeholder="Votre Email" required>
                        </div>
                        <div class="mb-4">
                            <input type="text" class="form-control border-0 py-3" name="téléphone" placeholder="Numéro de Téléphone">
                        </div>
                        <div class="mb-4">
                            <input type="text" class="form-control border-0 py-3" name="adresse" placeholder="Adresse">
                        </div>
                        <div class="mb-4">
                            <select class="form-control border-0 py-3" name="service" required>
                                <option value="" disabled selected>Choisissez un service</option>
                                <?php foreach ($services as $service): ?>
                                    <option value="<?php echo $service['nom']; ?>"><?php echo $service['nom']; ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="mb-4">
                            <label for="cv_pdf" class="form-label">Téléchargez CV (PDF)</label>
                            <input type="file" class="form-control" id="cv_pdf" name="cv_pdf" accept=".pdf" required>
                        </div>
                        <div class="text-start">
                            <button type="submit" class="btn bg-primary text-white py-3 px-5" name="submit">Envoyer</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
<!-- Contact End -->

<?php require_once __DIR__.'/footer.php'; ?>

<style>
    .contact-form {
        max-width: 100%;
        margin: auto;
    }
</style>
