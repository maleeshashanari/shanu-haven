

<?php
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/config/customer-session.php';

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
    return htmlspecialchars(
        (string)$value,
        ENT_QUOTES,
        'UTF-8'
    );
}

// Get a safe room image path
function homeRoomImage($filename) {

    $filename = (string)$filename;

    $oldImages = [
        'Deluxe Room.png',
        'Ocean View Room.png',
        'Family Suite.png',
        'villa-hero.png'
    ];

    if (in_array($filename, $oldImages, true)) {
        return 'assets/images/' .
            rawurlencode($filename);
    }

    if (preg_match(
        '/^room_[a-f0-9]{32}\.(jpg|png|webp)$/',
        $filename
    )) {
        return 'assets/images/rooms/' .
            rawurlencode($filename);
    }

    return 'assets/images/villa-hero.png';
}
?>


<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">

    <meta name="viewport"
          content="width=device-width, initial-scale=1.0">

    <meta name="description"
          content="Shanu Haven - A luxury coastal villa in Sri Lanka. Explore beautiful rooms and enjoy a peaceful stay.">

    <title>Shanu Haven | Luxury Coastal Villa</title>

    <!-- Main CSS -->
<link rel="stylesheet" href="assets/css/style.css?v=22">
    <!-- JavaScript -->
    <script src="assets/js/main.js" defer></script>

    <link rel="stylesheet"
      href="assets/css/customer-nav.css">

      <style>

/* Room Availability Search Design */

.availability-search {
    display: grid;
    grid-template-columns: 1fr 1fr 1fr auto auto;
    align-items: end;
    gap: 20px;
    padding: 25px;
    margin-bottom: 35px;
    background: #12343B;
    border: 1px solid #D7B98E;
    border-radius: 16px;
    box-shadow: 0 8px 25px rgba(0,0,0,0.15);
}

.availability-search .search-field {
    min-width: 0;
}

.availability-search label {
    display: block;
    color: #D7B98E;
    font-size: 14px;
    font-weight: 600;
    margin-bottom: 10px;
}

.availability-search input {
    width: 100%;
    height: 45px;
    padding: 10px 12px;
    box-sizing: border-box;
    background: rgba(255,255,255,0.10);
    color: #F7F4ED;
    border: 1px solid rgba(215,185,142,0.5);
    border-radius: 8px;
    color-scheme: dark;
    font-size: 14px;
}

.availability-search input:focus {
    outline: none;
    border-color: #D7B98E;
    box-shadow: 0 0 0 3px rgba(215,185,142,0.15);
}

.availability-search .search-btn {
    height: 45px;
    padding: 0 25px;
    background: #D7B98E;
    color: #12343B;
    border: none;
    border-radius: 8px;
    font-size: 14px;
    font-weight: 700;
    cursor: pointer;
}

.availability-search .search-btn:hover {
    background: #F7F4ED;
}

.availability-search .reset-search {
    color: #D7B98E;
    padding: 12px;
    text-decoration: none;
    font-weight: 600;
}

.search-result {
    color: #12343B;
    margin-bottom: 20px;
}

.search-error {
    color: #922B21;
    margin-bottom: 20px;
}

@media (max-width: 900px) {
    .availability-search {
        grid-template-columns: 1fr 1fr;
    }
}

@media (max-width: 550px) {
    .availability-search {
        grid-template-columns: 1fr;
        padding: 18px;
    }
}

</style>

</head>

<body>

    <!-- ==================================
         NAVIGATION BAR
    ================================== -->

    <header class="site-header">

        <nav class="navbar container">

            <!-- Logo -->
            
<!-- Shanu Haven Logo -->
<a href="index.php" class="logo">
    <img
        src="assets/images/shanu-logo.png"
        alt="Shanu Haven Coastal Villa"
        class="logo-image"
    >
</a>


            <!-- Mobile Menu Button -->
            <button
                class="menu-toggle"
                type="button"
                aria-label="Open navigation"
                aria-expanded="false"
                aria-controls="nav-links">
                &#9776;
            </button>

            <!-- Navigation Links -->
            <div class="nav-links" id="nav-links">

                <a href="#home">Home</a>

                <a href="#about">About Us</a>

                <a href="#rooms">Rooms</a>

                <a href="#facilities">Facilities</a>

                <a href="#contact">Contact</a>

                <a href="#rooms" class="nav-book">
                    Book Now
                </a>

                <div class="customer-nav-actions">

    <?php if (isCustomerLoggedIn()): ?>

        <a href="my-bookings.php"
   class="customer-login-btn">

    My Bookings

</a>

        <span class="customer-nav-name">

            <span class="customer-user-icon">
                &#9679;
            </span>

            <?= htmlspecialchars(
                getCustomerName(),
                ENT_QUOTES,
                'UTF-8'
            ) ?>

        </span>

        <form method="POST"
              action="logout.php"
              class="customer-logout-form">

            <input type="hidden"
                   name="csrf_token"
                   value="<?= htmlspecialchars(
                       getCustomerLogoutToken(),
                       ENT_QUOTES,
                       'UTF-8'
                   ) ?>">

            <button type="submit"
                    class="customer-logout-btn">

                Logout

            </button>

        </form>

    <?php else: ?>

        <a href="login.php"
           class="customer-login-btn">

            Login

        </a>

        <a href="register.php"
           class="customer-register-btn">

            Register

        </a>

    <?php endif; ?>

</div>

            </div>

        </nav>

    </header>


    <main>

        <!-- ==================================
             HERO SECTION
        ================================== -->

        <section class="hero" id="home">

            <div class="hero-content">

                <p class="eyebrow">
                    WELCOME TO SHANU HAVEN
                </p>

                <h1>
                    Your Perfect Coastal Escape
                </h1>

                <p>
                    Discover comfort, relaxation and
                    unforgettable moments by the beautiful
                    coast of Sri Lanka.
                </p>

                <div class="hero-buttons">

                    <a href="#rooms"
                       class="btn btn-primary">
                        Explore Rooms
                    </a>

                    <a href="#about"
                       class="btn btn-outline">
                        Discover More
                    </a>

                </div>

            </div>

        </section>


        <!-- ==================================
             ABOUT OUR VILLA
        ================================== -->

        <section class="section about" id="about">

            <div class="container about-grid">

                <!-- About Text -->
                <div class="about-content">

                    <p class="section-label">
                        ABOUT OUR VILLA
                    </p>

                    <h2>
                        Experience Coastal Luxury
                    </h2>

                    <p>
                        Welcome to Shanu Haven, your peaceful
                        getaway on the beautiful coast of
                        Sri Lanka.
                    </p>

                    <p>
                        Enjoy comfortable rooms, modern
                        facilities and a relaxing tropical
                        atmosphere.
                    </p>

                    <p>
                        Whether you are planning a romantic
                        holiday, a family vacation or a
                        relaxing weekend, Shanu Haven offers
                        a memorable coastal experience.
                    </p>

                    <a href="#rooms"
                       class="btn btn-primary">
                        View Our Rooms
                    </a>

                </div>

                <!-- About Image -->
                <div class="about-image-wrapper">

                    <img
                        src="assets/images/villa-hero.png"
                        alt="Shanu Haven coastal villa"
                        class="about-image"
                        loading="lazy"
                    >

                </div>

            </div>

        </section>


        <!-- ==================================
             ROOMS SECTION
        ================================== -->

    
<!-- ==================================
     ROOMS SECTION
================================== -->

<section class="section rooms" id="rooms">

    <div class="container">

        <div class="section-heading">

            <p class="section-label">
                OUR ACCOMMODATION
            </p>

            <h2>
                Explore Our Beautiful Rooms
            </h2>

            <p>
                Find the perfect room for your stay.
            </p>

        </div>

        <form class="availability-search"
      method="GET"
      action="index.php#rooms">

    <div class="search-field">

        <label for="search_check_in">
            Check-in Date
        </label>

        <input type="date"
               id="search_check_in"
               name="check_in"
               min="<?= date('Y-m-d') ?>"
               value="<?= e($checkIn) ?>"
               required>

    </div>

    <div class="search-field">

        <label for="search_check_out">
            Check-out Date
        </label>

        <input type="date"
               id="search_check_out"
               name="check_out"
               min="<?= date('Y-m-d', strtotime('+1 day')) ?>"
               value="<?= e($checkOut) ?>"
               required>

    </div>

    <div class="search-field">

        <label for="search_guests">
            Number of Guests
        </label>

        <input type="number"
               id="search_guests"
               name="guests"
               min="1"
               value="<?= (int)$guests ?>"
               required>

    </div>

    <button type="submit"
            name="search"
            value="1"
            class="search-btn">

        Search Rooms

    </button>

    <a href="index.php#rooms"
       class="reset-search">

        Reset

    </a>

</form>

<?php if ($searchError): ?>

    <p class="search-error">
        <?= e($searchError) ?>
    </p>

<?php elseif ($isSearch): ?>

    <p class="search-result">
        <?= count($rooms) ?> available room(s) found.
    </p>

<?php endif; ?>

        <div class="room-grid">

            <?php foreach ($rooms as $room): ?>

                <article class="room-card">

                    <img
                        src="<?= e(homeRoomImage($room['image'])) ?>"
                        alt="<?= e($room['room_name']) ?>"
                        loading="lazy"
                    >

                    <div class="room-info">

                        <h3>
                            <?= e($room['room_name']) ?>
                        </h3>

                        <p>
                            <?= e($room['description']) ?>
                        </p>

                        <p>
                            <?= (int)$room['max_guests'] ?>
                            Guests
                            |
                            <?= e($room['facilities']) ?>
                        </p>

                        <p class="price">
                            LKR
                            <?= number_format(
                                (float)$room['price_per_night']
                            ) ?>
                            / night
                        </p>

                        <p class="room-availability">
                            <?php if ($room['status'] === 'available'): ?>

                                <span class="available-label">
                                    Available
                                </span>

                            <?php else: ?>

                                <span class="unavailable-label">
                                    Currently Unavailable
                                </span>

                            <?php endif; ?>
                        </p>

                        <a
                            href="room-details.php?id=<?= (int)$room['room_id'] ?>"
                            class="btn btn-primary"
                        >
                            View Details
                        </a>

                    </div>

                </article>

            <?php endforeach; ?>

            <?php if (!$rooms): ?>

                <p>
                    No rooms are currently listed.
                </p>

            <?php endif; ?>

        </div>

    </div>

</section>


                  


         


                     
                           
<a


        <!-- ==================================
             VILLA FACILITIES
        ================================== -->

        <section class="section facilities" id="facilities">

            <div class="container">

                <div class="section-heading">

                    <p class="section-label">
                        OUR SERVICES
                    </p>

                    <h2>
                        Villa Facilities
                    </h2>

                    <p>
                        Everything you need for
                        a relaxing stay.
                    </p>

                </div>


                <div class="facility-grid">

                    <!-- Beach Access -->
                    <div class="facility-card">

                        <span aria-hidden="true">🌊</span>

                        <h3>Beach Access</h3>

                        <p>
                            Enjoy the beautiful
                            coastline of Sri Lanka.
                        </p>

                    </div>


                    <!-- Swimming Pool -->
                    <div class="facility-card">

                        <span aria-hidden="true">🏊</span>

                        <h3>Swimming Pool</h3>

                        <p>
                            Relax and enjoy our
                            beautiful swimming pool.
                        </p>

                    </div>


                    <!-- Free Wi-Fi -->
                    <div class="facility-card">

                        <span aria-hidden="true">📶</span>

                        <h3>Free Wi-Fi</h3>

                        <p>
                            Stay connected with
                            high-speed internet.
                        </p>

                    </div>


                    <!-- Air Conditioning -->
                    <div class="facility-card">

                        <span aria-hidden="true">❄️</span>

                        <h3>Air Conditioning</h3>

                        <p>
                            Enjoy a cool and
                            comfortable room.
                        </p>

                    </div>

                </div>

            </div>

        </section>


        <!-- ==================================
             BOOKING CALL TO ACTION
        ================================== -->

        <section class="booking-cta" id="booking-info">

            <div class="container">

                <h2>
                    Ready for Your Dream Vacation?
                </h2>

                <p>
                    Your relaxing coastal getaway
                    starts here.
                </p>

                <a href="#contact"
                   class="btn btn-cream">
                    Contact Us
                </a>

                <p class="booking-note">
                   Online reservations are now available.
                </p>

            </div>

        </section>

    </main>


    <!-- ==================================
         FOOTER
    ================================== -->

    <footer class="footer" id="contact">

        <div class="container footer-grid">

            <!-- Villa Information -->
            <div class="footer-about">

                <h3>SHANU HAVEN</h3>

                <p>
                    Your peaceful coastal escape
                    in Sri Lanka.
                </p>

                <p>
                    Experience comfort, relaxation
                    and unforgettable memories.
                </p>

            </div>


            <!-- Quick Links -->
            <div class="footer-links">

                <h3>Quick Links</h3>

                <a href="#home">Home</a>

                <a href="#about">About Us</a>

                <a href="#rooms">Our Rooms</a>

                <a href="#facilities">Facilities</a>

                <a href="#contact">Contact</a>

            </div>


            <!-- Contact Information -->
            <div class="footer-contact">

                <h3>Contact Us</h3>

                <p>
                    Location: Sri Lanka
                </p>

                <p>
                    Contact details coming soon
                </p>

            </div>

        </div>


        <!-- Copyright -->
        <div class="footer-bottom">

            <p>
                &copy; <?php echo date("Y"); ?>
                Shanu Haven.
                All Rights Reserved.
            </p>

        </div>

    </footer>

</body>
</html>
