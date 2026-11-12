<?php
require 'includes/bootstrap.php'; require_login(MGR);
$pid = (int)($_POST['product_id'] ?? 0); $qty = (int)($_POST['change_qty'] ?? 0);
$reason = trim($_POST['reason'] ?? '') ?: 'Manual adjustment';
$p = q1('SELECT * FROM products WHERE product_id=?', [$pid]);
if (!$p || $qty === 0) flash('Invalid adjustment.', 'error');
elseif ($p['stock_qty'] + $qty < 0) flash('Adjustment would make stock negative.', 'error');
else {
    run('UPDATE products SET stock_qty=stock_qty+? WHERE product_id=?', [$qty, $pid]);
    run('INSERT INTO stock_adjustments(product_id,user_id,change_qty,reason,adj_date) VALUES(?,?,?,?,?)', [$pid, user()['id'], $qty, $reason, now()]);
    flash("Stock of {$p['name']} adjusted by " . sprintf('%+d', $qty) . '.');
}
redirect('products.php');
