<?php
$pageTitle = 'My products';
include 'includes/db.php';

if (!isset($_SESSION['user'])) {
    header('Location: login.php?next=my_products.php');
    exit;
}
$user = $_SESSION['user'];
if ($user['role'] !== 'seller') {
    header('Location: products.php');
    exit;
}

if (empty($_SESSION['csrf'])) {
    $_SESSION['csrf'] = bin2hex(random_bytes(16));
}

/* Delete one of my own products */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_id'])) {
    if (hash_equals($_SESSION['csrf'], $_POST['csrf'] ?? '')) {
        $id = (int)$_POST['delete_id'];

        $stmt = $conn->prepare("SELECT image FROM products WHERE id = ? AND seller_id = ?");
        $stmt->bind_param("ii", $id, $user['id']);
        $stmt->execute();
        $row = $stmt->get_result()->fetch_assoc();

        if ($row) {
            $stmt = $conn->prepare("DELETE FROM products WHERE id = ? AND seller_id = ?");
            $stmt->bind_param("ii", $id, $user['id']);
            $stmt->execute();
            if ($row['image']) {
                @unlink(__DIR__ . '/uploads/' . basename($row['image']));
            }
        }
    }
    header('Location: my_products.php?deleted=1');
    exit;
}

$stmt = $conn->prepare("SELECT id, name, price, category, image FROM products WHERE seller_id = ? ORDER BY created_at DESC");
$stmt->bind_param("i", $user['id']);
$stmt->execute();
$result = $stmt->get_result();

include 'includes/header.php';
?>
<main class="container">
  <div class="my-head">
    <h2 class="section-title">My products</h2>
    <a href="add_product.php" class="btn">+ Add a product</a>
  </div>

  <?php if (isset($_GET['added'])): ?><div class="notice">Product published.</div><?php endif; ?>
  <?php if (isset($_GET['deleted'])): ?><div class="notice">Product deleted.</div><?php endif; ?>

  <?php if ($result->num_rows === 0): ?>
    <p class="empty">You have not listed anything yet. Add your first product.</p>
  <?php endif; ?>

  <?php while ($p = $result->fetch_assoc()): ?>
    <div class="my-row">
      <div class="my-thumb">
        <?php if ($p['image']): ?>
          <img src="uploads/<?php echo htmlspecialchars($p['image']); ?>" alt="">
        <?php else: ?>🛍️<?php endif; ?>
      </div>
      <div class="my-info">
        <a href="product.php?id=<?php echo (int)$p['id']; ?>"><strong><?php echo htmlspecialchars($p['name']); ?></strong></a>
        <div class="card-cat"><?php echo htmlspecialchars($p['category']); ?></div>
        <div class="card-price">₦<?php echo number_format($p['price']); ?></div>
      </div>
      <form method="POST" onsubmit="return confirm('Delete this product?');">
        <input type="hidden" name="csrf" value="<?php echo $_SESSION['csrf']; ?>">
        <input type="hidden" name="delete_id" value="<?php echo (int)$p['id']; ?>">
        <button type="submit" class="remove-btn">Delete</button>
      </form>
    </div>
  <?php endwhile; ?>
</main>
<?php include 'includes/footer.php'; ?>