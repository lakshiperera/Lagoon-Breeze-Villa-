<?php
// admin/dashboard.php - summary numbers + latest reservations
$base = '../';
require_once '../includes/db.php';
require_admin();

$total_rooms    = $pdo->query("SELECT COUNT(*) FROM rooms")->fetchColumn();
$total_bookings = $pdo->query("SELECT COUNT(*) FROM bookings")->fetchColumn();
$pending        = $pdo->query("SELECT COUNT(*) FROM bookings WHERE status = 'Pending'")->fetchColumn();
$revenue        = $pdo->query("SELECT COALESCE(SUM(total_cost), 0) FROM bookings WHERE status = 'Confirmed'")->fetchColumn();

$recent = $pdo->query("SELECT b.*, u.full_name, r.name AS room_name
                       FROM bookings b JOIN users u ON b.user_id = u.id JOIN rooms r ON b.room_id = r.id
                       ORDER BY b.created_at DESC, b.id DESC LIMIT 8")->fetchAll();

$page_title = 'Admin Dashboard';
include '../includes/header.php';
?>
<section>
    <div class="container">
        <h1>Admin dashboard</h1>
        <div class="stats">
            <div class="stat"><strong><?php echo (int)$total_rooms; ?></strong>Total rooms</div>
            <div class="stat"><strong><?php echo (int)$total_bookings; ?></strong>Total bookings</div>
            <div class="stat"><strong><?php echo (int)$pending; ?></strong>Pending approvals</div>
            <div class="stat"><strong><?php echo lkr($revenue); ?></strong>Confirmed revenue</div>
        </div>

        <h2>Recent reservations</h2>
        <div class="table-wrap">
        <table>
            <tr><th>#</th><th>Guest</th><th>Room</th><th>Check-in</th><th>Check-out</th><th>Total</th><th>Status</th></tr>
            <?php foreach ($recent as $b): ?>
            <tr>
                <td><?php echo (int)$b['id']; ?></td>
                <td><?php echo e($b['full_name']); ?></td>
                <td><?php echo e($b['room_name']); ?></td>
                <td><?php echo e($b['check_in']); ?></td>
                <td><?php echo e($b['check_out']); ?></td>
                <td><?php echo lkr($b['total_cost']); ?></td>
                <td><span class="badge badge-<?php echo e($b['status']); ?>"><?php echo e($b['status']); ?></span></td>
            </tr>
            <?php endforeach; ?>
        </table>
        </div>
        <p><a class="btn" href="manage_bookings.php">Manage bookings</a> <a class="btn btn-brown" href="manage_rooms.php">Manage rooms</a></p>
    </div>
</section>
<?php include '../includes/footer.php'; ?>
