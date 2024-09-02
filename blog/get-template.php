<?php
require 'config/database.php';

if (isset($_GET['id'])) {
    $id = filter_var($_GET['id'], FILTER_SANITIZE_NUMBER_INT);
    $query = "SELECT name FROM templates WHERE id=?";
    $stmt = $pdo->prepare($query);
    $stmt->execute([$id]);
    $template = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($template) {
        echo $template['name'];
    } else {
        echo '';
    }
} else {
    echo '';
}
?>

