<?php
require 'config/constants.php';
require 'config/database.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$isAdmin = isset($_SESSION['user_is_admin']) && $_SESSION['user_is_admin'];

include 'partials/header.php';

// Pagination variables
$postsPerPage = 12;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $postsPerPage;

// Fetch posts from blog_posts table with pagination
$sql = "SELECT blog_posts.*, categories.name AS category_name, users.username AS author_username, users.avatar_url AS author_avatar_url
        FROM blog_posts
        JOIN categories ON blog_posts.category_id = categories.id
        JOIN users ON blog_posts.user_id = users.id
        WHERE status='published'
        ORDER BY blog_posts.created_at DESC
        LIMIT :limit OFFSET :offset";
$stmt = $pdo->prepare($sql);
$stmt->bindParam(':limit', $postsPerPage, PDO::PARAM_INT);
$stmt->bindParam(':offset', $offset, PDO::PARAM_INT);
$stmt->execute();
$posts = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Fetch total number of posts for pagination
$total_query = "SELECT COUNT(*) AS total_posts FROM blog_posts WHERE status='published'";
$total_posts = $pdo->query($total_query)->fetch(PDO::FETCH_ASSOC)['total_posts'];
$total_pages = ceil($total_posts / $postsPerPage);
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
<!--====================== END OF PAGINATION ====================-->

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
        width: 90%;
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
        padding: 0.5rem 1rem;
        border-radius: var(--card-border-radius-2);
        font-weight: 600;
        font-size: 0.8rem;
        text-align: center;
        border-top: var(--color-primary);
    }

    .category__button:hover {
        color: var(--color-white);
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
        color:#a0a0a0 ;
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
    }

    .dropdown-item:hover {
        background-color: #f0f0f0;
        color: var(--color-primary);
    }

    .post__icons {
        display: flex;
        align-items: center;
        justify-content: flex-start;
        gap: 1.5rem;
        margin-top: 1rem;
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
    .comment-count {
        font-size: 1rem;
        color: var(--color-primary);
    }

    .modal {
        display: none;
        position: fixed;
        z-index: 1000;
        left: 0;
        top: 0;
        width: 100%;
        height: 100%;
        overflow: auto;
        background-color: rgba(0, 0, 0, 0.5);
        padding-top: 60px;
    }

    .modal-content {
        background-color: #fefefe;
        margin: 5% auto;
        padding: 20px;
        border: 1px solid #888;
        width: 80%;
        max-width: 500px;
        border-radius: 10px;
    }

    .modal-content .dropdown-item {
        padding: 10px 20px;
        margin: 5px 0;
        background-color: #fff;
        color: var(--color-primary);
        border: 1px solid var(--color-primary);
        border-radius: 5px;
        cursor: pointer;
    }

    .modal-content .dropdown-item:hover {
        background-color: var(--color-primary);
        color: #fff;
    }

    .close {
        color: #aaa;
        float: right;
        font-size: 28px;
        font-weight: bold;
    }

    .close:hover,
    .close:focus {
        color: black;
        text-decoration: none;
        cursor: pointer;
    }

    .post__title h3,
    .post__title a {
        color: var(--color-primary);
    }

    .pagination {
        display: flex;
        justify-content: center;
        margin-top: 2rem;
        gap: 0.5rem;
    }

    .pagination__link {
        padding: 0.5rem 1rem;
        background: #6f6af8;
        color: white;
        border-radius: 5px;
        text-decoration: none;
        font-weight: bold;
    }

    .pagination__link.active {
        background: #5854c7;
        font-weight: bold;
    }

    .pagination__link:hover {
        background: #5854c7;
    }

    .featured {
        margin-top: 0;
        background-color: #f9f9f9;
        padding: 2rem;
        border-radius: 16px;
        margin-bottom: 2rem;
    }

    .section__extra-margin {
        margin-top: 2rem;
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