<?php
require 'includes/bootstrap.php'; require_login(MGR);
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    run('INSERT INTO suppliers(name,contact_person,phone,email,address) VALUES(?,?,?,?,?)', [trim($_POST['name']), trim($_POST['contact_person']), trim($_POST['phone']), trim($_POST['email']), trim($_POST['address'])]);
    flash('Supplier added.'); redirect('suppliers.php');
}
$rows = q('SELECT s.*, (SELECT COUNT(*) FROM products WHERE supplier_id=s.supplier_id) n_products,
           (SELECT COALESCE(SUM(total_cost),0) FROM purchase_orders WHERE supplier_id=s.supplier_id) spend FROM suppliers s ORDER BY name');
render_head('Suppliers'); ?>
<h1>Suppliers</h1><p class="sub">Vendor directory and purchasing history</p>
<div class="card"><table><tr><th>Supplier</th><th>Contact</th><th>Phone</th><th>Email</th><th>Address</th><th class="r">Products</th><th class="r">Total ordered</th></tr>
<?php foreach ($rows as $s): ?><tr><td><?= e($s['name']) ?></td><td><?= e($s['contact_person']) ?></td><td><?= e($s['phone']) ?></td><td><?= e($s['email']) ?></td><td><?= e($s['address']) ?></td><td class="r"><?= $s['n_products'] ?></td><td class="r"><?= ugx($s['spend']) ?></td></tr><?php endforeach; ?></table></div>
<form class="card" method="post"><?= csrf_field() ?><h2>Add supplier</h2><div class="form"><div><label>Name</label><input name="name" required></div><div><label>Contact person</label><input name="contact_person"></div><div><label>Phone</label><input name="phone"></div><div><label>Email</label><input name="email" type="email"></div><div><label>Address</label><input name="address"></div></div><p><button>Add supplier</button></p></form>
<?php render_foot();
