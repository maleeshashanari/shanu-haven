
<?php
require_once __DIR__ . '/room-helpers.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    exit('Method not allowed.');
}

checkRoomCsrf();

$idRaw = $_POST['room_id'] ?? '';
$isEdit = $idRaw !== '';

$id = $isEdit
    ? filter_var($idRaw, FILTER_VALIDATE_INT)
    : null;

if ($isEdit && (!$id || $id < 1)) {
    http_response_code(400);
    exit('Invalid room ID.');
}

$redirect = $isEdit
    ? 'room-form.php?id=' . $id
    : 'room-form.php';

$newImage = null;
$oldImage = null;
$saved = false;

try {
    $name = trim((string)($_POST['room_name'] ?? ''));
    $description = trim(
        (string)($_POST['description'] ?? '')
    );
    $facilities = trim(
        (string)($_POST['facilities'] ?? '')
    );

    $guests = filter_var(
        $_POST['max_guests'] ?? null,
        FILTER_VALIDATE_INT
    );

    $priceRaw = trim(
        (string)($_POST['price_per_night'] ?? '')
    );

    $status = $_POST['status'] ?? '';

    if (
        $name === '' ||
        mb_strlen($name) > 100 ||
        $description === '' ||
        $facilities === '' ||
        mb_strlen($facilities) > 255
    ) {
        throw new RuntimeException(
            'Please enter valid room information.'
        );
    }

    if (!$guests || $guests < 1 || $guests > 100) {
        throw new RuntimeException(
            'Maximum guests must be between 1 and 100.'
        );
    }

    if (
        !preg_match(
            '/^\d{1,8}(\.\d{1,2})?$/',
            $priceRaw
        ) ||
        (float)$priceRaw <= 0
    ) {
        throw new RuntimeException(
            'Please enter a valid room price.'
        );
    }

    if (!in_array(
        $status,
        ['available', 'unavailable'],
        true
    )) {
        throw new RuntimeException(
            'Invalid availability status.'
        );
    }

    if ($isEdit) {
        $stmt = $pdo->prepare(
            "SELECT image
             FROM rooms
             WHERE room_id = ?"
        );
        $stmt->execute([$id]);

        $oldImage = $stmt->fetchColumn();

        if ($oldImage === false) {
            throw new RuntimeException(
                'Room not found.'
            );
        }
    }

    $newImage = uploadRoomImage(
        $_FILES['room_image'] ?? []
    );

    if (!$isEdit && !$newImage) {
        throw new RuntimeException(
            'Please upload a room image.'
        );
    }

    $image = $newImage ?: $oldImage;

    if ($isEdit) {
        $stmt = $pdo->prepare(
            "UPDATE rooms
             SET room_name = ?,
                 description = ?,
                 facilities = ?,
                 max_guests = ?,
                 price_per_night = ?,
                 image = ?,
                 status = ?
             WHERE room_id = ?"
        );

        $stmt->execute([
            $name,
            $description,
            $facilities,
            $guests,
            $priceRaw,
            $image,
            $status,
            $id
        ]);

        $message = 'Room updated successfully.';

    } else {
        $stmt = $pdo->prepare(
            "INSERT INTO rooms
             (room_name, description, facilities,
              max_guests, price_per_night,
              image, status)
             VALUES (?, ?, ?, ?, ?, ?, ?)"
        );

        $stmt->execute([
            $name,
            $description,
            $facilities,
            $guests,
            $priceRaw,
            $image,
            $status
        ]);

        $message = 'New room added successfully.';
    }

    $saved = true;

    // Delete the old managed photo only after
    // the database update has succeeded.
    if ($isEdit && $newImage &&
        $oldImage !== $newImage) {
        removeManagedImage($oldImage);
    }

    roomNotice($message);

    header('Location: rooms.php', true, 303);
    exit;

} catch (RuntimeException $e) {
    roomNotice($e->getMessage());

} catch (Throwable $e) {
    error_log($e->getMessage());
    roomNotice(
        'Unable to save the room. Please try again.'
    );
}

// Clean up a new upload if saving failed.
if (!$saved && $newImage) {
    removeManagedImage($newImage);
}

header('Location: ' . $redirect, true, 303);
exit;
