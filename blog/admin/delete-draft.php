<?php
require '../config/database.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['user-id'])) {
    header('Location: ' . ROOT_URL . 'signin.php');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['id'])) {
    $draft_id = $_GET['id'];
    $user_id = $_SESSION['user-id'];

    // Check if the draft belongs to the logged-in user
    $query = "SELECT * FROM blog_posts WHERE id = ? AND user_id = ? AND status = 'draft'";
    $stmt = $pdo->prepare($query);
    $stmt->execute([$draft_id, $user_id]);
    $draft = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($draft) {
        // Delete the draft
        $delete_query = "DELETE FROM blog_posts WHERE id = ? AND user_id = ? AND status = 'draft'";
        $delete_stmt = $pdo->prepare($delete_query);
        $delete_stmt->execute([$draft_id, $user_id]);

        $_SESSION['delete-draft-success'] = "Draft deleted successfully.";
    } else {
        $_SESSION['delete-draft-error'] = "Draft not found or you do not have permission to delete it.";
    }
} else {
    $_SESSION['delete-draft-error'] = "Invalid request.";
}

header('Location: ' . ROOT_URL . 'manage-drafts.php');
exit();
?>
