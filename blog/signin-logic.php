<?php
session_start();
require 'config/constants.php';
require 'config/database.php';

if (isset($_POST['submit'])) {
    // Get form data
    $username_email = filter_var($_POST['username_email'], FILTER_SANITIZE_FULL_SPECIAL_CHARS);
    $password = filter_var($_POST['password'], FILTER_SANITIZE_FULL_SPECIAL_CHARS);

    if (!$username_email) {
        $_SESSION['signin'] = "Username or Email required";
    } elseif (!$password) {
        $_SESSION['signin'] = "Password required";
    } else {
        // Fetch user from database using prepared statements
        $fetch_user_query = "SELECT * FROM users WHERE username = ? OR email = ?";
        $stmt = $pdo->prepare($fetch_user_query);
        $stmt->execute([$username_email, $username_email]);
        $user_record = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user_record) {
            $db_password = $user_record['password'];
            // Compare form password with database password
            if (password_verify($password, $db_password)) {
                // Set session for access control
                $_SESSION['user-id'] = $user_record['id'];
                $_SESSION['user_avatar'] = $user_record['avatar_url'];

                // Check if the user is an admin
                if ($user_record['is_admin'] == 1) {
                    $_SESSION['user_is_admin'] = true;
                    // Redirect to admin dashboard
                    header('Location: ' . ROOT_URL . 'admin/index.php');
                } else {
                    $_SESSION['user_is_admin'] = false;
                    // Redirect to user index page
                    header('Location: ' . ROOT_URL . 'index.php');
                }
                exit();
            } else {
                // Password is incorrect
                $_SESSION['signin'] = "Incorrect password";
            }
        } else {
            // Username or email is incorrect
            $_SESSION['signin'] = "Username or Email not found";
        }
    }

    // If any problem, redirect back to signin page with login data
    if (isset($_SESSION['signin'])) {
        $_SESSION['signin-data'] = $_POST;
        header('Location: ' . ROOT_URL . 'signin.php');
        exit();
    }
} else {
    header('Location: ' . ROOT_URL . 'signin.php');
    exit();
}
?>
