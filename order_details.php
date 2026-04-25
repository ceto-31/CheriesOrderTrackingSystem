<?php
require_once 'includes/session_check.php';

$page_title = "Order Details";
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

// Get order items
$items_query = "SELECT * FROM order_items WHERE order_id = :order_id";
$items_stmt = $db->prepare($items_query);
$items_stmt->bindParam(':order_id', $order_id);
$items_stmt->execute();
$items = $items_stmt->fetchAll();

include 'includes/header.php';
?>

<!-- Sidebar -->
<aside>
  <div class="logo">🍴 DineClick</div>
  <div class="sidebar-menu">
    <a href="cart.php">My Cart</a>
    <a href="orders.php" class="active">My Orders</a>
    <a href="order_history.php">Order History</a>
    <a href="profile.php">Profile Settings</a>
    <a href="logout.php">Logout</a>
  </div>
</aside>

<!-- Main Content -->
<main>
  <header>
    <h3>Order Details</h3>
    <nav>
      <a href="index.php">Home</a>
      <a href="menu.php">Menu</a>
      <a href="orders.php" class="active">My Orders</a>
      <a href="profile.php">Profile</a>
    </nav>
  </header>

  <div style="padding: 30px; overflow-y: auto;">
    <div style="margin-bottom: 20px;">
      <a href="orders.php" style="color: #B76E09; text-decoration: none; font-weight: 600;">
        ← Back to Orders
      </a>
    </div>
    
    <div style="display: grid; grid-template-columns: 1fr 350px; gap: 30px;">
      <!-- Order Info -->
      <div>
        <div style="background: #fff; padding: 25px; border-radius: 12px; box-shadow: 0 2px 4px rgba(0,0,0,0.08); margin-bottom: 20px;">
          <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
            <h2 style="margin: 0;">Order #<?php echo $order['id']; ?></h2>
            <?php
            $status_colors = [
                'Pending' => '#ffc107',
                'Confirmed' => '#17a2b8',
                'Preparing' => '#fd7e14',
                'Out for Delivery' => '#007bff',
                'Delivered' => '#28a745',
                'Cancelled' => '#dc3545'
            ];
            $status_color = $status_colors[$order['status']] ?? '#6c757d';
            ?>
            <span style="padding: 8px 16px; background: <?php echo $status_color; ?>; color: #fff; border-radius: 20px; font-weight: 600;">
              <?php echo $order['status']; ?>
            </span>
          </div>
          
          <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px; padding: 15px; background: #f8f9fa; border-radius: 8px;">
            <div>
              <p style="margin: 0; color: #7D6E6E; font-size: 0.9rem;">Order Date</p>
              <p style="margin: 5px 0 0 0; font-weight: 600; color: #000;">
                <?php echo date('M d, Y - h:i A', strtotime($order['created_at'])); ?>
              </p>
            </div>
            <div>
              <p style="margin: 0; color: #7D6E6E; font-size: 0.9rem;">Payment Method</p>
              <p style="margin: 5px 0 0 0; font-weight: 600; color: #000;">
                <?php echo htmlspecialchars($order['payment_method']); ?>
              </p>
            </div>
            <div>
              <p style="margin: 0; color: #7D6E6E; font-size: 0.9rem;">Contact Number</p>
              <p style="margin: 5px 0 0 0; font-weight: 600; color: #000;">
                <?php echo htmlspecialchars($order['contact_number']); ?>
              </p>
            </div>
            <div>
              <p style="margin: 0; color: #7D6E6E; font-size: 0.9rem;">Delivery Address</p>
              <p style="margin: 5px 0 0 0; font-weight: 600; color: #000;">
                <?php echo htmlspecialchars($order['delivery_address']); ?>
              </p>
            </div>
          </div>
          
          <?php if (!empty($order['notes'])): ?>
            <div style="margin-top: 15px; padding: 15px; background: #fff3cd; border-left: 4px solid #ffc107; border-radius: 4px;">
              <p style="margin: 0; color: #856404; font-size: 0.9rem;"><strong>Notes:</strong></p>
              <p style="margin: 5px 0 0 0; color: #856404;">
                <?php echo nl2br(htmlspecialchars($order['notes'])); ?>
              </p>
            </div>
          <?php endif; ?>
        </div>
        
        <!-- Order Items -->
        <div style="background: #fff; padding: 25px; border-radius: 12px; box-shadow: 0 2px 4px rgba(0,0,0,0.08);">
          <h3 style="margin-bottom: 20px;">Order Items</h3>
          
          <?php foreach ($items as $item): ?>
            <div style="display: flex; justify-content: space-between; padding: 15px 0; border-bottom: 1px solid #f0f0f0;">
              <div style="flex: 1;">
                <h4 style="margin: 0; font-size: 1.1rem; color: #000;">
                  <?php echo htmlspecialchars($item['product_name']); ?>
                </h4>
                <p style="margin: 5px 0 0 0; color: #7D6E6E; font-size: 0.9rem;">
                  <?php echo format_price($item['price']); ?> × <?php echo $item['quantity']; ?>
                </p>
              </div>
              <div style="font-weight: 700; color: #B76E09; font-size: 1.1rem;">
                <?php echo format_price($item['subtotal']); ?>
              </div>
            </div>
          <?php endforeach; ?>
        </div>
      </div>
      
      <!-- Order Summary -->
      <div>
        <div style="background: #fff; padding: 20px; border-radius: 12px; box-shadow: 0 2px 4px rgba(0,0,0,0.08); position: sticky; top: 20px;">
          <h3 style="margin-bottom: 15px;">Order Summary</h3>
          
          <?php
          $subtotal = $order['total_amount'] - $order['delivery_fee'];
          ?>
          
          <div style="display: flex; justify-content: space-between; margin: 10px 0; color: #7D6E6E;">
            <span>Subtotal</span>
            <span><?php echo format_price($subtotal); ?></span>
          </div>
          <div style="display: flex; justify-content: space-between; margin: 10px 0; color: #7D6E6E;">
            <span>Delivery Fee</span>
            <span><?php echo format_price($order['delivery_fee']); ?></span>
          </div>
          <div style="display: flex; justify-content: space-between; margin: 15px 0 0 0; padding-top: 15px; border-top: 2px solid #B76E09; font-weight: 700; font-size: 1.3rem; color: #000;">
            <span>Total</span>
            <span style="color: #B76E09;"><?php echo format_price($order['total_amount']); ?></span>
          </div>
          
          <div style="margin-top: 20px; padding-top: 20px; border-top: 1px solid #f0f0f0;">
            <p style="margin: 0; color: #7D6E6E; font-size: 0.85rem; text-align: center;">
              Updated: <?php echo date('M d, Y - h:i A', strtotime($order['updated_at'])); ?>
            </p>
          </div>
        </div>
      </div>
    </div>
  </div>

<?php include 'includes/footer.php'; ?>

<style>
@media (max-width: 768px) {
  div[style*="grid-template-columns: 1fr 350px"] {
    grid-template-columns: 1fr !important;
  }
}
</style>
