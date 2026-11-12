<?php
function nav($file, $label, $also = []) {
    $cur = basename($_SERVER['SCRIPT_NAME']);
    $on = ($cur === $file || in_array($cur, $also, true)) ? ' class="on"' : '';
    echo "<a$on href=\"$file\">$label</a>";
}
function flashes() {
    foreach ($_SESSION['flash'] ?? [] as [$c, $t]) echo '<div class="flash ' . e($c) . '">' . e($t) . '</div>';
    unset($_SESSION['flash']);
}
function render_head($title = '', $bare = false) {
    echo '<!doctype html><html lang="en"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1">';
    echo '<link rel="icon" href="' . e(cfg()['logo']) . '"><title>' . e($title ? "$title · " . BRAND : BRAND) . '</title><link rel="stylesheet" href="assets/style.css"></head><body>';
    if ($bare) return;
    $u = user();
    echo '<div class="layout"><nav class="side"><div class="brand">' . logo_html() . '<div class="bname">The <span>Leaf</span><small>Supermarket</small></div></div><div class="slogan">' . e(SLOGAN) . '</div>';
    echo '<div class="grp">Operations</div>';
    nav('dashboard.php', 'Dashboard'); nav('pos.php', 'Point of Sale'); nav('sales.php', 'Sales History', ['receipt.php']);
    nav('products.php', 'Products &amp; Stock', ['product_form.php']); nav('customers.php', 'Loyalty Customers');
    if (is_mgr()) {
        echo '<div class="grp">Management</div>';
        nav('orders.php', 'Purchase Orders', ['order_new.php', 'order_view.php']); nav('suppliers.php', 'Suppliers');
        nav('stock_log.php', 'Stock Log'); nav('reports.php', 'Reports &amp; Analytics');
    }
    if ($u['role'] === 'admin') { echo '<div class="grp">Administration</div>'; nav('users.php', 'Users'); }
    echo '<div class="who"><b>' . e($u['name']) . '</b>' . e(ucfirst($u['role'])) . ' · <a href="logout.php" style="color:var(--amber)">Log out</a></div></nav><main>';
    flashes();
}
function render_foot() { echo '</main></div><script src="assets/app.js"></script></body></html>'; }
function bars(array $rows, $money = true) {
    if (!$rows) { echo '<p class="sub">No data yet.</p>'; return; }
    foreach ($rows as $r) {
        echo '<div class="bar"><div class="t" title="' . e($r['label']) . '">' . e($r['label']) . '</div><div class="b"><i style="width:' . (int)$r['pct'] . '%"></i></div><div class="v">' . ($money ? ugx($r['value']) : num($r['value'])) . '</div></div>';
    }
}

// Logo picture slot (top-left). Shows the file set in config.php, or a placeholder box if the file is missing.
function logo_html($class = '') {
    $path = cfg()['logo'] ?? '';
    if ($path && file_exists(__DIR__ . '/../' . $path)) return '<span class="logo ' . $class . '"><img src="' . e($path) . '" alt="' . e(BRAND) . ' logo"></span>';
    return '<span class="logo empty ' . $class . '">LOGO</span>';
}
