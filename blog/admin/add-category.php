<?php
include 'partials/header.php';

// get back form data if invalid
$title = $_SESSION['add-category-data']['title'] ?? null;
$description = $_SESSION['add-category-data']['description'] ?? null;

unset($_SESSION['add-category-data']);
?>
<style>
    #sidebar-container {
        width: 250px; /* Keeping the same size as in manage-categories */
        background-color: white;
        color: #6f6af8;
        overflow-y: auto;
        padding-top: 20px;
        border-right: 2px solid;
        height: 100vh;
        position: fixed;
        top: 0;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1); /* Subtle shadow */
    }

    #sidebar-container .sidebar-wrapper {
        padding: 20px;
    }

    #sidebar-container .sidebar-link {
        text-align: center;
        font-size: 1.6rem;
        color: #6f6af8;
        text-decoration: none;
        font-weight: bold;
        display: block;
        margin-bottom: 30px;
    }

    #sidebar-container .sidebar-menu ul.menu {
        list-style: none;
        padding: 0;
        margin: 0;
    }

    #sidebar-container .sidebar-menu ul.menu .sidebar-item {
        margin-bottom: 15px;
    }

    #sidebar-container .sidebar-menu ul.menu .sidebar-item a {
        color: #6f6af8;
        padding: 10px 20px;
        display: flex;
        align-items: center;
        text-decoration: none;
        transition: background-color 0.3s ease, color 0.3s ease;
        border-radius: 8px;
        font-size: 1rem;
    }

    #sidebar-container .sidebar-menu ul.menu .sidebar-item a i {
        margin-right: 12px;
        font-size: 1.4rem;
    }

    #sidebar-container .sidebar-menu ul.menu .sidebar-item a:hover {
        background-color: #e6e6ff;
        color: #5854c7;
    }

    #sidebar-container .sidebar-menu ul.menu .sidebar-item a.active {
        background-color: #d9d9ff;
        color: #5854c7;
    }

    /* Dropdown Menu */
    .sidebar-item.dropdown .submenu {
        display: none; /* Hide the submenu by default */
        list-style: none;
        padding-left: 1.5rem;
        margin-top: 10px;
    }

    .sidebar-item.dropdown .submenu li {
        margin-bottom: 10px;
    }

    .sidebar-item.dropdown .submenu li a {
        padding: 8px 10px;
        display: block;
        color: #6f6af8;
        text-decoration: none;
        border-radius: 5px;
        transition: background-color 0.3s ease, color 0.3s ease;
    }

    .sidebar-item.dropdown .submenu li a:hover {
        background-color: #e6e6ff;
        color: #5854c7;
    }

    /* Show submenu when dropdown is active */
    .sidebar-item.dropdown.active .submenu {
        display: block;
    }

    /* Toggle Icon (optional) */
    .sidebar-item.dropdown .dropdown-toggle::after {
        content: '\f078'; /* Font Awesome icon for caret down */
        font-family: 'FontAwesome';
        float: right;
        margin-right: 10px;
        transition: transform 0.3s ease;
    }

    .sidebar-item.dropdown.active .dropdown-toggle::after {
        transform: rotate(-180deg);
    }

</style>
    <body>
    <div id="app">
        <!-- Sidebar Container -->
        <div id="sidebar-container" class="active">
            <div class="sidebar-wrapper active">
                <a href="index.php" class="sidebar-link">Dialogue</a>

                <div class="sidebar-menu">
                    <ul class="menu">
                        <li class="sidebar-item">
                            <a href="dashboard.php" class='sidebar-link'>
                                <i class="bi bi-person"></i>
                                <span>Dashboard</span>
                            </a>
                        </li>

                        <li class="sidebar-item">
                            <a href="add-post.php" class='sidebar-link'>
                                <i class="bi bi-pencil-square"></i>
                                <span>Add Post</span>
                            </a>
                        </li>

                        <!-- Dropdown for Manage Categories -->
                        <li class="sidebar-item dropdown">
                            <a href="#" class="sidebar-link dropdown-toggle">
                                <i class="bi bi-tags"></i> <!-- Changed to tags icon -->
                                <span>Manage Categories</span>
                            </a>
                            <ul class="submenu">
                                <li><a href="add-category.php">Add Category</a></li>
                                <li><a href="edit-category.php">Edit Category</a></li>
                            </ul>
                        </li>

                        <li class="sidebar-item">
                            <a href="manage-users.php" class='sidebar-link'>
                                <i class="bi bi-people"></i>
                                <span>Manage Users</span>
                            </a>
                        </li>
                        <li class="sidebar-item">
                            <a href="manage-posts.php" class='sidebar-link active'>
                                <i class="bi bi-card-text"></i>
                                <span>Manage Posts</span>
                            </a>
                        </li>

                        <li class="sidebar-item">
                            <a href="edit-profile.php" class='sidebar-link'>
                                <i class="bi bi-person"></i>
                                <span>Edit Profile</span>
                            </a>
                        </li>

                    </ul>
                </div>
            </div>
        </div>
    </div>
    </body>
<section class="form__section">
    <div class="container form__section-container">
        <h2>Add Category</h2>
        <?php if (isset($_SESSION['add-category'])) : ?>
            <div class="alert__message error">
                <p>
                    <?= $_SESSION['add-category'];
                    unset($_SESSION['add-category']) ?>
                </p>
            </div>
        <?php endif ?>
        <form action="<?= ROOT_URL ?>admin/add-category-logic.php" method="POST">
            <input type="text" value="<?= $title ?>" name="title" placeholder="Title">
            <textarea rows="4" value="<?= $description ?>" name="description" placeholder="Description"></textarea>
            <button type="submit" name="submit" class="btn">Add Category</button>
        </form>
    </div>
</section>

<?php
include '../partials/footer.php';
?>