<?php
// includes/header.php - top of every page.
// Pages set $page_title and $base ('' for root pages, '../' for admin pages) before including this.
if (!isset($base)) { $base = ''; }
if (!isset($page_title)) { $page_title = 'Lagoon Breeze Villa'; }
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?php echo e($page_title); ?> | Lagoon Breeze Villa - Negombo</title>
    <link rel="stylesheet" href="<?php echo $base; ?>css/style.css">
</head>
<body>
<header class="site-header">
    <div class="container nav-wrap">
        <a class="logo" href="<?php echo $base; ?>index.php">Lagoon Breeze Villa <small>Negombo</small></a>
        <button class="menu-toggle" id="menuToggle" type="button">Menu</button>
        <nav id="mainNav">
            <a href="<?php echo $base; ?>index.php">Home</a>
            <a href="<?php echo $base; ?>rooms.php">Rooms</a>
            <?php if (is_admin()): ?>
                <a href="<?php echo $base; ?>admin/dashboard.php">Dashboard</a>
                <a href="<?php echo $base; ?>admin/manage_rooms.php">Manage Rooms</a>
                <a href="<?php echo $base; ?>admin/manage_bookings.php">Manage Bookings</a>
            <?php elseif (is_logged_in()): ?>
                <a href="<?php echo $base; ?>my_bookings.php">My Bookings</a>
            <?php endif; ?>
            <?php if (is_logged_in()): ?>
                <a href="<?php echo $base; ?>logout.php">Logout (<?php echo e($_SESSION['name']); ?>)</a>
            <?php else: ?>
                <a href="<?php echo $base; ?>login.php">Login</a>
                <a class="btn btn-small" href="<?php echo $base; ?>register.php">Register</a>
            <?php endif; ?>
        </nav>
    </div>
</header>
<main>
<?php
// Show a one-time flash message if there is one
if (isset($_SESSION['flash'])) {
    echo '<div class="container"><div class="alert alert-' . e($_SESSION['flash']['type']) . '">'
       . e($_SESSION['flash']['message']) . '</div></div>';
    unset($_SESSION['flash']);
}
?>
