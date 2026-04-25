<?php
require_once '../includes/admin_check.php';

$page_title = "View Order";
$database = new Database();
$db = $database->getConnection();

$order_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

// Get order details
$query = "SELECT o.*, u.first_name, u.last_name, u.email, u.phone FROM orders o
          JOIN users u ON o.user_id = u.id
          WHERE o.id = :id";
$stmt = $db->prepare($query);
$stmt->bindParam(':id', $order_id);
$stmt->execute();

if ($stmt->rowCount() == 0) {
    $_SESSION['error'] = "Order not found.";
    redirect(SITE_URL . 'admin/orders_manage.php');
}

$order = $stmt->fetch();

// Get order items
$items_query = "SELECT * FROM order_items WHERE order_id = :order_id";
$items_stmt = $db->prepare($items_query);
$items_stmt->bindParam(':order_id', $order_id);
$items_stmt->execute();
$items = $items_stmt->fetchAll();

include '../includes/header.php';
?>

<?php $active_nav = 'orders_manage.php'; include '../includes/admin_nav.php'; ?>

  <div style="padding: 30px; overflow-y: auto;">
    <div style="margin-bottom: 20px;">
      <a href="orders_manage.php" style="color: #B76E09; text-decoration: none; font-weight: 600;">
        ← Back to Orders
      </a>
    </div>
    
    <div style="display: grid; grid-template-columns: 2fr 1fr; gap: 30px;">
      <!-- Order Info -->
      <div>
        <div style="background: #fff; padding: 25px; border-radius: 12px; box-shadow: 0 2px 4px rgba(0,0,0,0.08); margin-bottom: 20px;">
          <h2 style="margin-bottom: 20px;">Order #<?php echo $order['id']; ?></h2>
          
          <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px; padding: 15px; background: #f8f9fa; border-radius: 8px; margin-bottom: 20px;">
            <div>
              <p style="margin: 0; color: #7D6E6E; font-size: 0.9rem;">Customer Name</p>
              <p style="margin: 5px 0 0 0; font-weight: 600; color: #000;">
                <?php echo htmlspecialchars($order['first_name'] . ' ' . $order['last_name']); ?>
              </p>
            </div>
            <div>
              <p style="margin: 0; color: #7D6E6E; font-size: 0.9rem;">Email</p>
              <p style="margin: 5px 0 0 0; font-weight: 600; color: #000;">
                <?php echo htmlspecialchars($order['email']); ?>
              </p>
            </div>
            <div>
              <p style="margin: 0; color: #7D6E6E; font-size: 0.9rem;">Contact Number</p>
              <p style="margin: 5px 0 0 0; font-weight: 600; color: #000;">
                <?php echo htmlspecialchars($order['contact_number']); ?>
              </p>
            </div>
            <div>
              <p style="margin: 0; color: #7D6E6E; font-size: 0.9rem;">Payment Method</p>
              <p style="margin: 5px 0 0 0; font-weight: 600; color: #000;">
                <?php echo htmlspecialchars($order['payment_method']); ?>
              </p>
            </div>
            <div style="grid-column: 1 / -1;">
              <p style="margin: 0; color: #7D6E6E; font-size: 0.9rem;">Delivery Address</p>
              <p style="margin: 5px 0 0 0; font-weight: 600; color: #000;">
                <?php echo nl2br(htmlspecialchars($order['delivery_address'])); ?>
              </p>
            </div>
          </div>
          
          <?php if (!empty($order['notes'])): ?>
            <div style="padding: 15px; background: #fff3cd; border-left: 4px solid #ffc107; border-radius: 4px; margin-bottom: 20px;">
              <p style="margin: 0; color: #856404; font-size: 0.9rem;"><strong>Customer Notes:</strong></p>
              <p style="margin: 5px 0 0 0; color: #856404;">
                <?php echo nl2br(htmlspecialchars($order['notes'])); ?>
              </p>
            </div>
          <?php endif; ?>
          
          <div style="display: flex; gap: 10px; align-items: center;">
            <label style="font-weight: 600; color: #000;">Update Status:</label>
            <select id="statusSelect" style="padding: 10px; border: 1px solid #ddd; border-radius: 8px; font-weight: 600; flex: 1; outline: none; cursor: pointer;">
              <option value="Pending" <?php echo $order['status'] == 'Pending' ? 'selected' : ''; ?>>Pending</option>
              <option value="Confirmed" <?php echo $order['status'] == 'Confirmed' ? 'selected' : ''; ?>>Confirmed</option>
              <option value="Preparing" <?php echo $order['status'] == 'Preparing' ? 'selected' : ''; ?>>Preparing</option>
              <option value="Out for Delivery" <?php echo $order['status'] == 'Out for Delivery' ? 'selected' : ''; ?>>Out for Delivery</option>
              <option value="Delivered" <?php echo $order['status'] == 'Delivered' ? 'selected' : ''; ?>>Delivered</option>
              <option value="Cancelled" <?php echo $order['status'] == 'Cancelled' ? 'selected' : ''; ?>>Cancelled</option>
            </select>
            <button id="updateStatusBtn" style="padding: 10px 20px; background: #B76E09; color: #fff; border: none; border-radius: 8px; font-weight: 600; cursor: pointer;">
              Update
            </button>
          </div>
        </div>
        
        <!-- Order Items -->
        <div style="background: #fff; padding: 25px; border-radius: 12px; box-shadow: 0 2px 4px rgba(0,0,0,0.08);">
          <h3 style="margin-bottom: 20px;">Order Items</h3>
          
          <table style="width: 100%; border-collapse: collapse;">
            <thead>
              <tr style="background: #f8f9fa;">
                <th style="padding: 12px; text-align: left; font-weight: 600;">Product</th>
                <th style="padding: 12px; text-align: left; font-weight: 600;">Price</th>
                <th style="padding: 12px; text-align: left; font-weight: 600;">Quantity</th>
                <th style="padding: 12px; text-align: left; font-weight: 600;">Subtotal</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($items as $item): ?>
                <tr style="border-bottom: 1px solid #f0f0f0;">
                  <td style="padding: 12px; font-weight: 600;"><?php echo htmlspecialchars($item['product_name']); ?></td>
                  <td style="padding: 12px;"><?php echo format_price($item['price']); ?></td>
                  <td style="padding: 12px;"><?php echo $item['quantity']; ?></td>
                  <td style="padding: 12px; font-weight: 700; color: #B76E09;">
                    <?php echo format_price($item['subtotal']); ?>
                  </td>
                </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
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
            <p style="margin: 5px 0; color: #7D6E6E; font-size: 0.85rem;">
              <strong>Created:</strong><br>
              <?php echo date('M d, Y - h:i A', strtotime($order['created_at'])); ?>
            </p>
            <p style="margin: 5px 0; color: #7D6E6E; font-size: 0.85rem;">
              <strong>Last Updated:</strong><br>
              <?php echo date('M d, Y - h:i A', strtotime($order['updated_at'])); ?>
            </p>
          </div>
          
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
          
          <div style="margin-top: 20px; padding: 15px; background: <?php echo $status_color; ?>; color: #fff; border-radius: 8px; text-align: center;">
            <p style="margin: 0; font-size: 0.85rem;">Current Status</p>
            <p style="margin: 5px 0 0 0; font-size: 1.2rem; font-weight: 700;">
              <?php echo $order['status']; ?>
            </p>
          </div>
        </div>
      </div>
    </div>
  </div>

<?php 
$additional_js = "
<script>
$('#updateStatusBtn').click(function() {
    const newStatus = $('#statusSelect').val();
    const orderId = {$order_id};
    
    Swal.fire({
        title: 'Update Order Status?',
        text: 'Change status to ' + newStatus + '?',
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#B76E09',
        cancelButtonColor: '#7D6E6E',
        confirmButtonText: 'Yes, update it'
    }).then((result) => {
        if (result.isConfirmed) {
            $.ajax({
                url: 'order_update_status.php',
                type: 'POST',
                data: {
                    order_id: orderId,
                    status: newStatus
                },
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Updated!',
                            text: 'Order status has been updated.',
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
</script>
";

include '../includes/footer.php'; 
?>
