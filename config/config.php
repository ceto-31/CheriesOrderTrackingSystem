<?php
/**
 * Application Configuration
 */

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Define constants
define('SITE_NAME',   'Cheries Order Tracking');
define('SITE_URL',    'http://localhost/CheriesOrderTrackingSystem/');
define('UPLOAD_PATH', __DIR__ . '/../assets/images/products/');
define('UPLOAD_URL',  SITE_URL . 'assets/images/products/');

// Timezone
date_default_timezone_set('Asia/Manila');

// Error reporting (set to 0 in production)
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Include database connections (DbMain + DbAnalytics + Database alias)
require_once __DIR__ . '/database.php';

// ─── Security helpers ────────────────────────────────────────

/** Strip tags, trim, and encode HTML entities to prevent XSS. */
function clean_input(string $data): string
{
    return htmlspecialchars(strip_tags(trim($data)), ENT_QUOTES | ENT_HTML5, 'UTF-8');
}

// ─── Auth helpers ────────────────────────────────────────────

function is_logged_in(): bool
{
    return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
}

function is_admin(): bool
{
    return isset($_SESSION['is_admin']) && (int)$_SESSION['is_admin'] === 1;
}

function is_cashier(): bool
{
    return isset($_SESSION['role']) && (int)$_SESSION['role'] === 2;
}

// ─── Utility helpers ─────────────────────────────────────────

function redirect(string $url): void
{
    header('Location: ' . $url);
    exit();
}

/** Format a numeric value as Philippine Peso. */
function format_price($amount): string
{
    if ($amount === null || $amount === '' || !is_numeric($amount)) {
        $amount = 0;
    }
    return '₱' . number_format((float)$amount, 2);
}
?>
