<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Password</title>
    <!-- CUSTOM STYLESHEET -->
    <link rel="stylesheet" href="css/style.css">
    <!-- ICONSCOUT CDN -->
    <link rel="stylesheet" href="https://unicons.iconscout.com/release/v4.0.0/css/line.css">
    <!-- GOOGLE FONT (MONTSERRAT) -->
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
    <style>
        .password-container {
            position: relative;
            width: 100%;
            margin-bottom: 1rem;
        }

        .password-container input[type="password"] {
            width: 100%;
            padding-right: 40px; /* Space for the eye icon */
        }

        .password-container .toggle-password {
            position: absolute;
            top: 50%;
            right: 10px;
            transform: translateY(-50%);
            cursor: pointer;
            color: #333;
        }
    </style>
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
        <form action="reset_password_logic.php" method="POST">
            <div class="password-container">
                <input type="password" name="new_password" id="new_password" placeholder="New Password" required>
                <i class="uil uil-eye toggle-password" id="toggleNewPassword"></i>
            </div>
            <div class="password-container">
                <input type="password" name="confirm_password" id="confirm_password" placeholder="Confirm Password" required>
                <i class="uil uil-eye toggle-password" id="toggleConfirmPassword"></i>
            </div>
            <button type="submit" name="submit" class="btn">Reset Password</button>
        </form>
    </div>
</section>

<script>
    // Toggle password visibility
    const toggleNewPassword = document.getElementById('toggleNewPassword');
    const newPassword = document.getElementById('new_password');

    toggleNewPassword.addEventListener('click', function() {
        const type = newPassword.getAttribute('type') === 'password' ? 'text' : 'password';
        newPassword.setAttribute('type', type);
        this.classList.toggle('uil-eye-slash');
    });

    const toggleConfirmPassword = document.getElementById('toggleConfirmPassword');
    const confirmPassword = document.getElementById('confirm_password');

    toggleConfirmPassword.addEventListener('click', function() {
        const type = confirmPassword.getAttribute('type') === 'password' ? 'text' : 'password';
        confirmPassword.setAttribute('type', type);
        this.classList.toggle('uil-eye-slash');
    });
</script>

</body>

</html>
s