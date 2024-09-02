<?php
session_start();
require '../config/database.php';

// Enable detailed error reporting
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

$transactionStarted = false;

try {
    // Enable error reporting for PDO
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Ensure this script only runs on POST requests to avoid accidental deletions via GET
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id'])) {
        $id = filter_var($_POST['id'], FILTER_SANITIZE_NUMBER_INT);

        if (!$id) {
            throw new Exception("Invalid user ID.");
        }

        // Begin transaction
        $pdo->beginTransaction();
        $transactionStarted = true;  // Transaction has started

        // Fetch user from database
        $query = "SELECT * FROM users WHERE id = ?";
        $stmt = $pdo->prepare($query);
        $stmt->execute([$id]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user) {
            // Output for debugging (remove in production)
            echo "User found: " . $user['username'] . "<br>";

            // Delete user avatar if it exists
            $avatar_name = $user['avatar_url'];
            $avatar_path = '../images/' . $avatar_name;

            if (file_exists($avatar_path)) {
                if (unlink($avatar_path)) {
                    echo "Avatar deleted.<br>";
                } else {
                    echo "Failed to delete avatar.<br>";
                }
            } else {
                echo "Avatar file does not exist.<br>";
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

                // For the followers table, we need to bind the ID twice since we have two placeholders
                if ($table === 'followers') {
                    if ($stmt->execute([$id, $id])) {
                        echo ucfirst($table) . " deleted: " . $stmt->rowCount() . " rows.<br>";
                    } else {
                        echo "Failed to delete from " . $table . ".<br>";
                    }
                } else {
                    if ($stmt->execute([$id])) {
                        echo ucfirst($table) . " deleted: " . $stmt->rowCount() . " rows.<br>";
                    } else {
                        echo "Failed to delete from " . $table . ".<br>";
                    }
                }
            }

            // Now delete the user
            $delete_user_query = "DELETE FROM users WHERE id = ?";
            $stmt_user = $pdo->prepare($delete_user_query);
            if ($stmt_user->execute([$id])) {
                echo "User deleted.<br>";
                $pdo->commit(); // Commit transaction
                $_SESSION['delete-user-success'] = "{$user['username']} deleted successfully";
            } else {
                throw new Exception("Couldn't delete '{$user['username']}'");
            }
        } else {
            throw new Exception("User not found.");
        }
    } else {
        throw new Exception("Invalid request method or missing user ID.");
    }
} catch (Exception $e) {
    // Only attempt rollback if the transaction was started
    if ($transactionStarted) {
        $pdo->rollBack();
    }
    echo "Error: " . $e->getMessage() . "<br>";
    $_SESSION['delete-user'] = "Error: " . $e->getMessage();
}

// Remove or uncomment the header redirect after debugging
 header('Location: ' . ROOT_URL . 'admin/manage-users.php');
 exit();
