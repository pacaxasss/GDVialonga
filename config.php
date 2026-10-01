<?php
/**
 * GD Vialonga — Configuração Global
 * Valores sensíveis são fornecidos pelo ambiente do servidor.
 */

define('DB_PATH',           __DIR__ . '/data/gdvialonga.db');
define('SITE_NAME',         'GD Vialonga');
define('SITE_URL',          rtrim(getenv('GDV_SITE_URL') ?: 'http://localhost:8080', '/'));
define('ADMIN_SESSION_KEY', 'gdv_admin');
define('SEASON',            '2026/27');
define('FPF_BASE_URL',      'https://centroderesultados-as-prd.azurewebsites.net');
define('FPF_COMPETITION_ID','28123');
define('FPF_SEASON_ID',     '106');
define('FPF_COMPETITION_NAME', 'CD I DIVISÃO | SABSEG');
define('DEBUG',             filter_var(getenv('GDV_DEBUG') ?: 'false', FILTER_VALIDATE_BOOLEAN));

if (DEBUG) {
    ini_set('display_errors', 1);
    error_reporting(E_ALL);
} else {
    ini_set('display_errors', 0);
    ini_set('log_errors', 1);
    error_reporting(0);
}

ini_set('session.use_strict_mode', '1');
ini_set('session.use_only_cookies', '1');
ini_set('session.use_trans_sid', '0');
ini_set('session.cookie_httponly', '1');
ini_set('session.cookie_samesite', 'Lax');
ini_set('session.gc_maxlifetime', '1800');
$requestIsHttps = !empty($_SERVER['HTTPS']) && strtolower((string)$_SERVER['HTTPS']) !== 'off';
$configuredForHttps = parse_url(SITE_URL, PHP_URL_SCHEME) === 'https';
if ($configuredForHttps && !$requestIsHttps && PHP_SAPI !== 'cli') {
    $requestUri = $_SERVER['REQUEST_URI'] ?? '/';
    header('Location: ' . SITE_URL . $requestUri, true, 301);
    exit;
}

if (session_status() === PHP_SESSION_NONE) {
    session_name(ADMIN_SESSION_KEY);
    session_set_cookie_params([
        'lifetime' => 0,
        'path' => '/',
        'secure' => $requestIsHttps || $configuredForHttps,
        'httponly' => true,
        'samesite' => 'Lax',
    ]);
    session_start();
}

if (!headers_sent()) {
    header_remove('X-Powered-By');
    header('X-Content-Type-Options: nosniff');
    header('X-Frame-Options: SAMEORIGIN');
    header('Referrer-Policy: strict-origin-when-cross-origin');
    header('Permissions-Policy: camera=(), microphone=(), geolocation=()');
    header("Content-Security-Policy: default-src 'self'; script-src 'self'; style-src 'self' 'unsafe-inline' https://fonts.googleapis.com; font-src 'self' https://fonts.gstatic.com data:; img-src 'self' data:; connect-src 'self'; object-src 'none'; base-uri 'self'; form-action 'self'; frame-ancestors 'self'");

    if (str_contains($_SERVER['SCRIPT_NAME'] ?? '', '/admin/')) {
        header('Cache-Control: no-store, private');
    }

    if ($requestIsHttps || $configuredForHttps) {
        header('Strict-Transport-Security: max-age=31536000');
    }
}
