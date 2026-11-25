<?php

require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/config/customer-session.php';
require_once __DIR__ . '/includes/home-functions.php';

$facilities = [
    ['🌊', 'Beach Access', 'Enjoy the beautiful coastline of Sri Lanka.'],
    ['🏊', 'Swimming Pool', 'Relax and enjoy our beautiful swimming pool.'],
    ['📶', 'Free Wi-Fi', 'Stay connected with high-speed internet.'],
    ['❄️', 'Air Conditioning', 'Enjoy a cool and comfortable room.']
];

?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <meta name="description"
          content="Shanu Haven - A luxury coastal villa in Sri Lanka. Explore beautiful rooms and enjoy a peaceful stay.">

    <title>Shanu Haven | Luxury Coastal Villa</title>

    <link rel="stylesheet" href="assets/css/style.css?v=22">
    <link rel="stylesheet" href="assets/css/customer-nav.css">

    <script src="assets/js/main.js" defer></script>

    <!-- Room Availability Search CSS -->

    <style>

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

    <!-- Map CSS -->

    <link rel="stylesheet"
          href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css">

    <link rel="stylesheet" href="assets/CSS/location.css">

</head>

<body>

<!-- NAVIGATION BAR -->

<header class="site-header">

    <nav class="navbar container">

        <a href="index.php" class="logo">
            <img src="assets/images/shanu-logo.png"
                 alt="Shanu Haven Coastal Villa"
                 class="logo-image">
        </a>

        <button class="menu-toggle"
                type="button"
                aria-label="Open navigation"
                aria-expanded="false"
                aria-controls="nav-links">
            &#9776;
        </button>

        <div class="nav-links" id="nav-links">

            <a href="#home">Home</a>
            <a href="#about">About Us</a>
            <a href="#rooms">Rooms</a>
            <a href="#facilities">Facilities</a>
            <a href="#contact">Contact</a>
            <a href="#rooms" class="nav-book">Book Now</a>

            <div class="customer-nav-actions">

                <?php if (isCustomerLoggedIn()): ?>

                    <a href="my-bookings.php" class="customer-login-btn">
                        My Bookings
                    </a>

                    <span class="customer-nav-name">

                        <span class="customer-user-icon">&#9679;</span>

                        <?= e(getCustomerName()) ?>

                    </span>

                    <form method="POST"
                          action="logout.php"
                          class="customer-logout-form">

                        <input type="hidden"
                               name="csrf_token"
                               value="<?= e(getCustomerLogoutToken()) ?>">

                        <button type="submit" class="customer-logout-btn">
                            Logout
                        </button>

                    </form>

                <?php else: ?>

                    <a href="login.php" class="customer-login-btn">
                        Login
                    </a>

                    <a href="register.php" class="customer-register-btn">
                        Register
                    </a>

                <?php endif; ?>

            </div>

        </div>

    </nav>

</header>

<main>

    <!-- HERO SECTION -->

    <section class="hero" id="home">

        <div class="hero-content">

            <p class="eyebrow">WELCOME TO SHANU HAVEN</p>

            <h1>Your Perfect Coastal Escape</h1>

            <p>
                Discover comfort, relaxation and unforgettable
                moments by the beautiful coast of Sri Lanka.
            </p>

            <div class="hero-buttons">

                <a href="#rooms" class="btn btn-primary">
                    Explore Rooms
                </a>

                <a href="#about" class="btn btn-outline">
                    Discover More
                </a>

            </div>

        </div>

    </section>

    <!-- ABOUT SECTION -->

    <section class="section about" id="about">

        <div class="container about-grid">

            <div class="about-content">

                <p class="section-label">ABOUT OUR VILLA</p>

                <h2>Experience Coastal Luxury</h2>

                <p>
                    Welcome to Shanu Haven, your peaceful getaway
                    on the beautiful coast of Sri Lanka.
                </p>

                <p>
                    Enjoy comfortable rooms, modern facilities
                    and a relaxing tropical atmosphere.
                </p>

                <p>
                    Whether you are planning a romantic holiday,
                    a family vacation or a relaxing weekend,
                    Shanu Haven offers a memorable coastal experience.
                </p>

                <a href="#rooms" class="btn btn-primary">
                    View Our Rooms
                </a>

            </div>

            <div class="about-image-wrapper">

                <img src="assets/images/villa-hero.png"
                     alt="Shanu Haven coastal villa"
                     class="about-image"
                     loading="lazy">

            </div>

        </div>

    </section>

    <!-- ROOMS SECTION -->

    <?php require __DIR__ . '/includes/home-rooms.php'; ?>

    <!-- FACILITIES SECTION -->

    <section class="section facilities" id="facilities">

        <div class="container">

            <div class="section-heading">

                <p class="section-label">OUR SERVICES</p>
                <h2>Villa Facilities</h2>
                <p>Everything you need for a relaxing stay.</p>

            </div>

            <div class="facility-grid">

                <?php foreach ($facilities as [$icon, $title, $description]): ?>

                    <div class="facility-card">

                        <span aria-hidden="true"><?= e($icon) ?></span>
                        <h3><?= e($title) ?></h3>
                        <p><?= e($description) ?></p>

                    </div>

                <?php endforeach; ?>

            </div>

        </div>

    </section>

    <!-- BOOKING CALL TO ACTION -->

    <section class="booking-cta" id="booking-info">

        <div class="container">

            <h2>Ready for Your Dream Vacation?</h2>

            <p>Your relaxing coastal getaway starts here.</p>

            <a href="#contact" class="btn btn-cream">
                Contact Us
            </a>

            <a href="#location">Location</a>

            <p class="booking-note">
                Online reservations are now available.
            </p>

        </div>

    </section>

</main>

<!-- FOOTER -->

<footer class="footer" id="contact">

    <div class="container footer-grid">

        <div class="footer-about">

            <h3>SHANU HAVEN</h3>
            <p>Your peaceful coastal escape in Sri Lanka.</p>
            <p>Experience comfort, relaxation and unforgettable memories.</p>

        </div>

        <div class="footer-links">

            <h3>Quick Links</h3>

            <a href="#home">Home</a>
            <a href="#about">About Us</a>
            <a href="#rooms">Our Rooms</a>
            <a href="#facilities">Facilities</a>
            <a href="#contact">Contact</a>

        </div>

        <div class="footer-contact">

            <h3>Contact Us</h3>
            <p>Location: Sri Lanka</p>
            <p>Contact details coming soon</p>

        </div>

    </div>

    <!-- LOCATION AND GPS SECTION -->

    <section class="sh-location-section" id="location">

        <div class="sh-location-heading">

            <span class="sh-location-subtitle">
                DISCOVER OUR LOCATION
            </span>

            <h2>Find Your Coastal Escape</h2>

            <p>
                Discover Shanu Haven in Galle, Sri Lanka
                and plan your journey with ease.
            </p>

        </div>

        <div class="sh-location-container">

            <div class="sh-map-wrapper">
                <div id="villaMap"></div>
            </div>

            <div class="sh-location-info">

                <span class="sh-location-label">OUR LOCATION</span>

                <h3>Shanu Haven</h3>

                <p class="sh-location-address">
                    Galle, Southern Province, Sri Lanka
                </p>

                <p class="sh-location-description">
                    Enjoy a peaceful coastal escape surrounded
                    by the natural beauty of southern Sri Lanka.
                </p>

                <div class="sh-location-search">

                    <label for="locationSearch">
                        Choose Your Starting Location
                    </label>

                    <input type="text"
                           id="locationSearch"
                           placeholder="Enter city or location..."
                           autocomplete="off">

                    <button type="button"
                            id="searchLocationBtn"
                            class="sh-gps-btn">
                        Search Location
                    </button>

                    <select id="locationResults"
                            aria-label="Search results"
                            hidden>
                    </select>

                </div>

                <button type="button"
                        id="findLocationBtn"
                        class="sh-directions-btn">
                    &#8982; Use My Current Location
                </button>

                <button type="button"
                        id="selectMapBtn"
                        class="sh-directions-btn">
                    Select on Map
                </button>

                <div class="sh-location-distance">

                    <span>Starting Location</span>

                    <strong id="selectedLocation">
                        Not selected
                    </strong>

                    <span class="sh-distance-label">
                        Distance to Villa
                    </span>

                    <strong id="villaDistance">
                        Select your location
                    </strong>

                </div>

                <button type="button"
                        id="directionsBtn"
                        class="sh-gps-btn">
                    &#10148; Get Directions
                </button>

                <p id="gpsStatus" aria-live="polite">
                    Search a city, use GPS or select a location on the map.
                </p>

            </div>

        </div>

    </section>

    <div class="footer-bottom">

        <p>
            &copy; <?= date('Y') ?>
            Shanu Haven. All Rights Reserved.
        </p>

    </div>

</footer>

<!-- MAP JAVASCRIPT -->

<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script src="assets/js/location.js"></script>

</body>
</html>