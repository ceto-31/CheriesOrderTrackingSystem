<?php
require_once '../includes/admin_check.php';

$page_title = "Admin Dashboard";
$db = (new Database())->getConnection();

// â”€â”€ KPI stats â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€
$total_orders   = (int)$db->query("SELECT COUNT(*) FROM orders")->fetchColumn();
$total_revenue  = (float)$db->query("SELECT COALESCE(SUM(total_amount),0) FROM orders WHERE status='Delivered'")->fetchColumn();
$pending_orders = (int)$db->query("SELECT COUNT(*) FROM orders WHERE status='Pending'")->fetchColumn();
$total_products = (int)$db->query("SELECT COUNT(*) FROM products")->fetchColumn();
$today_revenue  = (float)$db->query("SELECT COALESCE(SUM(total_amount),0) FROM orders WHERE status='Delivered' AND DATE(created_at)=CURDATE()")->fetchColumn();

// â”€â”€ Low-stock alerts â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€
$low_stock = $db->query(
    "SELECT id, name, stock, low_stock_threshold
     FROM   products
     WHERE  stock <= low_stock_threshold AND is_available = 1
     ORDER  BY stock ASC
     LIMIT  10"
)->fetchAll();

// â”€â”€ Monthly revenue â€“ last 6 months â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€
$monthly_labels  = [];
$monthly_revenue = [];
$monthly_orders  = [];

for ($i = 5; $i >= 0; $i--) {
    $m_start = date('Y-m-01', strtotime("-{$i} months"));
    $m_end   = date('Y-m-t',  strtotime("-{$i} months"));
    $m_label = date('M Y',    strtotime("-{$i} months"));

    $stmt = $db->prepare(
        "SELECT COALESCE(SUM(total_amount),0) AS rev, COUNT(*) AS cnt
         FROM   orders
         WHERE  status = 'Delivered'
           AND  created_at BETWEEN :s AND :e"
    );
    $stmt->execute([':s' => $m_start . ' 00:00:00', ':e' => $m_end . ' 23:59:59']);
    $row = $stmt->fetch();

    $monthly_labels[]  = $m_label;
    $monthly_revenue[] = round((float)$row['rev'], 2);
    $monthly_orders[]  = (int)$row['cnt'];
}

// â”€â”€ Daily revenue â€“ last 14 days â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€
$daily_labels  = [];
$daily_revenue = [];

for ($i = 13; $i >= 0; $i--) {
    $day = date('Y-m-d', strtotime("-{$i} days"));
    $stmt = $db->prepare(
        "SELECT COALESCE(SUM(total_amount),0) AS rev
         FROM   orders
         WHERE  status = 'Delivered' AND DATE(created_at) = :day"
    );
    $stmt->execute([':day' => $day]);
    $daily_labels[]  = date('M d', strtotime($day));
    $daily_revenue[] = round((float)$stmt->fetchColumn(), 2);
}

// â”€â”€ Top 6 products by revenue â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€
$top_prods = $db->query(
    "SELECT oi.product_name,
            SUM(oi.quantity)  AS total_qty,
            SUM(oi.subtotal)  AS total_rev
     FROM   order_items oi
     JOIN   orders o ON o.id = oi.order_id
     WHERE  o.status = 'Delivered'
     GROUP  BY oi.product_id, oi.product_name
     ORDER  BY total_rev DESC
     LIMIT  6"
)->fetchAll();

$top_labels   = array_column($top_prods, 'product_name');
$top_revenues = array_map('floatval', array_column($top_prods, 'total_rev'));

// â”€â”€ Recent 8 orders â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€
$recent_orders = $db->query(
    "SELECT o.id, o.total_amount, o.status, o.created_at,
            u.first_name, u.last_name
     FROM   orders o
     JOIN   users  u ON u.id = o.user_id
     ORDER  BY o.created_at DESC
     LIMIT  8"
)->fetchAll();

include '../includes/header.php';
?>

<!-- â•â•â•â•â•â•â• SIDEBAR â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â• -->
<aside>
  <div class="logo">ðŸ´ Cheries Admin</div>
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

<!-- â•â•â•â•â•â•â• MAIN â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â• -->
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

  <div style="flex:1; padding:30px; overflow-y:auto;">

    <!-- â”€â”€ KPI Cards â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€ -->
    <div style="display:grid; grid-template-columns:repeat(auto-fit,minmax(200px,1fr)); gap:18px; margin-bottom:28px;">

      <div style="background:linear-gradient(135deg,#667eea,#764ba2); padding:22px; border-radius:12px; color:#fff;">
        <div style="font-size:.82rem; opacity:.85; margin-bottom:6px;">Total Orders</div>
        <div style="font-size:2rem; font-weight:700;"><?php echo $total_orders; ?></div>
      </div>

      <div style="background:linear-gradient(135deg,#f093fb,#f5576c); padding:22px; border-radius:12px; color:#fff;">
        <div style="font-size:.82rem; opacity:.85; margin-bottom:6px;">Total Revenue</div>
        <div style="font-size:2rem; font-weight:700;"><?php echo format_price($total_revenue); ?></div>
      </div>

      <div style="background:linear-gradient(135deg,#43e97b,#38f9d7); padding:22px; border-radius:12px; color:#fff;">
        <div style="font-size:.82rem; opacity:.85; margin-bottom:6px;">Today's Revenue</div>
        <div style="font-size:2rem; font-weight:700;"><?php echo format_price($today_revenue); ?></div>
      </div>

      <div style="background:linear-gradient(135deg,#4facfe,#00f2fe); padding:22px; border-radius:12px; color:#fff;">
        <div style="font-size:.82rem; opacity:.85; margin-bottom:6px;">Pending Orders</div>
        <div style="font-size:2rem; font-weight:700;"><?php echo $pending_orders; ?></div>
      </div>

      <div style="background:linear-gradient(135deg,#fa8231,#f7b731); padding:22px; border-radius:12px; color:#fff;">
        <div style="font-size:.82rem; opacity:.85; margin-bottom:6px;">Total Products</div>
        <div style="font-size:2rem; font-weight:700;"><?php echo $total_products; ?></div>
      </div>

      <?php if (count($low_stock) > 0): ?>
      <div style="background:linear-gradient(135deg,#ff416c,#ff4b2b); padding:22px; border-radius:12px; color:#fff; cursor:pointer;"
           onclick="document.getElementById('lowStockSection').scrollIntoView({behavior:'smooth'})">
        <div style="font-size:.82rem; opacity:.85; margin-bottom:6px;">âš  Low Stock Alerts</div>
        <div style="font-size:2rem; font-weight:700;"><?php echo count($low_stock); ?></div>
        <div style="font-size:.75rem; opacity:.8;">Click to view</div>
      </div>
      <?php endif; ?>

    </div>

    <!-- â”€â”€ Charts Row â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€ -->
    <div style="display:grid; grid-template-columns:1fr 1fr; gap:22px; margin-bottom:28px;">

      <!-- Monthly Revenue -->
      <div style="background:#fff; padding:22px; border-radius:12px; box-shadow:0 2px 6px rgba(0,0,0,.08);">
        <h3 style="margin:0 0 4px; font-size:.95rem; font-weight:600;">Monthly Revenue</h3>
        <p style="margin:0 0 18px; font-size:.78rem; color:#7D6E6E;">Last 6 months Â· Delivered orders</p>
        <canvas id="monthlyChart" style="max-height:240px;"></canvas>
      </div>

      <!-- Daily Revenue -->
      <div style="background:#fff; padding:22px; border-radius:12px; box-shadow:0 2px 6px rgba(0,0,0,.08);">
        <h3 style="margin:0 0 4px; font-size:.95rem; font-weight:600;">Daily Revenue</h3>
        <p style="margin:0 0 18px; font-size:.78rem; color:#7D6E6E;">Last 14 days Â· Delivered orders</p>
        <canvas id="dailyChart" style="max-height:240px;"></canvas>
      </div>

    </div>

    <!-- â”€â”€ Top Products + Low-stock Row â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€ -->
    <div style="display:grid; grid-template-columns:1fr 1fr; gap:22px; margin-bottom:28px;">

      <!-- Top Products Doughnut -->
      <div style="background:#fff; padding:22px; border-radius:12px; box-shadow:0 2px 6px rgba(0,0,0,.08);">
        <h3 style="margin:0 0 18px; font-size:.95rem; font-weight:600;">Top Products by Revenue</h3>
        <canvas id="topProdChart" style="max-height:250px;"></canvas>
      </div>

      <!-- Low-stock alerts -->
      <div id="lowStockSection"
           style="background:#fff; padding:22px; border-radius:12px; box-shadow:0 2px 6px rgba(0,0,0,.08);">
        <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:14px;">
          <h3 style="margin:0; font-size:.95rem; font-weight:600;">âš  Low Stock Alerts</h3>
          <a href="products.php" style="font-size:.82rem; color:#B76E09; text-decoration:none; font-weight:600;">Manage â†’</a>
        </div>

        <?php if (empty($low_stock)): ?>
          <p style="color:#28a745; font-weight:600;">âœ“ All products have sufficient stock.</p>
        <?php else: ?>
          <div style="overflow-x:auto;">
            <table style="width:100%; border-collapse:collapse; font-size:.88rem;">
              <thead>
                <tr style="background:#fff8f0;">
                  <th style="padding:9px 12px; text-align:left; font-weight:600; color:#B76E09;">Product</th>
                  <th style="padding:9px 12px; text-align:center; font-weight:600; color:#B76E09;">Stock</th>
                  <th style="padding:9px 12px; text-align:center; font-weight:600; color:#B76E09;">Threshold</th>
                  <th style="padding:9px 12px; text-align:center; font-weight:600; color:#B76E09;">Level</th>
                </tr>
              </thead>
              <tbody>
                <?php foreach ($low_stock as $ls):
                  $pct = $ls['low_stock_threshold'] > 0
                       ? min(100, round($ls['stock'] / $ls['low_stock_threshold'] * 100))
                       : 0;
                  $bar_color = $ls['stock'] === 0 ? '#dc3545' : ($pct < 50 ? '#fd7e14' : '#ffc107');
                ?>
                  <tr style="border-bottom:1px solid #f5f5f5;">
                    <td style="padding:9px 12px; font-weight:600;">
                      <?php echo htmlspecialchars($ls['name'], ENT_QUOTES, 'UTF-8'); ?>
                    </td>
                    <td style="padding:9px 12px; text-align:center; color:<?php echo $ls['stock']==0?'#dc3545':'#fd7e14'; ?>; font-weight:700;">
                      <?php echo (int)$ls['stock']; ?>
                    </td>
                    <td style="padding:9px 12px; text-align:center; color:#7D6E6E;">
                      <?php echo (int)$ls['low_stock_threshold']; ?>
                    </td>
                    <td style="padding:9px 12px;">
                      <div style="background:#eee; border-radius:4px; height:7px; overflow:hidden;">
                        <div style="width:<?php echo $pct; ?>%; background:<?php echo $bar_color; ?>; height:100%;
                                    border-radius:4px; transition:width .4s;"></div>
                      </div>
                    </td>
                  </tr>
                <?php endforeach; ?>
              </tbody>
            </table>
          </div>
        <?php endif; ?>
      </div>

    </div>

    <!-- â”€â”€ Recent Orders â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€ -->
    <div style="background:#fff; padding:22px; border-radius:12px; box-shadow:0 2px 6px rgba(0,0,0,.08);">
      <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:18px;">
        <h3 style="margin:0; font-size:.95rem; font-weight:600;">Recent Orders</h3>
        <a href="orders_manage.php" style="font-size:.82rem; color:#B76E09; text-decoration:none; font-weight:600;">View All â†’</a>
      </div>

      <div style="overflow-x:auto;">
        <table style="width:100%; border-collapse:collapse; font-size:.9rem;">
          <thead>
            <tr style="background:#f8f9fa;">
              <th style="padding:11px 14px; text-align:left; font-weight:600;">Order ID</th>
              <th style="padding:11px 14px; text-align:left; font-weight:600;">Customer</th>
              <th style="padding:11px 14px; text-align:left; font-weight:600;">Total</th>
              <th style="padding:11px 14px; text-align:left; font-weight:600;">Status</th>
              <th style="padding:11px 14px; text-align:left; font-weight:600;">Date</th>
              <th style="padding:11px 14px; text-align:left; font-weight:600;">Action</th>
            </tr>
          </thead>
          <tbody>
            <?php
            $status_colors = [
              'Pending'          => '#ffc107',
              'Confirmed'        => '#17a2b8',
              'Preparing'        => '#fd7e14',
              'Out for Delivery' => '#007bff',
              'Delivered'        => '#28a745',
              'Cancelled'        => '#dc3545',
            ];
            foreach ($recent_orders as $o):
              $sc = $status_colors[$o['status']] ?? '#6c757d';
            ?>
              <tr style="border-bottom:1px solid #f5f5f5;">
                <td style="padding:11px 14px; font-weight:600;">#<?php echo (int)$o['id']; ?></td>
                <td style="padding:11px 14px;"><?php echo htmlspecialchars($o['first_name'].' '.$o['last_name'], ENT_QUOTES, 'UTF-8'); ?></td>
                <td style="padding:11px 14px; font-weight:600; color:#B76E09;"><?php echo format_price($o['total_amount']); ?></td>
                <td style="padding:11px 14px;">
                  <span style="padding:4px 11px; background:<?php echo $sc; ?>; color:#fff;
                               border-radius:12px; font-size:.78rem; font-weight:600;">
                    <?php echo htmlspecialchars($o['status'], ENT_QUOTES, 'UTF-8'); ?>
                  </span>
                </td>
                <td style="padding:11px 14px; color:#7D6E6E;"><?php echo date('M d, Y', strtotime($o['created_at'])); ?></td>
                <td style="padding:11px 14px;">
                  <a href="order_view.php?id=<?php echo (int)$o['id']; ?>"
                     style="color:#B76E09; text-decoration:none; font-weight:600;">View â†’</a>
                </td>
              </tr>
            <?php endforeach; ?>
            <?php if (empty($recent_orders)): ?>
              <tr><td colspan="6" style="padding:22px; text-align:center; color:#7D6E6E;">No orders yet.</td></tr>
            <?php endif; ?>
          </tbody>
        </table>
      </div>
    </div><!-- /recent orders -->

  </div><!-- /content pad -->
</main>

<?php
$additional_js = '
<script>
const PALETTE = ["#B76E09","#667eea","#f5576c","#43e97b","#4facfe","#fa8231","#a55eea","#17a2b8"];

// â”€â”€ Monthly revenue (line) â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€
new Chart(document.getElementById("monthlyChart"), {
  type: "line",
  data: {
    labels: ' . json_encode($monthly_labels) . ',
    datasets: [{
      label: "Revenue (â‚±)",
      data:  ' . json_encode($monthly_revenue) . ',
      borderColor: "#B76E09", backgroundColor: "rgba(183,110,9,.1)",
      borderWidth: 3, tension: 0.4, fill: true,
      pointBackgroundColor: "#B76E09", pointRadius: 5, pointHoverRadius: 7
    }, {
      label: "Orders",
      data:  ' . json_encode($monthly_orders) . ',
      borderColor: "#667eea", backgroundColor: "rgba(102,126,234,.08)",
      borderWidth: 2, tension: 0.4, fill: false,
      pointRadius: 4, yAxisID: "y1"
    }]
  },
  options: {
    responsive: true,
    interaction: { mode: "index", intersect: false },
    plugins: { legend: { position: "top" },
               tooltip: { callbacks: { label: c => c.datasetIndex===0
                 ? "â‚±"+c.parsed.y.toLocaleString("en-PH",{minimumFractionDigits:2})
                 : "Orders: "+c.parsed.y } } },
    scales: {
      y:  { beginAtZero:true, position:"left",  ticks:{ callback: v=>"â‚±"+v.toLocaleString("en-PH") } },
      y1: { beginAtZero:true, position:"right", grid:{ drawOnChartArea:false }, ticks:{ stepSize:1 } }
    }
  }
});

// â”€â”€ Daily revenue (bar) â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€
new Chart(document.getElementById("dailyChart"), {
  type: "bar",
  data: {
    labels: ' . json_encode($daily_labels) . ',
    datasets: [{
      label: "Revenue (â‚±)",
      data:  ' . json_encode($daily_revenue) . ',
      backgroundColor: "rgba(183,110,9,.75)",
      borderColor:     "#B76E09",
      borderWidth: 1,
      borderRadius: 5
    }]
  },
  options: {
    responsive: true,
    plugins: { legend: { display: false },
               tooltip: { callbacks: { label: c=>"â‚±"+c.parsed.y.toLocaleString("en-PH",{minimumFractionDigits:2}) } } },
    scales: { y: { beginAtZero:true, ticks:{ callback: v=>"â‚±"+v.toLocaleString("en-PH") } } }
  }
});

// â”€â”€ Top products (doughnut) â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€
new Chart(document.getElementById("topProdChart"), {
  type: "doughnut",
  data: {
    labels: ' . json_encode($top_labels) . ',
    datasets: [{
      data:            ' . json_encode($top_revenues) . ',
      backgroundColor: PALETTE,
      borderWidth: 2,
      hoverOffset: 6
    }]
  },
  options: {
    responsive: true,
    plugins: {
      legend: { position: "bottom", labels:{ font:{ size:11 } } },
      tooltip: { callbacks: { label: c=>" â‚±"+c.parsed.toLocaleString("en-PH",{minimumFractionDigits:2}) } }
    }
  }
});
</script>

<style>
@media(max-width:992px){
  div[style*="grid-template-columns:1fr 1fr"]{grid-template-columns:1fr !important;}
}
@media(max-width:768px){
  table{display:block;overflow-x:auto;}
  table th,table td{font-size:.8rem;padding:8px !important;}
}
@media(max-width:576px){
  div[style*="padding:30px"]{padding:14px !important;}
  div[style*="padding:22px"]{padding:14px !important;}
  div[style*="minmax(200px"]{grid-template-columns:repeat(auto-fit,minmax(140px,1fr)) !important;}
}
</style>
';

include '../includes/footer.php';
?>
