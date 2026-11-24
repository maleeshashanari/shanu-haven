<?php

require_once __DIR__ . '/config/customer-session.php';
requireCustomerLogin();

require_once __DIR__ . '/config/database.php';

$userId = getCustomerId();

function e($value) {
    return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
}

if (empty($_SESSION['cancel_token'])) {
    $_SESSION['cancel_token'] = bin2hex(random_bytes(32));
}

// Cancel pending booking
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $token = $_POST['csrf_token'] ?? '';

    if (
        !is_string($token) ||
        !hash_equals($_SESSION['cancel_token'], $token)
    ) {
        http_response_code(403);
        exit('Invalid request.');
    }

    $bookingId = filter_input(
        INPUT_POST,
        'booking_id',
        FILTER_VALIDATE_INT
    );

    if ($bookingId && $bookingId > 0) {

        $stmt = $pdo->prepare(
            "UPDATE bookings
             SET booking_status = 'cancelled'
             WHERE booking_id = ?
             AND user_id = ?
             AND booking_status = 'pending'"
        );

        $stmt->execute([$bookingId, $userId]);

        $_SESSION['booking_message'] =
            $stmt->rowCount() > 0
            ? 'Booking cancelled successfully.'
            : 'Booking could not be cancelled.';
    }

    header('Location: my-bookings.php', true, 303);
    exit;
}

// Get customer bookings only
$stmt = $pdo->prepare(
    "SELECT b.*, r.room_name
     FROM bookings b
     INNER JOIN rooms r
     ON b.room_id = r.room_id
     WHERE b.user_id = ?
     ORDER BY b.created_at DESC"
);

$stmt->execute([$userId]);
$bookings = $stmt->fetchAll();

$message = $_SESSION['booking_message'] ?? '';

unset($_SESSION['booking_message']);

?>

<!DOCTYPE html>
<html lang="en">

<head>

<meta charset="UTF-8">

<meta name="viewport"
      content="width=device-width, initial-scale=1.0">

<title>My Bookings | Shanu Haven</title>

<style>

* {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
}

body {
    font-family: 'Segoe UI', Arial, sans-serif;
    min-height: 100vh;
    color: #F7F4ED;

    background:
        linear-gradient(
            135deg,
            rgba(8,30,35,.85),
            rgba(18,52,59,.70)
        ),
        url('assets/images/villa-hero.png')
        center / cover fixed;
}

header {
    background: rgba(18,52,59,.85);
    border-bottom: 1px solid #D7B98E;
    padding: 20px 6%;

    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 15px;
}

.brand {
    color: #D7B98E;
    font-size: 24px;
    font-weight: 700;
    text-decoration: none;
}

.home-link {
    color: #D7B98E;
    text-decoration: none;
}

.container {
    width: 92%;
    max-width: 1200px;
    margin: 45px auto;
}

.heading {
    text-align: center;
    margin-bottom: 35px;
}

.heading h1 {
    color: #D7B98E;
    font-size: 36px;
    margin-bottom: 10px;
}

.heading p {
    color: #F7F4ED;
}

.booking-grid {
    display: grid;
    grid-template-columns:
        repeat(auto-fit, minmax(min(300px, 100%), 1fr));
    gap: 24px;
}

.booking-card {
    padding: 25px;

    background: linear-gradient(
        135deg,
        rgba(18,52,59,.55),
        rgba(18,52,59,.35)
    );

    backdrop-filter: blur(25px);
    -webkit-backdrop-filter: blur(25px);

    border: 1px solid rgba(215,185,142,.5);
    border-radius: 18px;

    box-shadow: 0 15px 35px rgba(0,0,0,.20);
}

.booking-card h2 {
    color: #D7B98E;
    font-size: 23px;
    margin: 15px 0 20px;
}

.card-top {
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 10px;
}

.booking-id {
    font-size: 13px;
    color: #D7B98E;
}

.status {
    padding: 7px 12px;
    border-radius: 20px;
    font-size: 12px;
    font-weight: 600;
}

.pending {
    background: #FCE7B2;
    color: #795400;
}

.confirmed {
    background: #D5F5E3;
    color: #17663A;
}

.cancelled {
    background: #FADBD8;
    color: #922B21;
}

.detail {
    display: flex;
    justify-content: space-between;
    gap: 15px;

    padding: 12px 0;
    border-bottom: 1px solid rgba(255,255,255,.12);

    font-size: 14px;
}

.total {
    color: #D7B98E;
    font-size: 20px;
    font-weight: 700;
    margin: 20px 0;
}

.cancel-btn {
    width: 100%;
    padding: 12px;
    border-radius: 8px;

    border: 1px solid #E6A5A5;
    background: rgba(150,35,35,.25);
    color: #FFE0E0;

    font-size: 14px;
    cursor: pointer;
}

.cancel-btn:hover {
    background: #922B21;
}

.empty-box {
    text-align: center;
    padding: 45px 25px;

    border: 1px solid #D7B98E;
    border-radius: 18px;

    background: rgba(18,52,59,.55);
}

.browse-btn {
    display: inline-block;
    margin-top: 20px;
    padding: 12px 24px;

    background: #D7B98E;
    color: #12343B;

    border-radius: 8px;
    text-decoration: none;
    font-weight: 600;
}

.message {
    padding: 15px;
    margin-bottom: 25px;
    background: #F7F4ED;
    color: #12343B;
    border-radius: 8px;
    text-align: center;
}

@media (max-width: 600px) {

    .heading h1 {
        font-size: 28px;
    }

    .brand {
        font-size: 19px;
    }

    .booking-card {
        padding: 20px;
    }
}

</style>

</head>

<body>

<header>

    <a href="index.php" class="brand">
        SHANU HAVEN
    </a>

    <a href="index.php" class="home-link">
        &larr; Back to Home
    </a>

</header>

<main class="container">

    <div class="heading">

        <h1>My Bookings</h1>

        <p>
            Welcome, <?= e(getCustomerName()) ?>.
            Manage your reservations.
        </p>

    </div>

    <?php if ($message): ?>

        <div class="message" role="status">
            <?= e($message) ?>
        </div>

    <?php endif; ?>

    <?php if (!$bookings): ?>

        <div class="empty-box">

            <h2>No Bookings Yet</h2>

            <p>
                You have not made any reservations.
            </p>

            <a href="index.php#rooms" class="browse-btn">
                Explore Rooms
            </a>

        </div>

    <?php else: ?>

        <div class="booking-grid">

        <?php foreach ($bookings as $booking): ?>

            <article class="booking-card">

                <div class="card-top">

                    <span class="booking-id">
                        Booking #<?= (int)$booking['booking_id'] ?>
                    </span>

                    <span class="status <?= e($booking['booking_status']) ?>">

                        <?= e(ucfirst($booking['booking_status'])) ?>

                    </span>

                </div>

                <h2><?= e($booking['room_name']) ?></h2>

                <div class="detail">

                    <span>Check-in</span>

                    <strong>
                        <?= e($booking['check_in']) ?>
                    </strong>

                </div>

                <div class="detail">

                    <span>Check-out</span>

                    <strong>
                        <?= e($booking['check_out']) ?>
                    </strong>

                </div>

                <div class="detail">

                    <span>Guests</span>

                    <strong>
                        <?= (int)$booking['guests'] ?>
                    </strong>

                </div>

                <div class="total">

                    LKR <?= number_format(
                        (float)$booking['total_price'],
                        2
                    ) ?>

                </div>

                <?php if ($booking['booking_status'] === 'pending'): ?>

                    <form method="POST"
                          action="my-bookings.php"
                          onsubmit="return confirm('Cancel this booking?');">

                        <input type="hidden"
                               name="csrf_token"
                               value="<?= e($_SESSION['cancel_token']) ?>">

                        <input type="hidden"
                               name="booking_id"
                               value="<?= (int)$booking['booking_id'] ?>">

                        <button type="submit"
                                class="cancel-btn">

                            Cancel Booking

                        </button>

                    </form>

                <?php endif; ?>

            </article>

        <?php endforeach; ?>

        </div>

    <?php endif; ?>

</main>

</body>
</html>