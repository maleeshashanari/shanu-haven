<?php

require_once __DIR__ . '/auth.php';
requireAdminLogin();

require_once __DIR__ . '/../config/database.php';

header('Cache-Control: no-store, private');

require_once __DIR__ . '/includes/dashboard-functions.php';

// Statistics card details
$cards = [
    ['01', 'Total Bookings', 'total', 'All reservation requests'],
    ['02', 'Pending Bookings', 'pending', 'Awaiting approval'],
    ['03', 'Confirmed Bookings', 'confirmed', 'Approved reservations'],
    ['04', 'Cancelled Bookings', 'cancelled', 'Cancelled reservations']
];

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Admin Dashboard | Shanu Haven</title>

    <link rel="stylesheet" href="../assets/CSS/admin.css?v=35">
</head>

<body class="sh-dashboard">

<!-- HEADER -->

<header class="sh-admin-header">
    <div class="sh-header-inner">

        <a href="../index.php">
            <img src="../assets/images/shanu-logo.png"
                 alt="Shanu Haven"
                 class="sh-admin-logo">
        </a>

        <div class="sh-header-right">

            <span class="sh-welcome">
                Welcome, <?= e($adminName) ?>
            </span>

            <form method="POST" action="logout.php">
                <button type="submit" class="sh-logout-btn">
                    Logout
                </button>
            </form>

        </div>

    </div>
</header>

<!-- MAIN CONTENT -->

<main class="sh-admin-main">

    <section class="sh-dashboard-heading">

        <p class="sh-eyebrow">SHANU HAVEN COASTAL VILLA</p>

        <h1>Admin Dashboard</h1>

        <p>Manage your villa reservations and monitor booking activity.</p>

    </section>

    <?php if ($notice): ?>

        <div class="sh-notice" role="status">
            <?= e($notice) ?>
        </div>

    <?php endif; ?>

    <!-- STATISTICS -->

    <section class="sh-stats-grid">

        <?php foreach ($cards as [$number, $title, $key, $description]): ?>

            <article class="sh-stat-card">

                <div class="sh-stat-icon"><?= e($number) ?></div>

                <span><?= e($title) ?></span>

                <strong><?= $stats[$key] ?></strong>

                <p><?= e($description) ?></p>

            </article>

        <?php endforeach; ?>

    </section>

    <!-- RECENT BOOKINGS -->

    <?php require __DIR__ . '/includes/dashboard-bookings.php'; ?>

    <!-- MANAGE ROOMS -->

    <div class="sh-room-management">

        <a href="rooms.php" class="sh-manage-rooms-btn">
            Manage Rooms &rarr;
        </a>

    </div>

</main>

<!-- FOOTER -->

<footer class="sh-admin-footer">
    &copy; <?= date('Y') ?>
    Shanu Haven Coastal Villa.
    Admin Management System.
</footer>

</body>
</html>