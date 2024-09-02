<?php
session_start();
require 'config/constants.php';
require 'config/database.php';

if (!isset($_SESSION['user-id'])) {
    header('Location: ' . ROOT_URL . 'signin.php');
    exit();
}

$isAdmin = isset($_SESSION['user_is_admin']) && $_SESSION['user_is_admin'];

if (isset($_POST['submit']) || isset($_POST['draft'])) {
    $id = filter_var($_POST['id'], FILTER_SANITIZE_NUMBER_INT);
    $previous_thumbnail_name = filter_var($_POST['previous_thumbnail_name'], FILTER_SANITIZE_FULL_SPECIAL_CHARS);
    $title = filter_var($_POST['title'], FILTER_SANITIZE_FULL_SPECIAL_CHARS);
    $content = $_POST['content'];
    $category_id = filter_var($_POST['category'], FILTER_SANITIZE_NUMBER_INT);
    $restricted_to_followers = isset($_POST['restricted_to_followers']) ? 1 : 0;
    $thumbnail = $_FILES['thumbnail'];

    // For admins, check the is_featured checkbox value
    if ($isAdmin) {
        $is_featured = isset($_POST['is_featured']) ? 1 : 0;
    } else {
        // For non-admins, fetch the existing value from the database
        $query = "SELECT is_featured FROM blog_posts WHERE id = :id LIMIT 1";
        $stmt = $pdo->prepare($query);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        $is_featured = $result['is_featured'];
    }

    $status = isset($_POST['draft']) ? 'draft' : 'published';

    if (!$title || !$category_id || !$content) {
        $_SESSION['edit-post'] = "Please fill in all required fields.";
        header('Location: ' . ROOT_URL . 'edit-post.php?id=' . $id);
        exit();
    }

    $content = str_replace(['<p><p>', '</p></p>'], ['<p>', '</p>'], $content);

    $thumbnail_to_insert = $previous_thumbnail_name;
    if (!empty($thumbnail['name'])) {
        $previous_thumbnail_path = 'C:\xampp\htdocs\blog-app-with-admin-panel\blog\images\\' . $previous_thumbnail_name;
        if (file_exists($previous_thumbnail_path) && $previous_thumbnail_name !== 'default-avatar.png') {
            unlink($previous_thumbnail_path);
        }

        $time = time();
        $thumbnail_name = $time . '_' . preg_replace('/\s+/', '_', $thumbnail['name']);
        $thumbnail_tmp_name = $thumbnail['tmp_name'];
        $thumbnail_destination_path = 'C:\xampp\htdocs\blog-app-with-admin-panel\blog\images\\' . $thumbnail_name;

        $allowed_mime_types = ['image/png', 'image/jpg', 'image/jpeg'];
        $file_mime_type = mime_content_type($thumbnail_tmp_name);
        if (in_array($file_mime_type, $allowed_mime_types)) {
            if ($thumbnail['size'] < 2000000) {
                if (move_uploaded_file($thumbnail_tmp_name, $thumbnail_destination_path)) {
                    $thumbnail_to_insert = $thumbnail_name;
                } else {
                    $_SESSION['edit-post'] = "Couldn't update post. Failed to upload the new thumbnail.";
                    header('Location: ' . ROOT_URL . 'edit-post.php?id=' . $id);
                    exit();
                }
            } else {
                $_SESSION['edit-post'] = "Couldn't update post. Thumbnail size too big. Should be less than 2MB.";
                header('Location: ' . ROOT_URL . 'edit-post.php?id=' . $id);
                exit();
            }
        } else {
            $_SESSION['edit-post'] = "Couldn't update post. Thumbnail should be a PNG, JPG, or JPEG image.";
            header('Location: ' . ROOT_URL . 'edit-post.php?id=' . $id);
            exit();
        }
    }

    $query = "UPDATE blog_posts SET title = :title, content = :content, image_url = :image_url, category_id = :category_id, is_featured = :is_featured, restricted_to_followers = :restricted_to_followers, status = :status WHERE id = :id LIMIT 1";
    $stmt = $pdo->prepare($query);
    $stmt->bindParam(':title', $title, PDO::PARAM_STR);
    $stmt->bindParam(':content', $content, PDO::PARAM_STR);
    $stmt->bindParam(':image_url', $thumbnail_to_insert, PDO::PARAM_STR);
    $stmt->bindParam(':category_id', $category_id, PDO::PARAM_INT);
    $stmt->bindParam(':is_featured', $is_featured, PDO::PARAM_INT);
    $stmt->bindParam(':restricted_to_followers', $restricted_to_followers, PDO::PARAM_INT);
    $stmt->bindParam(':status', $status, PDO::PARAM_STR);
    $stmt->bindParam(':id', $id, PDO::PARAM_INT);

    if ($stmt->execute()) {
        if ($status === 'draft') {
            $_SESSION['edit-post-success'] = "Draft saved successfully.";
        } else {
            $_SESSION['edit-post-success'] = "Post updated successfully.";
        }
        header('Location: ' . ROOT_URL . 'index.php');
        exit();
    } else {
        $_SESSION['edit-post'] = "Couldn't update post. Database error.";
        header('Location: ' . ROOT_URL . 'edit-post.php?id=' . $id);
        exit();
    }
}

header('Location: ' . ROOT_URL . 'dashboard.php');
exit();
