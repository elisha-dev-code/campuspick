<?php
$pageTitle = 'Home';
include 'includes/db.php';
include 'includes/header.php';
?>
<main>
  <section class="hero">
    <div class="container">
      <h1>Pick it. Own it. <span>FUOYE.</span></h1>
      <p>Your campus, in your pocket. Buy and sell with students around you.</p>
      <a href="products.php" class="btn">Start picking</a>
    </div>
  </section>

  <section class="container">
    <h2 class="section-title">Shop by category</h2>
    <div class="chips">
      <a class="chip" href="products.php?cat=Hostel Essentials">Hostel Essentials</a>
      <a class="chip" href="products.php?cat=Textbooks">Textbooks</a>
      <a class="chip" href="products.php?cat=Gadgets">Gadgets</a>
      <a class="chip" href="products.php?cat=Fashion">Fashion</a>
      <a class="chip" href="products.php?cat=Food">Food</a>
      <a class="chip" href="products.php?cat=Print Shop">Print Shop</a>
    </div>
  </section>
</main>
<?php include 'includes/footer.php'; ?>