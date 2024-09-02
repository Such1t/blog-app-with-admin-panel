<?php
require 'vendor/autoload.php';

use Google\Client;

$client = new Client();
$client->setApplicationName("My Application");
$client->setDeveloperKey("YOUR_API_KEY"); // Replace with your actual API key

echo "Google Client Library is working!";
