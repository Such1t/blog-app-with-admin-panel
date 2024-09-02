<?php
global $pdo;
require 'config/constants.php';
require 'config/database.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

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

$avatarUrl = !empty($user['avatar_url']) ? htmlspecialchars($user['avatar_url'], ENT_QUOTES, 'UTF-8') : 'default-avatar.png';

// Include the header after checking session
include 'partials/header.php';
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Profile</title>
    <link rel="stylesheet" href="<?= ROOT_URL ?>css/style.css">
    <!-- ICONSCOUT CDN -->
    <link rel="stylesheet" href="https://unicons.iconscout.com/release/v4.0.0/css/line.css">
    <!-- GOOGLE FONT (MONTSERRAT) -->
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@300;400;500;600;700;800;900&display=swap"
          rel="stylesheet">
    <style>
        /* Existing styles remain unchanged */
        body {
            font-family: 'Montserrat', sans-serif;
            background-color: #f4f6f8;
            color: #333;
            margin: 0;
            padding: 0;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: flex-start;
            min-height: 100vh;
            position: relative;
            overflow-x: hidden;
        }

        .main-container {
            width: 100%;
            max-width: 1200px;
            padding: 20px;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            animation: fadeIn 1s ease-in-out;
        }

        .form__section {
            background-color: #ffffff;
            padding: 2rem;
            border-radius: 10px;
            box-shadow: 0 8px 16px rgba(0, 0, 0, 0.1);
            max-width: 500px;
            width: 100%;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            transform: translateY(20px);
            animation: slideUp 0.5s ease-out forwards;
            z-index: 1;
        }

        h2 {
            text-align: center;
            color: #6f6af8;
            margin-bottom: 1.5rem;
            font-weight: 700;
            font-size: 2rem;
            opacity: 0;
            animation: fadeIn 1s ease-in-out forwards;
            animation-delay: 0.3s;
        }

        .form__control {
            margin-bottom: 1.5rem;
            opacity: 0;
            animation: fadeIn 0.8s ease-in-out forwards;
            animation-delay: 0.5s;
        }

        .form__control label {
            display: block;
            font-weight: 600;
            color: #6f6af8;
        }

        .form__control input,
        .form__control textarea {
            width: 100%;
            padding: 0.8rem 1rem;
            border-radius: 6px;
            border: 1px solid #ddd;
            background-color: #f9f9f9;
            font-size: 1rem;
            color: #6f6af8;
            outline: none;
            transition: border-color 0.3s ease, box-shadow 0.3s ease;
        }

        .form__control input:focus,
        .form__control textarea:focus {
            border-color: #6f6af8;
            box-shadow: 0 0 10px rgba(111, 106, 248, 0.1);
            background-color: #fff;
        }

        .form__control input[type="file"] {
            padding: 0.5rem 0;
            border: none;
            background: none;
        }

        .btn {
            width: 100%;
            background-color: #6f6af8;
            color: #fff;
            padding: 0.8rem 1rem;
            border-radius: 6px;
            font-size: 1rem;
            cursor: pointer;
            text-align: center;
            border: none;
            transition: background-color 0.3s ease, transform 0.3s ease;
            font-weight: 600;
            margin-top: 1rem;
            text-transform: uppercase;
            animation: bounceIn 1s ease;
        }

        .btn:hover {
            background-color: #5854c7;
            transform: translateY(-3px);
        }

        .reset-password__link {
            display: block;
            text-align: center;
            color: #6f6af8;
            text-decoration: none;
            font-weight: 500;
            transition: color 0.3s ease;
            cursor: pointer;
            margin-bottom: 1.5rem;
            text-transform: uppercase;
            animation: fadeIn 1s ease-in-out forwards;
            animation-delay: 0.8s;
        }

        .reset-password__link:hover {
            color: #5854c7;
        }

        /* Avatar preview styles */
        .avatar-preview {
            display: flex;
            justify-content: center;
            margin-bottom: 1rem;
        }

        .avatar-preview img {
            width: 100px;
            height: 100px;
            border-radius: 50%;
            object-fit: cover;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }

        /* Modal styles */
        .modal {
            display: none;
            position: fixed;
            z-index: 10000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            overflow-y: auto;
            background-color: rgba(0, 0, 0, 0.5);
            animation: fadeIn 0.5s ease-in-out;
        }

        .modal-content {
            background-color: #fefefe;
            margin: 5% auto;
            padding: 20px;
            border: 1px solid #888;
            width: 90%;
            max-width: 400px;
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            position: relative;
            z-index: 10001;
            max-height: 90vh;
            overflow-y: auto;
        }

        .close {
            color: #333333;
            float: right;
            font-size: 35px;
            font-weight: bold;
        }

        .close:hover,
        .close:focus {
            color: black;
            text-decoration: none;
            cursor: pointer;
        }

        .password-toggle {
            position: relative;
        }

        .password-toggle input {
            padding-right: 2.5rem;
        }

        .password-toggle .toggle-icon {
            position: absolute;
            top: 65%;
            right: 0.75rem;
            transform: translateY(-50%);
            cursor: pointer;
            font-size: 1.2rem;
            color: #333;
        }

        footer {
            margin-top: auto;
            background-color: #6f6af8;
            color: #fff;
            padding: 1rem 0;
            text-align: center;
            width: 100%;
            box-shadow: 0 -2px 10px rgba(0, 0, 0, 0.1);
            position: fixed;
            bottom: 0;
            left: 0;
            right: 0;
            z-index: 999;
        }

        footer .social-icons {
            margin-top: 0.5rem;
        }

        footer .social-icons a {
            margin: 0 10px;
            color: #fff;
            font-size: 1.5rem;
            transition: color 0.3s;
        }

        footer .social-icons a:hover {
            color: #ddd;
        }

        /* Animations */
        @keyframes fadeIn {
            0% {
                opacity: 0;
            }

            100% {
                opacity: 1;
            }
        }

        @keyframes slideUp {
            0% {
                transform: translateY(20px);
                opacity: 0;
            }

            100% {
                transform: translateY(0);
                opacity: 1;
            }
        }

        @keyframes slideDown {
            0% {
                transform: translateY(-20px);
                opacity: 0;
            }

            100% {
                transform: translateY(0);
                opacity: 1;
            }
        }

        @keyframes bounceIn {
            0% {
                transform: scale(0.5);
                opacity: 0;
            }

            50% {
                transform: scale(1.1);
                opacity: 1;
            }

            100% {
                transform: scale(1);
            }
        }
    </style>
</head>

<body>

<div class="main-container">
    <section class="form__section">
        <h2>Edit Profile</h2>
        <!-- Avatar preview section -->
        <div class="avatar-preview">
            <img src="<?= ROOT_URL ?>images/<?= $avatarUrl ?>" alt="Current Avatar" id="avatar-preview-img">
        </div>
        <form action="update-profile.php" method="POST" enctype="multipart/form-data">
            <div class="form__control">
                <label for="avatar">Change Profile Picture</label>
                <input type="file" name="avatar" id="avatar" accept="image/*" onchange="previewAvatar()">
            </div>
            <div class="form__control">
                <label for="username">Username</label>
                <input type="text" name="username" id="username" value="<?= htmlspecialchars($user['username'], ENT_QUOTES) ?>" required>
            </div>
            <div class="form__control">
                <label for="email">Email</label>
                <input type="email" name="email" id="email" value="<?= htmlspecialchars($user['email'], ENT_QUOTES) ?>" required>
            </div>
            <div class="form__control">
                <label for="profile_info">Profile Info</label>
                <textarea name="profile_info" id="profile_info" rows="4"><?= htmlspecialchars($user['profile_info'], ENT_QUOTES) ?></textarea>
            </div>
            <span class="reset-password__link">Reset Password</span>
            <button type="submit" class="btn">Update Profile</button>
        </form>
    </section>
</div>

<!-- Modal -->
<div id="resetPasswordModal" class="modal">
    <div class="modal-content">
        <span class="close">&times;</span>
        <h2>Reset Password</h2>
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
            <button type="submit" class="btn">Reset Password</button>
        </form>
    </div>
</div>

<script>
    // Get the modal
    var modal = document.getElementById("resetPasswordModal");

    // Get the button that opens the modal
    var btn = document.querySelector(".reset-password__link");

    // Get the <span> element that closes the modal
    var span = document.getElementsByClassName("close")[0];

    // When the user clicks on the button, open the modal
    btn.onclick = function() {
        modal.style.display = "block";
        document.body.style.overflow = "hidden"; // Prevent scrolling when modal is open
    }

    // When the user clicks on <span> (x), close the modal
    span.onclick = function() {
        modal.style.display = "none";
        document.body.style.overflow = ""; // Restore scrolling when modal is closed
    }

    // When the user clicks anywhere outside of the modal, close it
    window.onclick = function(event) {
        if (event.target == modal) {
            modal.style.display = "none";
            document.body.style.overflow = ""; // Restore scrolling when modal is closed
        }
    }

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

    function previewAvatar() {
        var file = document.getElementById('avatar').files[0];
        var reader = new FileReader();

        reader.onloadend = function() {
            document.getElementById('avatar-preview-img').src = reader.result;
        };

        if (file) {
            reader.readAsDataURL(file);
        } else {
            document.getElementById('avatar-preview-img').src = '<?= ROOT_URL ?>images/<?= $avatarUrl ?>';
        }
    }
</script>

<?php include 'partials/footer.php'; ?>
</body>

</html>
