<?php
require '../config/database.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $post_id = isset($_POST['post_id']) ? $_POST['post_id'] : null; // Fetch the post_id if it exists
    $user_id = $_POST['user_id'];
    $title = $_POST['title'];
    $category_id = $_POST['category'];
    $content = $_POST['body'];
    $status = 'draft'; // Ensure the status is set to 'draft'

    if ($post_id) {
        // Check if the specific draft exists for this user
        $check_query = "SELECT id FROM blog_posts WHERE id = ? AND user_id = ? AND status = ?";
        $check_stmt = $pdo->prepare($check_query);
        $check_stmt->execute([$post_id, $user_id, $status]);
        $existing_draft = $check_stmt->fetch(PDO::FETCH_ASSOC);
    } else {
        $existing_draft = false;
    }

    if ($existing_draft) {
        // Update existing draft
        $update_query = "UPDATE blog_posts SET title = ?, category_id = ?, content = ?, updated_at = NOW() WHERE id = ?";
        $update_stmt = $pdo->prepare($update_query);
        $update_stmt->execute([$title, $category_id, $content, $existing_draft['id']]);
    } else {
        // Insert new draft
        $insert_query = "INSERT INTO blog_posts (user_id, title, category_id, content, status, created_at) VALUES (?, ?, ?, ?, ?, NOW())";
        $insert_stmt = $pdo->prepare($insert_query);
        $insert_stmt->execute([$user_id, $title, $category_id, $content, $status]);

        // Return the new draft ID for future reference
        $post_id = $pdo->lastInsertId();
    }

    echo json_encode(['status' => 'success', 'post_id' => $post_id]);
}
?>
