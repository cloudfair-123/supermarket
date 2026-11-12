<?php
require 'includes/bootstrap.php';
require 'includes/seed.php';
$msg = ''; $err = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try { set_time_limit(300); $msg = install_database(); }
    catch (Throwable $ex) { $err = 'Setup failed: ' . $ex->getMessage() . ' — check config.php and that MySQL is running.'; }
}
render_head('Install database', true); ?>
<div class="login"><form class="box" method="post">
  <h1>Install database</h1>
  <p class="sub">Creates the <b><?= e(cfg()['name']) ?></b> MySQL database with all tables and sample data. <b>Any existing data in these tables is erased.</b></p>
  <?php if ($msg) echo '<div class="flash ok">' . e($msg) . '</div><a class="btn" href="login.php">Go to login</a>'; ?>
  <?php if ($err) echo '<div class="flash error">' . e($err) . '</div>'; ?>
  <?= csrf_field() ?>
  <?php if (!$msg): ?><button style="width:100%">Install / reset demo data</button><?php endif; ?>
</form></div></body></html>
