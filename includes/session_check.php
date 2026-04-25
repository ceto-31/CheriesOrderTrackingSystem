<?php
/**
 * Check if user is logged in
 * Redirect to login if not
 */
require_once __DIR__ . '/../config/config.php';

if (!is_logged_in()) {
    $_SESSION['error'] = "Please log in to access this page.";
    redirect(SITE_URL . 'login.php');
}
?>
