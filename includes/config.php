<?php
/**
 * MANON Luxury Fashion Configuration
 */

if (session_status() === PHP_SESSION_NONE) {
    // Secure session settings
    ini_set('session.cookie_httponly', 1);
    ini_set('session.use_only_cookies', 1);
    session_start();
}

// Database Credentials
define('DB_HOST', '127.0.0.1');
define('DB_NAME', 'manon_db');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_CHARSET', 'utf8mb4');

// Store Constants
define('SITE_NAME', 'MANON | منون');
define('SITE_SLOGAN', 'عبايتك فخامة تليق بك');
define('WHATSAPP_RAW', '+20 12 73572887');
define('WHATSAPP_PHONE', '201273572887'); // For wa.me link
define('STORE_PHONE', '+20 12 73572887');
define('STORE_EMAIL', 'contact@manon-abaya.com');
define('STORE_ADDRESS', 'القاهرة - مصر');
define('DEFAULT_CURRENCY', 'ج.م');

// Auto-detect base URL
$protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https://' : 'http://';
$host = $_SERVER['HTTP_HOST'] ?? 'localhost';
$scriptDir = str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? ''));
$basePath = preg_replace('#/(admin|api).*$#i', '', $scriptDir);
if ($basePath === '/' || $basePath === '') {
    $basePath = '/manon';
}
define('BASE_URL', rtrim($protocol . $host . $basePath, '/') . '/');
define('ROOT_PATH', dirname(__DIR__) . '/');

// Timezone
date_default_timezone_set('Africa/Cairo');
