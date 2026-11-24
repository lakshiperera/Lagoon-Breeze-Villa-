<?php
// my_bookings.php - guest's own bookings, cancel pending ones, leave a review
$base = '';
require_once 'includes/db.php';
require_login();
$uid = $_SESSION['user_id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $booking_id = (int)($_POST['booking_id'] ?? 0);

    if ($action === 'cancel') {
        // only the owner can cancel, and only while Pending
        $stmt = $pdo->prepare("UPDATE bookings SET status = 'Cancelled' WHERE id = ? AND user_id = ? AND status = 'Pending'");
        $stmt->execute([$booking_id, $uid]);
        if ($stmt->rowCount() > 0) { set_flash('Booking cancelled.'); }
        else { set_flash('Only your own Pending bookings can be cancelled.', 'error'); }

    } elseif ($action === 'review') {
        $rating = (int)($_POST['rating'] ?? 0);
        $comment = trim($_POST['comment'] ?? '');

        $stmt = $pdo->prepare("SELECT id FROM bookings WHERE id = ? AND user_id = ? AND status = 'Confirmed'");
        $stmt->execute([$booking_id, $uid]);
        $stmt2 = $pdo->prepare("SELECT id FROM reviews WHERE booking_id = ?");
        $stmt2->execute([$booking_id]);

        if (!$stmt->fetch()) {
            set_flash('You can only review your own Confirmed bookings.', 'error');
        } elseif ($stmt2->fetch()) {
            set_flash('You already reviewed this booking.', 'error');
        } elseif ($rating < 1 || $rating > 5 || strlen($comment) < 5 || strlen($comment) > 500) {
            set_flash('Please give a rating (1-5) and a comment of 5 to 500 characters.', 'error');
        } else {
            $stmt = $pdo->prepare("INSERT INTO reviews (user_id, booking_id, rating, comment) VALUES (?, ?, ?, ?)");
            $stmt->execute([$uid, $booking_id, $rating, $comment]);
            set_flash('Thank you for your review!');
        }
    }
    header('Location: my_bookings.php');
    exit;
}

$stmt = $pdo->prepare("SELECT b.*, r.name AS room_name, rv.id AS review_id, rv.rating, rv.comment
                       FROM bookings b
                       JOIN rooms r ON b.room_id = r.id
                       LEFT JOIN reviews rv ON rv.booking_id = b.id
                       WHERE b.user_id = ? ORDER BY b.created_at DESC, b.id DESC");
$stmt->execute([$uid]);
$bookings = $stmt->fetchAll();

$page_title = 'My Bookings';
include 'includes/header.php';
?>
<section>
    <div class="container">
        <h1>My bookings</h1>
        <?php if (count($bookings) == 0): ?>
            <div class="alert alert-success">You have no bookings yet. <a href="rooms.php">Browse rooms</a> to get started.</div>
        <?php endif; ?>

        <?php if (count($bookings) > 0): ?>
        <div class="table-wrap">
        <table>
            <tr><th>#</th><th>Room</th><th>Check-in</th><th>Check-out</th><th>Guests</th><th>Total</th><th>Status</th><th>Action</th></tr>
            <?php foreach ($bookings as $b): ?>
            <tr>
                <td><?php echo (int)$b['id']; ?></td>
                <td><?php echo e($b['room_name']); ?></td>
                <td><?php echo e($b['check_in']); ?></td>
                <td><?php echo e($b['check_out']); ?></td>
                <td><?php echo (int)$b['guests']; ?></td>
                <td><?php echo lkr($b['total_cost']); ?></td>
                <td><span class="badge badge-<?php echo e($b['status']); ?>"><?php echo e($b['status']); ?></span></td>
                <td>
                    <?php if ($b['status'] === 'Pending'): ?>
                        <form method="post" action="my_bookings.php">
                            <input type="hidden" name="action" value="cancel">
                            <input type="hidden" name="booking_id" value="<?php echo (int)$b['id']; ?>">
                            <button class="btn btn-red btn-small confirm-action" data-message="Cancel this booking?" type="submit">Cancel</button>
                        </form>
                    <?php elseif ($b['status'] === 'Confirmed' && !$b['review_id']): ?>
                        <form method="post" action="my_bookings.php">
                            <input type="hidden" name="action" value="review">
                            <input type="hidden" name="booking_id" value="<?php echo (int)$b['id']; ?>">
                            <select name="rating" required>
                                <option value="">Rating</option>
                                <?php for ($i = 5; $i >= 1; $i--) { echo "<option value=\"$i\">$i star" . ($i > 1 ? 's' : '') . "</option>"; } ?>
                            </select>
                            <textarea name="comment" rows="2" maxlength="500" placeholder="Write your review" required></textarea>
                            <button class="btn btn-small" type="submit">Submit review</button>
                        </form>
                    <?php elseif ($b['review_id']): ?>
                        <span class="stars"><?php echo str_repeat('★', (int)$b['rating']); ?></span>
                        <span class="small">Reviewed</span>
                    <?php else: ?>
                        <span class="small">-</span>
                    <?php endif; ?>
                </td>
            </tr>
            <?php endforeach; ?>
        </table>
        </div>
        <?php endif; ?>
    </div>
</section>
<?php include 'includes/footer.php'; ?>
