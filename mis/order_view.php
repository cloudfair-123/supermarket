<?php
require 'includes/bootstrap.php'; require_login(MGR);
$po = q1('SELECT po.*, s.name supplier FROM purchase_orders po JOIN suppliers s ON s.supplier_id=po.supplier_id WHERE po_id=?', [(int)($_GET['id'] ?? 0)]);
if (!$po) { http_response_code(404); exit('Order not found.'); }
$items = q('SELECT i.*, p.name FROM po_items i JOIN products p ON p.product_id=i.product_id WHERE po_id=?', [$po['po_id']]);
render_head('Purchase order #' . $po['po_id']); ?>
<h1>Purchase order #<?= $po['po_id'] ?></h1><p class="sub"><?= e($po['supplier']) ?> · ordered <?= e($po['order_date']) ?> · <span class="badge <?= $po['status'] === 'Received' ? 'b-green' : 'b-amber' ?>"><?= $po['status'] ?></span></p>
<div class="card"><table><tr><th>Product</th><th class="r">Qty</th><th class="r">Unit cost</th><th class="r">Line total</th></tr>
<?php foreach ($items as $i): ?><tr><td><?= e($i['name']) ?></td><td class="r"><?= $i['qty'] ?></td><td class="r"><?= num($i['unit_cost']) ?></td><td class="r"><?= num($i['qty'] * $i['unit_cost']) ?></td></tr><?php endforeach; ?>
<tr><th colspan="3" class="r">TOTAL</th><th class="r"><?= ugx($po['total_cost']) ?></th></tr></table></div>
<?php if ($po['status'] === 'Pending'): ?><form method="post" action="orders.php"><?= csrf_field() ?><input type="hidden" name="po_id" value="<?= $po['po_id'] ?>"><button>Mark as received</button></form><?php endif; ?>
<?php render_foot();
