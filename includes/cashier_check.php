<?php
/**
 * Cashier auth gate — include at the very top of every cashier page (before any output).
 * Allows role=2 (cashier) only.
 * Admins use /admin — they are NOT granted cashier access to prevent privilege confusion.
 */
require_once __DIR__ . '/../config/config.php';

if (!is_logged_in() || !is_cashier()) {
    $_SESSION['error'] = "Access denied. Cashier privileges required.";
    redirect(SITE_URL . 'login.php');
}
