<?php
session_start();
require 'config/database.php';
require 'config/constants.php';
require 'perspective_moderate_content.php'; // Include the moderation function

if (isset($_POST['submit']) || isset($_POST['draft']) || isset($_POST['autoSaveDraft'])) {
    $author_id = $_SESSION['user-id'];
    $title = filter_var($_POST['title'], FILTER_SANITIZE_FULL_SPECIAL_CHARS);
    $body = $_POST['body']; // Raw HTML content
    $category_id = filter_var($_POST['category'], FILTER_SANITIZE_NUMBER_INT);
    $is_featured = isset($_POST['is_featured']) ? 1 : 0;
    $restricted_to_followers = isset($_POST['restricted_to_followers']) ? 1 : 0;
    $thumbnail = $_FILES['thumbnail'];
    $status = isset($_POST['draft']) || isset($_POST['autoSaveDraft']) ? 'draft' : 'published';

    // Handle the thumbnail if it's provided
    $thumbnail_name = null; // Default to null if no thumbnail is provided
    if (!empty($thumbnail['name'])) {
        $time = time(); // Unique name using current timestamp
        $thumbnail_name = $time . '_' . preg_replace('/\s+/', '_', $thumbnail['name']); // Replace spaces with underscores
        $thumbnail_tmp_name = $thumbnail['tmp_name'];
        $thumbnail_destination_path = 'images/' . $thumbnail_name;

        // Ensure file is an image by checking MIME type
        $allowed_mime_types = ['image/png', 'image/jpg', 'image/jpeg'];
        $file_mime_type = mime_content_type($thumbnail_tmp_name);

        if (in_array($file_mime_type, $allowed_mime_types)) {
            // Ensure file is not too large (2MB+)
            if ($thumbnail['size'] < 2000000) {
                // Upload thumbnail
                if (!move_uploaded_file($thumbnail_tmp_name, $thumbnail_destination_path)) {
                    $_SESSION['add-post-data'] = [
                        'title' => $title,
                        'category' => $category_id,
                        'body' => $body, // Preserve raw HTML content
                        'is_featured' => $is_featured,
                        'restricted_to_followers' => $restricted_to_followers
                    ];
                    $_SESSION['add-post'] = 'Failed to upload thumbnail.';
                    header('Location: ' . ROOT_URL . 'add-post.php');
                    die();
                }
            } else {
                $_SESSION['add-post-data'] = [
                    'title' => $title,
                    'category' => $category_id,
                    'body' => $body, // Preserve raw HTML content
                    'is_featured' => $is_featured,
                    'restricted_to_followers' => $restricted_to_followers
                ];
                $_SESSION['add-post'] = 'Thumbnail size exceeds 2MB.';
                header('Location: ' . ROOT_URL . 'add-post.php');
                die();
            }
        } else {
            $_SESSION['add-post-data'] = [
                'title' => $title,
                'category' => $category_id,
                'body' => $body, // Preserve raw HTML content
                'is_featured' => $is_featured,
                'restricted_to_followers' => $restricted_to_followers
            ];
            $_SESSION['add-post'] = 'Invalid file type. Only PNG, JPG, and JPEG are allowed.';
            header('Location: ' . ROOT_URL . 'add-post.php');
            die();
        }
    }

    // Validation for published posts only
    if ($status === 'published') {
        // Validate form data for published posts
        if (!$title || !$category_id || !$body || empty($thumbnail_name)) {
            $_SESSION['add-post-data'] = [
                'title' => $title,
                'category' => $category_id,
                'body' => $body, // Preserve raw HTML content
                'is_featured' => $is_featured,
                'restricted_to_followers' => $restricted_to_followers
            ];
            $_SESSION['add-post'] = 'Please fill in all required fields.';
            header('Location: ' . ROOT_URL . 'add-post.php');
            die();
        }

        // Moderate the content for published posts
        $moderation_result = moderate_content($body);
        if (isset($moderation_result['error']) && $moderation_result['error']) {
            $_SESSION['add-post-data'] = [
                'title' => $title,
                'category' => $category_id,
                'body' => $body, // Preserve raw HTML content
                'is_featured' => $is_featured,
                'restricted_to_followers' => $restricted_to_followers
            ];
            $_SESSION['add-post'] = 'There was an error checking the content: ' . $moderation_result['message'];
            header('Location: ' . ROOT_URL . 'add-post.php');
            die();
        } elseif ($moderation_result['isInappropriate']) {
            $_SESSION['add-post-data'] = [
                'title' => $title,
                'category' => $category_id,
                'body' => $body, // Preserve raw HTML content
                'is_featured' => $is_featured,
                'restricted_to_followers' => $restricted_to_followers
            ];
            $_SESSION['add-post'] = 'Your content contains inappropriate content: ' . implode(', ', $moderation_result['flaggedText']);
            header('Location: ' . ROOT_URL . 'add-post.php');
            die();
        }
    }

    // Insert post into database
    $query = "INSERT INTO blog_posts (title, content, image_url, category_id, user_id, is_featured, restricted_to_followers, status) VALUES (:title, :content, :image_url, :category_id, :user_id, :is_featured, :restricted_to_followers, :status)";
    $stmt = $pdo->prepare($query);
    $stmt->execute([
        ':title' => $title,
        ':content' => $body, // Insert raw HTML content
        ':image_url' => $thumbnail_name, // Use the generated thumbnail name or null
        ':category_id' => $category_id,
        ':user_id' => $author_id,
        ':is_featured' => $is_featured,
        ':restricted_to_followers' => $restricted_to_followers,
        ':status' => $status
    ]);

    if ($stmt->rowCount()) {
        // Clear session data on successful post submission
        unset($_SESSION['add-post-data']);
        $_SESSION['add-post-success'] = "New post added successfully";
        header('Location: ' . ROOT_URL . 'index.php');
        die();
    } else {
        $_SESSION['add-post-data'] = [
            'title' => $title,
            'category' => $category_id,
            'body' => $body, // Preserve raw HTML content
            'is_featured' => $is_featured,
            'restricted_to_followers' => $restricted_to_followers
        ];
        $_SESSION['add-post'] = 'Failed to add new post.';
        header('Location: ' . ROOT_URL . 'add-post.php');
        die();
    }
}

// If not successful, redirect back to add-post page
header('Location: ' . ROOT_URL . 'add-post.php');
die();
?>
