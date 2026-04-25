<?php
/**
 * Application Configuration
 */

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Define constants
define('SITE_NAME', 'DineClick');
define('SITE_URL', 'http://localhost/DineClick/');
define('UPLOAD_PATH', __DIR__ . '/../assets/images/products/');
define('UPLOAD_URL', SITE_URL . 'assets/images/products/');

// Timezone
date_default_timezone_set('Asia/Manila');

// Error reporting (disable in production)
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Include database
require_once __DIR__ . '/database.php';

// Security function to prevent XSS
function clean_input($data) {
    return htmlspecialchars(strip_tags(trim($data)));
}

// Check if user is logged in
function is_logged_in() {
    return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
}

// Check if admin is logged in
function is_admin() {
    return isset($_SESSION['is_admin']) && $_SESSION['is_admin'] == 1;
}

// Redirect function
function redirect($url) {
    header("Location: " . $url);
    exit();
}

// Format price
function format_price($amount) {
    // Handle null, empty, or non-numeric values
    if ($amount === null || $amount === '' || !is_numeric($amount)) {
        $amount = 0;
    }
    return '₱' . number_format((float)$amount, 2);
}
?>
