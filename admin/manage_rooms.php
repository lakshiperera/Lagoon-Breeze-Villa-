<?php
// admin/manage_rooms.php - add, edit and delete rooms
$base = '../';
require_once '../includes/db.php';
require_admin();

$types = ['Standard', 'Deluxe', 'Family', 'Suite'];
$image_files = array_values(array_filter(scandir(__DIR__ . '/../assets/images'), function($f) {
    return preg_match('/\.(jpg|jpeg|png|jfif|webp)$/i', $f) && !in_array($f, ['hero.jpg', 'dashboard banner.jfif']);
}));
$images = !empty($image_files) ? $image_files : ['hero.jpg'];
$errors = [];

// Default (empty) form values
$room = ['id' => 0, 'name' => '', 'room_type' => 'Standard', 'description' => '', 'facilities' => '',
         'max_guests' => 2, 'price_per_night' => '', 'image' => $images[0], 'status' => 'Available'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'delete') {
        $id = (int)($_POST['id'] ?? 0);
        try {
            $stmt = $pdo->prepare("DELETE FROM rooms WHERE id = ?");
            $stmt->execute([$id]);
            set_flash('Room deleted.');
        } catch (PDOException $ex) {
            // foreign key stops deleting rooms that already have bookings
            set_flash('This room has bookings and cannot be deleted. Set it to Unavailable instead.', 'error');
        }
        header('Location: manage_rooms.php');
        exit;
    }

    if ($action === 'save') {
        $room['id']              = (int)($_POST['id'] ?? 0);
        $room['name']            = trim($_POST['name'] ?? '');
        $room['room_type']       = $_POST['room_type'] ?? '';
        $room['description']     = trim($_POST['description'] ?? '');
        $room['facilities']      = trim($_POST['facilities'] ?? '');
        $room['max_guests']      = (int)($_POST['max_guests'] ?? 0);
        $room['price_per_night'] = trim($_POST['price_per_night'] ?? '');
        $room['image']           = $_POST['image'] ?? '';
        $room['status']          = $_POST['status'] ?? '';

        if ($room['name'] === '' || strlen($room['name']) > 100) { $errors[] = 'Room name is required (max 100 characters).'; }
        if (!in_array($room['room_type'], $types)) { $errors[] = 'Choose a valid room type.'; }
        if ($room['description'] === '') { $errors[] = 'Description is required.'; }
        if ($room['facilities'] === '') { $errors[] = 'Facilities are required.'; }
        if ($room['max_guests'] < 1 || $room['max_guests'] > 10) { $errors[] = 'Max guests must be between 1 and 10.'; }
        if (!is_numeric($room['price_per_night']) || $room['price_per_night'] <= 0) { $errors[] = 'Price must be a number greater than 0.'; }
        if (!in_array($room['image'], $images)) { $errors[] = 'Choose a valid image.'; }
        if (!in_array($room['status'], ['Available', 'Unavailable'])) { $errors[] = 'Choose a valid status.'; }

        if (!$errors) {
            if ($room['id'] > 0) {
                $stmt = $pdo->prepare("UPDATE rooms SET name=?, room_type=?, description=?, facilities=?, max_guests=?, price_per_night=?, image=?, status=? WHERE id=?");
                $stmt->execute([$room['name'], $room['room_type'], $room['description'], $room['facilities'], $room['max_guests'], $room['price_per_night'], $room['image'], $room['status'], $room['id']]);
                set_flash('Room updated.');
            } else {
                $stmt = $pdo->prepare("INSERT INTO rooms (name, room_type, description, facilities, max_guests, price_per_night, image, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
                $stmt->execute([$room['name'], $room['room_type'], $room['description'], $room['facilities'], $room['max_guests'], $room['price_per_night'], $room['image'], $room['status']]);
                set_flash('New room added.');
            }
            header('Location: manage_rooms.php');
            exit;
        }
    }
} elseif (isset($_GET['edit'])) {
    // load the room to edit into the form
    $stmt = $pdo->prepare("SELECT * FROM rooms WHERE id = ?");
    $stmt->execute([(int)$_GET['edit']]);
    $found = $stmt->fetch();
    if ($found) { $room = $found; }
}

$all = $pdo->query("SELECT * FROM rooms ORDER BY id")->fetchAll();

$page_title = 'Manage Rooms';
include '../includes/header.php';
?>
<section>
    <div class="container">
        <h1>Manage rooms</h1>

        <div class="form-box wide">
            <h2><?php echo $room['id'] ? 'Edit room #' . (int)$room['id'] : 'Add a new room'; ?></h2>
            <?php if ($errors): ?>
                <div class="alert alert-error"><ul><?php foreach ($errors as $er) { echo '<li>' . e($er) . '</li>'; } ?></ul></div>
            <?php endif; ?>
            <form method="post" action="manage_rooms.php">
                <input type="hidden" name="action" value="save">
                <input type="hidden" name="id" value="<?php echo (int)$room['id']; ?>">
                <div class="row">
                    <div class="field"><label for="name">Room name</label><input type="text" id="name" name="name" value="<?php echo e($room['name']); ?>" required></div>
                    <div class="field"><label for="room_type">Type</label>
                        <select id="room_type" name="room_type">
                            <?php foreach ($types as $t) { echo '<option ' . ($room['room_type'] === $t ? 'selected' : '') . '>' . $t . '</option>'; } ?>
                        </select></div>
                </div>
                <div class="field"><label for="description">Description</label><textarea id="description" name="description" rows="3" required><?php echo e($room['description']); ?></textarea></div>
                <div class="field"><label for="facilities">Facilities (comma separated)</label><input type="text" id="facilities" name="facilities" value="<?php echo e($room['facilities']); ?>" required></div>
                <div class="row">
                    <div class="field"><label for="max_guests">Max guests</label><input type="number" id="max_guests" name="max_guests" min="1" max="10" value="<?php echo (int)$room['max_guests']; ?>" required></div>
                    <div class="field"><label for="price_per_night">Price per night (LKR)</label><input type="number" id="price_per_night" name="price_per_night" min="1" step="0.01" value="<?php echo e($room['price_per_night']); ?>" required></div>
                    <div class="field"><label for="image">Image</label>
                        <select id="image" name="image">
                            <?php foreach ($images as $img) { echo '<option ' . ($room['image'] === $img ? 'selected' : '') . '>' . $img . '</option>'; } ?>
                        </select></div>
                    <div class="field"><label for="status">Status</label>
                        <select id="status" name="status">
                            <?php foreach (['Available', 'Unavailable'] as $s) { echo '<option ' . ($room['status'] === $s ? 'selected' : '') . '>' . $s . '</option>'; } ?>
                        </select></div>
                </div>
                <button class="btn" type="submit"><?php echo $room['id'] ? 'Save changes' : 'Add room'; ?></button>
                <?php if ($room['id']): ?><a class="btn btn-brown" href="manage_rooms.php">Cancel edit</a><?php endif; ?>
            </form>
        </div>

        <h2 style="margin-top:2rem;">All rooms</h2>
        <div class="table-wrap">
        <table>
            <tr><th>#</th><th>Name</th><th>Type</th><th>Guests</th><th>Price / night</th><th>Status</th><th>Actions</th></tr>
            <?php foreach ($all as $r): ?>
            <tr>
                <td><?php echo (int)$r['id']; ?></td>
                <td><?php echo e($r['name']); ?></td>
                <td><?php echo e($r['room_type']); ?></td>
                <td><?php echo (int)$r['max_guests']; ?></td>
                <td><?php echo lkr($r['price_per_night']); ?></td>
                <td><span class="badge badge-<?php echo e($r['status']); ?>"><?php echo e($r['status']); ?></span></td>
                <td>
                    <a class="btn btn-small" href="manage_rooms.php?edit=<?php echo (int)$r['id']; ?>">Edit</a>
                    <form method="post" action="manage_rooms.php" style="display:inline;">
                        <input type="hidden" name="action" value="delete">
                        <input type="hidden" name="id" value="<?php echo (int)$r['id']; ?>">
                        <button class="btn btn-red btn-small confirm-action" data-message="Delete this room permanently?" type="submit">Delete</button>
                    </form>
                </td>
            </tr>
            <?php endforeach; ?>
        </table>
        </div>
    </div>
</section>
<?php include '../includes/footer.php'; ?>
