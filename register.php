<?php
// register.php - guest registration
$base = '';
require_once 'includes/db.php';

if (is_logged_in()) { header('Location: index.php'); exit; }

$errors = [];
$name = $email = $phone = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name     = trim($_POST['full_name'] ?? '');
    $email    = trim($_POST['email'] ?? '');
    $phone    = trim($_POST['phone'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm  = $_POST['confirm'] ?? '';

    if (strlen($name) < 3 || strlen($name) > 100) { $errors[] = 'Full name must be 3 to 100 characters.'; }
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) { $errors[] = 'Please enter a valid email address.'; }
    if (!preg_match('/^\+?[0-9 \-]{9,15}$/', $phone)) { $errors[] = 'Please enter a valid phone number (9 to 15 digits).'; }
    if (strlen($password) < 8) { $errors[] = 'Password must be at least 8 characters.'; }
    if ($password !== $confirm) { $errors[] = 'Passwords do not match.'; }

    if (!$errors) {
        $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->execute([$email]);
        if ($stmt->fetch()) {
            $errors[] = 'That email is already registered. Try logging in.';
        }
    }

    if (!$errors) {
        $hash = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $pdo->prepare("INSERT INTO users (full_name, email, phone, password_hash, role) VALUES (?, ?, ?, ?, 'guest')");
        $stmt->execute([$name, $email, $phone, $hash]);
        set_flash('Account created. You can log in now.');
        header('Location: login.php');
        exit;
    }
}

$page_title = 'Register';
include 'includes/header.php';
?>
<section>
    <div class="container">
        <div class="form-box">
            <h1>Create an account</h1>
            <?php if ($errors): ?>
                <div class="alert alert-error"><ul><?php foreach ($errors as $er) { echo '<li>' . e($er) . '</li>'; } ?></ul></div>
            <?php endif; ?>
            <form method="post" action="register.php">
                <div class="field"><label for="full_name">Full name</label><input type="text" id="full_name" name="full_name" value="<?php echo e($name); ?>" required></div>
                <div class="field"><label for="email">Email</label><input type="email" id="email" name="email" value="<?php echo e($email); ?>" required></div>
                <div class="field"><label for="phone">Contact number</label><input type="text" id="phone" name="phone" value="<?php echo e($phone); ?>" placeholder="0771234567" required></div>
                <div class="field"><label for="password">Password (min 8 characters)</label><input type="password" id="password" name="password" required></div>
                <div class="field"><label for="confirm">Confirm password</label><input type="password" id="confirm" name="confirm" required></div>
                <button class="btn" type="submit">Register</button>
                <p class="small">Already have an account? <a href="login.php">Log in</a></p>
            </form>
        </div>
    </div>
</section>
<?php include 'includes/footer.php'; ?>
