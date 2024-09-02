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

    $params = [
        'username' => $username,
        'email' => $email,
        'profile_info' => $profile_info,
        'id' => $user_id
    ];

    // Handle avatar upload if provided
    if ($avatar_name) {
        $avatar_tmp_name = $avatar['tmp_name'];
        $avatar_new_name = time() . '-' . $avatar_name;
        $avatar_upload_path = 'images/' . $avatar_new_name;

        if (move_uploaded_file($avatar_tmp_name, $avatar_upload_path)) {
            $avatar_sql = ", avatar_url = :avatar_url";
            $params['avatar_url'] = $avatar_new_name;
        } else {
            $_SESSION['update-profile-error'] = "Failed to upload the avatar.";
            header('Location: ' . ROOT_URL . 'edit-profile.php');
            exit();
        }
    } else {
        $avatar_sql = "";
    }

    // Update query with conditional avatar SQL
    $update_query = "
        UPDATE users 
        SET username = :username, email = :email, profile_info = :profile_info $avatar_sql
        WHERE id = :id
    ";

    try {
        $stmt = $pdo->prepare($update_query);
        $stmt->execute($params);

        $_SESSION['update-profile-success'] = "Profile updated successfully.";
        header('Location: ' . ROOT_URL . 'dashboard.php');
        exit();
    } catch (PDOException $e) {
        $_SESSION['update-profile-error'] = "Error updating profile: " . $e->getMessage();
        header('Location: ' . ROOT_URL . 'edit-profile.php');
        exit();
    }
}
?>
