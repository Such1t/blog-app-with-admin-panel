<?php
session_start();
require '../config/database.php';

if (isset($_POST['submit']) || isset($_POST['draft'])) {
    $author_id = $_SESSION['user-id'];
    $title = filter_var($_POST['title'], FILTER_SANITIZE_FULL_SPECIAL_CHARS);
    $body = $_POST['body']; // Do not sanitize here; we will sanitize before inserting into the database
    $category_id = filter_var($_POST['category'], FILTER_SANITIZE_NUMBER_INT);
    $template_id = isset($_POST['template_id']) ? filter_var($_POST['template_id'], FILTER_SANITIZE_NUMBER_INT) : null;
    $is_featured = isset($_POST['is_featured']) ? 1 : 0;
    $restricted_to_followers = isset($_POST['restricted_to_followers']) ? 1 : 0;
    $thumbnail = $_FILES['thumbnail'];

    // Array of flagged words (example, replace with actual logic or list)
    $flagged_words = ['inappropriate', 'badword', 'fuck'];
    $found_flagged_words = [];
    echo $title;
    // Determine post status (published or draft)
    $status = isset($_POST['draft']) ? 'draft' : 'published';
    print_r($_POST);

    // Validate form data
    if (!$title) {
        $_SESSION['add-post'] = "Enter post title";
    } elseif (!$category_id) {
        $_SESSION['add-post'] = "Select post category";
    } elseif (!$body) {
        $_SESSION['add-post'] = "Enter post body";
    } elseif (!$thumbnail['name'] && $status === 'published') {
        $_SESSION['add-post'] = "Choose post thumbnail";
    } else {
        // WORK ON THUMBNAIL
        // Rename the image
        $time = time(); // make each image name unique
        $thumbnail_name = $time . '_' . $thumbnail['name']; // Use underscore to separate timestamp
        $thumbnail_tmp_name = $thumbnail['tmp_name'];
        $thumbnail_destination_path = '../images/' . $thumbnail_name;

        // Make sure file is an image
        $allowed_files = ['png', 'jpg', 'jpeg'];
        $extension = pathinfo($thumbnail_name, PATHINFO_EXTENSION);
        if (in_array($extension, $allowed_files)) {
            // Make sure image is not too big (2mb+)
            if ($thumbnail['size'] < 2000000) {
                // Upload thumbnail
                move_uploaded_file($thumbnail_tmp_name, $thumbnail_destination_path);
            } else {
                $_SESSION['add-post'] = "File size too big. Should be less than 2mb";
            }
        } else {
            $_SESSION['add-post'] = "File should be png, jpg, or jpeg";
        }

        // Check for inappropriate content
        $is_flagged = false;
        foreach ($flagged_words as $word) {
            if (stripos($body, $word) !== false) {
                $is_flagged = true;
                $found_flagged_words[] = $word;
            }
        }

        if ($is_flagged) {
            $_SESSION['add-post'] = "Content is inappropriate. Flagged words: " . implode(', ', $found_flagged_words);
            $_SESSION['flagged-words'] = $found_flagged_words; // Save flagged words in session
        }
    }

    // Redirect back (with form data) to add-post page if there is any problem
    if (isset($_SESSION['add-post'])) {
        $_SESSION['add-post-data'] = $_POST;
        header('Location: ' . ROOT_URL . 'admin/add-post.php');
        die();
    } else {
        // Insert post into database using prepared statements
        $query = "INSERT INTO blog_posts (title, content, image_url, category_id, user_id, is_featured, restricted_to_followers, template_id, status) 
                  VALUES (:title, :content, :image_url, :category_id, :user_id, :is_featured, :restricted_to_followers, :template_id, :status)";

        $stmt = $pdo->prepare($query);
        $stmt->execute([
            ':title' => $title,
            ':content' => $body, // Insert raw HTML content
            ':image_url' => $thumbnail_name,
            ':category_id' => $category_id,
            ':user_id' => $author_id,
            ':is_featured' => $is_featured,
            ':restricted_to_followers' => $restricted_to_followers,
            ':template_id' => $template_id,
            ':status' => $status // Save as 'draft' or 'published'
        ]);

        if ($stmt->rowCount()) {
            $_SESSION['add-post-success'] = "New post added successfully";
            unset($_SESSION['add-post-data']);
            unset($_SESSION['flagged-words']);
            header('Location: ' . ROOT_URL . 'admin/');
            die();
        } else {
            $_SESSION['add-post'] = "Failed to add new post";
        }
    }
}

// If not successful, redirect back to add-post page
header('Location: ' . ROOT_URL . 'admin/add-post.php');
die();
