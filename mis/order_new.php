<?php
require 'includes/bootstrap.php'; require_login(MGR);
$sups = q('SELECT * FROM suppliers ORDER BY name'); $sid = (int)($_REQUEST['supplier'] ?? 0);
$prods = $sid ? q('SELECT * FROM products WHERE supplier_id=? AND active=1 ORDER BY stock_qty/NULLIF(reorder_level,0)', [$sid]) : [];
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $sid) {
    $lines = [];
    foreach ($prods as $p) {
        $q = (int)($_POST['qty_' . $p['product_id']] ?? 0);
        if ($q > 0) $lines[] = [$p['product_id'], $q, (float)($_POST['cost_' . $p['product_id']] ?? $p['cost_price'])];
    }
    if (!$lines) flash('Enter a quantity for at least one product.', 'error');
    else {
        $pdo = db(); $pdo->beginTransaction();
        $tot = 0; foreach ($lines as $l) $tot += $l[1] * $l[2];
        run('INSERT INTO purchase_orders(supplier_id,user_id,order_date,total_cost) VALUES(?,?,?,?)', [$sid, user()['id'], date('Y-m-d'), $tot]);
        $po = $pdo->lastInsertId();
        foreach ($lines as $l) run('INSERT INTO po_items(po_id,product_id,qty,unit_cost) VALUES(?,?,?,?)', [$po, $l[0], $l[1], $l[2]]);
        $pdo->commit(); flash("Purchase order #$po created."); redirect('orders.php');
    }
}
render_head('New purchase order'); ?>
<h1>New purchase order</h1><p class="sub">Choose a supplier, then enter quantities (0 = skip). Low-stock items are listed first.</p>
<form class="card inline" method="get"><select name="supplier" style="max-width:300px"><option value="">Select supplier…</option><?php foreach ($sups as $s) echo '<option value="' . $s['supplier_id'] . '"' . ($sid == $s['supplier_id'] ? ' selected' : '') . '>' . e($s['name']) . '</option>'; ?></select><button>Load products</button></form>
<?php if ($prods): ?><form class="card" method="post"><?= csrf_field() ?><input type="hidden" name="supplier" value="<?= $sid ?>"><table><tr><th>Product</th><th class="r">Stock</th><th class="r">Reorder at</th><th>Order qty</th><th>Unit cost</th></tr>
<?php foreach ($prods as $p): $low = $p['stock_qty'] <= $p['reorder_level']; ?><tr><td><?= e($p['name']) ?></td><td class="r"><span class="badge <?= $low ? 'b-red' : 'b-green' ?>"><?= $p['stock_qty'] ?></span></td><td class="r"><?= $p['reorder_level'] ?></td>
<td><input type="number" min="0" name="qty_<?= $p['product_id'] ?>" value="<?= $low ? $p['reorder_level'] * 2 - $p['stock_qty'] : 0 ?>" style="width:100px"></td><td><input type="number" min="0" step="any" name="cost_<?= $p['product_id'] ?>" value="<?= (int)$p['cost_price'] ?>" style="width:120px"></td></tr><?php endforeach; ?></table>
<p><button>Create purchase order</button></p></form><?php endif; ?>
<?php render_foot();
