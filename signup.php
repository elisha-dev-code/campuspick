<?php
$pageTitle = 'Sign up';
$authPage = true;
include 'includes/db.php';

if (isset($_SESSION['user'])) {
    header('Location: products.php');
    exit;
}

$errors = [];
$first = $last = $email = $phone = '';
$role = 'buyer';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $first = trim($_POST['first_name'] ?? '');
    $last  = trim($_POST['last_name'] ?? '');
    $email = strtolower(trim($_POST['email'] ?? ''));
    $phone = preg_replace('/\D/', '', $_POST['phone'] ?? '');
    $pass  = $_POST['password'] ?? '';
    $role  = $_POST['role'] ?? 'buyer';

    if ($first === '' || $last === '') {
        $errors[] = 'Please enter your first and last name.';
    }
    if (mb_strlen($first . ' ' . $last) > 100) {
        $errors[] = 'That name is too long.';
    }
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Please enter a valid email address.';
    }
    if (strlen($phone) === 11 && $phone[0] === '0') {
        $phone = '234' . substr($phone, 1);
    }
    if (!preg_match('/^234\d{10}$/', $phone)) {
        $errors[] = 'Enter a valid Nigerian WhatsApp number, like 2348012345678.';
    }
    if (strlen($pass) < 6) {
        $errors[] = 'Password must be at least 6 characters.';
    }
    if (!in_array($role, ['buyer', 'seller'], true)) {
        $role = 'buyer';
    }

    if (!$errors) {
        $stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        if ($stmt->get_result()->num_rows > 0) {
            $errors[] = 'That email is already registered. Try logging in.';
        }
    }

    if (!$errors) {
        $hash = password_hash($pass, PASSWORD_DEFAULT);
        $name = $first . ' ' . $last;
        try {
            $stmt = $conn->prepare("INSERT INTO users (name, email, password, role, phone) VALUES (?, ?, ?, ?, ?)");
            $stmt->bind_param("sssss", $name, $email, $hash, $role, $phone);
            $stmt->execute();

            session_regenerate_id(true);
            $_SESSION['user'] = ['id' => $conn->insert_id, 'name' => $name, 'role' => $role];
            header('Location: products.php');
            exit;
        } catch (mysqli_sql_exception $e) {
            $errors[] = 'Something went wrong. Please try again.';
        }
    }
}

include 'includes/header.php';
?>
<div class="su-page">
  <a href="index.php" class="su-logo">Campus<span>Pick</span></a>

  <div class="su-card">
    <h1>Create a new account</h1>
    <p class="sub">It's quick and easy.</p>
    <hr class="su-divider">

    <?php if ($errors): ?>
      <div class="alert" role="alert">
        <ul>
          <?php foreach ($errors as $e): ?>
            <li><?php echo htmlspecialchars($e); ?></li>
          <?php endforeach; ?>
        </ul>
      </div>
    <?php endif; ?>

    <form method="POST" action="signup.php" class="auth-form">
      <div class="su-row">
        <div class="field">
          <input type="text" name="first_name" placeholder="First name" aria-label="First name" autocomplete="given-name" value="<?php echo htmlspecialchars($first); ?>" required>
        </div>
        <div class="field">
          <input type="text" name="last_name" placeholder="Last name" aria-label="Last name" autocomplete="family-name" value="<?php echo htmlspecialchars($last); ?>" required>
        </div>
      </div>
      <div class="field">
        <input type="email" name="email" placeholder="Email address" aria-label="Email address" autocomplete="email" value="<?php echo htmlspecialchars($email); ?>" required>
      </div>
      <div class="field">
        <input type="tel" name="phone" placeholder="WhatsApp (2348012345678)" aria-label="WhatsApp number" autocomplete="tel" value="<?php echo htmlspecialchars($phone); ?>" required>
      </div>
      <div class="field">
        <div class="pass-wrap">
          <input type="password" id="password" name="password" placeholder="Password (6+ characters)" aria-label="Password" minlength="6" autocomplete="new-password" required>
          <button type="button" class="toggle-pass" data-target="password">Show</button>
        </div>
        <div id="strength" data-level="0"></div>
      </div>
      <div class="field">
        <div class="role-pick">
          <label><input type="radio" name="role" value="buyer" <?php echo $role === 'buyer' ? 'checked' : ''; ?>><span>🛍️ I want to buy</span></label>
          <label><input type="radio" name="role" value="seller" <?php echo $role === 'seller' ? 'checked' : ''; ?>><span>💰 I want to sell</span></label>
        </div>
      </div>

      <p class="su-note">By creating an account, you agree to use CampusPick responsibly and to meet other students in safe campus spots.</p>
      <button type="submit" class="btn btn-green">Create account</button>
    </form>

    <p class="su-login"><a href="login.php">Already have an account?</a></p>
  </div>
</div>
<script src="js/auth.js"></script>
<?php include 'includes/footer.php'; ?>