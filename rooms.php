<?php
// rooms.php - list rooms and filter by dates, guests, type and price
$base = '';
require_once 'includes/db.php';

$check_in  = trim($_GET['check_in'] ?? '');
$check_out = trim($_GET['check_out'] ?? '');
$guests    = (int)($_GET['guests'] ?? 0);
$type      = trim($_GET['room_type'] ?? '');
$max_price = (int)($_GET['max_price'] ?? 0);

$errors = [];
$dates_ok = false;

// Dates are optional, but if one is given both must be valid
if ($check_in !== '' || $check_out !== '') {
    if (!valid_date($check_in) || !valid_date($check_out)) {
        $errors[] = 'Please enter both a valid check-in and check-out date.';
    } elseif ($check_in < date('Y-m-d')) {
        $errors[] = 'Check-in date cannot be in the past.';
    } elseif ($check_out <= $check_in) {
        $errors[] = 'Check-out date must be after check-in date.';
    } else {
        $dates_ok = true;
    }
}

// Build the query step by step (always with prepared statement placeholders)
$sql = "SELECT * FROM rooms WHERE status = 'Available'";
$params = [];
if ($guests > 0) { $sql .= " AND max_guests >= ?"; $params[] = $guests; }
if ($type !== '') { $sql .= " AND room_type = ?"; $params[] = $type; }
if ($max_price > 0) { $sql .= " AND price_per_night <= ?"; $params[] = $max_price; }
if ($dates_ok) {
    // hide rooms that have a Confirmed booking overlapping the dates
    $sql .= " AND id NOT IN (SELECT room_id FROM bookings
                             WHERE status = 'Confirmed' AND check_in < ? AND check_out > ?)";
    $params[] = $check_out;
    $params[] = $check_in;
}
$sql .= " ORDER BY price_per_night ASC";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$rooms = $stmt->fetchAll();

// Pass the chosen dates/guests on to the booking page
$query = '';
if ($dates_ok) { $query .= '&check_in=' . urlencode($check_in) . '&check_out=' . urlencode($check_out); }
if ($guests > 0) { $query .= '&guests=' . $guests; }

$page_title = 'Rooms';
include 'includes/header.php';
?>
<section>
    <div class="container">
        <h1>Rooms &amp; availability</h1>
        <form class="search-bar date-search" method="get" action="rooms.php">
            <div class="field"><label for="check_in">Check-in</label><input type="date" id="check_in" name="check_in" value="<?php echo e($check_in); ?>"></div>
            <div class="field"><label for="check_out">Check-out</label><input type="date" id="check_out" name="check_out" value="<?php echo e($check_out); ?>"></div>
            <div class="field"><label for="guests">Guests</label><input type="number" id="guests" name="guests" min="1" max="10" value="<?php echo $guests > 0 ? $guests : ''; ?>"></div>
            <div class="field"><label for="room_type">Room type</label>
                <select id="room_type" name="room_type">
                    <option value="">Any</option>
                    <?php foreach (['Standard', 'Deluxe', 'Family', 'Suite'] as $t): ?>
                        <option value="<?php echo $t; ?>" <?php echo $type === $t ? 'selected' : ''; ?>><?php echo $t; ?></option>
                    <?php endforeach; ?>
                </select></div>
            <div class="field"><label for="max_price">Max price (LKR)</label><input type="number" id="max_price" name="max_price" min="0" step="500" value="<?php echo $max_price > 0 ? $max_price : ''; ?>"></div>
            <div class="field"><button class="btn" type="submit">Search</button> <a href="rooms.php" class="small">Clear</a></div>
        </form>

        <?php if ($errors): ?>
            <div class="alert alert-error"><ul><?php foreach ($errors as $er) { echo '<li>' . e($er) . '</li>'; } ?></ul></div>
        <?php endif; ?>

        <?php if ($dates_ok): ?>
            <p>Showing rooms free from <strong><?php echo e($check_in); ?></strong> to <strong><?php echo e($check_out); ?></strong>.</p>
        <?php endif; ?>

        <?php if (count($rooms) == 0): ?>
            <div class="alert alert-error">No rooms match your search. Try different dates or a higher price limit.</div>
        <?php endif; ?>

        <div class="grid">
            <?php foreach ($rooms as $room) { room_card($room, $query); } ?>
        </div>
    </div>
</section>
<?php include 'includes/footer.php'; ?>
