
<?php
require_once __DIR__ . '/auth.php';
requireAdminLogin();

require_once __DIR__ . '/../config/database.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    exit('Method not allowed.');
}

// Validate CSRF token
$token = $_POST['csrf_token'] ?? '';

if (
    !is_string($token) ||
    empty($_SESSION['booking_csrf_admin']) ||
    !hash_equals(
        $_SESSION['booking_csrf_admin'],
        $token
    )
) {
    http_response_code(403);
    exit('Invalid security token.');
}

$bookingId = filter_var(
    $_POST['booking_id'] ?? null,
    FILTER_VALIDATE_INT
);

$action = $_POST['action'] ?? '';

if (
    !$bookingId ||
    !is_string($action) ||
    !in_array($action, ['confirm', 'cancel'], true)
) {
    http_response_code(400);
    exit('Invalid booking request.');
}

try {
    $pdo->beginTransaction();

    // Find the booking's room
    $stmt = $pdo->prepare(
        "SELECT room_id
         FROM bookings
         WHERE booking_id = ?"
    );

    $stmt->execute([$bookingId]);
    $roomId = $stmt->fetchColumn();

    if ($roomId === false) {
        throw new RuntimeException(
            'Booking not found.'
        );
    }

    // Lock room to coordinate booking changes
    $stmt = $pdo->prepare(
        "SELECT status
         FROM rooms
         WHERE room_id = ?
         FOR UPDATE"
    );

    $stmt->execute([$roomId]);
    $room = $stmt->fetch();

    if (!$room) {
        throw new RuntimeException(
            'Room not found.'
        );
    }

    // Lock booking and check current status
    $stmt = $pdo->prepare(
        "SELECT booking_status, check_in, check_out
         FROM bookings
         WHERE booking_id = ?
         FOR UPDATE"
    );

    $stmt->execute([$bookingId]);
    $booking = $stmt->fetch();

    if (
        !$booking ||
        $booking['booking_status'] !== 'pending'
    ) {
        throw new RuntimeException(
            'Only pending bookings can be updated.'
        );
    }

    if ($action === 'confirm') {

        if ($room['status'] !== 'available') {
            throw new RuntimeException(
                'Room is currently unavailable.'
            );
        }

        // Prevent overlapping confirmed bookings
        $stmt = $pdo->prepare(
            "SELECT COUNT(*)
             FROM bookings
             WHERE room_id = ?
               AND booking_id <> ?
               AND booking_status = 'confirmed'
               AND check_in < ?
               AND check_out > ?"
        );

        $stmt->execute([
            $roomId,
            $bookingId,
            $booking['check_out'],
            $booking['check_in']
        ]);

        if ((int)$stmt->fetchColumn() > 0) {
            throw new RuntimeException(
                'Another confirmed booking overlaps these dates.'
            );
        }

        $newStatus = 'confirmed';
        $message = 'Booking confirmed successfully.';

    } else {

        $newStatus = 'cancelled';
        $message = 'Booking cancelled successfully.';
    }

    // Update only pending bookings
    $stmt = $pdo->prepare(
        "UPDATE bookings
         SET booking_status = ?
         WHERE booking_id = ?
           AND booking_status = 'pending'"
    );

    $stmt->execute([
        $newStatus,
        $bookingId
    ]);

    if ($stmt->rowCount() !== 1) {
        throw new RuntimeException(
            'Booking could not be updated.'
        );
    }

    $pdo->commit();

    $_SESSION['admin_notice'] = $message;

} catch (RuntimeException $e) {

    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }

    $_SESSION['admin_notice'] = $e->getMessage();

} catch (Throwable $e) {

    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }

    error_log($e->getMessage());

    $_SESSION['admin_notice'] =
        'Something went wrong. Please try again.';
}

$returnTo = $_POST['return_to'] ?? '';

$destination = (
    is_string($returnTo) &&
    $returnTo === 'bookings'
)
    ? 'bookings.php'
    : 'dashboard.php';

header('Location: ' . $destination, true, 303);
exit;
