<?php
$pageTitle = 'Add a product';
include 'includes/db.php';

if (!isset($_SESSION['user'])) {
    header('Location: login.php?next=add_product.php');
    exit;
}
$user = $_SESSION['user'];
$isSeller = ($user['role'] === 'seller');

if (empty($_SESSION['csrf'])) {
    $_SESSION['csrf'] = bin2hex(random_bytes(16));
}

$cats = ['Hostel Essentials', 'Textbooks', 'Gadgets', 'Fashion', 'Food', 'Print Shop'];
$errors = [];
$name = $desc = $price = $cat = '';

if ($isSeller && $_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!hash_equals($_SESSION['csrf'], $_POST['csrf'] ?? '')) {
        $errors[] = 'Your session expired. Please try again.';
    }

    $name  = trim($_POST['name'] ?? '');
    $desc  = trim($_POST['description'] ?? '');
    $price = trim($_POST['price'] ?? '');
    $cat   = $_POST['category'] ?? '';

    if ($name === '' || mb_strlen($name) > 150) {
        $errors[] = 'Enter a product name (up to 150 characters).';
    }
    if (mb_strlen($desc) > 1000) {
        $errors[] = 'Description is too long (1000 characters maximum).';
    }
    if (!is_numeric($price) || $price < 1 || $price > 10000000) {
        $errors[] = 'Enter a valid price in naira.';
    }
    if (!in_array($cat, $cats, true)) {
        $errors[] = 'Please choose a category.';
    }

    /* Check the photo (optional) */
    $file = null;
    $ext = null;
    if (isset($_FILES['image']) && $_FILES['image']['error'] !== UPLOAD_ERR_NO_FILE) {
        $file = $_FILES['image'];
        if ($file['error'] !== UPLOAD_ERR_OK) {
            $errors[] = 'The photo could not be uploaded. Try another one.';
        } elseif ($file['size'] > 2 * 1024 * 1024) {
            $errors[] = 'The photo must be 2 MB or smaller.';
        } else {
            $info = @getimagesize($file['tmp_name']);
            $allowed = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp'];
            if (!$info || !isset($allowed[$info['mime']])) {
                $errors[] = 'Only JPG, PNG or WEBP photos are allowed.';
            } else {
                $ext = $allowed[$info['mime']];
            }
        }
    }

    if (!$errors) {
        $imageName = null;
        if ($file && $ext) {
            $imageName = bin2hex(random_bytes(8)) . '.' . $ext;
            if (!move_uploaded_file($file['tmp_name'], __DIR__ . '/uploads/' . $imageName)) {
                $errors[] = 'Could not save the photo. Check the uploads folder.';
            }
        }
    }

    if (!$errors) {
        $priceVal = (float)$price;
        $stmt = $conn->prepare(
            "INSERT INTO products (seller_id, name, description, price, category, image) VALUES (?, ?, ?, ?, ?, ?)"
        );
        $stmt->bind_param("issdss", $user['id'], $name, $desc, $priceVal, $cat, $imageName);
        $stmt->execute();
        header('Location: my_products.php?added=1');
        exit;
    }
}

include 'includes/header.php';
?>
<main class="container">
  <h2 class="section-title">Add a product</h2>

  <?php if (!$isSeller): ?>
    <div class="alert">Only seller accounts can list products. Sign up again and choose "I want to sell".</div>
  <?php else: ?>
    <div class="form-panel">
      <?php if ($errors): ?>
        <div class="alert" role="alert">
          <ul><?php foreach ($errors as $e): ?><li><?php echo htmlspecialchars($e); ?></li><?php endforeach; ?></ul>
        </div>
      <?php endif; ?>

      <form method="POST" enctype="multipart/form-data">
        <input type="hidden" name="csrf" value="<?php echo $_SESSION['csrf']; ?>">

        <div class="field">
          <label for="name">Product name</label>
          <input type="text" id="name" name="name" maxlength="150" value="<?php echo htmlspecialchars($name); ?>" required>
        </div>

        <div class="field">
          <label for="category">Category</label>
          <select id="category" name="category" required>
            <option value="">Choose one</option>
            <?php foreach ($cats as $c): ?>
              <option value="<?php echo htmlspecialchars($c); ?>" <?php echo $cat === $c ? 'selected' : ''; ?>><?php echo htmlspecialchars($c); ?></option>
            <?php endforeach; ?>
          </select>
        </div>

        <div class="field">
          <label for="price">Price (₦)</label>
          <input type="number" id="price" name="price" min="1" max="10000000" step="1" value="<?php echo htmlspecialchars($price); ?>" required>
        </div>

        <div class="field">
          <label for="description">Description</label>
          <textarea id="description" name="description" rows="4" maxlength="1000"><?php echo htmlspecialchars($desc); ?></textarea>
        </div>

        <div class="field">
          <label for="image">Photo (JPG, PNG or WEBP, up to 2 MB)</label>
          <input type="file" id="image" name="image" accept="image/jpeg,image/png,image/webp">
          <img id="img-preview" class="img-preview" alt="" hidden>
        </div>

        <button type="submit" class="btn btn-green">Publish product</button>
      </form>
    </div>
    <script>
      document.getElementById('image').addEventListener('change', function () {
        var p = document.getElementById('img-preview');
        if (this.files && this.files[0]) {
          p.src = URL.createObjectURL(this.files[0]);
          p.hidden = false;
        } else {
          p.hidden = true;
        }
      });
    </script>
  <?php endif; ?>
</main>
<?php include 'includes/footer.php'; ?>