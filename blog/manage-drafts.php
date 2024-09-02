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

$cleanup_query = "
    DELETE FROM blog_posts
    WHERE user_id = :user_id AND status = 'draft' AND created_at < NOW() - INTERVAL 1 WEEK
";
$cleanup_stmt = $pdo->prepare($cleanup_query);
$cleanup_stmt->execute(['user_id' => $loggedInUserId]);
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
    <link rel="stylesheet" href="<?= ROOT_URL ?>css/style.css">
    <!-- ICONSCOUT CDN -->
    <link rel="stylesheet" href="https://unicons.iconscout.com/release/v4.0.0/css/line.css">
    <!-- GOOGLE FONT (MONTSERRAT) -->
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
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

        main {
            flex-grow: 1;
            padding: 2rem;
            background-color: #ffffff;
            border-radius: 12px;
            box-shadow: 0 8px 16px rgba(0, 0, 0, 0.1);
            margin: 2rem auto;
            max-width: 1200px;
            width: 100%;
        }

        h2 {
            color: #333;
            margin-bottom: 2rem;
            font-weight: 700;
            text-align: center;
            font-size: 2rem;
        }

        .drafts__container {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 1.5rem;
        }
        .drafts__container p{
            color:#6f6af8;
        }
        .draft__card {
            background-color: #f9f9f9;
            border-radius: 10px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
            padding: 1.5rem;
            transition: box-shadow 0.3s ease, transform 0.3s ease;
        }

        .draft__card:hover {
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.1);
            transform: translateY(-5px);
        }

        .draft__title {
            font-size: 1.2rem;
            color: #6f6af8;
            font-weight: 600;
            margin-bottom: 1rem;
        }

        .draft__title a {
            text-decoration: none;
            color: #6f6af8;
            transition: color 0.3s ease;
        }

        .draft__title a:hover {
            color: #5854c7;
        }

        .draft__category {
            background: var(--color-primary-light);
            color: var(--color-primary);
            display: inline-block;
            padding: 0.5rem 1rem;
            border-radius: var(--card-border-radius-2);
            font-weight: 600;
            font-size: 0.8rem;
            text-align: center
        }

        .draft__category:hover {
            background-color: #5854c7;
            transform: translateY(-2px);
        }

        .draft__content {
            font-size: 1rem;
            color: #6f6af8;
            margin-bottom: 1rem;
        }

        .draft__actions {
            display: flex;
            gap: 1rem;
        }

        .draft__actions .btn {
            background-color: #6f6af8;
            color: white;
            border: none;
            padding: 0.75rem 1.25rem;
            border-radius: 8px;
            cursor: pointer;
            transition: background-color 0.3s ease, transform 0.3s ease;
            font-size: 1rem;
            text-align: center;
        }

        .draft__actions .btn:hover {
            background-color: #5854c7;
            transform: translateY(-2px);
        }

        .draft__actions .btn.danger {
            background-color: #e74c3c;
        }

        .draft__actions .btn.danger:hover {
            background-color: #c0392b;
            transform: translateY(-2px);
        }

        @media (max-width: 768px) {
            main {
                padding: 1rem;
            }

            .draft__actions {
                flex-direction: column;
            }

            .draft__actions .btn {
                width: 100%;
                text-align: center;
            }
        }
        .dashboard__container {
            display: grid;
            grid-template-columns: auto 14rem; /* Changed the order to place the sidebar on the right */
            gap: 1rem;
            background: #c0c0e8;
            padding: 0.8rem;
            margin-bottom: 5rem;
        }

        /* Adjustments for the main container */
        main {
            order: 1; /* Ensure the main content comes first */
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


    </style>
</head>
<body>
<?php include 'partials/header.php'; ?>


<main>
    <h2>Manage Drafts</h2>
    <div class="drafts__container">
        <?php if (empty($drafts)): ?>
            <p>No drafts available.</p>
        <?php else: ?>
            <?php foreach ($drafts as $draft): ?>
                <div class="draft__card">
                    <a href="<?= ROOT_URL ?>category-posts.php?id=<?= $draft['category_id'] ?>" class="draft__category"><?= htmlspecialchars($draft['category_name'], ENT_QUOTES) ?></a>
                    <h3 class="draft__title">
                        <a href="<?= ROOT_URL ?>edit-draft.php?id=<?= $draft['id'] ?>"><?= htmlspecialchars($draft['title'], ENT_QUOTES) ?></a>
                    </h3>
                    <p class="draft__content">
                        <?php
                        // Remove images from content for preview
                        $content_preview = strip_tags($draft['content']);
                        $content_preview = preg_replace('/<img[^>]+\>/i', '', $content_preview);
                        echo htmlspecialchars_decode(substr($content_preview, 0, 150), ENT_QUOTES);
                        ?>...
                    </p>
                    <div class="draft__actions">
                        <a href="edit-draft.php?id=<?= $draft['id'] ?>" class="btn sm">Edit</a>
                        <a href="delete-draft.php?id=<?= $draft['id'] ?>" class="btn sm danger">Delete</a>
                    </div>
                </div>
            <?php endforeach ?>
        <?php endif; ?>
    </div>
</main>



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
</script>
</body>
</html>
