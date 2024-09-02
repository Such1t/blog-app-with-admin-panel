<?php
if ($_FILES['upload']) {
    // Directory where images will be saved
    $uploadDir = 'uploads/';

    // Create the directory if it doesn't exist
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0755, true);
    }

    // Allowed file types
    $allowedTypes = ['image/jpeg', 'image/png', 'image/gif'];
    $fileType = mime_content_type($_FILES['upload']['tmp_name']);

    // Check if the file type is allowed
    if (in_array($fileType, $allowedTypes)) {
        $fileName = time() . '_' . basename($_FILES['upload']['name']);
        $filePath = $uploadDir . $fileName;

        // Move the uploaded file to the designated directory
        if (move_uploaded_file($_FILES['upload']['tmp_name'], $filePath)) {
            // Return the URL of the uploaded image
            $imageUrl = ROOT_URL . $filePath;
            $funcNum = $_GET['CKEditorFuncNum'];
            echo "<script>window.parent.CKEDITOR.tools.callFunction($funcNum, '$imageUrl', '');</script>";
        } else {
            echo "<script>window.parent.CKEDITOR.tools.callFunction($funcNum, '', 'Failed to upload image.');</script>";
        }
    } else {
        echo "<script>window.parent.CKEDITOR.tools.callFunction($funcNum, '', 'Invalid file type. Only JPG, PNG, and GIF files are allowed.');</script>";
    }
}
?>
