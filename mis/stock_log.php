<?php
require 'includes/bootstrap.php'; require_login(MGR);
$rows = q('SELECT a.*, p.name product, u.full_name user FROM stock_adjustments a JOIN products p ON p.product_id=a.product_id JOIN users u ON u.user_id=a.user_id ORDER BY adj_id DESC LIMIT 100');
render_head('Stock Log'); ?>
<h1>Stock Movement Log</h1><p class="sub">Audit trail of every stock change (latest 100)</p>
<div class="card"><table><tr><th>Date</th><th>Product</th><th class="r">Change</th><th>Reason</th><th>By</th></tr>
<?php foreach ($rows as $a): ?><tr><td><?= e($a['adj_date']) ?></td><td><?= e($a['product']) ?></td><td class="r"><span class="badge <?= $a['change_qty'] > 0 ? 'b-green' : 'b-red' ?>"><?= sprintf('%+d', $a['change_qty']) ?></span></td><td><?= e($a['reason']) ?></td><td><?= e($a['user']) ?></td></tr><?php endforeach; ?></table></div>
<?php render_foot();
