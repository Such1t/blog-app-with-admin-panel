<?php
session_start();
require '../config/database.php';

// Check which form is submitted
if (isset($_POST['submit-add'])) {
    // Handle add category
    $title = filter_var($_POST['title'], FILTER_SANITIZE_FULL_SPECIAL_CHARS);
    $description = filter_var($_POST['description'], FILTER_SANITIZE_FULL_SPECIAL_CHARS);

    if (!$title) {
        $_SESSION['add-category'] = "Enter title";
    } elseif (!$description) {
        $_SESSION['add-category'] = "Enter description";
    }

    if (isset($_SESSION['add-category'])) {
        $_SESSION['add-category-data'] = $_POST;
        header('location: ' . ROOT_URL . 'admin/manage-categories.php');
        die();
    } else {
        $query = "INSERT INTO categories (title, description) VALUES (:title, :description)";
        $stmt = $pdo->prepare($query);
        $stmt->bindParam(':title', $title);
        $stmt->bindParam(':description', $description);

        if ($stmt->execute()) {
            $_SESSION['add-category-success'] = "$title category added successfully";
        } else {
            $_SESSION['add-category'] = "Couldn't add category";
        }
        header('location: ' . ROOT_URL . 'admin/manage-categories.php');
        die();
    }
} elseif (isset($_POST['submit-edit'])) {
    // Handle edit category
    $id = filter_var($_POST['id'], FILTER_SANITIZE_NUMBER_INT);
    $name = filter_var($_POST['name'], FILTER_SANITIZE_FULL_SPECIAL_CHARS);

    if (!$name) {
        $_SESSION['edit-category'] = "Invalid form input on edit category page";
        header('location: ' . ROOT_URL . 'admin/manage-categories.php');
        die();
    } else {
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
} elseif (isset($_GET['delete'])) {
    // Handle delete category
    $id = filter_var($_GET['delete'], FILTER_SANITIZE_NUMBER_INT);

    try {
        $pdo->beginTransaction();

        $update_query = "UPDATE posts SET category_id = :new_category_id WHERE category_id = :old_category_id";
        $stmt = $pdo->prepare($update_query);
        $stmt->execute(['new_category_id' => 5, 'old_category_id' => $id]);

        $delete_query = "DELETE FROM categories WHERE id = :id LIMIT 1";
        $stmt = $pdo->prepare($delete_query);
        $stmt->execute(['id' => $id]);

        $pdo->commit();

        $_SESSION['delete-category-success'] = "Category deleted successfully";
    } catch (Exception $e) {
        $pdo->rollBack();
        $_SESSION['delete-category'] = "Failed to delete category: " . $e->getMessage();
    }

    header('location: ' . ROOT_URL . 'admin/manage-categories.php');
    die();
} else {
    header('location: ' . ROOT_URL . 'admin/manage-categories.php');
    die();
}
?>
<?php
