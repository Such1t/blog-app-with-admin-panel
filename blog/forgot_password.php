<?php
session_start();
require 'config/constants.php';
include 'partials/header.php';
$email = $_SESSION['forgot-password-data']['email'] ?? null;
unset($_SESSION['forgot-password-data']);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Forgot Password</title>
    <!-- CUSTOM STYLESHEET -->
    <link rel="stylesheet" href="css/style.css">
    <!-- ICONSCOUT CDN -->
    <link rel="stylesheet" href="https://unicons.iconscout.com/release/v4.0.0/css/line.css">
    <!-- GOOGLE FONT (MONTSERRAT) -->
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
</head>
<body>
<section class="form__section">
    <div class="container form__section-container">
        <h2>Forgot Password</h2>
        <?php if (isset($_SESSION['forgot-password'])) : ?>
            <div class="alert__message error">
                <p>
                    <?= htmlspecialchars($_SESSION['forgot-password'], ENT_QUOTES, 'UTF-8');
                    unset($_SESSION['forgot-password']);
                    ?>
                </p>
            </div>
        <?php endif ?>
        <form action="forgot_password_logic.php" method="POST">
            <input type="email" name="email" value="<?= htmlspecialchars($email, ENT_QUOTES, 'UTF-8') ?>" placeholder="Email">
            <button type="submit" name="submit" class="btn">Send Reset Link</button>
        </form>
    </div>
</section>
</body>
</html>
