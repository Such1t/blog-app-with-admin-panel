<?php
global $pdo;
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require 'config/constants.php';
require 'config/database.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['user-id'])) {
    header('Location: ' . ROOT_URL . 'signin.php');
    exit();
}

$loggedInUserId = $_SESSION['user-id'];
$profileUserId = isset($_GET['user_id']) ? filter_var($_GET['user_id'], FILTER_SANITIZE_NUMBER_INT) : $loggedInUserId;

$isAdmin = isset($_SESSION['user_is_admin']) && $_SESSION['user_is_admin'];
$isOwner = $loggedInUserId == $profileUserId;

if ($isAdmin && $isOwner) {
    header('Location: ' . ROOT_URL . 'admin/dashboard.php');
    exit();
}

// Fetch user details
$user_query = "SELECT * FROM users WHERE id = ?";
$user_stmt = $pdo->prepare($user_query);
$user_stmt->execute([$profileUserId]);
$user = $user_stmt->fetch(PDO::FETCH_ASSOC);

if (!$user) {
    header('Location: ' . ROOT_URL . 'dashboard.php');
    exit();
}

// Provide default values if fields are empty
$user['avatar_url'] = !empty($user['avatar_url']) ? $user['avatar_url'] : 'default-avatar.jpg';
$user['username'] = !empty($user['username']) ? $user['username'] : 'Unknown User';
$user['email'] = !empty($user['email']) ? $user['email'] : 'Unknown Email';
$user['profile_info'] = !empty($user['profile_info']) ? $user['profile_info'] : '';

// Fetch top 3 performing posts
$top_posts_query = "
    SELECT blog_posts.id, blog_posts.title, blog_posts.content, blog_posts.image_url, blog_posts.created_at, blog_posts.category_id,
    (SELECT COUNT(*) FROM likes WHERE likes.post_id = blog_posts.id) AS total_likes,
    (SELECT COUNT(*) FROM comments WHERE comments.post_id = blog_posts.id AND comments.user_id != blog_posts.user_id) AS total_comments,
    (SELECT COUNT(*) FROM analytics WHERE analytics.post_id = blog_posts.id) AS total_views,
    categories.name AS category_name
    FROM blog_posts
    LEFT JOIN categories ON blog_posts.category_id = categories.id
    WHERE blog_posts.user_id = :user_id AND blog_posts.status = 'published'
    ORDER BY total_likes DESC, total_comments DESC, total_views DESC
    LIMIT 3
";
$top_posts_stmt = $pdo->prepare($top_posts_query);
$top_posts_stmt->execute(['user_id' => $profileUserId]);
$top_posts = $top_posts_stmt->fetchAll(PDO::FETCH_ASSOC);

// Fetch user's posts with likes, comments, views, and category name
$posts_query = "
    SELECT blog_posts.id, blog_posts.title, blog_posts.content, blog_posts.image_url, blog_posts.created_at, blog_posts.category_id,
    (SELECT COUNT(*) FROM likes WHERE likes.post_id = blog_posts.id) AS total_likes,
    (SELECT COUNT(*) FROM comments WHERE comments.post_id = blog_posts.id AND comments.user_id != blog_posts.user_id) AS total_comments,
    (SELECT COUNT(*) FROM analytics WHERE analytics.post_id = blog_posts.id) AS total_views,
    categories.name AS category_name
    FROM blog_posts
    LEFT JOIN categories ON blog_posts.category_id = categories.id
    WHERE blog_posts.user_id = :user_id AND blog_posts.status = 'published'
";
$posts_stmt = $pdo->prepare($posts_query);
$posts_stmt->execute(['user_id' => $profileUserId]);
$posts = $posts_stmt->fetchAll(PDO::FETCH_ASSOC);

// Fetch user's drafts
$drafts_query = "
    SELECT blog_posts.id, blog_posts.title, blog_posts.content, blog_posts.image_url, blog_posts.created_at, blog_posts.category_id,
    categories.name AS category_name
    FROM blog_posts
    LEFT JOIN categories ON blog_posts.category_id = categories.id
    WHERE blog_posts.user_id = :user_id AND blog_posts.status = 'draft'
";
$drafts_stmt = $pdo->prepare($drafts_query);
$drafts_stmt->execute(['user_id' => $profileUserId]);
$drafts = $drafts_stmt->fetchAll(PDO::FETCH_ASSOC);

// Fetch total follower count
$follower_query = "SELECT COUNT(*) as follower_count FROM followers WHERE followed_id = ?";
$follower_stmt = $pdo->prepare($follower_query);
$follower_stmt->execute([$profileUserId]);
$follower_count = $follower_stmt->fetch(PDO::FETCH_ASSOC)['follower_count'];

// Fetch total following count
$following_count_query = "SELECT COUNT(*) as following_count FROM followers WHERE follower_id = ?";
$following_count_stmt = $pdo->prepare($following_count_query);
$following_count_stmt->execute([$profileUserId]);
$following_count = $following_count_stmt->fetch(PDO::FETCH_ASSOC)['following_count'];

// Fetch total likes, comments, and views count for all posts by user
$total_likes_query = "SELECT COUNT(*) as total_likes FROM likes WHERE post_id IN (SELECT id FROM blog_posts WHERE user_id = ?)";
$total_likes_stmt = $pdo->prepare($total_likes_query);
$total_likes_stmt->execute([$profileUserId]);
$total_likes = $total_likes_stmt->fetch(PDO::FETCH_ASSOC)['total_likes'];

$total_comments_query = "SELECT COUNT(*) as total_comments FROM comments WHERE post_id IN (SELECT id FROM blog_posts WHERE user_id = ?) AND comments.user_id != ?";
$total_comments_stmt = $pdo->prepare($total_comments_query);
$total_comments_stmt->execute([$profileUserId, $profileUserId]);
$total_comments = $total_comments_stmt->fetch(PDO::FETCH_ASSOC)['total_comments'];

$total_views_query = "SELECT COUNT(*) as total_views FROM analytics WHERE post_id IN (SELECT id FROM blog_posts WHERE user_id = ?)";
$total_views_stmt = $pdo->prepare($total_views_query);
$total_views_stmt->execute([$profileUserId]);
$total_views = $total_views_stmt->fetch(PDO::FETCH_ASSOC)['total_views'];

// Check if the current user follows this user
$following_query = "SELECT 1 FROM followers WHERE follower_id = ? AND followed_id = ?";
$following_stmt = $pdo->prepare($following_query);
$following_stmt->execute([$loggedInUserId, $profileUserId]);
$is_following = $following_stmt->fetchColumn() > 0;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Dashboard</title>
    <link rel="stylesheet" href="<?= ROOT_URL ?>css/style.css">
    <link rel="stylesheet" href="https://unicons.iconscout.com/release/v4.0.0/css/line.css">
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
    <style>
        /* Add your styles here */
        body {
            font-family: 'Montserrat', sans-serif;
            background-color: #f4f6f8;
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        .dashboard {
            padding: 5rem 1.5rem;
            max-width: 1200px;
            margin: 0 auto;
            min-height: 96vh;
            border-radius: 16px;
        }

        .dashboard__container {
            gap: 1rem;
            align-items: stretch;
            justify-content: flex;
            height: 100%;
        }

        .sidebar {
            width: 250px;
            background-color: #ffffff;
            padding: 1.5rem;
            border-radius: 16px;
            box-shadow: 0 8px 16px rgba(0, 0, 0, 0.1);
            display: flex;
            flex-direction: column;
            align-items: center;
            height: 100%;
        }

        .sidebar ul {
            list-style: none;
            padding: 0;
            width: 100%;
        }

        .sidebar ul li {
            margin-bottom: 1rem;
            width: 100%;
        }

        .sidebar ul li a {
            display: flex;
            align-items: center;
            gap: 1rem;
            text-decoration: none;
            color: #ffffff;
            font-size: 1.1rem;
            padding: 0.75rem 1rem;
            border-radius: 12px;
            transition: background-color 0.3s ease, transform 0.3s ease;
            font-weight: 500;
            background: linear-gradient(145deg, #6f6af8, #9a98f2);
            justify-content: center;
        }

        .sidebar ul li a:hover {
            background-color: #5e5cf1;
            transform: translateX(5px);
        }

        .sidebar ul li a.active {
            color: #6f6af8;
            background-color: #f0f0f0;
            font-weight: 700;
        }

        .profile__info {
            flex: 1;
            background: #ffffff;
            padding: 2.5rem;
            border-radius: 16px;
            box-shadow: 0 8px 16px rgba(0, 0, 0, 0.1);
            display: flex;
            flex-direction: column;
            align-items: center;
            text-align: center;
            transition: box-shadow 0.3s ease, transform 0.3s ease;
            height: 100%;
        }

        .profile__info:hover {
            box-shadow: 0 12px 24px rgba(0, 0, 0, 0.2);
            transform: translateY(-5px);
        }

        .profile__info img {
            width: 140px;
            height: 140px;
            border-radius: 50%;
            margin-bottom: 1rem;
            border: 4px solid #ffffff;
            box-shadow: 0 6px 12px rgba(0, 0, 0, 0.15);
        }

        .profile__info h3 {
            font-size: 1.75rem;
            margin-bottom: 0.5rem;
            color: #6f6af8;
            font-weight: 600;
        }

        .profile__info p {
            margin: 0;
            font-size: 1.1rem;
            color: #6f6af8;
        }

        .profile__stats {
            display: flex;
            gap: 1rem;
            margin-top: 2rem;
            width: 100%;
            justify-content: space-between;
        }

        .profile__stats li {
            padding: 1rem;
            background: #6f6af8;
            color: #ffffff;
            border-radius: 12px;
            text-align: center;
            flex: 1;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            transition: transform 0.3s ease, background-color 0.3s ease;
        }

        .profile__stats li:hover {
            transform: translateY(-5px);
            background-color: #5a55d1;
        }

        .profile__stats li span {
            display: block;
            font-size: 1.25rem;
            font-weight: 600;
            margin-top: 0.5rem;
        }

        .posts__container {
            display: flex;
            flex-direction: column;
            gap: 2rem;
            width: 90%;
            margin: 0 auto;
        }

        .posts__grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 1.5rem;
        }

        .post {
            background: #ffffff;
            border-radius: 12px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
            overflow: hidden;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            display: flex;
            flex-direction: column;
        }

        .post img {
            width: 100%;
            height: 180px;
            object-fit: cover;
        }

        .post:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.15);
        }

        .post__info {
            padding: 1.5rem;
            color: #6f6af8;
        }

        .post__title {
            font-size: 1.3rem;
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
            text-decoration: none;
            transition: color 0.3s ease;
        }

        .post__title a:hover {
            color: #5a55d1;
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

        .follow-btn {
            padding: 0.75rem 1.5rem;
            background-color: #6f6af8;
            color: white;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            margin-bottom: 10px;
            font-weight: bold;
            font-size: 1.1rem;
            transition: background-color 0.3s ease, transform 0.3s ease;
        }

        .follow-btn.unfollow-btn {
            background-color: #dc3545;
        }

        .follow-btn:hover {
            background-color: #5854c7;
            transform: translateY(-3px);
        }

        .follow-btn.unfollow-btn:hover {
            background-color: #a71b2a;
        }

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

        @media (max-width: 768px) {
            .dashboard__container {
                flex-direction: column;
                gap: 2rem;
            }

            .sidebar {
                width: 100%;
                margin-bottom: 2rem;
            }

            .profile__info {
                max-width: 100%;
            }
        }

        section {
            margin-top: 3rem;
            width: 100%;
        }

        .posts {
            background-color: #ffffff;
            padding: 2rem;
            border-radius: 16px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
            border-top: 4px solid #6f6af8;
            margin-top: 1rem;
            width: 100%;
        }

        .top-posts h2, .all-posts h2 {
            font-size: 1.8rem;
            color: #6f6af8;
            margin-bottom: 1rem;
        }

    </style>
</head>
<body>
<?php include 'partials/header.php'; ?>

<?php
// Display success message
if (isset($_SESSION['delete-post-success'])): ?>
    <div class="popup success" id="deleteSuccessPopup"><?= htmlspecialchars($_SESSION['delete-post-success'], ENT_QUOTES) ?></div>
    <?php unset($_SESSION['delete-post-success']); ?>
<?php endif; ?>

<?php
// Display error message
if (isset($_SESSION['delete-post-error'])): ?>
    <div class="popup error" id="deleteErrorPopup"><?= htmlspecialchars($_SESSION['delete-post-error'], ENT_QUOTES) ?></div>
    <?php unset($_SESSION['delete-post-error']); ?>
<?php endif; ?>

<section class="dashboard">
    <div class="dashboard__container">
        <aside class="sidebar">
            <ul>
                <?php if ($isOwner || ($isAdmin && !$isOwner)): ?>
                    <?php if ($isOwner): ?>
                        <li>
                            <a href="add-post.php"><i class="uil uil-pen"></i> Add Post</a>
                        </li>
                        <li>
                            <a href="manage-drafts.php"><i class="uil uil-pen"></i> Manage Drafts</a>
                        </li>
                        <li>
                            <a href="edit-profile.php"><i class="uil uil-user"></i> Edit Profile</a>
                        </li>
                    <?php endif; ?>
                <?php endif; ?>
            </ul>
        </aside>

        <div class="profile__info">
            <img src="<?= ROOT_URL ?>images/<?= htmlspecialchars($user['avatar_url'], ENT_QUOTES) ?>" alt="User Avatar">
            <h3><?= htmlspecialchars($user['username'], ENT_QUOTES) ?></h3>
            <p><?= htmlspecialchars($user['email'], ENT_QUOTES) ?></p>
            <p><?= htmlspecialchars($user['profile_info'], ENT_QUOTES) ?></p>
            <?php if ($loggedInUserId != $user['id']): ?>
                <button class="follow-btn <?= $is_following ? 'unfollow-btn' : '' ?>" data-user-id="<?= $profileUserId ?>"><?= $is_following ? 'Unfollow' : 'Follow' ?></button>
            <?php endif; ?>
            <ul class="profile__stats">
                <li>
                    <a href="followers.php?user_id=<?= $profileUserId ?>&type=followers" style="color: inherit; text-decoration: none;">
                        Total Followers: <span id="follower-count"><?= htmlspecialchars($follower_count, ENT_QUOTES) ?></span>
                    </a>
                </li>
                <li>
                    <a href="followers.php?user_id=<?= $profileUserId ?>&type=following" style="color: inherit; text-decoration: none;">
                        Total Following: <span><?= htmlspecialchars($following_count, ENT_QUOTES) ?></span>
                    </a>
                </li>
                <li>Total Likes: <span><?= htmlspecialchars($total_likes, ENT_QUOTES) ?></span></li>
                <li>Total Comments: <span><?= htmlspecialchars($total_comments, ENT_QUOTES) ?></span></li>
                <!-- <li>Total Views: <span><?= htmlspecialchars($total_views, ENT_QUOTES) ?></span></li> -->
            </ul>
        </div>
    </div>
</section>

<section class="posts">
    <div class="container posts__container">
        <!-- Top 3 Performing Posts Section -->
        <div class="top-posts">
            <h2>Top 3 Posts</h2>
            <div class="posts__grid">
                <?php foreach ($top_posts as $post): ?>
                    <article class="post">
                        <div class="post__thumbnail">
                            <a href="<?= ROOT_URL ?>post.php?id=<?= $post['id'] ?>">
                                <img src="<?= ROOT_URL ?>images/<?= htmlspecialchars($post['image_url'], ENT_QUOTES) ?>" alt="<?= htmlspecialchars($post['title'], ENT_QUOTES) ?>" loading="lazy">
                            </a>
                        </div>
                        <div class="post__info">
                            <a href="<?= ROOT_URL ?>category-posts.php?id=<?= $post['category_id'] ?>" class="category__button"><?= htmlspecialchars($post['category_name'], ENT_QUOTES) ?></a>
                            <h3 class="post__title">
                                <a href="<?= ROOT_URL ?>post.php?id=<?= $post['id'] ?>"><?= htmlspecialchars($post['title'], ENT_QUOTES) ?></a>
                            </h3>
                            <div class="post__author">
                                <div class="post__author-avatar">
                                    <img src="<?= ROOT_URL ?>images/<?= htmlspecialchars($user['avatar_url'], ENT_QUOTES) ?>" alt="<?= htmlspecialchars($user['username'], ENT_QUOTES) ?>" loading="lazy">
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
                <?php endforeach; ?>
            </div>
        </div>

        <!-- All Posts Section -->
        <div class="all-posts">
            <h2>All Posts</h2>
            <div class="posts__grid">
                <?php foreach ($posts as $post): ?>
                    <article class="post">
                        <div class="post__thumbnail">
                            <a href="<?= ROOT_URL ?>post.php?id=<?= $post['id'] ?>">
                                <img src="<?= ROOT_URL ?>images/<?= htmlspecialchars($post['image_url'], ENT_QUOTES) ?>" alt="<?= htmlspecialchars($post['title'], ENT_QUOTES) ?>" loading="lazy">
                            </a>
                        </div>
                        <div class="post__info">
                            <a href="<?= ROOT_URL ?>category-posts.php?id=<?= $post['category_id'] ?>" class="category__button"><?= htmlspecialchars($post['category_name'], ENT_QUOTES) ?></a>
                            <h3 class="post__title">
                                <a href="<?= ROOT_URL ?>post.php?id=<?= $post['id'] ?>"><?= htmlspecialchars($post['title'], ENT_QUOTES) ?></a>
                            </h3>
                            <div class="post__author">
                                <div class="post__author-avatar">
                                    <img src="<?= ROOT_URL ?>images/<?= htmlspecialchars($user['avatar_url'], ENT_QUOTES) ?>" alt="<?= htmlspecialchars($user['username'], ENT_QUOTES) ?>" loading="lazy">
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
        </div>
    </div>
</section>

<?php include 'partials/footer.php'; ?>

<script defer>
    document.addEventListener('DOMContentLoaded', function () {
        const followButton = document.querySelector('.follow-btn');
        if (followButton) {
            followButton.addEventListener('click', function () {
                const userId = this.dataset.userId;

                fetch('<?= ROOT_URL ?>follow.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: `followed_id=${userId}`
                })
                    .then(response => response.json())
                    .then(data => {
                        if (data.status === 'success') {
                            const followerCountSpan = document.getElementById('follower-count');
                            if (data.action === 'followed') {
                                this.classList.add('unfollow-btn');
                                this.textContent = 'Unfollow';
                                followerCountSpan.textContent = data.follower_count;
                            } else {
                                this.classList.remove('unfollow-btn');
                                this.textContent = 'Follow';
                                followerCountSpan.textContent = data.follower_count;
                            }
                        } else {
                            alert(data.message);
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        alert('There was an error processing your request.');
                    });
            });
        }

        const successPopup = document.getElementById('deleteSuccessPopup');
        const errorPopup = document.getElementById('deleteErrorPopup');

        if (successPopup) {
            successPopup.style.display = 'block';
            setTimeout(() => {
                successPopup.style.display = 'none';
            }, 3000);
        }

        if (errorPopup) {
            errorPopup.style.display = 'block';
            setTimeout(() => {
                errorPopup.style.display = 'none';
            }, 3000);
        }
    });
</script>
</body>
</html>
