// Show / hide password
document.querySelectorAll('.toggle-pass').forEach(function (btn) {
  btn.addEventListener('click', function () {
    var input = document.getElementById(btn.dataset.target);
    var show = input.type === 'password';
    input.type = show ? 'text' : 'password';
    btn.textContent = show ? 'Hide' : 'Show';
  });
});

// Password strength bar (signup only)
var pw = document.getElementById('password');
var bar = document.getElementById('strength');
if (pw && bar) {
  pw.addEventListener('input', function () {
    var v = pw.value, score = 0;
    if (v.length >= 6) score++;
    if (v.length >= 10) score++;
    if (/[A-Z]/.test(v) && /[a-z]/.test(v)) score++;
    if (/\d/.test(v)) score++;
    bar.dataset.level = v ? score : 0;
  });
}

// Feedback when the form is sent
document.querySelectorAll('form.auth-form').forEach(function (form) {
  form.addEventListener('submit', function () {
    var b = form.querySelector('button[type="submit"]');
    b.textContent = 'Please wait...';
    b.disabled = true;
  });
});