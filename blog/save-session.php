<?php
session_start();

// Capture the JSON input from the AJAX request
$input = file_get_contents('php://input');
$formData = json_decode($input, true);

// Save the form data into the session
$_SESSION['add-post-data'] = $formData;

// Respond with success
echo json_encode(['status' => 'success']);
?>
