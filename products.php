<?php
$pageTitle = 'Products';
include 'includes/db.php';
include 'includes/header.php';

$cat = $_GET['cat'] ?? '';

if ($cat !== '') {
    $stmt = $conn->prepare("SELECT id, name, price, category, image FROM products WHERE category = ? ORDER BY created_at DESC");
    $stmt->bind_param("s", $cat);
} else {
    $stmt = $conn->prepare("SELECT id, name, price, category, image FROM products ORDER BY created_at DESC");
}
$stmt->execute();
$result = $stmt->get_result();

$cats = ['Hostel Essentials', 'Textbooks', 'Gadgets', 'Fashion', 'Food', 'Print Shop'];
?>
<main class="container">
  <h2 class="section-title">Products</h2>

  <div class="chips">
    <a class="chip <?php echo $cat === '' ? 'active' : ''; ?>" href="products.php">All</a>
    <?php foreach ($cats as $c): ?>
      <a class="chip <?php echo $cat === $c ? 'active' : ''; ?>"
         href="products.php?cat=<?php echo urlencode($c); ?>"><?php echo htmlspecialchars($c); ?></a>
    <?php endforeach; ?>
  </div>

  <div class="grid">
    <?php if ($result->num_rows === 0): ?>
      <p class="empty">Nothing here yet. Go pick something else.</p>
    <?php endif; ?>

    <?php while ($row = $result->fetch_assoc()): ?>
      <a class="card" href="product.php?id=<?php echo (int)$row['id']; ?>">
        <div class="card-img">
          <?php if ($row['image']): ?>
            <img src="uploads/<?php echo htmlspecialchars($row['image']); ?>" alt="">
          <?php else: ?>🛍️<?php endif; ?>
        </div>
        <div class="card-body">
          <div class="card-cat"><?php echo htmlspecialchars($row['category']); ?></div>
          <div class="card-name"><?php echo htmlspecialchars($row['name']); ?></div>
          <div class="card-price">₦<?php echo number_format($row['price']); ?></div>
        </div>
      </a>
    <?php endwhile; ?>
  </div>
</main>
<?php include 'includes/footer.php'; ?>