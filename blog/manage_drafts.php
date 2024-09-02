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

$loggedInUserId = $_SESSION['user-id'];

// Fetch user's drafts
$drafts_query = "
    SELECT blog_posts.id, blog_posts.title, blog_posts.content, blog_posts.image_url, blog_posts.created_at, blog_posts.category_id,
    categories.name AS category_name
    FROM blog_posts
    LEFT JOIN categories ON blog_posts.category_id = categories.id
    WHERE blog_posts.user_id = :user_id AND blog_posts.status = 'draft'
";
$drafts_stmt = $pdo->prepare($drafts_query);
$drafts_stmt->execute(['user_id' => $loggedInUserId]);
$drafts = $drafts_stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Drafts</title>
    <link rel="stylesheet" href="<?= ROOT_URL ?>css/style.css">
    <!-- ICONSCOUT CDN -->
    <link rel="stylesheet" href="https://unicons.iconscout.com/release/v4.0.0/css/line.css">
    <!-- GOOGLE FONT (MONTSERRAT) -->
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
    <style>
        .post__actions {
            margin-top: 10px;
            display: flex;
            gap: 10px;
        }

        .post__actions .btn {
            padding: 5px 10px;
        }

        .post__thumbnail img {
            width: 100%;
            height: auto;
        }

        .post__author-avatar img {
            width: 50px;
            height: 50px;
            border-radius: 50%;
        }

        .post__icons {
            display: flex;
            gap: 15px;
            margin-top: 10px;
        }

        .icon-container {
            display: flex;
            align-items: center;
            gap: 5px;
        }

        .icon-container i {
            font-size: 1.2rem;
            color: var(--color-primary);
        }

        .like-count,
        .comment-count,
        .follower-count {
            font-size: 1rem;
            color: var(--color-primary);
        }

        .follow-btn {
            padding: 0.5rem 1rem;
            background-color: var(--color-primary);
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            margin-bottom: 10px;
        }
    </style>
</head>
<body>
<?php include 'partials/header.php'; ?>

<section class="dashboard">
    <div class="container dashboard__container">
        <button id="show__sidebar-btn" class="sidebar__toggle"><i class="uil uil-angle-right-b"></i></button>
        <button id="hide__sidebar-btn" class="sidebar__toggle"><i class="uil uil-angle-left-b"></i></button>
        <aside>
            <ul>
                <li>
                    <a href="dashboard.php"><i class="uil uil-estate"></i>
                        <h5>Dashboard</h5>
                    </a>
                </li>
                <li>
                    <a href="add-post.php"><i class="uil uil-pen"></i>
                        <h5>Add Post</h5>
                    </a>
                </li>
                <li>
                    <a href="edit-profile.php"><i class="uil uil-user"></i>
                        <h5>Edit Profile</h5>
                    </a>
                </li>
                <li>
                    <a href="manage-drafts.php"><i class="uil uil-file"></i>
                        <h5>Manage Drafts</h5>
                    </a>
                </li>
            </ul>
        </aside>
        <main>
            <h2>Manage Drafts</h2>
            <div class="container drafts__container">
                <?php foreach ($drafts as $draft): ?>
                    <article class="post">
                        <div class="post__thumbnail">
                            <a href="<?= ROOT_URL ?>edit-draft.php?id=<?= $draft['id'] ?>">
                                <img src="<?= ROOT_URL ?>images/<?= htmlspecialchars($draft['image_url'], ENT_QUOTES) ?>">
                            </a>
                        </div>
                        <div class="post__info">
                            <a href="<?= ROOT_URL ?>category-posts.php?id=<?= $draft['category_id'] ?>" class="category__button"><?= htmlspecialchars($draft['category_name'], ENT_QUOTES) ?></a>
                            <h3 class="post__title">
                                <a href="<?= ROOT_URL ?>edit-draft.php?id=<?= $draft['id'] ?>"><?= htmlspecialchars($draft['title'], ENT_QUOTES) ?></a>
                            </h3>
                            <p class="post__body">
                                <?= htmlspecialchars_decode(substr($draft['content'], 0, 150), ENT_QUOTES) ?>...
                            </p>
                            <div class="post__actions">
                                <a href="edit-draft.php?id=<?= $draft['id'] ?>" class="btn sm">Edit</a>
                                <a href="delete-draft.php?id=<?= $draft['id'] ?>" class="btn sm danger">Delete</a>
                            </div>
                        </div>
                    </article>
                <?php endforeach ?>
            </div>
        </main>
    </div>
</section>

<script>
    const navItems = document.querySelector('.nav__items');
    const openNavBtn = document.querySelector('#open__nav-btn');
    const closeNavBtn = document.querySelector('#close__nav-btn');

    const openNav = () => {
        navItems.style.display = 'flex';
        openNavBtn.style.display = 'none';
        closeNavBtn.style.display = 'inline-block';
    }

    const closeNav = () => {
        navItems.style.display = 'none';
        openNavBtn.style.display = 'inline-block';
        closeNavBtn.style.display = 'none';
    }

    openNavBtn.addEventListener('click', openNav);
    closeNavBtn.addEventListener('click', closeNav);

    const sidebar = document.querySelector('aside');
    const showSidebarBtn = document.querySelector('#show__sidebar-btn');
    const hideSidebarBtn = document.querySelector('#hide__sidebar-btn');

    const showSidebar = () => {
        sidebar.style.left = '0';
        showSidebarBtn.style.display = 'none';
        hideSidebarBtn.style.display = 'inline-block';
    }
    const hideSidebar = () => {
        sidebar.style.left = '-100%';
        showSidebarBtn.style.display = 'inline-block';
        hideSidebarBtn.style.display = 'none';
    }

    showSidebarBtn.addEventListener('click', showSidebar);
    hideSidebarBtn.addEventListener('click', hideSidebar);
</script>
</body>
</html>
