<?php
/**
 * Check if user is admin
 * Redirect if not authorized
 */
require_once __DIR__ . '/../config/config.php';

if (!is_logged_in() || !is_admin()) {
    $_SESSION['error'] = "Access denied. Admin privileges required.";
    redirect(SITE_URL . 'login.php');
}
?>
