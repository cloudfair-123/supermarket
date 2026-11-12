<?php
require 'includes/bootstrap.php'; require_login(MGR);
if ($_SERVER['REQUEST_METHOD'] === 'POST') {   // receive goods: adds quantities to stock
    $po = (int)($_POST['po_id'] ?? 0); $pdo = db();
    $o = q1('SELECT * FROM purchase_orders WHERE po_id=?', [$po]);
    if (!$o || $o['status'] === 'Received') { flash('Order not found or already received.', 'error'); redirect('orders.php'); }
    $pdo->beginTransaction();
    foreach (q('SELECT * FROM po_items WHERE po_id=?', [$po]) as $i) {
        run('UPDATE products SET stock_qty=stock_qty+?, cost_price=? WHERE product_id=?', [$i['qty'], $i['unit_cost'], $i['product_id']]);
        run('INSERT INTO stock_adjustments(product_id,user_id,change_qty,reason,adj_date) VALUES(?,?,?,?,?)', [$i['product_id'], user()['id'], $i['qty'], "Goods received PO #$po", now()]);
    }
    run("UPDATE purchase_orders SET status='Received', received_date=? WHERE po_id=?", [date('Y-m-d'), $po]);
    $pdo->commit(); flash("PO #$po received and stock updated."); redirect('orders.php');
}
$rows = q('SELECT po.*, s.name supplier FROM purchase_orders po JOIN suppliers s ON s.supplier_id=po.supplier_id ORDER BY po_id DESC');
render_head('Purchase Orders'); ?>
<h1>Purchase Orders</h1><p class="sub">Receiving an order automatically adds the goods to stock</p>
<p><a class="btn amber" href="order_new.php">+ New purchase order</a></p>
<div class="card"><table><tr><th>PO</th><th>Supplier</th><th>Ordered</th><th>Status</th><th class="r">Total</th><th></th></tr>
<?php foreach ($rows as $o): ?><tr><td><a href="order_view.php?id=<?= $o['po_id'] ?>">#<?= $o['po_id'] ?></a></td><td><?= e($o['supplier']) ?></td><td><?= e($o['order_date']) ?></td><td><span class="badge <?= $o['status'] === 'Received' ? 'b-green' : 'b-amber' ?>"><?= $o['status'] ?></span></td><td class="r"><?= ugx($o['total_cost']) ?></td>
<td><?php if ($o['status'] === 'Pending'): ?><form method="post"><?= csrf_field() ?><input type="hidden" name="po_id" value="<?= $o['po_id'] ?>"><button class="sm">Mark received</button></form><?php endif; ?></td></tr><?php endforeach; ?></table></div>
<?php render_foot();
