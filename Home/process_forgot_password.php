<?php
session_start();
require 'vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'db_connect.php';

if (isset($_POST['pwdrst'])) {
    $email = trim($_POST['email']);

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        // Use session to set error message
        $_SESSION['error'] = 'Adresse email invalide.';
        header('Location: forgot_password.php');
        exit;
    }

    try {
        $stmt = $conn->prepare("SELECT COUNT(*) FROM utilisateurs WHERE email = ?");
        $stmt->execute([$email]);
        $email_exists = $stmt->fetchColumn() > 0;

        if ($email_exists) {
            $token = bin2hex(random_bytes(32));
            $reset_link = "http://localhost/SahlaMahla/Home/reset_password.php?token=" . $token;

            $stmt = $conn->prepare("INSERT INTO password_resets (email, token, expires_at) VALUES (?, ?, ?)");
            $expires_at = date('Y-m-d H:i:s', strtotime('+1 hour'));
            $stmt->execute([$email, $token, $expires_at]);

            $mail = new PHPMailer(true);

            try {
                $mail->isSMTP();
                $mail->Host       = 'smtp.gmail.com';
                $mail->SMTPAuth   = true;
                $mail->Username   = 'sahlamahla35@gmail.com';
                $mail->Password   = 'sppw vpje mffj pgev';
                $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
                $mail->Port       = 587;

                $mail->SMTPDebug = 2;

                $mail->setFrom('sahlamahla35@gmail.com', 'Sahla Mahla');
                $mail->addAddress($email);

                $mail->isHTML(true);
                $mail->Subject = 'Réinitialisation de mot de passe';
                $mail->Body    = "Pour réinitialiser votre mot de passe, veuillez cliquer sur le lien suivant : <a href=\"$reset_link\">Réinitialiser le mot de passe</a>";

                $mail->send();
                $_SESSION['success'] = 'Un lien de réinitialisation du mot de passe a été envoyé à votre email.';
                header('Location: forgot_password.php');
            } catch (Exception $e) {
                $_SESSION['error'] = 'Erreur lors de l\'envoi de l\'email: ' . $mail->ErrorInfo;
                header('Location: forgot_password.php');
            }
        } else {
            $_SESSION['error'] = 'Aucun compte trouvé avec cet email.';
            header('Location: forgot_password.php');
        }
    } catch (PDOException $e) {
        $_SESSION['error'] = 'Erreur de base de données: ' . $e->getMessage();
        header('Location: forgot_password.php');
    }

    exit;
}
