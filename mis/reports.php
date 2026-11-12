<?php
require 'includes/bootstrap.php'; require_login(MGR);
$since = date('Y-m-d', strtotime('-30 days'));
$byCat = pct_rows(q('SELECT c.name label, SUM(si.qty*si.unit_price) value FROM sale_items si JOIN sales s ON s.sale_id=si.sale_id JOIN products p ON p.product_id=si.product_id JOIN categories c ON c.category_id=p.category_id WHERE DATE(s.sale_date)>=? GROUP BY c.category_id, c.name ORDER BY value DESC', [$since]));
$top = pct_rows(q('SELECT p.name label, SUM(si.qty) value FROM sale_items si JOIN sales s ON s.sale_id=si.sale_id JOIN products p ON p.product_id=si.product_id WHERE DATE(s.sale_date)>=? GROUP BY p.product_id, p.name ORDER BY value DESC LIMIT 10', [$since]));
$pay = pct_rows(q('SELECT payment_method label, SUM(total) value FROM sales WHERE DATE(sale_date)>=? GROUP BY payment_method ORDER BY value DESC', [$since]));
$reorder = q('SELECT p.*, s.name supplier FROM products p JOIN suppliers s ON s.supplier_id=p.supplier_id WHERE p.active=1 AND p.stock_qty<=p.reorder_level ORDER BY p.stock_qty/NULLIF(p.reorder_level,0)');
$expiring = q('SELECT * FROM products WHERE active=1 AND expiry_date IS NOT NULL AND expiry_date<=? ORDER BY expiry_date', [date('Y-m-d', strtotime('+14 days'))]);
$admin = user()['role'] === 'admin';
if ($admin) {
    $monthly = array_reverse(pct_rows(q("SELECT DATE_FORMAT(s.sale_date,'%Y-%m') label, SUM(si.qty*si.unit_price) value, SUM(si.qty*(si.unit_price-si.unit_cost)) profit FROM sale_items si JOIN sales s ON s.sale_id=si.sale_id GROUP BY label ORDER BY label DESC LIMIT 4")));
    $cashiers = q('SELECT u.full_name, COUNT(*) n, SUM(s.total) value FROM sales s JOIN users u ON u.user_id=s.user_id WHERE DATE(s.sale_date)>=? GROUP BY u.user_id, u.full_name ORDER BY value DESC', [$since]);
    $custs = q('SELECT c.full_name, SUM(s.total) value FROM sales s JOIN customers c ON c.customer_id=s.customer_id GROUP BY c.customer_id, c.full_name ORDER BY value DESC LIMIT 5');
    $margin = q('SELECT c.name, SUM(si.qty*(si.unit_price-si.unit_cost))*100/SUM(si.qty*si.unit_price) pct FROM sale_items si JOIN products p ON p.product_id=si.product_id JOIN categories c ON c.category_id=p.category_id GROUP BY c.category_id, c.name ORDER BY pct DESC');
    $share = (float)q1('SELECT SUM(CASE WHEN customer_id IS NOT NULL THEN total ELSE 0 END)*100/NULLIF(SUM(total),0) p FROM sales WHERE DATE(sale_date)>=?', [$since])['p'];
}
render_head('Reports & Analytics'); ?>
<h1>Reports &amp; Analytics</h1><p class="sub">Last 30 days unless stated</p>
<h2><span class="tag">Tactical</span> Management reports</h2>
<div class="grid g2" style="margin-bottom:16px">
  <div class="card"><h2>Revenue by category</h2><?php bars($byCat); ?></div>
  <div class="card"><h2>Top 10 products (units sold)</h2><?php bars($top, false); ?></div>
  <div class="card"><h2>Revenue by payment method</h2><?php bars($pay); ?></div>
  <div class="card"><h2>Expiring in 14 days</h2><table><tr><th>Product</th><th>Expiry</th><th class="r">Qty</th></tr>
  <?php foreach ($expiring as $p) echo '<tr><td>' . e($p['name']) . '</td><td>' . e($p['expiry_date']) . '</td><td class="r">' . $p['stock_qty'] . '</td></tr>'; if (!$expiring) echo '<tr><td colspan="3">None.</td></tr>'; ?></table></div>
</div>
<div class="card"><h2>Reorder list</h2><table><tr><th>Product</th><th>Supplier</th><th class="r">Stock</th><th class="r">Reorder level</th></tr>
<?php foreach ($reorder as $p) echo '<tr><td>' . e($p['name']) . '</td><td>' . e($p['supplier']) . '</td><td class="r"><span class="badge b-red">' . $p['stock_qty'] . '</span></td><td class="r">' . $p['reorder_level'] . '</td></tr>'; if (!$reorder) echo '<tr><td colspan="4">Nothing to reorder.</td></tr>'; ?></table>
<p><a class="btn amber sm" href="order_new.php">Create purchase order</a></p></div>
<?php if ($admin): ?>
<h2 style="margin-top:24px"><span class="tag">Strategic</span> Owner insights</h2>
<div class="grid g2">
  <div class="card"><h2>Monthly revenue &amp; gross profit</h2>
  <?php foreach ($monthly as $m): ?><div class="bar"><div class="t"><?= e($m['label']) ?></div><div class="b"><i style="width:<?= $m['pct'] ?>%"></i></div><div class="v"><?= ugx($m['value']) ?></div></div>
  <div class="sub" style="margin:-4px 0 6px 140px;font-size:.78rem">Profit <?= ugx($m['profit']) ?> · margin <?= round($m['profit'] * 100 / $m['value'], 1) ?>%</div><?php endforeach; ?></div>
  <div class="card"><h2>Gross margin by category</h2><table><?php foreach ($margin as $m) echo '<tr><td>' . e($m['name']) . '</td><td class="r"><b>' . round($m['pct'], 1) . '%</b></td></tr>'; ?></table></div>
  <div class="card"><h2>Top loyalty customers</h2><table><?php foreach ($custs as $c) echo '<tr><td>' . e($c['full_name']) . '</td><td class="r">' . ugx($c['value']) . '</td></tr>'; ?></table>
  <p class="sub">Loyalty members account for <b><?= round($share, 1) ?>%</b> of the last 30 days' revenue.</p></div>
  <div class="card"><h2>Cashier performance</h2><table><tr><th>Cashier</th><th class="r">Sales</th><th class="r">Revenue</th></tr><?php foreach ($cashiers as $c) echo '<tr><td>' . e($c['full_name']) . '</td><td class="r">' . $c['n'] . '</td><td class="r">' . ugx($c['value']) . '</td></tr>'; ?></table></div>
</div>
<?php endif; render_foot();
