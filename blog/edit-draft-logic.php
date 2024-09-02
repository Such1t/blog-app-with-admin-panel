<?php
session_start();
require 'config/database.php';
require 'config/constants.php';
require 'perspective_moderate_content.php';

// Ensure the user is logged in
if (!isset($_SESSION['user-id'])) {
    header('Location: ' . ROOT_URL . 'signin.php');
    exit();
}

if (isset($_POST['draft']) || isset($_POST['post_draft'])) {
    $post_id = filter_var($_POST['id'], FILTER_SANITIZE_NUMBER_INT);
    $previous_thumbnail_name = filter_var($_POST['previous_thumbnail_name'], FILTER_SANITIZE_FULL_SPECIAL_CHARS);
    $title = filter_var($_POST['title'], FILTER_SANITIZE_FULL_SPECIAL_CHARS);
    $body = $_POST['content']; // Raw HTML content
    $category_id = filter_var($_POST['category'], FILTER_SANITIZE_NUMBER_INT);
    $is_featured = isset($_POST['is_featured']) ? 1 : 0;
    $restricted_to_followers = isset($_POST['restricted_to_followers']) ? 1 : 0;
    $thumbnail = $_FILES['thumbnail'];

    // Determine the status based on whether 'draft' or 'post_draft' button was pressed
    $status = isset($_POST['draft']) ? 'draft' : 'published';

    // Validate input values
    if (!$title || !$category_id || !$body) {
        $_SESSION['edit-draft-error'] = "Please fill in all required fields.";
        header('Location: ' . ROOT_URL . 'edit-draft.php?id=' . $post_id);
        exit();
    }

    // Handle the new thumbnail if uploaded
    $thumbnail_name = $previous_thumbnail_name;
    if (!empty($thumbnail['name'])) {
        $previous_thumbnail_path = 'images/' . $previous_thumbnail_name;
        if (!empty($previous_thumbnail_name) && file_exists($previous_thumbnail_path)) {
            if (!unlink($previous_thumbnail_path)) {
                $_SESSION['edit-draft-error'] = "Failed to delete the previous thumbnail.";
                header('Location: ' . ROOT_URL . 'edit-draft.php?id=' . $post_id);
                exit();
            }
        }

        // Rename and move the new thumbnail
        $time = time(); // Unique name using current timestamp
        $thumbnail_name = $time . '_' . preg_replace('/\s+/', '_', $thumbnail['name']); // Replace spaces with underscores
        $thumbnail_tmp_name = $thumbnail['tmp_name'];
        $thumbnail_destination_path = 'images/' . $thumbnail_name;

        // Ensure file is an image by checking MIME type
        $allowed_mime_types = ['image/png', 'image/jpg', 'image/jpeg'];
        $file_mime_type = mime_content_type($thumbnail_tmp_name);
        if (in_array($file_mime_type, $allowed_mime_types)) {
            // Ensure thumbnail is not too large (2MB+)
            if ($thumbnail['size'] < 2000000) {
                // Upload thumbnail
                if (!move_uploaded_file($thumbnail_tmp_name, $thumbnail_destination_path)) {
                    $_SESSION['edit-draft-error'] = "Failed to upload thumbnail.";
                    header('Location: ' . ROOT_URL . 'edit-draft.php?id=' . $post_id);
                    exit();
                }
            } else {
                $_SESSION['edit-draft-error'] = "Thumbnail size exceeds 2MB.";
                header('Location: ' . ROOT_URL . 'edit-draft.php?id=' . $post_id);
                exit();
            }
        } else {
            $_SESSION['edit-draft-error'] = "Invalid file type. Only PNG, JPG, and JPEG are allowed.";
            header('Location: ' . ROOT_URL . 'edit-draft.php?id=' . $post_id);
            exit();
        }
    }

    // Moderate the content if publishing the post
    if ($status === 'published') {
        if (empty($thumbnail_name)) {
            $_SESSION['edit-draft-error'] = "Thumbnail is required to publish the post.";
            header('Location: ' . ROOT_URL . 'edit-draft.php?id=' . $post_id);
            exit();
        }

        $moderation_result = moderate_content($body);
        if (isset($moderation_result['error']) && $moderation_result['error']) {
            $_SESSION['edit-draft-error'] = 'There was an error checking the content: ' . $moderation_result['message'];
            header('Location: ' . ROOT_URL . 'edit-draft.php?id=' . $post_id);
            exit();
        } elseif ($moderation_result['isInappropriate']) {
            $_SESSION['edit-draft-error'] = 'Your content contains inappropriate content: ' . implode(', ', $moderation_result['flaggedText']);
            header('Location: ' . ROOT_URL . 'edit-draft.php?id=' . $post_id);
            exit();
        }
    }

    // Update the post in the database
    $query = "UPDATE blog_posts SET title = :title, content = :content, image_url = :image_url, category_id = :category_id, is_featured = :is_featured, restricted_to_followers = :restricted_to_followers, status = :status WHERE id = :id";
    $stmt = $pdo->prepare($query);
    $stmt->bindParam(':title', $title, PDO::PARAM_STR);
    $stmt->bindParam(':content', $body, PDO::PARAM_STR);
    $stmt->bindParam(':image_url', $thumbnail_name, PDO::PARAM_STR);
    $stmt->bindParam(':category_id', $category_id, PDO::PARAM_INT);
    $stmt->bindParam(':is_featured', $is_featured, PDO::PARAM_INT);
    $stmt->bindParam(':restricted_to_followers', $restricted_to_followers, PDO::PARAM_INT);
    $stmt->bindParam(':status', $status, PDO::PARAM_STR);
    $stmt->bindParam(':id', $post_id, PDO::PARAM_INT);

    if ($stmt->execute()) {
        if ($status === 'published') {
            $_SESSION['edit-draft-success'] = "Post published successfully.";
            header('Location: ' . ROOT_URL . 'index.php');
        } else {
            $_SESSION['edit-draft-success'] = "Draft updated successfully.";
            header('Location: ' . ROOT_URL . 'manage-drafts.php');
        }
        exit();
    } else {
        $_SESSION['edit-draft-error'] = "Couldn't update draft. Database error.";
        header('Location: ' . ROOT_URL . 'edit-draft.php?id=' . $post_id);
        exit();
    }
}

header('Location: ' . ROOT_URL . 'manage-drafts.php');
exit();
