<?php
session_start();

// تدمير جميع بيانات الجلسة
$_SESSION = array();
session_destroy();

// منع التخزين المؤقت للصفحات
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");

// إعادة التوجيه إلى صفحة تسجيل الدخول
header('Location: /SahlaMahla/Home/login.php');
exit();
?>