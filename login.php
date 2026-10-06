<?php
$pageTitle = 'Login';
$authPage = true;
include 'includes/header.php';
?>
<div class="auth">
  <section class="auth-brand">
    <a href="index.php" class="logo">Campus<span>Pick</span></a>
    <h2>Your campus, <span>in your pocket.</span></h2>
    <p>Buy, sell and pick from fellow FUOYE students, all in one place.</p>
    <div class="floaters">
      <span class="floater">📚 Textbooks</span>
      <span class="floater">🛏️ Hostel Essentials</span>
      <span class="floater">👕 Print Shop</span>
      <span class="floater">🍛 Food</span>
    </div>
  </section>

  <section class="auth-side">
    <div class="auth-box">
      <a class="auth-back" href="index.php">&larr; Back to home</a>
      <h1>Welcome back</h1>
      <p class="sub">Log in to pick, buy and sell.</p>

      <form method="POST" action="login.php" class="auth-form">
        <div class="field">
          <label for="email">Email</label>
          <input type="email" id="email" name="email" placeholder="you@example.com" autocomplete="email" required>
        </div>
        <div class="field">
          <label for="password">Password</label>
          <div class="pass-wrap">
            <input type="password" id="password" name="password" autocomplete="current-password" required>
            <button type="button" class="toggle-pass" data-target="password">Show</button>
          </div>
        </div>
        <button type="submit" class="btn">Log in</button>
      </form>

      <p class="form-switch">New here? <a href="signup.php">Create an account</a></p>
    </div>
  </section>
</div>
<script src="js/auth.js"></script>
<?php include 'includes/footer.php'; ?>