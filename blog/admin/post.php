<?php

require 'config/constants.php';
require 'config/database.php';
include 'partials/header.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$isAdmin = isset($_SESSION['user_is_admin']) && $_SESSION['user_is_admin'];
$userId = isset($_SESSION['user-id']) ? $_SESSION['user-id'] : null;

if (!$pdo) {
    die("Database connection failed: " . $pdo->errorInfo());
}

if (isset($_GET['id'])) {
    $id = filter_var($_GET['id'], FILTER_SANITIZE_NUMBER_INT);
    $query = "SELECT * FROM blog_posts WHERE id=?";
    $stmt = $pdo->prepare($query);
    $stmt->execute([$id]);
    $post = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$post) {
        header('location: ' . ROOT_URL . 'blog.php');
        die();
    }

    // Fetch the category of the post
    $category_query = "SELECT name FROM categories WHERE id=?";
    $category_stmt = $pdo->prepare($category_query);
    $category_stmt->execute([$post['category_id']]);
    $category = $category_stmt->fetch(PDO::FETCH_ASSOC);

    $comment_query = "
        SELECT comments.*, users.username, 
               (SELECT COUNT(*) FROM comments AS replies WHERE replies.parent_id = comments.id) AS reply_count
        FROM comments 
        JOIN users ON comments.user_id = users.id 
        WHERE post_id = ? 
        ORDER BY created_at DESC 
    ";
    $comment_stmt = $pdo->prepare($comment_query);
    $comment_stmt->execute([$id]);
    $comments = $comment_stmt->fetchAll(PDO::FETCH_ASSOC);

    $like_query = "SELECT COUNT(*) as like_count FROM likes WHERE post_id=?";
    $like_stmt = $pdo->prepare($like_query);
    $like_stmt->execute([$id]);
    $like_count = $like_stmt->fetch(PDO::FETCH_ASSOC)['like_count'];

    $user_like_query = "SELECT * FROM likes WHERE post_id=? AND user_id=?";
    $user_like_stmt = $pdo->prepare($user_like_query);
    $user_like_stmt->execute([$id, $userId]);
    $user_has_liked = $user_like_stmt->fetch(PDO::FETCH_ASSOC);

    $follow_query = "SELECT * FROM followers WHERE follower_id = ? AND followed_id = ?";
    $follow_stmt = $pdo->prepare($ follow_query);
    $follow_stmt->execute([$userId, $post['user_id']]);
    $is_following = $follow_stmt->rowCount() > 0;

    $follower_count_query = "SELECT COUNT(*) as follower_count FROM followers WHERE followed_id = ?";
    $follower_count_stmt = $pdo->prepare($follower_count_query);
    $follower_count_stmt->execute([$post['user_id']]);
    $follower_count = $follower_count_stmt->fetch(PDO::FETCH_ASSOC)['follower_count'];

    // Check if the author has more than one post
    $post_count_query = "SELECT COUNT(*) as post_count FROM blog_posts WHERE user_id=?";
    $post_count_stmt = $pdo->prepare($post_count_query);
    $post_count_stmt->execute([$post['user_id']]);
    $post_count = $post_count_stmt->fetch(PDO::FETCH_ASSOC)['post_count'];

} else {
    header('location: ' . ROOT_URL . 'blog.php');
    die();
}

include 'common_functions.php';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($post['title'], ENT_QUOTES, 'UTF-8') ?></title>
    <link rel="stylesheet" href="<?= ROOT_URL ?>css/style.css">
    <!-- ICONSCOUT CDN -->
    <link rel="stylesheet" href="https://unicons.iconscout.com/release/v4.0.0/css/line.css">
    <!-- GOOGLE FONT (MONTSERRAT) -->
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
</head>

<body>
<style>
    :root {
        --color-primary: #6f6af8;
        --color-primary-light: hsl(242, 91%, 69%, 18%);
        --color-primary-variant: #5854c7;
        --color-red: #da0f3f;
        --color-red-light: hsl(346, 87%, 46%, 15%);
        --color-gray-900: #1e1e66;
        --color-gray-200: rgba(242, 242, 254, 0.7);
        --color-bg: #f9f9f9;
        --color-blue-border: #0000ff;
        --color-blue: #0000ff;
        --color-highlight: #f0f8ff;
        --transition: all 300ms ease;
        --form-width: 40%;
        --card-border-radius-2: 0.5rem;
        --header-height: 60px;
    }

    body {
        font-family: 'Montserrat', sans-serif;
        color: #6f6af8;
        background:#F8F9FA;
        margin: 0;
        padding-top: var(--header-height);
    }

    header {
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: var(--header-height);
        background: var(--color-primary);
        z-index: 1000;
        display: flex;
        align-items: center;
        padding: 0 20px;
        box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
    }

    .content {
        display: flex;
        justify-content: space-between;
        max-width: 1200px;
        margin: 1rem auto;
        padding: 0 10px;
    }

    .singlepost__container {
        width: 70%;
        padding: 1.5rem;
        border-radius: var(--card-border-radius-2);
        background-color: #fff;
        box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        margin-top: 1rem;
        position: relative;
    }

    .singlepost__thumbnail {
        position: relative;
        overflow: hidden;
        border-radius: var(--card-border-radius-2);
        background: var(--color-background);
        box-shadow: 0 3px 6px rgba(0, 0, 0, 0.1);
        max-width: 30%;
        margin: 0 auto 1rem auto;
        transition: transform 0.3s ease-in-out;
    }
    .singlepost__thumbnail img {
        width: 100%;
        height: 100px; /* Fixed height for all thumbnails */
        object-fit: cover; /* Ensures the image covers the entire thumbnail area while maintaining aspect ratio */
        border-radius: var(--card-border-radius-2);
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
        transition: transform 0.3s ease-in-out;
    }

    .post__author-info {
        margin-bottom: 1rem; /* Added margin to create space between author info (including the follow button) and the thumbnail */
    }

    .singlepost__thumbnail img:hover {
        transform: scale(1.05);
    }

    #post-content img {
        display: block;
        width: 100%;
        height: auto;
        max-height: 350px;
        object-fit:cover;
        border-radius: var (--card-border-radius-1);
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
        margin-bottom: 1rem;
    }

    .post__author-avatar img {
        width: 50px;
        height: 50px;
        border-radius: 50%;
    }

    .post__author-info h5,
    .post__author-info small {
        margin: 0;
    }

    .singlepost__container h2 {
        margin-top: 0;
        margin-bottom: 1rem;
        color: var(--color-primary);
    }

    .singlepost__container p {
        line-height: 1.8;
        color: var(--color-primary-variant);
    }

    .icon-container {
        display: flex;
        align-items: center;
        gap: 1rem;
        margin-top: 1rem;
        padding-top: 1rem;
        border-top: 1px solid var(--color-primary-light);
    }

    .icon-container i {
        font-size: 1.5rem;
        color: var(--color-primary);
        cursor: pointer;
    }

    .icon-container i.liked {
        color: var(--color-blue);
    }

    .comment-count,
    .like-count,
    .follow-count {
        font-size: 1rem;
        color: var(--color-primary);
    }

    .reply-link {
        color: var(--color-primary);
        cursor: pointer;
        margin-right: auto;
    }

    .delete-comment {
        color: var(--color-red);
        cursor: pointer;
        background: none;
        border: none;
        padding: 0;
        font: inherit;
        align-self: flex-end;
        margin-left: auto;
        display: inline;
    }

    .delete-comment i {
        font-size: 1.2rem;
        font-family: "Unicons";
    }

    .reply-form-container,
    .comment-reply-container {
        margin-left: 10px;
        margin-top: 10px;
    }

    .reply-form-container {
        border-left: 2px solid var(--color-primary-light);
        padding-left: 10px;
        flex: 1;
    }

    .ajax-reply-form textarea {
        width: 100%;
        padding: 10px;
        border: 1px solid var(--color-primary-light);
        border-radius: var(--card-border-radius-2);
        margin-bottom: 10px;
        background-color: #f8f8ff;
    }

    .ajax-reply-form .btn {
        background-color: var(--color-primary);
        color: white;
        padding: 8px 16px;
        border: none;
        border-radius: var(--card-border-radius-2);
        cursor: pointer;
    }

    .like-animation {
        animation: like-animation 0.5s ease-in-out;
    }

    @keyframes like-animation {
        0% { transform: scale(1); color: #6f6af8; }
        50% { transform: scale(1.5); color: #00008B; }
        100% { transform: scale(1); color: #6f6af8; }
    }

    .follow-btn {
        padding: 0.6rem 1.2rem;
        background-color: var(--color-primary);
        color: white;
        border: none;
        border-radius: var(--card-border-radius-2);
        cursor: pointer;
        transition: background-color 0.3s ease;
    }

    .follow-btn.unfollow-btn {
        background-color: var(--color-red);
    }

    .follow-btn:hover {
        background-color: var(--color-primary-variant);
    }

    .follow-btn.unfollow-btn:hover {
        background-color: #d32f2f;
    }

    .comments__container {
        width: 70%;
        padding: 1.5rem;
        border: 2px solid var(--color-primary-variant);
        background-color: #f9f9f9;
        margin-top: 1rem;
    }

    .add-comment__container {
        margin-bottom: 1rem;
        padding: 1rem;
        border-radius: var(--card-border-radius-2);
        background-color: #ffffff;
        box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
    }

    .comment {
        padding: 1rem;
        border: 1px solid var(--color-primary-light);
        border-radius: var(--card-border-radius-2);
        background-color: #f0f0f8;
        position: relative;
    }

    .comment.post-owner {
        background-color: var(--color-highlight);
        border-left: 5px solid var(--color-primary);
    }

    .comment-reply {
        margin-top: 1rem;
        padding: 0.8rem;
        border: 1px solid var(--color-primary-light);
        border-radius: var(--card-border-radius-2);
        background-color: #e9e9f8;
        margin-left: 1rem;
    }

    .reply-count {
        font-size: 0.9rem;
        color: var(--color-primary);
        margin-left: 10px;
    }

    .related-posts {
        width: 30%;
        margin-top: 1rem;
    }

    .related-posts h3 {
        color: var(--color-primary);
        font-size: 1.5rem;
        margin-bottom: 1rem;
    }

    .related-post-card {
        padding: 1rem;
        border-radius: var(--card-border-radius-2);
        background-color: #ffffff;
        box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        margin-bottom: 1rem;
    }

    .related-post-card h4 {
        margin: 0;
        font-size: 1.1rem;
        color: var(--color-primary);
    }

    .related-post-card p {
        margin: 0.5rem 0;
        color: var (--color-primary-variant);
    }

    .related-post-card img {
        width: 100%;
        height: auto;
        border-radius: var(--card-border-radius-2);
        margin-bottom: 0.5rem;
    }

    .category__button-small {
        background: var(--color-primary-light);
        color: var(--color-primary);
        display: inline-block;
        padding: 0.4rem 1rem;
        border-radius: var(--card-border-radius-2);
        font-weight: 600;
        font-size: 0.8rem;
        text-align: center;
        border-top: var(--color-primary);
    }

    .category__button-small:hover {
        color: var(--color-white);
    }

    .o-navbar {
        background-color: #ffffff;
        padding: 1rem 2rem;
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
        z-index: 1000;
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        display: flex;
        align-items: center;
        justify-content: space-between;
        transition: background-color 0.3s ease, box-shadow 0.3s ease;
    }

    .related-posts__container {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
        gap: 1rem;
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
</style>

<div class="content" data-post-count="<?= $post_count ?>">
    <section class="singlepost">
        <div class="singlepost__container">
            <!-- Display the category above the title -->
            <a href="<?= ROOT_URL ?>category-posts.php?id=<?= $post['category_id'] ?>" class="category__button">
                <?= htmlspecialchars($category['name'], ENT_QUOTES, 'UTF-8') ?>
            </a>
            <h2><?= htmlspecialchars($post['title'], ENT_QUOTES, 'UTF-8') ?></h2>
            <div class="post__author">
                <?php
                $author_id = $post['user_id'];
                $author_query = "SELECT username, avatar_url FROM users WHERE id=?";
                $author_stmt = $pdo->prepare($author_query);
                $author_stmt->execute([$author_id]);
                $author = $author_stmt->fetch(PDO::FETCH_ASSOC);

                $follower_count_query = "SELECT COUNT(*) AS follower_count FROM followers WHERE followed_id = ?";
                $follower_count_stmt = $pdo->prepare($follower_count_query);
                $follower_count_stmt->execute([$author_id]);
                $follower_count = $follower_count_stmt->fetch(PDO::FETCH_ASSOC)['follower_count'];
                ?>
                <div class="post__author-avatar">
                    <a href="<?= ROOT_URL ?>profile.php?user_id=<?= $author_id ?>">
                        <img src="<?= ROOT_URL ?>images/<?= htmlspecialchars($author['avatar_url'], ENT_QUOTES, 'UTF-8') ?>">
                    </a>
                </div>
                <div class="post__author-info">
                    <h5>By: <?= htmlspecialchars($author['username'], ENT_QUOTES, 'UTF-8') ?></h5>
                    <small>
                        <?= date("M d, Y - H:i", strtotime($post['created_at'])) ?>
                    </small>
                    <?php if ($userId && $userId != $post['user_id']) : ?>
                        <button class="follow-btn <?= $is_following ? 'unfollow-btn' : '' ?>" data-user-id="<?= $post['user_id'] ?>" data-restricted="<?= $post['restricted_to_followers'] ?>">
                            <?= $is_following ? 'Unfollow' : 'Follow' ?>
                        </button>
                    <?php endif; ?>
                </div>
            </div>
            <div class="singlepost__thumbnail">
                <img src="<?= ROOT_URL ?>images/<?= htmlspecialchars($post['image_url'], ENT_QUOTES, 'UTF-8') ?>">
            </div>
            <div id="post-content">
                <?php
                if ($post['restricted_to_followers'] == 1 && !$is_following && !$isAdmin && $userId !== $post['user_id']) {
                    echo '<p>This post is restricted to followers only. Follow the author to view the content</p>';
                } else {
                    echo htmlspecialchars_decode($post['content'], ENT_QUOTES);
                }
                ?>
            </div>
            <?php if ($post['restricted_to_followers'] == 0 || $is_following || $isAdmin || $userId === $post['user_id']) : ?>
                <div class="icon-container">
                    <div class="like-count">
                        <i class="uil uil-thumbs-up <?= $user_has_liked ? 'liked' : '' ?>" data-post-id="<?= $post['id'] ?>" data-liked="<?= $user_has_liked ? 'true' : 'false' ?>"></i> <span><?= $like_count ?></span>
                    </div>
                    <div class="comment-count">
                        <i class="uil uil-comments"></i> <span id="comment-count"><?= count($comments) + array_sum(array_column($comments, 'reply_count')) ?></span>
                    </div>
                    <div class="follow-count">
                        <i class="uil uil-user-plus <?= $is_following ? 'unfollow-btn' : '' ?>" data-user-id="<?= $post['user_id'] ?>"></i> <span><?= $follower_count ?></span>
                    </div>
                </div>
            <?php endif; ?>
        </div>
        <div class="comments__container">
            <div class="add-comment__container">
                <h3>Leave a Comment</h3>
                <form id="comment-form">
                    <input type="hidden" name="post_id" value="<?= $post['id'] ?>">
                    <input type="hidden" name="parent_id" id="parent_id" value="">
                    <textarea name="content" placeholder="Your comment" required></textarea>
                    <button type="submit" class="btn">Submit</button>
                </form>
            </div>

            <h3>Comments</h3>
            <div id="comments-section">
                <?php display_comments($comments, $post['user_id']); ?>
            </div>
        </div>
    </section>

    <?php
    $related_posts_query = "SELECT p.id, p.title, p.image_url, c.name as category_name FROM blog_posts p
                        JOIN categories c ON p.category_id = c.id
                        WHERE p.user_id=? AND p.id != ? AND p.status = 'published' ORDER BY p.created_at DESC LIMIT 5";
    $related_posts_stmt = $pdo->prepare($related_posts_query);
    $related_posts_stmt->execute([$author_id, $id]);
    $related_posts = $related_posts_stmt->fetchAll(PDO::FETCH_ASSOC);
    ?>

    <?php if (!empty($related_posts)) : ?>
        <aside class="related-posts">
            <h3>Other Posts by the Author</h3>
            <div class="related-posts__container">
                <?php foreach ($related_posts as $related_post) : ?>
                    <div class="post">
                        <a href="<?= ROOT_URL ?>post.php?id=<?= $related_post['id'] ?>">
                            <div class="post__thumbnail">
                                <img src="<?= ROOT_URL ?>images/<?= htmlspecialchars($related_post['image_url'], ENT_QUOTES, 'UTF-8') ?>" alt="<?= htmlspecialchars($related_post['title'], ENT_QUOTES, 'UTF-8') ?>">
                            </div>
                            <div class="post__info">
                                <p class="category__button-small"><?= htmlspecialchars($related_post['category_name'], ENT_QUOTES, 'UTF-8') ?></p>
                                <h4 class="post__title"><?= htmlspecialchars($related_post['title'], ENT_QUOTES, 'UTF-8') ?></h4>
                            </div>
                        </a>
                    </div>
                <?php endforeach; ?>
            </div>
        </aside>
    <?php endif; ?>

    <!-- Sign In/Sign Up Modal -->
    <div id="authModal" class="modal">
        <div class="modal-content">
            <span class="close">&times;</span>
            <h2>Join to interact</h2>
            <p>Once you join, you can interact with the post.</p>
            <div class="modal-buttons">
                <a href="<?= ROOT_URL ?>signin.php" class="btn">Log in</a>
                <a href="<?= ROOT_URL ?>signup.php" class="btn">Sign up</a>
            </div>
        </div>
    </div>

    <!-- Alert Modal for Inappropriate Content -->
    <div id="alertModal2" class="modal2">
        <div class="modal2-content">
            <span class="close2">&times;</span>
            <p id="alertMessage2"></p>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            // Hide related posts section if the author has only one post
            const postCount = document.querySelector('.content').dataset.postCount;
            if (postCount <= 1) {
                const relatedPostsSection = document.querySelector('.related-posts');
                if (relatedPostsSection) {
                    relatedPostsSection.style.display = 'none';
                }
            }

            // Function to submit a new comment or reply
            function submitCommentForm(form) {
                const formData = new FormData(form);

                fetch('<?= ROOT_URL ?>add-comment.php', {
                    method: 'POST',
                    body: formData
                })
                    .then(response => response.json())
                    .then(data => {
                        if (data.status === 'success') {
                            const commentHtml = data.comment_html;
                            const parentId = form.querySelector('input[name="parent_id"]').value;

                            if (parentId) {
                                // Add reply to the parent comment
                                const parentComment = document.querySelector(`.comment[data-comment-id="${parentId}"] .comment-reply-container`);
                                if (parentComment) {
                                    parentComment.insertAdjacentHTML('beforeend', commentHtml);
                                }

                                // Update the reply count displayed on the parent comment
                                updateReplyCount(parentId, 1);
                            } else {
                                // Add the new comment to the comments section (top-level comment)
                                const commentsSection = document.getElementById('comments-section');
                                if (commentsSection) {
                                    commentsSection.insertAdjacentHTML('beforeend', commentHtml);
                                }
                            }

                            // Increment the total comment count
                            updateCommentCount(1);

                            // Reset the form after successful submission
                            form.reset();
                        } else {
                            showModal2(data.message);
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        showModal2('Error submitting comment');
                    });
            }

            // Function to update the total comment count
            function updateCommentCount(change) {
                const commentCountElement = document.getElementById('comment-count');
                if (commentCountElement) {
                    let currentCount = parseInt(commentCountElement.textContent);
                    currentCount += change;
                    commentCountElement.textContent = currentCount;
                }
            }

            // Function to update the reply count for a specific comment
            function updateReplyCount(parentId, change) {
                const replyLinkElement = document.querySelector(`.comment[data-comment-id="${parentId}"] .reply-link`);
                if (replyLinkElement) {
                    let currentCount = parseInt(replyLinkElement.textContent.match(/\d+/)[0]);
                    currentCount += change;
                    replyLinkElement.textContent = `Reply (${currentCount})`;
                }

                // Update the overall comment count
                updateCommentCount(change);
            }

            // Attach the submit event to the comment form
            const commentForm = document.getElementById('comment-form');
            if (commentForm) {
                commentForm.addEventListener('submit', function (e) {
                    e.preventDefault();
                    if (!isLoggedIn()) {
                        showModal();
                        return;
                    }
                    submitCommentForm(this);
                });
            }

            // Function to handle replies
            function attachReplyFormSubmitHandler(form) {
                if (form) {
                    form.addEventListener('submit', function (e) {
                        e.preventDefault();
                        submitReplyForm(this);
                    });
                }
            }

            // Function to handle submitting a reply
            function submitReplyForm(form) {
                const formData = new FormData(form);
                const parentId = form.querySelector('input[name="parent_id"]').value;

                fetch('<?= ROOT_URL ?>add-comment.php', {
                    method: 'POST',
                    body: formData
                })
                    .then(response => response.json())
                    .then(data => {
                        if (data.status === 'success') {
                            const commentHtml = data.comment_html;
                            const parentComment = document.querySelector(`.comment[data-comment-id="${parentId}"] .comment-reply-container`);
                            if (parentComment) {
                                parentComment.insertAdjacentHTML('beforeend', commentHtml);
                            }

                            // Increment the total comment count
                            updateCommentCount(1);

                            // Update the reply count
                            updateReplyCount(parentId, 1);

                            form.reset();
                        } else {
                            showModal2(data.message);
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        showModal2('Error submitting reply');
                    });
            }

            // Check if the user is logged in
            function isLoggedIn() {
                return <?= isset($_SESSION['user-id']) ? 'true' : 'false' ?>;
            }

            // Show authentication modal if the user is not logged in
            function showModal() {
                const modal = document.getElementById("authModal");
                if (modal) modal.style.display = "block";
            }

            // Show an alert modal with a message
            function showModal2(message) {
                const modal2 = document.getElementById("alertModal2");
                const messageElement = document.getElementById("alertMessage2");
                const closeBtn = document.getElementsByClassName("close2")[0];

                if (messageElement) messageElement.innerHTML = message;
                if (modal2) modal2.style.display = "block";

                if (closeBtn) {
                    closeBtn.onclick = function () {
                        if (modal2) modal2.style.display = "none";
                    }
                }

                window.onclick = function (event) {
                    if (event.target == modal2) {
                        if (modal2) modal2.style.display = "none";
                    }
                }
            }

            // Close authentication modal
            function closeModal() {
                const modal = document.getElementById("authModal");
                if (modal) modal.style.display = "none";
            }

            // Attach event listener for modal close button
            const modalCloseBtn = document.querySelector('.modal .close');
            if (modalCloseBtn) {
                modalCloseBtn.addEventListener('click', closeModal);
            }

            // Handle reply clicks to show reply form
            function handleReplyClick(e) {
                e.preventDefault();
                if (!isLoggedIn()) {
                    showModal();
                    return;
                }

                const button = e.target.closest('.reply-link');
                if (!button) return;
                const commentId = button.dataset.commentId;
                const replyFormContainer = button.parentElement.nextElementSibling;
                const commentReplyContainer = button.parentElement.nextElementSibling.nextElementSibling;

                if (replyFormContainer && commentReplyContainer) {
                    if (replyFormContainer.style.display === 'none' || replyFormContainer.style.display === '') {
                        replyFormContainer.style.display = 'block';
                        commentReplyContainer.style.display = 'block';
                        replyFormContainer.innerHTML = `
                    <form class="ajax-reply-form">
                        <input type="hidden" name="post_id" value="<?= $post['id'] ?>">
                        <input type="hidden" name="parent_id" value="${commentId}">
                        <textarea name="content" placeholder="Your reply" required></textarea>
                        <button type="submit" class="btn">Reply</button>
                    </form>
                `;
                        attachReplyFormSubmitHandler(replyFormContainer.querySelector('form'));
                    } else {
                        replyFormContainer.style.display = 'none';
                        commentReplyContainer.style.display = 'none';
                        replyFormContainer.innerHTML = '';
                    }
                }
            }

            // Event delegation to handle clicks
            const container = document.body;
            container.addEventListener('click', function (e) {
                if (e.target.closest('.reply-link')) {
                    handleReplyClick(e);
                } else if (e.target.closest('.delete-comment')) {
                    handleDeleteClick(e);
                } else if (e.target.closest('.uil-thumbs-up')) {
                    handleLikeClick(e);
                } else if (e.target.closest('.follow-btn')) {
                    handleFollowClick(e);
                }
            });

            // Handle comment deletion
            function handleDeleteClick(e) {
                const button = e.target.closest('.delete-comment');
                if (!button) return;
                const commentId = button.dataset.commentId;

                if (confirm('Are you sure you want to delete this comment?')) {
                    fetch('<?= ROOT_URL ?>delete-comment.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/x-www-form-urlencoded',
                        },
                        body: `comment_id=${commentId}`
                    })
                        .then(response => response.json())
                        .then(data => {
                            if (data.status === 'success') {
                                const commentElement = document.querySelector(`.comment[data-comment-id="${commentId}"]`);
                                if (commentElement) commentElement.remove();
                                updateCommentCount(-1);
                            } else {
                                showModal2(data.message);
                            }
                        })
                        .catch(error => {
                            console.error('Error:', error);
                            showModal2('Error deleting comment');
                        });
                }
            }

            // Handle like functionality
            function handleLikeClick(e) {
                if (!isLoggedIn()) {
                    showModal();
                    return;
                }

                const button = e.target.closest('.uil-thumbs-up');
                if (!button) return;
                const postId = button.dataset.postId;

                fetch('<?= ROOT_URL ?>handle-like.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: `post_id=${postId}`
                })
                    .then(response => response.json())
                    .then(data => {
                        if (data.status === 'success') {
                            if (data.action === 'liked') {
                                button.classList.add('like-animation', 'liked');
                                button.dataset.liked = 'true';
                                if (button.nextElementSibling) button.nextElementSibling.textContent = data.like_count;
                            } else {
                                button.classList.remove('like-animation', 'liked');
                                button.dataset.liked = 'false';
                                if (button.nextElementSibling) button.nextElementSibling.textContent = data.like_count;
                            }
                        } else {
                            showModal2(data.message);
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        showModal2('Error liking the post');
                    });
            }

            // Handle follow/unfollow functionality
            function handleFollowClick(e) {
                if (!isLoggedIn()) {
                    showModal();
                    return;
                }

                const button = e.target.closest('.follow-btn');
                if (!button) return;
                const userId = button.dataset.userId;
                const postId = '<?= $post['id'] ?>';
                const restricted = button.dataset.restricted;

                fetch('<?= ROOT_URL ?>follow.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: `followed_id=${userId}&post_id=${postId}`
                })
                    .then(response => response.json())
                    .then(data => {
                        if (data.status === 'success') {
                            const followerCountSpan = document.querySelector('.follow-count span');
                            const postContent = document.getElementById('post-content');
                            const commentsContainer = document.querySelector('.comments__container');
                            const addCommentContainer = document.querySelector('.add-comment__container');

                            if (data.action === 'followed') {
                                button.classList.add('unfollow-btn');
                                button.textContent = 'Unfollow';
                                if (followerCountSpan) followerCountSpan.textContent = data.follower_count;
                                if (postContent && data.content) postContent.innerHTML = data.content;
                                if (restricted == 1) {
                                    if (commentsContainer) commentsContainer.style.display = 'block';
                                    if (addCommentContainer) addCommentContainer.style.display = 'block';
                                }
                            } else {
                                button.classList.remove('unfollow-btn');
                                button.textContent = 'Follow';
                                if (followerCountSpan) followerCountSpan.textContent = data.follower_count;
                                if (restricted == 1) {
                                    if (postContent) postContent.innerHTML = '<p>This post is restricted to followers only. Follow the author to view the content</p>';
                                    if (commentsContainer) commentsContainer.style.display = 'none';
                                    if (addCommentContainer) addCommentContainer.style.display = 'none';
                                }
                            }
                        } else {
                            showModal2(data.message);
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        showModal2('Error following/unfollowing the user');
                    });
            }
        });

    </script>
    <?php
    include 'partials/footer.php';
    ?>
</body>
</html>
