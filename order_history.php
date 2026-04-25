<?php
require_once 'includes/session_check.php';

$page_title = "Order History";
$database = new Database();
$db = $database->getConnection();

$user_id = $_SESSION['user_id'];

// Get completed/cancelled orders
$query = "SELECT * FROM orders WHERE user_id = :user_id 
          AND status IN ('Delivered', 'Cancelled') 
          ORDER BY created_at DESC";
$stmt = $db->prepare($query);
$stmt->bindParam(':user_id', $user_id);
$stmt->execute();
$orders = $stmt->fetchAll();

include 'includes/header.php';
include 'includes/sidebar.php';
?>

<!-- Main Content -->
<main>
  <header>
    <h3>Order History</h3>
    <nav>
      <a href="index.php">Home</a>
      <a href="menu.php">Menu</a>
      <a href="orders.php">My Orders</a>
      <a href="profile.php">Profile</a>
    </nav>
  </header>

  <div style="padding: 30px; overflow-y: auto;">
    <h2 style="margin-bottom: 25px; font-weight: 600;">Past Orders</h2>
    
    <?php if (count($orders) > 0): ?>
      <div style="display: flex; flex-direction: column; gap: 20px;">
        <?php foreach ($orders as $order): ?>
          <div style="background: #fff; padding: 20px; border-radius: 12px; box-shadow: 0 2px 4px rgba(0,0,0,0.08);">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 10px;">
              <div>
                <h3 style="margin: 0; font-size: 1.2rem; color: #000;">Order #<?php echo $order['id']; ?></h3>
                <p style="margin: 5px 0 0 0; color: #7D6E6E; font-size: 0.9rem;">
                  <?php echo date('M d, Y - h:i A', strtotime($order['created_at'])); ?>
                </p>
              </div>
              <div>
                <?php
                $status_color = $order['status'] == 'Delivered' ? '#28a745' : '#dc3545';
                ?>
                <span style="padding: 8px 16px; background: <?php echo $status_color; ?>; color: #fff; border-radius: 20px; font-weight: 600; font-size: 0.9rem;">
                  <?php echo $order['status']; ?>
                </span>
              </div>
            </div>
            
            <div style="display: flex; justify-content: space-between; align-items: center;">
              <div>
                <p style="margin: 0; color: #7D6E6E; font-size: 0.85rem;">Total Amount</p>
                <p style="margin: 5px 0 0 0; font-weight: 700; font-size: 1.1rem; color: #B76E09;">
                  <?php echo format_price($order['total_amount']); ?>
                </p>
              </div>
              <a href="order_details.php?id=<?php echo $order['id']; ?>" 
                 style="padding: 10px 20px; background: #B76E09; color: #fff; text-decoration: none; border-radius: 8px; font-weight: 600; font-size: 0.9rem;">
                View Details
              </a>
            </div>
          </div>
        <?php endforeach; ?>
      </div>
    <?php else: ?>
      <div style="text-align: center; padding: 60px 20px;">
        <p style="font-size: 1.2rem; color: #7D6E6E; margin-bottom: 20px;">No order history yet</p>
        <a href="menu.php" style="display: inline-block; padding: 12px 30px; background: #B76E09; color: #fff; text-decoration: none; border-radius: 8px; font-weight: 600;">
          Start Ordering
        </a>
      </div>
    <?php endif; ?>
  </div>

<?php include 'includes/footer.php'; ?>
