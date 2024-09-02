<?php
require 'config/constants.php';
require 'config/database.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

try {
    if (isset($_GET['id'])) {
        $id = filter_var($_GET['id'], FILTER_SANITIZE_NUMBER_INT);

        // Fetch post from database to delete thumbnail from images folder
        $query = "SELECT * FROM blog_posts WHERE id = :id";
        $stmt = $pdo->prepare($query);

        $stmt->execute(['id' => $id]);

        // Ensure only 1 record/post was fetched
        if ($stmt->rowCount() == 1) {
            $post = $stmt->fetch(PDO::FETCH_ASSOC);
            $thumbnail_name = $post['image_url'];
            $thumbnail_path = 'images/' . $thumbnail_name;

            // Check if the thumbnail exists before trying to delete it
            if ($thumbnail_name && file_exists($thumbnail_path)) {
                if (!unlink($thumbnail_path)) {
                    $_SESSION['delete-post-error'] = "Failed to delete thumbnail. However, continuing with the deletion of the post.";
                }
            } else {
                $_SESSION['delete-post-warning'] = "Thumbnail does not exist. Continuing with the deletion of the post.";
            }

            // Begin transaction
            $pdo->beginTransaction();

            // Delete related records from the likes table
            $delete_likes_query = "DELETE FROM likes WHERE post_id = :id";
            $delete_likes_stmt = $pdo->prepare($delete_likes_query);
            $delete_likes_stmt->execute(['id' => $id]);

            // Delete related records from the comments table
            $delete_comments_query = "DELETE FROM comments WHERE post_id = :id";
            $delete_comments_stmt = $pdo->prepare($delete_comments_query);
            $delete_comments_stmt->execute(['id' => $id]);

            // Delete related records from the analytics table
            $delete_analytics_query = "DELETE FROM analytics WHERE post_id = :id";
            $delete_analytics_stmt = $pdo->prepare($delete_analytics_query);
            $delete_analytics_stmt->execute(['id' => $id]);

            // Finally, delete the post from the database
            $delete_post_query = "DELETE FROM blog_posts WHERE id = :id LIMIT 1";
            $delete_post_stmt = $pdo->prepare($delete_post_query);
            $delete_post_stmt->execute(['id' => $id]);

            if ($delete_post_stmt->rowCount() > 0) {
                $pdo->commit();  // Commit the transaction
                $_SESSION['delete-post-success'] = "Post deleted successfully";
            } else {
                $pdo->rollBack();  // Rollback the transaction on failure
                $_SESSION['delete-post-error'] = "Failed to delete the post from the database.";
            }
        } else {
            $_SESSION['delete-post-error'] = "Post not found.";
        }
    } else {
        $_SESSION['delete-post-error'] = "Invalid post ID.";
    }
} catch (PDOException $e) {
    // Rollback the transaction in case of an error
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    error_log('delete-post.php: Error executing query: ' . $e->getMessage());
    $_SESSION['delete-post-error'] = "An error occurred while deleting the post.";
}

// Determine where to redirect based on the referer
if (isset($_SERVER['HTTP_REFERER'])) {
    $referer = $_SERVER['HTTP_REFERER'];
    if (strpos($referer, 'dashboard.php') !== false) {
        header('Location: ' . ROOT_URL . 'dashboard.php');
    } elseif (strpos($referer, 'manage-posts.php') !== false) {
        header('Location: ' . ROOT_URL . 'admin/manage-posts.php');
    } else {
        header('Location: ' . ROOT_URL . 'admin/manage-posts.php'); // Default redirect if the referer is unrecognized
    }
} else {
    header('Location: ' . ROOT_URL . 'admin/manage-posts.php'); // Default redirect if no referer is available
}
exit();
?>
