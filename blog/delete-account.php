<?php
session_start();
require 'config/database.php'; // Ensure this path is correct for your setup

// Enable detailed error reporting
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

$transactionStarted = false;

try {
    // Enable error reporting for PDO
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Ensure this script only runs on POST requests and the user is authenticated
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_SESSION['user-id'])) {
        $user_id = $_SESSION['user-id'];

        // Begin transaction
        $pdo->beginTransaction();
        $transactionStarted = true;

        // Fetch user from the database to confirm the ID exists
        $query = "SELECT * FROM users WHERE id = ?";
        $stmt = $pdo->prepare($query);
        $stmt->execute([$user_id]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user) {
            // Delete user avatar if it exists
            $avatar_name = $user['avatar_url'];
            $avatar_path = 'images/' . $avatar_name;

            if (file_exists($avatar_path)) {
                unlink($avatar_path);
            }

            // Delete related records from comments, followers, likes, and blog_posts
            $tables = [
                'comments' => "DELETE FROM comments WHERE user_id = ?",
                'followers' => "DELETE FROM followers WHERE follower_id = ? OR followed_id = ?",
                'likes' => "DELETE FROM likes WHERE user_id = ?",
                'blog_posts' => "DELETE FROM blog_posts WHERE user_id = ?"
            ];

            foreach ($tables as $table => $delete_query) {
                $stmt = $pdo->prepare($delete_query);

                if ($table === 'followers') {
                    $stmt->execute([$user_id, $user_id]);
                } else {
                    $stmt->execute([$user_id]);
                }
            }

            // Now delete the user
            $delete_user_query = "DELETE FROM users WHERE id = ?";
            $stmt_user = $pdo->prepare($delete_user_query);
            if ($stmt_user->execute([$user_id])) {
                $pdo->commit(); // Commit transaction

                // Destroy the session after account deletion
                session_unset();
                session_destroy();

                // Redirect to the signup page after deletion
                header('Location: ' . ROOT_URL . 'signup.php');
                exit();
            } else {
                throw new Exception("Couldn't delete '{$user['username']}'");
            }
        } else {
            throw new Exception("User not found.");
        }
    } else {
        throw new Exception("Invalid request method or you are not logged in.");
    }
} catch (Exception $e) {
    // Only attempt rollback if the transaction was started
    if ($transactionStarted) {
        $pdo->rollBack();
    }
    $_SESSION['delete-account-error'] = "Error: " . $e->getMessage();

    // Redirect to the signup page in case of error
    header('Location: ' . ROOT_URL . 'signup.php');
    exit();
}
