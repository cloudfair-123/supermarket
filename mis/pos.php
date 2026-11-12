<?php
require 'includes/bootstrap.php'; require_login();
$prods = q('SELECT product_id id, sku, name, selling_price price, stock_qty stock FROM products WHERE active=1 ORDER BY name');
foreach ($prods as &$p) { $p['id'] = (int)$p['id']; $p['price'] = (float)$p['price']; $p['stock'] = (int)$p['stock']; }
render_head('Point of Sale'); ?>
<h1>Point of Sale</h1><p class="sub">Search or tap a product to add it to the cart.</p>
<div class="pos">
  <div class="card"><input id="search" placeholder="Search product name or SKU…" autofocus style="margin-bottom:10px"><div class="plist" id="plist"></div></div>
  <div class="card"><h2>Cart</h2>
    <table><tbody id="cart"></tbody></table><p id="empty" class="sub">Cart is empty.</p>
    <div class="total" id="total">UGX 0</div>
    <div class="form" style="margin-bottom:12px">
      <div><label>Payment method</label><select id="pay"><option>Cash</option><option>Mobile Money</option><option>Card</option></select></div>
      <div><label>Loyalty phone (optional)</label><input id="phone" placeholder="07xx xxx xxx"></div></div>
    <div id="msg" class="flash error" style="display:none"></div>
    <button id="go" style="width:100%">Complete sale</button>
    <button id="clear" type="button" class="btn sec" style="width:100%;margin-top:8px">Clear cart</button>
  </div>
</div>
<script>window.POS = <?= json_encode(['products' => $prods, 'checkoutUrl' => 'checkout.php', 'csrf' => csrf()]) ?>;</script>
<script src="assets/pos.js"></script>
<?php render_foot();
