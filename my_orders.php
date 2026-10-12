<?php
$pageTitle = 'My orders';
include 'includes/db.php';
require 'includes/config.php';

if (!isset($_SESSION['user'])) {
    header('Location: login.php?next=my_orders.php');
    exit;
}
$userId = (int)$_SESSION['user']['id'];

if (empty($_SESSION['csrf'])) {
    $_SESSION['csrf'] = bin2hex(random_bytes(16));
}

/* Orders left unpaid for 24 hours are marked cancelled */
$exp = $conn->prepare("UPDATE orders SET status = 'cancelled' WHERE buyer_id = ? AND status = 'pending' AND created_at < (NOW() - INTERVAL 1 DAY)");
$exp->bind_param("i", $userId);
$exp->execute();

$stmt = $conn->prepare("SELECT id, total, status, payment_ref, created_at FROM orders WHERE buyer_id = ? ORDER BY created_at DESC");
$stmt->bind_param("i", $userId);
$stmt->execute();
$orders = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

$itemStmt = $conn->prepare("SELECT name, price, qty FROM order_items WHERE order_id = ?");

include 'includes/header.php';
?>
<main class="container">
  <h2 class="section-title">My orders</h2>

  <?php if (!$orders): ?>
    <p class="empty">No orders yet. <a href="products.php"><b>Go pick something.</b></a></p>
  <?php endif; ?>

  <?php foreach ($orders as $o):
    $itemStmt->bind_param("i", $o['id']);
    $itemStmt->execute();
    $items = $itemStmt->get_result()->fetch_all(MYSQLI_ASSOC);
  ?>
    <div class="co-box" style="margin-bottom:14px">
      <div class="co-line">
        <span><b><?php echo htmlspecialchars($o['payment_ref']); ?></b><br><small><?php echo htmlspecialchars($o['created_at']); ?></small></span>
        <span class="status status-<?php echo htmlspecialchars($o['status']); ?>"><?php echo htmlspecialchars($o['status']); ?></span>
      </div>

      <?php foreach ($items as $i): ?>
        <div class="co-line"><span><?php echo htmlspecialchars($i['name']); ?> × <?php echo (int)$i['qty']; ?></span><span>₦<?php echo number_format($i['price'] * $i['qty']); ?></span></div>
      <?php endforeach; ?>

      <div class="co-line co-total"><span>Total</span><b>₦<?php echo number_format($o['total']); ?></b></div>

      <?php if ($o['status'] === 'pending'): ?>
        <button class="btn btn-green pay-order" style="width:100%" data-order="<?php echo (int)$o['id']; ?>">Pay now</button>
      <?php endif; ?>
    </div>
  <?php endforeach; ?>
</main>

<script src="https://js.paystack.co/v1/inline.js"></script>
<script>
var CSRF = <?php echo json_encode($_SESSION['csrf']); ?>;
var PAYSTACK_KEY = <?php echo json_encode(PAYSTACK_PUBLIC_KEY); ?>;

window.addEventListener('load', function () {
  document.addEventListener('click', function (e) {
    var btn = e.target.closest('.pay-order');
    if (!btn) return;

    btn.disabled = true;
    btn.textContent = 'Please wait...';

    fetch('pay_order.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ csrf: CSRF, order_id: parseInt(btn.dataset.order, 10) })
    })
    .then(function (r) { return r.json(); })
    .then(function (d) {
      if (!d.ok) { throw new Error(d.error || 'Could not start payment.'); }
      if (d.paid) { window.location = 'my_orders.php'; return; }

      var handler = PaystackPop.setup({
        key: PAYSTACK_KEY,
        email: d.email,
        amount: d.amount,
        currency: 'NGN',
        ref: d.ref,
        callback: function (response) {
          window.location = 'verify_payment.php?ref=' + encodeURIComponent(response.reference);
        },
        onClose: function () {
          btn.disabled = false;
          btn.textContent = 'Pay now';
          showToast('Payment window closed. You can pay any time.');
        }
      });
      handler.openIframe();
    })
    .catch(function (err) {
      btn.disabled = false;
      btn.textContent = 'Pay now';
      showToast(err.message);
    });
  });
});
</script>
<?php include 'includes/footer.php'; ?>