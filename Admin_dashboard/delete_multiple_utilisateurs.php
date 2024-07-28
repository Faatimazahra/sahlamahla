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

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $ids = $_POST['ids'];

    if (!empty($ids)) {
        try {
            $conn = new PDO($dsn, DB_USER, DB_PASSWORD);
            $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

            // Start a transaction
            $conn->beginTransaction();

            $id_placeholders = implode(',', array_fill(0, count($ids), '?'));

            // Delete from employe_services
            $stmt = $conn->prepare("DELETE FROM employe_services WHERE employe_id IN ($id_placeholders)");
            $stmt->execute($ids);

            // Delete from horaires_travail
            $stmt = $conn->prepare("DELETE FROM horaires_travail WHERE employé_id IN ($id_placeholders)");
            $stmt->execute($ids);

            // Delete from utilisateurs
            $stmt = $conn->prepare("DELETE FROM utilisateurs WHERE id IN ($id_placeholders)");
            $stmt->execute($ids);

            // Commit the transaction
            $conn->commit();

            header("Location: employees.php?delete_success=1");
        } catch (PDOException $e) {
            // Rollback the transaction if something failed
            $conn->rollBack();
            header("Location: employees.php?delete_error=1");
        }
    } else {
        header("Location: employees.php?delete_error=1");
    }
}
?>
