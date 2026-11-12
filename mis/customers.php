<?php
require 'includes/bootstrap.php'; require_login();
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try { run('INSERT INTO customers(full_name,phone,joined_on) VALUES(?,?,?)', [trim($_POST['full_name']), trim($_POST['phone']), date('Y-m-d')]); flash('Customer registered.'); }
    catch (PDOException $ex) { flash('A customer with that phone number already exists.', 'error'); }
    redirect('customers.php');
}
$rows = q('SELECT c.*, COALESCE((SELECT SUM(total) FROM sales WHERE customer_id=c.customer_id),0) spent, (SELECT COUNT(*) FROM sales WHERE customer_id=c.customer_id) visits FROM customers c ORDER BY spent DESC');
render_head('Loyalty Customers'); ?>
<h1>Loyalty Customers</h1><p class="sub">1 point earned for every UGX 1,000 spent</p>
<form class="card" method="post"><?= csrf_field() ?><h2>Register customer</h2><div class="form"><div><label>Full name</label><input name="full_name" required></div><div><label>Phone</label><input name="phone" required placeholder="07xxxxxxxx"></div><div><button>Register</button></div></div></form>
<div class="card"><input data-filter="#t" placeholder="Search customers…" style="max-width:260px;margin-bottom:10px"><table id="t"><tr><th>Name</th><th>Phone</th><th>Joined</th><th class="r">Visits</th><th class="r">Total spent</th><th class="r">Points</th></tr>
<?php foreach ($rows as $c): ?><tr><td><?= e($c['full_name']) ?></td><td><?= e($c['phone']) ?></td><td><?= e($c['joined_on']) ?></td><td class="r"><?= $c['visits'] ?></td><td class="r"><?= ugx($c['spent']) ?></td><td class="r"><span class="badge b-amber"><?= $c['loyalty_points'] ?></span></td></tr><?php endforeach; ?></table></div>
<?php render_foot();
