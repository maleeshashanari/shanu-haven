
<?php
require_once __DIR__ . '/room-helpers.php';

$id = filter_input(
    INPUT_GET,
    'id',
    FILTER_VALIDATE_INT
);

$isEdit = isset($_GET['id']);

$room = [
    'room_id' => '',
    'room_name' => '',
    'description' => '',
    'facilities' => '',
    'max_guests' => 2,
    'price_per_night' => '',
    'image' => '',
    'status' => 'available'
];

if ($isEdit) {

    if (!$id || $id < 1) {
        http_response_code(400);
        exit('Invalid room ID.');
    }

    $stmt = $pdo->prepare(
        "SELECT * FROM rooms WHERE room_id = ?"
    );

    $stmt->execute([$id]);
    $existing = $stmt->fetch();

    if (!$existing) {
        http_response_code(404);
        exit('Room not found.');
    }

    $room = $existing;
}

$notice = $_SESSION['room_notice'] ?? '';
unset($_SESSION['room_notice']);
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport"
      content="width=device-width, initial-scale=1">

<title>
    <?= $isEdit ? 'Edit Room' : 'Add New Room' ?>
    | Shanu Haven
</title>

<link rel="stylesheet" href="../assets/css/admin.css?v=42">
</head>

<body class="sh-dashboard">

<header class="sh-admin-header">
    <div class="sh-header-inner">

        <a href="rooms.php">
            <img
                src="../assets/images/shanu-logo.png"
                class="sh-admin-logo"
                alt="Shanu Haven">
        </a>

        <div class="sh-header-right">
            <a href="dashboard.php"
               class="sh-nav-link">
                Dashboard
            </a>

            <a href="rooms.php"
               class="sh-nav-link">
                Manage Rooms
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

        <h1>
            <?= $isEdit ? 'Edit Room' : 'Add New Room' ?>
        </h1>

        <p>
            <?= $isEdit
                ? 'Update room information and availability.'
                : 'Create a new accommodation for your villa.'
            ?>
        </p>

    </section>

    <?php if ($notice): ?>
        <div class="sh-notice" role="alert">
            <?= roomE($notice) ?>
        </div>
    <?php endif; ?>

    <section class="sh-room-form-card">

        <form
            method="POST"
            action="save-room.php"
            enctype="multipart/form-data">

            <input type="hidden"
                   name="csrf_token"
                   value="<?= roomE(
                       $_SESSION['room_csrf']
                   ) ?>">

            <input type="hidden"
                   name="room_id"
                   value="<?= $isEdit ? (int)$id : '' ?>">

            <div class="sh-room-form-grid">

                <div class="sh-room-field">
                    <label for="room_name">
                        Room Name *
                    </label>

                    <input
                        type="text"
                        id="room_name"
                        name="room_name"
                        maxlength="100"
                        value="<?= roomE(
                            $room['room_name']
                        ) ?>"
                        required>
                </div>

                <div class="sh-room-field">
                    <label for="max_guests">
                        Maximum Guests *
                    </label>

                    <input
                        type="number"
                        id="max_guests"
                        name="max_guests"
                        min="1"
                        max="100"
                        value="<?= (int)$room['max_guests'] ?>"
                        required>
                </div>

                <div class="sh-room-field">
                    <label for="price_per_night">
                        Price Per Night (LKR) *
                    </label>

                    <input
                        type="number"
                        id="price_per_night"
                        name="price_per_night"
                        min="0.01"
                        step="0.01"
                        value="<?= roomE(
                            $room['price_per_night']
                        ) ?>"
                        required>
                </div>

                <div class="sh-room-field">
                    <label for="status">
                        Room Availability *
                    </label>

                    <select
                        id="status"
                        name="status"
                        required>

                        <option value="available"
                            <?= $room['status'] === 'available'
                                ? 'selected' : '' ?>>
                            Available
                        </option>

                        <option value="unavailable"
                            <?= $room['status'] === 'unavailable'
                                ? 'selected' : '' ?>>
                            Unavailable
                        </option>

                    </select>
                </div>

                <div class="sh-room-field sh-field-full">
                    <label for="description">
                        Room Description *
                    </label>

                    <textarea
                        id="description"
                        name="description"
                        rows="5"
                        required><?= roomE(
                            $room['description']
                        ) ?></textarea>
                </div>

                <div class="sh-room-field sh-field-full">
                    <label for="facilities">
                        Room Facilities *
                    </label>

                    <input
                        type="text"
                        id="facilities"
                        name="facilities"
                        maxlength="255"
                        placeholder="Wi-Fi, Air Conditioning, TV"
                        value="<?= roomE(
                            $room['facilities']
                        ) ?>"
                        required>

                    <small>
                        Separate facilities using commas.
                    </small>
                </div>

                <div class="sh-room-field sh-field-full">
                    <label for="room_image">
                        Room Image
                        <?= $isEdit ? '(Optional)' : '*' ?>
                    </label>

                    <?php if ($isEdit): ?>

                        <img
                            src="<?= roomE(
                                roomImageUrl($room['image'])
                            ) ?>"
                            class="sh-room-preview"
                            alt="Current room image">

                        <small>
                            Leave empty to keep the current image.
                        </small>

                    <?php endif; ?>

                    <input
                        type="file"
                        id="room_image"
                        name="room_image"
                        accept=".jpg,.jpeg,.png,.webp"
                        <?= $isEdit ? '' : 'required' ?>>

                    <small>
                        JPG, PNG or WebP.
                        Maximum file size: 5 MB.
                    </small>
                </div>

            </div>

            <div class="sh-room-form-actions">

                <button
                    type="submit"
                    class="sh-room-primary">
                    <?= $isEdit
                        ? 'Save Changes'
                        : 'Add Room' ?>
                </button>

                <a href="rooms.php"
                   class="sh-room-secondary">
                    Cancel
                </a>

            </div>

        </form>

    </section>

</main>

<footer class="sh-admin-footer">
    &copy; <?= date('Y') ?> Shanu Haven.
</footer>

</body>
</html>
