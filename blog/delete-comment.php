<?php
require 'config/constants.php';
require 'config/database.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['user-id'])) {
    echo json_encode(['status' => 'error', 'message' => 'You must be logged in to delete a comment.']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user_id = $_SESSION['user-id'];
    $comment_id = filter_input(INPUT_POST, 'comment_id', FILTER_SANITIZE_NUMBER_INT);

    if (!$comment_id) {
        echo json_encode(['status' => 'error', 'message' => 'Invalid comment ID.']);
        exit;
    }

    // Check if the user is the comment owner, the post owner, or an admin
    $query = "SELECT comments.*, blog_posts.user_id as post_owner_id FROM comments
              JOIN blog_posts ON comments.post_id = blog_posts.id
              WHERE comments.id = ?";
    $stmt = $pdo->prepare($query);
    $stmt->execute([$comment_id]);
    $comment = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($comment && ($comment['user_id'] == $user_id || isset($_SESSION['user_is_admin']) && $_SESSION['user_is_admin'])) {
        // Delete the comment and its replies
        delete_comment_and_replies($comment_id);

        echo json_encode(['status' => 'success', 'message' => 'Comment deleted successfully.']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'You do not have permission to delete this comment.']);
    }
}

function delete_comment_and_replies($comment_id) {
    global $pdo;

    // Find all replies to this comment
    $query = "SELECT id FROM comments WHERE parent_id = ?";
    $stmt = $pdo->prepare($query);
    $stmt->execute([$comment_id]);
    $replies = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Delete the comment
    $delete_query = "DELETE FROM comments WHERE id = ?";
    $delete_stmt = $pdo->prepare($delete_query);
    $delete_stmt->execute([$comment_id]);

    // Recursively delete replies
    foreach ($replies as $reply) {
        delete_comment_and_replies($reply['id']);
    }
}
?>
