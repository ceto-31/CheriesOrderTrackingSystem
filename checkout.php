<?php
require_once 'includes/session_check.php';

$page_title = "Checkout";
$database = new Database();
$db = $database->getConnection();

$user_id = $_SESSION['user_id'];

// Get cart items
$query = "SELECT c.*, p.name, p.price, p.stock 
          FROM cart c 
          JOIN products p ON c.product_id = p.id 
          WHERE c.user_id = :user_id";
$stmt = $db->prepare($query);
$stmt->bindParam(':user_id', $user_id);
$stmt->execute();
$cart_items = $stmt->fetchAll();

if (count($cart_items) == 0) {
    $_SESSION['error'] = "Your cart is empty!";
    redirect(SITE_URL . 'cart.php');
}

// Calculate totals
$subtotal = 0;
foreach ($cart_items as $item) {
    $subtotal += $item['price'] * $item['quantity'];
}
$delivery_fee = 50.00;
$total = $subtotal + $delivery_fee;

// Get user info
$user_query = "SELECT * FROM users WHERE id = :id";
$user_stmt = $db->prepare($user_query);
$user_stmt->bindParam(':id', $user_id);
$user_stmt->execute();
$user = $user_stmt->fetch();

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $delivery_address = clean_input($_POST['delivery_address']);
    $contact_number = clean_input($_POST['contact_number']);
    $payment_method = clean_input($_POST['payment_method']);
    $notes = clean_input($_POST['notes']);
    
    if (empty($delivery_address) || empty($contact_number) || empty($payment_method)) {
        $error = "Please fill in all required fields.";
    } else {
        try {
            $db->beginTransaction();
            
            // Verify stock availability again
            foreach ($cart_items as $item) {
                if ($item['stock'] < $item['quantity']) {
                    throw new Exception("Insufficient stock for " . $item['name']);
                }
            }
            
            // Create order
            $order_query = "INSERT INTO orders (user_id, total_amount, delivery_fee, payment_method, delivery_address, contact_number, notes) 
                            VALUES (:user_id, :total_amount, :delivery_fee, :payment_method, :delivery_address, :contact_number, :notes)";
            $order_stmt = $db->prepare($order_query);
            $order_stmt->bindParam(':user_id', $user_id);
            $order_stmt->bindParam(':total_amount', $total);
            $order_stmt->bindParam(':delivery_fee', $delivery_fee);
            $order_stmt->bindParam(':payment_method', $payment_method);
            $order_stmt->bindParam(':delivery_address', $delivery_address);
            $order_stmt->bindParam(':contact_number', $contact_number);
            $order_stmt->bindParam(':notes', $notes);
            $order_stmt->execute();
            
            $order_id = $db->lastInsertId();
            
            // Insert order items and update stock
            foreach ($cart_items as $item) {
                $item_query = "INSERT INTO order_items (order_id, product_id, product_name, price, quantity, subtotal) 
                               VALUES (:order_id, :product_id, :product_name, :price, :quantity, :subtotal)";
                $item_stmt = $db->prepare($item_query);
                $item_subtotal = $item['price'] * $item['quantity'];
                $item_stmt->bindParam(':order_id', $order_id);
                $item_stmt->bindParam(':product_id', $item['product_id']);
                $item_stmt->bindParam(':product_name', $item['name']);
                $item_stmt->bindParam(':price', $item['price']);
                $item_stmt->bindParam(':quantity', $item['quantity']);
                $item_stmt->bindParam(':subtotal', $item_subtotal);
                $item_stmt->execute();
                
                // Update stock (real-time stock update)
                $stock_query = "UPDATE products SET stock = stock - :quantity WHERE id = :id";
                $stock_stmt = $db->prepare($stock_query);
                $stock_stmt->bindParam(':quantity', $item['quantity']);
                $stock_stmt->bindParam(':id', $item['product_id']);
                $stock_stmt->execute();
            }
            
            // Clear cart
            $clear_cart = "DELETE FROM cart WHERE user_id = :user_id";
            $clear_stmt = $db->prepare($clear_cart);
            $clear_stmt->bindParam(':user_id', $user_id);
            $clear_stmt->execute();
            
            // Create notification
            $notif_query = "INSERT INTO notifications (user_id, title, message) 
                            VALUES (:user_id, :title, :message)";
            $notif_stmt = $db->prepare($notif_query);
            $notif_title = "Order Placed Successfully";
            $notif_message = "Your order #" . $order_id . " has been placed and is being processed.";
            $notif_stmt->bindParam(':user_id', $user_id);
            $notif_stmt->bindParam(':title', $notif_title);
            $notif_stmt->bindParam(':message', $notif_message);
            $notif_stmt->execute();
            
            $db->commit();
            
            $_SESSION['success'] = "Order placed successfully! Order ID: #" . $order_id;
            redirect(SITE_URL . 'order_details.php?id=' . $order_id);
            
        } catch (Exception $e) {
            $db->rollBack();
            $error = $e->getMessage();
        }
    }
}

include 'includes/header.php';
?>

<!-- Sidebar -->
<aside>
  <div class="logo">🍴 DineClick</div>
  <div class="sidebar-menu">
    <a href="cart.php">My Cart</a>
    <a href="orders.php">My Orders</a>
    <a href="order_history.php">Order History</a>
    <a href="profile.php">Profile Settings</a>
    <a href="logout.php">Logout</a>
  </div>
</aside>

<!-- Main Content -->
<main>
  <header>
    <h3>Checkout</h3>
    <nav>
      <a href="index.php">Home</a>
      <a href="menu.php">Menu</a>
      <a href="cart.php">Cart</a>
      <a href="orders.php">My Orders</a>
    </nav>
  </header>

  <div style="padding: 30px; overflow-y: auto;">
    <?php if (!empty($error)): ?>
      <div class="alert alert-error"><?php echo $error; ?></div>
    <?php endif; ?>
    
    <div style="display: grid; grid-template-columns: 1fr 400px; gap: 30px;">
      <!-- Checkout Form -->
      <div style="background: #fff; padding: 25px; border-radius: 12px; box-shadow: 0 2px 4px rgba(0,0,0,0.08);">
        <h2 style="margin-bottom: 20px;">Delivery Information</h2>
        
        <form method="POST" action="">
          <div style="margin-bottom: 20px;">
            <label style="display: block; margin-bottom: 8px; font-weight: 600; color: #000;">Delivery Address *</label>
            <textarea name="delivery_address" rows="3" required 
                      style="width: 100%; padding: 12px; border: 1px solid #ddd; border-radius: 8px; font-family: 'Poppins', sans-serif; resize: vertical;"><?php echo htmlspecialchars($user['address'] ?? ''); ?></textarea>
          </div>
          
          <div style="margin-bottom: 20px;">
            <label style="display: block; margin-bottom: 8px; font-weight: 600; color: #000;">Contact Number *</label>
            <input type="tel" name="contact_number" required 
                   value="<?php echo htmlspecialchars($user['phone'] ?? ''); ?>"
                   style="width: 100%; padding: 12px; border: 1px solid #ddd; border-radius: 8px;">
          </div>
          
          <div style="margin-bottom: 20px;">
            <label style="display: block; margin-bottom: 8px; font-weight: 600; color: #000;">Payment Method *</label>
            <select name="payment_method" required 
                    style="width: 100%; padding: 12px; border: 1px solid #ddd; border-radius: 8px;">
              <option value="">Select payment method</option>
              <option value="Cash on Delivery">Cash on Delivery</option>
              <option value="GCash">GCash</option>
              <option value="PayMaya">PayMaya</option>
              <option value="Bank Transfer">Bank Transfer</option>
            </select>
          </div>
          
          <div style="margin-bottom: 20px;">
            <label style="display: block; margin-bottom: 8px; font-weight: 600; color: #000;">Order Notes (Optional)</label>
            <textarea name="notes" rows="3" 
                      placeholder="Any special instructions for your order?"
                      style="width: 100%; padding: 12px; border: 1px solid #ddd; border-radius: 8px; font-family: 'Poppins', sans-serif; resize: vertical;"></textarea>
          </div>
          
          <button type="submit" 
                  style="width: 100%; padding: 14px; background: #B76E09; color: #fff; border: none; border-radius: 8px; font-weight: 600; font-size: 1.1rem; cursor: pointer;">
            Place Order
          </button>
        </form>
      </div>
      
      <!-- Order Summary -->
      <div>
        <div style="background: #fff; padding: 20px; border-radius: 12px; box-shadow: 0 2px 4px rgba(0,0,0,0.08); margin-bottom: 20px;">
          <h3 style="margin-bottom: 15px;">Order Summary</h3>
          
          <div style="max-height: 300px; overflow-y: auto; margin-bottom: 15px;">
            <?php foreach ($cart_items as $item): ?>
              <div style="display: flex; justify-content: space-between; padding: 10px 0; border-bottom: 1px solid #f0f0f0;">
                <div>
                  <div style="font-weight: 600; color: #000;"><?php echo htmlspecialchars($item['name']); ?></div>
                  <div style="font-size: 0.85rem; color: #7D6E6E;">Qty: <?php echo $item['quantity']; ?> × <?php echo format_price($item['price']); ?></div>
                </div>
                <div style="font-weight: 600; color: #B76E09;">
                  <?php echo format_price($item['price'] * $item['quantity']); ?>
                </div>
              </div>
            <?php endforeach; ?>
          </div>
          
          <div style="display: flex; justify-content: space-between; margin: 8px 0; color: #7D6E6E;">
            <span>Subtotal</span>
            <span><?php echo format_price($subtotal); ?></span>
          </div>
          <div style="display: flex; justify-content: space-between; margin: 8px 0; color: #7D6E6E;">
            <span>Delivery Fee</span>
            <span><?php echo format_price($delivery_fee); ?></span>
          </div>
          <div style="display: flex; justify-content: space-between; margin: 12px 0 0 0; padding-top: 12px; border-top: 2px solid #B76E09; font-weight: 700; font-size: 1.2rem; color: #000;">
            <span>Total</span>
            <span style="color: #B76E09;"><?php echo format_price($total); ?></span>
          </div>
        </div>
        
        <a href="cart.php" style="display: block; text-align: center; padding: 12px; background: #7D6E6E; color: #fff; text-decoration: none; border-radius: 8px; font-weight: 600;">
          ← Back to Cart
        </a>
      </div>
    </div>
  </div>

<?php include 'includes/footer.php'; ?>

<style>
@media (max-width: 768px) {
  div[style*="grid-template-columns: 1fr 400px"] {
    grid-template-columns: 1fr !important;
  }
}
</style>
