<?php
$pageTitle = 'Find your match';
include 'includes/db.php';
include 'includes/header.php';

$res = $conn->query("SELECT id, name, price, category FROM products ORDER BY created_at DESC");
$products = $res->fetch_all(MYSQLI_ASSOC);
?>
<main class="container">
  <div class="quiz" id="quiz"></div>
</main>
<script>var PRODUCTS = <?php echo json_encode($products, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_QUOT); ?>;</script>
<script src="js/quiz.js"></script>
<?php include 'includes/footer.php'; ?>