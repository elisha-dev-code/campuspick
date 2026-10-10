var CART_KEY = 'campuspick_cart';

/* ---------- Reading and saving ---------- */
function getCart() {
  try {
    return JSON.parse(localStorage.getItem(CART_KEY)) || [];
  } catch (e) {
    return [];
  }
}

function saveCart(cart) {
  localStorage.setItem(CART_KEY, JSON.stringify(cart));
}

/* ---------- Helpers ---------- */
function formatNaira(amount) {
  return '₦' + Number(amount).toLocaleString('en-NG');
}

function escapeHtml(text) {
  var d = document.createElement('div');
  d.textContent = text;
  return d.innerHTML;
}

function cartCount() {
  return getCart().reduce(function (sum, item) { return sum + item.qty; }, 0);
}

function cartTotal() {
  return getCart().reduce(function (sum, item) { return sum + item.price * item.qty; }, 0);
}

/* ---------- Changing the cart ---------- */
function addToCart(item) {
  var cart = getCart();
  var found = cart.find(function (c) { return c.id === item.id; });
  if (found) {
    found.qty += 1;
  } else {
    cart.push({ id: item.id, name: item.name, price: item.price, qty: 1 });
  }
  saveCart(cart);
}

function changeQty(id, change) {
  var cart = getCart();
  var item = cart.find(function (c) { return c.id === id; });
  if (!item) return;
  item.qty += change;
  if (item.qty <= 0) {
    cart = cart.filter(function (c) { return c.id !== id; });
  }
  saveCart(cart);
}

function removeItem(id) {
  saveCart(getCart().filter(function (c) { return c.id !== id; }));
}

/* ---------- Showing things on the page ---------- */
function updateBadge(bump) {
  var badge = document.getElementById('cart-count');
  if (!badge) return;
  badge.textContent = cartCount();
  if (bump) {
    badge.classList.remove('bump');
    void badge.offsetWidth; /* restarts the animation */
    badge.classList.add('bump');
  }
}

function showToast(message) {
  var toast = document.getElementById('toast');
  if (!toast) {
    toast = document.createElement('div');
    toast.id = 'toast';
    toast.className = 'toast';
    toast.setAttribute('role', 'status');
    document.body.appendChild(toast);
  }
  toast.textContent = message;
  toast.classList.add('show');
  clearTimeout(showToast.timer);
  showToast.timer = setTimeout(function () { toast.classList.remove('show'); }, 1800);
}

function renderCart() {
  var box = document.getElementById('cart-items');
  if (!box) return; /* not on the cart page */

  var cart = getCart();
  if (cart.length === 0) {
    box.innerHTML =
      '<div class="cart-empty"><div class="big">🛒</div>' +
      '<p>Nothing here yet. Go pick something.</p>' +
      '<a href="products.php" class="btn" style="margin-top:14px">Browse products</a></div>';
  } else {
    box.innerHTML = cart.map(function (item) {
      return '<div class="cart-item">' +
        '<div class="cart-thumb">🛍️</div>' +
        '<div class="cart-info">' +
          '<div class="name">' + escapeHtml(item.name) + '</div>' +
          '<div class="price">' + formatNaira(item.price) + '</div>' +
          '<button class="remove-btn" data-action="remove" data-id="' + escapeHtml(item.id) + '">Remove</button>' +
        '</div>' +
        '<div class="qty">' +
          '<button data-action="dec" data-id="' + escapeHtml(item.id) + '" aria-label="Decrease quantity">−</button>' +
          '<span>' + item.qty + '</span>' +
          '<button data-action="inc" data-id="' + escapeHtml(item.id) + '" aria-label="Increase quantity">+</button>' +
        '</div>' +
      '</div>';
    }).join('');
  }

  document.getElementById('cart-qty').textContent = cartCount();
  document.getElementById('cart-total').textContent = formatNaira(cartTotal());
}

/* ---------- Listening for taps ---------- */
document.addEventListener('click', function (e) {
  var addBtn = e.target.closest('.add-to-cart');
  if (addBtn) {
    addToCart({
      id: String(addBtn.dataset.id),
      name: addBtn.dataset.name,
      price: parseFloat(addBtn.dataset.price)
    });
    updateBadge(true);
    showToast('Picked! Added to your cart.');
    return;
  }

  var actionBtn = e.target.closest('[data-action]');
  if (actionBtn) {
    var id = actionBtn.dataset.id;
    var action = actionBtn.dataset.action;
    if (action === 'inc') changeQty(id, 1);
    if (action === 'dec') changeQty(id, -1);
    if (action === 'remove') removeItem(id);
    updateBadge(false);
    renderCart();
  }
});

/* ---------- Run when the page loads ---------- */
updateBadge(false);
renderCart();