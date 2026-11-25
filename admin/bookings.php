
<?php

require_once __DIR__ . '/auth.php';
requireAdminLogin();

require_once __DIR__ . '/../config/database.php';

header('Cache-Control: no-store, private');

require_once __DIR__ . '/includes/booking-functions.php';

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>All Bookings | Shanu Haven</title>

    <link rel="stylesheet" href="../assets/CSS/admin.css?v=31">
</head>

<body class="sh-dashboard">

<header class="sh-admin-header">
    <div class="sh-header-inner">

        <a href="dashboard.php">
            <img src="../assets/images/shanu-logo.png"
                 alt="Shanu Haven"
                 class="sh-admin-logo">
        </a>

        <div class="sh-header-right">
            <a href="dashboard.php" class="sh-nav-link">
                Dashboard
            </a>

            <form method="POST" action="logout.php">
                <button class="sh-logout-btn" type="submit">
                    Logout
                </button>
            </form>
        </div>

    </div>
</header>

<main class="sh-admin-main">

    <section class="sh-dashboard-heading">
        <p class="sh-eyebrow">SHANU HAVEN MANAGEMENT</p>
        <h1>All Bookings</h1>
        <p>Search, filter and manage your villa reservations.</p>
    </section>

    <?php if ($notice): ?>
        <div class="sh-notice" role="status">
            <?= e($notice) ?>
        </div>
    <?php endif; ?>

    <section class="sh-bookings-panel">

        <!-- Search Form -->
        <form method="GET"
              action="bookings.php"
              class="sh-search-form"
              id="bookingSearchForm">

            <label for="bookingSearch">Search Bookings</label>

            <div class="sh-search-row">

                <input type="search"
                       name="q"
                       id="bookingSearch"
                       value="<?= e($search) ?>"
                       placeholder="Booking ID, guest, email, phone or room">

                <input type="hidden" name="status"
                       value="<?= e($status) ?>">

                <button type="submit" class="sh-search-btn">
                    Search
                </button>

                <a href="bookings.php" class="sh-reset-btn">
                    Reset
                </a>

            </div>
        </form>

        <!-- Status Filters -->
        <nav class="sh-filter-tabs" aria-label="Booking status filters">

            <?php foreach ($allowedStatuses as $filter): ?>

                <a class="sh-filter-tab <?= $status === $filter ? 'active' : '' ?>"
                   href="bookings.php?<?= e(http_build_query([
                       'status' => $filter,
                       'q' => $search
                   ])) ?>">

                    <?= e(ucfirst($filter)) ?>
                    <span><?= $counts[$filter] ?></span>

                </a>

            <?php endforeach; ?>

        </nav>

        <div class="sh-panel-heading">
            <div>
                <p class="sh-eyebrow">RESERVATION RECORDS</p>
                <h2>Booking List</h2>
            </div>

            <span class="sh-record-count">
                <?= count($bookings) ?> Result(s)
            </span>
        </div>

        <?php require __DIR__ . '/includes/booking-table.php'; ?>

    </section>
</main>

<?php require __DIR__ . '/includes/booking-modal.php'; ?>

<footer class="sh-admin-footer">
    &copy; <?= date('Y') ?> Shanu Haven Coastal Villa.
</footer>

<script src="../assets/js/admin-bookings.js"></script>

</body>
</html>
