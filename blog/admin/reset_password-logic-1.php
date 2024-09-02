<?php
session_start();
require 'config/constants.php';
require 'config/database.php';

if (isset($_POST['current_password'], $_POST['new_password'], $_POST['confirm_password'])) {
    $current_password = $_POST['current_password'];
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];
    $user_id = $_SESSION['user-id'];

    // Fetch the user's current password from the database
    $stmt = $pdo->prepare("SELECT password FROM users WHERE id = ?");
    $stmt->execute([$user_id]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$user || !password_verify($current_password, $user['password'])) {
        $_SESSION['reset-password-error'] = "Current password is incorrect.";
        header('Location: ' . $_SERVER['HTTP_REFERER']);
        exit();
    }

    // Validate new password
    if ($new_password !== $confirm_password) {
        $_SESSION['reset-password-error'] = "Passwords do not match.";
        header('Location: ' . $_SERVER['HTTP_REFERER']);
        exit();
    }

    if (strlen($new_password) < 8 || !preg_match('/[A-Z]/', $new_password) || !preg_match('/\d/', $new_password)) {
        $_SESSION['reset-password-error'] = "Password must be at least 8 characters long, contain at least one uppercase letter, and one number.";
        header('Location: ' . $_SERVER['HTTP_REFERER']);
        exit();
    }

    // Hash the new password
    $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);

    // Update the user's password in the database
    $stmt = $pdo->prepare("UPDATE users SET password = ? WHERE id = ?");
    if ($stmt->execute([$hashed_password, $user_id])) {
        // Regenerate session ID for security
        session_regenerate_id(true);

        $_SESSION['reset-password-success'] = "Password reset successfully.";
        header('Location: ' . $_SERVER['HTTP_REFERER']);
        exit();
    } else {
        $_SESSION['reset-password-error'] = "Failed to reset password. Please try again.";
        header('Location: ' . $_SERVER['HTTP_REFERER']);
        exit();
    }
} else {
    $_SESSION['reset-password-error'] = "Invalid request.";
    header('Location: ' . $_SERVER['HTTP_REFERER']);
    exit();
}
