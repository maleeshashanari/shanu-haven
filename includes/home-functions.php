<?php

// Room availability search
$checkIn = trim($_GET['check_in'] ?? '');
$checkOut = trim($_GET['check_out'] ?? '');
$guests = (int)($_GET['guests'] ?? 1);

$searchError = '';
$isSearch = isset($_GET['search']);

$sql = "SELECT * FROM rooms";
$params = [];

if ($isSearch) {
    $start = DateTimeImmutable::createFromFormat('!Y-m-d', $checkIn);
    $end = DateTimeImmutable::createFromFormat('!Y-m-d', $checkOut);

    if (
        !$start || !$end ||
        $start->format('Y-m-d') !== $checkIn ||
        $end->format('Y-m-d') !== $checkOut ||
        $start < new DateTimeImmutable('today') ||
        $end <= $start ||
        $start->diff($end)->days > 30 ||
        $guests < 1
    ) {
        $searchError = 'Please enter valid dates and guest count.';
        $isSearch = false;
    } else {
        $sql .= " WHERE status = 'available'
                  AND max_guests >= ?
                  AND NOT EXISTS (
                      SELECT 1 FROM bookings b
                      WHERE b.room_id = rooms.room_id
                      AND b.booking_status IN ('pending', 'confirmed')
                      AND b.check_in < ?
                      AND b.check_out > ?
                  )";

        $params = [$guests, $checkOut, $checkIn];
    }
}

$sql .= " ORDER BY room_id ASC";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$rooms = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Escape HTML output
function e($value) {
    return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
}

// Safe room image path
function homeRoomImage($filename) {
    $filename = (string)$filename;

    $oldImages = [
        'Deluxe Room.png',
        'Ocean View Room.png',
        'Family Suite.png',
        'villa-hero.png'
    ];

    if (in_array($filename, $oldImages, true)) {
        return 'assets/images/' . rawurlencode($filename);
    }

    if (preg_match('/^room_[a-f0-9]{32}\.(jpg|png|webp)$/', $filename)) {
        return 'assets/images/rooms/' . rawurlencode($filename);
    }

    return 'assets/images/villa-hero.png';
}