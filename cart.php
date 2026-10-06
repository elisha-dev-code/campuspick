<?php
$pageTitle = 'Cart';
include 'includes/header.php';
?>
<main class="container">
  <h2 class="section-title">Your cart</h2>

  <div class="cart-layout">
    <div class="cart-list" id="cart-items">
      <div class="cart-empty">
        <div class="big">🛒</div>
        <p>Nothing here yet. Go pick something.</p>
        <a href="products.php" class="btn" style="margin-top:14px">Browse products</a>
      </div>
    </div>

    <aside class="cart-summary">
      <h3>Order summary</h3>
      <div class="sum-row"><span>Items</span><span id="cart-qty">0</span></div>
      <div class="sum-row sum-total"><span>Total</span><span id="cart-total">₦0</span></div>
      <a href="checkout.php" class="btn">Checkout</a>
    </aside>
  </div>
</main>
<?php include 'includes/footer.php'; ?>