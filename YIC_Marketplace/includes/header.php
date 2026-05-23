<?php
require_once __DIR__ . '/../classes/Auth.php';
require_once __DIR__ . '/functions.php';

Auth::startSession();
$page_title = $page_title ?? 'YIC Marketplace ';
$is_logged_in = !empty($_SESSION['user_id']);
$is_admin = $is_logged_in && ($_SESSION['role'] ?? 'student') === 'admin';
$display_name = $is_logged_in ? ($_SESSION['user_name'] ?? '') : '';
$current_page = basename($_SERVER['PHP_SELF']);
$nav_class = function ($page, $extra = '') use ($current_page) {
    $classes = trim($extra . ' ' . ($current_page === $page ? 'active' : ''));
    return $classes === '' ? '' : ' class="' . e($classes) . '"';
};
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo e($page_title); ?></title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
<header class="site-header">
    <div class="logo">
        <a href="index.php" class="brand-link">
            <img src="assets/img/yic-logo.png" alt="YIC Logo" class="brand-logo" width="48" height="48">
            <span>YIC Marketplace</span>
        </a>
    </div>

    <nav>
        <ul class="nav-list">
            <li><a href="index.php"<?php echo $nav_class('index.php'); ?>>Home</a></li>

            <?php if ($is_logged_in): ?>
                <?php if ($is_admin): ?>
                    <li><a href="admin_dashboard.php"<?php echo $nav_class('admin_dashboard.php', 'admin-link'); ?>>Stats Dashboard</a></li>
                    <li><a href="admin_management.php"<?php echo $nav_class('admin_management.php', 'admin-link'); ?>>Admin Controls</a></li>
                <?php endif; ?>

                <li><a href="purchase_orders.php"<?php echo $nav_class('purchase_orders.php'); ?>>Sales & Orders</a></li>
                <li><a href="add_item.php"<?php echo $nav_class('add_item.php', 'sell-link'); ?>>Sell Item</a></li>
                <li><a href="inbox.php"<?php echo $nav_class('inbox.php'); ?>>Inbox</a></li>
                <li><a href="profile.php"<?php echo $nav_class('profile.php'); ?>>Profile</a></li>
                <li class="nav-user-item">
                    <?php if ($display_name !== ''): ?>
                        <span class="nav-user-name">Welcome, <?php echo htmlspecialchars($display_name, ENT_QUOTES, 'UTF-8'); ?></span>
                        <span class="nav-separator">|</span>
                    <?php endif; ?>
                    <a href="logout.php" class="logout-link">Logout</a>
                </li>
            <?php else: ?>
                <li><a href="login.php"<?php echo $nav_class('login.php'); ?>>Login</a></li>
                <li><a href="register.php"<?php echo $nav_class('register.php'); ?>>Register</a></li>
            <?php endif; ?>
        </ul>
    </nav>
</header>
