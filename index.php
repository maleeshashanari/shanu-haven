
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
    <link rel="stylesheet" href="assets/css/style.css?v=3">

    <!-- JavaScript -->
    <script src="assets/js/main.js" defer></script>
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


                <div class="room-grid">

                    <!-- =====================
                         DELUXE ROOM
                    ===================== -->

                    <article class="room-card">

                        <img
                            src="assets/images/Deluxe Room.png"
                            alt="Deluxe Room"
                            loading="lazy"
                        >

                        <div class="room-info">

                            <h3>
                                Deluxe Room
                            </h3>

                            <p>
                                Comfortable room with
                                modern facilities.
                            </p>

                            <p>
                                2 Guests | Wi-Fi |
                                Air Conditioning
                            </p>

                            <p class="price">
                                LKR 15,000 / night
                            </p>

                           
<a href="room-details.php?id=1"
   class="btn btn-primary">
    View Details
</a>
>

                        </div>

                    </article>


                    <!-- =====================
                         OCEAN VIEW ROOM
                    ===================== -->

                    <article class="room-card">

                        <img
                            src="assets/images/Ocean View Room.png"
                            alt="Ocean View Room"
                            loading="lazy"
                        >

                        <div class="room-info">

                            <h3>
                                Ocean View Room
                            </h3>

                            <p>
                                Relax with a beautiful
                                ocean view.
                            </p>

                            <p>
                                2 Guests | Wi-Fi |
                                Ocean View
                            </p>

                            <p class="price">
                                LKR 22,000 / night
                            </p>

                           
<a href="room-details.php?id=2"
   class="btn btn-primary">
    View Details
</a>


                        </div>

                    </article>


                    <!-- =====================
                         FAMILY SUITE
                    ===================== -->

                    <article class="room-card">

                        <img
                            src="assets/images/Family Suite.png"
                            alt="Family Suite"
                            loading="lazy"
                        >

                        <div class="room-info">

                            <h3>
                                Family Suite
                            </h3>

                            <p>
                                Spacious accommodation
                                for families.
                            </p>

                            <p>
                                4 Guests | Wi-Fi |
                                Family Space
                            </p>

                            <p class="price">
                                LKR 30,000 / night
                            </p>

                           
<a href="room-details.php?id=3"
   class="btn btn-primary">
    View Details
</a>


                        </div>

                    </article>

                </div>

                <p class="demo-note">
                    Sample room information and prices
                    for demonstration purposes.
                </p>

            </div>

        </section>


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
                    Online reservations will
                    be available soon.
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
