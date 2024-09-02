<?php
session_start();
unset($_SESSION['add-post-data']);
unset($_SESSION['flagged-words']);
session_write_close();
?>

