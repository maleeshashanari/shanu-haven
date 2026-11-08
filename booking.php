
<?php
session_start();
require_once __DIR__ . '/config/database.php';

function e($value) {
    return htmlspecialchars(
        (string)$value,
        ENT_QUOTES,
        'UTF-8'
    );
}

// Get room ID
$roomId = filter_input(
    INPUT_GET,
    'room_id',
    FILTER_VALIDATE_INT
);

if (!$roomId || $roomId < 1) {
    http_response_code(404);
    exit("Invalid room selected.");
}

// Get selected room
$stmt = $pdo->prepare(
    "SELECT * FROM rooms WHERE room_id = ?"
);
$stmt->execute([$roomId]);
$room = $stmt->fetch();

if (!$room) {
    http_response_code(404);
    exit("Room not found.");
}

// Allowed room images
$allowedImages = [
    'Deluxe Room.png',
    'Ocean View Room.png',
    'Family Suite.png'
];

$imageName = in_array(
    $room['image'],
    $allowedImages,
    true
) ? $room['image'] : 'villa-hero.png';

$imagePath = 'assets/images/' .
    rawurlencode($imageName);

// CSRF token
if (empty($_SESSION['booking_csrf'])) {
    $_SESSION['booking_csrf'] =
        bin2hex(random_bytes(32));
}

$errors = [];
$success = false;
$bookingId = null;
$totalPrice = 0;

$name = '';
$email = '';
$phone = '';
$checkIn = '';
$checkOut = '';
$guests = 1;

// Process form
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $name = trim($_POST['guest_name'] ?? '');
    $email = trim($_POST['guest_email'] ?? '');
    $phone = trim($_POST['guest_phone'] ?? '');
    $checkIn = trim($_POST['check_in'] ?? '');
    $checkOut = trim($_POST['check_out'] ?? '');
    $guests = filter_var(
        $_POST['guests'] ?? null,
        FILTER_VALIDATE_INT
    );

    // Security token check
    if (
        !isset($_POST['csrf_token']) ||
        !hash_equals(
            $_SESSION['booking_csrf'],
            $_POST['csrf_token']
        )
    ) {
        $errors[] = "Invalid form submission.";
    }

    if ($name === '' || mb_strlen($name) > 100) {
        $errors[] = "Enter a valid full name.";
    }

    if (
        !filter_var($email, FILTER_VALIDATE_EMAIL) ||
        strlen($email) > 150
    ) {
        $errors[] = "Enter a valid email address.";
    }

    if (
        !preg_match('/^[0-9+\-\s()]{7,20}$/', $phone)
    ) {
        $errors[] = "Enter a valid phone number.";
    }

    if (
        $guests === false ||
        $guests < 1 ||
        $guests > (int)$room['max_guests']
    ) {
        $errors[] = "Invalid number of guests.";
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
        $errors[] = "Please select valid dates.";
    } else {
        $today = new DateTimeImmutable('today');

        if ($start < $today) {
            $errors[] =
                "Check-in cannot be in the past.";
        }

        if ($end <= $start) {
            $errors[] =
                "Check-out must be after check-in.";
        }

        $nights = (int)$start->diff($end)->days;

        if ($nights > 30) {
            $errors[] =
                "Maximum stay is 30 nights.";
        }

        $totalPrice =
            $nights * (float)$room['price_per_night'];
    }

    if ($room['status'] !== 'available') {
        $errors[] = "This room is unavailable.";
    }

    // Save booking
    if (empty($errors)) {
        try {
            $pdo->beginTransaction();

            // Lock selected room row.
            // This serializes bookings for this room.
            $lockStmt = $pdo->prepare(
                "SELECT room_id, status
                 FROM rooms
                 WHERE room_id = ?
                 FOR UPDATE"
            );
            $lockStmt->execute([$roomId]);
            $lockedRoom = $lockStmt->fetch();

            if (
                !$lockedRoom ||
                $lockedRoom['status'] !== 'available'
            ) {
                throw new RuntimeException(
                    "Room is currently unavailable."
                );
            }

            // Check overlapping reservations
            $checkStmt = $pdo->prepare(
                "SELECT COUNT(*)
                 FROM bookings
                 WHERE room_id = ?
                   AND booking_status IN
                       ('pending', 'confirmed')
                   AND check_in < ?
                   AND check_out > ?"
            );

            $checkStmt->execute([
                $roomId,
                $checkOut,
                $checkIn
            ]);

            if ((int)$checkStmt->fetchColumn() > 0) {
                throw new RuntimeException(
                    "This room is already booked " .
                    "for the selected dates."
                );
            }

            $insertStmt = $pdo->prepare(
                "INSERT INTO bookings
                (
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
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'pending')"
            );

            $insertStmt->execute([
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

            // Prevent duplicate submissions
            unset($_SESSION['booking_csrf']);
            $success = true;

        } catch (RuntimeException $ex) {
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
                "Booking could not be saved. " .
                "Please try again.";
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

    <link rel="stylesheet"
          href="assets/css/style.css">

    <style>
        
/* ============================================
   SHANU HAVEN - BOOKING PAGE
   COMPLETE LUXURY CSS
   Colours: Dark Teal, Gold, Cream
============================================ */

/* 1. BOOKING PAGE BACKGROUND */

.booking-page {
    min-height: 80vh;
    padding: 65px 0 90px;

    background:
        radial-gradient(
            circle at 85% 25%,
            rgba(215, 185, 142, 0.32),
            transparent 42%
        ),
        radial-gradient(
            circle at 10% 80%,
            rgba(45, 100, 104, 0.14),
            transparent 42%
        ),
        linear-gradient(
            135deg,
            #F7F4ED 0%,
            #E4EDEA 55%,
            #F7F4ED 100%
        );
}

/* 2. PAGE HEADING */

.booking-heading {
    text-align: center;
    margin-bottom: 40px;
}

.booking-heading h1 {
    color: #12343B;
    font-size: clamp(30px, 4vw, 44px);
    font-weight: 700;
    margin-bottom: 12px;
}

.booking-heading p {
    color: #657779;
    font-size: 15px;
}

/* 3. BACK TO ROOM DETAILS */

.booking-back {
    display: inline-block;
    margin-bottom: 25px;
    color: #2D6468;
    font-weight: 600;
    text-decoration: none;
    transition: all 0.3s ease;
}

.booking-back:hover {
    color: #B59462;
    transform: translateX(-4px);
}

/* 4. BOOKING LAYOUT */

.booking-layout {
    display: grid;
    grid-template-columns: 1.5fr 1fr;
    gap: 30px;
    align-items: start;
}

/* 5. GUEST INFORMATION CARD */

.booking-card {
    background: #FFFFFF;
    padding: 32px;

    border: 2px solid rgba(215, 185, 142, 0.28);
    border-radius: 20px;

    box-shadow:
        0 12px 35px rgba(18, 52, 59, 0.08);

    transition:
        border-color 0.35s ease,
        box-shadow 0.35s ease,
        transform 0.35s ease;
}

/* 6. GOLD HOVER ANIMATION */

.booking-card:hover,
.booking-card:focus-within {
    border-color: #D7B98E;

    box-shadow:
        0 18px 45px rgba(18, 52, 59, 0.14),
        0 0 20px rgba(215, 185, 142, 0.28);

    transform: translateY(-4px);
}

/* 7. CARD HEADINGS */

.booking-card h2,
.booking-summary h2 {
    color: #12343B;
    font-size: 24px;
    font-weight: 700;
    margin-bottom: 24px;
}

/* 8. FORM GRID */

.form-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 20px;
}

/* 9. FORM GROUP */

.form-group {
    margin-bottom: 22px;
}

/* 10. INPUT LABELS */

.form-group label {
    display: block;
    color: #12343B;
    font-weight: 600;
    font-size: 14px;
    margin-bottom: 9px;
}

/* 11. INPUT AND SELECT FIELDS */

.form-group input,
.form-group select {
    display: block;
    width: 100%;

    padding: 14px 15px;

    background: #FFFFFF;
    color: #12343B;

    border: 1.5px solid #D8DFDA;
    border-radius: 9px;

    font-size: 15px;
    font-family: inherit;

    transition:
        border-color 0.3s ease,
        box-shadow 0.3s ease,
        background 0.3s ease;
}

/* 12. INPUT HOVER */

.form-group input:hover,
.form-group select:hover {
    border-color: #D7B98E;
    background: #FFFEFA;
}

/* 13. GOLD FOCUS EFFECT */

.booking-card .form-group input:focus,
.booking-card .form-group select:focus {
    outline: none;

    border-color: #D7B98E;

    box-shadow:
        0 0 0 4px rgba(215, 185, 142, 0.22);

    background: #FFFFFF;
}

/* 14. INPUT PLACEHOLDER */

.form-group input::placeholder {
    color: #98A5A5;
    font-size: 14px;
}

/* 15. BOOKING SUBMIT BUTTON */

.booking-submit {
    display: block;
    width: 100%;

    background: #12343B;
    color: #D7B98E;

    border: 2px solid #12343B;
    border-radius: 10px;

    padding: 16px 20px;

    font-size: 16px;
    font-weight: 700;
    font-family: inherit;

    cursor: pointer;

    transition:
        background 0.35s ease,
        color 0.35s ease,
        border-color 0.35s ease,
        box-shadow 0.35s ease,
        transform 0.35s ease;
}

/* 16. GOLD BUTTON HOVER */

.booking-submit:hover {
    background: #2D6468;
    color: #FFFFFF;

    border-color: #D7B98E;

    transform: translateY(-3px);

    box-shadow:
        0 10px 25px rgba(18, 52, 59, 0.20),
        0 0 15px rgba(215, 185, 142, 0.20);
}

.booking-submit:active {
    transform: translateY(0);
}

.booking-submit:focus-visible {
    outline: 3px solid #D7B98E;
    outline-offset: 4px;
}

/* ============================================
   YOUR SELECTED ROOM - GLASS EFFECT
============================================ */

/* 17. GLASS CARD */

.booking-summary {
    position: relative;
    overflow: hidden;

    padding: 32px;

    background: rgba(255, 255, 255, 0.20);

    backdrop-filter: blur(18px);
    -webkit-backdrop-filter: blur(18px);

    border: 1.5px solid rgba(215, 185, 142, 0.65);
    border-radius: 20px;

    box-shadow:
        0 15px 40px rgba(18, 52, 59, 0.12);

    transition:
        background 0.35s ease,
        border-color 0.35s ease,
        box-shadow 0.35s ease,
        transform 0.35s ease;
}

/* 18. GLASS HOVER */

.booking-summary:hover {
    background: rgba(255, 255, 255, 0.32);

    border-color: #D7B98E;

    transform: translateY(-5px);

    box-shadow:
        0 20px 45px rgba(18, 52, 59, 0.18),
        0 0 18px rgba(215, 185, 142, 0.25);
}

/* 19. GLASS CARD TOP DECORATION */

.booking-summary::before {
    content: "";

    position: absolute;
    top: 0;
    left: 0;

    width: 100%;
    height: 4px;

    background: linear-gradient(
        90deg,
        transparent,
        #D7B98E,
        transparent
    );
}

/* 20. ROOM IMAGE */

.summary-image {
    display: block;

    width: 100%;
    height: 210px;

    object-fit: cover;

    border-radius: 12px;

    margin-bottom: 20px;

    border: 1px solid rgba(215, 185, 142, 0.45);

    transition: transform 0.4s ease;
}

.booking-summary:hover .summary-image {
    transform: scale(1.015);
}

/* 21. ROOM DETAILS ROW */

.summary-row {
    display: flex;
    align-items: center;
    justify-content: space-between;

    gap: 15px;
    margin-bottom: 14px;

    color: #263B3E;
    font-size: 14px;
}

.summary-row strong {
    color: #12343B;
    text-align: right;
}

/* 22. PRICE COLOUR */

.summary-price,
.summary-row .summary-price {
    color: #2D6468;
    font-size: 16px;
    font-weight: 700;
}

/* 23. TRANSPARENT TOTAL PRICE BOX */

.booking-summary .summary-total {
    background: rgba(255, 255, 255, 0.38);

    backdrop-filter: blur(8px);
    -webkit-backdrop-filter: blur(8px);

    border: 1px solid rgba(215, 185, 142, 0.55);
    border-radius: 12px;

    padding: 20px;
    margin-top: 25px;

    box-shadow:
        0 6px 18px rgba(18, 52, 59, 0.05);
}

/* 24. TOTAL PRICE TEXT */

.summary-total p {
    color: #657779;
    margin-bottom: 7px;
}

.summary-total strong {
    display: block;

    color: #12343B;
    font-size: 24px;
    font-weight: 700;

    margin-bottom: 6px;
}

/* ============================================
   BOOKING ALERT MESSAGES
============================================ */

/* 25. COMMON ALERT */

.booking-alert {
    padding: 22px;
    border-radius: 12px;
    margin-bottom: 28px;
    line-height: 1.8;
}

/* 26. ERROR MESSAGE */

.booking-error {
    background: #FFF1F0;
    color: #8B2424;
    border: 1px solid #E5AAAA;
}

.booking-alert ul {
    padding-left: 22px;
    margin-top: 8px;
}

/* 27. SUCCESS MESSAGE */

.booking-success {
    background: #EAF5EE;
    color: #175A35;
    border: 1px solid #9AC9A8;

    box-shadow:
        0 10px 30px rgba(18, 52, 59, 0.08);
}

.booking-success h2 {
    color: #175A35;
    margin-bottom: 15px;
}

.booking-alert a {
    color: inherit;
    text-decoration: underline;
}

/* ============================================
   RESPONSIVE DESIGN
============================================ */

/* 28. TABLET */

@media (max-width: 850px) {

    .booking-layout {
        grid-template-columns: 1fr;
    }

    .booking-summary {
        width: 100%;
    }

    .booking-page {
        padding: 45px 0 70px;
    }
}

/* 29. MOBILE */

@media (max-width: 550px) {

    .booking-page {
        padding: 35px 0 60px;
    }

    .booking-heading {
        margin-bottom: 28px;
    }

    .booking-heading h1 {
        font-size: 30px;
    }

    .booking-card,
    .booking-summary {
        padding: 22px;
        border-radius: 15px;
    }

    .form-grid {
        grid-template-columns: 1fr;
        gap: 0;
    }

    .booking-card h2,
    .booking-summary h2 {
        font-size: 21px;
    }

    .summary-image {
        height: 190px;
    }

    .summary-total strong {
        font-size: 21px;
    }

    .booking-submit {
        font-size: 15px;
        padding: 15px;
    }
}

/* 30. ACCESSIBILITY */

@media (prefers-reduced-motion: reduce) {

    .booking-card,
    .booking-summary,
    .booking-submit,
    .summary-image,
    .form-group input,
    .form-group select {
        transition: none;
    }

    .booking-card:hover,
    .booking-summary:hover,
    .booking-submit:hover,
    .booking-summary:hover .summary-image {
        transform: none;
    }
}


    </style>
</head>

<body>

<header class="site-header">
    <nav class="navbar container">

        <a href="index.php" class="logo">
            <img
                src="assets/images/shanu-logo.png"
                alt="Shanu Haven"
                class="logo-image">
        </a>

        <a href="index.php#rooms"
           class="btn btn-cream">
            Back to Rooms
        </a>

    </nav>
</header>

<main class="booking-page">
<div class="container">

    <a class="booking-back"
       href="room-details.php?id=<?= $roomId ?>">
        &larr; Back to Room Details
    </a>

    <div class="booking-heading">
        <p class="section-label">
            SHANU HAVEN RESERVATIONS
        </p>
        <h1>Book Your Stay</h1>
        <p>
            Complete the form to request your booking.
        </p>
    </div>

    <?php if (!empty($errors)): ?>
        <div class="booking-alert booking-error"
             role="alert">
            <strong>Please correct the following:</strong>
            <ul>
                <?php foreach ($errors as $error): ?>
                    <li><?= e($error) ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>

    <?php if ($success): ?>

        <div class="booking-alert booking-success"
             role="status">
            <h2>Booking Request Submitted!</h2>

            <p>
                Thank you, <?= e($name) ?>.
                Your booking request has been saved.
            </p>

            <p>
                Booking Reference:
                <strong>#<?= e($bookingId) ?></strong>
            </p>

            <p>
                Status: <strong>Pending</strong>
            </p>

            <p>
                Your reservation is not yet confirmed.
            </p>

            <a href="index.php">
                Return to Home
            </a>
        </div>

    <?php else: ?>

    <div class="booking-layout">

        <!-- BOOKING FORM -->
        <div class="booking-card">

            <h2>Guest Information</h2>

            <form method="POST"
                  action="booking.php?room_id=<?= $roomId ?>">

                <input type="hidden"
                       name="csrf_token"
                       value="<?= e($_SESSION['booking_csrf']) ?>">

                <div class="form-group">
                    <label for="guest_name">
                        Full Name *
                    </label>

                    <input
                        type="text"
                        id="guest_name"
                        name="guest_name"
                        maxlength="100"
                        value="<?= e($name) ?>"
                        placeholder="Enter your full name"
                        autocomplete="name"
                        required>
                </div>

                <div class="form-grid">

                    <div class="form-group">
                        <label for="guest_email">
                            Email Address *
                        </label>

                        <input
                            type="email"
                            id="guest_email"
                            name="guest_email"
                            maxlength="150"
                            value="<?= e($email) ?>"
                            placeholder="example@email.com"
                            autocomplete="email"
                            required>
                    </div>

                    <div class="form-group">
                        <label for="guest_phone">
                            Phone Number *
                        </label>

                        <input
                            type="tel"
                            id="guest_phone"
                            name="guest_phone"
                            maxlength="20"
                            value="<?= e($phone) ?>"
                            placeholder="07XXXXXXXX"
                            autocomplete="tel"
                            required>
                    </div>

                </div>

                <h2>Stay Information</h2>

                <div class="form-grid">

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

                <div class="form-group">
                    <label for="guests">
                        Number of Guests *
                    </label>

                    <select
                        id="guests"
                        name="guests"
                        required>

                        <?php for (
                            $i = 1;
                            $i <= (int)$room['max_guests'];
                            $i++
                        ): ?>

                            <option
                                value="<?= $i ?>"
                                <?= $guests == $i
                                    ? 'selected' : '' ?>>
                                <?= $i ?>
                                <?= $i === 1
                                    ? 'Guest' : 'Guests' ?>
                            </option>

                        <?php endfor; ?>

                    </select>
                </div>

                <p style="color:#657779;
                          font-size:13px;
                          margin-bottom:22px;">
                    Your booking will be saved as
                    pending until confirmed.
                </p>

                <button type="submit"
                        class="booking-submit">
                    Confirm Booking Request
                </button>

            </form>

        </div>

        <!-- BOOKING SUMMARY -->
        <aside class="booking-summary">

            <h2>Your Selected Room</h2>

            <img
                src="<?= e($imagePath) ?>"
                alt="<?= e($room['room_name']) ?>"
                class="summary-image">

            <h3 style="color:#12343B;
                       margin-bottom:18px;">
                <?= e($room['room_name']) ?>
            </h3>

            <div class="summary-row">
                <span>Maximum Guests</span>
                <strong>
                    <?= (int)$room['max_guests'] ?>
                </strong>
            </div>

            <div class="summary-row">
                <span>Price Per Night</span>
                <strong class="summary-price">
                    LKR <?= number_format(
                        (float)$room['price_per_night']
                    ) ?>
                </strong>
            </div>

            <div class="summary-row">
                <span>Room Status</span>
                <strong>
                    <?= e(ucfirst($room['status'])) ?>
                </strong>
            </div>

            <div class="summary-total">
                <p>Estimated Total</p>
                <strong id="estimated-total">
                    Select your dates
                </strong>
                <p id="night-count"
                   style="font-size:13px;
                          color:#657779;">
                    Based on selected nights
                </p>
            </div>

        </aside>

    </div>

    <?php endif; ?>

</div>
</main>

<footer class="footer">
    <div class="footer-bottom">
        <p>
            &copy; <?= date('Y') ?>
            Shanu Haven. All Rights Reserved.
        </p>
    </div>
</footer>

<script>
const checkIn = document.getElementById('check_in');
const checkOut = document.getElementById('check_out');
const total = document.getElementById('estimated-total');
const nightCount = document.getElementById('night-count');

const pricePerNight =
    <?= json_encode((float)$room['price_per_night']) ?>;

function updateTotal() {
    if (!checkIn || !checkOut || !total) return;

    if (checkIn.value) {
        const nextDay = new Date(
            checkIn.value + 'T00:00:00'
        );

        nextDay.setDate(nextDay.getDate() + 1);

        const minDate = [
            nextDay.getFullYear(),
            String(nextDay.getMonth() + 1).padStart(2, '0'),
            String(nextDay.getDate()).padStart(2, '0')
        ].join('-');

        checkOut.min = minDate;
    }

    if (!checkIn.value || !checkOut.value) {
        total.textContent = 'Select your dates';
        nightCount.textContent = 'Based on selected nights';
        return;
    }

    const start = Date.parse(checkIn.value + 'T00:00:00Z');
    const end = Date.parse(checkOut.value + 'T00:00:00Z');

    const nights = Math.round(
        (end - start) / 86400000
    );

    if (nights <= 0 || nights > 30) {
        total.textContent = 'Invalid date range';
        nightCount.textContent = 'Maximum 30 nights';
        return;
    }

    const amount = nights * pricePerNight;

    total.textContent = 'LKR ' +
        amount.toLocaleString('en-LK');

    nightCount.textContent =
        nights + (nights === 1 ? ' night' : ' nights');
}

if (checkIn && checkOut) {
    checkIn.addEventListener('change', updateTotal);
    checkOut.addEventListener('change', updateTotal);
    updateTotal();
}
</script>

</body>
</html>