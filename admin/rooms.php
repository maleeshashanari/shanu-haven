
<?php
require_once __DIR__ . '/room-helpers.php';

$stmt = $pdo->query(
    "SELECT r.*,
        (SELECT COUNT(*)
         FROM bookings b
         WHERE b.room_id = r.room_id)
         AS booking_count
     FROM rooms r
     ORDER BY r.room_id DESC"
);

$rooms = $stmt->fetchAll();

$notice = $_SESSION['room_notice'] ?? '';
unset($_SESSION['room_notice']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport"
      content="width=device-width, initial-scale=1">
<title>Room Management | Shanu Haven</title>
<link rel="stylesheet"
      href="../assets/css/admin.css?v=40">
</head>

<body class="sh-dashboard">

<header class="sh-admin-header">
    <div class="sh-header-inner">
        <a href="dashboard.php">
            <img
                src="../assets/images/shanu-logo.png"
                class="sh-admin-logo"
                alt="Shanu Haven">
        </a>

        <div class="sh-header-right">
            <a href="dashboard.php"
               class="sh-nav-link">
                Dashboard
            </a>
            <a href="bookings.php"
               class="sh-nav-link">
                Bookings
            </a>
            <form method="POST" action="logout.php">
                <button class="sh-logout-btn"
                        type="submit">
                    Logout
                </button>
            </form>
        </div>
    </div>
</header>

<main class="sh-admin-main">

    <div class="sh-room-heading">
        <div>
            <p class="sh-eyebrow">
                SHANU HAVEN MANAGEMENT
            </p>
            <h1>Room Management</h1>
            <p>
                Manage accommodation,
                availability and room images.
            </p>
        </div>

        <a href="/shanu-haven/admin/room-form.php"
   class="sh-room-primary">
    + Add New Room
</a>
    

    <?php if ($notice): ?>
        <div class="sh-notice" role="status">
            <?= roomE($notice) ?>
        </div>
    <?php endif; ?>

    <section class="sh-bookings-panel">
        <div class="sh-panel-heading">
            <div>
                <p class="sh-eyebrow">
                    ACCOMMODATION LIST
                </p>
                <h2>All Rooms</h2>
            </div>

            <span class="sh-record-count">
                <?= count($rooms) ?> Rooms
            </span>
        </div>

        <div class="sh-table-wrapper">
            <table class="sh-booking-table sh-room-table">
                <thead>
                    <tr>
                        <th>Image</th>
                        <th>Room</th>
                        <th>Guests</th>
                        <th>Price / Night</th>
                        <th>Availability</th>
                        <th>Bookings</th>
                        <th>Actions</th>
                    </tr>
                </thead>

                <tbody>
                <?php foreach ($rooms as $room): ?>
                    <tr>
                        <td>
                            <img
                                class="sh-room-thumbnail"
                                src="<?= roomE(
                                    roomImageUrl($room['image'])
                                ) ?>"
                                alt="<?= roomE(
                                    $room['room_name']
                                ) ?>">
                        </td>

                        <td>
                            <strong>
                                <?= roomE($room['room_name']) ?>
                            </strong>
                            <small>
                                Room #<?= (int)$room['room_id'] ?>
                            </small>
                        </td>

                        <td>
                            <?= (int)$room['max_guests'] ?>
                        </td>

                        <td class="sh-amount">
                            LKR <?= number_format(
                                (float)$room['price_per_night'],
                                2
                            ) ?>
                        </td>

                        <td>
                            <span class="sh-status <?= 
                                $room['status'] === 'available'
                                ? 'sh-confirmed'
                                : 'sh-cancelled'
                            ?>">
                                <?= roomE(ucfirst(
                                    $room['status']
                                )) ?>
                            </span>
                        </td>

                        <td>
                            <?= (int)$room['booking_count'] ?>
                        </td>

                        <td>
                            <div class="sh-actions">

                                <a
                                    href="room-form.php?id=<?=
                                        (int)$room['room_id']
                                    ?>"
                                    class="sh-view-btn sh-edit-link">
                                    Edit
                                </a>

                                <?php if (
                                    (int)$room['booking_count'] === 0
                                ): ?>
                                    <form
                                        method="POST"
                                        action="delete-room.php"
                                        onsubmit="return confirm('Permanently delete this room?');">

                                        <input type="hidden"
                                               name="csrf_token"
                                               value="<?= roomE(
                                                   $_SESSION['room_csrf']
                                               ) ?>">

                                        <input type="hidden"
                                               name="room_id"
                                               value="<?=
                                                   (int)$room['room_id']
                                               ?>">

                                        <button
                                            type="submit"
                                            class="sh-cancel-btn">
                                            Delete
                                        </button>
                                    </form>
                                <?php else: ?>
                                    <span class="sh-no-action"
                                          title="Room has booking history">
                                        Delete locked
                                    </span>
                                <?php endif; ?>

                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>

                <?php if (!$rooms): ?>
                    <tr>
                        <td colspan="7" class="sh-empty">
                            No rooms found.
                        </td>
                    </tr>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
    </section>
</main>

<footer class="sh-admin-footer">
    &copy; <?= date('Y') ?> Shanu Haven.
</footer>
</body>
</html>
