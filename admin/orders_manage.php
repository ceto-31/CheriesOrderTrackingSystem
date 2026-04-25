<?php
require_once '../includes/admin_check.php';

$page_title = "Manage Orders";
$database = new Database();
$db = $database->getConnection();

// Get filter
$status_filter = isset($_GET['status']) ? clean_input($_GET['status']) : '';

// Build query
$query = "SELECT o.*, u.first_name, u.last_name, u.email FROM orders o
          JOIN users u ON o.user_id = u.id";

if (!empty($status_filter)) {
    $query .= " WHERE o.status = :status";
}

$query .= " ORDER BY o.created_at DESC";

$stmt = $db->prepare($query);

if (!empty($status_filter)) {
    $stmt->bindParam(':status', $status_filter);
}

$stmt->execute();
$orders = $stmt->fetchAll();

include '../includes/header.php';
?>

<!-- Sidebar -->
<aside>
  <div class="logo">🍴 DineClick Admin</div>
  <div class="sidebar-menu">
    <a href="dashboard.php">Dashboard</a>
    <a href="products.php">Products</a>
    <a href="orders_manage.php" class="active">Manage Orders</a>
    <a href="categories.php">Categories</a>
    <a href="support_tickets.php">Support Tickets</a>
    <a href="reports.php">Sales Reports</a>
    <a href="../logout.php">Logout</a>
  </div>
</aside>

<!-- Main Content -->
<main>
  <header>
    <h3>Manage Orders</h3>
    <nav>
      <a href="dashboard.php">Dashboard</a>
      <a href="products.php">Products</a>
      <a href="orders_manage.php" class="active">Orders</a>
      <a href="reports.php">Reports</a>
    </nav>
  </header>

  <div style="flex: 1; padding: 30px; overflow-y: auto;">
    <?php if (isset($_SESSION['success'])): ?>
      <div class="alert alert-success"><?php echo $_SESSION['success']; unset($_SESSION['success']); ?></div>
    <?php endif; ?>
    
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 25px;">
      <h2 style="margin: 0; font-weight: 600;">All Orders</h2>
      
      <form method="GET" style="display: flex; gap: 10px;">
        <select name="status" style="padding: 10px 15px; border: 1px solid #ddd; border-radius: 8px; outline: none;">
          <option value="">All Status</option>
          <option value="Pending" <?php echo $status_filter == 'Pending' ? 'selected' : ''; ?>>Pending</option>
          <option value="Confirmed" <?php echo $status_filter == 'Confirmed' ? 'selected' : ''; ?>>Confirmed</option>
          <option value="Preparing" <?php echo $status_filter == 'Preparing' ? 'selected' : ''; ?>>Preparing</option>
          <option value="Out for Delivery" <?php echo $status_filter == 'Out for Delivery' ? 'selected' : ''; ?>>Out for Delivery</option>
          <option value="Delivered" <?php echo $status_filter == 'Delivered' ? 'selected' : ''; ?>>Delivered</option>
          <option value="Cancelled" <?php echo $status_filter == 'Cancelled' ? 'selected' : ''; ?>>Cancelled</option>
        </select>
        <button type="submit" style="padding: 10px 20px; background: #B76E09; color: #fff; border: none; border-radius: 8px; cursor: pointer; font-weight: 600;">
          Filter
        </button>
        <?php if (!empty($status_filter)): ?>
          <a href="orders_manage.php" style="padding: 10px 20px; background: #7D6E6E; color: #fff; text-decoration: none; border-radius: 8px; font-weight: 600;">
            Clear
          </a>
        <?php endif; ?>
      </form>
    </div>
    
    <div style="background: #fff; border-radius: 12px; box-shadow: 0 2px 4px rgba(0,0,0,0.08); overflow-x: auto;">
      <table style="width: 100%; border-collapse: collapse;">
        <thead>
          <tr style="background: #f8f9fa;">
            <th style="padding: 15px; text-align: left; font-weight: 600;">Order ID</th>
            <th style="padding: 15px; text-align: left; font-weight: 600;">Customer</th>
            <th style="padding: 15px; text-align: left; font-weight: 600;">Total</th>
            <th style="padding: 15px; text-align: left; font-weight: 600;">Payment</th>
            <th style="padding: 15px; text-align: left; font-weight: 600;">Status</th>
            <th style="padding: 15px; text-align: left; font-weight: 600;">Date</th>
            <th style="padding: 15px; text-align: left; font-weight: 600;">Actions</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($orders as $order): ?>
            <tr style="border-bottom: 1px solid #f0f0f0;">
              <td style="padding: 15px; font-weight: 600;">#<?php echo $order['id']; ?></td>
              <td style="padding: 15px;">
                <div style="font-weight: 600;"><?php echo htmlspecialchars($order['first_name'] . ' ' . $order['last_name']); ?></div>
                <div style="font-size: 0.85rem; color: #7D6E6E;"><?php echo htmlspecialchars($order['email']); ?></div>
              </td>
              <td style="padding: 15px; font-weight: 600; color: #B76E09;">
                <?php echo format_price($order['total_amount']); ?>
              </td>
              <td style="padding: 15px; color: #7D6E6E;">
                <?php echo htmlspecialchars($order['payment_method']); ?>
              </td>
              <td style="padding: 15px;">
                <select class="status-select" data-order-id="<?php echo $order['id']; ?>" 
                        style="padding: 8px 12px; border: 1px solid #ddd; border-radius: 5px; font-weight: 600; outline: none; cursor: pointer;">
                  <option value="Pending" <?php echo $order['status'] == 'Pending' ? 'selected' : ''; ?>>Pending</option>
                  <option value="Confirmed" <?php echo $order['status'] == 'Confirmed' ? 'selected' : ''; ?>>Confirmed</option>
                  <option value="Preparing" <?php echo $order['status'] == 'Preparing' ? 'selected' : ''; ?>>Preparing</option>
                  <option value="Out for Delivery" <?php echo $order['status'] == 'Out for Delivery' ? 'selected' : ''; ?>>Out for Delivery</option>
                  <option value="Delivered" <?php echo $order['status'] == 'Delivered' ? 'selected' : ''; ?>>Delivered</option>
                  <option value="Cancelled" <?php echo $order['status'] == 'Cancelled' ? 'selected' : ''; ?>>Cancelled</option>
                </select>
              </td>
              <td style="padding: 15px; color: #7D6E6E; font-size: 0.9rem;">
                <?php echo date('M d, Y<\b\r>h:i A', strtotime($order['created_at'])); ?>
              </td>
              <td style="padding: 15px;">
                <a href="order_view.php?id=<?php echo $order['id']; ?>" 
                   style="color: #B76E09; text-decoration: none; font-weight: 600;">
                  View Details →
                </a>
              </td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </div>

<?php 
$additional_js = "
<script>
$('.status-select').change(function() {
    const orderId = $(this).data('order-id');
    const newStatus = $(this).val();
    const selectElement = $(this);
    
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
                            confirmButtonColor: '#B76E09',
                            timer: 1500,
                            showConfirmButton: false
                        });
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: response.message
                        });
                        location.reload();
                    }
                },
                error: function() {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: 'An error occurred'
                    });
                    location.reload();
                }
            });
        } else {
            location.reload();
        }
    });
});
</script>
";

include '../includes/footer.php'; 
?>
