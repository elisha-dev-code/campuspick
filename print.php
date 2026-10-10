<?php
$pageTitle = 'Custom Print';
include 'includes/header.php';
?>
<main class="container">
  <h2 class="section-title">Design your own</h2>

  <div class="print-layout">
    <div class="print-stage">
      <svg viewBox="0 0 300 300" id="preview" role="img" aria-label="Product preview">
        <g id="shirt">
          <path id="shirt-body" d="M95 30 L40 60 L10 120 L55 140 L75 115 L75 270 L225 270 L225 115 L245 140 L290 120 L260 60 L205 30 Q150 70 95 30 Z" fill="#ffffff" stroke="#c9c4b2" stroke-width="3"/>
        </g>
        <g id="mug" style="display:none">
          <rect id="mug-body" x="70" y="60" width="140" height="180" rx="14" fill="#ffffff" stroke="#c9c4b2" stroke-width="3"/>
          <path d="M210 100 H245 a32 32 0 0 1 0 90 H210" fill="none" stroke="#c9c4b2" stroke-width="14"/>
        </g>
        <text id="print-text" x="150" y="160" text-anchor="middle" fill="#0B5D3B" font-size="28" font-weight="700">Your text</text>
      </svg>
    </div>

    <div class="print-controls">
      <div class="field">
        <label>Product</label>
        <div class="role-pick">
          <label><input type="radio" name="ptype" value="shirt" checked><span>👕 T-shirt</span></label>
          <label><input type="radio" name="ptype" value="mug"><span>☕ Mug</span></label>
        </div>
      </div>

      <div class="field">
        <label for="ptext">Your text</label>
        <input type="text" id="ptext" maxlength="20" value="FUOYE Vibes" autocomplete="off">
      </div>

      <div class="print-row">
        <div class="field"><label for="pcolor">Text color</label><input type="color" id="pcolor" value="#0B5D3B"></div>
        <div class="field"><label for="bcolor">Product color</label><input type="color" id="bcolor" value="#ffffff"></div>
      </div>

      <div class="field">
        <label for="pfont">Style</label>
        <select id="pfont">
          <option value="Poppins, sans-serif">Modern</option>
          <option value="Georgia, serif">Classic</option>
          <option value="'Courier New', monospace">Typewriter</option>
          <option value="Impact, sans-serif">Bold</option>
        </select>
      </div>

      <div class="field">
        <label for="psize">Size</label>
        <input type="range" id="psize" min="14" max="48" value="28">
      </div>

      <div class="print-price">Price: <b id="pprice">₦6,000</b></div>
      <button class="btn btn-green" id="add-print" style="width:100%">Pick it</button>
    </div>
  </div>
</main>
<script src="js/print.js"></script>
<?php include 'includes/footer.php'; ?>