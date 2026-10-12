<?php
include 'includes/db.php';
header('Content-Type: application/json');

function fail($msg, $code = 400) {
    http_response_code($code);
    echo json_encode(['ok' => false, 'error' => $msg]);
    exit;
}

if (!isset($_SESSION['user'])) fail('Please log in first.', 401);
if ($_SERVER['REQUEST_METHOD'] !== 'POST') fail('Invalid request.', 405);

$body = json_decode(file_get_contents('php://input'), true);
if (!is_array($body)) fail('Invalid request.');
if (!hash_equals($_SESSION['csrf'] ?? '', $body['csrf'] ?? '')) fail('Session expired. Refresh the page.', 403);

$cart = $body['cart'] ?? [];
if (!is_array($cart) || count($cart) === 0 || count($cart) > 30) fail('Your cart is empty.');

$printPrices = ['shirt' => 600000, 'mug' => 350000];   // in kobo
$lines = [];
$totalKobo = 0;
$find = $conn->prepare("SELECT id, seller_id, name, price FROM products WHERE id = ?");

foreach ($cart as $item) {
    $id  = (string)($item['id'] ?? '');
    $qty = (int)($item['qty'] ?? 0);
    if ($qty < 1 || $qty > 20) fail('Invalid quantity.');

    if (ctype_digit($id)) {
        $pid = (int)$id;
        $find->bind_param("i", $pid);
        $find->execute();
        $p = $find->get_result()->fetch_assoc();
        if (!$p) fail('A product in your cart is no longer available. Remove it and try again.');
        $kobo = (int)round($p['price'] * 100);
        $lines[] = ['product_id' => (int)$p['id'], 'seller_id' => (int)$p['seller_id'], 'name' => $p['name'], 'kobo' => $kobo, 'qty' => $qty];
    } elseif (preg_match('/^print-(shirt|mug)-\d+$/', $id, $m)) {
        $kobo = $printPrices[$m[1]];
        $name = mb_substr(trim(strip_tags((string)($item['name'] ?? 'Custom print'))), 0, 200);
        $lines[] = ['product_id' => null, 'seller_id' => null, 'name' => $name, 'kobo' => $kobo, 'qty' => $qty];
    } else {
        fail('Invalid item in cart.');
    }
    $totalKobo += $kobo * $qty;
}

$userId = (int)$_SESSION['user']['id'];
$stmt = $conn->prepare("SELECT email FROM users WHERE id = ?");
$stmt->bind_param("i", $userId);
$stmt->execute();
$u = $stmt->get_result()->fetch_assoc();
if (!$u) fail('Account not found.', 401);

$ref   = 'CP' . strtoupper(bin2hex(random_bytes(6)));
$total = $totalKobo / 100;

try {
    $conn->begin_transaction();

    $stmt = $conn->prepare("INSERT INTO orders (buyer_id, total, status, payment_ref) VALUES (?, ?, 'pending', ?)");
    $stmt->bind_param("ids", $userId, $total, $ref);
    $stmt->execute();
    $orderId = $conn->insert_id;

    $ins = $conn->prepare("INSERT INTO order_items (order_id, product_id, seller_id, name, price, qty) VALUES (?, ?, ?, ?, ?, ?)");
    foreach ($lines as $l) {
        $price = $l['kobo'] / 100;
        $ins->bind_param("iiisdi", $orderId, $l['product_id'], $l['seller_id'], $l['name'], $price, $l['qty']);
        $ins->execute();
    }

    $conn->commit();
} catch (Throwable $e) {
    $conn->rollback();
    fail('Could not create your order. Please try again.', 500);
}

echo json_encode(['ok' => true, 'ref' => $ref, 'amount' => $totalKobo, 'email' => $u['email']]);