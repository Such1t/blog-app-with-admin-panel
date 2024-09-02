<?php
require 'config/constants.php';
require 'config/database.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

header('Content-Type: application/json');

// Ensure the user is logged in
$user_id = isset($_SESSION['user-id']) ? $_SESSION['user-id'] : null;
if (!$user_id) {
    http_response_code(403);
    echo json_encode(['status' => 'error', 'message' => 'User not logged in']);
    exit;
}

// Ensure a valid post ID is provided
$post_id = isset($_POST['post_id']) ? filter_var($_POST['post_id'], FILTER_SANITIZE_NUMBER_INT) : null;
if (!$post_id) {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'Invalid post ID']);
    exit;
}

try {
    // Check if the user has already liked the post
    $query = "SELECT * FROM likes WHERE post_id = ? AND user_id = ?";
    $stmt = $pdo->prepare($query);
    $stmt->execute([$post_id, $user_id]);
    $like = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($like) {
        // Unlike the post
        $query = "DELETE FROM likes WHERE post_id = ? AND user_id = ?";
        $stmt = $pdo->prepare($query);
        $stmt->execute([$post_id, $user_id]);
        $action = 'unliked';
    } else {
        // Like the post
        $query = "INSERT INTO likes (post_id, user_id) VALUES (?, ?)";
        $stmt = $pdo->prepare($query);
        $stmt->execute([$post_id, $user_id]);
        $action = 'liked';
    }

    // Get the updated like count
    $query = "SELECT COUNT(*) as like_count FROM likes WHERE post_id = ?";
    $stmt = $pdo->prepare($query);
    $stmt->execute([$post_id]);
    $like_count = $stmt->fetch(PDO::FETCH_ASSOC)['like_count'];

    echo json_encode(['status' => 'success', 'action' => $action, 'like_count' => $like_count, 'user_has_liked' => $action === 'liked']);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => 'Internal Server Error']);
}
?>
