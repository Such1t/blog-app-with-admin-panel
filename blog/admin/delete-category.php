<?php
session_start();
require '../config/database.php';

if (isset($_GET['id'])) {
    $id = filter_var($_GET['id'], FILTER_SANITIZE_NUMBER_INT);

    try {
        // Start transaction
        $pdo->beginTransaction();

        // Update category_id of posts that belong to this category to id of uncategorized category
        $update_query = "UPDATE posts SET category_id = :new_category_id WHERE category_id = :old_category_id";
        $stmt = $pdo->prepare($update_query);
        $stmt->execute(['new_category_id' => 5, 'old_category_id' => $id]);

        // Delete category
        $delete_query = "DELETE FROM categories WHERE id = :id LIMIT 1";
        $stmt = $pdo->prepare($delete_query);
        $stmt->execute(['id' => $id]);

        // Commit transaction
        $pdo->commit();

        $_SESSION['delete-category-success'] = "Category deleted successfully";
    } catch (Exception $e) {
        // Rollback transaction on error
        $pdo->rollBack();
        $_SESSION['delete-category'] = "Failed to delete category: " . $e->getMessage();
    }
}

header('location: ' . ROOT_URL . 'admin/manage-categories.php');
die();
?>
