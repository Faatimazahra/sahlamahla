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
ob_start();
require_once __DIR__ . '/header.php';
require_once __DIR__ . '/cnx/cnx.php';

// Database connection using PDO
try {
    $conn = new PDO($dsn, DB_USER, DB_PASSWORD);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    echo "Connection failed: " . $e->getMessage();
}

$id = $_GET['id'];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nom = $_POST['nom'];
    $description = $_POST['description'];
    $prix = $_POST['prix'];
    $url_image = $_POST['url_image'];

    // Handle file upload
    if ($_FILES['image']['name']) {
        $target_dir = __DIR__ . "/images/";
        if (!is_dir($target_dir)) {
            mkdir($target_dir, 0777, true);
        }
        $target_file = $target_dir . basename($_FILES['image']['name']);
        $imageFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));
        $uploadOk = 1;

        // Check if image file is an actual image or fake image
        $check = getimagesize($_FILES["image"]["tmp_name"]);
        if($check !== false) {
            $uploadOk = 1;
        } else {
            echo "File is not an image.";
            $uploadOk = 0;
        }

        // Check if file already exists
        if (file_exists($target_file)) {
            echo "Sorry, file already exists.";
            $uploadOk = 0;
        }

        // Check file size
        if ($_FILES["image"]["size"] > 500000) {
            echo "Sorry, your file is too large.";
            $uploadOk = 0;
        }

        // Allow certain file formats
        if($imageFileType != "jpg" && $imageFileType != "png" && $imageFileType != "jpeg" && $imageFileType != "gif" ) {
            echo "Sorry, only JPG, JPEG, PNG & GIF files are allowed.";
            $uploadOk = 0;
        }

        if ($uploadOk && move_uploaded_file($_FILES["image"]["tmp_name"], $target_file)) {
            $url_image = basename($_FILES["image"]["name"]);
        } else {
            echo "Sorry, there was an error uploading your file.";
        }
    }

    $sql = "UPDATE services SET nom = ?, description = ?, prix = ?, url_image = ? WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->execute([$nom, $description, $prix, $url_image, $id]);

    header("Location: service.php?success=1");
    exit();
}

$sql = "SELECT * FROM services WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->execute([$id]);
$service = $stmt->fetch(PDO::FETCH_ASSOC);
?>

<div class="row tm-content-row tm-mt-big">
    <div class="col-xl-13 col-lg-12 tm-md-12 tm-sm-12 tm-col">
        <div class="bg-white tm-block h-100">
        <a href="service.php" class="btn btn-secondary btn-back">
                    <i class="fas fa-arrow-left"></i> 
                </a>
            <h2 class="tm-block-title d-inline-block">Modifier le service</h2>

            <form action="" method="post" enctype="multipart/form-data" class="mt-3">
                <div class="form-group">
                    <label for="nom">Nom</label>
                    <input type="text" class="form-control" id="nom" name="nom" value="<?php echo htmlspecialchars($service['nom']); ?>" required>

                    <label for="description">Description</label>
                    <input type="text" class="form-control" id="description" name="description" value="<?php echo htmlspecialchars($service['description']); ?>" required>

                    <label for="image">Image</label>
                    <input type="file" class="form-control" id="image" name="image">
                    <input type="hidden" name="url_image" value="<?php echo htmlspecialchars($service['url_image']); ?>">
                    <img src="images/<?php echo htmlspecialchars($service['url_image']); ?>" alt="Image" style="width: 100px; height: 100px;">

                    <label for="prix">Prix</label>
                    <input type="text" class="form-control" id="prix" name="prix" value="<?php echo htmlspecialchars($service['prix']); ?>" required>
                </div>
                <button type="submit" class="btn btn-primary btn-block">Mettre à jour</button>
            </form>
        </div>
    </div>
</div>

<?php
require_once __DIR__ . '/footer.php';
ob_end_flush();
?>
