<section class="sh-bookings-panel">

    <div class="sh-panel-heading">
        <div>
            <p class="sh-eyebrow">BOOKING MANAGEMENT</p>
            <h2>Recent Bookings</h2>
        </div>

        <a href="bookings.php"
           class="sh-record-count sh-all-bookings-link">
            View All Bookings &rarr;
        </a>
    </div>

    <div class="sh-table-wrapper">
        <table class="sh-booking-table">

            <thead>
                <tr>
                    <th>Booking ID</th>
                    <th>Guest Details</th>
                    <th>Room</th>
                    <th>Check-in</th>
                    <th>Check-out</th>
                    <th>Guests</th>
                    <th>Total</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>

            <tbody>

            <?php if (empty($bookings)): ?>

                <tr>
                    <td colspan="9" class="sh-empty">
                        No bookings available.
                    </td>
                </tr>

            <?php else: ?>

                <?php foreach ($bookings as $booking): ?>

                    <tr>
                        <td class="sh-ref">
                            <?= e(bookingRef($booking['booking_id'])) ?>
                        </td>

                        <td>
                            <strong><?= e($booking['guest_name']) ?></strong>
                            <small><?= e($booking['guest_email']) ?></small>
                            <small><?= e($booking['guest_phone']) ?></small>
                        </td>

                        <td><?= e($booking['room_name']) ?></td>
                        <td><?= e($booking['check_in']) ?></td>
                        <td><?= e($booking['check_out']) ?></td>
                        <td><?= (int)$booking['guests'] ?></td>

                        <td class="sh-amount">
                            LKR <?= number_format(
                                (float)$booking['total_price'], 2
                            ) ?>
                        </td>

                        <td>
                            <span class="sh-status sh-<?= e($booking['booking_status']) ?>">
                                <?= e(ucfirst($booking['booking_status'])) ?>
                            </span>
                        </td>

                        <td>
                            <?php if ($booking['booking_status'] === 'pending'): ?>

                                <div class="sh-actions">

                                    <?php foreach (['confirm', 'cancel'] as $action): ?>

                                        <form method="POST"
                                              action="update-booking.php"
                                              <?= $action === 'cancel'
                                                  ? 'onsubmit="return confirm(\'Cancel this booking request?\');"'
                                                  : '' ?>>

                                            <input type="hidden"
                                                   name="csrf_token"
                                                   value="<?= e($_SESSION['booking_csrf_admin']) ?>">

                                            <input type="hidden"
                                                   name="booking_id"
                                                   value="<?= (int)$booking['booking_id'] ?>">

                                            <input type="hidden"
                                                   name="action"
                                                   value="<?= e($action) ?>">

                                            <button type="submit"
                                                    class="sh-<?= e($action) ?>-btn">
                                                <?= ucfirst($action) ?>
                                            </button>

                                        </form>

                                    <?php endforeach; ?>

                                </div>

                            <?php else: ?>

                                <span class="sh-no-action">
                                    Completed
                                </span>

                            <?php endif; ?>
                        </td>
                    </tr>

                <?php endforeach; ?>

            <?php endif; ?>

            </tbody>
        </table>
    </div>

</section>