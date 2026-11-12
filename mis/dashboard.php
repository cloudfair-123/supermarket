<?php
require 'includes/bootstrap.php'; require_login();
$role = user()['role']; $t = date('Y-m-d');
$today = q1('SELECT COALESCE(SUM(total),0) s, COUNT(*) c FROM sales WHERE DATE(sale_date)=?', [$t]);
$low = q('SELECT * FROM products WHERE active=1 AND stock_qty<=reorder_level ORDER BY stock_qty/NULLIF(reorder_level,0) LIMIT 8');
$lowCount = q1('SELECT COUNT(*) c FROM products WHERE active=1 AND stock_qty<=reorder_level')['c'];
$exp = q('SELECT * FROM products WHERE active=1 AND expiry_date IS NOT NULL AND expiry_date<=? ORDER BY expiry_date LIMIT 8', [date('Y-m-d', strtotime('+7 days'))]);
$recent = q('SELECT s.*, u.full_name cashier FROM sales s JOIN users u ON u.user_id=s.user_id ORDER BY sale_id DESC LIMIT 6');
$week = [];
if (is_mgr()) {
    for ($i = 6; $i >= 0; $i--) {
        $day = date('Y-m-d', strtotime("-$i days"));
        $week[] = ['label' => date('D d', strtotime($day)), 'value' => q1('SELECT COALESCE(SUM(total),0) s FROM sales WHERE DATE(sale_date)=?', [$day])['s']];
    }
    $week = pct_rows($week);
}
if ($role === 'admin') {
    $month = q1('SELECT COALESCE(SUM(si.qty*si.unit_price),0) rev, COALESCE(SUM(si.qty*(si.unit_price-si.unit_cost)),0) profit
                 FROM sale_items si JOIN sales s ON s.sale_id=si.sale_id WHERE DATE(s.sale_date)>=?', [date('Y-m-01')]);
}
$levels = ['cashier' => 'Operational view', 'manager' => 'Tactical view', 'admin' => 'Strategic view'];
render_head('Dashboard'); ?>
<h1>Hello, <?= e(explode(' ', user()['name'])[0]) ?> <span class="tag"><?= $levels[$role] ?></span></h1>
<p class="sub"><?= $t ?> · The Leaf Supermarket</p>
<div class="grid g4" style="margin-bottom:16px">
  <div class="card stat"><div class="n"><?= ugx($today['s']) ?></div><div class="l">Sales today (<?= $today['c'] ?> transactions)</div></div>
  <div class="card stat <?= $lowCount ? 'warn' : '' ?>"><div class="n"><?= $lowCount ?></div><div class="l">Products at or below reorder level</div></div>
  <div class="card stat <?= $exp ? 'warn' : '' ?>"><div class="n"><?= count($exp) ?></div><div class="l">Expiring within 7 days</div></div>
  <?php if ($role === 'admin'): ?>
  <div class="card stat"><div class="n"><?= ugx($month['rev']) ?></div><div class="l">Revenue this month</div></div>
  <div class="card stat"><div class="n"><?= ugx($month['profit']) ?></div><div class="l">Gross profit this month (<?= $month['rev'] > 0 ? round($month['profit'] * 100 / $month['rev'], 1) : 0 ?>% margin)</div></div>
  <?php endif; ?>
</div>
<?php if ($week): ?><div class="card"><h2>Sales – last 7 days</h2><?php bars($week); ?></div><?php endif; ?>
<div class="grid g2">
  <div class="card"><h2>Low stock alerts</h2><table><tr><th>Product</th><th class="r">In stock</th><th class="r">Reorder at</th></tr>
  <?php foreach ($low as $p): ?><tr><td><?= e($p['name']) ?></td><td class="r"><span class="badge b-red"><?= $p['stock_qty'] ?></span></td><td class="r"><?= $p['reorder_level'] ?></td></tr>
  <?php endforeach; if (!$low) echo '<tr><td colspan="3">All stock levels healthy.</td></tr>'; ?></table></div>
  <div class="card"><h2>Expiring soon</h2><table><tr><th>Product</th><th>Expires</th><th class="r">Qty</th></tr>
  <?php foreach ($exp as $p): ?><tr><td><?= e($p['name']) ?></td><td><span class="badge <?= $p['expiry_date'] <= $t ? 'b-red' : 'b-amber' ?>"><?= e($p['expiry_date']) ?></span></td><td class="r"><?= $p['stock_qty'] ?></td></tr>
  <?php endforeach; if (!$exp) echo '<tr><td colspan="3">Nothing expiring this week.</td></tr>'; ?></table></div>
</div>
<div class="card" style="margin-top:16px"><h2>Recent sales</h2><table><tr><th>Receipt</th><th>Time</th><th>Cashier</th><th>Payment</th><th class="r">Total</th></tr>
<?php foreach ($recent as $s): ?><tr><td><a href="receipt.php?id=<?= $s['sale_id'] ?>">#<?= $s['sale_id'] ?></a></td><td><?= e($s['sale_date']) ?></td><td><?= e($s['cashier']) ?></td><td><?= e($s['payment_method']) ?></td><td class="r"><?= ugx($s['total']) ?></td></tr><?php endforeach; ?></table></div>
<?php render_foot();
