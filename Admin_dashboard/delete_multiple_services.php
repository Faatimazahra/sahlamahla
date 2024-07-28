
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
require_once __DIR__ . '/cnx/cnx.php';

if (isset($_POST['ids'])) {
    $ids = $_POST['ids'];

    // Database connection using PDO
    try {
        $conn = new PDO($dsn, DB_USER, DB_PASSWORD);
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        // Create a parameterized query to delete multiple records
        $in  = str_repeat('?,', count($ids) - 1) . '?';
        $sql = "DELETE FROM services WHERE id IN ($in)";
        $stmt = $conn->prepare($sql);

        if ($stmt->execute($ids)) {
            header('Location: service.php?delete_success=1');
        } else {
            header('Location: service.php?delete_error=1');
        }
    } catch (PDOException $e) {
        header('Location: service.php?delete_error=1');
    }
} else {
    header('Location: service.php?delete_error=1');
}
exit();
