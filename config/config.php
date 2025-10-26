<?php
// Database configuration for Kottackal Medical Store
// Compatible with PHP 8.4 and MariaDB 10.5

// Database connection parameters
define('DB_HOST', 'localhost');
define('DB_NAME', 'kottackal_medical_store');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_CHARSET', 'utf8mb4');

// Application settings
define('APP_NAME', 'Kottackal Medical Store');
define('APP_VERSION', '1.0.0');
define('BASE_URL', '/');

// Session settings
define('SESSION_TIMEOUT', 3600); // 1 hour in seconds

// Timezone
date_default_timezone_set('Asia/Kolkata');

// Error reporting - disable in production
ini_set('display_errors', 0);
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/../logs/php_errors.log');
error_reporting(E_ALL);

// Security settings
ini_set('session.cookie_httponly', 1);
ini_set('session.use_only_cookies', 1);
ini_set('session.cookie_secure', 0); // Set to 1 if using HTTPS
?>
