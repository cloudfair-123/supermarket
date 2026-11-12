<?php
// Shared helpers: database, session, security, formatting.
date_default_timezone_set('Africa/Kampala');
session_start();
const BRAND = 'The Leaf Supermarket';
const SLOGAN = 'Everything you need under one leaf';
const MGR = ['manager', 'admin'];
const ADM = ['admin'];

function cfg() { static $c; return $c ??= require __DIR__ . '/../config.php'; }
function db(): PDO {
    static $pdo;
    if (!$pdo) {
        $c = cfg();
        $pdo = new PDO("mysql:host={$c['host']};port={$c['port']};dbname={$c['name']};charset=utf8mb4", $c['user'], $c['pass'], [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ]);
    }
    return $pdo;
}
function run($sql, $a = []) { $s = db()->prepare($sql); $s->execute($a); return $s; }
function q($sql, $a = []) { return run($sql, $a)->fetchAll(); }
function q1($sql, $a = []) { $r = q($sql, $a); return $r[0] ?? null; }

function e($v) { return htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8'); }
function ugx($n) { return 'UGX ' . number_format((float)$n); }
function num($n) { return number_format((float)$n); }
function now() { return date('Y-m-d H:i:s'); }
function redirect($u) { header("Location: $u"); exit; }
function flash($msg, $type = 'ok') { $_SESSION['flash'][] = [$type, $msg]; }
function pct_rows(array $rows, $key = 'value') {
    $max = 1; foreach ($rows as $r) $max = max($max, (float)$r[$key]);
    foreach ($rows as &$r) $r['pct'] = round($r[$key] / $max * 100);
    return $rows;
}

// CSRF protection for every POST request
function csrf() { return $_SESSION['csrf'] ??= bin2hex(random_bytes(16)); }
function csrf_field() { return '<input type="hidden" name="csrf" value="' . csrf() . '">'; }
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $t = $_POST['csrf'] ?? ($_SERVER['HTTP_X_CSRF_TOKEN'] ?? '');
    if (!hash_equals(csrf(), (string)$t)) { http_response_code(400); exit('Invalid form token. Go back and reload the page.'); }
}

// Authentication and role-based access
function user() { return $_SESSION['user'] ?? null; }
function is_mgr() { return in_array(user()['role'] ?? '', MGR, true); }
function require_login(array $roles = []) {
    if (!user()) redirect('login.php');
    if ($roles && !in_array(user()['role'], $roles, true)) {
        http_response_code(403);
        require_once __DIR__ . '/layout.php';
        render_head('Access denied');
        echo '<div class="card"><h1>Access denied</h1><p>Your role does not have access to this page.</p><a class="btn" href="dashboard.php">Back to dashboard</a></div>';
        render_foot(); exit;
    }
}
require_once __DIR__ . '/layout.php';
