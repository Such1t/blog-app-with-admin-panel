<?php
session_start();
require '../config/database.php';

// Ensure the user is logged in
if (!isset($_SESSION['user-id'])) {
    header('Location: ' . ROOT_URL . 'signin.php');
    exit();
}

// Fetch all users from the database
$query = "SELECT * FROM users";
$stmt = $pdo->prepare($query);
$stmt->execute();
$users = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <?php include 'partials/header.php'; ?>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Users</title>
    <link rel="preconnect" href="https://fonts.gstatic.com">
    <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@300;400;600;700;800&display=swap" rel="stylesheet">
    <link rel="shortcut icon" href="assets/images/favicon.svg" type="image/x-icon">

    <!-- Bootstrap Icons CDN for Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css">

    <style>
        body {
            font-family: 'Nunito', sans-serif;
            background-color: #f0f2f5;
            margin: 0;
            padding: 0;
            display: flex;
            min-height: 100vh;
            height: 100vh;
            color: #444;
        }

        #app {
            display: flex;
            width: 100%;
            height: 100%;
        }

        #sidebar-container {
            width: 250px;
            background-color: white;
            color: #6f6af8;
            overflow-y: auto;
            padding-top: 20px;
            border-right: 2px solid #6f6af8;
            height: 100vh;
            position: fixed;
            top: 0;
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
            transition: background-color 0.3s ease;
            border-radius: 8px;
            font-size: 1rem;
        }

        #sidebar-container .sidebar-menu ul.menu .sidebar-item a i {
            margin-right: 12px;
            font-size: 1.4rem;
        }

        #sidebar-container .sidebar-menu ul.menu .sidebar-item a:hover {
            background-color: #e6e6ff;
            color: #5854c7;
        }

        #sidebar-container .sidebar-menu ul.menu .sidebar-item a.active {
            background-color: #d9d9ff;
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

        #main-container {
            margin-left: 250px;
            padding: 40px;
            background-color: #fff;
            border-radius: 10px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
            flex: 1;
            min-height: calc(100vh - 40px);
            overflow-y: auto;
        }

        h2 {
            font-size: 2rem;
            color: #6f6af8;
            margin-bottom: 20px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
            background-color: #f9f9fb;
            color: #6f6af8;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.05);
        }

        table th, table td {
            padding: 15px 20px;
            border-bottom: 1px solid #eee;
            text-align: left;
        }

        table th {
            background-color: #6f6af8;
            color: #fff;
        }

        table td {
            background-color: #f4f6f8;
        }

        .btn.sm {
            padding: 8px 15px;
            font-size: 0.9rem;
            border-radius: 6px;
            background-color: #6f6af8;
            color: #fff;
            text-decoration: none;
            display: inline-block;
            transition: background-color 0.3s ease;
        }

        .btn.sm.danger {
            background-color: #dc3545;
        }

        .btn.sm:hover {
            background-color: #5854c7;
        }

        .btn.sm.danger:hover {
            background-color: #c82333;
        }

        img {
            border-radius: 50%;
            width: 50px;
            height: 50px;
        }

        .nav__profile ul li a {
            padding: 1rem;
            background:white;
            display: block;
            width: 100%;
            color:#6f6af8;
        }

        .nav__profile ul li:last-child a {
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

    </style>
</head>
<body>
<section id="app">
    <!-- Success Popup -->
    <?php if (isset($_SESSION['delete-user-success'])): ?>
        <div id="success-popup" class="popup success">User deleted successfully.</div>
        <?php unset($_SESSION['delete-user-success']); ?>
    <?php endif; ?>

    <!-- Error Popup -->
    <?php if (isset($_SESSION['delete-user-error'])): ?>
        <div id="error-popup" class="popup error">Error deleting user.</div>
        <?php unset($_SESSION['delete-user-error']); ?>
    <?php endif; ?>

    <!-- Sidebar Container -->
    <div id="sidebar-container" class="active">
        <div class="sidebar-wrapper active">
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
                                <a href="manage-categories.php" class="sidebar-link">Categories</a>
                            </li>
                        </ul>
                    </li>

                    <li class="sidebar-item">
                        <a href="manage-users.php" class='sidebar-link active'>
                            <i class="bi bi-people"></i>
                            <span>Manage Users</span>
                        </a>
                    </li>

                    <li class="sidebar-item">
                        <a href="edit-profile.php" class='sidebar-link'>
                            <i class="bi bi-person"></i>
                            <span>Edit Profile</span>
                        </a>
                    </li>
                </ul>
            </div>
        </div>
    </div>

    <!-- Main Container -->
    <div id="main-container">
        <h2>Manage Users</h2>
        <div class="manage-users-section">
            <h3>Existing Users</h3>
            <?php if (count($users) > 0) : ?>
                <table>
                    <thead>
                    <tr>
                        <th>Profile Picture</th>
                        <th>Username</th>
                        <th>Email</th>
                        <th>Delete</th>
                        <th>Admin</th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($users as $user) : ?>
                        <tr>
                            <td>
                                <a href="<?= ROOT_URL ?>dashboard.php?user_id=<?= $user['id'] ?>">
                                    <img src="../images/<?= htmlspecialchars($user['avatar_url'], ENT_QUOTES, 'UTF-8') ?>" alt="Profile Picture">
                                </a>
                            </td>
                            <td><?= htmlspecialchars($user['username'], ENT_QUOTES, 'UTF-8') ?></td>
                            <td><?= htmlspecialchars($user['email'], ENT_QUOTES, 'UTF-8') ?></td>
                            <td>
                                <form action="<?= ROOT_URL ?>admin/delete-user.php" method="POST" style="display:inline;">
                                    <input type="hidden" name="id" value="<?= $user['id'] ?>">
                                    <button type="submit" class="btn sm danger">Delete</button>
                                </form>
                            </td>
                            <td><?= $user['is_admin'] ? 'Yes' : 'No' ?></td>
                        </tr>
                    <?php endforeach ?>
                    </tbody>
                </table>
            <?php else : ?>
                <div class="alert__message error"><?= "No users found" ?></div>
            <?php endif ?>
        </div>
    </div>
</section>
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
