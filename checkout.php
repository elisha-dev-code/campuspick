<?php
$pageTitle = 'Checkout';
include 'includes/db.php';
require 'includes/config.php';

if (!isset($_SESSION['user'])) {
    header('Location: login.php?next=checkout.php');
    exit;
}
if (empty($_SESSION['csrf'])) {
    $_SESSION['csrf'] = bin2hex(random_bytes(16));
}
include 'includes/header.php';
?>
<main class="container">
  <h2 class="section-title">Checkout</h2>

  <div class="co-box">
    <div id="co-items"></div>
    <div class="co-line co-total"><span>Total</span><b id="co-total">₦0</b></div>
    <p class="co-note">Test mode: no real money is charged. The final amount is confirmed by our server.</p>
    <button class="btn btn-green" id="pay-btn" style="width:100%">Pay with Paystack</button>
    <p class="co-note">Test card: 4084 0840 8408 4081, any future expiry date, CVV 408. If Paystack shows other steps, follow them. Test details are in Paystack's documentation.</p>
  </div>
</main>

<script src="https://js.paystack.co/v1/inline.js"></script>
<script>
var CSRF = <?php echo json_encode($_SESSION['csrf']); ?>;
var PAYSTACK_KEY = <?php echo json_encode(PAYSTACK_PUBLIC_KEY); ?>;

function renderCheckout() {
  var cart = getCart();
  var list = document.getElementById('co-items');
  var btn = document.getElementById('pay-btn');
  if (cart.length === 0) {
    list.innerHTML = '<p class="empty">Your cart is empty. <a href="products.php"><b>Browse products</b></a></p>';
    btn.disabled = true;
  } else {
    list.innerHTML = cart.map(function (i) {
      return '<div class="co-line"><span>' + escapeHtml(i.name) + ' × ' + i.qty + '</span><b>' + formatNaira(i.price * i.qty) + '</b></div>';
    }).join('');
  }
  document.getElementById('co-total').textContent = formatNaira(cartTotal());
}

function resetPayButton() {
  var btn = document.getElementById('pay-btn');
  btn.disabled = getCart().length === 0;
  btn.textContent = 'Pay with Paystack';
}

window.addEventListener('load', function () {
  renderCheckout();

  document.getElementById('pay-btn').addEventListener('click', function () {
    var btn = this;
    btn.disabled = true;
    btn.textContent = 'Please wait...';

    var items = getCart().map(function (i) { return { id: i.id, name: i.name, qty: i.qty }; });

    fetch('create_order.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ csrf: CSRF, cart: items })
    })
    .then(function (r) { return r.json(); })
    .then(function (d) {
      if (!d.ok) { throw new Error(d.error || 'Could not start payment.'); }
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
          resetPayButton();
          showToast('Payment window closed. Your cart is safe.');
        }
      });
      handler.openIframe();
    })
    .catch(function (err) {
      resetPayButton();
      showToast(err.message);
    });
  });
});
</script>
<?php include 'includes/footer.php'; ?>