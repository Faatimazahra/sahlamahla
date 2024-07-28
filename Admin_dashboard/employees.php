<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header('Location: /SahlaMahla/Home/login.php');
    exit();
}

header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");

require_once __DIR__ . '/header.php';
require_once __DIR__ . '/cnx/cnx.php';

// Handle search functionality
$search = isset($_GET['search']) ? $_GET['search'] : '';

// Initialize query parameters
$whereClause = "WHERE u.rôle = 'employé'";
$bindings = [];

if (!empty($search)) {
    $whereClause .= " AND u.nom LIKE :search";
    $bindings['search'] = '%' . $search . '%';
}

// Query to fetch employees and their services
$query = "SELECT u.id, u.nom, u.email, u.téléphone, u.adresse, 
             GROUP_CONCAT(DISTINCT s.nom SEPARATOR ', ') AS services,
             GROUP_CONCAT(CONCAT_WS(' ', ht.jour_semaine, ht.heure_debut, '-', ht.heure_fin) SEPARATOR ', ') AS horaires_travail
          FROM utilisateurs u
          LEFT JOIN employe_services es ON u.id = es.employe_id
          LEFT JOIN services s ON es.service_id = s.id
          LEFT JOIN horaires_travail ht ON u.id = ht.employé_id
          $whereClause
          GROUP BY u.id
          ORDER BY u.id DESC";
$stmt = $conn->prepare($query);

// Bind parameters if search is used
foreach ($bindings as $key => $value) {
    $stmt->bindValue(":$key", $value, PDO::PARAM_STR);
}

$stmt->execute();
?>

<!-- row -->
<div class="row tm-content-row tm-mt-big">
    <div class="col-xl-8 col-lg-12 tm-md-12 tm-sm-12 tm-col">
        <div class="bg-white tm-block h-100">

            <form action="" method="get" class="input-group mb-3">
                <input class="form-control mr-sm-2" type="search" name="search" placeholder="Rechercher par nom" aria-label="Search" value="<?php echo htmlspecialchars($search); ?>">
                <button class="btn btn-primary" type="submit">Rechercher</button>
            </form>

            <?php if (isset($_GET['delete_success'])): ?>
                <div class="alert alert-success" role="alert">Employé supprimé avec succès !</div>
            <?php elseif (isset($_GET['delete_error'])): ?>
                <div class="alert alert-danger" role="alert">Une erreur s’est produite lors de la suppression de l'employé.</div>
            <?php elseif (isset($_GET['insert_success'])): ?>
                <div class="alert alert-success" role="alert">Nouvel employé ajouté avec succès !</div>
            <?php elseif (isset($_GET['insert_error'])): ?>
                <div class="alert alert-danger" role="alert">Une erreur s’est produite lors de l’ajout de l'employé.</div>
            <?php elseif (isset($_GET['update_success'])): ?>
                <div class="alert alert-success" role="alert">Employé mis à jour avec succès !</div>
            <?php elseif (isset($_GET['update_error'])): ?>
                <div class="alert alert-danger" role="alert">Une erreur s’est produite lors de la mise à jour de l'employé.</div>
            <?php endif; ?>

            <div class="table-responsive">
                <form id="delete-form" action="delete_multiple_utilisateurs.php" method="post">
                    <table class="table table-hover table-striped tm-table-striped-even mt-3">
                        <thead>
                            <tr class="tm-bg-gray">
                                <th scope="col">Select</th>
                                <th scope="col">Nom</th>
                                <th scope="col" class="text-center">Email</th>
                                <th scope="col" class="text-center">Téléphone</th>
                                <th scope="col" class="text-center">Adresse</th>
                                <th scope="col" class="text-center">Services</th>
                                <th scope="col" class="text-center">Horaires de travail</th>
                                <th scope="col">Delete</th>
                                <th scope="col">Edit</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                                echo "<tr>
                                        <th scope='row'>
                                            <input type='checkbox' name='ids[]' value='{$row['id']}' aria-label='Checkbox'>
                                        </th>
                                        <td class='tm-employees-name'>{$row['nom']}</td>
                                        <td class='text-center'>{$row['email']}</td>
                                        <td class='text-center'>{$row['téléphone']}</td>
                                        <td class='text-center'>{$row['adresse']}</td>
                                        <td class='text-center'>{$row['services']}</td>
                                        <td class='text-center'>{$row['horaires_travail']}</td>
                                        <td class='text-center'>
                                            <form action='delete_utilisateur.php' method='post' style='display:inline-block'>
                                                <input type='hidden' name='id' value='{$row['id']}'>
                                                <button type='submit' style='border:none;background:none;color:red;cursor:pointer;'><i class='fas fa-trash-alt tm-trash-icon'></i></button>
                                            </form>
                                        </td>
                                        <td class='text-center'><a href='edit_utilisateur.php?id={$row['id']}'><i class='fas fa-edit tm-edit-icon'></i></a></td>
                                      </tr>";
                            }
                            ?>
                        </tbody>
                    </table>
                </form>
            </div>

            <div class="tm-table-mt tm-table-actions-row">
                <div class="tm-table-actions-col-left">
                    <button class="btn btn-danger" onclick="document.getElementById('delete-form').submit();">Supprimer les éléments sélectionnés</button>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-4 col-lg-12 tm-md-12 tm-sm-12 tm-col">
        <div class="bg-white tm-block h-100">
            <h2 class="tm-block-title d-inline-block">Ajouter un nouvel employé</h2>
            <form action="insert_utilisateur.php" method="post" enctype="multipart/form-data" class="mt-3">
                <div class="form-group">
                    <label for="nom">Nom</label>
                    <input type="text" class="form-control" id="nom" name="nom" placeholder="Entrez le nom de l'employé" required>

                    <label for="email">Email</label>
                    <input type="email" class="form-control" id="email" name="email" placeholder="Entrez l'email de l'employé" required>

                    <label for="mot_de_passe">Mot de passe</label>
                    <input type="password" class="form-control" id="mot_de_passe" name="mot_de_passe" required>

                    <label for="telephone">Téléphone</label>
                    <input type="text" class="form-control" id="telephone" name="telephone" placeholder="Entrez le téléphone de l'employé" required>

                    <label for="adresse">Adresse</label>
                    <input type="text" class="form-control" id="adresse" name="adresse" placeholder="Entrez l'adresse de l'employé" required>

                    <label for="role">Rôle</label>
                    <select class="dropdown-item" id="role" name="role" required>
                        <option value="client">Client</option>
                        <option value="employé">Employé</option>
                        <option value="admin">Admin</option>
                    </select>

                    <label for="services">Services</label>
                    <select class="dropdown-item" id="services" name="services[]" multiple required>
                        <?php
                        $servicesQuery = $conn->query("SELECT id, nom FROM services");
                        while ($service = $servicesQuery->fetch(PDO::FETCH_ASSOC)) {
                            echo "<option value='{$service['id']}'>{$service['nom']}</option>";
                        }
                        ?>
                    </select>

                    <label for="jour_semaine">Jour de la semaine</label>
                    <select class="dropdown-item" id="jour_semaine" name="jour_semaine[]" multiple required>
                        <option value="Lundi">Lundi</option>
                        <option value="Mardi">Mardi</option>
                        <option value="Mercredi">Mercredi</option>
                        <option value="Jeudi">Jeudi</option>
                        <option value="Vendredi">Vendredi</option>
                        <option value="Samedi">Samedi</option>
                        <option value="Dimanche">Dimanche</option>
                    </select>

                    <label for="heure_debut">Heure de début</label>
                    <input type="time" class="form-control" id="heure_debut" name="heure_debut" required>

                    <label for="heure_fin">Heure de fin</label>
                    <input type="time" class="form-control" id="heure_fin" name="heure_fin" required>
                </div>
                <button type="submit" class="btn btn-primary btn-block">Ajouter un employé</button>
            </form>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/footer.php'; ?>
