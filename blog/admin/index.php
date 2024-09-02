<?php
session_start(); // Ensure session is started

// Redirect to the appropriate dashboard based on the user's role
if (isset($_SESSION['user_is_admin']) && $_SESSION['user_is_admin']) {
    // Continue to the admin index page with the sidebar
} else {
    header('Location: ../index.php');
    exit();
}

// Database connection and queries for statistics
require '../config/constants.php';
require '../config/database.php';

// Initialize and assign values to $total_users, $total_comments, $total_likes, and $total_posts
$query_users = "SELECT COUNT(*) AS total_users FROM users";
$stmt_users = $pdo->query($query_users);
$total_users = $stmt_users->fetch(PDO::FETCH_ASSOC)['total_users'];

$query_comments = "SELECT COUNT(*) AS total_comments FROM comments";
$stmt_comments = $pdo->query($query_comments);
$total_comments = $stmt_comments->fetch(PDO::FETCH_ASSOC)['total_comments'];

$query_likes = "SELECT COUNT(*) AS total_likes FROM likes";
$stmt_likes = $pdo->query($query_likes);
$total_likes = $stmt_likes->fetch(PDO::FETCH_ASSOC)['total_likes'];

$query_posts = "SELECT COUNT(*) AS total_posts FROM blog_posts WHERE status = 'published'";
$stmt_posts = $pdo->query($query_posts);
$total_posts = $stmt_posts->fetch(PDO::FETCH_ASSOC)['total_posts'];

// Pagination variables
$postsPerPage = 12;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $postsPerPage;

// Fetch blog posts with author details where status is 'published'
$query_blog_posts = "
    SELECT blog_posts.id, blog_posts.title, blog_posts.image_url, blog_posts.created_at, 
           categories.name AS category_name, users.username, users.avatar_url, blog_posts.category_id
    FROM blog_posts
    LEFT JOIN categories ON blog_posts.category_id = categories.id
    LEFT JOIN users ON blog_posts.user_id = users.id
    WHERE blog_posts.status = 'published'
    ORDER BY blog_posts.created_at DESC
    LIMIT :limit OFFSET :offset
";
$stmt_blog_posts = $pdo->prepare($query_blog_posts);
$stmt_blog_posts->bindParam(':limit', $postsPerPage, PDO::PARAM_INT);
$stmt_blog_posts->bindParam(':offset', $offset, PDO::PARAM_INT);
$stmt_blog_posts->execute();
$posts = $stmt_blog_posts->fetchAll(PDO::FETCH_ASSOC);


// Fetch total number of posts for pagination
$total_query = "
    SELECT COUNT(*) AS total_posts
    FROM blog_posts
    WHERE status = 'published'
";
$total_pages = ceil($total_posts / $postsPerPage);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <?php include 'partials/header.php'; ?>

    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>

    <link rel="preconnect" href="https://fonts.gstatic.com">
    <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@300;400;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css">
    <link rel="shortcut icon" href="assets/images/favicon.svg" type="image/x-icon">

    <!-- Internal CSS -->
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

        /* Style for the stats table */
        .dashboard__stats-table {
            width: 100%;
            margin: 20px 0;
            border-collapse: collapse;
            text-align: left;
            font-size: 1rem;
            background-color: #6f6af8;
            color: white;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            border-radius: 8px;
            overflow: hidden;
        }

        .dashboard__stats-table th,
        .dashboard__stats-table td {
            padding: 12px;
            border: 1px solid #fff;
        }

        .dashboard__stats-table th {
            background-color: #4c4ba0;
            color: #fff;
        }

        .dashboard__stats-table td {
            background-color: #6f6af8;
        }

        .dashboard__stats-table td:first-child,
        .dashboard__stats-table th:first-child {
            border-top-left-radius: 8px;
            border-bottom-left-radius: 8px;
        }

        .dashboard__stats-table td:last-child,
        .dashboard__stats-table th:last-child {
            border-top-right-radius: 8px;
            border-bottom-right-radius: 8px;
        }

        .main__container h3 {
            color: #6f6af8;
        }

        .profile__stats {
            display: flex;
            gap: 7.5rem; /* Reduce the gap */
            margin-top: 2rem; /* Reduce top margin */
            padding: 0.8rem; /* Reduce padding */
            border: 1px solid #dcdcdc; /* Slightly darker border */
            border-radius: 0.75rem; /* Slightly more rounded corners */
            background: linear-gradient(145deg, #ffffff, #f0f0f0);
            box-shadow: 0 6px 12px rgba(0, 0, 0, 0.15); /* Slightly deeper shadow */
            align-items: center; /* Vertically center items */
            flex-wrap: wrap; /* Wrap items to fit the container */
        }

        .profile__stats:hover {
            background: linear-gradient(145deg, #f0f0f0, #ffffff);
            box-shadow: 0 8px 16px rgba(0, 0, 0, 0.2); /* Deeper shadow */
            transform: translateY(-2px); /* Slight lift effect */
        }

        @media (max-width: 768px) {
            .profile__stats {
                flex-direction: column; /* Stack items vertically on smaller screens */
                align-items: center; /* Center align items on smaller screens */
            }
        }

        .profile__stats li {
            padding: 0.8rem; /* Reduce padding */
            width: 80%; /* Make list items take full width */
            max-width: 180px; /* Reduce max-width */
            color: #ffffff; /* White text for better contrast */
            border-radius: 12px;
            text-align: center;
            flex: 1;
            border: 1px solid #6f6af8; /* Border color matching your theme */
            background: linear-gradient(145deg, #6f6af8, #c0c0e8); /* Gradient background */
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2); /* Slightly deeper shadow */
            transition: all 0.3s ease;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .profile__stats li:hover {
            background: linear-gradient(145deg, #4a4b8d, #6f6af8); /* Inverse gradient on hover */
            box-shadow: 0 6px 12px rgba(0, 0, 0, 0.3); /* Deeper shadow on hover */
            transform: translateY(-2px); /* Slight lift effect on hover */
        }

        @media (max-width: 768px) {
            .profile__stats li {
                max-width: 100%; /* Adjust to 100% width on smaller screens */
                padding: 0.6rem; /* Adjust padding for better fit */
            }
        }

        .profile__stats li span {
            display: block;
            font-size: 1rem; /* Adjust font size */
            font-weight: bold;
            margin-top: 0.4rem; /* Adjust margin */
        }

        /* Styles for blog posts section */
        .posts__container {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 1rem;
            width: 82%;
        }

        .post {
            background: #ffffff;
            border-radius: 8px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            overflow: hidden;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }

        .post:hover {
            transform: translateY(-5px);
            box-shadow: 0 6px 15px rgba(0, 0, 0, 0.15);
        }

        .post__thumbnail img {
            width: 100%;
            height: 180px;
            object-fit: cover;
        }

        .post__info {
            padding: 1.5rem;
            color: #6f6af8;
        }

        .post__title {
            font-size: 1.2rem;
            color: #6f6af8;
            margin-bottom: 0.5rem;
        }

        .post__author-info h5,
        .post__author-info small {
            margin: 0;
            color: #6f6af8;
        }

        .post__title h3,
        .post__title a {
            color: #6f6af8;
        }

        .post__author-avatar img {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            margin-right: 0.5rem;
        }

        .post__actions {
            display: flex;
            justify-content: space-between;
            margin-top: 1rem;
        }

        .post__actions .btn {
            padding: 0.5rem 1rem;
            font-size: 0.9rem;
            border-radius: 6px;
            background-color: #6f6af8;
            color: #fff;
            text-align: center;
            transition: background-color 0.3s ease;
        }

        .post__actions .btn:hover {
            background-color: #5854c7;
        }

        .page-heading h3 {
            color: #6f6af8;
        }

        /* Pagination styles */
        .pagination {
            display: flex;
            justify-content: center;
            margin-top: 1rem;
            gap: 0.5rem;
            margin-right:30rem;
            margin-bottom:5rem;
        }

        .pagination__link {
            padding: 0.5rem 1rem;
            background: #6f6af8;
            color: white;
            border-radius: 5px;
            text-decoration: none;
        }

        .pagination__link.active {
            background: #5854c7;
            font-weight: bold;
        }

        .pagination__link:hover {
            background: #5854c7;
        }

        /* Popup styles */
        #success-popup {
            display: none;
            position: fixed;
            top: 10%;
            right: 10%;
            background-color: #28a745;
            color: white;
            padding: 1rem 2rem;
            border-radius: 8px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            z-index: 1000;
            font-size: 1.2rem;
            transition: all 0.3s ease;
        }
    </style>
</head>

<body>

<?php if (isset($_SESSION['edit-post-success'])): ?>
    <div id="success-popup"><?= $_SESSION['edit-post-success'] ?></div>
    <?php unset($_SESSION['edit-post-success']); ?>
<?php endif; ?>

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

    <!-- Main Container -->
    <div id="main-container">
        <div class="page-heading">
            <h3>Website Statistics</h3>
        </div>

        <ul class="profile__stats">
            <li>
                Total Users:<br><?= htmlspecialchars($total_users, ENT_QUOTES, 'UTF-8') ?>
            </li>
            <li>
                Total Comments:<br><?= htmlspecialchars($total_comments, ENT_QUOTES, 'UTF-8') ?>
            </li>
            <li>
                Total Likes:<br><?= htmlspecialchars($total_likes, ENT_QUOTES, 'UTF-8') ?>
            </li>
            <li>
                Total Posts:<br><?= htmlspecialchars($total_posts, ENT_QUOTES, 'UTF-8') ?>
            </li>
        </ul>

        <section class="posts">
            <div class="posts__container">
                <?php foreach ($posts as $post): ?>
                    <article class="post">
                        <div class="post__thumbnail">
                            <a href="<?= ROOT_URL ?>post.php?id=<?= $post['id'] ?>">
                                <img src="<?= ROOT_URL ?>images/<?= htmlspecialchars($post['image_url'], ENT_QUOTES) ?>" alt="<?= htmlspecialchars($post['title'], ENT_QUOTES) ?>">
                            </a>
                        </div>
                        <div class="post__info">
                            <a href="<?= ROOT_URL ?>category-posts.php?id=<?= $post['category_id'] ?>" class="category__button"><?= htmlspecialchars($post['category_name'], ENT_QUOTES) ?></a>
                            <h3 class="post__title">
                                <a href="<?= ROOT_URL ?>post.php?id=<?= $post['id'] ?>"><?= htmlspecialchars($post['title'], ENT_QUOTES) ?></a>
                            </h3>
                            <div class="post__author">
                                <div class="post__author-avatar">
                                    <img src="<?= ROOT_URL ?>images/<?= htmlspecialchars($post['avatar_url'], ENT_QUOTES) ?>" alt="<?= htmlspecialchars($post['username'], ENT_QUOTES) ?>">
                                </div>
                                <div class="post__author-info">
                                    <h5>By: <?= htmlspecialchars($post['username'], ENT_QUOTES) ?></h5>
                                    <small><?= date("M d, Y - H:i", strtotime($post['created_at'])) ?></small>
                                </div>
                            </div>

                        </div>
                    </article>
                <?php endforeach ?>
            </div>

            <!-- Pagination -->
            <div class="pagination">
                <?php if ($page > 1): ?>
                    <a href="?page=<?= $page - 1 ?>" class="pagination__link">&laquo; Prev</a>
                <?php endif; ?>

                <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                    <a href="?page=<?= $i ?>" class="pagination__link <?= $i == $page ? 'active' : '' ?>"><?= $i ?></a>
                <?php endfor; ?>

                <?php if ($page < $total_pages): ?>
                    <a href="?page=<?= $page + 1 ?>" class="pagination__link">Next &raquo;</a>
                <?php endif; ?>
            </div>
        </section>
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

        // Popup display logic
        const successPopup = document.getElementById('success-popup');
        if (successPopup) {
            successPopup.style.display = 'block';
            setTimeout(() => {
                successPopup.style.display = 'none';
            }, 3000); // Hide after 3 seconds
        }
    });
</script>

</body>
</html>
