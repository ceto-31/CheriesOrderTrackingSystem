<?php
require_once 'includes/session_check.php';

$page_title = "Track Order";
$db         = (new Database())->getConnection();
$user_id    = (int)$_SESSION['user_id'];
$order_id   = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// â”€â”€ Fetch order (user-scoped to prevent IDOR) â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€
$stmt = $db->prepare(
    "SELECT o.*, u.first_name, u.last_name
     FROM   orders o
     JOIN   users  u ON u.id = o.user_id
     WHERE  o.id = :id AND o.user_id = :uid"
);
$stmt->execute([':id' => $order_id, ':uid' => $user_id]);

if ($stmt->rowCount() === 0) {
    $_SESSION['error'] = "Order not found.";
    redirect(SITE_URL . 'orders.php');
}
$order = $stmt->fetch();

// â”€â”€ Fetch order items â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€
$items_stmt = $db->prepare(
    "SELECT product_name, price, quantity, subtotal FROM order_items WHERE order_id = :oid"
);
$items_stmt->execute([':oid' => $order_id]);
$order_items = $items_stmt->fetchAll();

// â”€â”€ Progress tracking â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€
// Each status maps to a step index 0â€“4
$step_map = [
    'Pending'          => 0,
    'Confirmed'        => 1,
    'Preparing'        => 2,
    'Out for Delivery' => 3,
    'Delivered'        => 4,
    'Cancelled'        => -1,
];
$current_step = $step_map[$order['status']] ?? 0;

// Progress milestones
$milestones = [
    ['icon' => 'ðŸ“‹', 'label' => 'Order Placed',    'desc' => 'We received your order'],
    ['icon' => 'âœ…', 'label' => 'Confirmed',        'desc' => 'Order confirmed by kitchen'],
    ['icon' => 'ðŸ‘¨â€ðŸ³', 'label' => 'Preparing',       'desc' => 'Chef is preparing your meal'],
    ['icon' => 'ðŸ›µ', 'label' => 'Out for Delivery', 'desc' => 'Rider is on the way'],
    ['icon' => 'ðŸŽ‰', 'label' => 'Delivered',        'desc' => 'Enjoy your meal!'],
];

include 'includes/header.php';
?>

<!-- â•â•â• SIDEBAR â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â• -->
<aside>
  <div class="logo">ðŸ´ <?php echo SITE_NAME; ?></div>
  <div class="sidebar-menu">
    <a href="cart.php">My Cart</a>
    <a href="orders.php" class="active">My Orders</a>
    <a href="order_history.php">Order History</a>
    <a href="notifications.php">Notifications</a>
    <a href="profile.php">Profile</a>
    <a href="logout.php">Logout</a>
  </div>
</aside>

<!-- â•â•â• MAIN â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â• -->
<main>
  <header>
    <h3>Track My Order</h3>
    <nav>
      <a href="index.php">Home</a>
      <a href="menu.php">Menu</a>
      <a href="orders.php" class="active">My Orders</a>
      <a href="profile.php">Profile</a>
    </nav>
  </header>

  <section style="flex:1; padding:30px; overflow-y:auto;">

    <!-- â”€â”€ Back + title â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€ -->
    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:24px; flex-wrap:wrap; gap:10px;">
      <h2 style="margin:0; font-size:1.35rem; color:#000;">
        Order <span style="color:#B76E09;">#<?php echo $order_id; ?></span>
      </h2>
      <a href="orders.php" style="color:#B76E09; text-decoration:none; font-weight:600; font-size:.9rem;">
        â† Back to Orders
      </a>
    </div>

    <!-- â”€â”€ CANCELLED banner â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€ -->
    <?php if ($order['status'] === 'Cancelled'): ?>
      <div style="background:#fdecea; padding:20px; border-radius:12px; border-left:4px solid #dc3545; margin-bottom:24px;">
        <h3 style="margin:0 0 8px; color:#c62828;">âŒ Order Cancelled</h3>
        <p style="margin:0; color:#c62828;">This order has been cancelled. Contact support if you need help.</p>
      </div>

    <!-- â”€â”€ DELIVERED banner â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€ -->
    <?php elseif ($order['status'] === 'Delivered'): ?>
      <div style="background:#e8f5e9; padding:20px; border-radius:12px; border-left:4px solid #28a745; margin-bottom:24px;">
        <h3 style="margin:0 0 8px; color:#1b5e20;">ðŸŽ‰ Order Delivered!</h3>
        <p style="margin:0; color:#1b5e20;">
          Delivered on <?php echo date('F d, Y \a\t h:i A', strtotime($order['updated_at'])); ?>.
          Thank you for ordering from <?php echo SITE_NAME; ?>!
        </p>
      </div>
    <?php endif; ?>

    <!-- â”€â”€ Progress Bar (hidden for Cancelled) â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€ -->
    <?php if ($order['status'] !== 'Cancelled'): ?>
    <div style="background:#fff; padding:28px; border-radius:14px; box-shadow:0 2px 8px rgba(0,0,0,.08); margin-bottom:24px;">
      <h4 style="margin:0 0 26px; font-size:.95rem; font-weight:600; color:#333;">Order Progress</h4>

      <!-- Percentage progress bar -->
      <?php $pct = $current_step >= 0 ? min(100, round($current_step / 4 * 100)) : 0; ?>
      <div style="margin-bottom:28px;">
        <div style="display:flex; justify-content:space-between; font-size:.8rem; color:#7D6E6E; margin-bottom:6px;">
          <span>Status: <strong style="color:#B76E09;"><?php echo htmlspecialchars($order['status'], ENT_QUOTES, 'UTF-8'); ?></strong></span>
          <span><?php echo $pct; ?>% complete</span>
        </div>
        <div style="height:10px; background:#f0f0f0; border-radius:99px; overflow:hidden;">
          <div id="progressBar"
               style="height:100%; width:0; background:linear-gradient(90deg,#B76E09,#f7b731);
                      border-radius:99px; transition:width 1s ease;"></div>
        </div>
      </div>

      <!-- Step milestones -->
      <div style="display:flex; justify-content:space-between; position:relative;">
        <!-- Connector line -->
        <div style="position:absolute; top:20px; left:20px; right:20px; height:3px; background:#ececec; z-index:1;"></div>
        <?php $filled_w = $current_step > 0 ? round($current_step / 4 * 100) : 0; ?>
        <div style="position:absolute; top:20px; left:20px; height:3px;
                    width:calc(<?php echo $filled_w; ?>% - 40px * <?php echo $current_step/4; ?>);
                    background:linear-gradient(90deg,#B76E09,#f7b731); z-index:2; transition:width 1s;"></div>

        <?php foreach ($milestones as $i => $m):
          $done    = $current_step >= $i;
          $active  = $current_step === $i;
          $bg      = $done  ? '#B76E09' : '#ececec';
          $txtCol  = $done  ? '#B76E09' : '#aaa';
          $ring    = $active ? 'box-shadow:0 0 0 4px rgba(183,110,9,.25);' : '';
        ?>
          <div style="text-align:center; position:relative; z-index:3; flex:1;">
            <div style="width:42px; height:42px; border-radius:50%; background:<?php echo $bg; ?>;
                        color:#fff; font-size:1.2rem; line-height:42px; margin:0 auto 10px;
                        transition:background .4s; <?php echo $ring; ?>">
              <?php echo $done ? ($i < $current_step ? 'âœ“' : $m['icon']) : ($i + 1); ?>
            </div>
            <div style="font-size:.78rem; font-weight:700; color:<?php echo $txtCol; ?>;"><?php echo $m['label']; ?></div>
            <div style="font-size:.7rem; color:#bbb; margin-top:2px;"><?php echo $done ? $m['desc'] : ''; ?></div>
          </div>
        <?php endforeach; ?>
      </div>
    </div>
    <?php endif; ?>

    <!-- â”€â”€ Info cards â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€ -->
    <div style="display:grid; grid-template-columns:repeat(auto-fit,minmax(220px,1fr)); gap:16px; margin-bottom:24px;">

      <div style="background:#fff; padding:18px; border-radius:12px; box-shadow:0 2px 6px rgba(0,0,0,.07);">
        <div style="font-size:.78rem; color:#7D6E6E; margin-bottom:5px; text-transform:uppercase; letter-spacing:.5px;">Delivery Address</div>
        <div style="font-size:.9rem; color:#333; line-height:1.5;">
          <?php echo nl2br(htmlspecialchars($order['delivery_address'], ENT_QUOTES, 'UTF-8')); ?>
        </div>
      </div>

      <div style="background:#fff; padding:18px; border-radius:12px; box-shadow:0 2px 6px rgba(0,0,0,.07);">
        <div style="font-size:.78rem; color:#7D6E6E; margin-bottom:5px; text-transform:uppercase; letter-spacing:.5px;">Contact</div>
        <div style="font-size:.9rem; font-weight:600; color:#333;"><?php echo htmlspecialchars($order['contact_number'], ENT_QUOTES, 'UTF-8'); ?></div>
        <div style="font-size:.78rem; color:#7D6E6E; margin-top:8px; text-transform:uppercase; letter-spacing:.5px;">Payment</div>
        <div style="font-size:.9rem; font-weight:600; color:#333;"><?php echo htmlspecialchars($order['payment_method'], ENT_QUOTES, 'UTF-8'); ?></div>
      </div>

      <div style="background:#fff; padding:18px; border-radius:12px; box-shadow:0 2px 6px rgba(0,0,0,.07);">
        <div style="font-size:.78rem; color:#7D6E6E; margin-bottom:5px; text-transform:uppercase; letter-spacing:.5px;">Order Total</div>
        <div style="font-size:1.4rem; font-weight:700; color:#B76E09;"><?php echo format_price($order['total_amount']); ?></div>
        <div style="font-size:.78rem; color:#7D6E6E; margin-top:4px;">
          Includes delivery fee: <?php echo format_price($order['delivery_fee']); ?>
        </div>
      </div>

    </div>

    <!-- â”€â”€ Order items â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€ -->
    <div style="background:#fff; padding:22px; border-radius:12px; box-shadow:0 2px 6px rgba(0,0,0,.07); margin-bottom:24px;">
      <h4 style="margin:0 0 16px; font-size:.95rem; font-weight:600;">Items Ordered</h4>
      <table style="width:100%; border-collapse:collapse; font-size:.9rem;">
        <thead>
          <tr style="background:#f8f9fa;">
            <th style="padding:10px 14px; text-align:left; font-weight:600;">Item</th>
            <th style="padding:10px 14px; text-align:center; font-weight:600;">Qty</th>
            <th style="padding:10px 14px; text-align:right; font-weight:600;">Unit Price</th>
            <th style="padding:10px 14px; text-align:right; font-weight:600;">Subtotal</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($order_items as $it): ?>
            <tr style="border-bottom:1px solid #f5f5f5;">
              <td style="padding:10px 14px; font-weight:600;"><?php echo htmlspecialchars($it['product_name'], ENT_QUOTES, 'UTF-8'); ?></td>
              <td style="padding:10px 14px; text-align:center; color:#7D6E6E;">Ã— <?php echo (int)$it['quantity']; ?></td>
              <td style="padding:10px 14px; text-align:right; color:#7D6E6E;"><?php echo format_price($it['price']); ?></td>
              <td style="padding:10px 14px; text-align:right; font-weight:600; color:#B76E09;"><?php echo format_price($it['subtotal']); ?></td>
            </tr>
          <?php endforeach; ?>
          <tr>
            <td colspan="3" style="padding:10px 14px; text-align:right; font-weight:600;">Total:</td>
            <td style="padding:10px 14px; text-align:right; font-weight:700; color:#B76E09; font-size:1.05rem;">
              <?php echo format_price($order['total_amount']); ?>
            </td>
          </tr>
        </tbody>
      </table>
    </div>

    <!-- â”€â”€ Actions â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€ -->
    <div style="display:flex; gap:12px; flex-wrap:wrap;">
      <a href="order_details.php?id=<?php echo $order_id; ?>"
         style="flex:1; min-width:160px; padding:12px; background:#B76E09; color:#fff; text-align:center;
                text-decoration:none; border-radius:8px; font-weight:600; font-size:.95rem; transition:background .25s;">
        ðŸ“„ View Full Details
      </a>
      <a href="contact.php"
         style="flex:1; min-width:160px; padding:12px; background:#fff; color:#B76E09; text-align:center;
                text-decoration:none; border-radius:8px; font-weight:600; font-size:.95rem;
                border:2px solid #B76E09; transition:all .25s;">
        ðŸ’¬ Contact Support
      </a>
    </div>

  </section><!-- /section -->
</main>

<?php
$additional_js = '
<script>
// Animate progress bar on load
document.addEventListener("DOMContentLoaded", function(){
  var bar = document.getElementById("progressBar");
  if (bar) {
    setTimeout(function(){ bar.style.width = "' . $pct . '%"; }, 200);
  }
});
</script>

<style>
@media(max-width:576px){
  section[style*="padding:30px"]{ padding:16px !important; }
  div[style*="grid-template-columns:repeat(auto-fit,minmax(220px"]{ grid-template-columns:1fr !important; }
  table{ font-size:.8rem; }
  table th, table td{ padding:8px 10px !important; }
}
</style>
';
include 'includes/footer.php';
?>
