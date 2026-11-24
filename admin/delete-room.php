
<?php
require_once __DIR__ . '/room-helpers.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    exit('Method not allowed.');
}

checkRoomCsrf();

$id = filter_var(
    $_POST['room_id'] ?? null,
    FILTER_VALIDATE_INT
);

if (!$id || $id < 1) {
    http_response_code(400);
    exit('Invalid room ID.');
}

try {
    $pdo->beginTransaction();

    $stmt = $pdo->prepare(
        "SELECT image
         FROM rooms
         WHERE room_id = ?
         FOR UPDATE"
    );
    $stmt->execute([$id]);

    $room = $stmt->fetch();

    if (!$room) {
        throw new RuntimeException('Room not found.');
    }

    $stmt = $pdo->prepare(
        "SELECT COUNT(*)
         FROM bookings
         WHERE room_id = ?"
    );
    $stmt->execute([$id]);

    if ((int)$stmt->fetchColumn() > 0) {
        throw new RuntimeException(
            'This room has booking history. ' .
            'Set it to Unavailable instead.'
        );
    }

    $stmt = $pdo->prepare(
        "DELETE FROM rooms
         WHERE room_id = ?"
    );
    $stmt->execute([$id]);

    $pdo->commit();

    removeManagedImage($room['image']);

    roomNotice('Room deleted successfully.');

} catch (RuntimeException $e) {

    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }

    roomNotice($e->getMessage());

} catch (Throwable $e) {

    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }

    error_log($e->getMessage());
    roomNotice(
        'Room could not be deleted.'
    );
}

header('Location: rooms.php', true, 303);
exit;

<?php
require_once __DIR__ . '/room-helpers.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    exit('Method not allowed.');
}

checkRoomCsrf();

$id = filter_var(
    $_POST['room_id'] ?? null,
    FILTER_VALIDATE_INT
);

if (!$id || $id < 1) {
    http_response_code(400);
    exit('Invalid room ID.');
}

try {
    $pdo->beginTransaction();

    $stmt = $pdo->prepare(
        "SELECT image
         FROM rooms
         WHERE room_id = ?
         FOR UPDATE"
    );
    $stmt->execute([$id]);

    $room = $stmt->fetch();

    if (!$room) {
        throw new RuntimeException('Room not found.');
    }

    $stmt = $pdo->prepare(
        "SELECT COUNT(*)
         FROM bookings
         WHERE room_id = ?"
    );
    $stmt->execute([$id]);

    if ((int)$stmt->fetchColumn() > 0) {
        throw new RuntimeException(
            'This room has booking history. ' .
            'Set it to Unavailable instead.'
        );
    }

    $stmt = $pdo->prepare(
        "DELETE FROM rooms
         WHERE room_id = ?"
    );
    $stmt->execute([$id]);

    $pdo->commit();

    removeManagedImage($room['image']);

    roomNotice('Room deleted successfully.');

} catch (RuntimeException $e) {

    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }

    roomNotice($e->getMessage());

} catch (Throwable $e) {

    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }

    error_log($e->getMessage());
    roomNotice(
        'Room could not be deleted.'
    );
}

header('Location: rooms.php', true, 303);
exit;
