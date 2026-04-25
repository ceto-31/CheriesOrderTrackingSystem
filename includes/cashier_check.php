<?php
/**
 * Cashier auth gate — include at top of every cashier page.
 * Requires role = 2.  Admins (role = 1) are also allowed.
 */
require_once __DIR__ . '/../config/config.php';

if (!is_logged_in() || (!is_cashier() && !is_admin())) {
    $_SESSION['error'] = "Access denied. Cashier privileges required.";
    redirect(SITE_URL . 'login.php');
}
