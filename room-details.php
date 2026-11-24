<?php
require_once "config/database.php";
$room_id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
if (!$room_id || $room_id < 1) { http_response_code(404); exit('Room not found.'); }
$stmt = $pdo->prepare('SELECT * FROM rooms WHERE room_id = :id');
$stmt->execute(['id' => $room_id]);
$room = $stmt->fetch();
if (!$room) { http_response_code(404); exit('Room not found.'); }
function e($value) { return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8'); }
$facilities = array_filter(array_map('trim', explode(',', (string)($room['facilities'] ?? ''))));


$filename = basename((string)$room['image']);

$oldImages = [
    'Deluxe Room.png',
    'Ocean View Room.png',
    'Family Suite.png',
    'villa-hero.png'
];

if (in_array($filename, $oldImages, true)) {
    $imagePath = 'assets/images/' .
        rawurlencode($filename);

} elseif (preg_match(
    '/^room_[a-f0-9]{32}\.(jpg|png|webp)$/',
    $filename
)) {
    $imagePath = 'assets/images/rooms/' .
        rawurlencode($filename);

} else {
    $imagePath = 'assets/images/villa-hero.png';
}


$isAvailable = $room['status'] === 'available';
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?= e($room['room_name']) ?> | Shanu Haven</title>
<link rel="stylesheet" href="assets/css/style.css?v=21">


<style>
/* Remove golden light effect only */
.details-section .details-info.room-info-glass::after,
.details-section .room-price-box::before,
.details-section .room-price-box::after {
    content: none !important;
    display: none !important;
    animation: none !important;
}

/* Stop animations on the glass card and price box */
.details-section .details-info.room-info-glass,
.details-section .room-price-box {
    animation: none !important;
}

/* Keep the transparent glass card */
.details-section .details-info.room-info-glass {
    background: rgba(255,255,255,0.22) !important;
    backdrop-filter: blur(20px);
    -webkit-backdrop-filter: blur(20px);
}

/* Keep the original price box design */
.details-section .room-price-box {
    background: rgba(215,185,142,0.15) !important;
}
</style>



</head>
<body>
<header class="site-header">
  <nav class="navbar container">
    <a href="index.php" class="logo"><img src="assets/images/shanu-logo.png" alt="Shanu Haven Coastal Villa" class="logo-image"></a>
    <a href="index.php#rooms" class="btn btn-cream">Back to Rooms</a>
  </nav>
</header>
<main>
<section class="details-section">
 <div class="container">
  <div class="details-card">
   <div class="details-photo-panel">
    <img src="<?= e($imagePath) ?>" alt="<?= e($room['room_name']) ?>" class="details-image">
    <div class="details-photo-caption">SHANU HAVEN · COASTAL VILLA</div>
   </div>
   <div class="details-info room-info-glass">
    <p class="section-label">SHANU HAVEN ACCOMMODATION</p>
    <h1><?= e($room['room_name']) ?></h1>
    <p class="details-description"><?= e($room['description']) ?></p>
    <div class="details-guest-row"><span>Maximum Guests</span><strong><?= (int)$room['max_guests'] ?></strong></div>
    <h2 class="details-subheading">Room Facilities</h2>
    <ul class="facility-list">
     <?php foreach ($facilities as $facility): ?>
      <li class="facility-badge"><?= e($facility) ?></li>
     <?php endforeach; ?>
    </ul>
    <div class="room-price-box">
      <span class="details-price-label">PRICE PER NIGHT</span>
      <div class="details-price">LKR <?= number_format((float)$room['price_per_night']) ?> <span>/ night</span></div>
    </div>
    <div class="details-status"><span>Room Status</span><strong class="<?= $isAvailable ? 'status-available' : 'status-unavailable' ?>"><?= e(ucfirst($room['status'])) ?></strong></div>
    <div class="details-actions">
      <?php if ($isAvailable): ?>
      <a href="booking.php?room_id=<?= (int)$room['room_id'] ?>" class="btn room-book-btn">Book Now</a>
      <?php else: ?>
      <span class="btn room-book-disabled" aria-disabled="true">Currently Unavailable</span>
      <?php endif; ?>
      <a href="index.php#rooms" class="btn details-back">View Other Rooms</a>
    </div>
   </div>
  </div>
 </div>
</section>
</main>
<footer class="footer"><div class="footer-bottom"><p>&copy; <?= date('Y') ?> Shanu Haven. All Rights Reserved.</p></div></footer>
</body>
</html>
