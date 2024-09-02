<?php
require 'config/constants.php';
session_start(); // Ensure the session is started

// Destroy all session variables
session_unset();
session_destroy();

// Redirect to the home page
header('Location: ' . ROOT_URL);
exit();
?>
