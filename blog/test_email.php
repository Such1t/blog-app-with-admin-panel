<?php
$to = 'suchitmashelkar11@gmail.com';  // Replace with your email address to test
$subject = 'Test Email';
$message = 'This is a test email sent from PHP on XAMPP.';
$headers = 'From: suchitmashelkar11@gmail.com' . "\r\n" .
    'Reply-To: suchitmashelkar11@gmail.com' . "\r\n" .
    'X-Mailer: PHP/' . phpversion();

if (mail($to, $subject, $message, $headers)) {
    echo 'Email sent successfully.';
} else {
    echo 'Failed to send email.';
}
?>
