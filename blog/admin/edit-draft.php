<?php
session_start();
require '../config/database.php';
require '../config/constants.php';

// Clear session data if coming from the dashboard
if (isset($_SERVER['HTTP_REFERER']) && strpos($_SERVER['HTTP_REFERER'], 'dashboard.php') !== false) {
    unset($_SESSION['edit-draft-data']);
    unset($_SESSION['flagged-words']);
}

// Ensure the user is logged in
if (!isset($_SESSION['user-id'])) {
    header('Location: ' . ROOT_URL . 'signin.php');
    exit();
}

// Get the post ID from the URL
$post_id = filter_var($_GET['id'], FILTER_SANITIZE_NUMBER_INT);

// Fetch the draft from the database
$query = "SELECT * FROM blog_posts WHERE id = :post_id AND status = 'draft' AND user_id = :user_id";
$stmt = $pdo->prepare($query);
$stmt->execute(['post_id' => $post_id, 'user_id' => $_SESSION['user-id']]);
$post = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$post) {
    $_SESSION['edit-draft'] = "Draft not found.";
    header('Location: ' . ROOT_URL . 'admin/manage-drafts.php');
    exit();
}

// Fetch categories from the database using PDO
$query = "SELECT * FROM categories";
$stmt = $pdo->prepare($query);
$stmt->execute();
$categories = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Fetch the current user's username and avatar URL
$user_id = $_SESSION['user-id'];
$user_query = "SELECT username, avatar_url FROM users WHERE id = ?";
$user_stmt = $pdo->prepare($user_query);
$user_stmt->execute([$user_id]);
$user = $user_stmt->fetch(PDO::FETCH_ASSOC);

$username = $user ? htmlspecialchars($user['username'], ENT_QUOTES, 'UTF-8') : 'Unknown User';
$avatarUrl = !empty($user['avatar_url']) ? htmlspecialchars($user['avatar_url'], ENT_QUOTES, 'UTF-8') : 'default-avatar.png';

// Load form data from the database if it exists
$title = isset($post['title']) ? htmlspecialchars($post['title'], ENT_QUOTES, 'UTF-8') : '';
$category = isset($post['category_id']) ? (int)$post['category_id'] : '';
$body = isset($post['content']) ? htmlspecialchars_decode($post['content'], ENT_QUOTES) : '';
$is_featured = isset($post['is_featured']) && $post['is_featured'] ? 'checked' : '';
$restricted_to_followers = isset($post['restricted_to_followers']) && $post['restricted_to_followers'] ? 'checked' : '';
$previous_thumbnail = htmlspecialchars($post['image_url'], ENT_QUOTES, 'UTF-8');
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Draft</title>
    <link href="https://cdn.quilljs.com/1.3.6/quill.snow.css" rel="stylesheet">
    <style>
        /* General body styling */
        body {
            font-family: 'Montserrat', sans-serif;
            color: #6f6af8;
            background: white;
            display: flex;
            flex-direction: column;
            min-height: 100vh;
        }

        .container {
            width: 80%;
            margin-inline: auto;
            flex: 1;
            padding-bottom: calc(50px + 2rem);
            padding-left: 20px;
        }

        .form__section {
            display: flex;
            flex-direction: column;
            align-items: center;
            padding-top: 50px;
        }

        .form__section-container {
            width: 100%;
            padding-bottom: 7rem;
        }

        .title-input {
            width: 85%;
            padding: 0.5rem 1rem;
            border: none;
            border-radius: 0.5rem;
            font-size: 1.2rem;
            margin-bottom: 1rem;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }

        #editor-container {
            height: 500px;
            border: 2px solid #0000ff;
            border-radius: 0.5rem;
            overflow-y: auto;
            width: 85%;
        }

        select {
            width: 15%;
            padding: 0.5rem 1rem;
            border: 1px solid #0000ff;
            border-radius: 0.5rem;
            font-size: 1.1rem;
            margin-bottom: 1.5rem;
        }

        .file-upload-container {
            display: flex;
            flex-direction: column;
            gap: 1rem;
            align-items: flex-start;
            margin-top: 1.5rem;
        }

        .file-upload-container label {
            font-weight: bold;
            color: #5854c7;
        }

        .file-upload-container input[type="file"] {
            padding: 0.4rem;
            border-radius: 0.5rem;
            border: 1px solid #0000ff;
            width: 300px;
        }

        .file-upload-container img {
            max-width: 150px;
            border-radius: 0.5rem;
            margin-top: 1rem;
        }

        .btn {
            display: inline-block;
            padding: 0.7rem 1.5rem;
            font-size: 1rem;
            font-weight: bold;
            color: #fff;
            background-color: #6f6af8;
            border: none;
            border-radius: 0.3rem;
            cursor: pointer;
            transition: all 300ms ease;
            box-shadow: 0px 4px 6px rgba(0, 0, 0, 0.1);
            text-align: center;
            min-width: 120px;
            margin-right: 10px;
            margin-top: 1.5rem;
        }

        .btn:hover {
            background-color: #5854c7;
        }

        @media screen and (max-width: 600px) {
            .title-input, #editor-container, select {
                font-size: 1rem;
                width: 100%;
            }

            .btn {
                padding: 0.6rem 1.2rem;
                font-size: 0.9rem;
            }
        }

        /* Header styling specific to edit-draft.php */
        .o-navbar {
            background-color: #ffffff;
            padding: 1rem 2rem;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
            z-index: 1000;
            position: relative;
            display: flex;
            align-items: center;
            justify-content: space-between;
            transition: background-color 0.3s ease, box-shadow 0.3s ease;
        }

        .nav__logo {
            font-weight: bold;
            font-size: 1.8rem;
            color: #6f6af8;
            text-decoration: none;
            margin-left: 0;
            text-shadow: 1px 1px 3px rgba(0, 0, 0, 0.1);
            transition: color 0.3s ease;
            cursor: pointer; /* Added for better UX */
        }

        .nav__logo:hover {
            color: #5854c7;
        }

        .avatar {
            position: relative;
        }

        .avatar img {
            width: 36px;
            height: 36px;
            border-radius: 50%;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.15);
            transition: box-shadow 0.3s ease, transform 0.3s ease;
        }

        .avatar img:hover {
            box-shadow: 0 6px 15px rgba(0, 0, 0, 0.25);
            transform: scale(1.1);
        }

        .nav__profile ul {
            position: absolute;
            top: 120%;
            right: 0;
            display: flex;
            flex-direction: column;
            background-color: #ffffff;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.2);
            visibility: hidden;
            opacity: 0;
            transition: all 300ms ease;
            z-index: 1100;
            border-radius: 8px;
            overflow: hidden;
            min-width: 150px;
        }

        .nav__profile:hover > ul {
            visibility: visible;
            opacity: 1;
        }

        .nav__profile ul li {
            border-bottom: 1px solid #e0e0e0;
        }

        .nav__profile ul li:last-child {
            border-bottom: none;
        }

        .nav__profile ul li a {
            padding: 10px 15px;
            background: #ffffff;
            display: block;
            width: 100%;
            color: #333;
            text-decoration: none;
            transition: background-color 0.3s ease, color 0.3s ease;
            font-size: 0.9rem;
            font-family: 'Montserrat', sans-serif;
        }

        .nav__profile ul li a:hover {
            background-color: #f0f0f0;
            color: #6f6af8;
        }

        .ql-toolbar {
            width: 85%; /* Match the width of the editor container */
            border: 2px solid #0000ff; /* Match the border style */
            border-bottom: none; /* Remove bottom border to seamlessly attach to the editor */
            border-radius: 0.5rem 0.5rem 0 0; /* Rounded corners on top only */
            margin: auto; /* Center the toolbar */
            margin-bottom: -2px;
            margin-left: 2px; /* Overlap the editor border */
        }

        img {
            max-width: 100%;
            height: auto;
        }
    </style>
</head>
<body>

<!-- Custom header for edit-draft.php -->
<nav class="o-navbar">
    <a href="<?= (isset($_SESSION['user_is_admin']) && $_SESSION['user_is_admin']) ? ROOT_URL . 'admin/index.php' : ROOT_URL . 'index.php' ?>" class="nav__logo">Dialogue</a>
    <?php if (isset($_SESSION['user-id'])): ?>
        <div class="nav__profile">
            <div class="avatar">
                <img src="<?= ROOT_URL ?>images/<?= $avatarUrl ?>" alt="User Avatar">
            </div>
            <ul>
                <li><a href="<?= ROOT_URL ?>admin/dashboard.php">Dashboard</a></li>
                <li><a href="<?= ROOT_URL ?>logout.php">Logout</a></li>
            </ul>
        </div>
    <?php endif; ?>
</nav>

<section class="form__section">
    <div class="container form__section-container">
        <h2>Edit Draft</h2>
        <div id="error-message" class="alert__message error" style="display:none;"></div>
        <?php if (isset($_SESSION['edit-draft'])) : ?>
            <div class="alert__message error">
                <p>
                    <?= htmlspecialchars($_SESSION['edit-draft'], ENT_QUOTES, 'UTF-8');
                    unset($_SESSION['edit-draft']);
                    ?>
                </p>
            </div>
        <?php endif ?>
        <form id="post-form" action="<?= ROOT_URL ?>admin/edit-draft-logic.php" enctype="multipart/form-data" method="POST">
            <input type="hidden" name="id" value="<?= $post_id ?>">
            <input type="hidden" name="previous_thumbnail_name" value="<?= $previous_thumbnail ?>">
            <input type="text" name="title" id="title" class="title-input" placeholder="Title" value="<?= $title ?>">
            <div class="form__control inline">
                <select name="category" id="category">
                    <?php foreach ($categories as $category_option) : ?>
                        <option value="<?= $category_option['id'] ?>" <?= $category_option['id'] == $category ? 'selected' : '' ?>><?= htmlspecialchars($category_option['name'], ENT_QUOTES) ?></option>
                    <?php endforeach ?>
                </select>
            </div>
            <div id="editor-container"><?= $body ?></div>
            <textarea name="content" id="content" style="display:none;"><?= htmlspecialchars($body, ENT_QUOTES, 'UTF-8') ?></textarea>
            <div class="file-upload-container">
                <label for="thumbnail">Current Thumbnail</label>
                <?php if ($previous_thumbnail) : ?>
                    <img src="<?= ROOT_URL ?>images/<?= $previous_thumbnail ?>" alt="Thumbnail" width="150">
                <?php endif; ?>
                <label for="thumbnail">Change Thumbnail</label>
                <input type="file" name="thumbnail" id="thumbnail">
            </div>
            <div class="form__control inline">
                <input type="checkbox" name="is_featured" id="is_featured" value="1" <?= $is_featured ?>>
                <label for="is_featured">Featured</label>
                <input type="checkbox" name="restricted_to_followers" id="restricted_to_followers" value="1" <?= $restricted_to_followers ?>>
                <label for="restricted_to_followers">Restrict to followers only</label>
            </div>
            <div class="form__control inline">
                <button type="submit" name="action" value="update_draft" class="btn">Update Draft</button>
                <button type="submit" name="action" value="post_draft" class="btn">Post Draft</button>
                <button type="button" id="preview-button" class="btn">Preview</button>
            </div>
        </form>
    </div>
</section>

<!-- Include Quill library after header.php -->
<link href="https://cdn.quilljs.com/1.3.6/quill.snow.css" rel="stylesheet">
<script src="https://cdn.quilljs.com/1.3.6/quill.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/quill-image-drop-module@1.0.3/image-drop.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/quill-image-resize-module@3.0.0/image-resize.min.js"></script>

<script>
    var quill = new Quill('#editor-container', {
        theme: 'snow',
        modules: {
            toolbar: [
                [{ 'header': '1'}, {'header': '2'}, { 'font': [] }],
                [{ 'list': 'ordered'}, { 'list': 'bullet' }],
                ['bold', 'italic', 'underline'],
                ['image'],
                [{ 'align': [] }],
            ],
            imageDrop: true,
            imageResize: {
                modules: ['Resize', 'DisplaySize', 'Toolbar']
            }
        }
    });

    document.addEventListener("DOMContentLoaded", function() {
        <?php if ($body) : ?>
        quill.root.innerHTML = <?= json_encode($body) ?>;
        <?php endif ?>
    });

    document.getElementById('post-form').addEventListener('submit', function() {
        document.querySelector('textarea[name=content]').value = quill.root.innerHTML;
    });

    document.getElementById('preview-button').addEventListener('click', function() {
        var title = document.querySelector('input[name=title]').value;
        var body = quill.root.innerHTML; // Capture content including styles
        var thumbnail = document.getElementById('thumbnail').files[0];
        var reader = new FileReader();

        reader.onloadend = function() {
            var previewWindow = window.open('', 'Preview', 'width=1200,height=600');
            previewWindow.document.write('<html><head><title>Preview</title>');
            previewWindow.document.write('<link rel="stylesheet" href="https://cdn.quilljs.com/1.3.6/quill.snow.css">');
            previewWindow.document.write('<link rel="stylesheet" type="text/css" href="<?= ROOT_URL ?>css/style.css">');
            previewWindow.document.write('<style>');

            previewWindow.document.write(`
            body {
                font-family: 'Montserrat', sans-serif;
                color: #6f6af8;
                background:#F8F9FA;
                margin: 0;
                padding-top: var(--header-height);
            }

            header {
                position: fixed;
                top: 0;
                left: 0;
                width: 100%;
                height: var(--header-height);
                background: var(--color-primary);
                z-index: 1000;
                display: flex;
                align-items: center;
                padding: 0 20px;
                box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
            }

            .content {
                display: flex;
                justify-content: space-between;
                max-width: 1200px;
                margin: 1rem auto;
                padding: 0 10px;
            }

            .singlepost__container {
                width: 65%;
                padding: 1.5rem;
                border-radius: var(--card-border-radius-2);
                background-color: #fff;
                box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
                margin-top: 1rem;
                position: relative;
            }

            .singlepost__thumbnail {
        position: relative;
        border-radius: var(--card-border-radius-2);
        background: var(--color-background);
        box-shadow: 0 3px 6px rgba(0, 0, 0, 0.1);
        max-width: 30%;
        margin: 0 auto 1rem auto;
        transition: transform 0.3s ease-in-out;
    }
    .singlepost__thumbnail img {
        width: 100%;
        height: 100px; /* Fixed height for all thumbnails */
        object-fit: cover; /* Ensures the image covers the entire thumbnail area while maintaining aspect ratio */
        border-radius: var(--card-border-radius-2);
        transition: transform 0.3s ease-in-out;
    }
            .post__author-info {
                margin-bottom: 1rem; /* Added margin to create space between author info (including the follow button) and the thumbnail */
            }

            .singlepost__thumbnail img:hover {
                transform: scale(1.05);
            }

            /* Custom overrides for Quill editor images */
            img{
           display: block;
                width: 80%; /* Ensure full width for images */
                height: 300px; /* Maintain aspect ratio */
                max-height: 250px; /* Limit maximum height */
                object-fit: cover; /* Ensures the image covers the entire thumbnail area while maintaining aspect ratio */
                border-radius: var(--card-border-radius-2); /* Match the border-radius */
                box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2); /* Add shadow for a subtle depth effect */
                margin-bottom: 1rem; /* Adds spacing below the image */
            }


            .post__author-avatar img {
                width: 50px;
                height: 50px;
                border-radius: 50%;
            }

            .post__author-info h5,
            .post__author-info small {
                margin: 0;
            }

            .singlepost__container h2 {
                margin-top: 0;
                margin-bottom: 1rem;
                color: var(--color-primary);
            }

            .singlepost__container p {
                line-height: 1.8;
                color: var(--color-primary-variant);
            }
        `);

            previewWindow.document.write('</style>');
            previewWindow.document.write('</head><body>');
            previewWindow.document.write('<div class="singlepost__container">');
            previewWindow.document.write('<h2>' + title + '</h2>');
            previewWindow.document.write('<div class="post-author-info">');
            previewWindow.document.write('<h5>By: <?= $username ?></h5>');
            previewWindow.document.write('<small>Just now</small>');
            previewWindow.document.write('</div>');
            previewWindow.document.write('<div class="singlepost__thumbnail"><img src="' + reader.result + '" alt="Thumbnail"></div>');
            previewWindow.document.write('<div>' + body + '</div>'); // The body content with images from Quill
            previewWindow.document.write('</div>');
            previewWindow.document.write('</body></html>');
            previewWindow.document.close();
        }

        if (thumbnail) {
            reader.readAsDataURL(thumbnail);
        } else {
            var previewWindow = window.open('', 'Preview', 'width=800,height=600');
            previewWindow.document.write('<html><head><title>Preview</title>');
            previewWindow.document.write('<link rel="stylesheet" href="https://cdn.quilljs.com/1.3.6/quill.snow.css">');
            previewWindow.document.write('<link rel="stylesheet" type="text/css" href="<?= ROOT_URL ?>css/style.css">');
            previewWindow.document.write('<style>');

            previewWindow.document.write(`
            body {
                font-family: 'Montserrat', sans-serif;
                color: #6f6af8;
                background:#F8F9FA;
                margin: 0;
                padding-top: var(--header-height);
                padding-left:100px
            }

            header {
                position: fixed;
                top: 0;
                left: 0;
                width: 100%;
                height: var(--header-height);
                background: var(--color-primary);
                z-index: 1000;
                display: flex;
                align-items: center;
                padding: 0 20px;
                box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
            }

            .content {
                display: flex;
                justify-content: space-between;
                max-width: 1200px;
                margin: 1rem auto;
                padding: 0 10px;
            }

            .singlepost__container {
                width: 40%;
                padding: 1.5rem;
                border-radius: var(--card-border-radius-2);
                background-color: #fff;
                box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
                margin-top: 1rem;
                position: relative;
            }

            .singlepost__thumbnail {
                position: relative;
                border-radius: var(--card-border-radius-2);
                background: var(--color-background);
                box-shadow: 0 3px 6px rgba(0, 0, 0, 0.1);
                max-width: 30%;
                margin: 0 auto 1rem auto;
                transition: transform 0.3s ease-in-out;
            }

            .singlepost__thumbnail img {
                width: 100%;
                height: 100px; /* Fixed height for all thumbnails */
                object-fit: cover; /* Ensures the image covers the entire thumbnail area while maintaining aspect ratio */
                border-radius: var(--card-border-radius-2);
                transition: transform 0.3s ease-in-out;
            }

            .post__author-info {
                margin-bottom: 1rem; /* Added margin to create space between author info (including the follow button) and the thumbnail */
            }

            .singlepost__thumbnail img:hover {
                transform: scale(1.05);
            }

            /* Custom overrides for Quill editor images */
             img {
                display: block;
                width: 80%; /* Ensure full width for images */
                height: 300px; /* Maintain aspect ratio */
                max-height: 250px; /* Limit maximum height */
                object-fit: cover; /* Ensures the image covers the entire thumbnail area while maintaining aspect ratio */
                border-radius: var(--card-border-radius-2); /* Match the border-radius */
                box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2); /* Add shadow for a subtle depth effect */
                margin-bottom: 1rem; /* Adds spacing below the image */
            }

            .post__author-avatar img {
                width: 50px;
                height: 50px;
                border-radius: 50%;
            }

            .post__author-info h5,
            .post__author-info small {
                margin: 0;
            }

            .singlepost__container h2 {
                margin-top: 0;
                margin-bottom: 1rem;
                color: var(--color-primary);
            }

            .singlepost__container p {
                line-height: 1.8;
                color: var(--color-primary-variant);
            }
        `);

            previewWindow.document.write('</style>');
            previewWindow.document.write('</head><body>');
            previewWindow.document.write('<div class="singlepost__container">');
            previewWindow.document.write('<h2>' + title + '</h2>');
            previewWindow.document.write('<div class="post-author-info">');
            previewWindow.document.write('<h5>By: <?= $username ?></h5>');
            previewWindow.document.write('<small>Just now</small>');
            previewWindow.document.write('</div>');
            previewWindow.document.write('<div class="singlepost__thumbnail"><img src="" alt="Thumbnail"></div>');
            previewWindow.document.write('<div>' + body + '</div>'); // The body content with images from Quill
            previewWindow.document.write('</div>');
            previewWindow.document.write('</body></html>');
            previewWindow.document.close();
        }
    });
    // Auto-save draft using AJAX

    let autoSaveInterval;
    let draftStartTime = null;

    function startAutoSave() {
        document.getElementById('post-form').addEventListener('input', function() {
            draftStartTime = draftStartTime || Date.now();
            if (!autoSaveInterval) {
                autoSaveInterval = setInterval(() => {
                    if (draftStartTime && Date.now() - draftStartTime >= 900000) { // 15 minutes in milliseconds
                        autoSaveDraft();
                    }
                }, 60000); // Check every 1 minute
            }
        });
    }

    function autoSaveDraft() {
        if (!document.getElementById('post-form').getAttribute('data-is-published')) {
            var formData = new FormData();
            formData.append('title', document.getElementById('title').value);
            formData.append('category', document.getElementById('category').value);
            formData.append('body', quill.root.innerHTML);
            formData.append('is_featured', document.getElementById('is_featured').checked ? 1 : 0);
            formData.append('restricted_to_followers', document.getElementById('restricted_to_followers').checked ? 1 : 0);
            formData.append('user_id', <?= $user_id ?>);

            fetch('<?= ROOT_URL ?>auto-save-draft.php', {
                method: 'POST',
                body: formData
            })
                .then(response => response.json())
                .then(data => {
                    console.log('Draft saved', data);
                })
                .catch(error => {
                    console.error('Error saving draft:', error);
                    // Optional: Show an error message to the user
                });
        }
    }

    window.addEventListener('beforeunload', function(event) {
        if (draftStartTime && Date.now() - draftStartTime >= 900000) { // Save draft only if 15 minutes have passed
            autoSaveDraft();
        }
        event.returnValue = '';
    });

    window.addEventListener('beforeunload', function() {
        sessionStorage.removeItem('postFormData');
    });

    // Start the autosave process
    startAutoSave();
</script>
<?php include '../partials/footer.php' ?>
</body>
</html>
