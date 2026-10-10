var QUESTIONS = [
  { text: 'What do you need most right now?', options: [
    { label: '📚 Study materials', cats: ['Textbooks'] },
    { label: '🛏️ Hostel life', cats: ['Hostel Essentials'] },
    { label: '🔌 Tech and gadgets', cats: ['Gadgets'] },
    { label: '👗 Style', cats: ['Fashion'] },
    { label: '🍛 Food', cats: ['Food'] },
    { label: '🎨 Something custom', cats: ['Print Shop'] }
  ] },
  { text: 'What is your budget?', options: [
    { label: 'Under ₦5,000', min: 0, max: 5000 },
    { label: '₦5,000 to ₦10,000', min: 5000, max: 10000 },
    { label: 'Above ₦10,000', min: 10000, max: 1000000000 }
  ] },
  { text: 'Which sounds most like you?', options: [
    { label: '📖 The bookworm', cats: ['Textbooks', 'Gadgets'] },
    { label: '😎 The trendsetter', cats: ['Fashion', 'Print Shop'] },
    { label: '🏠 The homebody', cats: ['Hostel Essentials', 'Food'] },
    { label: '⚡ Always on the go', cats: ['Gadgets', 'Food'] }
  ] }
];

var step = 0;
var answers = [];
var box;

function escapeAttr(text) {
  return String(text).replace(/&/g, '&amp;').replace(/"/g, '&quot;')
    .replace(/</g, '&lt;').replace(/>/g, '&gt;');
}

function showQuestion() {
  var q = QUESTIONS[step];
  var percent = (step / QUESTIONS.length) * 100;
  box.innerHTML =
    '<div class="quiz-progress"><span style="width:' + percent + '%"></span></div>' +
    '<p class="quiz-step">Question ' + (step + 1) + ' of ' + QUESTIONS.length + '</p>' +
    '<h2>' + q.text + '</h2>' +
    '<div class="quiz-options">' +
    q.options.map(function (o, i) {
      return '<button class="quiz-opt" data-i="' + i + '">' + o.label + '</button>';
    }).join('') +
    '</div>';
}

function scoreProduct(p) {
  var need = answers[0], budget = answers[1], vibe = answers[2];
  var points = 0;
  if (need.cats.indexOf(p.category) !== -1) points += 3;
  if (vibe.cats.indexOf(p.category) !== -1) points += 1;
  var price = parseFloat(p.price);
  if (price >= budget.min && price <= budget.max) points += 2;
  return points;
}

function showResults() {
  var picks = PRODUCTS
    .map(function (p) { return { product: p, score: scoreProduct(p) }; })
    .filter(function (r) { return r.score > 0; })
    .sort(function (a, b) { return b.score - a.score; })
    .slice(0, 3);

  var html = '<div class="quiz-progress"><span style="width:100%"></span></div>';
  if (picks.length === 0) {
    html += '<h2>No match yet</h2><p>Try different answers, or browse everything.</p>';
  } else {
    html += '<h2>Your top picks ✨</h2><div class="quiz-results">' +
      picks.map(function (r) {
        var p = r.product;
        return '<div class="card"><div class="card-body">' +
          '<div class="card-cat">' + escapeAttr(p.category) + '</div>' +
          '<div class="card-name">' + escapeAttr(p.name) + '</div>' +
          '<div class="card-price">' + formatNaira(p.price) + '</div>' +
          '<div class="btn-row" style="margin-top:10px">' +
            '<a class="btn" href="product.php?id=' + parseInt(p.id, 10) + '">View</a>' +
            '<button class="btn btn-green add-to-cart" data-id="' + parseInt(p.id, 10) + '" ' +
              'data-name="' + escapeAttr(p.name) + '" data-price="' + parseFloat(p.price) + '">Pick it</button>' +
          '</div></div></div>';
      }).join('') + '</div>';
  }
  html += '<p style="margin-top:18px"><button class="btn" data-restart="1">Start again</button> ' +
          '<a class="btn btn-green" href="products.php">Browse all</a></p>';
  box.innerHTML = html;
}

window.addEventListener('load', function () {
  box = document.getElementById('quiz');
  showQuestion();

  box.addEventListener('click', function (e) {
    var opt = e.target.closest('.quiz-opt');
    if (opt) {
      answers.push(QUESTIONS[step].options[parseInt(opt.dataset.i, 10)]);
      step++;
      if (step < QUESTIONS.length) { showQuestion(); } else { showResults(); }
      return;
    }
    if (e.target.closest('[data-restart]')) {
      step = 0;
      answers = [];
      showQuestion();
    }
  });
});