<?php
session_start();
require 'config/constants.php';

$username = $_SESSION['signup-data']['username'] ?? '';
$email = $_SESSION['signup-data']['email'] ?? '';
$createpassword = $_SESSION['signup-data']['createpassword'] ?? '';
$confirmpassword = $_SESSION['signup-data']['confirmpassword'] ?? '';
unset($_SESSION['signup-data']);
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
            overflow: hidden;
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
            overflow-y: auto;
            max-height: 90vh;
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
            margin-bottom: 15px;
        }

        .input-container input {
            width: 100%;
            padding-right: 40px;
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
        <h2>Sign Up</h2>
        <?php if (isset($_SESSION['signup'])) : ?>
            <div class="alert__message error">
                <p>
                    <?= htmlspecialchars($_SESSION['signup'], ENT_QUOTES, 'UTF-8');
                    unset($_SESSION['signup']);
                    ?>
                </p>
            </div>
        <?php endif ?>
        <form action="<?= ROOT_URL ?>signup-logic.php" method="POST">
            <input type="text" name="username" value="<?= htmlspecialchars($username, ENT_QUOTES, 'UTF-8') ?>" placeholder="Username" required>
            <input type="email" name="email" value="<?= htmlspecialchars($email, ENT_QUOTES, 'UTF-8') ?>" placeholder="Email" required>

            <div class="input-container">
                <input type="password" id="createpassword" name="createpassword" value="<?= htmlspecialchars($createpassword, ENT_QUOTES, 'UTF-8') ?>" placeholder="Create Password" required>
                <i class="uil uil-eye-slash toggle-password" onclick="togglePassword('createpassword', this)"></i>
            </div>

            <div class="input-container">
                <input type="password" id="confirmpassword" name="confirmpassword" value="<?= htmlspecialchars($confirmpassword, ENT_QUOTES, 'UTF-8') ?>" placeholder="Confirm Password" required>
                <i class="uil uil-eye-slash toggle-password" onclick="togglePassword('confirmpassword', this)"></i>
            </div>

            <button type="submit" name="submit" class="btn">Sign Up</button>
            <p>Already have an account? <a href="<?= ROOT_URL ?>signin.php">Sign In</a></p>
        </form>
    </div>
</section>

<script>
    function togglePassword(fieldId, icon) {
        var passwordInput = document.getElementById(fieldId);
        if (passwordInput.type === "password") {
            passwordInput.type = "text";
            icon.classList.remove("uil-eye-slash");
            icon.classList.add("uil-eye");
        } else {
            passwordInput.type = "password";
            icon.classList.remove("uil-eye");
            icon.classList.add("uil-eye-slash");
        }
    }
</script>

</body>

</html>
