<?php
// JSON endpoint called by the POS: saves the sale, deducts stock, awards loyalty points (one DB transaction).
require 'includes/bootstrap.php';
header('Content-Type: application/json');
if (!user()) { http_response_code(401); exit(json_encode(['error' => 'Please log in again.'])); }
$in = json_decode(file_get_contents('php://input'), true) ?: [];
$qtys = [];
foreach ($in['items'] ?? [] as $it) { $id = (int)($it['id'] ?? 0); $qtys[$id] = ($qtys[$id] ?? 0) + (int)($it['qty'] ?? 0); }
if (!$qtys) { http_response_code(400); exit(json_encode(['error' => 'The cart is empty.'])); }
$pay = in_array($in['payment'] ?? '', ['Cash', 'Mobile Money', 'Card'], true) ? $in['payment'] : 'Cash';
$pdo = db();
try {
    $pdo->beginTransaction();
    $lines = []; $total = 0;
    foreach ($qtys as $id => $qty) {
        $p = q1('SELECT * FROM products WHERE product_id=? AND active=1 FOR UPDATE', [$id]);
        if (!$p || $qty <= 0) throw new RuntimeException('Invalid item in cart.');
        if ($p['stock_qty'] < $qty) throw new RuntimeException("Not enough stock for {$p['name']} (only {$p['stock_qty']} left).");
        $lines[] = [$p, $qty]; $total += $p['selling_price'] * $qty;
    }
    $cust = null; $phone = trim($in['phone'] ?? '');
    if ($phone !== '') {
        $cust = q1('SELECT * FROM customers WHERE phone=?', [$phone]);
        if (!$cust) throw new RuntimeException('No loyalty customer with that phone number.');
    }
    $pts = $cust ? intdiv((int)$total, 1000) : 0;
    run('INSERT INTO sales(sale_date,user_id,customer_id,total,payment_method,points_earned) VALUES(?,?,?,?,?,?)',
        [now(), user()['id'], $cust['customer_id'] ?? null, $total, $pay, $pts]);
    $sid = (int)$pdo->lastInsertId();
    foreach ($lines as [$p, $qty]) {
        run('INSERT INTO sale_items(sale_id,product_id,qty,unit_price,unit_cost) VALUES(?,?,?,?,?)', [$sid, $p['product_id'], $qty, $p['selling_price'], $p['cost_price']]);
        run('UPDATE products SET stock_qty=stock_qty-? WHERE product_id=?', [$qty, $p['product_id']]);
        run('INSERT INTO stock_adjustments(product_id,user_id,change_qty,reason,adj_date) VALUES(?,?,?,?,?)', [$p['product_id'], user()['id'], -$qty, "Sale #$sid", now()]);
    }
    if ($cust) run('UPDATE customers SET loyalty_points=loyalty_points+? WHERE customer_id=?', [$pts, $cust['customer_id']]);
    $pdo->commit();
    echo json_encode(['url' => "receipt.php?id=$sid"]);
} catch (RuntimeException $ex) {
    $pdo->rollBack(); http_response_code(400); echo json_encode(['error' => $ex->getMessage()]);
} catch (Throwable $ex) {
    if ($pdo->inTransaction()) $pdo->rollBack(); http_response_code(500); echo json_encode(['error' => 'Could not save the sale.']);
}
