<?php
$pageTitle = 'Product';
include 'includes/db.php';
include 'includes/header.php';

$id = (int)($_GET['id'] ?? 0);

$stmt = $conn->prepare(
  "SELECT p.id, p.name, p.description, p.price, p.category, p.image,
          u.name AS seller_name, u.phone
   FROM products p
   JOIN users u ON p.seller_id = u.id
   WHERE p.id = ?"
);
$stmt->bind_param("i", $id);
$stmt->execute();
$p = $stmt->get_result()->fetch_assoc();
?>
<main class="container">
<?php if (!$p): ?>
  <p class="empty">Product not found. <a class="back" href="products.php">Back to products</a></p>
<?php else:
  $msg = "Hi, I want to pick '" . $p['name'] . "' on CampusPick.";
  $wa  = "https://wa.me/" . preg_replace('/\D/', '', $p['phone']) . "?text=" . urlencode($msg);
?>
  <a class="back" href="products.php">&larr; Back to products</a>
  <div class="detail">
    <div class="detail-img">
      <?php if ($p['image']): ?>
        <img src="uploads/<?php echo htmlspecialchars($p['image']); ?>" alt="">
      <?php else: ?>🛍️<?php endif; ?>
    </div>
    <div class="detail-info">
      <div class="card-cat"><?php echo htmlspecialchars($p['category']); ?></div>
      <h1><?php echo htmlspecialchars($p['name']); ?></h1>
      <div class="detail-price">₦<?php echo number_format($p['price']); ?></div>
      <p><?php echo nl2br(htmlspecialchars($p['description'])); ?></p>

      <div class="detail-seller">Sold by <strong><?php echo htmlspecialchars($p['seller_name']); ?></strong></div>

      <div class="btn-row">
        <button class="btn btn-green add-to-cart"
                data-id="<?php echo (int)$p['id']; ?>"
                data-name="<?php echo htmlspecialchars($p['name']); ?>"
                data-price="<?php echo (float)$p['price']; ?>">Pick it</button>
        <a class="btn" href="<?php echo $wa; ?>" target="_blank">Chat seller on WhatsApp</a>
      </div>
    </div>
  </div>
<?php endif; ?>
</main>
<?php include 'includes/footer.php'; ?>