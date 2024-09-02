<?php
session_start();
require 'config/constants.php';
require 'config/database.php';

if (isset($_POST['submit'])) {
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];

    // Check if reset email is set in session
    if (!isset($_SESSION['reset-email'])) {
        $_SESSION['reset-password-error'] = "Invalid session. Please try again.";
        header('Location: ' . ROOT_URL . 'forgot_password.php');
        exit();
    }

    $email = $_SESSION['reset-email'];

    // Validate passwords
    if ($new_password !== $confirm_password) {
        $_SESSION['reset-password-error'] = "Passwords do not match.";
        header('Location: ' . ROOT_URL . 'reset_password.php?token=' . $_GET['token']);
        exit();
    }

    if (strlen($new_password) < 8) {
        $_SESSION['reset-password-error'] = "Password must be at least 6 characters.";
        header('Location: ' . ROOT_URL . 'reset_password.php?token=' . $_GET['token']);
        exit();
    }

    // Hash the new password
    $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);

    try {
        // Begin a transaction
        $pdo->beginTransaction();

        // Update the user's password in the database
        $stmt = $pdo->prepare("UPDATE users SET password = ? WHERE email = ?");
        if ($stmt->execute([$hashed_password, $email])) {
            // Delete the password reset token
            $stmt = $pdo->prepare("DELETE FROM password_resets WHERE email = ?");
            $stmt->execute([$email]);

            // Commit the transaction
            $pdo->commit();

            $_SESSION['reset-password-success'] = "Password reset successfully. You can now sign in with your new password.";
            header('Location: ' . ROOT_URL . 'signin.php');
            exit();
        } else {
            // Rollback the transaction
            $pdo->rollBack();
            $_SESSION['reset-password-error'] = "Failed to reset password. Please try again.";
            header('Location: ' . ROOT_URL . 'reset_password.php?token=' . $_GET['token']);
            exit();
        }
    } catch (Exception $e) {
        // Rollback the transaction in case of error
        $pdo->rollBack();
        $_SESSION['reset-password-error'] = "Failed to reset password. Please try again.";
        header('Location: ' . ROOT_URL . 'reset_password.php?token=' . $_GET['token']);
        exit();
    }
} else {
    header('Location: ' . ROOT_URL . 'forgot_password.php');
    exit();
}
?>
