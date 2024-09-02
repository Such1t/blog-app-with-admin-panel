<?php
ob_start(); // Start output buffering
session_start();
require '../config/database.php';
include 'partials/header.php';

if (isset($_GET['id'])) {
    $id = filter_var($_GET['id'], FILTER_SANITIZE_NUMBER_INT);

    // Fetch category from the database using PDO
    $query = "SELECT * FROM categories WHERE id = :id";
    $stmt = $pdo->prepare($query);
    $stmt->bindParam(':id', $id, PDO::PARAM_INT);
    $stmt->execute();

    if ($stmt->rowCount() == 1) {
        $category = $stmt->fetch(PDO::FETCH_ASSOC);
    } else {
        header('Location: ' . ROOT_URL . 'admin/manage-categories.php');
        exit();
    }
} else {
    header('Location: ' . ROOT_URL . 'admin/manage-categories.php');
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>

    <link rel="preconnect" href="https://fonts.gstatic.com">
    <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@300;400;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css">
    <link rel="shortcut icon" href="assets/images/favicon.svg" type="image/x-icon">

    <style>
        body {
            font-family: 'Nunito', sans-serif;
            background-color: #f4f6f8;
            margin: 0;
            padding: 0;
            display: flex;
            min-height: 100vh;
            height: 100vh;
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
            border-right: 2px solid;
            height: 100vh;
            position: fixed;
            top: 0;
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
            transition: background-color 0.3s ease, color 0.3s ease;
            border-radius: 8px;
            font-size: 1rem;
            cursor: pointer;
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

        #sidebar-container .sidebar-item.has-sub > a .bi-chevron-down {
            margin-left: auto;
            transition: transform 0.3s ease;
        }

        #sidebar-container .sidebar-item.has-sub.open > a .bi-chevron-down {
            transform: rotate(180deg);
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
            padding: 20px;
            background-color: #fff;
            border-radius: 10px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
            flex: 1;
            height: 100vh;
            overflow-y: auto;
        }

        .page-heading h3 {
            font-size: 1.8rem;
            color: #343a40;
            margin-bottom: 10px;
        }

        .page-heading p {
            color: #6c757d;
            margin-bottom: 20px;
        }

        .page-heading h3 {
            color: #6f6af8;
        }

        .form__section {
            display: flex;
            justify-content: center; /* Center horizontally */
            align-items: center; /* Center vertically */
            height: 100vh; /* Full height of the viewport */
        }

        .form__section-container {
            width: var(--form-width);
            margin-left: calc(50% - 400px); /* Move it more to the left by increasing the value */
            padding: 20px;
            background-color: #fff;
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            transform: translateX(-40px); /* Move it more to the left */
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
                                <a href="manage-posts.php" class="sidebar-link active">Manage Posts</a>
                            </li>
                            <li>
                                <a href="manage-drafts.php" class="sidebar-link">Manage Drafts</a>
                            </li>
                        </ul>
                    </li>

                    <li class="sidebar-item has-sub open">
                        <a href="javascript:void(0);" class='sidebar-link'>
                            <i class="bi bi-list"></i>
                            <span>Manage Categories</span>
                            <i class="bi bi-chevron-down"></i>
                        </a>
                        <ul class="submenu open">
                            <li>
                                <a href="add-category.php" class="sidebar-link">Add Category</a>
                            </li>
                            <li>
                                <a href="manage-categories.php" class="sidebar-link">Edit Categories</a>
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
        <!-- Main Content -->
        <section class="form__section">
            <div class="container form__section-container">
                <h2>Edit Category</h2>
                <form action="<?= ROOT_URL ?>admin/edit-category-logic.php" method="POST">
                    <input type="hidden" name="id" value="<?= htmlspecialchars($category['id'], ENT_QUOTES, 'UTF-8') ?>">
                    <input type="text" name="name" value="<?= htmlspecialchars($category['name'], ENT_QUOTES, 'UTF-8') ?>" placeholder="Name" required>
                    <button type="submit" name="submit" class="btn">Update Category</button>
                </form>
            </div>
        </section>
    </div>
    <!-- Your other content goes here -->

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
