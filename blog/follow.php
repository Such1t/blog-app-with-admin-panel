<?php
require 'config/constants.php';
require 'config/database.php';
require 'common_functions.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$response = ['status' => 'error', 'message' => 'An error occurred'];

try {
    if (isset($_POST['followed_id'], $_SESSION['user-id'])) {
        $follower_id = $_SESSION['user-id'];
        $followed_id = filter_var($_POST['followed_id'], FILTER_SANITIZE_NUMBER_INT);
        $post_id = isset($_POST['post_id']) ? filter_var($_POST['post_id'], FILTER_SANITIZE_NUMBER_INT) : null;

        // Validate followed_id exists in users table
        $user_check_query = "SELECT 1 FROM users WHERE id = ?";
        $user_check_stmt = $pdo->prepare($user_check_query);
        $user_check_stmt->execute([$followed_id]);
        if (!$user_check_stmt->fetchColumn()) {
            $response['message'] = 'The user you are trying to follow/unfollow does not exist.';
            throw new Exception('Invalid followed_id');
        }

        // Check if the user is already following
        $check_query = "SELECT 1 FROM followers WHERE follower_id = ? AND followed_id = ?";
        $check_stmt = $pdo->prepare($check_query);
        $check_stmt->execute([$follower_id, $followed_id]);
        $is_following = $check_stmt->fetchColumn();

        if ($is_following) {
            // Unfollow
            $delete_query = "DELETE FROM followers WHERE follower_id = ? AND followed_id = ?";
            $delete_stmt = $pdo->prepare($delete_query);
            $delete_stmt->execute([$follower_id, $followed_id]);
            $action = 'unfollowed';
        } else {
            // Follow
            $insert_query = "INSERT INTO followers (follower_id, followed_id) VALUES (?, ?)";
            $insert_stmt = $pdo->prepare($insert_query);
            $insert_stmt->execute([$follower_id, $followed_id]);
            $action = 'followed';
        }

        // Get updated follower count
        $follower_count_query = "SELECT COUNT(*) as follower_count FROM followers WHERE followed_id = ?";
        $follower_count_stmt = $pdo->prepare($follower_count_query);
        $follower_count_stmt->execute([$followed_id]);
        $follower_count = $follower_count_stmt->fetch(PDO::FETCH_ASSOC)['follower_count'];

        // If following and post_id is provided, get the post content and comments
        $content = '';
        $comments_html = '';
        if ($action === 'followed' && $post_id) {
            $content_query = "SELECT content, user_id FROM blog_posts WHERE id = ?";
            $content_stmt = $pdo->prepare($content_query);
            $content_stmt->execute([$post_id]);
            $post = $content_stmt->fetch(PDO::FETCH_ASSOC);

            if ($post) {
                $content = htmlspecialchars_decode($post['content'], ENT_QUOTES);

                // Fetch comments
                $comment_query = "
                    SELECT comments.*, users.username 
                    FROM comments 
                    JOIN users ON comments.user_id = users.id 
                    WHERE post_id = ? 
                    ORDER BY created_at ASC
                ";
                $comment_stmt = $pdo->prepare($comment_query);
                $comment_stmt->execute([$post_id]);
                $comments = $comment_stmt->fetchAll(PDO::FETCH_ASSOC);

                ob_start();
                display_comments($comments, null, $post['user_id']);
                $comments_html = ob_get_clean();
            }
        }

        $response = [
            'status' => 'success',
            'action' => $action,
            'follower_count' => $follower_count,
            'content' => $content,
            'comments_html' => $comments_html
        ];
    } else {
        $response['message'] = 'Invalid request parameters. Make sure you are logged in and providing the required data.';
    }
} catch (Exception $e) {
    $response['message'] = 'An error occurred: ' . $e->getMessage();
}

header('Content-Type: application/json');
echo json_encode($response);
