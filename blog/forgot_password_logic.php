<?php
session_start();
require 'config/constants.php';
require 'config/database.php';
require 'C:/xampp/htdocs/blog-app-with-admin-panel/vendor/autoload.php'; // Correct path to autoload.php

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

if (isset($_POST['submit'])) {
    $email = filter_var($_POST['email'], FILTER_VALIDATE_EMAIL);

    if (!$email) {
        $_SESSION['forgot-password'] = "Please enter a valid email";
        $_SESSION['forgot-password-data'] = $_POST;
        header('Location: ' . ROOT_URL . 'forgot_password.php');
        exit();
    } else {
        // Check if the email exists in the database
        $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user) {
            // Generate a unique token
            $token = bin2hex(random_bytes(50));

            // Store the token in the database with an expiration date
            $stmt = $pdo->prepare("INSERT INTO password_resets (email, token, expires_at) VALUES (?, ?, DATE_ADD(NOW(), INTERVAL 1 HOUR))");
            $stmt->execute([$email, $token]);

            // Send reset link to the user's email
            $resetLink = ROOT_URL . 'reset_password.php?token=' . $token;
            $subject = "Password Reset Request";
            $message = "Click the link below to reset your password:\n\n" . $resetLink;

            $mail = new PHPMailer(true);

            try {
                // Server settings
                $mail->isSMTP();
                $mail->Host = 'smtp.gmail.com'; // Gmail SMTP server
                $mail->SMTPAuth = true;
                $mail->Username = 'suchitmashelkar11@gmail.com'; // Your Gmail email address
                $mail->Password = 'glhe wttz qrzm spzc'; // Your Gmail app password
                $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
                $mail->Port = 587;

                // Recipients
                $mail->setFrom('suchitmashelkar11@gmail.com', 'Mailer');
                $mail->addAddress($email); // Add a recipient

                // Content
                $mail->isHTML(true);
                $mail->Subject = $subject;
                $mail->Body    = nl2br($message);
                $mail->AltBody = $message;

                $mail->send();
                $_SESSION['forgot-password-success'] = "Password reset link has been sent to your email";
                header('Location: ' . ROOT_URL . 'signin.php');
                exit();
            } catch (Exception $e) {
                $_SESSION['forgot-password'] = "Message could not be sent. Mailer Error: {$mail->ErrorInfo}";
                $_SESSION['forgot-password-data'] = $_POST;
                header('Location: ' . ROOT_URL . 'forgot_password.php');
                exit();
            }
        } else {
            $_SESSION['forgot-password'] = "Email not found";
            $_SESSION['forgot-password-data'] = $_POST;
            header('Location: ' . ROOT_URL . 'forgot_password.php');
            exit();
        }
    }
} else {
    header('Location: ' . ROOT_URL . 'forgot_password.php');
    exit();
}
?>
