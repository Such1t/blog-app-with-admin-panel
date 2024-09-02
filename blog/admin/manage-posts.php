<?php
require '../config/constants.php';
require '../config/database.php';

session_start(); // Ensure session is started

// Check if user is logged in and is an admin
if (!isset($_SESSION['user-id']) || !isset($_SESSION['user_is_admin']) || !$_SESSION['user_is_admin']) {
    header('Location: ' . ROOT_URL . 'signin.php');
    exit();
}

// Fetch all posts with likes, comments, and username for admin
$query = "
    SELECT blog_posts.id, blog_posts.title, categories.name AS category_name, users.username,
    (SELECT COUNT(*) FROM likes WHERE likes.post_id = blog_posts.id) AS total_likes,
    (SELECT COUNT(*) FROM comments WHERE comments.post_id = blog_posts.id) AS total_comments
    FROM blog_posts
    LEFT JOIN categories ON blog_posts.category_id = categories.id
    LEFT JOIN users ON blog_posts.user_id = users.id
    WHERE blog_posts.status = 'published'
";

$stmt = $pdo->query($query);
$posts = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Fetch top 5 popular posts based on likes and comments
$popular_query = "
    SELECT blog_posts.id, blog_posts.title, categories.name AS category_name, users.username,
    (SELECT COUNT(*) FROM likes WHERE likes.post_id = blog_posts.id) AS total_likes,
    (SELECT COUNT(*) FROM comments WHERE comments.post_id = blog_posts.id) AS total_comments
    FROM blog_posts
    LEFT JOIN categories ON blog_posts.category_id = categories.id
    LEFT JOIN users ON blog_posts.user_id = users.id
    WHERE blog_posts.status = 'published'
    ORDER BY total_likes DESC, total_comments DESC
    LIMIT 5
";

$popular_stmt = $pdo->query($popular_query);
$popular_posts = $popular_stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Posts</title>
    <link rel="stylesheet" href="../css/style.css">
    <!-- ICONSCOUT CDN -->
    <link rel="stylesheet" href="https://unicons.iconscout.com/release/v4.0.0/css/line.css">
    <!-- BOOTSTRAP ICONS CDN -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-icons/1.5.0/font/bootstrap-icons.min.css">
    <!-- GOOGLE FONT (MONTSERRAT) -->
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">

    <style>
        /* General styles */
        body {
            font-family: 'Montserrat', sans-serif;
            background-color: #f4f6f8;
            margin: 0;
            padding: 0;
        }

        #app {
            display: flex;
        }

        #sidebar-container {
            width: 250px;
            background-color: white;
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
        }

        h2 {
            font-size: 2rem;
            color: #343a40;
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

        /* Modal styles */
        .modal {
            display: none; /* Hidden by default */
            position: fixed;
            z-index: 1;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            overflow: auto;
            background-color: rgba(0, 0, 0, 0.4);
            padding-top: 60px;
        }

        .modal-content {
            background-color: #fff;
            margin: 5% auto;
            padding: 20px;
            border: 1px solid #888;
            width: 80%;
            max-width: 600px;
            text-align: center;
        }

        .modal-content h2 {
            margin: 0;
        }

        .modal-content p {
            margin: 20px 0;
        }

        .modal-close {
            background-color: #6f6af8;
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
        }

        .modal-close:hover {
            background-color: #5854c7;
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

        @media screen and (max-width: 768px) {
            #sidebar-container {
                width: 200px;
            }

            #main-content {
                margin-left: 200px;
            }
        }

        @media screen and (max-width: 576px) {
            #sidebar-container {
                width: 100%;
                height: auto;
                border-right: none;
                border-bottom: 2px solid #d1d1e9;
                position: relative;
            }

            #main-content {
                margin-left: 0;
                padding: 20px;
            }
        }
    </style>
</head>
<body>
<?php include 'partials/header.php'; ?>

<!-- Success Popup -->
<?php if (isset($_SESSION['delete-post-success'])): ?>
    <div id="success-popup" class="popup success">Post deleted successfully.</div>
<?php endif; ?>

<!-- Error Popup -->
<?php if (isset($_SESSION['delete-post-error'])): ?>
    <div id="error-popup" class="popup error">Error deleting post.</div>
<?php endif; ?>

<div id="app">
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
                                <a href="manage-posts.php" class="sidebar-link active">Manage Posts</a>
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

    <div id="main-content">
        <h2>Manage Posts</h2>

        <!-- Popular Posts Section -->
        <h3>Top 5 Popular Posts</h3>
        <table>
            <thead>
            <tr>
                <th>Title</th>
                <th>Category</th>
                <th>Username</th>
                <th>Likes</th>
                <th>Comments</th>
                <th>Edit</th>
                <th>Delete</th>
            </tr>
            </thead>
            <tbody>
            <?php foreach ($popular_posts as $post): ?>
                <tr>
                    <td><?= htmlspecialchars($post['title'], ENT_QUOTES, 'UTF-8') ?></td>
                    <td><?= htmlspecialchars($post['category_name'], ENT_QUOTES, 'UTF-8') ?></td>
                    <td><?= htmlspecialchars($post['username'], ENT_QUOTES, 'UTF-8') ?></td>
                    <td><?= htmlspecialchars($post['total_likes'], ENT_QUOTES, 'UTF-8') ?></td>
                    <td><?= htmlspecialchars($post['total_comments'], ENT_QUOTES, 'UTF-8') ?></td>
                    <td><a href="edit-post.php?id=<?= $post['id'] ?>" class="btn sm">Edit</a></td>
                    <td><a href="javascript:void(0);" class="btn sm danger" onclick="confirmDelete(<?= $post['id'] ?>)">Delete</a></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>

        <h3>All Posts</h3>
        <table>
            <thead>
            <tr>
                <th>Title</th>
                <th>Category</th>
                <th>Username</th>
                <th>Likes</th>
                <th>Comments</th>
                <th>Edit</th>
                <th>Delete</th>
            </tr>
            </thead>
            <tbody>
            <?php foreach ($posts as $post): ?>
                <tr>
                    <td><?= htmlspecialchars($post['title'], ENT_QUOTES, 'UTF-8') ?></td>
                    <td><?= htmlspecialchars($post['category_name'], ENT_QUOTES, 'UTF-8') ?></td>
                    <td><?= htmlspecialchars($post['username'], ENT_QUOTES, 'UTF-8') ?></td>
                    <td><?= htmlspecialchars($post['total_likes'], ENT_QUOTES, 'UTF-8') ?></td>
                    <td><?= htmlspecialchars($post['total_comments'], ENT_QUOTES, 'UTF-8') ?></td>
                    <td><a href="edit-post.php?id=<?= $post['id'] ?>" class="btn sm">Edit</a></td>
                    <td><a href="javascript:void(0);" class="btn sm danger" onclick="confirmDelete(<?= $post['id'] ?>)">Delete</a></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
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

    // Function to confirm deletion
    function confirmDelete(postId) {
        if (confirm("Are you sure you want to delete this post?")) {
            window.location.href = 'delete-post.php?id=' + postId;
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
</script>
</body>
</html>
