<?php
require 'includes/bootstrap.php'; require_login();
$rows = q('SELECT s.*, u.full_name cashier, c.full_name customer FROM sales s JOIN users u ON u.user_id=s.user_id LEFT JOIN customers c ON c.customer_id=s.customer_id ORDER BY sale_id DESC LIMIT 100');
render_head('Sales History'); ?>
<h1>Sales History</h1><p class="sub">Latest 100 transactions</p>
<div class="card"><input data-filter="#t" placeholder="Filter…" style="max-width:260px;margin-bottom:10px"><table id="t"><tr><th>Receipt</th><th>Date &amp; time</th><th>Cashier</th><th>Customer</th><th>Payment</th><th class="r">Total</th></tr>
<?php foreach ($rows as $s): ?><tr><td><a href="receipt.php?id=<?= $s['sale_id'] ?>">#<?= $s['sale_id'] ?></a></td><td><?= e($s['sale_date']) ?></td><td><?= e($s['cashier']) ?></td><td><?= e($s['customer'] ?: '–') ?></td><td><?= e($s['payment_method']) ?></td><td class="r"><?= ugx($s['total']) ?></td></tr><?php endforeach; ?></table></div>
<?php render_foot();
