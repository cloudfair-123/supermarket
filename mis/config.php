<?php
// Database settings. Defaults match a fresh XAMPP/WAMP install (user root, no password).
return [
    'host' => getenv('PB_DB_HOST') ?: 'localhost',
    'port' => (int)(getenv('PB_DB_PORT') ?: 3306),
    'name' => getenv('PB_DB_NAME') ?: 'leaf_supermarket',
    'user' => getenv('PB_DB_USER') ?: 'root',
    // Logo picture shown top-left on every page and on the login screen.
    // To use your own logo, save it in the assets folder and change this path (PNG, JPG or SVG).
    'logo' => 'assets/logo.svg',
    'pass' => getenv('PB_DB_PASS') ?: '',
];
