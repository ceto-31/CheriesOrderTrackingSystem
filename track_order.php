<?php
require_once 'includes/session_check.php';

$page_title = "Track Order";
$database = new Database();
$db = $database->getConnection();

$user_id = $_SESSION['user_id'];
$order_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

// Get order details
$query = "SELECT * FROM orders WHERE id = :id AND user_id = :user_id";
$stmt = $db->prepare($query);
$stmt->bindParam(':id', $order_id);
$stmt->bindParam(':user_id', $user_id);
$stmt->execute();

if ($stmt->rowCount() == 0) {
    $_SESSION['error'] = "Order not found.";
    redirect(SITE_URL . 'orders.php');
}

$order = $stmt->fetch();

// Determine progress step based on status
$progress_steps = [
    'Pending' => 0,
    'Confirmed' => 1,
    'Preparing' => 1,
    'Out for Delivery' => 2,
    'Delivered' => 4,
    'Cancelled' => 0
];

$current_step = $progress_steps[$order['status']] ?? 0;

include 'includes/header.php';
?>

<!-- Sidebar -->
<aside>
  <div class="logo">🍴 DineClick</div>
  <div class="sidebar-menu">
    <a href="cart.php">My Cart</a>
    <a href="orders.php" class="active">My Orders</a>
    <a href="order_history.php">Order History</a>
    <a href="notifications.php">Notifications</a>
    <a href="profile.php">Profile Settings</a>
    <a href="logout.php">Logout</a>
  </div>
</aside>

<!-- Main -->
<main>
  <header>
    <h3>Track My Order</h3>
    <nav>
      <a href="index.php">Home</a>
      <a href="menu.php">Menu</a>
      <a href="orders.php" class="active">My Orders</a>
      <a href="profile.php">Profile</a>
    </nav>
  </header>

  <section style="flex: 1; padding: 30px; overflow-y: auto;">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 25px;">
      <h2 style="margin: 0; font-size: 1.4rem; color: #000;">Order #<?php echo $order['id']; ?></h2>
      <a href="orders.php" style="text-decoration: none; color: #B76E09; font-weight: 600; font-size: 0.95rem;">
        ← Back to Orders
      </a>
    </div>

    <?php if ($order['status'] == 'Cancelled'): ?>
      <div style="background: #f8d7da; padding: 20px; border-radius: 12px; border-left: 4px solid #dc3545; margin-bottom: 25px;">
        <h3 style="margin: 0 0 10px 0; color: #721c24;">Order Cancelled</h3>
        <p style="margin: 0; color: #721c24;">This order has been cancelled. If you have any questions, please contact support.</p>
      </div>
    <?php elseif ($order['status'] == 'Delivered'): ?>
      <div style="background: #d4edda; padding: 20px; border-radius: 12px; border-left: 4px solid #28a745; margin-bottom: 25px;">
        <h3 style="margin: 0 0 10px 0; color: #155724;">✓ Order Delivered</h3>
        <p style="margin: 0; color: #155724;">Your order was delivered on <?php echo date('M d, Y \a\t h:i A', strtotime($order['updated_at'])); ?>. Thank you for ordering!</p>
      </div>
    <?php else: ?>
      
      <!-- Map Placeholder -->
      <div style="width: 100%; height: 240px; background-color: #fff; border-radius: 12px; border: 1px solid #ddd; margin-bottom: 30px; overflow: hidden;">
        <iframe
          src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3930.023442706348!2d121.7269!3d17.6131!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x0%3A0x0!2zMTfCsDM2JzQ3LjIiTiAxMjHCsDQzJzM2LjgiRQ!5e0!3m2!1sen!2sph!4v1697643245982!5m2!1sen!2sph"
          width="100%"
          height="240"
          style="border:0; border-radius:12px;"
          allowfullscreen=""
          loading="lazy"
          referrerpolicy="no-referrer-when-downgrade">
        </iframe>
      </div>

      <!-- Progress Bar -->
      <div style="display: flex; justify-content: space-between; position: relative; margin-bottom: 40px;">
        <div style="content: ''; position: absolute; top: 18px; left: 0; width: 100%; height: 4px; background-color: #ddd; z-index: 1;"></div>
        
        <div style="text-align: center; position: relative; z-index: 2; flex: 1;">
          <div style="width: 36px; height: 36px; border-radius: 50%; background-color: <?php echo $current_step >= 1 ? '#B76E09' : '#ddd'; ?>; color: #fff; font-weight: 600; line-height: 36px; margin: 0 auto 10px; transition: all 0.3s ease;">
            <?php echo $current_step >= 1 ? '✓' : '1'; ?>
          </div>
          <div style="color: #7D6E6E; font-size: 0.9rem; font-weight: 600;">Order Confirmed</div>
        </div>
        
        <div style="text-align: center; position: relative; z-index: 2; flex: 1;">
          <div style="width: 36px; height: 36px; border-radius: 50%; background-color: <?php echo $current_step >= 2 ? '#B76E09' : '#ddd'; ?>; color: #fff; font-weight: 600; line-height: 36px; margin: 0 auto 10px; transition: all 0.3s ease;">
            <?php echo $current_step >= 2 ? '✓' : '2'; ?>
          </div>
          <div style="color: #7D6E6E; font-size: 0.9rem; font-weight: 600;">Preparing</div>
        </div>
        
        <div style="text-align: center; position: relative; z-index: 2; flex: 1;">
          <div style="width: 36px; height: 36px; border-radius: 50%; background-color: <?php echo $current_step >= 3 ? '#B76E09' : '#ddd'; ?>; color: #fff; font-weight: 600; line-height: 36px; margin: 0 auto 10px; transition: all 0.3s ease;">
            <?php echo $current_step >= 3 ? '✓' : '3'; ?>
          </div>
          <div style="color: #7D6E6E; font-size: 0.9rem; font-weight: 600;">Out for Delivery</div>
        </div>
        
        <div style="text-align: center; position: relative; z-index: 2; flex: 1;">
          <div style="width: 36px; height: 36px; border-radius: 50%; background-color: <?php echo $current_step >= 4 ? '#B76E09' : '#ddd'; ?>; color: #fff; font-weight: 600; line-height: 36px; margin: 0 auto 10px; transition: all 0.3s ease;">
            <?php echo $current_step >= 4 ? '✓' : '4'; ?>
          </div>
          <div style="color: #7D6E6E; font-size: 0.9rem; font-weight: 600;">Delivered</div>
        </div>
      </div>

      <!-- Delivery Info -->
      <div style="display: flex; flex-wrap: wrap; gap: 15px; margin-bottom: 25px;">
        <div style="background: #fff; padding: 20px; border-radius: 12px; box-shadow: 0 2px 6px rgba(0,0,0,0.08); flex: 1; min-width: 250px;">
          <strong style="display: block; font-size: 1rem; margin-bottom: 6px; color: #000;">Delivering to</strong>
          <div style="color: #7D6E6E; font-size: 0.9rem;">
            <?php echo nl2br(htmlspecialchars($order['delivery_address'])); ?>
          </div>
        </div>
        
        <div style="background: #fff; padding: 20px; border-radius: 12px; box-shadow: 0 2px 6px rgba(0,0,0,0.08); max-width:160px; text-align:center;">
          <strong style="display: block; font-size: 1rem; margin-bottom: 6px; color: #000;">Status</strong>
          <div style="color: #B76E09; font-size: 0.9rem; font-weight: 600;">
            <?php echo $order['status']; ?>
          </div>
        </div>
        
        <div style="display:flex; align-items:center; justify-content:center;">
          <a href="contact.php" style="padding: 10px 20px; background-color: #fff; color: #B76E09; border: 1px solid #B76E09; text-decoration: none; border-radius: 8px; font-weight: 600; transition: all 0.3s ease;">
            Contact Support
          </a>
        </div>
      </div>
    <?php endif; ?>

    <a href="order_details.php?id=<?php echo $order['id']; ?>" style="display: block; width: 100%; padding: 12px; background-color: #B76E09; color: #fff; text-align: center; text-decoration: none; border-radius: 8px; font-weight: 600; font-size: 1rem; transition: background 0.3s;">
      View Order Details
    </a>
  </section>

<?php include 'includes/footer.php'; ?>
