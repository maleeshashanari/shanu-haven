
<?php

function e($value) {
    return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
}

function bookingRef($id) {
    return 'SH-' . str_pad((string)$id, 6, '0', STR_PAD_LEFT);
}

if (empty($_SESSION['booking_csrf_admin'])) {
    $_SESSION['booking_csrf_admin'] = bin2hex(random_bytes(32));
}

$search = substr(trim((string)($_GET['q'] ?? '')), 0, 150);
$status = $_GET['status'] ?? 'all';

$allowedStatuses = ['all', 'pending', 'confirmed', 'cancelled'];

if (!is_string($status) || !in_array($status, $allowedStatuses, true)) {
    $status = 'all';
}

// Get booking records
$sql = "SELECT b.*, r.room_name
        FROM bookings b
        INNER JOIN rooms r ON b.room_id = r.room_id
        WHERE 1 = 1";

$params = [];

if ($status !== 'all') {
    $sql .= " AND b.booking_status = :status";
    $params['status'] = $status;
}

if ($search !== '') {
    $sql .= " AND (
        b.guest_name LIKE :name
        OR b.guest_email LIKE :email
        OR b.guest_phone LIKE :phone
        OR r.room_name LIKE :room
        OR CAST(b.booking_id AS CHAR) LIKE :id
    )";

    $term = '%' . $search . '%';

    foreach (['name', 'email', 'phone', 'room', 'id'] as $key) {
        $params[$key] = $term;
    }
}

$sql .= " ORDER BY b.booking_id DESC";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$bookings = $stmt->fetchAll();

// Booking statistics
$counts = array_fill_keys($allowedStatuses, 0);

$stmt = $pdo->query(
    "SELECT booking_status, COUNT(*) AS total
     FROM bookings GROUP BY booking_status"
);

foreach ($stmt->fetchAll() as $row) {
    $count = (int)$row['total'];
    $counts['all'] += $count;

    if (array_key_exists($row['booking_status'], $counts)) {
        $counts[$row['booking_status']] = $count;
    }
}

$notice = $_SESSION['admin_notice'] ?? '';
unset($_SESSION['admin_notice']);

function bookingDetails($booking) {
    $nights = (new DateTimeImmutable($booking['check_in']))
        ->diff(new DateTimeImmutable($booking['check_out']))->days;

    return [
        'reference' => bookingRef($booking['booking_id']),
        'name' => $booking['guest_name'],
        'email' => $booking['guest_email'],
        'phone' => $booking['guest_phone'],
        'room' => $booking['room_name'],
        'checkIn' => $booking['check_in'],
        'checkOut' => $booking['check_out'],
        'nights' => $nights,
        'guests' => $booking['guests'],
        'amount' => 'LKR ' . number_format((float)$booking['total_price'], 2),
        'status' => ucfirst($booking['booking_status']),
        'created' => $booking['created_at']
    ];
}
