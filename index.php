<?php
require_once 'includes/session_check.php';

$page_title = "Home";
$database = new Database();
$db = $database->getConnection();

// Get user statistics
$user_id = $_SESSION['user_id'];

// Count active orders
$active_query = "SELECT COUNT(*) as count FROM orders WHERE user_id = :user_id 
                 AND status NOT IN ('Delivered', 'Cancelled')";
$active_stmt = $db->prepare($active_query);
$active_stmt->bindParam(':user_id', $user_id);
$active_stmt->execute();
$active_orders = $active_stmt->fetch()['count'];

// Get pending payment amount
$pending_query = "SELECT SUM(total_amount) as total FROM orders WHERE user_id = :user_id 
                  AND status = 'Pending'";
$pending_stmt = $db->prepare($pending_query);
$pending_stmt->bindParam(':user_id', $user_id);
$pending_stmt->execute();
$pending_amount = $pending_stmt->fetch()['total'] ?? 0;

// Get last order date
$last_query = "SELECT created_at FROM orders WHERE user_id = :user_id 
               ORDER BY created_at DESC LIMIT 1";
$last_stmt = $db->prepare($last_query);
$last_stmt->bindParam(':user_id', $user_id);
$last_stmt->execute();
$last_order = $last_stmt->fetch();
$last_order_date = $last_order ? date('M d, Y', strtotime($last_order['created_at'])) : 'No orders yet';

include 'includes/header.php';
include 'includes/sidebar.php';
?>

<!-- Main Content -->
<main>
  <header>
    <h3>Welcome, <?php echo htmlspecialchars(explode(' ', $_SESSION['user_name'])[0]); ?>!</h3>
    <nav>
      <a href="index.php" class="active">Home</a>
      <a href="menu.php">Menu</a>
      <a href="orders.php">My Orders</a>
      <a href="profile.php">Profile</a>
    </nav>
  </header>

  <div class="dashboard" style="flex: 1; padding: 30px; overflow-y: auto;">
    <div class="welcome" style="font-size: 1.3rem; font-weight: 500; margin-bottom: 25px;">
      Hello, <?php echo htmlspecialchars(explode(' ', $_SESSION['user_name'])[0]); ?>! Here's your quick summary:
    </div>
    
    <div class="cards" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 20px;">
      <div class="card" style="background: #fff; padding: 25px; border-radius: 12px; box-shadow: 0 2px 4px rgba(0,0,0,0.08); text-align: center; transition: transform 0.3s ease, box-shadow 0.3s ease;">
        <h4 style="margin: 0; color: #000; font-size: 1.1rem; margin-bottom: 6px;">Active Orders</h4>
        <p style="color: #7D6E6E; font-size: 2rem; font-weight: 700; margin: 10px 0;"><?php echo $active_orders; ?></p>
      </div>
      
      <div class="card" style="background: #fff; padding: 25px; border-radius: 12px; box-shadow: 0 2px 4px rgba(0,0,0,0.08); text-align: center; transition: transform 0.3s ease, box-shadow 0.3s ease;">
        <h4 style="margin: 0; color: #000; font-size: 1.1rem; margin-bottom: 6px;">Pending Payments</h4>
        <p style="color: #B76E09; font-size: 1.5rem; font-weight: 700; margin: 10px 0;"><?php echo format_price($pending_amount); ?></p>
      </div>
      
      <div class="card" style="background: #fff; padding: 25px; border-radius: 12px; box-shadow: 0 2px 4px rgba(0,0,0,0.08); text-align: center; transition: transform 0.3s ease, box-shadow 0.3s ease;">
        <h4 style="margin: 0; color: #000; font-size: 1.1rem; margin-bottom: 6px;">Last Order</h4>
        <p style="color: #7D6E6E; font-size: 0.95rem; margin: 10px 0;"><?php echo $last_order_date; ?></p>
      </div>
      
      <div class="card" style="background: #fff; padding: 25px; border-radius: 12px; box-shadow: 0 2px 4px rgba(0,0,0,0.08); text-align: center; transition: transform 0.3s ease, box-shadow 0.3s ease;">
        <h4 style="margin: 0; color: #000; font-size: 1.1rem; margin-bottom: 6px;">Browse Menu</h4>
        <p style="color: #7D6E6E; font-size: 0.95rem; margin: 10px 0;">
          <a href="menu.php" style="color: #B76E09; text-decoration: none; font-weight: 600;">Explore Dishes →</a>
        </p>
      </div>
    </div>
  </div>

<?php include 'includes/footer.php'; ?>

<style>
.card:hover {
  transform: translateY(-5px);
  box-shadow: 0 6px 14px rgba(0,0,0,0.12);
}
</style>
