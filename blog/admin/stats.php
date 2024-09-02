<?php
// stats.php

// Ensure that $total_users, $total_comments, and $total_likes are set and populated
?>

<h3>Website Statistics</h3>
<table class="dashboard__stats-table">
    <thead>
    <tr>
        <th>Total Users</th>
        <th>Total Comments</th>
        <th>Total Likes</th>
    </tr>
    </thead>
    <tbody>
    <tr>
        <td><?= htmlspecialchars($total_users, ENT_QUOTES, 'UTF-8') ?></td>
        <td><?= htmlspecialchars($total_comments, ENT_QUOTES, 'UTF-8') ?></td>
        <td><?= htmlspecialchars($total_likes, ENT_QUOTES, 'UTF-8') ?></td>
    </tr>
    </tbody>
</table>
