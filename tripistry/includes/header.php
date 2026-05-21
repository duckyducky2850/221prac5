<?php
/*Shared page header
$page_title should be set before including this file.*/
require_once __DIR__ . '/../config/db.php'; // added bsc BASE_URL is only defined in db.php
require_once __DIR__ . '/auth.php';

$page_title = $page_title ?? 'Tripistry';
$flash = get_flash();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e($page_title) ?> – Tripistry</title>
    <link rel="stylesheet" href="<?= BASE_URL ?>/css/style.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Playfair+Display:wght@700&display=swap" rel="stylesheet">
</head>
<body>

<nav class="navbar">
    <div class="nav-inner">
        <a href="<?= BASE_URL ?>/index.php" class="nav-brand">✈ Tripistry</a>

        <ul class="nav-links">
            <?php if (!is_logged_in()): ?>
                <li><a href="<?= BASE_URL ?>/index.php">Home</a></li>
                <li><a href="<?= BASE_URL ?>/login.php" class="btn btn-outline">Log In</a></li>
                <li><a href="<?= BASE_URL ?>/register.php" class="btn btn-primary">Register</a></li>

            <?php elseif (current_role() === 'traveller'): ?>
                <li><a href="<?= BASE_URL ?>/traveller/dashboard.php">Dashboard</a></li>
                <li><a href="<?= BASE_URL ?>/traveller/destinations.php">Destinations</a></li>
                <li><a href="<?= BASE_URL ?>/traveller/packages.php">Packages</a></li>
                <li><a href="<?= BASE_URL ?>/traveller/bookings.php">My Bookings</a></li>
                <li><a href="<?= BASE_URL ?>/traveller/reviews.php">Reviews</a></li>
                <li><a href="<?= BASE_URL ?>/logout.php" class="btn btn-outline">Log Out</a></li>

            <?php elseif (current_role() === 'agency'): ?>
                <li><a href="<?= BASE_URL ?>/agency/dashboard.php">Dashboard</a></li>
                <li><a href="<?= BASE_URL ?>/agency/packages.php">Packages</a></li>
                <li><a href="<?= BASE_URL ?>/agency/group_trips.php">Group Trips</a></li>
                <li><a href="<?= BASE_URL ?>/agency/manage_content.php">Manage Content</a></li>
                <li><a href="<?= BASE_URL ?>/logout.php" class="btn btn-outline">Log Out</a></li>
            <?php endif; ?>

            <!-- Dark mode toggle -->
        <li>
            <button class="dark-toggle" onclick="toggleDarkMode()" id="dark-btn">🌙 Dark</button>
        </li>
        </ul>
    </div>
</nav>

<main class="main-content">

<?php if ($flash): ?>
    <div class="flash flash--<?= e($flash['type']) ?>">
        <?= e($flash['msg']) ?>
    </div>
<?php endif; ?>

<script>
// Dark mode — persists across pages via localStorage
function toggleDarkMode() {
    document.body.classList.toggle('dark-mode');
    const isDark = document.body.classList.contains('dark-mode');
    localStorage.setItem('darkMode', isDark ? '1' : '0');
    document.getElementById('dark-btn').textContent = isDark ? '☀️ Light' : '🌙 Dark';
}

// Apply on every page load before render to avoid flash
(function() {
    if (localStorage.getItem('darkMode') === '1') {
        document.body.classList.add('dark-mode');
        document.addEventListener('DOMContentLoaded', function() {
            const btn = document.getElementById('dark-btn');
            if (btn) btn.textContent = '☀️ Light';
        });
    }
})();
</script>
