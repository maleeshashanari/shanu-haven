
<div class="sh-table-wrapper">
    <table class="sh-booking-table">
        <thead>
            <tr>
                <th>Booking ID</th>
                <th>Guest</th>
                <th>Room</th>
                <th>Check-in</th>
                <th>Check-out</th>
                <th>Total</th>
                <th>Status</th>
                <th>Actions</th>
            </tr>
        </thead>

        <tbody>
        <?php if (!$bookings): ?>
            <tr>
                <td colspan="8" class="sh-empty">
                    No matching bookings found.
                </td>
            </tr>
        <?php else: ?>

            <?php foreach ($bookings as $booking): ?>
                <?php $details = bookingDetails($booking); ?>

                <tr>
                    <td class="sh-ref">
                        <?= e($details['reference']) ?>
                    </td>

                    <td>
                        <strong><?= e($booking['guest_name']) ?></strong>
                        <small><?= e($booking['guest_email']) ?></small>
                    </td>

                    <td><?= e($booking['room_name']) ?></td>
                    <td><?= e($booking['check_in']) ?></td>
                    <td><?= e($booking['check_out']) ?></td>

                    <td class="sh-amount">
                        <?= e($details['amount']) ?>
                    </td>

                    <td>
                        <span class="sh-status sh-<?= e($booking['booking_status']) ?>">
                            <?= e($details['status']) ?>
                        </span>
                    </td>

                    <td>
                        <div class="sh-actions">

                            <button type="button"
                                    class="sh-view-btn"
                                    data-booking="<?= e(json_encode(
                                        $details,
                                        JSON_HEX_TAG | JSON_HEX_AMP |
                                        JSON_HEX_APOS | JSON_HEX_QUOT
                                    )) ?>">
                                View
                            </button>

                            <?php if ($booking['booking_status'] === 'pending'): ?>

                                <?php foreach (['confirm', 'cancel'] as $action): ?>

                                    <form method="POST"
                                          action="update-booking.php"
                                          <?= $action === 'cancel'
                                              ? 'onsubmit="return confirm(\'Cancel this booking request?\');"'
                                              : '' ?>>

                                        <input type="hidden" name="csrf_token"
                                               value="<?= e($_SESSION['booking_csrf_admin']) ?>">

                                        <input type="hidden" name="booking_id"
                                               value="<?= (int)$booking['booking_id'] ?>">

                                        <input type="hidden" name="action"
                                               value="<?= e($action) ?>">

                                        <input type="hidden" name="return_to"
                                               value="bookings">

                                        <button type="submit"
                                                class="sh-<?= e($action) ?>-btn">
                                            <?= ucfirst($action) ?>
                                        </button>
                                    </form>

                                <?php endforeach; ?>

                            <?php endif; ?>

                        </div>
                    </td>
                </tr>

            <?php endforeach; ?>
        <?php endif; ?>
        </tbody>
    </table>
</div>
