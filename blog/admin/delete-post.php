<?php
session_start();
require 'config/database.php';

try {
    if (isset($_GET['id'])) {
        $id = filter_var($_GET['id'], FILTER_SANITIZE_NUMBER_INT);

        // Fetch post from the database using PDO to delete the thumbnail from the images folder
        $query = "SELECT * FROM blog_posts WHERE id = :id LIMIT 1";
        $stmt = $pdo->prepare($query);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->execute();

        if ($stmt->rowCount() == 1) {
            $post = $stmt->fetch(PDO::FETCH_ASSOC);
            $image_url = $post['image_url'];

            if ($image_url) {
                $thumbnail_path = '../images/' . $image_url;
                if (file_exists($thumbnail_path)) {
                    unlink($thumbnail_path);
                } else {
                    $_SESSION['delete-post-error'] = "Image file does not exist.";
                }
            }

            $pdo->beginTransaction();

            // Delete associated analytics data
            $delete_analytics_query = "DELETE FROM analytics WHERE post_id = :id";
            $delete_analytics_stmt = $pdo->prepare($delete_analytics_query);
            $delete_analytics_stmt->bindParam(':id', $id, PDO::PARAM_INT);
            $delete_analytics_stmt->execute();

            // Delete associated comments
            $delete_comments_query = "DELETE FROM comments WHERE post_id = :id";
            $delete_comments_stmt = $pdo->prepare($delete_comments_query);
            $delete_comments_stmt->bindParam(':id', $id, PDO::PARAM_INT);
            $delete_comments_stmt->execute();

            // Delete associated likes
            $delete_likes_query = "DELETE FROM likes WHERE post_id = :id";
            $delete_likes_stmt = $pdo->prepare($delete_likes_query);
            $delete_likes_stmt->bindParam(':id', $id, PDO::PARAM_INT);
            $delete_likes_stmt->execute();

            // Delete the post
            $delete_post_query = "DELETE FROM blog_posts WHERE id = :id LIMIT 1";
            $delete_post_stmt = $pdo->prepare($delete_post_query);
            $delete_post_stmt->bindParam(':id', $id, PDO::PARAM_INT);
            $delete_post_stmt->execute();

            if ($delete_post_stmt->rowCount()) {
                $pdo->commit();
                $_SESSION['delete-post-success'] = "Post deleted successfully";
            } else {
                $pdo->rollBack();
                $_SESSION['delete-post-error'] = "Failed to delete post from the database.";
            }
        } else {
            $_SESSION['delete-post-error'] = "Post not found.";
        }
    } else {
        $_SESSION['delete-post-error'] = "Invalid post ID.";
    }
} catch (PDOException $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    $_SESSION['delete-post-error'] = "Error: " . $e->getMessage();
}

header('Location: ' . ROOT_URL . 'admin/manage-posts.php');
die();
?>
