<?php
function display_comments($comments, $post_owner_id, $parent_id = NULL) {
    global $userId, $isAdmin;

    foreach ($comments as $comment) {
        if ($comment['parent_id'] == $parent_id) {
            $reply_count = array_reduce($comments, function($count, $reply) use ($comment) {
                return $count + ($reply['parent_id'] == $comment['id'] ? 1 : 0);
            }, 0);
            echo '<div class="comment" data-comment-id="' . $comment['id'] . '">';
            echo '<p><strong>' . htmlspecialchars($comment['username']) . ':</strong> ' . htmlspecialchars($comment['content']) . '</p>';
            echo '<div style="display: flex; align-items: center;">';
            echo '<a href="#" class="reply-link" data-comment-id="' . $comment['id'] . '">Reply (' . $reply_count . ')</a>';
            echo '<button class="delete-comment" data-comment-id="' . $comment['id'] . '">Delete</button>';
            echo '</div>';
            echo '<div class="reply-form-container" style="display: none;"></div>'; // Add reply form container here
            echo '<div class="comment-reply-container" style="display: none;">';
            display_comments($comments, $post_owner_id, $comment['id']);
            echo '</div>';
            echo '</div>';
        }
    }
}
?>