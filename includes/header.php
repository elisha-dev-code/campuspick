<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
$user = $_SESSION['user'] ?? null;
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title><?php echo $pageTitle ?? 'CampusPick'; ?> | CampusPick</title>
  <link rel="stylesheet" href="css/style.css">
</head>
<body>
<?php if (empty($authPage)): ?>
<header class="site-header">
  <div class="container nav">
    <a href="index.php" class="logo">Campus<span>Pick</span></a>
    <button class="menu-btn" id="menu-btn" aria-label="Open menu" aria-expanded="false">☰</button>
    <nav class="nav-links" id="nav-links">
      <a href="index.php">Home</a>
      <a href="products.php">Products</a>
      <a href="print.php">Custom Print</a>
      <a href="quiz.php">Find your match</a>
      <a href="cart.php">Cart <span class="cart-badge" id="cart-count">0</span></a>
      <?php if ($user): ?>
        <a href="logout.php">Log out (<?php echo htmlspecialchars(explode(' ', $user['name'])[0]); ?>)</a>
      <?php else: ?>
        <a href="login.php">Login</a>
      <?php endif; ?>
    </nav>
  </div>
</header>
<?php endif; ?>