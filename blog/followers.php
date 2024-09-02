<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require 'config/constants.php';
require 'config/database.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['user-id'])) {
    header('Location: ' . ROOT_URL . 'signin.php');
    exit();
}

$loggedInUserId = $_SESSION['user-id'];
$profileUserId = isset($_GET['user_id']) ? filter_var($_GET['user_id'], FILTER_SANITIZE_NUMBER_INT) : $loggedInUserId;
$type = isset($_GET['type']) ? $_GET['type'] : 'followers'; // Default to 'followers'

if ($type === 'following') {
    $query = "
        SELECT users.id, users.username, users.avatar_url
        FROM users
        JOIN followers ON users.id = followers.followed_id
        WHERE followers.follower_id = ?
    ";
} else {
    $query = "
        SELECT users.id, users.username, users.avatar_url
        FROM users
        JOIN followers ON users.id = followers.follower_id
        WHERE followers.followed_id = ?
    ";
}

$stmt = $pdo->prepare($query);
$stmt->execute([$profileUserId]);
$users = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Fetch current user's following list for follow/unfollow functionality
$following_query = "SELECT followed_id FROM followers WHERE follower_id = ?";
$following_stmt = $pdo->prepare($following_query);
$following_stmt->execute([$loggedInUserId]);
$following_ids = $following_stmt->fetchAll(PDO::FETCH_COLUMN, 0);

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= ucfirst($type) ?> List</title>
    <link rel="stylesheet" href="<?= ROOT_URL ?>css/style.css">
    <!-- ICONSCOUT CDN -->
    <link rel="stylesheet" href="https://unicons.iconscout.com/release/v4.0.0/css/line.css">
    <!-- GOOGLE FONT (MONTSERRAT) -->
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
    <style>
        body {
            display: flex;
            flex-direction: column;
            min-height: 100vh;
            font-family: 'Montserrat', sans-serif;
            background-color: #f4f6f8;
            margin: 0;
            color: #333;
        }

        .users__container {
            width: 80%; /* Set the container width to 90% of the parent */
            max-width: 1200px; /* Set a max width to prevent it from being too wide on large screens */
            margin: 3rem auto;
            padding: 2.5rem;
            background-color: #ffffff;
            border-radius: 16px;
            box-shadow: 0 8px 24px rgba(0, 0, 0, 0.12);
        }


        .user__item {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 1.5rem 0;
            border-bottom: 1px solid #e0e0e0;
        }

        .user__item:last-child {
            border-bottom: none;
        }

        .user__item img {
            width: 55px;
            height: 55px;
            border-radius: 50%;
            margin-right: 1.5rem;
            object-fit: cover;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }

        .user__info {
            display: flex;
            align-items: center;
        }

        .user__info h4 {
            margin: 0;
            font-size: 1.3rem;
            color: #6f6af8;
            font-weight: 600;
        }

        .follow-btn {
            padding: 0.6rem 1.2rem;
            background-color: #6f6af8;
            color: white;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-weight: bold;
            font-size: 1rem;
            transition: background-color 0.3s ease;
        }

        .follow-btn.unfollow-btn {
            background-color: #dc3545; /* Red color for unfollow */
        }

        .follow-btn:hover {
            background-color: #5854c7; /* Darker blue on hover */
        }

        .follow-btn.unfollow-btn:hover {
            background-color: #a71b2a; /* Darker red on hover */
        }

        .no-users {
            text-align: center;
            color: #6f6af8;
            font-size: 1.3rem;
            margin-top: 2.5rem;
        }

        .content {
            flex: 1;
            display: flex;
            flex-direction: column;
        }

        footer {
            background-color: #333;
            color: white;
            padding: 1.5rem 0;
            text-align: center;
            margin-top: auto;
            width: 100%;
            box-shadow: 0 -4px 8px rgba(0, 0, 0, 0.1);
        }

        footer a {
            color: #6f6af8;
            text-decoration: none;
            font-weight: bold;
        }
    </style>
</head>
<body>
<?php include 'partials/header.php'; ?>

<section class="content">
    <div class="container users__container">
        <h2><?= ucfirst($type) ?></h2> <!-- This will dynamically display 'Followers' or 'Following' -->
        <?php if (count($users) > 0): ?>
            <?php foreach ($users as $user): ?>
                <div class="user__item">
                    <div class="user__info">
                        <a href="<?= ROOT_URL ?>profile.php?user_id=<?= $user['id'] ?>">
                            <img src="<?= ROOT_URL ?>images/<?= htmlspecialchars($user['avatar_url'], ENT_QUOTES) ?>" alt="<?= htmlspecialchars($user['username'], ENT_QUOTES) ?>">
                        </a>
                        <h4><?= htmlspecialchars($user['username'], ENT_QUOTES) ?></h4>
                    </div>
                    <?php if ($loggedInUserId != $user['id']): ?>
                        <button class="follow-btn <?= in_array($user['id'], $following_ids) ? 'unfollow-btn' : '' ?>" data-user-id="<?= $user['id'] ?>">
                            <?= in_array($user['id'], $following_ids) ? 'Unfollow' : 'Follow' ?>
                        </button>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <p class="no-users">Nothing to see here.</p>
        <?php endif; ?>
    </div>
</section>

<?php include 'partials/footer.php'; ?>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const followButtons = document.querySelectorAll('.follow-btn');
        followButtons.forEach(button => {
            button.addEventListener('click', function () {
                const userId = this.dataset.userId;

                fetch('<?= ROOT_URL ?>follow.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: `followed_id=${userId}`
                })
                    .then(response => response.json())
                    .then(data => {
                        if (data.status === 'success') {
                            if (data.action === 'followed') {
                                this.classList.add('unfollow-btn');
                                this.textContent = 'Unfollow';
                            } else {
                                this.classList.remove('unfollow-btn');
                                this.textContent = 'Follow';
                            }
                        } else {
                            alert(data.message); // Simple alert for errors
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        alert('There was an error processing your request.');
                    });
            });
        });
    });
</script>
</body>
</html>
