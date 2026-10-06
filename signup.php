<?php
$pageTitle = 'Sign up';
$authPage = true;
include 'includes/header.php';
?>
<div class="su-page">
  <a href="index.php" class="su-logo">Campus<span>Pick</span></a>

  <div class="su-card">
    <h1>Create a new account</h1>
    <p class="sub">It's quick and easy.</p>
    <hr class="su-divider">

    <form method="POST" action="signup.php" class="auth-form">
      <div class="su-row">
        <div class="field">
          <input type="text" name="first_name" placeholder="First name" aria-label="First name" autocomplete="given-name" required>
        </div>
        <div class="field">
          <input type="text" name="last_name" placeholder="Last name" aria-label="Last name" autocomplete="family-name" required>
        </div>
      </div>
      <div class="field">
        <input type="email" name="email" placeholder="Email address" aria-label="Email address" autocomplete="email" required>
      </div>
      <div class="field">
        <input type="tel" name="phone" placeholder="WhatsApp number (2348012345678)" aria-label="WhatsApp number" autocomplete="tel" required>
      </div>
      <div class="field">
        <div class="pass-wrap">
          <input type="password" id="password" name="password" placeholder="New password (at least 6 characters)" aria-label="Password" minlength="6" autocomplete="new-password" required>
          <button type="button" class="toggle-pass" data-target="password">Show</button>
        </div>
        <div id="strength" data-level="0"></div>
      </div>
      <div class="field">
        <div class="role-pick">
          <label><input type="radio" name="role" value="buyer" checked><span>🛍️ I want to buy</span></label>
          <label><input type="radio" name="role" value="seller"><span>💰 I want to sell</span></label>
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



