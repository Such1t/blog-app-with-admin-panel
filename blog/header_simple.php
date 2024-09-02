<?php
// Ensure session is started only once
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Define the root directory
define('ROOT_DIR', dirname(__DIR__));

// Correct file path for constants and database connection
require_once ROOT_DIR . '/config/constants.php';
require_once ROOT_DIR . '/config/database.php';

$isLoggedIn = isset($_SESSION['user-id']);
$isAdmin = isset($_SESSION['user_is_admin']) && $_SESSION['user_is_admin'];
$avatarUrl = $isLoggedIn && isset($_SESSION['user_avatar']) ? $_SESSION['user_avatar'] : 'default-avatar.jpg'; // Default avatar if not set
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Blog Application</title>
    <link rel="stylesheet" href="<?= ROOT_URL ?>css/style.css">
    <!-- ICONSCOUT CDN -->
    <link rel="stylesheet" href="https://unicons.iconscout.com/release/v4.0.0/css/line.css">
    <!-- GOOGLE FONT (MONTSERRAT) -->
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
    <style>
        /* Simplified navbar */
        .simple-navbar {
            background-color: #ffffff;
            padding: 1rem 2rem;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
            z-index: 1000;
            position: relative;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .nav__logo.logo {
            font-weight: bold;
            font-size: 1.8rem;
            color: #6f6af8;
            text-decoration: none;
        }

        /* Avatar */
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

        /* Dropdown menu */
        .nav__profile {
            position: relative;
            z-index: 1100;
        }

        .nav__profile ul {
            position: absolute;
            top: 140%;
            right: 0;
            display: flex;
            flex-direction: column;
            background-color: #c0c0e8;
            box-shadow: 0 3rem 3rem rgba(0, 0, 0, 0.4);
            visibility: hidden;
            opacity: 0;
            transition: all 300ms ease;
            z-index: 1100;
            border-radius: 0.5rem;
        }

        .nav__profile ul li a {
            padding: 1rem;
            background: #c0c0e8;
            display: block;
            width: 100%;
            color: #6f6af8;
            text-decoration: none;
            transition: background-color 0.3s ease, color 0.3s ease;
        }

        .nav__profile ul li a:hover {
            background-color: #5854c7;
            color: #ffffff;
        }

        .nav__profile:hover > ul {
            visibility: visible;
            opacity: 1;
        }
    </style>
</head>
<body>

<!-- Simplified Navbar -->
<nav class="simple-navbar">
    <a href="<?= ROOT_URL ?>" class="nav__logo logo">Dialogue</a>
    <?php if ($isLoggedIn): ?>
        <li class="nav__profile">
            <div class="avatar">
                <img src="<?= ROOT_URL ?>images/<?= htmlspecialchars($avatarUrl) ?>">
            </div>
            <ul>
                <?php if ($isAdmin): ?>
                    <li><a href="<?= ROOT_URL ?>admin/dashboard.php">Admin Dashboard</a></li>
                <?php else: ?>
                    <li><a href="<?= ROOT_URL ?>/dashboard.php">Your Dashboard</a></li>
                <?php endif; ?>
                <li><a href="<?= ROOT_URL ?>logout.php">Logout</a></li>
            </ul>
        </li>
    <?php endif; ?>
</nav>
<!--====================== END OF NAV ====================-->

</body>
</html>
