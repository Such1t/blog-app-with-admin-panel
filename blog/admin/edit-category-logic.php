<?php
session_start();
require '../config/database.php';

if (isset($_POST['submit'])) {
    $id = filter_var($_POST['id'], FILTER_SANITIZE_NUMBER_INT);
    $name = filter_var($_POST['name'], FILTER_SANITIZE_FULL_SPECIAL_CHARS);

    // Validate input
    if (!$name) {
        $_SESSION['edit-category'] = "Invalid form input on edit category page";
        header('location: ' . ROOT_URL . 'admin/edit-category.php?id=' . $id);
        die();
    } else {
        // Update category in the database using PDO
        $query = "UPDATE categories SET name = :name WHERE id = :id LIMIT 1";
        $stmt = $pdo->prepare($query);
        $stmt->bindParam(':name', $name, PDO::PARAM_STR);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);

        if ($stmt->execute()) {
            $_SESSION['edit-category-success'] = "Category $name updated successfully";
        } else {
            $_SESSION['edit-category'] = "Couldn't update category";
        }

        header('location: ' . ROOT_URL . 'admin/manage-categories.php');
        die();
    }
} else {
    header('location: ' . ROOT_URL . 'admin/manage-categories.php');
    die();
}
?>
