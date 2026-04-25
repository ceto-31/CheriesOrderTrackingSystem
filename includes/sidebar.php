<?php
// Get unread notification count
if (!isset($db)) {
    $database = new Database();
    $db = $database->getConnection();
}

if (isset($_SESSION['user_id'])) {
    $user_id = $_SESSION['user_id'];
    $notif_query = "SELECT COUNT(*) as count FROM notifications WHERE user_id = :user_id AND is_read = 0";
    $notif_stmt = $db->prepare($notif_query);
    $notif_stmt->bindParam(':user_id', $user_id);
    $notif_stmt->execute();
    $unread_count = $notif_stmt->fetch()['count'] ?? 0;
} else {
    $unread_count = 0;
}
?>

<!-- Sidebar -->
<aside>
  <div class="logo">🍴 DineClick</div>
  <div class="sidebar-menu">
    <a href="cart.php" <?php echo (basename($_SERVER['PHP_SELF']) == 'cart.php') ? 'class="active"' : ''; ?>>My Cart</a>
    <a href="orders.php" <?php echo (basename($_SERVER['PHP_SELF']) == 'orders.php') ? 'class="active"' : ''; ?>>My Orders</a>
    <a href="order_history.php" <?php echo (basename($_SERVER['PHP_SELF']) == 'order_history.php') ? 'class="active"' : ''; ?>>Order History</a>
    <a href="notifications.php" <?php echo (basename($_SERVER['PHP_SELF']) == 'notifications.php') ? 'class="active"' : ''; ?>>
      Notifications
      <?php if ($unread_count > 0): ?>
        <span class="notif-badge"><?php echo $unread_count; ?></span>
      <?php endif; ?>
    </a>
    <a href="profile.php" <?php echo (basename($_SERVER['PHP_SELF']) == 'profile.php') ? 'class="active"' : ''; ?>>Profile Settings</a>
    <a href="help.php" <?php echo (basename($_SERVER['PHP_SELF']) == 'help.php') ? 'class="active"' : ''; ?>>Help & FAQ</a>
    <a href="support.php" <?php echo (basename($_SERVER['PHP_SELF']) == 'support.php') ? 'class="active"' : ''; ?>>Contact Support</a>
    <a href="logout.php">Logout</a>
  </div>
</aside>

<style>
.sidebar-menu a {
  position: relative;
}

.notif-badge {
  position: absolute;
  top: 8px;
  right: 10px;
  background-color: #dc3545;
  color: #fff;
  font-size: 0.75rem;
  font-weight: 700;
  padding: 2px 6px;
  border-radius: 10px;
  min-width: 18px;
  text-align: center;
  line-height: 1.2;
  animation: pulse 2s infinite;
}

@keyframes pulse {
  0%, 100% {
    transform: scale(1);
  }
  50% {
    transform: scale(1.1);
  }
}
</style>
