<?php
require 'includes/bootstrap.php'; require_login(MGR);
$id = (int)($_GET['id'] ?? 0); $p = $id ? q1('SELECT * FROM products WHERE product_id=?', [$id]) : null;
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $v = [trim($_POST['sku']), trim($_POST['name']), (int)$_POST['category_id'], (int)$_POST['supplier_id'], (float)$_POST['cost_price'], (float)$_POST['selling_price'], (int)$_POST['reorder_level'], $_POST['expiry_date'] ?: null];
    try {
        if ($p) run('UPDATE products SET sku=?,name=?,category_id=?,supplier_id=?,cost_price=?,selling_price=?,reorder_level=?,expiry_date=? WHERE product_id=?', array_merge($v, [$id]));
        else run('INSERT INTO products(sku,name,category_id,supplier_id,cost_price,selling_price,reorder_level,expiry_date,stock_qty) VALUES(?,?,?,?,?,?,?,?,?)', array_merge($v, [max(0, (int)($_POST['stock_qty'] ?? 0))]));
        flash('Product saved.'); redirect('products.php');
    } catch (PDOException $ex) { flash('That SKU already exists or a value is invalid.', 'error'); }
}
$cats = q('SELECT * FROM categories ORDER BY name'); $sups = q('SELECT * FROM suppliers ORDER BY name');
render_head($p ? 'Edit product' : 'Add product'); ?>
<h1><?= $p ? 'Edit' : 'Add' ?> product</h1><p class="sub">Prices in UGX</p>
<form class="card" method="post"><?= csrf_field() ?><div class="form">
<div><label>SKU</label><input name="sku" value="<?= e($p['sku'] ?? '') ?>" required></div>
<div><label>Product name</label><input name="name" value="<?= e($p['name'] ?? '') ?>" required></div>
<div><label>Category</label><select name="category_id"><?php foreach ($cats as $c) echo '<option value="' . $c['category_id'] . '"' . (($p['category_id'] ?? 0) == $c['category_id'] ? ' selected' : '') . '>' . e($c['name']) . '</option>'; ?></select></div>
<div><label>Supplier</label><select name="supplier_id"><?php foreach ($sups as $s) echo '<option value="' . $s['supplier_id'] . '"' . (($p['supplier_id'] ?? 0) == $s['supplier_id'] ? ' selected' : '') . '>' . e($s['name']) . '</option>'; ?></select></div>
<div><label>Cost price</label><input type="number" min="0" step="any" name="cost_price" value="<?= e($p['cost_price'] ?? '') ?>" required></div>
<div><label>Selling price</label><input type="number" min="0" step="any" name="selling_price" value="<?= e($p['selling_price'] ?? '') ?>" required></div>
<div><label>Reorder level</label><input type="number" min="0" name="reorder_level" value="<?= e($p['reorder_level'] ?? 10) ?>" required></div>
<div><label>Expiry date (optional)</label><input type="date" name="expiry_date" value="<?= e($p['expiry_date'] ?? '') ?>"></div>
<?php if (!$p): ?><div><label>Opening stock</label><input type="number" min="0" name="stock_qty" value="0"></div><?php endif; ?>
</div><p><button>Save product</button> <a class="btn sec" href="products.php">Cancel</a></p></form>
<?php render_foot();
