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

// Use default avatar if the user avatar is not set in the session
$avatarUrl = $isLoggedIn && isset($_SESSION['user_avatar']) ? $_SESSION['user_avatar'] : 'default_avatar.jpg';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Panel</title>
    <link rel="stylesheet" href="<?= ROOT_URL ?>css/style.css">
    <!-- ICONSCOUT CDN -->
    <link rel="stylesheet" href="https://unicons.iconscout.com/release/v4.0.0/css/line.css">
    <!-- GOOGLE FONT (MONTSERRAT) -->
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
    <style>
        body {
            margin: 0;
            padding: 0;
            font-family: 'Montserrat', sans-serif;
        }

        /* Sidebar styling */
        aside {
            width: 250px;
            position: fixed;
            top: 0;
            left: 0;
            bottom: 0;
            background-color: #ffffff;
            box-shadow: 2px 0 12px rgba(0, 0, 0, 0.1);
            z-index: 1000;
            padding: 1rem;
        }

        /* Adjust the main content to account for the sidebar */
        .main-content {
            margin-left: 250px; /* Adjust this to match the width of the sidebar */
            padding: 1rem;
        }

        /* Navbar Styles */
        nav {
            background-color: white;
            padding: 1rem 2rem;
            box-shadow: 0 4px 16px rgba(0, 0, 0, 0.1);
            z-index: 1000;
            position: fixed;
            top: 0;
            left: 250px;
            width: calc(100% - 250px);
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-bottom: 2px solid #6f6af8;
            transition: box-shadow 0.3s ease, background-color 0.3s ease;
        }

        nav:hover {
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.15);
            background-color: #f9f9fb;
        }

        nav a {
            color: #6f6af8;
            text-decoration: none;
            font-weight: 600;
            transition: color 0.3s ease;
        }

        nav a:hover {
            color: #5854c7;
        }

        nav .logo {
            font-size: 1.8rem;
            font-weight: 700;
            color: #6f6af8;
        }

        nav .menu {
            display: flex;
            gap: 2rem;
        }

        nav .menu-item {
            position: relative;
        }

        nav .menu-item:hover .dropdown {
            visibility: visible;
            opacity: 1;
            transform: translateY(0);
        }

        .dropdown {
            visibility: hidden;
            opacity: 0;
            position:absolute;
            top: 100%;
            left: 0;
            background-color: white;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
            border-radius: 8px;
            transition: all 0.3s ease;
            transform: translateY(-10px);
            z-index: 1001;
        }

        .dropdown a {
            padding: 0.75rem 1.5rem;
            display: block;
            color: #6f6af8;
            font-size: 1rem;
        }

        .dropdown a:hover {
            background-color: #f1f1f9;
            color: #5854c7;
        }

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
            background-color: #ffffff;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.2);
            visibility: hidden;
            opacity: 0;
            transition: all 300ms ease;
            z-index: 1100;
            border-radius: 8px;
            overflow: hidden;
            min-width: 200px;
        }

        .nav__profile ul li {
            border-bottom: 1px solid #e0e0e0;
        }

        .nav__profile ul li:last-child {
            border-bottom: none;
        }

        .nav__profile ul li a {
            padding: 15px 20px;
            background: #ffffff;
            display: block;
            width: 100%;
            color: #333;
            text-decoration: none;
            transition: background-color 0.3s ease, color 0.3s ease;
            font-size: 1rem;
            font-family: 'Montserrat', sans-serif;
        }

        .nav__profile ul li a:hover {
            background-color: #f0f0f0;
            color: #6f6af8;
        }

        .nav__profile:hover > ul {
            visibility: visible;
            opacity: 1;
            top: 120%;
        }

        @media (max-width: 768px) {
            nav {
                padding: 1rem;
                left: 0;
                width: 100%;
            }

            .menu {
                display: none;
            }

            .menu.active {
                display: flex;
                flex-direction: column;
                gap: 1rem;
            }

            .menu-item {
                display: flex;
                justify-content: center;
            }

            .dropdown {
                left: 50%;
                transform: translate(-50%, 0);
                width: max-content;
            }
        }

        nav.scrolled {
            box-shadow: 0 6px 20px rgba(0, 0, 0, 0.15);
        }

        .nav__items {
            display: flex;
            align-items: center;
            gap: 4rem;
            justify-content: flex-end;
        }

        .nav__profile {
            display: flex;
            align-items: center;
            position: relative;
        }

        .nav__profile .avatar img {
            width: 36px;
            height: 36px;
            border-radius: 50%;
        }

        .nav__profile ul {
            list-style: none;
            margin: 0;
            padding: 0;
            position:absolute;
            top: 100%;
            right: 0;
            background-color: white;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
            border-radius: 8px;
            display: none;
        }

        .nav__profile ul li {
            margin: 0;
            padding: 0;
        }

        .nav__profile ul li a {
            display: block;
            padding: 1rem;
            color: #6f6af8;
            text-decoration: none;
        }

        .nav__profile:hover ul {
            display: block;
        }

        /* Additional Links */
        .nav__items li a {
            color: #6f6af8;
            text-decoration:none;
            font-family: 'Montserrat', sans-serif;
            font-weight: 500;
            font-size: 1rem;
        }

        .nav__items li a:hover {
            color: #5854c7;
            text-decoration: underline;
        }

        /* Responsive adjustments for mobile */
        @media screen and (max-width: 768px) {
            nav {
                width: 100%;
                left: 0;
                padding-left: 10px;
            }

            .main-content {
                margin-left: 0;
            }

            aside {
                width: 100%;
                position: static;
            }

            .nav__items {
                display: flex;
                align-items: center;
                gap: 4rem;
                margin-right: 2rem;
            }

            .nav__items.active {
                display: flex;
            }

            #open__nav-btn {
                display: inline-block;
            }

            #close__nav-btn {
                display: none;
            }

            #close__nav-btn.active {
                display: inline-block;
            }

            #open__nav-btn.active {
                display: none;
            }
        }

        /* Toggle button styles */
        #open__nav-btn,
        #close__nav-btn {
            background: none;
            border: none;
            cursor: pointer;
            font-size: 1.5rem;
            display: none;
        }

        <style>
         .nav__container {
             height: 100%;
             display: flex;
             align-items: center;
             justify-content: flex-end; /* Keep items aligned to the right */
             margin-left: auto; /* Push everything to the right */
             width: calc(100% - 50px); /* Adjust this width to control how far right items go */
         }

        .nav__items {
            margin-right:-600px; /* Fine-tune the position of nav items */
        }
    </style>

    </style>
</head>
<body>

<!-- Navbar -->
<nav class="o-navbar">
    <div class="container nav__container">
        <button id="open__nav-btn"><i class="uil uil-bars"></i></button>
        <button id="close__nav-btn"><i class="uil uil-multiply"></i></button>
        <ul class="nav__items">
            <li class="item">
                <a href="<?= ROOT_URL ?>blog.php">
                    <i class="uil uil-search"></i>
                </a>
            </li>
            <li class="item"><a href="<?= ROOT_URL ?>about.php">About</a></li>
            <li class="item"><a href="<?= ROOT_URL ?>services.php">Services</a></li>
            <li class="item"><a href="<?= ROOT_URL ?>contact.php">Contact</a></li>
            <?php if ($isLoggedIn): ?>
                <li class="nav__profile">
                    <div class="avatar">
                        <img src="<?= ROOT_URL ?>images/<?= htmlspecialchars($avatarUrl) ?>?t=<?= time(); ?>" alt="User Avatar">
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
            <?php else: ?>
                <li><a href="<?= ROOT_URL ?>signin.php">Sign In</a></li>
            <?php endif; ?>
        </ul>
    </div>
</nav>
<!--====================== END OF NAV ====================-->



    <script>
        // JavaScript to toggle menu on mobile
        const openNavBtn = document.getElementById('open__nav-btn');
        const closeNavBtn = document.getElementById('close__nav-btn');
        const menu = document.querySelector('.nav__items');

        openNavBtn.addEventListener('click', () => {
        menu.classList.add('active');
        openNavBtn.classList.add('active');
        closeNavBtn.classList.add('active');
    });

        closeNavBtn.addEventListener('click', () => {
        menu.classList.remove('active');
        openNavBtn.classList.remove('active');
        closeNavBtn.classList.remove('active');
    });

        // Handle the dropdown to prevent it from disappearing too quickly
        const navProfile = document.querySelector('.nav__profile');
        const dropdown = document.querySelector('.nav__profile .dropdown');

        navProfile.addEventListener('mouseenter', () => {
        dropdown.style.visibility = 'visible';
        dropdown.style.opacity = '1';
        dropdown.style.transform = 'translateY(0)';
    });

        navProfile.addEventListener('mouseleave', () => {
        dropdown.style.visibility = 'hidden';
        dropdown.style.opacity = '0';
        dropdown.style.transform = 'translateY(-10px)';
    });

        dropdown.addEventListener('mouseenter', () => {
        dropdown.style.visibility = 'visible';
        dropdown.style.opacity = '1';
        dropdown.style.transform = 'translateY(0)';
    });

        dropdown.addEventListener('mouseleave', () => {
        dropdown.style.visibility = 'hidden';
        dropdown.style.opacity = '0';
        dropdown.style.transform = 'translateY(-10px)';
    });

</script>

</body>
</html>
