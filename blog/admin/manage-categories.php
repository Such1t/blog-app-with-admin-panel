<?php
global $pdo;
session_start();
require '../config/database.php';

// Fetch categories from the database using PDO
$query = "SELECT * FROM categories ORDER BY name";
$stmt = $pdo->prepare($query);
$stmt->execute();
$categories = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <?php include 'partials/header.php'; ?>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="preconnect" href="https://fonts.gstatic.com">
    <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@300;400;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css">
    <link rel="shortcut icon" href="assets/images/favicon.svg" type="image/x-icon">

    <!-- Internal CSS -->
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
            background-color: #fff;
            color: #6f6af8;
            overflow-y: auto;
            padding-top: 20px;
            border-right: 2px solid #d1d1e9;
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
            color: #6f6af8;
        }

        #sidebar-container .sidebar-menu ul.menu .sidebar-item a.active {
            background-color: #d9d9ff;
            color: #6f6af8;
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
            height: 100vh;
            overflow-y: auto;
            transition: all 0.3s ease;
        }

        #main-container h2{
            margin-top:2rem;
        }

        h2 {
            font-size: 2rem;
            color: #333;
            margin-bottom: 20px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
            background-color: #f9f9fb;
            color: #333;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.05);
        }

        table th, table td {
            padding: 10px 10px;
            border-bottom: 1px solid #eee;
            text-align: left;
        }

        table th {
            background-color: #6f6af8;
            color: #fff;
        }

        table td a.btn {
            padding: 8px 15px;
            text-decoration: none;
            color: white;
            background-color: #6f6af8;
            border-radius: 6px;
            transition: background-color 0.3s ease;
        }

        table td a.btn:hover {
            background-color: #5854c7;
        }

        table tr:last-child td {
            border-bottom: none;
        }

        title{
            margin-top:2rem;
        }
    </style>
</head>

<body>
<div id="app">
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
                        <a href="javascript:void(0);" class='sidebar-link active'>
                            <i class="bi bi-list"></i>
                            <span>Manage Categories</span>
                            <i class="bi bi-chevron-down"></i>
                        </a>
                        <ul class="submenu open">
                            <li>
                                <a href="manage-categories.php" class="sidebar-link active">Edit Categories</a>
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
        <h2>Manage Categories</h2>
        <?php if (count($categories) > 0) : ?>
            <table>
                <thead>
                <tr>
                    <th>Name</th>
                    <th>Edit</th>
                </tr>
                </thead>
                <tbody>
                <?php foreach ($categories as $category) : ?>
                    <tr>
                        <td><?= htmlspecialchars($category['name'], ENT_QUOTES, 'UTF-8') ?></td>
                        <td><a href="<?= ROOT_URL ?>admin/edit-category.php?id=<?= $category['id'] ?>" class="btn sm">Edit</a></td>
                    </tr>
                <?php endforeach ?>
                </tbody>
            </table>
        <?php else : ?>
            <div class="alert__message error"><?= "No categories found" ?></div>
        <?php endif ?>
    </div>
</div>

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
    });
</script>

</body>
</html>
