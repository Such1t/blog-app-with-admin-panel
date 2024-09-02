<?php
require 'config/constants.php';
require 'config/database.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$isAdmin = isset($_SESSION['user_is_admin']) && $_SESSION['user_is_admin'];

include 'partials/header.php';

// Fetch all posts initially
$sql = "SELECT blog_posts.*, categories.name AS category_name, users.username AS author_username, users.avatar_url AS author_avatar_url
        FROM blog_posts
        JOIN categories ON blog_posts.category_id = categories.id
        JOIN users ON blog_posts.user_id = users.id
        WHERE status='published'
        ORDER BY blog_posts.created_at DESC";
$stmt = $pdo->query($sql);
$posts = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Display the modal only if no search has been performed
if (!isset($_SESSION['search_option_selected'])) {
    $_SESSION['search_option_selected'] = false;
}
?>

<section class="search__bar">
    <form class="container search__bar-container" action="search.php" method="GET" id="search-form">
        <div style="position: relative;">
            <i class="uil uil-search"></i>
            <input type="search" name="search" placeholder="Search" id="search-input" autocomplete="off">
            <input type="hidden" name="search_type" id="search_type" value="general">
            <div id="dropdown" class="dropdown-content">
                <div class="dropdown-item" data-type="general"><i class="uil uil-search"></i> General</div>
                <div class="dropdown-item" data-type="title"><i class="uil uil-book-open"></i> Title</div>
                <div class="dropdown-item" data-type="content"><i class="uil uil-align-left"></i> Content</div>
                <div class="dropdown-item" data-type="categories"><i class="uil uil-list-ul"></i> Categories</div>
                <div class="dropdown-item" data-type="users"><i class="uil uil-user"></i> Users</div>
            </div>
        </div>
        <button type="submit" name="submit" class="btn">Go</button>
    </form>
</section>
<!--====================== END OF SEARCH ====================-->

<section class="posts">
    <div class="container posts__container">
        <?php if (!empty($posts)): ?>
            <?php foreach ($posts as $post): ?>
                <article class="post">
                    <div class="post__thumbnail">
                        <a href="post.php?id=<?= $post['id'] ?>">
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
                                <img src="<?= ROOT_URL ?>images/<?= htmlspecialchars($post['author_avatar_url'], ENT_QUOTES) ?>" alt="<?= htmlspecialchars($post['author_username'], ENT_QUOTES) ?>">
                            </div>
                            <div class="post__author-info">
                                <h5>By: <?= htmlspecialchars($post['author_username'], ENT_QUOTES) ?></h5>
                                <small><?= date("M d, Y - H:i", strtotime($post['created_at'])) ?></small>
                            </div>
                        </div>
                    </div>
                </article>
            <?php endforeach ?>
        <?php else: ?>
            <p>No posts found.</p>
        <?php endif ?>
    </div>
</section>
<!--====================== END OF POSTS ====================-->

<section class="category__buttons">
    <div class="container category__buttons-container">
        <?php
        $all_categories_query = "SELECT * FROM categories";
        $all_categories_stmt = $pdo->query($all_categories_query);
        $all_categories = $all_categories_stmt->fetchAll(PDO::FETCH_ASSOC);
        ?>
        <?php foreach ($all_categories as $category): ?>
            <a href="category-posts.php?id=<?= $category['id'] ?>" class="category__button"><?= htmlspecialchars($category['name'], ENT_QUOTES) ?></a>
        <?php endforeach ?>
    </div>
</section>
<!--====================== END OF CATEGORY BUTTONS ====================-->

<?php include 'partials/footer.php'; ?>

<style>
    .posts__container {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
        gap: 1.5rem;
        width: 100%;
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
        height: 200px;
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
        font-weight: bold;
    }

    .post__author-info h5,
    .post__author-info small {
        margin: 0;
        color: #6f6af8;
    }

    .post__author-avatar img {
        width: 40px;
        height: 40px;
        border-radius: 50%;
        margin-right: 0.5rem;
    }

    .category__button {
        background: var(--color-primary-light);
        color: var(--color-primary);
        display: inline-block;
        padding: 0.5rem 1.2rem;
        border-radius: 5px;
        font-weight: bold;
        font-size: 0.9rem;
        text-align: center;
        border: 2px solid var(--color-primary);
        transition: all 0.3s ease;
    }

    .category__button:hover {
        color: #ffffff;
        background: var(--color-primary);
    }

    .search__bar {
        margin-top: 3rem;
    }

    .search__bar-container {
        position: relative;
        width: 30rem;
        display: flex;
        flex-direction: row;
        align-items: center;
        justify-content: space-between;
        overflow: visible;
        padding: 0.6rem 1rem;
        border-radius: var(--card-border-radius-2);
        color:#6f6af8;
        background-color: white;
    }

    .search__bar-container > div {
        width: 100%;
        display: flex;
        align-items: center;
    }

    .search__bar input {
        background: transparent;
        margin-left: 0.7rem;
        padding: 0.5rem 0;
        width: 100%;
        color:#6f6af8;
    }

    .search__bar input::placeholder {
        color: var(--color-gray-900);
    }

    #dropdown {
        position: absolute;
        top: calc(100% + 5px);
        left: 0;
        background-color:white;
        border: 1px solid #ccc;
        width: 100%;
        max-width: 400px;
        z-index: 1002;
        color:black;
        border-radius: 8px;
        padding: 8px 0;
        visibility: hidden;
        opacity: 0;
        transition: all 0.3s ease;
        transform: translateY(-10px);
    }

    .search__bar input:focus + #dropdown {
        visibility: visible;
        opacity: 1;
        transform: translateY(0);
    }

    .dropdown-item {
        padding: 10px 15px;
        cursor: pointer;
        font-size: 0.9rem;
        color:#6f6af8;
        display: flex;
        align-items: center;
    }

    .dropdown-item i {
        color: #6f6af8;
        margin-right: 10px;
    }

    .dropdown-item:hover {
        background-color: #f0f0f0;
        color: var(--color-primary);
    }
</style>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const searchBar = document.getElementById('search-input');
        const dropdown = document.getElementById('dropdown');
        const searchTypeSelect = document.getElementById('search_type');
        const searchForm = document.getElementById('search-form');

        // Show the dropdown when the search bar is focused
        searchBar.addEventListener('focus', function () {
            dropdown.style.visibility = 'visible';
            dropdown.style.opacity = '1';
            dropdown.style.transform = 'translateY(0)';
        });

        // Hide the dropdown when clicking outside
        document.addEventListener('click', function (event) {
            if (!dropdown.contains(event.target) && event.target !== searchBar) {
                dropdown.style.visibility = 'hidden';
                dropdown.style.opacity = '0';
                dropdown.style.transform = 'translateY(-10px)';
            }
        });

        // Hide the dropdown when an option is clicked
        dropdown.addEventListener('click', function (event) {
            if (event.target.classList.contains('dropdown-item')) {
                const searchType = event.target.getAttribute('data-type');
                searchTypeSelect.value = searchType;
                searchBar.placeholder = 'Search ' + searchType.charAt(0).toUpperCase() + searchType.slice(1);
                dropdown.style.visibility = 'hidden';
                dropdown.style.opacity = '0';
                dropdown.style.transform = 'translateY(-10px)';
                searchBar.focus();
            }
        });

        // Submit form and reset dropdown visibility status
        searchForm.addEventListener('submit', function () {
            dropdown.style.visibility = 'hidden';
            dropdown.style.opacity = '0';
            dropdown.style.transform = 'translateY(-10px)';
        });
    });
</script>
