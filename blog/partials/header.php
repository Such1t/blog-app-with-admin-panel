<?php
// Ensure session is started only once
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Define the root directory
define('ROOT_DIR', dirname(__DIR__));

// Correct file path for constants and database connection
require_once ROOT_DIR . '/config/constants.php';
require_once ROOT_DIR . '/config/database.php'; // Include the database connection file

// Check if the user is logged in and fetch the latest user data
$isLoggedIn = isset($_SESSION['user-id']);
if ($isLoggedIn) {
    $userId = $_SESSION['user-id'];
    $query = "SELECT avatar_url FROM users WHERE id = :id"; // Updated the column name to avatar_url
    $stmt = $pdo->prepare($query); // Use $pdo instead of $db
    $stmt->bindParam(':id', $userId, PDO::PARAM_INT);
    $stmt->execute();
    $userData = $stmt->fetch();
    $avatarUrl = $userData['avatar_url'] ? $userData['avatar_url'] : 'default_avatar.jpg'; // Updated the column name here as well
} else {
    $avatarUrl = 'default_avatar.jpg';
}

$isAdmin = isset($_SESSION['user_is_admin']) && $_SESSION['user_is_admin'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Blog Application</title>
    <link rel="stylesheet" href="<?= ROOT_URL ?>css/style.css">
    <!-- ICONSCOUT CDN -->
    <link rel="stylesheet" href="https://unicons.iconscout.com/release/v4.0.0/css/line.css">
    <!-- GOOGLE FONT (MONTSERRAT) -->
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
    <style>
        /* o-navbar */
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

        .o-navbar:hover {
            background-color: #f0f2f5;
            box-shadow: 0 6px 20px rgba(0, 0, 0, 0.15);
        }

        /* Logo */
        .nav__logo.logo {
            font-weight: bold;
            font-size: 1.8rem;
            color: #6f6af8;
            text-decoration: none;
            margin-left: 0;
            text-shadow: 1px 1px 3px rgba(0, 0, 0, 0.1);
            transition: color 0.3s ease;
        }

        .nav__logo.logo:hover {
            color: #5854c7;
        }

        /* Menu */
        .menu {
            display: flex;
            flex-direction: row;
            justify-content: flex-end;
            align-items: center;
            gap: 40px;
            margin-left: auto;
        }

        /* Menu items */
        .item a {
            color: #6f6af8;
            text-decoration: none;
            font-family: 'Montserrat', sans-serif;
            font-weight: 500;
            font-size: 1rem;
            text-shadow: 1px 1px 2px rgba(0, 0, 0, 0.1);
            transition: color 0.3s ease, transform 0.3s ease;
        }

        .item a:hover {
            color: #5854c7;
            text-decoration: underline;
            text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.2);
            transform: translateY(-3px);
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

        /* Responsive adjustments for mobile */
        @media screen and (max-width: 768px) {
            .o-navbar {
                flex-direction: column;
                align-items: flex-start;
            }

            .menu {
                flex-direction: column;
                align-items: flex-start;
                gap: 20px;
                display: none;
            }

            .menu.active {
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

        /* Modal Styles */
        .modal {
            display: none;
            position: fixed;
            z-index: 2000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            overflow: auto;
            background-color: rgba(0, 0, 0, 0.5); /* Semi-transparent background */
            padding-top: 60px;
            align-items: center;
            justify-content: center;
        }

        .modal-content {
            background-color: #ffffff;
            margin: auto;
            padding: 30px;
            border: none;
            width: 80%;
            max-width: 450px; /* Slightly reduced max-width for a more compact feel */
            border-radius: 12px; /* Increased border-radius for smoother corners */
            text-align: center;
            box-shadow: 0 6px 30px rgba(0, 0, 0, 0.3); /* Deeper shadow for more depth */
            animation: fadeIn 0.4s ease-out; /* Smooth fade-in animation */
        }

        .modal-content p {
            font-size: 1.15rem;
            color: #333;
            margin-bottom: 20px;
            line-height: 1.6;
            font-weight: 500; /* Slightly bolder text for readability */
        }

        .modal-close,
        .modal-confirm {
            background-color: #6f6af8;
            color: white;
            padding: 12px 25px;
            border: none;
            cursor: pointer;
            border-radius: 8px; /* Increased border-radius for button softness */
            font-size: 1rem;
            margin: 10px;
            transition: background-color 0.3s ease, transform 0.3s ease;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1); /* Added box-shadow for button depth */
        }

        .modal-close:hover,
        .modal-confirm:hover {
            background-color: #5854c7;
            transform: scale(1.05);
        }

        .modal-confirm {
            background-color: #f44336; /* Red background for emphasis on danger action */
        }

        .modal-confirm:hover {
            background-color: #d32f2f;
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(-10px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .modal-confirm {
            background-color: #d32f2f;
            color: white;
            padding: 12px 25px;
            border: none;
            cursor: pointer;
            border-radius: 8px; /* Increased border-radius for button softness */
            font-size: 1rem;
            margin: 10px;
            transition: background-color 0.3s ease, transform 0.3s ease;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1); /* Added box-shadow for button depth */
        }

        #deleteAccountForm {
            margin-top: 20px; /* Add some space above the form */
            text-align: center; /* Center the form contents */
        }

        #deleteAccountForm .modal-confirm {
            background-color: #f44336; /* Red background for delete action */
            color: white;
            padding: 12px 25px;
            border: none;
            cursor: pointer;
            border-radius: 8px; /* Rounded corners for a softer look */
            font-size: 1rem;
            margin: 0 auto; /* Center the button horizontally */
            transition: background-color 0.3s ease, transform 0.3s ease;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1); /* Slight shadow for depth */
            display: inline-block; /* Ensure the button is inline-block */
        }

        #deleteAccountForm .modal-confirm:hover {
            background-color: #d32f2f; /* Darker red on hover */
            transform: scale(1.05); /* Slight scaling effect on hover */
        }

    </style>
</head>
<body>

<!-- Navbar -->
<nav class="o-navbar">
    <a href="<?= ROOT_URL . ($isAdmin ? 'admin/index.php' : 'index.php') ?>" class="nav__logo logo">Dialogue</a>
    <ul class="nav__items menu">
        <!-- Replace Blog with Search Icon -->
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
                    <img src="<?= ROOT_URL ?>images/<?= htmlspecialchars($avatarUrl, ENT_QUOTES, 'UTF-8') ?>?t=<?= time(); ?>" alt="User Avatar">
                </div>
                <ul>
                    <?php if ($isAdmin): ?>
                        <li><a href="<?= ROOT_URL ?>admin/dashboard.php">Admin Dashboard</a></li>
                    <?php else: ?>
                        <li><a href="<?= ROOT_URL ?>dashboard.php">Your Dashboard</a></li>
                    <?php endif; ?>
                    <li><a href="<?= ROOT_URL ?>logout.php">Logout</a></li>
                    <li><a href="#" id="deleteAccountBtn">Delete Account</a></li>
                </ul>
            </li>
        <?php else: ?>
            <li><a href="<?= ROOT_URL ?>signin.php">Sign In</a></li>
        <?php endif; ?>
    </ul>
    <button id="open__nav-btn"><i class="uil uil-bars"></i></button>
    <button id="close__nav-btn"><i class="uil uil-multiply"></i></button>
</nav>
<!--====================== END OF NAV ====================-->

<!-- Modal -->
<div id="deleteAccountModal" class="modal">
    <div class="modal-content">
        <p>Sorry to see you go. Are you sure you want to delete your account? This action cannot be undone.</p>

        <!-- Form to handle account deletion -->
        <form id="deleteAccountForm" action="delete-account.php" method="POST">
            <button type="submit" class="modal-confirm">Confirm Delete</button>
        </form>

        <button class="modal-close" onclick="closeModal()">Cancel</button>
    </div>
</div>

<script>
    // JavaScript to toggle menu on mobile
    const openNavBtn = document.getElementById('open__nav-btn');
    const closeNavBtn = document.getElementById('close__nav-btn');
    const menu = document.querySelector('.menu');

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

    // Modal functionality
    const deleteAccountBtn = document.getElementById('deleteAccountBtn');
    const deleteAccountModal = document.getElementById('deleteAccountModal');

    deleteAccountBtn.addEventListener('click', (e) => {
        e.preventDefault();
        deleteAccountModal.style.display = 'block';
    });

    function closeModal() {
        deleteAccountModal.style.display = 'none';
    }

    // Close the modal if the user clicks anywhere outside of the modal
    window.onclick = function(event) {
        if (event.target === deleteAccountModal) {
            deleteAccountModal.style.display = "none";
        }
    }
</script>

</body>
</html>
