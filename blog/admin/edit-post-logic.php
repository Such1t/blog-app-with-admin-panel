<?php
session_start();
require 'config/database.php';

// Make sure the edit post button was clicked
if (isset($_POST['submit'])) {
    $id = filter_var($_POST['id'], FILTER_SANITIZE_NUMBER_INT);
    $previous_thumbnail_name = filter_var($_POST['previous_thumbnail_name'], FILTER_SANITIZE_FULL_SPECIAL_CHARS);
    $title = filter_var($_POST['title'], FILTER_SANITIZE_FULL_SPECIAL_CHARS);
    $content = $_POST['content'];
    $category_id = filter_var($_POST['category'], FILTER_SANITIZE_NUMBER_INT);
    $is_featured = isset($_POST['is_featured']) ? 1 : 0;
    $restricted_to_followers = isset($_POST['restricted_to_followers']) ? 1 : 0;
    $thumbnail = $_FILES['thumbnail'];

    // Check and validate input values
    if (!$title) {
        $_SESSION['edit-post'] = "Couldn't update post. Invalid form data on edit post page.";
    } elseif (!$category_id) {
        $_SESSION['edit-post'] = "Couldn't update post. Invalid form data on edit post page.";
    } elseif (!$content) {
        $_SESSION['edit-post'] = "Couldn't update post. Invalid form data on edit post page.";
    } else {
        // Delete existing thumbnail if a new thumbnail is available
        if ($thumbnail['name']) {
            $previous_thumbnail_path = '../images/' . $previous_thumbnail_name;
            if (file_exists($previous_thumbnail_path)) {
                unlink($previous_thumbnail_path);
            }

            // Work on the new thumbnail
            $time = time(); // Make each image name unique using the current timestamp
            $thumbnail_name = $time . '_' . $thumbnail['name'];
            $thumbnail_tmp_name = $thumbnail['tmp_name'];
            $thumbnail_destination_path = '../images/' . $thumbnail_name;

            // Make sure file is an image
            $allowed_files = ['png', 'jpg', 'jpeg'];
            $extension = pathinfo($thumbnail_name, PATHINFO_EXTENSION);
            if (in_array($extension, $allowed_files)) {
                // Make sure thumbnail is not too large (2mb+)
                if ($thumbnail['size'] < 2000000) {
                    // Upload the thumbnail
                    move_uploaded_file($thumbnail_tmp_name, $thumbnail_destination_path);
                } else {
                    $_SESSION['edit-post'] = "Couldn't update post. Thumbnail size too big. Should be less than 2mb";
                }
            } else {
                $_SESSION['edit-post'] = "Couldn't update post. Thumbnail should be png, jpg, or jpeg";
            }
        }

        if (!isset($_SESSION['edit-post'])) {
            // Set the thumbnail name if a new one was uploaded, else keep the old thumbnail name
            $thumbnail_to_insert = isset($thumbnail_name) ? $thumbnail_name : $previous_thumbnail_name;

            // Update the post in the database
            $query = "UPDATE blog_posts SET title = :title, content = :content, image_url = :image_url, category_id = :category_id, is_featured = :is_featured, restricted_to_followers = :restricted_to_followers WHERE id = :id LIMIT 1";
            $stmt = $pdo->prepare($query);
            $stmt->bindParam(':title', $title, PDO::PARAM_STR);
            $stmt->bindParam(':content', $content, PDO::PARAM_STR);
            $stmt->bindParam(':image_url', $thumbnail_to_insert, PDO::PARAM_STR);
            $stmt->bindParam(':category_id', $category_id, PDO::PARAM_INT);
            $stmt->bindParam(':is_featured', $is_featured, PDO::PARAM_INT);
            $stmt->bindParam(':restricted_to_followers', $restricted_to_followers, PDO::PARAM_INT);
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);

            if ($stmt->execute()) {
                $_SESSION['edit-post-success'] = "Post updated successfully";
            } else {
                $_SESSION['edit-post'] = "Couldn't update post. Database error.";
            }
        }
    }

    header('location: ' . ROOT_URL . 'admin/index.php');
    die();
}

header('location: ' . ROOT_URL . 'admin/d=index.php');
die();
