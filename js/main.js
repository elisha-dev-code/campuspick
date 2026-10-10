var btn = document.getElementById('menu-btn');
var nav = document.getElementById('nav-links');
if (btn && nav) {
  btn.addEventListener('click', function () {
    var open = nav.classList.toggle('open');
    btn.setAttribute('aria-expanded', open);
    btn.textContent = open ? '✕' : '☰';
  });
}