<?php
// includes/db.php - database connection + small helper functions used on every page
session_start();

$host   = 'localhost';
$dbname = 'lagoon_breeze_villa';
$dbuser = 'root';      // default XAMPP user
$dbpass = '';          // default XAMPP password is empty

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $dbuser, $dbpass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die('Database connection failed. Start MySQL in XAMPP and import db_structure.sql first.');
}

// Escape text before printing it in HTML (prevents XSS)
function e($text) {
    return htmlspecialchars((string)$text, ENT_QUOTES, 'UTF-8');
}

function is_logged_in() {
    return isset($_SESSION['user_id']);
}

function is_admin() {
    return isset($_SESSION['role']) && $_SESSION['role'] === 'admin';
}

// Send guests who are not logged in to the login page
function require_login($base = '') {
    if (!is_logged_in()) {
        $_SESSION['after_login'] = $_SERVER['REQUEST_URI'];
        set_flash('Please log in to continue.', 'error');
        header('Location: ' . $base . 'login.php');
        exit;
    }
}

// Only admins may open admin pages
function require_admin($base = '../') {
    if (!is_admin()) {
        set_flash('Admin access only.', 'error');
        header('Location: ' . $base . 'login.php');
        exit;
    }
}

// One-time message shown at the top of the next page
function set_flash($message, $type = 'success') {
    $_SESSION['flash'] = ['message' => $message, 'type' => $type];
}

// Check a date string is a real date in YYYY-MM-DD format
function valid_date($d) {
    $dt = DateTime::createFromFormat('Y-m-d', $d);
    return $dt && $dt->format('Y-m-d') === $d;
}

// Number of nights between two valid dates
function count_nights($in, $out) {
    return (int)(new DateTime($in))->diff(new DateTime($out))->days;
}

// Returns true if the room has NO confirmed booking overlapping the dates.
// Overlap rule: check_in < requested_checkout AND check_out > requested_checkin
function room_is_free($pdo, $room_id, $in, $out, $ignore_booking_id = 0) {
    $sql = "SELECT COUNT(*) FROM bookings
            WHERE room_id = ? AND status = 'Confirmed'
            AND check_in < ? AND check_out > ? AND id <> ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$room_id, $out, $in, $ignore_booking_id]);
    return $stmt->fetchColumn() == 0;
}

function lkr($amount) {
    return 'LKR ' . number_format($amount, 2);
}

// Prints one room card (used on the home page and the rooms page)
function room_card($r, $query = '') {
    ?>
    <div class="card room-card">
        <img src="assets/images/<?php echo e($r['image']); ?>" alt="<?php echo e($r['name']); ?>">
        <div class="card-body">
            <span class="tag"><?php echo e($r['room_type']); ?></span>
            <h3><?php echo e($r['name']); ?></h3>
            <p><?php echo e($r['description']); ?></p>
            <p class="small"><strong>Facilities:</strong> <?php echo e($r['facilities']); ?></p>
            <p class="small"><strong>Max guests:</strong> <?php echo (int)$r['max_guests']; ?></p>
            <p class="price"><?php echo lkr($r['price_per_night']); ?> <span class="small">/ night</span></p>
            <a class="btn" href="booking.php?room_id=<?php echo (int)$r['id'] . $query; ?>">Book this room</a>
        </div>
    </div>
    <?php
}
