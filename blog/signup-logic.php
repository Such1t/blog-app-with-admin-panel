<?php
session_start();
require 'config/constants.php';
require 'config/database.php';

if (isset($_POST['submit'])) {
    // Get form data
    $username = filter_var($_POST['username'], FILTER_SANITIZE_FULL_SPECIAL_CHARS);
    $email = filter_var($_POST['email'], FILTER_VALIDATE_EMAIL);
    $createpassword = filter_var($_POST['createpassword'], FILTER_SANITIZE_FULL_SPECIAL_CHARS);
    $confirmpassword = filter_var($_POST['confirmpassword'], FILTER_SANITIZE_FULL_SPECIAL_CHARS);

    // Validate input values
    if (!$username) {
        $_SESSION['signup'] = "Please enter your Username";
    } elseif (!$email) {
        $_SESSION['signup'] = "Please enter a valid email";
    } elseif (!$createpassword || !$confirmpassword) {
        $_SESSION['signup'] = "Please enter both passwords";
    } elseif (strlen($createpassword) < 8) {
        $_SESSION['signup'] = "Password should be at least 8 characters long";
    } elseif (!preg_match('/[A-Z]/', $createpassword)) {
        $_SESSION['signup'] = "Password should contain at least one uppercase letter";
    } elseif (!preg_match('/[a-z]/', $createpassword)) {
        $_SESSION['signup'] = "Password should contain at least one lowercase letter";
    } elseif (!preg_match('/\d/', $createpassword)) {
        $_SESSION['signup'] = "Password should contain at least one number";
    } elseif ($createpassword !== $confirmpassword) {
        $_SESSION['signup'] = "Passwords do not match";
    } else {
        // Check if the email already exists in the database
        $stmt = $pdo->prepare("SELECT * FROM users WHERE email = :email");
        $stmt->execute(['email' => $email]);
        if ($stmt->rowCount() > 0) {
            $_SESSION['signup'] = "An account already exists with this email. Please log in or use a different email.";
        } else {
            // Check if username already exists in the database
            $stmt = $pdo->prepare("SELECT * FROM users WHERE username = :username");
            $stmt->execute(['username' => $username]);
            if ($stmt->rowCount() > 0) {
                $_SESSION['signup'] = "Username already exists. Please choose another.";
            } else {
                // Hash password
                $hashed_password = password_hash($createpassword, PASSWORD_DEFAULT);

                // Insert new user into users table
                $stmt = $pdo->prepare("INSERT INTO users (username, email, password, avatar_url, is_admin) VALUES (:username, :email, :password, :avatar_url, 0)");
                $stmt->execute([
                    'username' => $username,
                    'email' => $email,
                    'password' => $hashed_password,
                    'avatar_url' => 'default_avatar.png'
                ]);

                // Get the newly inserted user ID
                $userId = $pdo->lastInsertId();

                // Set session variables
                $_SESSION['user-id'] = $userId;
                $_SESSION['user_is_admin'] = 0;

                // Redirect to the main index page
                header('Location: ' . ROOT_URL . 'index.php');
                exit();
            }
        }
    }

    // If there was an error, redirect back to signup page with form data
    if (isset($_SESSION['signup'])) {
        $_SESSION['signup-data'] = $_POST;
        header('location: ' . ROOT_URL . 'signup.php');
        exit();
    }
} else {
    // If the submit button was not clicked, redirect to the signup page
    header('location: ' . ROOT_URL . 'signup.php');
    exit();
}
