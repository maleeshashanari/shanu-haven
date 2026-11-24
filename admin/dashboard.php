<?php
require_once __DIR__ . '/auth.php';
requireAdminLogin();

require_once __DIR__ . '/../config/database.php';

header('Cache-Control: no-store, private');

function e($value) {
    return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
}

if (empty($_SESSION['booking_csrf_admin'])) {
    $_SESSION['booking_csrf_admin'] = bin2hex(random_bytes(32));
}

// Get statistics
$stats = [
    'total' => 0,
    'pending' => 0,
    'confirmed' => 0,
    'cancelled' => 0
];

$stmt = $pdo->query(
    "SELECT booking_status, COUNT(*) AS amount
     FROM bookings
     GROUP BY booking_status"
);

foreach ($stmt->fetchAll() as $item) {
    $count = (int)$item['amount'];
    $status = $item['booking_status'];

    $stats['total'] += $count;

    if (isset($stats[$status])) {
        $stats[$status] = $count;
    }
}

// Get latest bookings
$stmt = $pdo->query(
    "SELECT
        b.booking_id,
        b.guest_name,
        b.guest_email,
        b.guest_phone,
        b.check_in,
        b.check_out,
        b.guests,
        b.total_price,
        b.booking_status,
        r.room_name
     FROM bookings b
     INNER JOIN rooms r
        ON b.room_id = r.room_id
     ORDER BY b.booking_id DESC
     LIMIT 10"
);

$bookings = $stmt->fetchAll();

$adminName = $_SESSION['admin_name'] ?? 'Admin';
$notice = $_SESSION['admin_notice'] ?? '';

unset($_SESSION['admin_notice']);

function bookingRef($id) {
    return 'SH-' . str_pad((string)$id, 6, '0', STR_PAD_LEFT);
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">

    <meta name="viewport"
          content="width=device-width, initial-scale=1.0">

    <title>Admin Dashboard | Shanu Haven</title>

    <link rel="stylesheet"
          href="../assets/css/admin.css?v=35">
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

        <p class="sh-eyebrow">
            SHANU HAVEN COASTAL VILLA
        </p>

        <h1>Admin Dashboard</h1>

        <p>
            Manage your villa reservations
            and monitor booking activity.
        </p>

    </section>

    <?php if ($notice): ?>

        <div class="sh-notice" role="status">
            <?= e($notice) ?>
        </div>

    <?php endif; ?>

    <!-- STATISTICS -->

    <section class="sh-stats-grid">

        <article class="sh-stat-card">

            <div class="sh-stat-icon">01</div>

            <span>Total Bookings</span>

            <strong><?= $stats['total'] ?></strong>

            <p>All reservation requests</p>

        </article>

        <article class="sh-stat-card">

            <div class="sh-stat-icon">02</div>

            <span>Pending Bookings</span>

            <strong><?= $stats['pending'] ?></strong>

            <p>Awaiting approval</p>

        </article>

        <article class="sh-stat-card">

            <div class="sh-stat-icon">03</div>

            <span>Confirmed Bookings</span>

            <strong><?= $stats['confirmed'] ?></strong>

            <p>Approved reservations</p>

        </article>

        <article class="sh-stat-card">

            <div class="sh-stat-icon">04</div>

            <span>Cancelled Bookings</span>

            <strong><?= $stats['cancelled'] ?></strong>

            <p>Cancelled reservations</p>

        </article>

    </section>

    <!-- RECENT BOOKINGS -->

    <section class="sh-bookings-panel">

        <div class="sh-panel-heading">

            <div>

                <p class="sh-eyebrow">
                    BOOKING MANAGEMENT
                </p>

                <h2>Recent Bookings</h2>

            </div>

            <a href="bookings.php"
               class="sh-record-count sh-all-bookings-link">

                View All Bookings &rarr;

            </a>

        </div>

        <div class="sh-table-wrapper">

            <table class="sh-booking-table">

                <thead>

                    <tr>
                        <th>Booking ID</th>
                        <th>Guest Details</th>
                        <th>Room</th>
                        <th>Check-in</th>
                        <th>Check-out</th>
                        <th>Guests</th>
                        <th>Total</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>

                </thead>

                <tbody>

                <?php if (empty($bookings)): ?>

                    <tr>

                        <td colspan="9" class="sh-empty">
                            No bookings available.
                        </td>

                    </tr>

                <?php else: ?>

                    <?php foreach ($bookings as $booking): ?>

                    <tr>

                        <td class="sh-ref">

                            <?= e(bookingRef($booking['booking_id'])) ?>

                        </td>

                        <td>

                            <strong>
                                <?= e($booking['guest_name']) ?>
                            </strong>

                            <small>
                                <?= e($booking['guest_email']) ?>
                            </small>

                            <small>
                                <?= e($booking['guest_phone']) ?>
                            </small>

                        </td>

                        <td>
                            <?= e($booking['room_name']) ?>
                        </td>

                        <td>
                            <?= e($booking['check_in']) ?>
                        </td>

                        <td>
                            <?= e($booking['check_out']) ?>
                        </td>

                        <td>
                            <?= (int)$booking['guests'] ?>
                        </td>

                        <td class="sh-amount">

                            LKR <?= number_format(
                                (float)$booking['total_price'], 2
                            ) ?>

                        </td>

                        <td>

                            <span class="sh-status sh-<?= e($booking['booking_status']) ?>">

                                <?= e(ucfirst($booking['booking_status'])) ?>

                            </span>

                        </td>

                        <td>

                            <?php if ($booking['booking_status'] === 'pending'): ?>

                                <div class="sh-actions">

                                    <!-- CONFIRM BOOKING -->

                                    <form method="POST"
                                          action="update-booking.php">

                                        <input type="hidden"
                                               name="csrf_token"
                                               value="<?= e($_SESSION['booking_csrf_admin']) ?>">

                                        <input type="hidden"
                                               name="booking_id"
                                               value="<?= (int)$booking['booking_id'] ?>">

                                        <input type="hidden"
                                               name="action"
                                               value="confirm">

                                        <button type="submit"
                                                class="sh-confirm-btn">

                                            Confirm

                                        </button>

                                    </form>

                                    <!-- CANCEL BOOKING -->

                                    <form method="POST"
                                          action="update-booking.php"
                                          onsubmit="return confirm('Cancel this booking request?');">

                                        <input type="hidden"
                                               name="csrf_token"
                                               value="<?= e($_SESSION['booking_csrf_admin']) ?>">

                                        <input type="hidden"
                                               name="booking_id"
                                               value="<?= (int)$booking['booking_id'] ?>">

                                        <input type="hidden"
                                               name="action"
                                               value="cancel">

                                        <button type="submit"
                                                class="sh-cancel-btn">

                                            Cancel

                                        </button>

                                    </form>

                                </div>

                            <?php else: ?>

                                <span class="sh-no-action">
                                    Completed
                                </span>

                            <?php endif; ?>

                        </td>

                    </tr>

                    <?php endforeach; ?>

                <?php endif; ?>

                </tbody>

            </table>

        </div>

    </section>

    <!-- MANAGE ROOMS BUTTON -->

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