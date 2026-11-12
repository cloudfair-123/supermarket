<?php
require 'includes/bootstrap.php'; require_login(ADM);
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (($_POST['action'] ?? '') === 'toggle') {
        $id = (int)$_POST['user_id'];
        if ($id === (int)user()['id']) flash('You cannot deactivate your own account.', 'error');
        else run('UPDATE users SET active=1-active WHERE user_id=?', [$id]);
    } else {
        $role = in_array($_POST['role'], ['admin', 'manager', 'cashier'], true) ? $_POST['role'] : 'cashier';
        try { run('INSERT INTO users(full_name,username,password_hash,role) VALUES(?,?,?,?)', [trim($_POST['full_name']), trim($_POST['username']), password_hash($_POST['password'], PASSWORD_DEFAULT), $role]); flash('User created.'); }
        catch (PDOException $ex) { flash('That username is already taken.', 'error'); }
    }
    redirect('users.php');
}
$rows = q('SELECT * FROM users ORDER BY role, full_name');
render_head('Users'); ?>
<h1>User Accounts</h1><p class="sub">Role-based access: cashier (operational) · manager (tactical) · admin (strategic)</p>
<div class="card"><table><tr><th>Name</th><th>Username</th><th>Role</th><th>Status</th><th></th></tr>
<?php foreach ($rows as $u): ?><tr><td><?= e($u['full_name']) ?></td><td><?= e($u['username']) ?></td><td><?= e(ucfirst($u['role'])) ?></td><td><span class="badge <?= $u['active'] ? 'b-green' : 'b-red' ?>"><?= $u['active'] ? 'Active' : 'Disabled' ?></span></td>
<td><form method="post"><?= csrf_field() ?><input type="hidden" name="action" value="toggle"><input type="hidden" name="user_id" value="<?= $u['user_id'] ?>"><button class="btn sec sm"><?= $u['active'] ? 'Deactivate' : 'Activate' ?></button></form></td></tr><?php endforeach; ?></table></div>
<form class="card" method="post"><?= csrf_field() ?><h2>Add user</h2><div class="form"><div><label>Full name</label><input name="full_name" required></div><div><label>Username</label><input name="username" required></div><div><label>Password</label><input name="password" type="password" required minlength="6"></div>
<div><label>Role</label><select name="role"><option value="cashier">Cashier</option><option value="manager">Manager</option><option value="admin">Admin</option></select></div></div><p><button>Create user</button></p></form>
<?php render_foot();
