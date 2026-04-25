<?php
require_once 'includes/session_check.php';

$page_title = "My Orders";
$database = new Database();
$db = $database->getConnection();

$user_id = $_SESSION['user_id'];

// Get active orders
$query = "SELECT * FROM orders WHERE user_id = :user_id 
          AND status NOT IN ('Delivered', 'Cancelled') 
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
    <h3>My Active Orders</h3>
    <nav>
      <a href="index.php">Home</a>
      <a href="menu.php">Menu</a>
      <a href="orders.php" class="active">My Orders</a>
      <a href="profile.php">Profile</a>
    </nav>
  </header>

  <div style="padding: 30px; overflow-y: auto;">
    <h2 style="margin-bottom: 25px; font-weight: 600;">Active Orders</h2>
    
    <?php if (count($orders) > 0): ?>
      <div style="display: flex; flex-direction: column; gap: 20px;">
        <?php foreach ($orders as $order): ?>
          <div style="background: #fff; padding: 20px; border-radius: 12px; box-shadow: 0 2px 4px rgba(0,0,0,0.08);">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 15px; padding-bottom: 15px; border-bottom: 1px solid #f0f0f0;">
              <div>
                <h3 style="margin: 0; font-size: 1.2rem; color: #000;">Order #<?php echo $order['id']; ?></h3>
                <p style="margin: 5px 0 0 0; color: #7D6E6E; font-size: 0.9rem;">
                  Placed on <?php echo date('M d, Y - h:i A', strtotime($order['created_at'])); ?>
                </p>
              </div>
              <div>
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
                <span style="padding: 8px 16px; background: <?php echo $status_color; ?>; color: #fff; border-radius: 20px; font-weight: 600; font-size: 0.9rem;">
                  <?php echo $order['status']; ?>
                </span>
              </div>
            </div>
            
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 15px; margin-bottom: 15px;">
              <div>
                <p style="margin: 0; color: #7D6E6E; font-size: 0.85rem;">Total Amount</p>
                <p style="margin: 5px 0 0 0; font-weight: 700; font-size: 1.1rem; color: #B76E09;">
                  <?php echo format_price($order['total_amount']); ?>
                </p>
              </div>
              <div>
                <p style="margin: 0; color: #7D6E6E; font-size: 0.85rem;">Payment Method</p>
                <p style="margin: 5px 0 0 0; font-weight: 600; color: #000;">
                  <?php echo htmlspecialchars($order['payment_method']); ?>
                </p>
              </div>
              <div>
                <p style="margin: 0; color: #7D6E6E; font-size: 0.85rem;">Delivery Address</p>
                <p style="margin: 5px 0 0 0; font-weight: 600; color: #000;">
                  <?php echo htmlspecialchars(substr($order['delivery_address'], 0, 50)) . (strlen($order['delivery_address']) > 50 ? '...' : ''); ?>
                </p>
              </div>
            </div>
            
            <div style="display: flex; gap: 10px;">
              <a href="order_details.php?id=<?php echo $order['id']; ?>" 
                 style="padding: 10px 20px; background: #B76E09; color: #fff; text-decoration: none; border-radius: 8px; font-weight: 600; font-size: 0.9rem;">
                View Details
              </a>
              <?php if ($order['status'] == 'Pending'): ?>
                <button class="cancel-order-btn" data-order-id="<?php echo $order['id']; ?>" 
                        style="padding: 10px 20px; background: #dc3545; color: #fff; border: none; border-radius: 8px; font-weight: 600; font-size: 0.9rem; cursor: pointer;">
                  Cancel Order
                </button>
              <?php endif; ?>
            </div>
          </div>
        <?php endforeach; ?>
      </div>
    <?php else: ?>
      <div style="text-align: center; padding: 60px 20px;">
        <p style="font-size: 1.2rem; color: #7D6E6E; margin-bottom: 20px;">No active orders</p>
        <a href="menu.php" style="display: inline-block; padding: 12px 30px; background: #B76E09; color: #fff; text-decoration: none; border-radius: 8px; font-weight: 600;">
          Browse Menu
        </a>
      </div>
    <?php endif; ?>
  </div>

<?php 
$additional_js = "
<script>
$(document).ready(function() {
    $('.cancel-order-btn').click(function() {
        const orderId = $(this).data('order-id');
        
        Swal.fire({
            title: 'Cancel Order?',
            text: 'Are you sure you want to cancel this order?',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#dc3545',
            cancelButtonColor: '#7D6E6E',
            confirmButtonText: 'Yes, cancel it'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: 'ajax/cancel_order.php',
                    type: 'POST',
                    data: { order_id: orderId },
                    dataType: 'json',
                    success: function(response) {
                        if (response.success) {
                            Swal.fire({
                                icon: 'success',
                                title: 'Order Cancelled',
                                text: 'Your order has been cancelled successfully.',
                                confirmButtonColor: '#B76E09'
                            }).then(() => {
                                location.reload();
                            });
                        } else {
                            Swal.fire({
                                icon: 'error',
                                title: 'Error',
                                text: response.message
                            });
                        }
                    }
                });
            }
        });
    });
});
</script>
";

include 'includes/footer.php'; 
?>
