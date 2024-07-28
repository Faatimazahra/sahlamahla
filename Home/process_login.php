<?php
session_start();
require_once 'db_connect.php'; // Replace with your database connection file

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $_POST['email'];
    $password = $_POST['password'];
    
    // Validate and sanitize inputs (you should do more thorough validation)
    $email = filter_var($email, FILTER_SANITIZE_EMAIL);
    
    try {
        // Prepare SQL statement to fetch user based on email
        $stmt = $conn->prepare('SELECT * FROM utilisateurs WHERE email = :email');
        $stmt->bindParam(':email', $email);
        $stmt->execute();
        
        // Fetch the user
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($user) {
            // Verify password
            if (password_verify($password, $user['mot_de_passe'])) {
                // Password is correct, set session variables
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['user_role'] = $user['rôle'];
                $_SESSION['user_email'] = $user['email'];
                $_SESSION['user_nom'] = $user['nom']; // Add nom to session
                
                // Determine the dashboard based on user role and redirect
                switch ($user['rôle']) {
                    case 'admin':
                        header('Location: /SahlaMahla/Admin_dashboard/index.php');
                        exit();
                    case 'employé':
                        header('Location: /SahlaMahla/Employe_dashboard/index.php');
                        exit();
                    case 'client':
                        header('Location: /SahlaMahla/Client_dashboard/index.php');
                        exit();
                    default:
                        // If no role matches, redirect to a default page or handle as needed
                        header('Location: /SahlaMahla/default_dashboard.php');
                        exit();
                }
            } else {
                // Password is incorrect
                $_SESSION['error'] = "Invalid email or password."; // Set error message
                header('Location: /SahlaMahla/Home/login.php'); // Redirect back to login page
                exit();
            }
        } else {
            // User not found
            $_SESSION['error'] = "Invalid email or password."; // Set error message
            header('Location: /SahlaMahla/Home/login.php'); // Redirect back to login page
            exit();
        }
    } catch (PDOException $e) {
        // Database error
        $_SESSION['error'] = "Database error: " . $e->getMessage(); // Set error message
        header('Location: /SahlaMahla/Home/login.php'); // Redirect back to login page
        exit();
    }
} else {
    // Redirect to login page if accessed directly without POST method
    header('Location: /SahlaMahla/Home/login.php');
    exit();
}
?>
