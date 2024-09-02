<?php
require 'config/constants.php';
require 'config/database.php';
require 'perspective_moderate_content.php'; // Include the moderation function

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$response = ['status' => 'error', 'message' => 'An error occurred'];

if (isset($_POST['post_id'], $_POST['content']) && !empty($_POST['content'])) {
    $post_id = filter_var($_POST['post_id'], FILTER_SANITIZE_NUMBER_INT);
    $content = filter_var($_POST['content'], FILTER_SANITIZE_FULL_SPECIAL_CHARS);
    $parent_id = isset($_POST['parent_id']) ? filter_var($_POST['parent_id'], FILTER_SANITIZE_NUMBER_INT) : null;
    $user_id = $_SESSION['user-id'];

    // Moderate the content
    $moderationResult = moderate_content($content);
    if (isset($moderationResult['error'])) {
        $response['message'] = 'Content moderation failed: ' . $moderationResult['message'];
    } elseif ($moderationResult['isInappropriate']) {
        $response['message'] = 'Your comment contains inappropriate content and cannot be posted.';
    } else {
        // Fetch the actual username from the database
        $user_query = "SELECT username FROM users WHERE id = ?";
        $user_stmt = $pdo->prepare($user_query);
        $user_stmt->execute([$user_id]);
        $user = $user_stmt->fetch(PDO::FETCH_ASSOC);

        if ($user && $content) {
            $username = htmlspecialchars($user['username'], ENT_QUOTES, 'UTF-8');
            $query = "INSERT INTO comments (post_id, user_id, parent_id, content, created_at) VALUES (?, ?, ?, ?, NOW())";
            $stmt = $pdo->prepare($query);
            if ($stmt->execute([$post_id, $user_id, $parent_id, $content])) {
                $comment_id = $pdo->lastInsertId();

                // Use an icon for the delete button
                $comment_html = "
                    <div class='comment' data-comment-id='$comment_id'>
                        <p><strong>$username:</strong> " . htmlspecialchars($content) . "</p>
                        <div style='display: flex; align-items: center;'>
                            <a href='#' class='reply-link' data-comment-id='$comment_id'>Reply (0)</a>
                            <button class='delete-comment' data-comment-id='$comment_id'>
                                <i class='uil uil-trash-alt'></i>
                            </button>
                        </div>
                        <div class='reply-form-container' style='display: none;'></div>
                        <div class='comment-reply-container' style='display: none;'></div>
                    </div>
                ";

                // Calculate total comments including replies
                $total_comments_query = "SELECT COUNT(*) as total_comments FROM comments WHERE post_id = ?";
                $total_comments_stmt = $pdo->prepare($total_comments_query);
                $total_comments_stmt->execute([$post_id]);
                $total_comments = $total_comments_stmt->fetch(PDO::FETCH_ASSOC)['total_comments'];

                $response = [
                    'status' => 'success',
                    'comment_html' => $comment_html,
                    'is_reply' => $parent_id ? true : false,
                    'total_comments' => $total_comments,
                    'parent_id' => $parent_id
                ];
            }
        }
    }
}

header('Content-Type: application/json');
echo json_encode($response);
?>
