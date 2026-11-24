
<?php

ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
error_reporting(E_ALL);

require_once __DIR__ . '/auth.php';
requireAdminLogin();

require_once __DIR__ . '/../config/database.php';

header('Cache-Control: no-store, private');

function e($value) {
    return htmlspecialchars(
        (string)$value,
        ENT_QUOTES,
        'UTF-8'
    );
}

function bookingRef($id) {
    return 'SH-' . str_pad(
        (string)$id,
        6,
        '0',
        STR_PAD_LEFT
    );
}

if (empty($_SESSION['booking_csrf_admin'])) {
    $_SESSION['booking_csrf_admin'] =
        bin2hex(random_bytes(32));
}

$search = trim((string)($_GET['q'] ?? ''));
$search = substr($search, 0, 150);

$status = $_GET['status'] ?? 'all';

$allowedStatuses = [
    'all',
    'pending',
    'confirmed',
    'cancelled'
];

if (!is_string($status) ||
    !in_array($status, $allowedStatuses, true)) {
    $status = 'all';
}

// Build a safe, parameterized query.
$sql = "
    SELECT
        b.booking_id,
        b.room_id,
        b.guest_name,
        b.guest_email,
        b.guest_phone,
        b.check_in,
        b.check_out,
        b.guests,
        b.total_price,
        b.booking_status,
        b.created_at,
        r.room_name
    FROM bookings b
    INNER JOIN rooms r
        ON b.room_id = r.room_id
    WHERE 1 = 1
";

$params = [];

if ($status !== 'all') {
    $sql .= " AND b.booking_status = :status";
    $params['status'] = $status;
}

if ($search !== '') {
    $sql .= "
        AND (
            b.guest_name LIKE :name
            OR b.guest_email LIKE :email
            OR b.guest_phone LIKE :phone
            OR r.room_name LIKE :room
            OR CAST(b.booking_id AS CHAR) LIKE :id
        )
    ";

    $term = '%' . $search . '%';

    $params['name'] = $term;
    $params['email'] = $term;
    $params['phone'] = $term;
    $params['room'] = $term;
    $params['id'] = $term;
}

$sql .= " ORDER BY b.booking_id DESC";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);

$bookings = $stmt->fetchAll();

// Statistics for the filter buttons.
$counts = [
    'all' => 0,
    'pending' => 0,
    'confirmed' => 0,
    'cancelled' => 0
];

$stmt = $pdo->query(
    "SELECT booking_status, COUNT(*) AS total
     FROM bookings
     GROUP BY booking_status"
);

foreach ($stmt->fetchAll() as $row) {
    $count = (int)$row['total'];

    $counts['all'] += $count;

    if (array_key_exists(
        $row['booking_status'],
        $counts
    )) {
        $counts[$row['booking_status']] = $count;
    }
}

$notice = $_SESSION['admin_notice'] ?? '';
unset($_SESSION['admin_notice']);
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport"
      content="width=device-width, initial-scale=1">

<title>All Bookings | Shanu Haven</title>

<link rel="stylesheet"
      href="../assets/css/admin.css?v=31">
</head>

<body class="sh-dashboard">

<header class="sh-admin-header">
    <div class="sh-header-inner">

        <a href="dashboard.php">
            <img
                src="../assets/images/shanu-logo.png"
                alt="Shanu Haven"
                class="sh-admin-logo">
        </a>

        <div class="sh-header-right">
            <a href="dashboard.php"
               class="sh-nav-link">
                Dashboard
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

    <section class="sh-dashboard-heading">
        <p class="sh-eyebrow">
            SHANU HAVEN MANAGEMENT
        </p>

        <h1>All Bookings</h1>

        <p>
            Search, filter and manage
            your villa reservations.
        </p>
    </section>

    <?php if ($notice): ?>
        <div class="sh-notice" role="status">
            <?= e($notice) ?>
        </div>
    <?php endif; ?>

    <section class="sh-bookings-panel">

        <!-- SEARCH FORM -->

        <form method="GET"
              action="bookings.php"
              class="sh-search-form"
              id="bookingSearchForm">

            <label for="bookingSearch">
                Search Bookings
            </label>

            <div class="sh-search-row">

                <input
                    type="search"
                    name="q"
                    id="bookingSearch"
                    value="<?= e($search) ?>"
                    placeholder="Booking ID, guest, email, phone or room">

                <input type="hidden"
                       name="status"
                       value="<?= e($status) ?>">

                <button type="submit"
                        class="sh-search-btn">
                    Search
                </button>

                <a href="bookings.php"
                   class="sh-reset-btn">
                    Reset
                </a>

            </div>
        </form>

        <!-- STATUS FILTERS -->

        <nav class="sh-filter-tabs"
             aria-label="Booking status filters">

            <?php foreach ($allowedStatuses as $filter): ?>

                <a
                    class="sh-filter-tab <?= $status === $filter
                        ? 'active' : '' ?>"
                    href="bookings.php?<?= e(
                        http_build_query([
                            'status' => $filter,
                            'q' => $search
                        ])
                    ) ?>">

                    <?= e(ucfirst($filter)) ?>

                    <span>
                        <?= $counts[$filter] ?>
                    </span>
                </a>

            <?php endforeach; ?>

        </nav>

        <div class="sh-panel-heading">
            <div>
                <p class="sh-eyebrow">
                    RESERVATION RECORDS
                </p>

                <h2>Booking List</h2>
            </div>

            <span class="sh-record-count">
                <?= count($bookings) ?>
                Result(s)
            </span>
        </div>

        <!-- BOOKING TABLE -->

        <div class="sh-table-wrapper">

            <table class="sh-booking-table">

                <thead>
                    <tr>
                        <th>Booking ID</th>
                        <th>Guest</th>
                        <th>Room</th>
                        <th>Check-in</th>
                        <th>Check-out</th>
                        <th>Total</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>

                <tbody>

                <?php if (!$bookings): ?>

                    <tr>
                        <td colspan="8"
                            class="sh-empty">
                            No matching bookings found.
                        </td>
                    </tr>

                <?php else: ?>

                <?php foreach ($bookings as $booking): ?>

                    <?php
                    $nights = (new DateTimeImmutable(
                        $booking['check_in']
                    ))->diff(
                        new DateTimeImmutable(
                            $booking['check_out']
                        )
                    )->days;

                    $details = [
                        'reference' => bookingRef(
                            $booking['booking_id']
                        ),
                        'name' => $booking['guest_name'],
                        'email' => $booking['guest_email'],
                        'phone' => $booking['guest_phone'],
                        'room' => $booking['room_name'],
                        'checkIn' => $booking['check_in'],
                        'checkOut' => $booking['check_out'],
                        'nights' => $nights,
                        'guests' => $booking['guests'],
                        'amount' => 'LKR ' . number_format(
                            (float)$booking['total_price'],
                            2
                        ),
                        'status' => ucfirst(
                            $booking['booking_status']
                        ),
                        'created' => $booking['created_at']
                    ];
                    ?>

                    <tr>

                        <td class="sh-ref">
                            <?= e(bookingRef(
                                $booking['booking_id']
                            )) ?>
                        </td>

                        <td>
                            <strong>
                                <?= e($booking['guest_name']) ?>
                            </strong>

                            <small>
                                <?= e($booking['guest_email']) ?>
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

                        <td class="sh-amount">
                            LKR
                            <?= number_format(
                                (float)$booking['total_price'],
                                2
                            ) ?>
                        </td>

                        <td>
                            <span class="sh-status sh-<?=
                                e($booking['booking_status'])
                            ?>">
                                <?= e(ucfirst(
                                    $booking['booking_status']
                                )) ?>
                            </span>
                        </td>

                        <td>
                            <div class="sh-actions">

                                <button
                                    type="button"
                                    class="sh-view-btn"
                                    data-booking="<?= e(
                                        json_encode(
                                            $details,
                                            JSON_HEX_TAG |
                                            JSON_HEX_AMP |
                                            JSON_HEX_APOS |
                                            JSON_HEX_QUOT
                                        )
                                    ) ?>">
                                    View
                                </button>

                                <?php if (
                                    $booking['booking_status']
                                    === 'pending'
                                ): ?>

                                    <form
                                        method="POST"
                                        action="update-booking.php">

                                        <input type="hidden"
                                               name="csrf_token"
                                               value="<?= e(
                                                   $_SESSION[
                                                       'booking_csrf_admin'
                                                   ]
                                               ) ?>">

                                        <input type="hidden"
                                               name="booking_id"
                                               value="<?=
                                                   (int)$booking[
                                                       'booking_id'
                                                   ]
                                               ?>">

                                        <input type="hidden"
                                               name="action"
                                               value="confirm">

                                        <input type="hidden"
                                               name="return_to"
                                               value="bookings">

                                        <button
                                            type="submit"
                                            class="sh-confirm-btn">
                                            Confirm
                                        </button>
                                    </form>

                                    <form
                                        method="POST"
                                        action="update-booking.php"
                                        onsubmit="return confirm('Cancel this booking request?');">

                                        <input type="hidden"
                                               name="csrf_token"
                                               value="<?= e(
                                                   $_SESSION[
                                                       'booking_csrf_admin'
                                                   ]
                                               ) ?>">

                                        <input type="hidden"
                                               name="booking_id"
                                               value="<?=
                                                   (int)$booking[
                                                       'booking_id'
                                                   ]
                                               ?>">

                                        <input type="hidden"
                                               name="action"
                                               value="cancel">

                                        <input type="hidden"
                                               name="return_to"
                                               value="bookings">

                                        <button
                                            type="submit"
                                            class="sh-cancel-btn">
                                            Cancel
                                        </button>
                                    </form>

                                <?php endif; ?>

                            </div>
                        </td>

                    </tr>

                <?php endforeach; ?>
                <?php endif; ?>

                </tbody>
            </table>

        </div>

    </section>
</main>

<!-- BOOKING DETAILS MODAL -->

<div class="sh-modal-overlay"
     id="bookingModal"
     hidden>

    <section class="sh-modal"
             role="dialog"
             aria-modal="true"
             aria-labelledby="modalTitle">

        <div class="sh-modal-header">

            <div>
                <p class="sh-eyebrow">
                    RESERVATION DETAILS
                </p>

                <h2 id="modalTitle">
                    Booking Details
                </h2>
            </div>

            <button type="button"
                    class="sh-modal-close"
                    id="closeBookingModal"
                    aria-label="Close booking details">
                &times;
            </button>
        </div>

        <div class="sh-modal-grid"
             id="bookingDetailsContent">
        </div>

        <button type="button"
                class="sh-modal-done"
                id="doneBookingModal">
            Close Details
        </button>

    </section>
</div>

<footer class="sh-admin-footer">
    &copy; <?= date('Y') ?>
    Shanu Haven Coastal Villa.
</footer>

<script>
const modal = document.getElementById('bookingModal');
const content = document.getElementById(
    'bookingDetailsContent'
);

const closeButton = document.getElementById(
    'closeBookingModal'
);

const doneButton = document.getElementById(
    'doneBookingModal'
);

let previousFocus = null;

const fields = [
    ['reference', 'Booking Reference'],
    ['name', 'Guest Name'],
    ['email', 'Email Address'],
    ['phone', 'Phone Number'],
    ['room', 'Selected Room'],
    ['checkIn', 'Check-in'],
    ['checkOut', 'Check-out'],
    ['nights', 'Number of Nights'],
    ['guests', 'Number of Guests'],
    ['amount', 'Total Amount'],
    ['status', 'Booking Status'],
    ['created', 'Request Submitted']
];

function openBookingModal(data) {
    previousFocus = document.activeElement;
    content.replaceChildren();

    fields.forEach(([key, label]) => {
        const item = document.createElement('div');
        item.className = 'sh-modal-item';

        const heading = document.createElement('span');
        heading.textContent = label;

        const value = document.createElement('strong');
        value.textContent = data[key] ?? '-';

        item.append(heading, value);
        content.appendChild(item);
    });

    modal.hidden = false;
    document.body.classList.add('sh-modal-open');
    closeButton.focus();
}

function closeBookingModal() {
    modal.hidden = true;
    document.body.classList.remove('sh-modal-open');

    if (previousFocus) {
        previousFocus.focus();
    }
}

document.querySelectorAll('.sh-view-btn').forEach(
    button => {
        button.addEventListener('click', () => {
            const data = JSON.parse(
                button.dataset.booking
            );

            openBookingModal(data);
        });
    }
);

closeButton.addEventListener(
    'click',
    closeBookingModal
);

doneButton.addEventListener(
    'click',
    closeBookingModal
);

modal.addEventListener('click', event => {
    if (event.target === modal) {
        closeBookingModal();
    }
});

document.addEventListener('keydown', event => {
    if (modal.hidden) return;

    if (event.key === 'Escape') {
        closeBookingModal();
    }

    if (event.key === 'Tab') {
        const buttons = [
            closeButton,
            doneButton
        ];

        const currentIndex = buttons.indexOf(
            document.activeElement
        );

        if (event.shiftKey && currentIndex <= 0) {
            event.preventDefault();
            doneButton.focus();
        } else if (
            !event.shiftKey &&
            currentIndex === buttons.length - 1
        ) {
            event.preventDefault();
            closeButton.focus();
        }
    }
});
</script>

</body>
</html>
