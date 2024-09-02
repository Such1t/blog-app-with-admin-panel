<?php
require 'config/constants.php';
require 'config/database.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['user-id'])) {
    header('Location: ' . ROOT_URL . 'signin.php');
    exit();
}

$user_id = $_SESSION['user-id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = filter_var($_POST['username'], FILTER_SANITIZE_STRING);
    $email = filter_var($_POST['email'], FILTER_SANITIZE_EMAIL);
    $profile_info = filter_var($_POST['profile_info'], FILTER_SANITIZE_STRING);

    $avatar = $_FILES['avatar'];
    $avatar_name = $avatar['name'];

    if ($avatar_name) {
        $avatar_tmp_name = $avatar['tmp_name'];
        $avatar_new_name = time() . '-' . $avatar_name;
        $avatar_upload_path = 'images/' . $avatar_new_name; // Ensure this path is correct based on your folder structure

        if (move_uploaded_file($avatar_tmp_name, $avatar_upload_path)) {
            $avatar_sql = ", avatar_url = :avatar_url";
            $_SESSION['user_avatar'] = $avatar_new_name; // Update session with new avatar

            // Delete old avatar file if it's not the default avatar
            if (!empty($user['avatar_url']) && $user['avatar_url'] != 'default-avatar.png') {
                $old_avatar_path = 'images/' . $user['avatar_url'];
                if (file_exists($old_avatar_path)) {
                    unlink($old_avatar_path);
                }
            }

        } else {
            $_SESSION['update-profile-error'] = "Failed to upload the avatar.";
            header('Location: ' . ROOT_URL . 'edit-profile.php');
            exit();
        }
    } else {
        $avatar_sql = "";
    }

    $update_query = "
        UPDATE users 
        SET username = :username, email = :email, profile_info = :profile_info $avatar_sql
        WHERE id = :id
    ";

    $params = [
        'username' => $username,
        'email' => $email,
        'profile_info' => $profile_info,
        'id' => $user_id
    ];

    // Merge avatar URL parameter if it exists
    if ($avatar_name) {
        $params['avatar_url'] = $avatar_new_name;
    }

    try {
        $stmt = $pdo->prepare($update_query);
        $stmt->execute($params);

        // Update the session with the new data
        $_SESSION['user-username'] = $username;
        $_SESSION['user-email'] = $email;
        $_SESSION['user-profile-info'] = $profile_info;

        $_SESSION['update-profile-success'] = "Profile updated successfully.";
        header('Location: ' . ROOT_URL . 'dashboard.php');
        exit();
    } catch (PDOException $e) {
        $_SESSION['update-profile-error'] = "Failed to update profile: " . $e->getMessage();
        header('Location: ' . ROOT_URL . 'edit-profile.php');
        exit();
    }
}
