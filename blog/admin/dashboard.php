<?php
require '../config/constants.php';
require '../config/database.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Check if user is logged in and is an admin
if (!isset($_SESSION['user-id']) || !isset($_SESSION['user_is_admin']) || !$_SESSION['user_is_admin']) {
    header('Location: ' . ROOT_URL . 'signin.php');
    exit();
}

$loggedInUserId = $_SESSION['user-id'];
$profileUserId = isset($_GET['user_id']) ? filter_var($_GET['user_id'], FILTER_SANITIZE_NUMBER_INT) : $loggedInUserId;

// Fetch user details
$user_query = "SELECT * FROM users WHERE id = ?";
$user_stmt = $pdo->prepare($user_query);
$user_stmt->execute([$profileUserId]);
$user = $user_stmt->fetch(PDO::FETCH_ASSOC);

if (!$user) {
    header('Location: ' . ROOT_URL . 'dashboard.php');
    exit();
}

// Fetch user's posts with likes, comments, and category name
$posts_query = "
    SELECT blog_posts.id, blog_posts.title, blog_posts.content, blog_posts.image_url, blog_posts.created_at, blog_posts.category_id,
    (SELECT COUNT(*) FROM likes WHERE likes.post_id = blog_posts.id) AS total_likes,
    (SELECT COUNT(*) FROM comments WHERE comments.post_id = blog_posts.id) AS total_comments,
    categories.name AS category_name
FROM blog_posts
LEFT JOIN categories ON blog_posts.category_id = categories.id
WHERE blog_posts.user_id = :user_id
  AND blog_posts.status = 'published'
ORDER BY blog_posts.created_at DESC
";

$posts_stmt = $pdo->prepare($posts_query);
$posts_stmt->execute(['user_id' => $profileUserId]);
$posts = $posts_stmt->fetchAll(PDO::FETCH_ASSOC);

// Fetch total follower count for profile user
$follower_query = "SELECT COUNT(*) as follower_count FROM followers WHERE followed_id = ?";
$follower_stmt = $pdo->prepare($follower_query);
$follower_stmt->execute([$profileUserId]);
$follower_count = $follower_stmt->fetch(PDO::FETCH_ASSOC)['follower_count'];

// Fetch total following count for profile user
$following_query = "SELECT COUNT(*) as following_count FROM followers WHERE follower_id = ?";
$following_stmt = $pdo->prepare($following_query);
$following_stmt->execute([$profileUserId]);
$following_count = $following_stmt->fetch(PDO::FETCH_ASSOC)['following_count'];

// Fetch total likes and comments count for all posts by profile user
$total_likes_query = "SELECT COUNT(*) as total_likes FROM likes WHERE post_id IN (SELECT id FROM blog_posts WHERE user_id = ?)";
$total_likes_stmt = $pdo->prepare($total_likes_query);
$total_likes_stmt->execute([$profileUserId]);
$user_total_likes = $total_likes_stmt->fetch(PDO::FETCH_ASSOC)['total_likes'];

$total_comments_query = "SELECT COUNT(*) as total_comments FROM comments WHERE post_id IN (SELECT id FROM blog_posts WHERE user_id = ?)";
$total_comments_stmt = $pdo->prepare($total_comments_query);
$total_comments_stmt->execute([$profileUserId]);
$user_total_comments = $total_comments_stmt->fetch(PDO::FETCH_ASSOC)['total_comments'];

// Check if the logged-in user is viewing their own profile
$isOwner = $loggedInUserId == $profileUserId;

// Determine if the user is an admin
$isAdmin = isset($_SESSION['user_is_admin']) && $_SESSION['user_is_admin'];

// Check if a success or error message should be displayed
$deleteSuccess = isset($_GET['deleted']) && $_GET['deleted'] === 'success';
$deleteError = isset($_GET['deleted']) && $_GET['deleted'] === 'error';

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>
    <link rel="stylesheet" href="../css/style.css">
    <!-- ICONSCOUT CDN -->
    <link rel="stylesheet" href="https://unicons.iconscout.com/release/v4.0.0/css/line.css">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css">
    <!-- GOOGLE FONT (MONTSERRAT) -->
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
    <style>
        html, body {
            height: 100%;
            margin: 0;
            font-family: 'Montserrat', sans-serif;
            background-color: #f4f6f8;
        }

        body {
            display: flex;
            flex-direction: column; /* Ensure the content stacks vertically */
            background-color: #f4f6f8;
        }

        #sidebar-container {
            width: 250px;
            background-color: white;
            color: #6f6af8;
            overflow-y: auto;
            padding-top: 20px;
            border-right: 2px solid #ddd;
            height: 100vh;
            position: fixed;
            top: 0;
            left: 0;
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

        /* Main content */
        .main-content {
            margin-left: 250px; /* Space for the sidebar */
            padding: 2rem;
            width: calc(100% - 250px); /* Adjust width to fit the remaining space */
            background-color: #f4f6f8;
            box-sizing: border-box;
            display: flex;
            flex-direction: column; /* Stack profile and posts vertically */
            align-items: center; /* Center content horizontally */
        }

        /* Profile information section */
        .profile__info {
            width: 100%;
            max-width: 800px;
            background: #ffffff;
            padding: 2rem;
            border-radius: 12px;
            box-shadow: 0 8px 16px rgba(0, 0, 0, 0.1); /* Enhanced shadow for depth */
            margin: 2rem auto; /* Center the container horizontally */
            text-align: center;
            border: 10px solid #dbe2e8; /* Subtle light blue-gray border for a modern touch */
            transition: box-shadow 0.3s ease, border-color 0.3s ease;
            display: flex;
            flex-direction: column;
            align-items: center;
            position: relative; /* To use pseudo-elements for additional border styling */
        }

        .profile__info::before {
            content: "";
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            border-radius: 12px;
            border: 2px solid #e0e0e0; /* Slightly thicker border for added depth */
            pointer-events: none; /* Ensure this pseudo-element does not interfere with interactions */
            transition: border-color 0.3s ease;
        }

        .profile__info:hover::before {
            border-color: #c5d0e0; /* Light blue-gray color for hover effect */
        }

        .profile__info:hover {
            box-shadow: 0 12px 24px rgba(0, 0, 0, 0.15); /* Enhanced shadow on hover */
            border-color: #d1d1d1; /* Slightly darker border on hover for subtle emphasis */
        }

        .profile__info img {
            width: 120px; /* Slightly larger profile image */
            height: 120px; /* Maintain aspect ratio */
            border-radius: 50%;
            margin-bottom: -2rem; /* Adjust if necessary */
            border: 4px solid #6f6af8; /* Consistent with color scheme */
            box-shadow: 0 6px 12px rgba(0, 0, 0, 0.15); /* Enhanced shadow for depth */
            object-fit: cover;
            background-color: #ffffff; /* Ensure background color matches */
        }

        .profile__info h3 {
            font-size: 1.75rem; /* Larger font size for the name */
            color: #333; /* Darker text color for better contrast */
            margin: 0.5rem 0; /* Adjust margin for better spacing */
        }

        .profile__info p {
            font-size: 1.125rem; /* Slightly larger font size for description */
            color: #666; /* Softer text color for the description */
            margin: 0;
        }

        .profile__info h3 {
            font-size: 1.8rem;
            margin-top: 3.5rem;
            color: #6f6af8;
            font-weight: 700;
        }

        /* Profile Description */
        .profile__info p {
            margin: 0;
            font-size: 1.2rem; /* Readable size */
            color: #6f6af8; /* Softer color for readability */
        }

        /* Profile Stats */
        .profile__stats {
            display: flex;
            gap: 1rem; /* Reduced space between items for a more compact look */
            justify-content: center;
            margin-top: 1.5rem; /* Slightly reduced margin for a cleaner layout */
        }

        .profile__stats li {
            padding: 0.75rem; /* Reduced padding for a more compact design */
            background: linear-gradient(145deg, #6f6af8, #c0c0e8); /* Gradient background */
            color: #ffffff;
            border-radius: 10px; /* Slightly rounded corners for a modern feel */
            text-align: center;
            flex: 1;
            transition: background-color 0.3s ease, transform 0.3s ease, box-shadow 0.3s ease;
            box-shadow: 0 2px 6px rgba(0, 0, 0, 0.1); /* Lighter shadow for subtle depth */
            font-size: 1rem; /* Adjusted font size for a cleaner appearance */
            font-weight: 500;
        }

        .profile__stats li a {
            color: #ffffff;
        }

        .profile__stats li:hover {
            background: #6f6af8; /* Subtle hover background color */
            transform: translateY(-2px); /* Slight hover lift */
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.15); /* Enhanced shadow on hover */
        }

        .profile__stats li span {
            display: block;
            font-size: 1.2rem; /* Adjusted font size for consistency */
            font-weight: 600;
            margin-top: 0.25rem; /* Reduced margin for a more compact design */
        }

        /* Posts section */
        .posts {
            width: 100%;
            max-width: 1200px; /* Limit the width of the posts container */
            margin-top: 2rem; /* Move the entire posts section down */
            margin-left: 4rem;
        }

        .posts__container {
            display: grid;
            grid-template-columns: repeat(4, 1fr); /* 4 equal-width columns */
            gap: 1.8rem; /* Maintain spacing between items */
            justify-items: center; /* Center items horizontally */
            padding: 0; /* Remove padding if not needed */
            width: 100%; /* Full width container */
            box-sizing: border-box; /* Ensure padding and border are included in the width */
            margin: 0 auto; /* Center container within its parent if needed */
        }

        .post {
            background: #ffffff;
            border-radius: 8px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            overflow: hidden;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            width: 100%;
            max-width: 280px;
            margin: 0; /* Remove margin to reduce the gap */
        }

        .post:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 16px rgba(0, 0, 0, 0.2);
        }

        .post__thumbnail img {
            width: 100%;
            height: 160px;
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

        .post__actions .btn.danger {
            background-color: #dc3545;
        }

        .post__actions .btn.danger:hover {
            background-color: #c82333;
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
<?php include 'partials/header.php'; ?>

<!-- Success Popup -->
<?php if ($deleteSuccess): ?>
    <div id="success-popup" class="popup success">Post deleted successfully.</div>
<?php endif; ?>

<!-- Error Popup -->
<?php if ($deleteError): ?>
    <div id="error-popup" class="popup error">Error deleting post.</div>
<?php endif; ?>

<!-- Sidebar Section -->
<div id="app">
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
</div>

<!-- Main Content -->
<section class="main-content">
    <section class="profile">
        <div class="profile__info">
            <img src="<?= ROOT_URL ?>images/<?= htmlspecialchars($user['avatar_url'], ENT_QUOTES) ?>" alt="<?= htmlspecialchars($user['username'], ENT_QUOTES) ?>">
            <h3><?= htmlspecialchars($user['username'], ENT_QUOTES) ?></h3>
            <p><?= htmlspecialchars($user['email'], ENT_QUOTES) ?></p>
            <div class="profile__stats">
                <li><a href="<?= ROOT_URL ?>followers.php?user_id=<?= $profileUserId ?>&type=followers">Followers: <span><?= $follower_count ?></span></a></li>
                <li><a href="<?= ROOT_URL ?>followers.php?user_id=<?= $profileUserId ?>&type=following">Following: <span><?= $following_count ?></span></a></li>
                <li>Total Likes: <span><?= $user_total_likes ?></span></li>
                <li>Total Comments: <span><?= $user_total_comments ?></span></li>
            </div>
        </div>
    </section>

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
                                <img src="<?= ROOT_URL ?>images/<?= htmlspecialchars($user['avatar_url'], ENT_QUOTES) ?>" alt="<?= htmlspecialchars($user['username'], ENT_QUOTES) ?>">
                            </div>
                            <div class="post__author-info">
                                <h5>By: <?= htmlspecialchars($user['username'], ENT_QUOTES) ?></h5>
                                <small><?= date("M d, Y - H:i", strtotime($post['created_at'])) ?></small>
                            </div>
                        </div>
                        <?php if ($isOwner || $isAdmin): ?>
                            <div class="post__actions">
                                <?php if ($isOwner): ?>
                                    <a href="edit-post.php?id=<?= $post['id'] ?>" class="btn sm">Edit</a>
                                <?php endif; ?>
                                <a href="delete-post.php?id=<?= $post['id'] ?>" class="btn sm danger">Delete</a>
                            </div>
                        <?php endif; ?>
                    </div>
                </article>
            <?php endforeach ?>
        </div>
    </section>
</section>

<script>
    // Sidebar dropdown functionality
    document.querySelectorAll('.sidebar-item.has-sub').forEach(dropdown => {
        dropdown.addEventListener('click', function() {
            this.classList.toggle('open');
            const submenu = this.querySelector('.submenu');
            submenu.style.display = submenu.style.display === 'block' ? 'none' : 'block';
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
</script>
<script src="js/main.js"></script>
</body>
</html>
