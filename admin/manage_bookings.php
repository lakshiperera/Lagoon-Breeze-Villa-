<?php
// admin/manage_bookings.php - confirm or cancel bookings
$base = '../';
require_once '../includes/db.php';
require_admin();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $id = (int)($_POST['id'] ?? 0);

    $stmt = $pdo->prepare("SELECT * FROM bookings WHERE id = ?");
    $stmt->execute([$id]);
    $b = $stmt->fetch();

    if (!$b) {
        set_flash('Booking not found.', 'error');
    } elseif ($action === 'confirm') {
        if ($b['status'] !== 'Pending') {
            set_flash('Only Pending bookings can be confirmed.', 'error');
        } elseif (!room_is_free($pdo, $b['room_id'], $b['check_in'], $b['check_out'], $b['id'])) {
            // another Confirmed booking already uses this room on those dates
            set_flash('Cannot confirm: this room already has a Confirmed booking that overlaps these dates.', 'error');
        } else {
            $pdo->prepare("UPDATE bookings SET status = 'Confirmed' WHERE id = ?")->execute([$id]);
            set_flash('Booking #' . $id . ' confirmed.');
        }
    } elseif ($action === 'cancel') {
        if ($b['status'] === 'Cancelled') {
            set_flash('Booking is already cancelled.', 'error');
        } else {
            $pdo->prepare("UPDATE bookings SET status = 'Cancelled' WHERE id = ?")->execute([$id]);
            set_flash('Booking #' . $id . ' cancelled.');
        }
    }
    header('Location: manage_bookings.php');
    exit;
}

$bookings = $pdo->query("SELECT b.*, u.full_name, u.email, u.phone, r.name AS room_name
                         FROM bookings b JOIN users u ON b.user_id = u.id JOIN rooms r ON b.room_id = r.id
                         ORDER BY FIELD(b.status, 'Pending', 'Confirmed', 'Cancelled'), b.check_in")->fetchAll();

$page_title = 'Manage Bookings';
include '../includes/header.php';
?>
<section>
    <div class="container">
        <h1>Manage bookings</h1>
        <div class="table-wrap">
        <table>
            <tr><th>#</th><th>Guest contact</th><th>Room</th><th>Dates</th><th>Guests</th><th>Total</th><th>Requests</th><th>Status</th><th>Actions</th></tr>
            <?php foreach ($bookings as $b): ?>
            <tr>
                <td><?php echo (int)$b['id']; ?></td>
                <td><?php echo e($b['full_name']); ?><br><span class="small"><?php echo e($b['email']); ?><br><?php echo e($b['phone']); ?></span></td>
                <td><?php echo e($b['room_name']); ?></td>
                <td><?php echo e($b['check_in']); ?> to <?php echo e($b['check_out']); ?><br><span class="small"><?php echo (int)$b['nights']; ?> nights</span></td>
                <td><?php echo (int)$b['guests']; ?></td>
                <td><?php echo lkr($b['total_cost']); ?></td>
                <td class="small"><?php echo e($b['special_requests'] ?: '-'); ?></td>
                <td><span class="badge badge-<?php echo e($b['status']); ?>"><?php echo e($b['status']); ?></span></td>
                <td>
                    <?php if ($b['status'] === 'Pending'): ?>
                    <form method="post" action="manage_bookings.php" style="display:inline;">
                        <input type="hidden" name="action" value="confirm"><input type="hidden" name="id" value="<?php echo (int)$b['id']; ?>">
                        <button class="btn btn-small" type="submit">Confirm</button>
                    </form>
                    <?php endif; ?>
                    <?php if ($b['status'] !== 'Cancelled'): ?>
                    <form method="post" action="manage_bookings.php" style="display:inline;">
                        <input type="hidden" name="action" value="cancel"><input type="hidden" name="id" value="<?php echo (int)$b['id']; ?>">
                        <button class="btn btn-red btn-small confirm-action" data-message="Cancel booking #<?php echo (int)$b['id']; ?>?" type="submit">Cancel</button>
                    </form>
                    <?php endif; ?>
                </td>
            </tr>
            <?php endforeach; ?>
        </table>
        </div>
    </div>
</section>
<?php include '../includes/footer.php'; ?>
