
<?php

require_once __DIR__ . '/config/customer-session.php';
requireCustomerLogin();

require_once __DIR__ . '/config/database.php';

function e($value) {
    return htmlspecialchars(
        (string)$value,
        ENT_QUOTES,
        'UTF-8'
    );
}

// Get logged-in customer
$userId = getCustomerId();

$stmt = $pdo->prepare(
    "SELECT name, email, contact_number
     FROM users
     WHERE user_id = ? AND role = 'customer'"
);

$stmt->execute([$userId]);
$customer = $stmt->fetch();

if (!$customer) {
    http_response_code(403);
    exit('Customer account not found.');
}

// Get selected room
$roomId = filter_input(
    INPUT_GET,
    'room_id',
    FILTER_VALIDATE_INT
);

if (!$roomId || $roomId < 1) {
    http_response_code(404);
    exit('Invalid room selected.');
}

$stmt = $pdo->prepare(
    'SELECT * FROM rooms WHERE room_id = ?'
);

$stmt->execute([$roomId]);
$room = $stmt->fetch();

if (!$room) {
    http_response_code(404);
    exit('Room not found.');
}

$isRoomAvailable = $room['status'] === 'available';

// Room image
$imageName = basename((string)($room['image'] ?? ''));

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

// CSRF protection
if (empty($_SESSION['booking_csrf'])) {
    $_SESSION['booking_csrf'] = bin2hex(random_bytes(32));
}

$errors = [];

// Customer information
$name = $customer['name'];
$email = $customer['email'];
$phone = $customer['contact_number'];

$checkIn = '';
$checkOut = '';
$guests = 1;
$totalPrice = 0;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $checkIn = is_string($_POST['check_in'] ?? null)
        ? trim($_POST['check_in']) : '';

    $checkOut = is_string($_POST['check_out'] ?? null)
        ? trim($_POST['check_out']) : '';

    $guests = filter_var(
        $_POST['guests'] ?? null,
        FILTER_VALIDATE_INT
    );

    // Validate CSRF token
    $token = $_POST['csrf_token'] ?? '';

    if (
        !is_string($token) ||
        !hash_equals($_SESSION['booking_csrf'], $token)
    ) {
        $errors[] = 'Invalid form submission.';
    }

    // Validate customer details
    if ($name === '' || mb_strlen($name) > 100) {
        $errors[] = 'Invalid customer name.';
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Invalid customer email.';
    }

    if (!preg_match('/^[0-9+\-\s()]{7,20}$/', $phone)) {
        $errors[] = 'Invalid phone number.';
    }

    // Validate guests
    if (
        $guests === false ||
        $guests < 1 ||
        $guests > (int)$room['max_guests']
    ) {
        $errors[] = 'Invalid number of guests.';
    }

    // Validate dates
    $start = DateTimeImmutable::createFromFormat(
        '!Y-m-d',
        $checkIn
    );

    $end = DateTimeImmutable::createFromFormat(
        '!Y-m-d',
        $checkOut
    );

    $validStart = $start &&
        $start->format('Y-m-d') === $checkIn;

    $validEnd = $end &&
        $end->format('Y-m-d') === $checkOut;

    if (!$validStart || !$validEnd) {

        $errors[] = 'Please select valid dates.';

    } else {

        if ($start < new DateTimeImmutable('today')) {
            $errors[] = 'Check-in cannot be in the past.';
        }

        if ($end <= $start) {
            $errors[] = 'Check-out must be after check-in.';
        }

        $nights = (int)$start->diff($end)->days;

        if ($nights > 30) {
            $errors[] = 'Maximum stay is 30 nights.';
        }

        $totalPrice = $nights *
            (float)$room['price_per_night'];
    }

    if (!$isRoomAvailable) {
        $errors[] = 'This room is unavailable.';
    }

    // Save booking
    if (!$errors) {

        try {

            $pdo->beginTransaction();

            // Lock selected room
            $lockStmt = $pdo->prepare(
                'SELECT status, max_guests, price_per_night
                 FROM rooms
                 WHERE room_id = ?
                 FOR UPDATE'
            );

            $lockStmt->execute([$roomId]);
            $lockedRoom = $lockStmt->fetch();

            if (
                !$lockedRoom ||
                $lockedRoom['status'] !== 'available'
            ) {
                throw new DomainException(
                    'Room is currently unavailable.'
                );
            }

            if ($guests > (int)$lockedRoom['max_guests']) {
                throw new DomainException(
                    'Invalid number of guests.'
                );
            }

            $totalPrice = $nights *
                (float)$lockedRoom['price_per_night'];

            // Prevent overlapping bookings
            $checkStmt = $pdo->prepare(
                "SELECT COUNT(*) FROM bookings
                 WHERE room_id = ?
                 AND booking_status IN ('pending', 'confirmed')
                 AND check_in < ?
                 AND check_out > ?"
            );

            $checkStmt->execute([
                $roomId,
                $checkOut,
                $checkIn
            ]);

            if ((int)$checkStmt->fetchColumn() > 0) {
                throw new DomainException(
                    'This room is already booked for the selected dates.'
                );
            }

            // Save booking
            $insertStmt = $pdo->prepare(
                "INSERT INTO bookings
                (
                    user_id,
                    room_id,
                    guest_name,
                    guest_email,
                    guest_phone,
                    check_in,
                    check_out,
                    guests,
                    total_price,
                    booking_status
                )
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 'pending')"
            );

            $insertStmt->execute([
                $userId,
                $roomId,
                $name,
                $email,
                $phone,
                $checkIn,
                $checkOut,
                $guests,
                $totalPrice
            ]);

            $bookingId = $pdo->lastInsertId();

            $pdo->commit();

            $_SESSION['last_booking_id'] = (int)$bookingId;

            unset($_SESSION['booking_csrf']);

            header(
                'Location: booking-success.php?id=' .
                urlencode((string)$bookingId),
                true,
                303
            );

            exit;

        } catch (DomainException $ex) {

            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }

            $errors[] = $ex->getMessage();

        } catch (Throwable $ex) {

            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }

            error_log($ex->getMessage());

            $errors[] =
                'Booking could not be saved. Please try again.';
        }
    }
}

?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta name="viewport"
          content="width=device-width, initial-scale=1.0">

    <title>Book Your Stay | Shanu Haven</title>

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
                    rgba(8, 30, 35, 0.92)
                ),
                url('assets/images/villa-hero.png')
                center / cover fixed;

            background-color: #12343B;
        }

        .container {
            width: 92%;
            max-width: 1150px;
            margin: auto;
        }

        /* HEADER */

        .site-header {
            background: rgba(18, 52, 59, 0.85);
            border-bottom: 1px solid rgba(215, 185, 142, 0.3);
            backdrop-filter: blur(15px);
        }

        .navbar {
            min-height: 80px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 20px;
        }

        .logo-image {
            width: 155px;
            height: auto;
            display: block;
        }

        .nav-link {
            color: #D7B98E;
            text-decoration: none;
            font-size: 14px;
            font-weight: 600;
            border: 1px solid #D7B98E;
            padding: 10px 18px;
            border-radius: 7px;
            transition: 0.3s;
        }

        .nav-link:hover {
            background: #D7B98E;
            color: #12343B;
        }

        /* PAGE HEADING */

        .booking-page {
            padding: 30px 0 45px;
        }

        .booking-back {
            color: #D7B98E;
            text-decoration: none;
            font-size: 14px;
        }

        .booking-heading {
            text-align: center;
            margin: 22px 0 30px;
        }

        .section-label {
            color: #D7B98E;
            font-size: 12px;
            font-weight: 600;
            letter-spacing: 3px;
            margin-bottom: 10px;
        }

        .booking-heading h1 {
            font-family: Georgia, serif;
            font-size: 40px;
            font-weight: 500;
            margin-bottom: 10px;
        }

        .booking-heading p {
            color: #D3DFDF;
        }

        .booking-heading .section-label {
            color: #D7B98E;
        }

        /* BOOKING LAYOUT */

        .booking-layout {
            display: grid;
            grid-template-columns: 1.25fr 1fr;
            gap: 25px;
            align-items: start;
        }

        /* GLASS CARDS */

        .booking-card {
            background: rgba(255, 255, 255, 0.07);
            backdrop-filter: blur(18px);

            border: 1px solid
                rgba(215, 185, 142, 0.30);

            border-radius: 16px;
            padding: 25px;

            box-shadow:
                0 12px 35px rgba(0, 0, 0, 0.20);

            transition: border-color 0.3s,
                        box-shadow 0.3s;
        }

        .booking-card:hover,
        .booking-card:focus-within {
            border-color: #D7B98E;

            box-shadow:
                0 12px 35px rgba(0, 0, 0, 0.25),
                0 0 15px rgba(215, 185, 142, 0.12);
        }

        .booking-card h2 {
            font-family: Georgia, serif;
            color: #D7B98E;
            font-size: 25px;
            font-weight: 500;
            margin-bottom: 22px;
        }

        /* FORM */

        .form-group {
            margin-bottom: 18px;
        }

        .form-group label {
            display: block;
            font-size: 14px;
            font-weight: 500;
            margin-bottom: 8px;
        }

        .form-group input,
        .form-group select {
            width: 100%;
            padding: 12px 14px;

            border: 1px solid
                rgba(215, 185, 142, 0.35);

            border-radius: 8px;
            background: rgba(0, 0, 0, 0.15);
            color: #FFFFFF;
            font-family: inherit;
            font-size: 14px;
            color-scheme: dark;
            outline: none;
            transition: 0.3s;
        }

        .form-group input:focus,
        .form-group select:focus {
            border-color: #D7B98E;

            box-shadow:
                0 0 0 3px rgba(215, 185, 142, 0.15);
        }

        .form-group input[readonly] {
            background: rgba(255, 255, 255, 0.05);
            color: #D3DFDF;
            cursor: default;
        }

        .form-group select option {
            background: #12343B;
            color: #FFFFFF;
        }

        .form-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 15px;
        }

        .form-divider {
            border: none;
            border-top: 1px solid
                rgba(215, 185, 142, 0.25);
            margin: 8px 0 22px;
        }

        .account-note {
            font-size: 12px;
            color: #C1D0D0;
            margin: -5px 0 18px;
        }

        /* ROOM IMAGE */

        .room-image {
            width: 100%;
            height: 230px;
            object-fit: cover;
            border-radius: 10px;
            display: block;
            margin-bottom: 18px;
        }

        .room-name {
            font-family: Georgia, serif;
            font-size: 25px;
            margin-bottom: 12px;
        }

        .room-description {
            font-size: 14px;
            line-height: 1.7;
            color: #D3DFDF;
            margin-bottom: 15px;
        }

        .room-info-row {
            display: flex;
            justify-content: space-between;
            gap: 12px;
            padding: 12px 0;

            border-bottom: 1px solid
                rgba(215, 185, 142, 0.18);

            font-size: 14px;
        }

        .room-info-row strong {
            color: #D7B98E;
            text-align: right;
        }

        /* FACILITIES */

        .facilities {
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
            margin: 16px 0;
        }

        .facility-tag {
            background: rgba(215, 185, 142, 0.10);

            border: 1px solid
                rgba(215, 185, 142, 0.30);

            color: #E8D3B6;
            padding: 6px 11px;
            border-radius: 20px;
            font-size: 12px;
        }

        /* PRICE SUMMARY */

        .price-summary {
            background: rgba(215, 185, 142, 0.08);

            border: 1px solid
                rgba(215, 185, 142, 0.30);

            border-radius: 10px;
            padding: 16px;
            margin-top: 20px;
        }

        .price-row {
            display: flex;
            justify-content: space-between;
            gap: 10px;
            margin-bottom: 12px;
            font-size: 14px;
        }

        .total-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 10px;

            border-top: 1px solid
                rgba(215, 185, 142, 0.35);

            padding-top: 14px;
            font-size: 16px;
        }

        .total-row strong {
            color: #D7B98E;
            font-size: 23px;
        }

        /* BOOKING BUTTON */

        .booking-btn {
            width: 100%;
            padding: 15px;
            border: none;
            border-radius: 8px;
            background: #D7B98E;
            color: #12343B;
            font-size: 16px;
            font-weight: 700;
            cursor: pointer;
            transition: 0.3s;
            margin-top: 5px;
        }

        .booking-btn:hover {
            background: #E8D0AE;
            box-shadow: 0 5px 20px
                rgba(215, 185, 142, 0.20);
        }

        .booking-btn:disabled {
            opacity: 0.5;
            cursor: not-allowed;
        }

        .booking-note {
            text-align: center;
            font-size: 12px;
            line-height: 1.6;
            color: #C1D0D0;
            margin-top: 12px;
        }

        /* ERROR MESSAGE */

        .booking-error {
            padding: 16px 20px;
            margin-bottom: 22px;
            border-radius: 10px;
            background: rgba(150, 30, 30, 0.25);
            border: 1px solid #E99A9A;
            color: #FFD0D0;
            font-size: 14px;
        }

        .booking-error ul {
            padding-left: 20px;
            margin-top: 8px;
            line-height: 1.8;
        }

        .status-available {
            color: #9DE0B3 !important;
        }

        .status-unavailable {
            color: #FFAAAA !important;
        }

        /* FOOTER */

        .footer {
            text-align: center;
            padding: 22px;
            background: rgba(0, 0, 0, 0.20);

            border-top: 1px solid
                rgba(215, 185, 142, 0.20);

            color: #C1D0D0;
            font-size: 13px;
        }

        /* RESPONSIVE DESIGN */

        @media (max-width: 800px) {

            .booking-layout {
                grid-template-columns: 1fr;
            }

            .booking-heading h1 {
                font-size: 32px;
            }

            .booking-card {
                padding: 20px;
            }
        }

        @media (max-width: 480px) {

            .form-grid {
                grid-template-columns: 1fr;
                gap: 0;
            }

            .logo-image {
                width: 115px;
            }

            .nav-link {
                padding: 9px 12px;
                font-size: 12px;
            }

            .room-image {
                height: 200px;
            }
        }

    </style>

</head>

<body>

<!-- HEADER -->

<header class="site-header">

    <nav class="navbar container">

        <a href="index.php">
            <img
                src="assets/images/shanu-logo.png"
                alt="Shanu Haven"
                class="logo-image">
        </a>

        <a href="index.php#rooms" class="nav-link">
            Back to Rooms
        </a>

    </nav>

</header>

<!-- BOOKING PAGE -->

<main class="booking-page">

    <div class="container">

        <a href="room-details.php?id=<?= $roomId ?>"
           class="booking-back">

            &larr; Back to Room Details

        </a>

        <div class="booking-heading">

            <p class="section-label">
                SHANU HAVEN RESERVATIONS
            </p>

            <h1>Book Your Stay</h1>

            <p>
                Complete your reservation and enjoy
                a relaxing coastal escape.
            </p>

        </div>

        <!-- ERROR MESSAGES -->

        <?php if (!empty($errors)): ?>

            <div class="booking-error" role="alert">

                <strong>
                    Please correct the following:
                </strong>

                <ul>

                    <?php foreach ($errors as $error): ?>

                        <li><?= e($error) ?></li>

                    <?php endforeach; ?>

                </ul>

            </div>

        <?php endif; ?>

        <div class="booking-layout">

            <!-- GUEST INFORMATION -->

            <section class="booking-card">

                <h2>Guest Information</h2>

                <form
                    method="POST"
                    id="bookingForm"
                    action="booking.php?room_id=<?= $roomId ?>">

                    <input
                        type="hidden"
                        name="csrf_token"
                        value="<?= e($_SESSION['booking_csrf']) ?>">

                    <!-- CUSTOMER NAME -->

                    <div class="form-group">

                        <label for="guest_name">
                            Full Name
                        </label>

                        <input
                            type="text"
                            id="guest_name"
                            name="guest_name"
                            value="<?= e($name) ?>"
                            readonly>

                    </div>

                    <div class="form-grid">

                        <!-- EMAIL -->

                        <div class="form-group">

                            <label for="guest_email">
                                Email Address
                            </label>

                            <input
                                type="email"
                                id="guest_email"
                                name="guest_email"
                                value="<?= e($email) ?>"
                                readonly>

                        </div>

                        <!-- PHONE -->

                        <div class="form-group">

                            <label for="guest_phone">
                                Phone Number
                            </label>

                            <input
                                type="tel"
                                id="guest_phone"
                                name="guest_phone"
                                value="<?= e($phone) ?>"
                                readonly>

                        </div>

                    </div>

                    <p class="account-note">
                        Guest information is automatically
                        filled from your registered account.
                    </p>

                    <hr class="form-divider">

                    <h2>Reservation Details</h2>

                    <div class="form-grid">

                        <!-- CHECK-IN -->

                        <div class="form-group">

                            <label for="check_in">
                                Check-in Date *
                            </label>

                            <input
                                type="date"
                                id="check_in"
                                name="check_in"
                                min="<?= date('Y-m-d') ?>"
                                value="<?= e($checkIn) ?>"
                                required>

                        </div>

                        <!-- CHECK-OUT -->

                        <div class="form-group">

                            <label for="check_out">
                                Check-out Date *
                            </label>

                            <input
                                type="date"
                                id="check_out"
                                name="check_out"
                                min="<?= date('Y-m-d', strtotime('+1 day')) ?>"
                                value="<?= e($checkOut) ?>"
                                required>

                        </div>

                    </div>

                    <!-- GUEST COUNT -->

                    <div class="form-group">

                        <label for="guests">
                            Number of Guests *
                        </label>

                        <select
                            name="guests"
                            id="guests"
                            required>

                            <?php for (
                                $i = 1;
                                $i <= (int)$room['max_guests'];
                                $i++
                            ): ?>

                                <option
                                    value="<?= $i ?>"
                                    <?= $guests === $i
                                        ? 'selected' : '' ?>>

                                    <?= $i ?>
                                    <?= $i === 1
                                        ? 'Guest' : 'Guests' ?>

                                </option>

                            <?php endfor; ?>

                        </select>

                    </div>

                    <!-- SUBMIT BUTTON -->

                    <button
                        type="submit"
                        class="booking-btn"
                        <?= !$isRoomAvailable
                            ? 'disabled' : '' ?>>

                        <?= $isRoomAvailable
                            ? 'Confirm Booking'
                            : 'Room Unavailable' ?>

                    </button>

                    <p class="booking-note">

                        Your reservation will be submitted
                        for administrator approval.

                    </p>

                </form>

            </section>

            <!-- SELECTED ROOM -->

            <section class="booking-card">

                <h2>Selected Room</h2>

                <img
                    src="<?= e($imagePath) ?>"
                    alt="<?= e($room['room_name']) ?>"
                    class="room-image">

                <h3 class="room-name">

                    <?= e($room['room_name']) ?>

                </h3>

                <p class="room-description">

                    <?= e($room['description'] ?? '') ?>

                </p>

                <div class="room-info-row">

                    <span>Price per Night</span>

                    <strong>

                        LKR <?= number_format(
                            (float)$room['price_per_night'],
                            2
                        ) ?>

                    </strong>

                </div>

                <div class="room-info-row">

                    <span>Maximum Guests</span>

                    <strong>

                        <?= (int)$room['max_guests'] ?>

                    </strong>

                </div>

                <div class="room-info-row">

                    <span>Room Status</span>

                    <strong class="<?= $isRoomAvailable
                        ? 'status-available'
                        : 'status-unavailable' ?>">

                        <?= e(ucfirst($room['status'])) ?>

                    </strong>

                </div>

                <!-- ROOM FACILITIES -->

                <div class="facilities">

                    <?php

                    $facilities = array_filter(
                        array_map(
                            'trim',
                            explode(
                                ',',
                                (string)($room['facilities'] ?? '')
                            )
                        )
                    );

                    ?>

                    <?php foreach ($facilities as $facility): ?>

                        <span class="facility-tag">

                            <?= e($facility) ?>

                        </span>

                    <?php endforeach; ?>

                </div>

                <!-- PRICE SUMMARY -->

                <div class="price-summary">

                    <div class="price-row">

                        <span>Price per Night</span>

                        <span>

                            LKR <?= number_format(
                                (float)$room['price_per_night'],
                                2
                            ) ?>

                        </span>

                    </div>

                    <div class="price-row">

                        <span>Number of Nights</span>

                        <span id="nightCount">0</span>

                    </div>

                    <div class="price-row">

                        <span>Number of Guests</span>

                        <span id="guestCount">

                            <?= (int)$guests ?>

                        </span>

                    </div>

                    <div class="total-row">

                        <span>Total Price</span>

                        <strong id="totalPrice">

                            LKR 0.00

                        </strong>

                    </div>

                </div>

            </section>

        </div>

    </div>

</main>

<!-- FOOTER -->

<footer class="footer">

    &copy; <?= date('Y') ?>
    Shanu Haven Coastal Villa.
    All Rights Reserved.

</footer>

<!-- JAVASCRIPT -->

<script>

    const bookingForm =
        document.getElementById('bookingForm');

    const checkIn =
        document.getElementById('check_in');

    const checkOut =
        document.getElementById('check_out');

    const guests =
        document.getElementById('guests');

    const nightCount =
        document.getElementById('nightCount');

    const guestCount =
        document.getElementById('guestCount');

    const totalPrice =
        document.getElementById('totalPrice');

    const pricePerNight =
        <?= json_encode((float)$room['price_per_night']) ?>;

    const maxGuests =
        <?= (int)$room['max_guests'] ?>;

    const today =
        <?= json_encode(date('Y-m-d')) ?>;

    const formatter = new Intl.NumberFormat('en-LK', {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2
    });

    // Update minimum checkout date

    function updateCheckoutDate() {

        if (!checkIn.value) return;

        const nextDay =
            new Date(checkIn.value + 'T00:00:00Z');

        nextDay.setUTCDate(nextDay.getUTCDate() + 1);

        const minDate =
            nextDay.toISOString().slice(0, 10);

        checkOut.min = minDate;

        if (
            checkOut.value &&
            checkOut.value < minDate
        ) {
            checkOut.value = '';
        }

    }

    // Calculate total price

    function calculateTotal() {

        guestCount.textContent = guests.value || '0';

        if (!checkIn.value || !checkOut.value) {

            nightCount.textContent = '0';
            totalPrice.textContent = 'LKR 0.00';

            return 0;
        }

        const start =
            new Date(checkIn.value + 'T00:00:00Z');

        const end =
            new Date(checkOut.value + 'T00:00:00Z');

        const nights =
            Math.round(
                (end - start) / 86400000
            );

        if (!Number.isFinite(nights) || nights <= 0) {

            nightCount.textContent = '0';
            totalPrice.textContent = 'LKR 0.00';

            return 0;
        }

        const total = nights * pricePerNight;

        nightCount.textContent = nights;

        totalPrice.textContent =
            'LKR ' + formatter.format(total);

        return nights;

    }

    // Event listeners

    checkIn.addEventListener('change', function () {

        updateCheckoutDate();
        calculateTotal();

    });

    checkOut.addEventListener(
        'change',
        calculateTotal
    );

    guests.addEventListener(
        'change',
        calculateTotal
    );

    // Client-side validation

    bookingForm.addEventListener('submit', function (event) {

        const nights = calculateTotal();

        if (!checkIn.value || !checkOut.value) {

            event.preventDefault();

            alert('Please select your booking dates.');

            return;
        }

        if (checkIn.value < today) {

            event.preventDefault();

            alert('Check-in cannot be in the past.');

            return;
        }

        if (nights <= 0) {

            event.preventDefault();

            alert('Check-out must be after check-in.');

            return;
        }

        if (nights > 30) {

            event.preventDefault();

            alert('Maximum stay is 30 nights.');

            return;
        }

        if (
            Number(guests.value) < 1 ||
            Number(guests.value) > maxGuests
        ) {

            event.preventDefault();

            alert('Invalid number of guests.');

        }

    });

    // Initial calculation

    updateCheckoutDate();
    calculateTotal();

</script>

</body>
</html>
