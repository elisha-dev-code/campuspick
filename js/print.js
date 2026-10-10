var PRINT_PRICES = { shirt: 6000, mug: 3500 };
var PRINT_NAMES = { shirt: 'Custom T-shirt', mug: 'Custom mug' };

function currentType() {
  return document.querySelector('input[name="ptype"]:checked').value;
}

function updatePreview() {
  var type = currentType();
  var text = document.getElementById('ptext').value || ' ';
  var label = document.getElementById('print-text');

  document.getElementById('shirt').style.display = type === 'shirt' ? 'block' : 'none';
  document.getElementById('mug').style.display = type === 'mug' ? 'block' : 'none';

  document.getElementById('shirt-body').setAttribute('fill', document.getElementById('bcolor').value);
  document.getElementById('mug-body').setAttribute('fill', document.getElementById('bcolor').value);

  label.textContent = text;
  label.setAttribute('fill', document.getElementById('pcolor').value);
  label.style.fontFamily = document.getElementById('pfont').value;
  label.setAttribute('font-size', document.getElementById('psize').value);
  label.setAttribute('y', type === 'shirt' ? 160 : 160);

  document.getElementById('pprice').textContent = formatNaira(PRINT_PRICES[type]);
}

['ptext', 'pcolor', 'bcolor', 'pfont', 'psize'].forEach(function (id) {
  document.getElementById(id).addEventListener('input', updatePreview);
});
document.querySelectorAll('input[name="ptype"]').forEach(function (r) {
  r.addEventListener('change', updatePreview);
});

document.getElementById('add-print').addEventListener('click', function () {
  var type = currentType();
  var text = document.getElementById('ptext').value.trim();
  if (text === '') {
    showToast('Type something to print first.');
    return;
  }
  addToCart({
    id: 'print-' + type + '-' + Date.now(),
    name: PRINT_NAMES[type] + ': "' + text + '"',
    price: PRINT_PRICES[type]
  });
  updateBadge(true);
  showToast('Picked! Your design is in the cart.');
});

updatePreview();