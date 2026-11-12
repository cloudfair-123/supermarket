<?php
require 'includes/bootstrap.php'; require_login();
$t = date('Y-m-d'); $qs = trim($_GET['q'] ?? ''); $f = $_GET['filter'] ?? '';
$sql = 'SELECT p.*, c.name category, s.name supplier FROM products p JOIN categories c ON c.category_id=p.category_id JOIN suppliers s ON s.supplier_id=p.supplier_id WHERE p.active=1';
$args = [];
if ($qs !== '') { $sql .= ' AND (p.name LIKE ? OR p.sku LIKE ?)'; $args = ["%$qs%", "%$qs%"]; }
if ($f === 'low') $sql .= ' AND p.stock_qty<=p.reorder_level';
$rows = q($sql . ' ORDER BY p.name', $args);
render_head('Products & Stock'); ?>
<h1>Products &amp; Stock</h1><p class="sub"><?= count($rows) ?> products</p>
<div class="card"><form class="inline" style="margin-bottom:12px"><input name="q" value="<?= e($qs) ?>" placeholder="Search…" style="width:220px">
<select name="filter" style="width:170px"><option value="">All stock</option><option value="low" <?= $f === 'low' ? 'selected' : '' ?>>Low stock only</option></select><button>Filter</button>
<?php if (is_mgr()): ?><a class="btn amber" href="product_form.php" style="margin-left:auto">+ Add product</a><?php endif; ?></form>
<table><tr><th>SKU</th><th>Product</th><th>Category</th><th>Supplier</th><?php if (is_mgr()) echo '<th class="r">Cost</th>'; ?><th class="r">Price</th><th class="r">Stock</th><th>Expiry</th><?php if (is_mgr()) echo '<th>Adjust stock</th><th></th>'; ?></tr>
<?php foreach ($rows as $p): ?><tr><td><?= e($p['sku']) ?></td><td><?= e($p['name']) ?></td><td><?= e($p['category']) ?></td><td><?= e($p['supplier']) ?></td>
<?php if (is_mgr()) echo '<td class="r">' . num($p['cost_price']) . '</td>'; ?><td class="r"><?= num($p['selling_price']) ?></td>
<td class="r"><span class="badge <?= $p['stock_qty'] <= $p['reorder_level'] ? 'b-red' : 'b-green' ?>"><?= $p['stock_qty'] ?></span></td>
<td><?= $p['expiry_date'] ? '<span class="badge ' . ($p['expiry_date'] <= $t ? 'b-red' : '') . '">' . e($p['expiry_date']) . '</span>' : '–' ?></td>
<?php if (is_mgr()): ?><td><form class="inline" method="post" action="adjust.php"><?= csrf_field() ?><input type="hidden" name="product_id" value="<?= $p['product_id'] ?>"><input type="number" name="change_qty" placeholder="±qty" required><input class="w" name="reason" placeholder="Reason (damage…)"><button class="sm">Save</button></form></td>
<td><a class="btn sec sm" href="product_form.php?id=<?= $p['product_id'] ?>">Edit</a></td><?php endif; ?></tr><?php endforeach; ?></table></div>
<?php render_foot();
