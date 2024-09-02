<?php
require 'config/database.php';
include 'partials/header.php';

// Fetch posts if id is set
if (isset($_GET['id'])) {
    $id = filter_var($_GET['id'], FILTER_SANITIZE_NUMBER_INT);
    $query = "SELECT * FROM blog_posts WHERE category_id = :id AND status='published' ORDER BY created_at DESC";
    $stmt = $pdo->prepare($query);
    $stmt->execute(['id' => $id]);
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

<?php if (count($posts) > 0) : ?>
    <section class="posts">
        <div class="container posts__container">
            <?php foreach ($posts as $post) : ?>
                <article class="post">
                    <div class="post__thumbnail">
                        <a href="<?= ROOT_URL ?>post.php?id=<?= $post['id'] ?>">
                            <img src="<?= ROOT_URL ?>images/<?= htmlspecialchars($post['image_url'], ENT_QUOTES, 'UTF-8') ?>">
                        </a>
                    </div>
                    <div class="post__info">
                        <?php
                        // Fetch category from categories table using category_id of post
                        $category_query = "SELECT * FROM categories WHERE id = ?";
                        $category_stmt = $pdo->prepare($category_query);
                        $category_stmt->execute([$post['category_id']]);
                        $category = $category_stmt->fetch(PDO::FETCH_ASSOC);
                        ?>
                        <a href="<?= ROOT_URL ?>category-posts.php?id=<?= $post['category_id'] ?>" class="category__button"><?= htmlspecialchars($category['name'], ENT_QUOTES) ?></a>
                        <h3 class="post__title">
                            <a href="<?= ROOT_URL ?>post.php?id=<?= $post['id'] ?>"><?= htmlspecialchars($post['title'], ENT_QUOTES, 'UTF-8') ?></a>
                        </h3>
                        <p class="post__body">
                            <?php
                            // Remove all HTML tags, including images, from the content preview and limit to 15 characters
                            $clean_content = strip_tags($post['content']);
                            echo htmlspecialchars_decode(substr($clean_content, 0, 15)) . '...';
                            ?>
                        </p>
                        <div class="post__author">
                            <?php
                            // Fetch author from users table using author_id
                            $author_query = "SELECT * FROM users WHERE id = :author_id";
                            $stmt = $pdo->prepare($author_query);
                            $stmt->execute(['author_id' => $post['user_id']]);
                            $author = $stmt->fetch(PDO::FETCH_ASSOC);
                            ?>
                            <div class="post__author-avatar">
                                <img src="<?= ROOT_URL ?>images/<?= htmlspecialchars($author['avatar_url'], ENT_QUOTES, 'UTF-8') ?>">
                            </div>
                            <div class="post__author-info">
                                <h5>By: <?= htmlspecialchars($author['username'], ENT_QUOTES, 'UTF-8') ?></h5>
                                <small>
                                    <?= date("M d, Y - H:i", strtotime($post['created_at'])) ?>
                                </small>
                            </div>
                        </div>
                        <div class="post__icons">
                            <div class="icon-container">
                                <i class="uil uil-thumbs-up"></i>
                                <?php
                                // Fetch likes count
                                $like_query = "SELECT COUNT(*) as like_count FROM likes WHERE post_id=?";
                                $like_stmt = $pdo->prepare($like_query);
                                $like_stmt->execute([$post['id']]);
                                $like_count = $like_stmt->fetch(PDO::FETCH_ASSOC)['like_count'];
                                ?>
                                <span class="like-count"><?= $like_count ?></span>
                            </div>
                            <div class="icon-container">
                                <i class="uil uil-comments"></i>
                                <?php
                                // Fetch comments count
                                $comment_query = "SELECT COUNT(*) as comment_count FROM comments WHERE post_id=?";
                                $comment_stmt = $pdo->prepare($comment_query);
                                $comment_stmt->execute([$post['id']]);
                                $comment_count = $comment_stmt->fetch(PDO::FETCH_ASSOC)['comment_count'];
                                ?>
                                <span class="comment-count"><?= $comment_count ?></span>
                            </div>
                        </div>
                    </div>
                </article>
            <?php endforeach ?>
        </div>
    </section>
<?php else : ?>
    <div class="alert__message error lg">
        <p>No posts found for this category</p>
    </div>
<?php endif ?>
<!--====================== END OF POSTS ====================-->

<section class="category__buttons">
    <div class="container category__buttons-container">
        <?php
        $all_categories_query = "SELECT * FROM categories";
        $all_categories_stmt = $pdo->prepare($all_categories_query);
        $all_categories_stmt->execute();
        $all_categories = $all_categories_stmt->fetchAll(PDO::FETCH_ASSOC);
        ?>
        <?php foreach ($all_categories as $category) : ?>
            <a href="<?= ROOT_URL ?>category-posts.php?id=<?= $category['id'] ?>" class="category__button"><?= htmlspecialchars($category['name'], ENT_QUOTES, 'UTF-8') ?></a>
        <?php endforeach ?>
    </div>
</section>
<!--====================== END OF CATEGORY BUTTONS ====================-->

<?php
include 'partials/footer.php';
?>

<style>
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
    .comment-count {
        font-size: 1rem;
        color: var(--color-primary);
    }

    /* Apply index.php styles to the post section */
    .post {
        border: 1px solid rgba(0, 0, 0, 0.1); /* Light border for individual post */
        padding: 1rem;
        border-radius: var(--card-border-radius-2);
        background-color: #f9f9f9; /* Light background color for contrast */
    }

    .post__thumbnail {
        border-radius: var(--card-border-radius-2);
        overflow: hidden;
        margin-bottom: 1.6rem;
        height: 250px; /* Fixed height */
    }

    .post__thumbnail img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }

    .post__info {
        margin-bottom: 1rem;
        color: #333333;
    }

    .post__title a {
        color: #6f6af8;
    }

    .posts__container {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
        gap: 2rem;
        margin-bottom: 5rem;
    }

    .category__title {
        background-color: #6f6af8;
        padding: 1rem 2rem;
        color: white;
        margin-top: 4rem;
    }

    .category__title h1 {
        margin: -10; /* Remove default margin to get the title closer to the navbar */
        font-size: 2rem;
        text-align: center;
        color: white;
    }

    .post__author-info h5 {
        color: #6f6af8;
    }
</style>
