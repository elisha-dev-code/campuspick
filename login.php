<?php
$pageTitle = 'Login';
$authPage = true;
include 'includes/db.php';

if (isset($_SESSION['user'])) {
    header('Location: products.php');
    exit;
}

$error = '';
$email = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = strtolower(trim($_POST['email'] ?? ''));
    $pass  = $_POST['password'] ?? '';

    $stmt = $conn->prepare("SELECT id, name, password, role FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $user = $stmt->get_result()->fetch_assoc();

    if ($user && password_verify($pass, $user['password'])) {
        session_regenerate_id(true);
        $_SESSION['user'] = ['id' => $user['id'], 'name' => $user['name'], 'role' => $user['role']];

        $next = $_GET['next'] ?? '';
        if (!preg_match('/^[a-z_]+\.php$/', $next)) {
            $next = 'products.php';
        }
        header('Location: ' . $next);
        exit;
    }
    $error = 'Incorrect email or password.';
}

include 'includes/header.php';
?>
<div class="fb-page">
  <div class="fb-wrap">

    <section class="fb-brand">
      <a href="index.php" class="fb-logo">Campus<span>Pick</span></a>
      <p>Pick it. Own it. FUOYE. Buy, sell and pick from fellow students, all in one place.</p>

      <ul class="fb-features">
        <li>📚 Textbooks, gadgets, hostel items and more</li>
        <li>💬 Chat with sellers instantly on WhatsApp</li>
        <li>🎨 Preview custom prints before you order</li>
      </ul>

      <div class="fb-floats" aria-hidden="true">
        <div class="fb-mini"><span>📚</span><div><b>Engineering Mathematics</b><small>₦6,500</small></div></div>
        <div class="fb-mini"><span>🌀</span><div><b>Mini Rechargeable Fan</b><small>₦4,500</small></div></div>
        <div class="fb-mini"><span>🔋</span><div><b>Phone Power Bank</b><small>₦12,000</small></div></div>
      </div>
    </section>

    <section class="fb-side">
      <div class="fb-card">
        <div class="fb-title">
          <h1>Welcome back</h1>
          <p>Log in to pick, buy and sell.</p>
        </div>

        <?php if ($error): ?>
          <div class="alert" role="alert"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>

        <form method="POST" class="auth-form">
          <div class="field">
            <input type="email" name="email" placeholder="Email address" aria-label="Email address" autocomplete="email" value="<?php echo htmlspecialchars($email); ?>" required>
          </div>
          <div class="field">
            <div class="pass-wrap">
              <input type="password" id="password" name="password" placeholder="Password" aria-label="Password" autocomplete="current-password" required>
              <button type="button" class="toggle-pass" data-target="password">Show</button>
            </div>
          </div>
          <button type="submit" class="btn btn-green">Log in</button>
        </form>

        <a href="#" class="fb-forgot">Forgot password?</a>
        <hr class="fb-divider">
        <div class="fb-create">
          <a href="signup.php" class="btn">Create new account</a>
        </div>
      </div>
      <a href="index.php" class="fb-back">&larr; Back to home</a>
    </section>

  </div>
</div>
<script src="js/auth.js"></script>
<?php include 'includes/footer.php'; ?>