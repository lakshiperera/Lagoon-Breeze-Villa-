<?php
// login.php - log in with email + password
$base = '';
require_once 'includes/db.php';

if (is_logged_in()) { header('Location: index.php'); exit; }

$error = '';
$email = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch();

    if ($user && password_verify($password, $user['password_hash'])) {
        session_regenerate_id(true);   // new session id after login
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['name'] = $user['full_name'];
        $_SESSION['role'] = $user['role'];

        if ($user['role'] === 'admin') {
            header('Location: admin/dashboard.php');
        } else {
            // go back to the page the guest wanted (only local paths are allowed)
            $go = $_SESSION['after_login'] ?? 'index.php';
            unset($_SESSION['after_login']);
            if (strpos($go, '//') !== false || strpos($go, 'http') === 0) { $go = 'index.php'; }
            header('Location: ' . $go);
        }
        exit;
    }
    $error = 'Wrong email or password.';
}

$page_title = 'Login';
include 'includes/header.php';
?>
<section>
    <div class="container">
        <div class="form-box">
            <h1>Log in</h1>
            <?php if ($error): ?><div class="alert alert-error"><?php echo e($error); ?></div><?php endif; ?>
            <form method="post" action="login.php">
                <div class="field"><label for="email">Email</label><input type="email" id="email" name="email" value="<?php echo e($email); ?>" required></div>
                <div class="field"><label for="password">Password</label><input type="password" id="password" name="password" required></div>
                <button class="btn" type="submit">Log in</button>
                <p class="small">New guest? <a href="register.php">Create an account</a></p>
            </form>
        </div>
    </div>
</section>
<?php include 'includes/footer.php'; ?>
