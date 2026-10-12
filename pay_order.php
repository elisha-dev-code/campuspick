<?php
include 'includes/db.php';
require 'includes/config.php';
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

$userId  = (int)$_SESSION['user']['id'];
$orderId = (int)($body['order_id'] ?? 0);

$stmt = $conn->prepare("SELECT id, total, status, payment_ref FROM orders WHERE id = ? AND buyer_id = ?");
$stmt->bind_param("ii", $orderId, $userId);
$stmt->execute();
$order = $stmt->get_result()->fetch_assoc();

if (!$order) fail('Order not found.', 404);
if ($order['status'] === 'paid') { echo json_encode(['ok' => true, 'paid' => true]); exit; }
if ($order['status'] !== 'pending') fail('This order can no longer be paid. Please place a new order.');

$expected = (int)round($order['total'] * 100);

/* 1. Was the current reference already paid? */
$ch = curl_init('https://api.paystack.co/transaction/verify/' . rawurlencode($order['payment_ref']));
curl_setopt_array($ch, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_HTTPHEADER     => ['Authorization: Bearer ' . PAYSTACK_SECRET_KEY],
    CURLOPT_TIMEOUT        => 20,
    CURLOPT_SSL_VERIFYPEER => PAYSTACK_VERIFY_SSL,
]);
$raw = curl_exec($ch);
curl_close($ch);

if ($raw === false) fail('Could not reach Paystack. Check your internet and try again.', 502);

$res = json_decode($raw, true);
if (($res['status'] ?? false) === true
    && ($res['data']['status'] ?? '') === 'success'
    && (int)($res['data']['amount'] ?? 0) === $expected
    && ($res['data']['currency'] ?? '') === 'NGN') {
    $up = $conn->prepare("UPDATE orders SET status = 'paid' WHERE id = ? AND status = 'pending'");
    $up->bind_param("i", $orderId);
    $up->execute();
    echo json_encode(['ok' => true, 'paid' => true]);
    exit;
}

/* 2. Not paid: give the order a fresh reference */
$newRef = 'CP' . strtoupper(bin2hex(random_bytes(6)));
$up = $conn->prepare("UPDATE orders SET payment_ref = ? WHERE id = ? AND buyer_id = ? AND status = 'pending'");
$up->bind_param("sii", $newRef, $orderId, $userId);
$up->execute();

$u = $conn->prepare("SELECT email FROM users WHERE id = ?");
$u->bind_param("i", $userId);
$u->execute();
$email = $u->get_result()->fetch_assoc()['email'] ?? '';

echo json_encode(['ok' => true, 'paid' => false, 'ref' => $newRef, 'amount' => $expected, 'email' => $email]);