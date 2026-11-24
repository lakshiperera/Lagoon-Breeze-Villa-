<?php
// booking.php - reservation form (login required)
$base = '';
require_once 'includes/db.php';
require_login();

// Logged-in guest details (pre-filled, read only)
$stmt = $pdo->prepare("SELECT full_name, email, phone FROM users WHERE id = ?");
$stmt->execute([$_SESSION['user_id']]);
$guest = $stmt->fetch();

// All rooms that can be booked
$all_rooms = $pdo->query("SELECT id, name, room_type, max_guests, price_per_night FROM rooms WHERE status = 'Available' ORDER BY price_per_night")->fetchAll();

// Values come from POST (form submitted) or GET (link from rooms page)
$src = ($_SERVER['REQUEST_METHOD'] === 'POST') ? $_POST : $_GET;
$room_id   = (int)($src['room_id'] ?? 0);
$check_in  = trim($src['check_in'] ?? '');
$check_out = trim($src['check_out'] ?? '');
$guests    = (int)($src['guests'] ?? 1);
$requests  = trim($src['special_requests'] ?? '');
if ($guests < 1) { $guests = 1; }

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Server-side validation (never trust only the JavaScript checks)
    $stmt = $pdo->prepare("SELECT * FROM rooms WHERE id = ? AND status = 'Available'");
    $stmt->execute([$room_id]);
    $room = $stmt->fetch();

    if (!$room) { $errors[] = 'Please choose a valid room that is available.'; }
    if (!valid_date($check_in) || !valid_date($check_out)) {
        $errors[] = 'Please enter valid check-in and check-out dates.';
    } else {
        if ($check_in < date('Y-m-d')) { $errors[] = 'Check-in date cannot be in the past.'; }
        if ($check_out <= $check_in) { $errors[] = 'Check-out date must be after check-in date.'; }
    }
    if ($room && ($guests < 1 || $guests > $room['max_guests'])) {
        $errors[] = 'Number of guests must be between 1 and ' . (int)$room['max_guests'] . ' for this room.';
    }
    if (strlen($requests) > 500) { $errors[] = 'Special requests must be under 500 characters.'; }

    if (!$errors && !room_is_free($pdo, $room['id'], $check_in, $check_out)) {
        $errors[] = 'Sorry, this room is already booked for those dates. Please try other dates.';
    }

    if (!$errors) {
        // Total cost is always calculated on the server
        $nights = count_nights($check_in, $check_out);
        $total = $nights * $room['price_per_night'];
        $stmt = $pdo->prepare("INSERT INTO bookings (user_id, room_id, check_in, check_out, guests, nights, total_cost, special_requests, status)
                               VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'Pending')");
        $stmt->execute([$_SESSION['user_id'], $room['id'], $check_in, $check_out, $guests, $nights, $total, $requests]);
        set_flash('Booking request sent! It is Pending until the villa confirms it. Total: ' . lkr($total));
        header('Location: my_bookings.php');
        exit;
    }
}

$page_title = 'Book a Room';
include 'includes/header.php';
?>
<section>
    <div class="container">
        <div class="form-box wide">
            <h1>Book your stay</h1>
            <?php if ($errors): ?>
                <div class="alert alert-error"><ul><?php foreach ($errors as $er) { echo '<li>' . e($er) . '</li>'; } ?></ul></div>
            <?php endif; ?>
            <div class="alert alert-error" id="js-errors" style="display:none;"></div>

            <form method="post" action="booking.php" id="booking-form">
                <div class="row">
                    <div class="field"><label>Name</label><input type="text" value="<?php echo e($guest['full_name']); ?>" readonly></div>
                    <div class="field"><label>Email</label><input type="text" value="<?php echo e($guest['email']); ?>" readonly></div>
                    <div class="field"><label>Contact number</label><input type="text" value="<?php echo e($guest['phone']); ?>" readonly></div>
                </div>

                <div class="field">
                    <label for="room_id">Room</label>
                    <select id="room_id" name="room_id" required>
                        <option value="">-- Select a room --</option>
                        <?php foreach ($all_rooms as $r): ?>
                            <option value="<?php echo (int)$r['id']; ?>"
                                    data-price="<?php echo e($r['price_per_night']); ?>"
                                    data-max="<?php echo (int)$r['max_guests']; ?>"
                                    <?php echo $room_id == $r['id'] ? 'selected' : ''; ?>>
                                <?php echo e($r['name']) . ' (' . e($r['room_type']) . ') - ' . lkr($r['price_per_night']) . '/night'; ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="row">
                    <div class="field"><label for="check_in">Check-in</label><input type="date" id="check_in" name="check_in" value="<?php echo e($check_in); ?>" required></div>
                    <div class="field"><label for="check_out">Check-out</label><input type="date" id="check_out" name="check_out" value="<?php echo e($check_out); ?>" required></div>
                    <div class="field"><label for="guests">Guests</label><input type="number" id="guests" name="guests" min="1" value="<?php echo $guests; ?>" required></div>
                </div>

                <div class="field">
                    <label for="special_requests">Special requests (optional)</label>
                    <textarea id="special_requests" name="special_requests" rows="3" maxlength="500"><?php echo e($requests); ?></textarea>
                </div>

                <div class="summary-box">
                    <p><strong>Nights:</strong> <span id="sum-nights">0</span> |
                       <strong>Max guests:</strong> <span id="sum-max">-</span></p>
                    <p><strong>Estimated total:</strong> <span id="sum-total">LKR 0.00</span></p>
                    <p class="small">The final amount is calculated again by the server when you submit.</p>
                </div>
                <button class="btn" type="submit">Send booking request</button>
            </form>
        </div>
    </div>
</section>
<?php include 'includes/footer.php'; ?>
