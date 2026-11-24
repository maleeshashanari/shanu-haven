
<?php

require_once __DIR__ . '/config/customer-session.php';
requireCustomerLogin();

require_once __DIR__ . '/config/database.php';

header('Cache-Control: no-store, private');

function e($value) {
    return htmlspecialchars(
        (string)$value,
        ENT_QUOTES,
        'UTF-8'
    );
}

// Get booking ID
$bookingId = filter_input(
    INPUT_GET,
    'id',
    FILTER_VALIDATE_INT
);

$allowedBookingId =
    $_SESSION['last_booking_id'] ?? null;

// Verify booking session
if (
    !$bookingId ||
    !$allowedBookingId ||
    (int)$allowedBookingId !== (int)$bookingId
) {
    http_response_code(403);
    exit('Booking confirmation is not available.');
}

// Fetch booking and room details
$stmt = $pdo->prepare(
    "SELECT
        b.booking_id,
        b.guest_name,
        b.guest_email,
        b.check_in,
        b.check_out,
        b.guests,
        b.total_price,
        b.booking_status,
        r.room_name,
        r.image
     FROM bookings b
     INNER JOIN rooms r
        ON b.room_id = r.room_id
     WHERE b.booking_id = ?
       AND b.user_id = ?"
);

$stmt->execute([
    $bookingId,
    getCustomerId()
]);

$booking = $stmt->fetch();

if (!$booking) {
    http_response_code(404);
    exit('Booking not found.');
}

// Calculate number of nights
$start = new DateTimeImmutable(
    $booking['check_in']
);

$end = new DateTimeImmutable(
    $booking['check_out']
);

$nights = $start->diff($end)->days;

// Room image
$imageName = basename(
    (string)($booking['image'] ?? '')
);

if (
    preg_match('/^room_[a-f0-9]{32}\.(jpg|jpeg|png|webp)$/i', $imageName)
    && is_file(__DIR__ . '/assets/images/rooms/' . $imageName)
) {

    $imagePath = 'assets/images/rooms/' .
        rawurlencode($imageName);

} elseif (
    preg_match('/\.(jpg|jpeg|png|webp)$/i', $imageName)
    && is_file(__DIR__ . '/assets/images/' . $imageName)
) {

    $imagePath = 'assets/images/' .
        rawurlencode($imageName);

} else {

    $imagePath = 'assets/images/villa-hero.png';

}

// Booking reference
$reference = 'SH-' . str_pad(
    (string)$booking['booking_id'],
    6,
    '0',
    STR_PAD_LEFT
);

// Booking status
$status = $booking['booking_status'];

$statusClass = match ($status) {
    'confirmed' => 'status-confirmed',
    'cancelled' => 'status-cancelled',
    default => 'status-pending'
};

// My Bookings file
$myBookingsPage = is_file(
    __DIR__ . '/my-bookings.php'
) ? 'my-bookings.php' : 'my-booking.php';

?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta name="viewport"
          content="width=device-width, initial-scale=1.0">

    <title>Booking Received | Shanu Haven</title>

    <style>

        /* GLOBAL DESIGN */

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Arial, sans-serif;
            color: #F7F4ED;
            min-height: 100vh;

            background:
                linear-gradient(
                    rgba(8, 30, 35, 0.85),
                    rgba(8, 30, 35, 0.93)
                ),
                url('assets/images/villa-hero.png')
                center / cover fixed;

            background-color: #12343B;
        }

        a {
            text-decoration: none;
        }

        /* HEADER */

        .site-header {
            background: rgba(18, 52, 59, 0.85);

            border-bottom: 1px solid
                rgba(215, 185, 142, 0.30);

            backdrop-filter: blur(15px);
        }

        .navbar {
            width: 92%;
            max-width: 1150px;
            min-height: 80px;
            margin: auto;

            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .logo-image {
            width: 155px;
            height: auto;
            display: block;
        }

        .home-link {
            padding: 10px 20px;
            color: #D7B98E;
            border: 1px solid #D7B98E;
            border-radius: 8px;
            font-size: 14px;
            font-weight: 600;
            transition: 0.3s;
        }

        .home-link:hover {
            background: #D7B98E;
            color: #12343B;
        }

        /* SUCCESS PAGE */

        .success-page {
            min-height: 80vh;
            padding: 35px 20px 50px;
        }

        .success-container {
            max-width: 850px;
            margin: 0 auto;
        }

        /* SUCCESS HEADING */

        .success-hero {
            text-align: center;
            padding: 28px 25px;

            background: rgba(255, 255, 255, 0.07);

            border: 1px solid
                rgba(215, 185, 142, 0.35);

            border-radius: 18px 18px 0 0;
            backdrop-filter: blur(18px);
        }

        .success-icon {
            display: grid;
            place-items: center;

            width: 65px;
            height: 65px;

            margin: 0 auto 15px;

            border: 2px solid #D7B98E;
            border-radius: 50%;

            color: #D7B98E;
            font-size: 34px;
            font-weight: 700;

            background: rgba(215, 185, 142, 0.08);
        }

        .success-hero h1 {
            font-family: Georgia, serif;
            color: #D7B98E;
            font-size: clamp(25px, 4vw, 36px);
            font-weight: 500;
            margin-bottom: 10px;
        }

        .success-hero p {
            color: #D3DFDF;
            font-size: 14px;
            line-height: 1.7;
        }

        /* GLASS CARD */

        .success-card {
            padding: 28px;

            background: rgba(255, 255, 255, 0.07);

            border: 1px solid
                rgba(215, 185, 142, 0.35);

            border-top: none;
            border-radius: 0 0 18px 18px;

            backdrop-filter: blur(18px);

            box-shadow:
                0 15px 40px rgba(0, 0, 0, 0.20);
        }

        /* BOOKING REFERENCE */

        .reference-row {
            display: flex;
            align-items: center;
            justify-content: space-between;
            flex-wrap: wrap;
            gap: 15px;
        }

        .reference-label {
            color: #B9CBCB;
            font-size: 11px;
            letter-spacing: 2px;
            font-weight: 600;
            margin-bottom: 7px;
        }

        .reference-number {
            color: #D7B98E;
            font-size: 25px;
            font-weight: 700;
        }

        /* STATUS BADGES */

        .status-badge {
            padding: 8px 16px;
            border-radius: 25px;
            font-size: 13px;
            font-weight: 600;
        }

        .status-pending {
            background: rgba(215, 185, 142, 0.12);
            color: #E9CB98;
            border: 1px solid #D7B98E;
        }

        .status-confirmed {
            background: rgba(45, 150, 90, 0.15);
            color: #A5E4B7;
            border: 1px solid #79C995;
        }

        .status-cancelled {
            background: rgba(180, 45, 45, 0.15);
            color: #FFB4B4;
            border: 1px solid #D98585;
        }

        /* ROOM DETAILS */

        .success-room {
            display: grid;
            grid-template-columns: 170px 1fr;
            gap: 22px;
            align-items: center;
        }

        .success-room img {
            width: 170px;
            height: 120px;
            object-fit: cover;
            border-radius: 10px;

            border: 1px solid
                rgba(215, 185, 142, 0.35);
        }

        .success-room h2 {
            font-family: Georgia, serif;
            color: #D7B98E;
            font-size: 25px;
            font-weight: 500;
            margin-bottom: 10px;
        }

        .success-room p {
            color: #D3DFDF;
            font-size: 14px;
        }

        /* DIVIDER */

        .success-divider {
            border: none;

            border-top: 1px solid
                rgba(215, 185, 142, 0.25);

            margin: 22px 0;
        }

        /* GUEST DETAILS */

        .details-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 22px;
        }

        .detail-label {
            color: #B9CBCB;
            font-size: 13px;
            margin-bottom: 7px;
        }

        .detail-value {
            color: #F7F4ED;
            font-size: 15px;
            font-weight: 600;
            overflow-wrap: anywhere;
        }

        /* TOTAL PRICE */

        .total-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 15px;

            background: rgba(215, 185, 142, 0.08);

            border: 1px solid
                rgba(215, 185, 142, 0.30);

            border-radius: 10px;
            padding: 17px;
        }

        .total-row span {
            color: #F7F4ED;
            font-size: 15px;
        }

        .total-row strong {
            color: #D7B98E;
            font-size: 25px;
        }

        /* BOOKING NOTE */

        .booking-note {
            background: rgba(215, 185, 142, 0.07);
            color: #D3DFDF;

            border-left: 3px solid #D7B98E;

            border-radius: 6px;
            padding: 15px;
            margin: 22px 0;

            font-size: 13px;
            line-height: 1.7;
        }

        .booking-note strong {
            color: #D7B98E;
        }

        /* BUTTONS */

        .success-actions {
            display: flex;
            gap: 12px;
            flex-wrap: wrap;
        }

        .success-button {
            display: inline-block;

            padding: 12px 20px;

            background: #D7B98E;
            color: #12343B;

            border: 1px solid #D7B98E;
            border-radius: 8px;

            font-size: 14px;
            font-weight: 700;

            text-align: center;
            transition: 0.3s;
        }

        .success-button:hover {
            background: #E8D0AE;
            transform: translateY(-2px);

            box-shadow:
                0 5px 20px rgba(215, 185, 142, 0.15);
        }

        .success-button-outline {
            background: transparent;
            color: #D7B98E;
        }

        .success-button-outline:hover {
            background: #D7B98E;
            color: #12343B;
        }

        /* FOOTER */

        .footer {
            text-align: center;
            padding: 22px;

            background: rgba(0, 0, 0, 0.20);

            border-top: 1px solid
                rgba(215, 185, 142, 0.20);

            color: #B9CBCB;
            font-size: 13px;
        }

        /* RESPONSIVE DESIGN */

        @media (max-width: 600px) {

            .success-page {
                padding: 25px 15px 40px;
            }

            .success-card {
                padding: 20px;
            }

            .success-room {
                grid-template-columns: 1fr;
            }

            .success-room img {
                width: 100%;
                height: 190px;
            }

            .details-grid {
                grid-template-columns: 1fr;
                gap: 18px;
            }

            .reference-number {
                font-size: 22px;
            }

            .success-actions {
                flex-direction: column;
            }

            .success-button {
                width: 100%;
            }

            .logo-image {
                width: 120px;
            }
        }

    </style>

</head>

<body>

<!-- HEADER -->

<header class="site-header">

    <nav class="navbar">

        <a href="index.php">

            <img
                src="assets/images/shanu-logo.png"
                alt="Shanu Haven"
                class="logo-image">

        </a>

        <a href="index.php" class="home-link">
            Home
        </a>

    </nav>

</header>

<!-- SUCCESS PAGE -->

<main class="success-page">

    <div class="success-container">

        <section class="success-hero">

            <div class="success-icon">
                &#10003;
            </div>

            <h1>Booking Request Received!</h1>

            <p>
                Thank you for choosing Shanu Haven.
                <br>
                We have received your booking request.
            </p>

        </section>

        <section class="success-card">

            <!-- BOOKING REFERENCE -->

            <div class="reference-row">

                <div>

                    <p class="reference-label">
                        BOOKING REFERENCE
                    </p>

                    <p class="reference-number">
                        <?= e($reference) ?>
                    </p>

                </div>

                <span class="status-badge <?= e($statusClass) ?>">

                    <?= e(ucfirst($status)) ?>

                </span>

            </div>

            <hr class="success-divider">

            <!-- SELECTED ROOM -->

            <div class="success-room">

                <img
                    src="<?= e($imagePath) ?>"
                    alt="<?= e($booking['room_name']) ?>">

                <div>

                    <h2>
                        <?= e($booking['room_name']) ?>
                    </h2>

                    <p>

                        <?= (int)$nights ?>

                        <?= $nights == 1
                            ? 'Night' : 'Nights' ?>

                        &middot;

                        <?= (int)$booking['guests'] ?>

                        <?= (int)$booking['guests'] == 1
                            ? 'Guest' : 'Guests' ?>

                    </p>

                </div>

            </div>

            <hr class="success-divider">

            <!-- GUEST INFORMATION -->

            <div class="details-grid">

                <div>

                    <p class="detail-label">
                        Guest Name
                    </p>

                    <p class="detail-value">
                        <?= e($booking['guest_name']) ?>
                    </p>

                </div>

                <div>

                    <p class="detail-label">
                        Email Address
                    </p>

                    <p class="detail-value">
                        <?= e($booking['guest_email']) ?>
                    </p>

                </div>

                <div>

                    <p class="detail-label">
                        Check-in Date
                    </p>

                    <p class="detail-value">
                        <?= e($start->format('d M Y')) ?>
                    </p>

                </div>

                <div>

                    <p class="detail-label">
                        Check-out Date
                    </p>

                    <p class="detail-value">
                        <?= e($end->format('d M Y')) ?>
                    </p>

                </div>

            </div>

            <hr class="success-divider">

            <!-- TOTAL PRICE -->

            <div class="total-row">

                <span>
                    Total Booking Amount
                </span>

                <strong>

                    LKR <?= number_format(
                        (float)$booking['total_price'],
                        2
                    ) ?>

                </strong>

            </div>

            <!-- BOOKING STATUS MESSAGE -->

            <div class="booking-note">

                <?php if ($status === 'confirmed'): ?>

                    <strong>Confirmed:</strong>

                    Your reservation has been
                    confirmed by the administrator.

                <?php elseif ($status === 'cancelled'): ?>

                    <strong>Cancelled:</strong>

                    This reservation has been cancelled.

                <?php else: ?>

                    <strong>Important:</strong>

                    Your booking request is awaiting
                    administrator approval.
                    It is not yet a confirmed reservation.

                <?php endif; ?>

            </div>

            <!-- ACTION BUTTONS -->

            <div class="success-actions">

                <a href="<?= e($myBookingsPage) ?>"
                   class="success-button">

                    My Bookings

                </a>

                <a href="index.php"
                   class="success-button success-button-outline">

                    Return to Home

                </a>

                <a href="index.php#rooms"
                   class="success-button success-button-outline">

                    Explore More Rooms

                </a>

            </div>

        </section>

    </div>

</main>

<!-- FOOTER -->

<footer class="footer">

    &copy; <?= date('Y') ?>
    Shanu Haven Coastal Villa.
    All Rights Reserved.

</footer>

</body>
</html>
