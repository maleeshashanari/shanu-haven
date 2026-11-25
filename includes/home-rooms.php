<section class="section rooms" id="rooms">

    <div class="container">

        <div class="section-heading">
            <p class="section-label">OUR ACCOMMODATION</p>
            <h2>Explore Our Beautiful Rooms</h2>
            <p>Find the perfect room for your stay.</p>
        </div>

        <!-- Room Availability Search -->

        <form class="availability-search"
              method="GET"
              action="index.php#rooms">

            <div class="search-field">
                <label for="search_check_in">Check-in Date</label>

                <input type="date"
                       id="search_check_in"
                       name="check_in"
                       min="<?= date('Y-m-d') ?>"
                       value="<?= e($checkIn) ?>"
                       required>
            </div>

            <div class="search-field">
                <label for="search_check_out">Check-out Date</label>

                <input type="date"
                       id="search_check_out"
                       name="check_out"
                       min="<?= date('Y-m-d', strtotime('+1 day')) ?>"
                       value="<?= e($checkOut) ?>"
                       required>
            </div>

            <div class="search-field">
                <label for="search_guests">Number of Guests</label>

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

            <a href="index.php#rooms" class="reset-search">
                Reset
            </a>

        </form>

        <?php if ($searchError): ?>

            <p class="search-error"><?= e($searchError) ?></p>

        <?php elseif ($isSearch): ?>

            <p class="search-result">
                <?= count($rooms) ?> available room(s) found.
            </p>

        <?php endif; ?>

        <!-- Room Cards -->

        <div class="room-grid">

            <?php foreach ($rooms as $room): ?>

                <?php $available = $room['status'] === 'available'; ?>

                <article class="room-card">

                    <img src="<?= e(homeRoomImage($room['image'])) ?>"
                         alt="<?= e($room['room_name']) ?>"
                         loading="lazy">

                    <div class="room-info">

                        <h3><?= e($room['room_name']) ?></h3>

                        <p><?= e($room['description']) ?></p>

                        <p>
                            <?= (int)$room['max_guests'] ?> Guests |
                            <?= e($room['facilities']) ?>
                        </p>

                        <p class="price">
                            LKR <?= number_format((float)$room['price_per_night']) ?>
                            / night
                        </p>

                        <p class="room-availability">

                            <span class="<?= $available
                                ? 'available-label'
                                : 'unavailable-label' ?>">

                                <?= $available
                                    ? 'Available'
                                    : 'Currently Unavailable' ?>

                            </span>

                        </p>

                        <a href="room-details.php?id=<?= (int)$room['room_id'] ?>"
                           class="btn btn-primary">
                            View Details
                        </a>

                    </div>

                </article>

            <?php endforeach; ?>

            <?php if (!$rooms): ?>
                <p>No rooms are currently listed.</p>
            <?php endif; ?>

        </div>

    </div>

</section>