<?php
function display_comments($comments, $post_owner_id, $parent_id = NULL) {
    global $userId, $isAdmin;

    // Precompute reply counts for each comment to avoid recalculating within the loop
    $reply_counts = [];
    foreach ($comments as $comment) {
        if ($comment['parent_id']) {
            if (!isset($reply_counts[$comment['parent_id']])) {
                $reply_counts[$comment['parent_id']] = 0;
            }
            $reply_counts[$comment['parent_id']]++;
        }
    }

    foreach ($comments as $comment) {
        if ($comment['parent_id'] == $parent_id) {
            // Use the precomputed reply count
            $reply_count = isset($reply_counts[$comment['id']]) ? $reply_counts[$comment['id']] : 0;

            echo '<div class="comment" data-comment-id="' . htmlspecialchars($comment['id'], ENT_QUOTES, 'UTF-8') . '">';
            echo '<p><strong>' . htmlspecialchars($comment['username'], ENT_QUOTES, 'UTF-8') . ':</strong> ' . htmlspecialchars($comment['content'], ENT_QUOTES, 'UTF-8') . '</p>';
            echo '<div style="display: flex; align-items: center;">';
            echo '<a href="#" class="reply-link" data-comment-id="' . htmlspecialchars($comment['id'], ENT_QUOTES, 'UTF-8') . '">Reply (' . htmlspecialchars($reply_count, ENT_QUOTES, 'UTF-8') . ')</a>';

            // Show delete button only if the user is the comment owner or an admin
            if ($userId === $comment['user_id'] || $isAdmin) {
                echo '<button class="delete-comment" data-comment-id="' . htmlspecialchars($comment['id'], ENT_QUOTES, 'UTF-8') . '"><i class="uil uil-trash-alt"></i></button>';
            }

            echo '</div>';
            echo '<div class="reply-form-container" style="display: none;"></div>'; // Add reply form container here
            echo '<div class="comment-reply-container" style="display: none;">';
            // Recursively display replies
            display_comments($comments, $post_owner_id, $comment['id']);
            echo '</div>';
            echo '</div>';
        }
    }
}
?>
