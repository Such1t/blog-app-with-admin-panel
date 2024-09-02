<?php
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

// Get the current avatar URL
$avatarUrl = !empty($user['avatar_url']) ? htmlspecialchars($user['avatar_url'], ENT_QUOTES, 'UTF-8') : 'default-avatar.png';
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
    <!-- Bootstrap Icons CDN for Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css">

    <style>
        body {
            font-family: 'Montserrat', sans-serif;
            background-color: #f0f2f5;
            color: #6f6af8;
            margin: 0;
            padding: 0;
            height: 100vh;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: flex-start;
        }

        .main-container {
            display: flex;
            flex-direction: row;
            align-items: flex-start;
            width: 100%;
            max-width: 1200px;
        }

        #sidebar-container {
            width: 250px;
            background-color: #fff;
            color: #6f6af8;
            overflow-y: auto;
            padding-top: 20px;
            border-right: 2px solid #6f6af8;
            height: 100vh;
            position: fixed;
            top: 0;
            left: 0;
            transition: all 0.3s ease;
        }

        #sidebar-container .sidebar-wrapper {
            padding: 20px;
        }

        #sidebar-container .sidebar-link {
            text-align: center;
            font-size: 1.6rem;
            color: #6f6af8;
            text-decoration: none;
            font-weight: bold;
            display: block;
            margin-bottom: 30px;
            transition: color 0.3s ease;
        }

        #sidebar-container .sidebar-link:hover {
            color: #5854c7;
        }

        #sidebar-container .sidebar-menu ul.menu {
            list-style: none;
            padding: 0;
            margin: 0;
        }

        #sidebar-container .sidebar-menu ul.menu .sidebar-item {
            margin-bottom: 15px;
        }

        #sidebar-container .sidebar-menu ul.menu .sidebar-item a {
            color: #6f6af8;
            padding: 10px 20px;
            display: flex;
            align-items: center;
            text-decoration: none;
            transition: background-color 0.3s ease, color 0.3s ease;
            border-radius: 8px;
            font-size: 1rem;
        }

        #sidebar-container .sidebar-menu ul.menu .sidebar-item a i {
            margin-right: 12px;
            font-size: 1.4rem;
        }

        #sidebar-container .sidebar-menu ul.menu .sidebar-item a:hover,
        #sidebar-container .sidebar-menu ul.menu .sidebar-item a.active {
            background-color: #e6e6ff;
            color: #5854c7;
        }

        #sidebar-container .submenu {
            display: none;
            padding-left: 20px;
            margin-top: 10px;
            border-left: 2px solid #6f6af8;
        }

        #sidebar-container .submenu.open {
            display: block;
        }

        #main-content {
            margin-left: 250px;
            padding: 40px;
            background-color: #fff;
            border-radius: 10px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
            flex: 1;
            min-height: calc(100vh - 40px);
            overflow-y: auto;
            display: flex;
            flex-direction: column;
            align-items: center;
            margin-top: 5rem;
        }

        h2 {
            font-size: 2.4rem;
            color: #6f6af8;
            margin-bottom: 20px;
            font-weight: bold;
            text-align: center;
        }

        .form__section {
            background-color: #ffffff;
            padding: 2rem;
            border-radius: 10px;
            box-shadow: 0 8px 16px rgba(0, 0, 0, 0.1);
            max-width: 600px;
            width: 100%;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
        }

        .avatar-preview {
            display: flex;
            justify-content: center;
            margin-bottom: 1.5rem;
        }

        .avatar-preview img {
            width: 100px;
            height: 100px;
            border-radius: 50%;
            object-fit: cover;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }

        .form__control {
            margin-bottom: 1.5rem;
        }

        .form__control label {
            display: block;
            font-weight: 600;
            margin-bottom: 0.5rem;
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
            transition: border-color 0.3s ease;
        }

        .form__control input:focus,
        .form__control textarea:focus {
            border-color: #6f6af8;
            background-color: #fff;
        }

        .form__control input[type="file"] {
            padding: 0.5rem 0;
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
            transition: background-color 0.3s ease;
            font-weight: 600;
            margin-top: 1rem;
        }

        .btn:hover {
            background-color: #5854c7;
        }

        .reset-password__link {
            display: block;
            text-align:left;
            color: #6f6af8;
            text-decoration: none;
            font-weight: 500;
            transition: color 0.3s ease;
            cursor: pointer;
            margin-bottom: 1rem;
            margin-left: 0rem;
        }

        .reset-password__link:hover {
            color: #5854c7;
        }

        /* Modal styles */
        .modal {
            display: none;
            position: fixed;
            z-index: 1;
            left: 50%;
            top: 60%; /* Move modal further down */
            transform: translate(-50%, -50%);
            width: 100%;
            height: 100%;
            overflow: auto;
            background-color: rgba(0, 0, 0, 0.5);
            justify-content: center;
            align-items: center;
        }

        .modal-content {
            background-color: #fefefe;
            margin: auto;
            padding: 20px;
            border: 1px solid #888;
            width: 90%;
            max-width: 400px;
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            position: relative;
        }

        .close {
            color: #aaa;
            position: absolute;
            top: 10px;
            right: 10px;
            font-size: 28px;
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
            padding-right: 2.5rem; /* Space for the eye icon inside */
            width: 100%; /* Ensure the input takes up full width */
        }

        .password-toggle .toggle-icon {
            position: absolute;
            top: 75%; /* Adjusted to move the icon slightly down */
            right: 10px; /* Place the eye icon inside the input */
            transform: translateY(-50%);
            cursor: pointer;
            font-size: 1.2rem;
            color: #6f6af8;
        }

        /* Popup Style */
        .popup {
            position: fixed;
            top: 20px;
            right: 20px;
            padding: 1rem;
            color: white;
            border-radius: 8px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            z-index: 1000;
            display: none;
        }

        .popup.success {
            background-color: #28a745;
        }

        .popup.error {
            background-color: #dc3545;
        }

        /* Responsive Styles */
        @media screen and (max-width: 768px) {
            #sidebar-container {
                width: 100%;
                height: auto;
                position: relative;
                border-right: none;
            }

            #main-content {
                margin-left: 0;
                padding: 20px;
            }

            #sidebar-container .sidebar-link {
                font-size: 1.4rem;
            }

            .form__section {
                padding: 1.5rem;
            }

            h2 {
                font-size: 2rem;
            }
        }
    </style>
</head>

<body>
<?php include 'partials/header.php'; ?>
<div class="main-container">
    <!-- Sidebar Container -->
    <div id="sidebar-container">
        <div class="sidebar-wrapper">
            <a href="index.php" class="sidebar-link">Dialogue</a>

            <div class="sidebar-menu">
                <ul class="menu">
                    <li class="sidebar-item">
                        <a href="dashboard.php" class='sidebar-link'>
                            <i class="bi bi-person"></i>
                            <span>Dashboard</span>
                        </a>
                    </li>
                    <li class="sidebar-item has-sub">
                        <a href="javascript:void(0);" class='sidebar-link'>
                            <i class="bi bi-card-text"></i>
                            <span>Manage Posts</span>
                            <i class="bi bi-chevron-down"></i>
                        </a>
                        <ul class="submenu">
                            <li>
                                <a href="add-post.php" class="sidebar-link">Add Post</a>
                            </li>
                            <li>
                                <a href="manage-posts.php" class="sidebar-link">Manage Posts</a>
                            </li>
                        </ul>
                    </li>

                    <li class="sidebar-item has-sub">
                        <a href="javascript:void(0);" class='sidebar-link'>
                            <i class="bi bi-list"></i>
                            <span>Manage Categories</span>
                            <i class="bi bi-chevron-down"></i>
                        </a>
                        <ul class="submenu">
                            <li>
                                <a href="manage-categories.php" class="sidebar-link"> Edit Categories</a>
                            </li>

                        </ul>
                    </li>

                    <li class="sidebar-item">
                        <a href="manage-users.php" class='sidebar-link'>
                            <i class="bi bi-people"></i>
                            <span>Manage Users</span>
                        </a>
                    </li>

                    <li class="sidebar-item">
                        <a href="edit-profile.php" class='sidebar-link active'>
                            <i class="bi bi-person"></i>
                            <span>Edit Profile</span>
                        </a>
                    </li>

                </ul>
            </div>
        </div>
    </div>

    <div id="main-content">
        <h2>Edit Profile</h2>
        <section class="form__section">
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

<!-- Success and Error Popups -->
<?php if (isset($_SESSION['reset-password-success'])): ?>
    <div class="popup success" id="success-popup">
        <?= $_SESSION['reset-password-success'] ?>
        <?php unset($_SESSION['reset-password-success']); ?>
    </div>
<?php elseif (isset($_SESSION['reset-password-error'])): ?>
    <div class="popup error" id="error-popup">
        <?= $_SESSION['reset-password-error'] ?>
        <?php unset($_SESSION['reset-password-error']); ?>
    </div>
<?php endif; ?>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const subMenus = document.querySelectorAll('.has-sub > a');

        subMenus.forEach(menu => {
            menu.addEventListener('click', function () {
                const parent = this.parentElement;
                parent.classList.toggle('open');
                const submenu = parent.querySelector('.submenu');
                submenu.classList.toggle('open');
            });
        });

        // Get the modal
        var modal = document.getElementById("resetPasswordModal");

        // Get the button that opens the modal
        var btn = document.querySelector(".reset-password__link");

        // Get the <span> element that closes the modal
        var span = document.getElementsByClassName("close")[0];

        // When the user clicks on the button, open the modal
        btn.onclick = function() {
            modal.style.display = "block";
        }

        // When the user clicks on <span> (x), close the modal
        span.onclick = function() {
            modal.style.display = "none";
        }

        // When the user clicks anywhere outside of the modal, close it
        window.onclick = function(event) {
            if (event.target == modal) {
                modal.style.display = "none";
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

        // Show and hide the popup
        const successPopup = document.getElementById('success-popup');
        const errorPopup = document.getElementById('error-popup');

        if (successPopup) {
            successPopup.style.display = 'block';
            setTimeout(() => {
                successPopup.style.display = 'none';
            }, 3000); // Popup disappears after 3 seconds
        }

        if (errorPopup) {
            errorPopup.style.display = 'block';
            setTimeout(() => {
                errorPopup.style.display = 'none';
            }, 3000); // Popup disappears after 3 seconds
        }
    });
</script>
</body>

</html>
