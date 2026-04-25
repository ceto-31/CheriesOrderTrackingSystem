<?php
/**
 * Settings page redirects to profile.php
 * Both pages have similar functionality for account management
 */
require_once 'config/config.php';

// Redirect to profile page
redirect(SITE_URL . 'profile.php');
?>
