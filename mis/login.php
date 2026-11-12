<?php
require 'includes/bootstrap.php';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $u = q1('SELECT * FROM users WHERE username=? AND active=1', [trim($_POST['username'] ?? '')]);
        if ($u && password_verify($_POST['password'] ?? '', $u['password_hash'])) {
            session_regenerate_id(true);
            $_SESSION['user'] = ['id' => $u['user_id'], 'name' => $u['full_name'], 'role' => $u['role']];
            redirect('dashboard.php');
        }
        flash('Invalid username or password.', 'error');
    } catch (PDOException $ex) {
        flash('Database not ready. Open setup.php first to install the database.', 'error');
    }
    redirect('login.php');
}
render_head('Sign in', true); ?>
<div class="login"><form class="box" method="post">
  <div class="lhead"><?= logo_html("big") ?><h1>The Leaf Supermarket</h1><p class="slogan2"><?= e(SLOGAN) ?></p><span class="sub">Management Information System</span></div>
  <?php flashes(); echo csrf_field(); ?>
  <div><label>Username</label><input name="username" required autofocus></div>
  <div><label>Password</label><input name="password" type="password" required></div>
  <button style="width:100%">Sign in</button>
  <div class="demo"><b>Demo accounts</b><br>admin / admin123 (owner)<br>manager / manager123<br>cashier / cashier123<br><a href="setup.php">First time? Install the database</a></div>
</form></div></body></html>
