
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
?><?php
require_once __DIR__ . '/cnx/cnx.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nom = $_POST['nom'];
    $description = $_POST['description'];
    $prix = $_POST['prix'];

    // Handle file upload
    $target_dir = "images/";
    $target_file = $target_dir . basename($_FILES["image"]["name"]);
    $uploadOk = 1;
    $imageFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));

    // Check if image file is a actual image or fake image
    $check = getimagesize($_FILES["image"]["tmp_name"]);
    if ($check !== false) {
        $uploadOk = 1;
    } else {
        $uploadOk = 0;
    }

    if ($uploadOk && move_uploaded_file($_FILES["image"]["tmp_name"], $target_file)) {
        // File is valid, insert the record into the database
        try {
            $conn = new PDO($dsn, DB_USER, DB_PASSWORD);
            $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $sql = "INSERT INTO services (nom, description, url_image, prix) VALUES (?, ?, ?, ?)";
            $stmt = $conn->prepare($sql);
            $stmt->execute([$nom, $description, basename($target_file), $prix]);
            header('Location: service.php?insert_success=1');
        } catch (PDOException $e) {
            header('Location: service.php?insert_error=1');
        }
    } else {
        header('Location: service.php?insert_error=1');
    }
} else {
    header('Location: service.php?insert_error=1');
}
exit();
