<?php
session_start();
require 'config/constants.php';

$username_email = $_SESSION['signin-data']['username_email'] ?? null;
$password = $_SESSION['signin-data']['password'] ?? null;

unset($_SESSION['signin-data']);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PHP & MySQL Blog Application with Admin Panel</title>
    <!-- CUSTOM STYLESHEET -->
    <link rel="stylesheet" href="<?= ROOT_URL ?>css/style.css">
    <!-- ICONSCOUT CDN -->
    <link rel="stylesheet" href="https://unicons.iconscout.com/release/v4.0.0/css/line.css">
    <!-- GOOGLE FONT (MONTSERRAT) -->
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
    <style>
        .form__section {
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
        }

        .form__section-container {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            width: 100%;
            max-width: 400px;
            padding: 20px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            background-color: #fff;
            border-radius: 10px;
        }

        form {
            width: 100%;
        }

        form input,
        form button,
        form small {
            width: 100%;
            margin-bottom: 10px;
        }

        .alert__message {
            width: 100%;
            text-align: center;
            margin-bottom: 15px;
        }

        .input-container {
            position: relative;
            width: 100%;
        }

        .input-container input {
            width: 100%;
            padding-right: 40px; /* to make space for the eye icon */
        }

        .input-container .toggle-password {
            position: absolute;
            right: 10px;
            top: 50%;
            transform: translateY(-50%);
            cursor: pointer;
            font-size: 18px;
            color: #888;
        }
    </style>
</head>

<body>

<section class="form__section">
    <div class="container form__section-container">
        <h2>Sign In</h2>
        <?php if (isset($_SESSION['signup-success'])) : ?>
            <div class="alert__message success">
                <p>
                    <?= htmlspecialchars($_SESSION['signup-success'], ENT_QUOTES, 'UTF-8');
                    unset($_SESSION['signup-success']);
                    ?>
                </p>
            </div>
        <?php elseif (isset($_SESSION['signin'])) : ?>
            <div class="alert__message error">
                <p>
                    <?= htmlspecialchars($_SESSION['signin'], ENT_QUOTES, 'UTF-8');
                    unset($_SESSION['signin']);
                    ?>
                </p>
            </div>
        <?php endif ?>
        <form action="signin-logic.php" method="POST">
            <input type="text" name="username_email" value="<?= htmlspecialchars($username_email, ENT_QUOTES, 'UTF-8') ?>" placeholder="Username or Email">
            <div class="input-container">
                <input type="password" id="password" name="password" value="<?= htmlspecialchars($password, ENT_QUOTES, 'UTF-8') ?>" placeholder="Password">
                <i class="uil uil-eye-slash toggle-password" onclick="togglePassword()"></i>
            </div>
            <button type="submit" name="submit" class="btn">Sign In</button>
            <small>Don't have an account? <a href="signup.php">Sign Up</a></small>
            <small><a href="forgot_password.php">Forgot Password?</a></small>
        </form>
    </div>
</section>

<script>
    function togglePassword() {
        var passwordInput = document.getElementById("password");
        var toggleIcon = document.querySelector(".toggle-password");
        if (passwordInput.type === "password") {
            passwordInput.type = "text";
            toggleIcon.classList.remove("uil-eye-slash");
            toggleIcon.classList.add("uil-eye");
        } else {
            passwordInput.type = "password";
            toggleIcon.classList.remove("uil-eye");
            toggleIcon.classList.add("uil-eye-slash");
        }
    }
</script>

</body>

</html>
