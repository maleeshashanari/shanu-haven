
<?php
require_once "config/database.php";

// Read room ID from URL
$room_id = filter_input(
    INPUT_GET,
    'id',
    FILTER_VALIDATE_INT
);

if (!$room_id || $room_id < 1) {
    http_response_code(404);
    exit("Room not found.");
}

// Get room details from database
$stmt = $pdo->prepare(
    "SELECT * FROM rooms WHERE room_id = :id"
);

$stmt->execute(['id' => $room_id]);
$room = $stmt->fetch();

if (!$room) {
    http_response_code(404);
    exit("Room not found.");
}

// Escape text safely
function e($value) {
    return htmlspecialchars(
        (string)$value,
        ENT_QUOTES,
        'UTF-8'
    );
}

// Prepare facilities
$facilities = array_map(
    'trim',
    explode(',', $room['facilities'])
);

// Use only known image filenames
$allowedImages = [
    'Deluxe Room.png',
    'Ocean View Room.png',
    'Family Suite.png'
];

$image = in_array(
    $room['image'],
    $allowedImages,
    true
) ? $room['image'] : 'villa-hero.png';

$imagePath = 'assets/images/' .
    rawurlencode($image);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, initial-scale=1.0">

    <title>
        <?= e($room['room_name']) ?> | Shanu Haven
    </title>

    <link rel="stylesheet"
          href="assets/css/style.css">

    <style>
        .details-section {
            padding: 65px 0;
            background: #F7F4ED;
        }

        .details-card {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 45px;
            align-items: start;
        }

        .details-image {
            width: 100%;
            height: 430px;
            object-fit: cover;
            border-radius: 18px;
            border: 2px solid #D7B98E;
        }

        .details-info h1 {
            color: #12343B;
            font-size: 38px;
            margin-bottom: 18px;
        }

        .details-info p {
            margin-bottom: 18px;
        }

        .details-price {
            font-size: 28px;
            font-weight: bold;
            color: #2D6468;
            margin: 22px 0;
        }

        .facility-list {
            margin: 18px 0 28px;
            padding-left: 22px;
        }

        .facility-list li {
            margin-bottom: 10px;
        }

        .details-actions {
            display: flex;
            flex-wrap: wrap;
            gap: 12px;
        }

        .details-back {
            color: #12343B;
            font-weight: 600;
        }

        @media (max-width: 768px) {
            .details-card {
                grid-template-columns: 1fr;
            }

            .details-image {
                height: 300px;
            }

            .details-info h1 {
                font-size: 30px;
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
                alt="Shanu Haven Coastal Villa"
                class="logo-image">
        </a>

        <a href="index.php#rooms"
           class="btn btn-cream">
            Back to Rooms
        </a>

    </nav>
</header>

<main>
<section class="details-section">

    <div class="container">

        <div class="details-card">

            <!-- Room Image -->
            <div>
                <img
                    src="<?= e($imagePath) ?>"
                    alt="<?= e($room['room_name']) ?>"
                    class="details-image">
            </div>

            <!-- Room Information -->
            <div class="details-info">

                <p class="section-label">
                    SHANU HAVEN ACCOMMODATION
                </p>

                <h1>
                    <?= e($room['room_name']) ?>
                </h1>

                <p>
                    <?= e($room['description']) ?>
                </p>

                <p>
                    <strong>Maximum Guests:</strong>
                    <?= (int)$room['max_guests'] ?>
                </p>

                <h3>Room Facilities</h3>

                <ul class="facility-list">
                    <?php foreach ($facilities as $facility): ?>
                        <li><?= e($facility) ?></li>
                    <?php endforeach; ?>
                </ul>

                <div class="details-price">
                    LKR
                    <?= number_format(
                        (float)$room['price_per_night']
                    ) ?>
                    / night
                </div>

                <p>
                    <strong>Status:</strong>
                    <?= e(ucfirst($room['status'])) ?>
                </p>

                <div class="details-actions">

                   
<a
    href="booking.php?room_id=<?= (int)$room['room_id'] ?>"
    class="btn btn-primary">
    Book Now
</a>


                    <a
                        href="index.php#rooms"
                        class="btn details-back">
                        View Other Rooms
                    </a>

                </div>

            </div>

        </div>

    </div>

</section>
</main>

<footer class="footer">
    <div class="footer-bottom">
        <p>
            &copy; <?= date("Y") ?>
            Shanu Haven. All Rights Reserved.
        </p>
    </div>
</footer>

</body>
</html>
