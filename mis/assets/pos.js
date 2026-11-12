// Point-of-sale cart logic. window.POS = {products, checkoutUrl, csrf}
(function () {
  var P = POS.products, cart = [];
  var $ = function (id) { return document.getElementById(id); };
  var fmt = function (n) { return 'UGX ' + Math.round(n).toLocaleString(); };
  function list() {
    var q = $('search').value.toLowerCase();
    $('plist').innerHTML = P.filter(function (p) { return p.name.toLowerCase().includes(q) || p.sku.toLowerCase().includes(q); }).map(function (p) {
      return '<div class="pitem ' + (p.stock <= 0 ? 'out' : '') + '" data-id="' + p.id + '"><span>' + p.name + '<br><small>' + p.sku + ' · ' + p.stock + ' in stock</small></span><b>' + fmt(p.price) + '</b></div>';
    }).join('');
  }
  function find(id) { return P.find(function (x) { return x.id == id; }); }
  function add(id) {
    var p = find(id), l = cart.find(function (x) { return x.id == id; });
    if (l) { if (l.qty < p.stock) l.qty++; } else cart.push({ id: id, qty: 1 });
    draw();
  }
  function chg(id, d) {
    var l = cart.find(function (x) { return x.id == id; }), p = find(id);
    l.qty = Math.min(p.stock, l.qty + d);
    if (l.qty <= 0) cart = cart.filter(function (x) { return x.id != id; });
    draw();
  }
  function draw() {
    var t = 0;
    $('cart').innerHTML = cart.map(function (l) {
      var p = find(l.id); t += p.price * l.qty;
      return '<tr><td>' + p.name + '</td><td><span class="qty"><button type="button" class="sm" data-chg="' + l.id + '" data-d="-1">−</button>' + l.qty +
             '<button type="button" class="sm" data-chg="' + l.id + '" data-d="1">+</button></span></td><td class="r">' + fmt(p.price * l.qty) + '</td></tr>';
    }).join('');
    $('empty').style.display = cart.length ? 'none' : 'block';
    $('total').textContent = fmt(t);
  }
  $('plist').addEventListener('click', function (e) { var d = e.target.closest('.pitem'); if (d) add(d.dataset.id); });
  $('cart').addEventListener('click', function (e) { var b = e.target.closest('[data-chg]'); if (b) chg(b.dataset.chg, +b.dataset.d); });
  $('clear').onclick = function () { cart = []; draw(); };
  $('search').oninput = list;
  $('go').onclick = function () {
    $('msg').style.display = 'none';
    if (!cart.length) return;
    fetch(POS.checkoutUrl, { method: 'POST', headers: { 'Content-Type': 'application/json', 'X-CSRF-Token': POS.csrf },
      body: JSON.stringify({ items: cart, payment: $('pay').value, phone: $('phone').value }) })
      .then(function (r) { return r.json().then(function (j) { return { ok: r.ok, j: j }; }); })
      .then(function (x) { if (x.ok) location = x.j.url; else { $('msg').textContent = x.j.error; $('msg').style.display = 'block'; } })
      .catch(function () { $('msg').textContent = 'Could not reach the server.'; $('msg').style.display = 'block'; });
  };
  list(); draw();
})();
