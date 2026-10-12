<?php
$pageTitle = 'Payment';
include 'includes/db.php';
require 'includes/config.php';

if (!isset($_SESSION['user'])) {
    header('Location: login.php');
    exit;
}
$userId = (int)$_SESSION['user']['id'];
$ref = $_GET['ref'] ?? '';
$paid = false;
$message = '';
$order = null;

if (!preg_match('/^CP[A-F0-9]{12}$/', $ref)) {
    $message = 'Invalid payment reference.';
} else {
    $stmt = $conn->prepare("SELECT id, total, status FROM orders WHERE payment_ref = ? AND buyer_id = ?");
    $stmt->bind_param("si", $ref, $userId);
    $stmt->execute();
    $order = $stmt->get_result()->fetch_assoc();

    if (!$order) {
        $message = 'Order not found.';
    } elseif ($order['status'] === 'paid') {
        $paid = true;
    } else {
        $ch = curl_init('https://api.paystack.co/transaction/verify/' . rawurlencode($ref));
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER     => ['Authorization: Bearer ' . PAYSTACK_SECRET_KEY],
            CURLOPT_TIMEOUT        => 20,
            CURLOPT_SSL_VERIFYPEER => PAYSTACK_VERIFY_SSL,
        ]);
        $raw = curl_exec($ch);
        $err = curl_error($ch);
        curl_close($ch);

        if ($raw === false) {
            $message = 'Could not reach Paystack: ' . $err;
        } else {
            $res = json_decode($raw, true);
            $expected = (int)round($order['total'] * 100);

            if (($res['status'] ?? false) === true
                && ($res['data']['status'] ?? '') === 'success'
                && (int)($res['data']['amount'] ?? 0) === $expected
                && ($res['data']['currency'] ?? '') === 'NGN') {

                $up = $conn->prepare("UPDATE orders SET status = 'paid' WHERE id = ? AND status = 'pending'");
                $up->bind_param("i", $order['id']);
                $up->execute();
                $paid = true;
            } else {
                $message = 'We could not confirm your payment yet. If money left your account, contact support with reference ' . $ref . '.';
            }
        }
    }
}

$items = [];
if ($paid) {
    $stmt = $conn->prepare("SELECT oi.name, oi.price, oi.qty, u.phone FROM order_items oi LEFT JOIN users u ON oi.seller_id = u.id WHERE oi.order_id = ?");
    $stmt->bind_param("i", $order['id']);
    $stmt->execute();
    $items = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
}

include 'includes/header.php';
?>
<main class="container">
  <div class="success-card">
  <?php if ($paid): ?>
    <div class="big">🎉</div>
    <h2>Picked! Payment confirmed.</h2>
    <p>Order reference: <b><?php echo htmlspecialchars($ref); ?></b></p>

    <div class="co-box" style="text-align:left">
      <?php foreach ($items as $i): ?>
        <div class="co-line">
          <span><?php echo htmlspecialchars($i['name']); ?> × <?php echo (int)$i['qty']; ?></span>
          <b>₦<?php echo number_format($i['price'] * $i['qty']); ?></b>
        </div>
        <?php if ($i['phone']):
          $text = 'Hi, I just paid for "' . $i['name'] . '" (order ' . $ref . ') on CampusPick. Where can we meet on campus?';
          $wa = 'https://wa.me/' . preg_replace('/\D/', '', $i['phone']) . '?text=' . urlencode($text);
        ?>
          <a class="btn" style="margin:4px 0 10px" target="_blank" href="<?php echo $wa; ?>">Message the seller on WhatsApp</a>
        <?php endif; ?>
      <?php endforeach; ?>
      <div class="co-line co-total"><span>Total paid</span><b>₦<?php echo number_format($order['total']); ?></b></div>
    </div>

    <p style="margin-top:16px"><a class="btn btn-green" href="my_orders.php">View my orders</a> <a class="btn" href="products.php">Keep shopping</a></p>
    <script>
      window.addEventListener('load', function () { saveCart([]); updateBadge(false); });
    </script>
  <?php else: ?>
    <div class="big">⚠️</div>
    <h2>Payment not confirmed</h2>
    <p><?php echo htmlspecialchars($message); ?></p>
    <p style="margin-top:16px"><a class="btn" href="cart.php">Back to cart</a></p>
  <?php endif; ?>
  </div>
</main>
<?php include 'includes/footer.php'; ?>