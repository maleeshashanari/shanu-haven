<?php

function e($value) {
    return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
}

function bookingRef($id) {
    return 'SH-' . str_pad((string)$id, 6, '0', STR_PAD_LEFT);
}

// CSRF protection
if (empty($_SESSION['booking_csrf_admin'])) {
    $_SESSION['booking_csrf_admin'] = bin2hex(random_bytes(32));
}

// Booking statistics
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

// Latest 10 bookings
$stmt = $pdo->query(
    "SELECT b.booking_id, b.guest_name, b.guest_email,
            b.guest_phone, b.check_in, b.check_out,
            b.guests, b.total_price, b.booking_status,
            r.room_name
     FROM bookings b
     INNER JOIN rooms r ON b.room_id = r.room_id
     ORDER BY b.booking_id DESC
     LIMIT 10"
);

$bookings = $stmt->fetchAll();

// Admin session details
$adminName = $_SESSION['admin_name'] ?? 'Admin';
$notice = $_SESSION['admin_notice'] ?? '';

unset($_SESSION['admin_notice']);