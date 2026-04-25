<?php
require_once '../includes/admin_check.php';

$page_title = "Admin Dashboard";
$database = new Database();
$db = $database->getConnection();

// Get statistics
$total_orders_query = "SELECT COUNT(*) as count FROM orders";
$total_orders = $db->query($total_orders_query)->fetch()['count'];

$total_revenue_query = "SELECT SUM(total_amount) as total FROM orders WHERE status = 'Delivered'";
$total_revenue = $db->query($total_revenue_query)->fetch()['total'] ?? 0;

$pending_orders_query = "SELECT COUNT(*) as count FROM orders WHERE status = 'Pending'";
$pending_orders = $db->query($pending_orders_query)->fetch()['count'];

$total_products_query = "SELECT COUNT(*) as count FROM products";
$total_products = $db->query($total_products_query)->fetch()['count'];

// Get monthly revenue for chart (last 6 months)
$monthly_revenue = [];
$monthly_labels = [];

for ($i = 5; $i >= 0; $i--) {
    $month = date('Y-m', strtotime("-$i months"));
    $month_label = date('M Y', strtotime("-$i months"));
    
    $revenue_query = "SELECT SUM(total_amount) as total FROM orders 
                      WHERE DATE_FORMAT(created_at, '%Y-%m') = :month 
                      AND status = 'Delivered'";
    $stmt = $db->prepare($revenue_query);
    $stmt->bindParam(':month', $month);
    $stmt->execute();
    $revenue = $stmt->fetch()['total'] ?? 0;
    
    $monthly_revenue[] = $revenue;
    $monthly_labels[] = $month_label;
}

// Get recent orders
$recent_orders_query = "SELECT o.*, u.first_name, u.last_name FROM orders o
                        JOIN users u ON o.user_id = u.id
                        ORDER BY o.created_at DESC LIMIT 5";
$recent_orders = $db->query($recent_orders_query)->fetchAll();

include '../includes/header.php';
?>

<!-- Sidebar -->
<aside>
  <div class="logo">🍴 DineClick Admin</div>
  <div class="sidebar-menu">
    <a href="dashboard.php" class="active">Dashboard</a>
    <a href="products.php">Products</a>
    <a href="orders_manage.php">Manage Orders</a>
    <a href="categories.php">Categories</a>
    <a href="support_tickets.php">Support Tickets</a>
    <a href="reports.php">Sales Reports</a>
    <a href="../logout.php">Logout</a>
  </div>
</aside>

<!-- Main Content -->
<main>
  <header>
    <h3>Admin Dashboard</h3>
    <nav>
      <a href="dashboard.php" class="active">Dashboard</a>
      <a href="products.php">Products</a>
      <a href="orders_manage.php">Orders</a>
      <a href="reports.php">Reports</a>
    </nav>
  </header>

  <div style="flex: 1; padding: 30px; overflow-y: auto;">
    <h2 style="margin-bottom: 25px; font-weight: 600;">Overview</h2>
    
    <!-- Stats Cards -->
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 20px; margin-bottom: 30px;">
      <div style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); padding: 25px; border-radius: 12px; color: #fff; box-shadow: 0 4px 6px rgba(0,0,0,0.1);">
        <h4 style="margin: 0; font-size: 0.9rem; opacity: 0.9;">Total Orders</h4>
        <p style="margin: 10px 0 0 0; font-size: 2rem; font-weight: 700;"><?php echo $total_orders; ?></p>
      </div>
      
      <div style="background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%); padding: 25px; border-radius: 12px; color: #fff; box-shadow: 0 4px 6px rgba(0,0,0,0.1);">
        <h4 style="margin: 0; font-size: 0.9rem; opacity: 0.9;">Total Revenue</h4>
        <p style="margin: 10px 0 0 0; font-size: 2rem; font-weight: 700;"><?php echo format_price($total_revenue); ?></p>
      </div>
      
      <div style="background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%); padding: 25px; border-radius: 12px; color: #fff; box-shadow: 0 4px 6px rgba(0,0,0,0.1);">
        <h4 style="margin: 0; font-size: 0.9rem; opacity: 0.9;">Pending Orders</h4>
        <p style="margin: 10px 0 0 0; font-size: 2rem; font-weight: 700;"><?php echo $pending_orders; ?></p>
      </div>
      
      <div style="background: linear-gradient(135deg, #43e97b 0%, #38f9d7 100%); padding: 25px; border-radius: 12px; color: #fff; box-shadow: 0 4px 6px rgba(0,0,0,0.1);">
        <h4 style="margin: 0; font-size: 0.9rem; opacity: 0.9;">Total Products</h4>
        <p style="margin: 10px 0 0 0; font-size: 2rem; font-weight: 700;"><?php echo $total_products; ?></p>
      </div>
    </div>
    
    <!-- Revenue Chart -->
    <div style="background: #fff; padding: 25px; border-radius: 12px; box-shadow: 0 2px 4px rgba(0,0,0,0.08); margin-bottom: 30px;">
      <h3 style="margin-bottom: 20px;">Monthly Revenue (Last 6 Months)</h3>
      <canvas id="revenueChart" style="max-height: 300px;"></canvas>
    </div>
    
    <!-- Recent Orders -->
    <div style="background: #fff; padding: 25px; border-radius: 12px; box-shadow: 0 2px 4px rgba(0,0,0,0.08);">
      <h3 style="margin-bottom: 20px;">Recent Orders</h3>
      
      <table style="width: 100%; border-collapse: collapse;">
        <thead>
          <tr style="background: #f8f9fa;">
            <th style="padding: 12px; text-align: left; font-weight: 600; color: #000;">Order ID</th>
            <th style="padding: 12px; text-align: left; font-weight: 600; color: #000;">Customer</th>
            <th style="padding: 12px; text-align: left; font-weight: 600; color: #000;">Total</th>
            <th style="padding: 12px; text-align: left; font-weight: 600; color: #000;">Status</th>
            <th style="padding: 12px; text-align: left; font-weight: 600; color: #000;">Date</th>
            <th style="padding: 12px; text-align: left; font-weight: 600; color: #000;">Action</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($recent_orders as $order): ?>
            <tr style="border-bottom: 1px solid #f0f0f0;">
              <td style="padding: 12px;">#<?php echo $order['id']; ?></td>
              <td style="padding: 12px;"><?php echo htmlspecialchars($order['first_name'] . ' ' . $order['last_name']); ?></td>
              <td style="padding: 12px; font-weight: 600; color: #B76E09;">
                <?php echo format_price($order['total_amount']); ?>
              </td>
              <td style="padding: 12px;">
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
                <span style="padding: 6px 12px; background: <?php echo $status_color; ?>; color: #fff; border-radius: 15px; font-size: 0.85rem; font-weight: 600;">
                  <?php echo $order['status']; ?>
                </span>
              </td>
              <td style="padding: 12px; color: #7D6E6E; font-size: 0.9rem;">
                <?php echo date('M d, Y', strtotime($order['created_at'])); ?>
              </td>
              <td style="padding: 12px;">
                <a href="order_view.php?id=<?php echo $order['id']; ?>" 
                   style="color: #B76E09; text-decoration: none; font-weight: 600; font-size: 0.9rem;">
                  View →
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
// Revenue Chart
const ctx = document.getElementById('revenueChart').getContext('2d');
const revenueChart = new Chart(ctx, {
    type: 'line',
    data: {
        labels: " . json_encode($monthly_labels) . ",
        datasets: [{
            label: 'Revenue (₱)',
            data: " . json_encode($monthly_revenue) . ",
            backgroundColor: 'rgba(183, 110, 9, 0.1)',
            borderColor: '#B76E09',
            borderWidth: 3,
            tension: 0.4,
            fill: true,
            pointBackgroundColor: '#B76E09',
            pointBorderColor: '#fff',
            pointBorderWidth: 2,
            pointRadius: 5,
            pointHoverRadius: 7
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: true,
        plugins: {
            legend: {
                display: true,
                position: 'top'
            },
            tooltip: {
                callbacks: {
                    label: function(context) {
                        return 'Revenue: ₱' + context.parsed.y.toFixed(2).replace(/\d(?=(\d{3})+\.)/g, '\$&,');
                    }
                }
            }
        },
        scales: {
            y: {
                beginAtZero: true,
                ticks: {
                    callback: function(value) {
                        return '₱' + value.toFixed(0);
                    }
                }
            }
        }
    }
});
</script>
";

include '../includes/footer.php'; 
?>

<style>
/* Responsive Dashboard Styles */
@media (max-width: 768px) {
  /* Make table scrollable on mobile */
  table {
    display: block;
    overflow-x: auto;
    white-space: nowrap;
    -webkit-overflow-scrolling: touch;
  }
  
  /* Adjust table font size */
  table th, table td {
    font-size: 0.85rem;
    padding: 8px !important;
  }
  
  /* Stack header on mobile */
  header h3 {
    font-size: 0.95rem;
  }
}

@media (max-width: 576px) {
  /* Smaller stats cards on mobile */
  div[style*="grid-template-columns"] {
    grid-template-columns: repeat(auto-fit, minmax(140px, 1fr)) !important;
  }
  
  /* Reduce padding */
  div[style*="padding: 30px"] {
    padding: 15px !important;
  }
  
  div[style*="padding: 25px"] {
    padding: 15px !important;
  }
}
</style>
