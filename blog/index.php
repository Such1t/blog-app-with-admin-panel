<?php
global $pdo;
require 'config/constants.php';
require 'config/database.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$isAdmin = isset($_SESSION['user_is_admin']) && $_SESSION['user_is_admin'];

include $isAdmin ? 'admin/partials/header.php' : 'partials/header.php';

// Pagination variables
$postsPerPage = 4;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $postsPerPage;

// Fetch all featured posts from the database
$featured_query = "
    SELECT blog_posts.*, categories.name AS category_name, users.username AS author_username, users.avatar_url AS author_avatar_url
    FROM blog_posts
    JOIN categories ON blog_posts.category_id = categories.id
    JOIN users ON blog_posts.user_id = users.id
    WHERE blog_posts.is_featured = 1 AND blog_posts.status = 'published'
    LIMIT 2
";
$featured_result = $pdo->query($featured_query);
$featured_posts = $featured_result->fetchAll(PDO::FETCH_ASSOC);

// Fetch popular posts (based on the number of likes and comments)
$popular_query = "
    SELECT 
        blog_posts.*, 
        categories.name AS category_name, 
        users.username AS author_username, 
        users.avatar_url AS author_avatar_url,
        (SELECT COUNT(*) FROM likes WHERE likes.post_id = blog_posts.id) 
        + (SELECT COUNT(*) FROM comments WHERE comments.post_id = blog_posts.id) AS popularity_score
    FROM 
        blog_posts
    JOIN 
        categories ON blog_posts.category_id = categories.id
    JOIN 
        users ON blog_posts.user_id = users.id
    WHERE 
        blog_posts.status = 'published'
    ORDER BY 
        popularity_score DESC
    LIMIT 2
";
$popular_result = $pdo->query($popular_query);
$popular_posts = $popular_result->fetchAll(PDO::FETCH_ASSOC);

// Fetch posts from blog_posts table, excluding the featured and popular posts
$query = "
    SELECT blog_posts.*, categories.name AS category_name, users.username AS author_username, users.avatar_url AS author_avatar_url
    FROM blog_posts
    JOIN categories ON blog_posts.category_id = categories.id
    JOIN users ON blog_posts.user_id = users.id
    WHERE blog_posts.is_featured = 0 AND blog_posts.status = 'published'
    ORDER BY blog_posts.created_at DESC
    LIMIT :limit OFFSET :offset
";
$stmt = $pdo->prepare($query);
$stmt->bindParam(':limit', $postsPerPage, PDO::PARAM_INT);
$stmt->bindParam(':offset', $offset, PDO::PARAM_INT);
$stmt->execute();
$posts = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Fetch total number of posts for pagination
$total_query = "
    SELECT COUNT(*) AS total_posts
    FROM blog_posts
    WHERE is_featured = 0 AND status = 'published'
";
$total_posts = $pdo->query($total_query)->fetch(PDO::FETCH_ASSOC)['total_posts'];
$total_pages = ceil($total_posts / $postsPerPage);

// Fetch posts from users that the logged-in user is following
$followed_posts = [];
if (isset($_SESSION['user-id'])) {
    $user_id = $_SESSION['user-id'];

    $followed_query = "
        SELECT blog_posts.*, categories.name AS category_name, users.username AS author_username, users.avatar_url AS author_avatar_url
        FROM blog_posts
        JOIN categories ON blog_posts.category_id = categories.id
        JOIN users ON blog_posts.user_id = users.id
        JOIN followers ON blog_posts.user_id = followers.followed_id
        WHERE followers.follower_id = :user_id AND blog_posts.status = 'published'
        ORDER BY blog_posts.created_at DESC
        LIMIT :limit OFFSET :offset
    ";
    $followed_stmt = $pdo->prepare($followed_query);
    $followed_stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
    $followed_stmt->bindParam(':limit', $postsPerPage, PDO::PARAM_INT);
    $followed_stmt->bindParam(':offset', $offset, PDO::PARAM_INT);
    $followed_stmt->execute();
    $followed_posts = $followed_stmt->fetchAll(PDO::FETCH_ASSOC);
}
?>

<?php if (isset($_SESSION['edit-post-success']) || isset($_SESSION['edit-draft-success'])): ?>
    <div id="success-popup" class="success-popup">
        <p><?= htmlspecialchars($_SESSION['edit-post-success'] ?? $_SESSION['edit-draft-success'], ENT_QUOTES) ?></p>
    </div>
    <?php unset($_SESSION['edit-post-success'], $_SESSION['edit-draft-success']); ?>
<?php endif; ?>

<section class="featured">
    <h2 class="section__title">Featured & Popular Posts</h2>
    <div class="container featured__container">
        <div class="featured-posts">
            <h3 class="section__subtitle">Featured Posts</h3>
            <?php if (!empty($featured_posts)): ?>
                <?php foreach ($featured_posts as $featured): ?>
                    <article class="post featured__post">
                        <div class="post__thumbnail">
                            <a href="<?= ROOT_URL ?>post.php?id=<?= $featured['id'] ?>">
                                <img src="<?= ROOT_URL ?>images/<?= htmlspecialchars($featured['image_url'], ENT_QUOTES) ?>" alt="<?= htmlspecialchars($featured['title'], ENT_QUOTES) ?>" loading="lazy">
                            </a>
                        </div>
                        <div class="post__info">
                            <a href="<?= ROOT_URL ?>category-posts.php?id=<?= $featured['category_id'] ?>" class="category__button"><?= htmlspecialchars($featured['category_name'], ENT_QUOTES) ?></a>
                            <h2 class="post__title"><a href="<?= ROOT_URL ?>post.php?id=<?= $featured['id'] ?>"><?= htmlspecialchars($featured['title'], ENT_QUOTES) ?></a></h2>

                            <div class="post__author">
                                <div class="post__author-avatar">
                                    <a href="<?= ROOT_URL ?>profile.php?user_id=<?= $featured['user_id'] ?>">
                                        <img src="<?= ROOT_URL ?>images/<?= htmlspecialchars($featured['author_avatar_url'], ENT_QUOTES) ?>" alt="<?= htmlspecialchars($featured['author_username'], ENT_QUOTES) ?>" loading="lazy">
                                    </a>
                                </div>
                                <div class="post__author-info">
                                    <h5>By: <a href="<?= ROOT_URL ?>profile.php?user_id=<?= $featured['user_id'] ?>"><?= htmlspecialchars($featured['author_username'], ENT_QUOTES) ?></a></h5>
                                    <small><?= date("M d, Y - H:i", strtotime($featured['created_at'])) ?></small>
                                </div>
                            </div>

                            <div class="featured-badge">
                                <span>Featured Post</span>
                            </div>
                        </div>
                    </article>
                <?php endforeach; ?>
            <?php else: ?>
                <p>No featured posts found.</p>
            <?php endif; ?>
        </div>

        <div class="popular-posts">
            <h3 class="section__subtitle">Popular Posts</h3>
            <?php if (!empty($popular_posts)): ?>
                <?php foreach ($popular_posts as $popular): ?>
                    <article class="post popular__post">
                        <div class="post__thumbnail">
                            <a href="<?= ROOT_URL ?>post.php?id=<?= $popular['id'] ?>">
                                <img src="<?= ROOT_URL ?>images/<?= htmlspecialchars($popular['image_url'], ENT_QUOTES) ?>" alt="<?= htmlspecialchars($popular['title'], ENT_QUOTES) ?>" loading="lazy">
                            </a>
                        </div>
                        <div class="post__info">
                            <a href="<?= ROOT_URL ?>category-posts.php?id=<?= $popular['category_id'] ?>" class="category__button"><?= htmlspecialchars($popular['category_name'], ENT_QUOTES) ?></a>
                            <h2 class="post__title"><a href="<?= ROOT_URL ?>post.php?id=<?= $popular['id'] ?>"><?= htmlspecialchars($popular['title'], ENT_QUOTES) ?></a></h2>

                            <div class="post__author">
                                <div class="post__author-avatar">
                                    <a href="<?= ROOT_URL ?>profile.php?user_id=<?= $popular['user_id'] ?>">
                                        <img src="<?= ROOT_URL ?>images/<?= htmlspecialchars($popular['author_avatar_url'], ENT_QUOTES) ?>" alt="<?= htmlspecialchars($popular['author_username'], ENT_QUOTES) ?>" loading="lazy">
                                    </a>
                                </div>
                                <div class="post__author-info">
                                    <h5>By: <a href="<?= ROOT_URL ?>profile.php?user_id=<?= $popular['user_id'] ?>"><?= htmlspecialchars($popular['author_username'], ENT_QUOTES) ?></a></h5>
                                    <small><?= date("M d, Y - H:i", strtotime($popular['created_at'])) ?></small>
                                </div>
                            </div>

                            <div class="popular-badge">
                                <span>Popular Post</span>
                            </div>
                        </div>
                    </article>
                <?php endforeach; ?>
            <?php else: ?>
                <p>No popular posts found.</p>
            <?php endif; ?>
        </div>
    </div>
</section>

<?php if (isset($_SESSION['user-id'])): ?>
    <section class="followed-posts">
        <h2 class="section__title">Posts from People You Follow</h2>
        <div class="container posts__container">
            <?php if (!empty($followed_posts)): ?>
                <?php foreach ($followed_posts as $post): ?>
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
                <?php endforeach; ?>
            <?php else: ?>
                <p>You are not following anyone or they haven't posted yet.</p>
            <?php endif; ?>
        </div>
    </section>
<?php endif; ?>

<section class="posts <?= !empty($featured_posts) ? '' : 'section__extra-margin' ?>">
    <h2 class="section__title">Recent Posts</h2>
    <div class="container posts__container">
        <?php if (!empty($posts)): ?>
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
            <?php endforeach; ?>
        <?php else: ?>
            <p>No posts found.</p>
        <?php endif; ?>
    </div>

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
</section>

<section class="category__buttons">
    <div class="container category__buttons-container">
        <?php
        $all_categories_query = "SELECT * FROM categories";
        $all_categories = $pdo->query($all_categories_query)->fetchAll(PDO::FETCH_ASSOC);
        ?>
        <?php foreach ($all_categories as $category): ?>
            <a href="<?= ROOT_URL ?>category-posts.php?id=<?= $category['id'] ?>" class="category__button"><?= htmlspecialchars($category['name'], ENT_QUOTES) ?></a>
        <?php endforeach; ?>
    </div>
</section>

<?php include 'partials/footer.php'; ?>

<style>
    body {
        font-family: 'Montserrat', sans-serif;
        background-color: #f4f4f8;
        margin: 0;
        padding: 0;
        line-height: 1.6;
    }

    /* Section Title with Enhanced Gradient Effect */
    .section__title {
        font-size: 2.2rem;
        background: linear-gradient(45deg, #6f6af8, #5854c7, #9a98f2);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        margin-bottom: 2rem;
        text-align: left;
        padding-left: 5%;
        font-weight: bold;
        display: inline-block;
    }

    .section__subtitle {
        font-size: 1.8rem;
        margin-bottom: 1rem;
        padding-left: 5%;
        font-weight: bold;
        color: #6f6af8;
    }

    .posts__container {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
        gap: 1.5rem;
        width: 90%;
        margin: 0 auto;
    }

    .posts__container p {
        color: #6f6af8;
    }

    .post {
        background: #ffffff;
        border-radius: 12px;
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
        border-bottom: 3px solid #6f6af8;
    }

    .post__info {
        padding: 1.5rem;
        color: #3e3d56;
    }

    .post__title {
        font-size: 1.4rem;
        color: #6f6af8;
        margin-bottom: 0.5rem;
    }

    .post__title a {
        color: #6f6af8;
        text-decoration: none;
        font-weight: bold;
        transition: color 0.3s ease;
    }

    .post__title a:hover {
        color: #5854c7;
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
        background: var(--color-primary-light, #e0e0ff);
        color: var(--color-primary, #6f6af8);
        display: inline-block;
        padding: 0.5rem 1rem;
        border-radius: 8px;
        font-weight: 600;
        font-size: 0.8rem;
        text-align: center;
        text-decoration: none;
    }

    .category__button:hover {
        background: #5854c7;
        color: #ffffff;
    }

    .featured__container {
        display: flex;
        flex-direction: row;
        gap: 1.5rem;
        width: 90%;
        max-width: 1200px;
        margin: 0 auto;
        flex-wrap: wrap;
        border-bottom: 3px solid #6f6af8;
        padding-bottom: 2rem;
        border-right: 5px solid transparent;
    }

    .featured-posts {
        flex: 1;
        background-color: #f4f4f8; /* Light background color for contrast */
        padding: 1.5rem;
        border-radius: 8px; /* Rounded corners */
    }

    .popular-posts {
        flex: 1;
        background-color: #f4f4f8; /* Light background color for contrast */
        padding: 1.5rem;
        border-radius: 8px; /* Rounded corners */
    }

    .featured__post,
    .popular__post {
        display: flex;
        align-items: stretch;
        background: #f2f2f2;
        border-radius: 16px;
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        overflow: hidden;
        transition: transform 0.3s ease, box-shadow 0.3s ease;
        width: 100%;
        max-width: 100%;
        height: 250px;
        margin-bottom: 1.5rem;
        border: 2px solid #6f6af8;
    }

    .featured__post:hover,
    .popular__post:hover {
        transform: translateY(-5px);
        box-shadow: 0 6px 15px rgba(0, 0, 0, 0.15);
    }

    .featured__post .post__thumbnail,
    .popular__post .post__thumbnail {
        width: 50%;
        height: 100%;
        overflow: hidden;
        border-top-left-radius: 16px;
        border-bottom-left-radius: 16px;
    }

    .featured__post .post__thumbnail img,
    .popular__post .post__thumbnail img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        border-bottom: none;
    }

    .featured__post .post__info,
    .popular__post .post__info {
        width: 50%;
        padding: 1rem;
        color: #3e3d56;
        box-sizing: border-box;
        height: 100%;
        vertical-align: top;
    }

    .featured__post .post__title,
    .popular__post .post__title {
        font-size: 1.5rem;
        color: #6f6af8;
        margin-bottom: 0.5rem;
        font-weight: bold;
    }

    .featured-badge,
    .popular-badge {
        background-color: #6f6af8;
        color: white;
        padding: 0.4rem 0.7rem;
        border-radius: 12px;
        font-weight: bold;
        margin-top: 1rem;
        display: inline-block;
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

    .section__title {
        color: #6f6af8;
    }

    .section__extra-margin {
        margin-top: 2rem;
    }

    /* Success Popup */
    .success-popup {
        position: fixed;
        top: 20px;
        right: 20px;
        background: linear-gradient(135deg, #28a745, #28a745, #28a745);
        color: #fff;
        padding: 10px 20px;
        border-radius: 8px;
        font-size: 1rem;
        z-index: 1000;
        opacity: 0;
        transform: translateX(100%);
        transition: opacity 0.5s ease, transform 0.5s ease;
    }

    .success-popup.show {
        opacity: 1;
        transform: translateX(0);
    }
</style>

<script>
    // Show and hide the success popup
    document.addEventListener('DOMContentLoaded', function () {
        const successPopup = document.getElementById('success-popup');
        if (successPopup) {
            successPopup.classList.add('show');
            setTimeout(function () {
                successPopup.classList.remove('show');
            }, 3000); // Show for 3 seconds
        }
    });
</script>
