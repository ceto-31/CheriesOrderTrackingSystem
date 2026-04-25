<?php
/* =====================================================================
 * cashier/order_history.php
 * View-only list of orders processed by this cashier today.
 * ===================================================================== */
require_once '../includes/cashier_check.php';

$page_title  = "Today's Orders";
$db          = (new Database())->getConnection();
$cashier_id  = (int)$_SESSION['user_id'];

// ── Today's orders by this cashier ──────────────────────────────────
$stmt = $db->prepare(
    "SELECT o.id, o.customer_name, o.total_amount, o.payment_method,
            o.status, o.created_at,
            COUNT(oi.id) AS item_count
     FROM   orders o
     LEFT   JOIN order_items oi ON oi.order_id = o.id
     WHERE  o.order_source = 'cashier'
       AND  o.created_by   = :cid
       AND  DATE(o.created_at) = CURDATE()
     GROUP  BY o.id
     ORDER  BY o.created_at DESC"
);
$stmt->execute([':cid' => $cashier_id]);
$orders = $stmt->fetchAll(PDO::FETCH_ASSOC);

// ── Summary KPIs ─────────────────────────────────────────────────────
$kpi = $db->prepare(
    "SELECT COUNT(*)              AS total_orders,
            COALESCE(SUM(total_amount),0) AS total_revenue
     FROM   orders
     WHERE  order_source = 'cashier'
       AND  created_by   = :cid
       AND  DATE(created_at) = CURDATE()"
);
$kpi->execute([':cid' => $cashier_id]);
$kpi = $kpi->fetch(PDO::FETCH_ASSOC);

$status_colors = [
    'Pending'          => '#ffc107',
    'Confirmed'        => '#17a2b8',
    'Preparing'        => '#fd7e14',
    'Out for Delivery' => '#007bff',
    'Delivered'        => '#28a745',
    'Cancelled'        => '#dc3545',
];

include '../includes/header.php';
?>
<?php include '../includes/cashier_nav.php'; ?>

  <div style="flex:1; padding:28px; overflow-y:auto; background:#f5f0ea;">

    <h2 style="margin:0 0 20px; font-size:1.15rem; font-weight:700; color:#2d1a09;">
      <?php echo date('l, F j, Y'); ?> — Your Orders
    </h2>

    <!-- KPI Cards -->
    <div style="display:grid; grid-template-columns:repeat(auto-fit,minmax(180px,1fr)); gap:16px; margin-bottom:26px;">
      <div style="background:linear-gradient(135deg,#a66b27,#c8861e); padding:20px; border-radius:12px; color:#fff;">
        <div style="font-size:.8rem; opacity:.85; margin-bottom:6px;">Orders Today</div>
        <div style="font-size:2.2rem; font-weight:700;"><?php echo (int)$kpi['total_orders']; ?></div>
      </div>
      <div style="background:linear-gradient(135deg,#43e97b,#38f9d7); padding:20px; border-radius:12px; color:#fff;">
        <div style="font-size:.8rem; opacity:.85; margin-bottom:6px;">Revenue Today</div>
        <div style="font-size:2rem; font-weight:700;"><?php echo format_price($kpi['total_revenue']); ?></div>
      </div>
    </div>

    <!-- Orders Table -->
    <div style="background:#fff; border-radius:12px; box-shadow:0 2px 6px rgba(0,0,0,.07); overflow:hidden;">
      <div style="padding:16px 20px; border-bottom:1px solid #f0e8dd; display:flex; justify-content:space-between; align-items:center;">
        <span style="font-weight:600; color:#2d1a09;">Today's Order Log</span>
        <a href="order_entry.php"
           style="padding:8px 18px; background:#a66b27; color:#fff; text-decoration:none;
                  border-radius:8px; font-weight:600; font-size:.85rem;">+ New Order</a>
      </div>

      <div style="overflow-x:auto;">
        <table style="width:100%; border-collapse:collapse; font-size:.88rem;">
          <thead>
            <tr style="background:#fdf5ec;">
              <th style="padding:11px 16px; text-align:left; font-weight:600; color:#a66b27;">Order #</th>
              <th style="padding:11px 16px; text-align:left; font-weight:600; color:#a66b27;">Customer</th>
              <th style="padding:11px 16px; text-align:center; font-weight:600; color:#a66b27;">Items</th>
              <th style="padding:11px 16px; text-align:left; font-weight:600; color:#a66b27;">Payment</th>
              <th style="padding:11px 16px; text-align:right; font-weight:600; color:#a66b27;">Total</th>
              <th style="padding:11px 16px; text-align:center; font-weight:600; color:#a66b27;">Status</th>
              <th style="padding:11px 16px; text-align:left; font-weight:600; color:#a66b27;">Time</th>
            </tr>
          </thead>
          <tbody>
            <?php if (empty($orders)): ?>
              <tr>
                <td colspan="7" style="padding:40px; text-align:center; color:#bbb;">
                  <div style="font-size:2.5rem; margin-bottom:8px;">📋</div>
                  No orders processed today yet.
                </td>
              </tr>
            <?php else: ?>
              <?php foreach ($orders as $o):
                $sc = $status_colors[$o['status']] ?? '#6c757d';
              ?>
              <tr style="border-bottom:1px solid #f5f0ea;">
                <td style="padding:11px 16px; font-weight:700; color:#a66b27;">#<?php echo (int)$o['id']; ?></td>
                <td style="padding:11px 16px; font-weight:600; color:#2d1a09;">
                  <?php echo htmlspecialchars($o['customer_name'] ?: 'Walk-in', ENT_QUOTES, 'UTF-8'); ?>
                </td>
                <td style="padding:11px 16px; text-align:center; color:#7D6E6E;"><?php echo (int)$o['item_count']; ?></td>
                <td style="padding:11px 16px; color:#7D6E6E;"><?php echo htmlspecialchars($o['payment_method'], ENT_QUOTES, 'UTF-8'); ?></td>
                <td style="padding:11px 16px; text-align:right; font-weight:700; color:#2d1a09;">
                  <?php echo format_price($o['total_amount']); ?>
                </td>
                <td style="padding:11px 16px; text-align:center;">
                  <span style="padding:4px 12px; background:<?php echo $sc; ?>; color:#fff;
                               border-radius:12px; font-size:.78rem; font-weight:600; white-space:nowrap;">
                    <?php echo htmlspecialchars($o['status'], ENT_QUOTES, 'UTF-8'); ?>
                  </span>
                </td>
                <td style="padding:11px 16px; color:#7D6E6E; font-size:.82rem;">
                  <?php echo date('h:i A', strtotime($o['created_at'])); ?>
                </td>
              </tr>
              <?php endforeach; ?>
            <?php endif; ?>
          </tbody>
        </table>
      </div>
    </div><!-- /table card -->

  </div><!-- /content pad -->

<?php
$additional_js = '<style>body { background:#f5f0ea; }</style>';
include '../includes/footer.php';
?>
