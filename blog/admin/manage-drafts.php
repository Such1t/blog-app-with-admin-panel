<?php
require '../config/constants.php';
require '../config/database.php';

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
    SELECT blog_posts.id, blog_posts.title, blog_posts.content, blog_posts.created_at, blog_posts.category_id,
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
    <link rel="preconnect" href="https://fonts.gstatic.com">
    <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@300;400;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css">
    <link rel="shortcut icon" href="assets/images/favicon.svg" type="image/x-icon">

    <style>
        body {
            font-family: 'Montserrat', sans-serif;
            background-color: #f4f4f9;
            color: #333;
            margin: 0;
            padding: 0;
            display: flex;
            flex-direction: column;
            min-height: 100vh;
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

        main {
            margin-left: 360px;
            padding: 2rem;
            flex-grow: 1;
            background-color: #ffffff;
            border-radius: 12px;
            box-shadow: 0 8px 16px rgba(0, 0, 0, 0.1);
            margin: 2rem auto;
            max-width: 1200px;
            width: 100%;
        }

        h2 {
            color: #6f6af8;
            margin-bottom: 1.5rem;
            font-weight: 700;
            text-align: center;
            font-size: 2rem;
        }

        .drafts__container {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
            gap: 1.5rem;
            justify-content: center;
            margin-left: 60px; /* Adjust this value to move further right */
        }

        .post__info {
            padding: 1.5rem;
            border: 1px solid #ddd;
            border-radius: 8px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.05);
            transition: box-shadow 0.3s ease;
        }

        .post__info:hover {
            box-shadow: 0 6px 15px rgba(0, 0, 0, 0.1);
        }

        .post__title {
            margin: 0 0 0.5rem;
            font-size: 1.2rem;
            color: #333;
            font-weight: 600;
        }

        .post__title a {
            text-decoration: none;
            color: #6f6af8;
            transition: color 0.3s ease;
        }

        .post__title a:hover {
            color: #5854c7;
        }

        .post__body {
            margin: 0 0 1rem;
            font-size: 0.9rem;
            color: #333;
        }

        .post__actions {
            display: flex;
            gap: 0.5rem;
        }

        .post__actions .btn {
            background-color: #6f6af8;
            color: white;
            border: none;
            padding: 0.5rem 1rem;
            border-radius: 5px;
            cursor: pointer;
            transition: background-color 0.3s ease;
            font-size: 0.9rem;
            text-align: center;
        }

        .post__actions .btn:hover {
            background-color: #5854c7;
        }

        .post__actions .btn.danger {
            background-color: #e74c3c;
        }

        .post__actions .btn.danger:hover {
            background-color: #c0392b;
        }

        .category__button {
            display: inline-block;
            padding: 0.3rem 0.7rem;
            background-color: #6f6af8;
            color: white;
            border-radius: 5px;
            font-size: 0.85rem;
            text-decoration: none;
            margin-bottom: 0.5rem;
        }

        @media (max-width: 768px) {
            main {
                padding-left: 0;
                padding-top: 0;
            }

            #sidebar-container {
                width: 100%;
                height: auto;
                position: relative;
                border-right: none;
            }

            .drafts__container {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
<?php include 'partials/header.php'; ?>

<!-- Sidebar Section -->
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
                        <li>
                            <a href="manage-drafts.php" class="sidebar-link">Manage Drafts</a>
                        </li>
                    </ul>
                </li>

                <li class="sidebar-item has-sub">
                    <a href="javascript:void(0);" class='sidebar-link'>
                        <i class="uil uil-list-ul"></i>
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

<!-- Main Content -->
<main>
    <h2>Manage Drafts</h2>
    <div class="drafts__container">
        <?php if (empty($drafts)): ?>
            <p>No drafts available.</p>
        <?php else: ?>
            <?php foreach ($drafts as $draft): ?>
                <article class="post__info">
                    <a href="<?= ROOT_URL ?>category-posts.php?id=<?= $draft['category_id'] ?>" class="category__button"><?= htmlspecialchars($draft['category_name'], ENT_QUOTES) ?></a>
                    <h3 class="post__title">
                        <a href="<?= ROOT_URL ?>admin/edit-draft.php?id=<?= $draft['id'] ?>"><?= htmlspecialchars($draft['title'], ENT_QUOTES) ?></a>
                    </h3>
                    <p class="post__body">
                        <?php
                        // Remove images from content for preview
                        $content_preview = strip_tags($draft['content']);
                        $content_preview = preg_replace('/<img[^>]+\>/i', '', $content_preview);
                        echo htmlspecialchars_decode(substr($content_preview, 0, 150), ENT_QUOTES);
                        ?>...
                    </p>
                    <div class="post__actions">
                        <a href="edit-draft.php?id=<?= $draft['id'] ?>" class="btn sm">Edit</a>
                        <a href="delete-draft.php?id=<?= $draft['id'] ?>" class="btn sm danger">Delete</a>
                    </div>
                </article>
            <?php endforeach ?>
        <?php endif; ?>
    </div>
</main>

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
