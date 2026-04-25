<?php
require_once '../includes/admin_check.php';

$page_title = "Sales Reports";
$database = new Database();
$db = $database->getConnection();

// Get filter dates
$start_date = isset($_GET['start_date']) ? $_GET['start_date'] : date('Y-m-01');
$end_date = isset($_GET['end_date']) ? $_GET['end_date'] : date('Y-m-t');

// Get sales summary
$summary_query = "SELECT 
                    COUNT(*) as total_orders,
                    SUM(CASE WHEN status = 'Delivered' THEN total_amount ELSE 0 END) as total_revenue,
                    SUM(CASE WHEN status = 'Cancelled' THEN 1 ELSE 0 END) as cancelled_orders,
                    AVG(CASE WHEN status = 'Delivered' THEN total_amount ELSE NULL END) as avg_order_value
                  FROM orders 
                  WHERE DATE(created_at) BETWEEN :start_date AND :end_date";
$summary_stmt = $db->prepare($summary_query);
$summary_stmt->bindParam(':start_date', $start_date);
$summary_stmt->bindParam(':end_date', $end_date);
$summary_stmt->execute();
$summary = $summary_stmt->fetch();

// Get top products
$top_products_query = "SELECT p.name, SUM(oi.quantity) as total_sold, SUM(oi.subtotal) as revenue
                       FROM order_items oi
                       JOIN products p ON oi.product_id = p.id
                       JOIN orders o ON oi.order_id = o.id
                       WHERE DATE(o.created_at) BETWEEN :start_date AND :end_date
                       AND o.status = 'Delivered'
                       GROUP BY oi.product_id
                       ORDER BY total_sold DESC
                       LIMIT 5";
$top_products_stmt = $db->prepare($top_products_query);
$top_products_stmt->bindParam(':start_date', $start_date);
$top_products_stmt->bindParam(':end_date', $end_date);
$top_products_stmt->execute();
$top_products = $top_products_stmt->fetchAll();

// Daily sales for chart
$daily_sales = [];
$daily_labels = [];

$period = new DatePeriod(
    new DateTime($start_date),
    new DateInterval('P1D'),
    (new DateTime($end_date))->modify('+1 day')
);

foreach ($period as $date) {
    $day = $date->format('Y-m-d');
    $day_label = $date->format('M d');
    
    $daily_query = "SELECT SUM(total_amount) as total FROM orders 
                    WHERE DATE(created_at) = :day AND status = 'Delivered'";
    $daily_stmt = $db->prepare($daily_query);
    $daily_stmt->bindParam(':day', $day);
    $daily_stmt->execute();
    $revenue = $daily_stmt->fetch()['total'] ?? 0;
    
    $daily_sales[] = $revenue;
    $daily_labels[] = $day_label;
}

include '../includes/header.php';
?>

<?php include '../includes/admin_nav.php'; ?>

  <div style="flex:1; padding:30px; overflow-y:auto;">
    <!-- Date Filter -->
    <div style="background: #fff; padding: 20px; border-radius: 12px; box-shadow: 0 2px 4px rgba(0,0,0,0.08); margin-bottom: 30px;">
      <form method="GET" style="display: flex; gap: 15px; align-items: end; flex-wrap: wrap;">
        <div>
          <label style="display: block; margin-bottom: 5px; font-weight: 600;">Start Date</label>
          <input type="date" name="start_date" value="<?php echo $start_date; ?>" 
                 style="padding: 10px; border: 1px solid #ddd; border-radius: 8px; outline: none;">
        </div>
        <div>
          <label style="display: block; margin-bottom: 5px; font-weight: 600;">End Date</label>
          <input type="date" name="end_date" value="<?php echo $end_date; ?>" 
                 style="padding: 10px; border: 1px solid #ddd; border-radius: 8px; outline: none;">
        </div>
        <button type="submit" style="padding: 10px 20px; background: #B76E09; color: #fff; border: none; border-radius: 8px; font-weight: 600; cursor: pointer;">
          Generate Report
        </button>
      </form>
    </div>
    
    <!-- Summary Cards -->
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 20px; margin-bottom: 30px;">
      <div style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); padding: 25px; border-radius: 12px; color: #fff; box-shadow: 0 4px 6px rgba(0,0,0,0.1);">
        <h4 style="margin: 0; font-size: 0.9rem; opacity: 0.9;">Total Orders</h4>
        <p style="margin: 10px 0 0 0; font-size: 2rem; font-weight: 700;"><?php echo $summary['total_orders']; ?></p>
      </div>
      
      <div style="background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%); padding: 25px; border-radius: 12px; color: #fff; box-shadow: 0 4px 6px rgba(0,0,0,0.1);">
        <h4 style="margin: 0; font-size: 0.9rem; opacity: 0.9;">Total Revenue</h4>
        <p style="margin: 10px 0 0 0; font-size: 2rem; font-weight: 700;"><?php echo format_price($summary['total_revenue'] ?? 0); ?></p>
      </div>
      
      <div style="background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%); padding: 25px; border-radius: 12px; color: #fff; box-shadow: 0 4px 6px rgba(0,0,0,0.1);">
        <h4 style="margin: 0; font-size: 0.9rem; opacity: 0.9;">Avg Order Value</h4>
        <p style="margin: 10px 0 0 0; font-size: 2rem; font-weight: 700;">
          <?php echo format_price($summary['avg_order_value'] ?? 0); ?>
        </p>
      </div>
      
      <div style="background: linear-gradient(135deg, #fa709a 0%, #fee140 100%); padding: 25px; border-radius: 12px; color: #fff; box-shadow: 0 4px 6px rgba(0,0,0,0.1);">
        <h4 style="margin: 0; font-size: 0.9rem; opacity: 0.9;">Cancelled Orders</h4>
        <p style="margin: 10px 0 0 0; font-size: 2rem; font-weight: 700;"><?php echo $summary['cancelled_orders']; ?></p>
      </div>
    </div>
    
    <!-- Daily Sales Chart -->
    <div style="background: #fff; padding: 25px; border-radius: 12px; box-shadow: 0 2px 4px rgba(0,0,0,0.08); margin-bottom: 30px;">
      <h3 style="margin-bottom: 20px;">Daily Sales</h3>
      <canvas id="dailySalesChart" style="max-height: 350px;"></canvas>
    </div>
    
    <!-- Top Products -->
    <div style="background: #fff; padding: 25px; border-radius: 12px; box-shadow: 0 2px 4px rgba(0,0,0,0.08);">
      <h3 style="margin-bottom: 20px;">Top Selling Products</h3>
      
      <?php if (count($top_products) > 0): ?>
        <table style="width: 100%; border-collapse: collapse;">
          <thead>
            <tr style="background: #f8f9fa;">
              <th style="padding: 12px; text-align: left; font-weight: 600;">Product</th>
              <th style="padding: 12px; text-align: left; font-weight: 600;">Units Sold</th>
              <th style="padding: 12px; text-align: left; font-weight: 600;">Revenue</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($top_products as $product): ?>
              <tr style="border-bottom: 1px solid #f0f0f0;">
                <td style="padding: 12px; font-weight: 600;"><?php echo htmlspecialchars($product['name']); ?></td>
                <td style="padding: 12px;"><?php echo $product['total_sold']; ?> units</td>
                <td style="padding: 12px; font-weight: 700; color: #B76E09;">
                  <?php echo format_price($product['revenue']); ?>
                </td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      <?php else: ?>
        <p style="text-align: center; color: #7D6E6E; padding: 40px 0;">No sales data for selected period</p>
      <?php endif; ?>
    </div>
  </div>

<?php 
$additional_js = "
<script>
// Daily Sales Chart
const ctx = document.getElementById('dailySalesChart').getContext('2d');
const dailySalesChart = new Chart(ctx, {
    type: 'bar',
    data: {
        labels: " . json_encode($daily_labels) . ",
        datasets: [{
            label: 'Daily Revenue (₱)',
            data: " . json_encode($daily_sales) . ",
            backgroundColor: 'rgba(183, 110, 9, 0.8)',
            borderColor: '#B76E09',
            borderWidth: 2,
            borderRadius: 5
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
