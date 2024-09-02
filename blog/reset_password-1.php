<?php
session_start();
require 'config/constants.php';
require 'config/database.php';

if (!isset($_SESSION['user-id'])) {
    header('Location: ' . ROOT_URL . 'signin.php');
    exit();
}

$user_id = $_SESSION['user-id'];

// Fetch user details
$user_query = "SELECT * FROM users WHERE id = ?";
$user_stmt = $pdo->prepare($user_query);
$user_stmt->execute([$user_id]);
$user = $user_stmt->fetch(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Password</title>
    <link rel="stylesheet" href="<?= ROOT_URL ?>css/style.css">
    <!-- ICONSCOUT CDN -->
    <link rel="stylesheet" href="https://unicons.iconscout.com/release/v4.0.0/css/line.css">
    <!-- GOOGLE FONT (MONTSERRAT) -->
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
</head>
<body>

<section class="form__section">
    <div class="container form__section-container">
        <h2>Reset Password</h2>
        <?php if (isset($_SESSION['reset-password-error'])) : ?>
            <div class="alert__message error">
                <p>
                    <?= htmlspecialchars($_SESSION['reset-password-error'], ENT_QUOTES, 'UTF-8');
                    unset($_SESSION['reset-password-error']);
                    ?>
                </p>
            </div>
        <?php endif ?>
        <form action="reset_password-logic-1.php" method="POST">
            <div class="form__control password-toggle">
                <label for="current_password">Current Password</label>
                <input type="password" name="current_password" id="current_password" required>
                <i class="uil uil-eye toggle-icon" onclick="togglePasswordVisibility('current_password')"></i>
            </div>
            <div class="form__control password-toggle">
                <label for="new_password">New Password</label>
                <input type="password" name="new_password" id="new_password" required>
                <i class="uil uil-eye toggle-icon" onclick="togglePasswordVisibility('new_password')"></i>
            </div>
            <div class="form__control password-toggle">
                <label for="confirm_password">Confirm New Password</label>
                <input type="password" name="confirm_password" id="confirm_password" required>
                <i class="uil uil-eye toggle-icon" onclick="togglePasswordVisibility('confirm_password')"></i>
            </div>
            <button type="submit" name="submit" class="btn">Reset Password</button>
        </form>
    </div>
</section>

<script>
    function togglePasswordVisibility(fieldId) {
        var field = document.getElementById(fieldId);
        var icon = field.nextElementSibling;
        if (field.type === "password") {
            field.type = "text";
            icon.classList.replace("uil-eye", "uil-eye-slash");
        } else {
            field.type = "password";
            icon.classList.replace("uil-eye-slash", "uil-eye");
        }
    }
</script>
</body>
</html>
