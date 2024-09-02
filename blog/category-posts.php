<?php
require 'config/constants.php';
require 'config/database.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$isAdmin = isset($_SESSION['user_is_admin']) && $_SESSION['user_is_admin'];

include 'partials/header.php';

// Determine the current page number
$page = isset($_GET['page']) && is_numeric($_GET['page']) ? (int) $_GET['page'] : 1;
$limit = 12;
$offset = ($page - 1) * $limit;

// Fetch posts if id is set
if (isset($_GET['id'])) {
    $id = filter_var($_GET['id'], FILTER_SANITIZE_NUMBER_INT);
    $total_posts_query = "SELECT COUNT(*) as total FROM blog_posts WHERE category_id = :id AND status='published'";
    $stmt = $pdo->prepare($total_posts_query);
    $stmt->execute(['id' => $id]);
    $total_posts = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
    $total_pages = ceil($total_posts / $limit);

    $query = "SELECT blog_posts.*, categories.name AS category_name, users.username AS author_username, users.avatar_url AS author_avatar_url
              FROM blog_posts
              JOIN categories ON blog_posts.category_id = categories.id
              JOIN users ON blog_posts.user_id = users.id
              WHERE blog_posts.category_id = :id AND status='published'
              ORDER BY created_at DESC
              LIMIT :limit OFFSET :offset";
    $stmt = $pdo->prepare($query);
    $stmt->bindParam(':id', $id, PDO::PARAM_INT);
    $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
    $stmt->bindParam(':offset', $offset, PDO::PARAM_INT);
    $stmt->execute();
    $posts = $stmt->fetchAll(PDO::FETCH_ASSOC);
} else {
    header('Location: ' . ROOT_URL . 'blog.php');
    die();
}
?>

<header class="category__title">
    <h1>
        <?php
        // Fetch category from categories table using category_id of post
        $category_query = "SELECT * FROM categories WHERE id = :id";
        $stmt = $pdo->prepare($category_query);
        $stmt->execute(['id' => $id]);
        $category = $stmt->fetch(PDO::FETCH_ASSOC);
        echo htmlspecialchars($category['name'], ENT_QUOTES, 'UTF-8');
        ?>
    </h1>
</header>
<!--====================== END OF CATEGORY TITLE ====================-->

<section class="posts">
    <div class="container posts__container">
        <?php if (!empty($posts)): ?>
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
<section class="pagination">
    <div class="container pagination__container">
        <?php if ($total_pages > 1): ?>
            <ul>
                <?php if ($page > 1): ?>
                    <li><a href="<?= ROOT_URL ?>category-posts.php?id=<?= $id ?>&page=<?= $page - 1 ?>">Prev</a></li>
                <?php endif; ?>

                <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                    <li><a href="<?= ROOT_URL ?>category-posts.php?id=<?= $id ?>&page=<?= $i ?>" <?= $i == $page ? 'class="active"' : '' ?>><?= $i ?></a></li>
                <?php endfor; ?>

                <?php if ($page < $total_pages): ?>
                    <li><a href="<?= ROOT_URL ?>category-posts.php?id=<?= $id ?>&page=<?= $page + 1 ?>">Next</a></li>
                <?php endif; ?>
            </ul>
        <?php endif; ?>
    </div>
</section>
<!--====================== END OF PAGINATION ====================-->

<section class="category__buttons">
    <div class="container category__buttons-container">
        <?php
        $all_categories_query = "SELECT * FROM categories";
        $all_categories = $pdo->query($all_categories_query)->fetchAll(PDO::FETCH_ASSOC);
        ?>
        <?php foreach ($all_categories as $category): ?>
            <a href="<?= ROOT_URL ?>category-posts.php?id=<?= $category['id'] ?>" class="category__button"><?= htmlspecialchars($category['name'], ENT_QUOTES, 'UTF-8') ?></a>
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
        height: 250px;
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

    .category__title {
        background-color: white;
        padding: 1rem 2rem;
        color:#6f6af8;
        margin-top: 0rem;
        text-align: center;
        border-bottom:5px solid #6f6af8;
        border-top:5px solid #6f6af8;
    }

    .category__title h1 {
        margin: 0;
        font-size: 2rem;
        color:#6f6af8;
    }
    .singlepost__container {
        padding: 1rem;
        border-radius: var(--card-border-radius-2);
        background-color: #fff;
        box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        margin-top: 0rem;
        position: relative;
        max-width: 800px; /* Adjust this value as needed */
        margin-left: auto;
        margin-right: auto;
    }

    .pagination {
        margin: 2rem 0;
        text-align: center;
    }

    .pagination ul {
        list-style-type: none;
        padding: 0;
        margin: 0;
        display: inline-block;
    }

    .pagination ul li {
        display: inline;
        margin-right: 5px;
    }

    .pagination ul li a {
        color: #6f6af8;
        background-color: #fff;
        padding: 8px 12px;
        border-radius: 4px;
        text-decoration: none;
        border: 1px solid #6f6af8;
    }

    .pagination ul li a.active,
    .pagination ul li a:hover {
        background-color: #6f6af8;
        color: #fff;
    }

    .posts__container {
        color: #6f6af8;
    }
</style>
