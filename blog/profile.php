<?php
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

$user['avatar_url'] = $user['avatar_url'] ?? 'default-avatar.jpg';
$user['username'] = $user['username'] ?? 'Unknown User';
$user['email'] = $user['email'] ?? 'Unknown Email';
$user['profile_info'] = $user['profile_info'] ?? '';

// Fetch user's posts with likes, comments, and category name
$posts_query = "
    SELECT blog_posts.id, blog_posts.title, blog_posts.content, blog_posts.image_url, blog_posts.created_at, blog_posts.category_id,
    (SELECT COUNT(*) FROM likes WHERE likes.post_id = blog_posts.id) AS total_likes,
    (SELECT COUNT(*) FROM comments WHERE comments.post_id = blog_posts.id) AS total_comments,
    categories.name AS category_name,
    users.username AS author_username,
    users.avatar_url AS author_avatar_url
    FROM blog_posts
    LEFT JOIN categories ON blog_posts.category_id = categories.id
    LEFT JOIN users ON blog_posts.user_id = users.id
    WHERE blog_posts.user_id = :user_id AND blog_posts.status = 'published'
";
$posts_stmt = $pdo->prepare($posts_query);
$posts_stmt->execute(['user_id' => $profileUserId]);
$posts = $posts_stmt->fetchAll(PDO::FETCH_ASSOC);

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

// Check if the current user follows this user
$following_query = "SELECT 1 FROM followers WHERE follower_id = ? AND followed_id = ?";
$following_stmt = $pdo->prepare($following_query);
$following_stmt->execute([$loggedInUserId, $profileUserId]);
$is_following = $following_stmt->fetchColumn() > 0;

include 'partials/header.php';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($user['username'], ENT_QUOTES) ?>'s Profile</title>
    <link rel="stylesheet" href="<?= ROOT_URL ?>css/style.css">
    <link rel="stylesheet" href="https://unicons.iconscout.com/release/v4.0.0/css/line.css">
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Montserrat', sans-serif;
            background-color: #f4f6f8;
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        .profile__info {
            background: #ffffff;
            padding: 2.5rem;
            border-radius: 16px;
            box-shadow: 0 8px 16px rgba(0, 0, 0, 0.1);
            display: flex;
            flex-direction: column;
            align-items: center;
            text-align: center;
            transition: box-shadow 0.3s ease, transform 0.3s ease;
            max-width: calc(4 * 300px + 3 * 1.5rem); /* Adjust this to match the width of four posts with gaps */
            margin: 2rem auto;
            background: white;
        }

        @media (max-width: 1200px) {
            .profile__info {
                max-width: 100%; /* For smaller screens, ensure it fits */
            }
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
            gap: 1.5rem;
            margin-top: 2rem;
            width: 100%;
            justify-content: center; /* Center the items */
        }

        .profile__stats li {
            flex: none; /* Prevent stretching */
            width: 120px; /* Fixed width for each stat item */
            padding: 1rem;
            background: #6f6af8;
            color: #ffffff;
            border-radius: 12px;
            text-align: center;
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
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 1.5rem;
            width: 90%;
            margin: 0 auto;
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

        .posts {
            background-color: #ffffff;
            padding: 2rem;
            border-radius: 16px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
            border-top: 4px solid #6f6af8; /* Adds a colored border at the top */
            margin-top: 1rem; /* Adds a bit of space above the posts section */
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

        .post__author {
            display: flex;
            align-items: center;
            margin-top: 1rem;
        }

        .post__author-avatar img {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            margin-right: 0.5rem;
        }

        /* Follow Button Styles */
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

        @media (max-width: 768px) {
            .posts__container {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>

<section class="profile__info">
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
                Followers: <span id="follower-count"><?= $follower_count ?></span>
            </a>
        </li>
        <li>
            <a href="followers.php?user_id=<?= $profileUserId ?>&type=following" style="color: inherit; text-decoration: none;">
                Following: <span><?= $following_count ?></span>
            </a>
        </li>
    </ul>
</section>

<section class="posts">
    <div class="container posts__container">
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
                            <img src="<?= ROOT_URL ?>images/<?= htmlspecialchars($post['author_avatar_url'], ENT_QUOTES) ?>" alt="<?= htmlspecialchars($post['author_username'], ENT_QUOTES) ?>" loading="lazy">
                        </div>
                        <div class="post__author-info">
                            <h5>By: <?= htmlspecialchars($post['author_username'], ENT_QUOTES) ?></h5>
                            <small><?= date("M d, Y - H:i", strtotime($post['created_at'])) ?></small>
                        </div>
                    </div>
                </div>
            </article>
        <?php endforeach ?>
    </div>
</section>

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
    });
</script>
<?php include 'partials/footer.php'; ?>
</body>
</html>
