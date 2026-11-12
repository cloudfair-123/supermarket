<?php
require 'includes/bootstrap.php'; require_login();
$s = q1('SELECT s.*, u.full_name cashier, c.full_name customer FROM sales s JOIN users u ON u.user_id=s.user_id LEFT JOIN customers c ON c.customer_id=s.customer_id WHERE sale_id=?', [(int)($_GET['id'] ?? 0)]);
if (!$s) { http_response_code(404); exit('Receipt not found.'); }
$items = q('SELECT si.*, p.name FROM sale_items si JOIN products p ON p.product_id=si.product_id WHERE sale_id=?', [$s['sale_id']]);
render_head('Receipt #' . $s['sale_id']); ?>
<div class="card receipt"><div class="c"><?= logo_html("rcpt") ?><br><b>THE LEAF SUPERMARKET</b><br><i><?= e(SLOGAN) ?></i><br>Plot 12, Kampala Road, Uganda<br>Tel: 0700 000 123</div><hr>
Receipt #<?= $s['sale_id'] ?><br><?= e($s['sale_date']) ?><br>Cashier: <?= e($s['cashier']) ?><?php if ($s['customer']) echo '<br>Customer: ' . e($s['customer']); ?><hr>
<table><?php foreach ($items as $i): ?><tr><td style="white-space:normal"><?= e($i['name']) ?><br><?= $i['qty'] ?> × <?= num($i['unit_price']) ?></td><td class="r"><?= num($i['qty'] * $i['unit_price']) ?></td></tr><?php endforeach; ?></table><hr>
<b>TOTAL: <?= ugx($s['total']) ?></b><br>Paid by: <?= e($s['payment_method']) ?><?php if ($s['points_earned']) echo '<br>Loyalty points earned: ' . $s['points_earned']; ?><hr><div class="c">Thank you for shopping with us!</div></div>
<p class="noprint" style="text-align:center"><button onclick="print()">Print</button> <a class="btn sec" href="pos.php">New sale</a></p>
<?php render_foot();
