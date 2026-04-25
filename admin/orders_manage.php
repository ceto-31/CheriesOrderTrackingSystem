<?php
require_once '../includes/admin_check.php';

$page_title = "Manage Orders";
$db = (new Database())->getConnection();

// ── Filters ───────────────────────────────────────────────────────────────
$status_filter = isset($_GET['status']) ? clean_input($_GET['status']) : '';
$search_filter = isset($_GET['q'])      ? clean_input($_GET['q'])      : '';

$allowed_statuses = ['Pending','Confirmed','Preparing','Out for Delivery','Delivered','Cancelled'];

$params = [];
$where  = [];

if (!empty($status_filter) && in_array($status_filter, $allowed_statuses, true)) {
    $where[]            = 'o.status = :status';
    $params[':status']  = $status_filter;
}

if (!empty($search_filter)) {
    $where[]            = '(u.first_name LIKE :q OR u.last_name LIKE :q OR u.email LIKE :q OR o.id LIKE :q)';
    $params[':q']       = '%' . $search_filter . '%';
}

$sql = "SELECT o.id, o.total_amount, o.status, o.payment_method, o.created_at,
               u.first_name, u.last_name, u.email
        FROM   orders o
        JOIN   users  u ON u.id = o.user_id"
     . (!empty($where) ? ' WHERE ' . implode(' AND ', $where) : '')
     . " ORDER BY o.created_at DESC";

$stmt = $db->prepare($sql);
$stmt->execute($params);
$orders = $stmt->fetchAll();

// ── Status counts for tabs ────────────────────────────────────────────────
$counts_stmt = $db->query(
    "SELECT status, COUNT(*) AS cnt FROM orders GROUP BY status"
);
$counts = [];
foreach ($counts_stmt->fetchAll() as $row) {
    $counts[$row['status']] = (int)$row['cnt'];
}

include '../includes/header.php';

$status_colors = [
    'Pending'          => '#ffc107',
    'Confirmed'        => '#17a2b8',
    'Preparing'        => '#fd7e14',
    'Out for Delivery' => '#007bff',
    'Delivered'        => '#28a745',
    'Cancelled'        => '#dc3545',
];
?>

<?php include '../includes/admin_nav.php'; ?>

  <div style="flex:1; padding:30px; overflow-y:auto;">

    <?php if (isset($_SESSION['success'])): ?>
      <div class="alert alert-success"><?php echo htmlspecialchars($_SESSION['success'], ENT_QUOTES, 'UTF-8'); unset($_SESSION['success']); ?></div>
    <?php endif; ?>

    <!-- ── Page title + search ──────────────────────────────────────── -->
    <div style="display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:12px; margin-bottom:22px;">
      <h2 style="margin:0; font-weight:600;">All Orders
        <span style="font-size:.85rem; color:#7D6E6E; font-weight:400;">(<?php echo count($orders); ?> results)</span>
      </h2>

      <form method="GET" style="display:flex; gap:10px; flex-wrap:wrap;">
        <input type="text" name="q" value="<?php echo htmlspecialchars($search_filter, ENT_QUOTES, 'UTF-8'); ?>"
               placeholder="Search customer / order ID…"
               style="padding:9px 14px; border:1px solid #ddd; border-radius:8px; outline:none; font-size:.9rem; min-width:200px;">

        <select name="status" style="padding:9px 14px; border:1px solid #ddd; border-radius:8px; outline:none; background:#fff; font-size:.9rem;">
          <option value="">All Statuses</option>
          <?php foreach ($allowed_statuses as $s): ?>
            <option value="<?php echo $s; ?>" <?php echo $status_filter===$s?'selected':''; ?>>
              <?php echo $s; ?> (<?php echo $counts[$s] ?? 0; ?>)
            </option>
          <?php endforeach; ?>
        </select>

        <button type="submit" style="padding:9px 18px; background:#B76E09; color:#fff; border:none; border-radius:8px; cursor:pointer; font-weight:600;">
          Filter
        </button>
        <?php if (!empty($status_filter) || !empty($search_filter)): ?>
          <a href="orders_manage.php" style="padding:9px 18px; background:#7D6E6E; color:#fff; text-decoration:none; border-radius:8px; font-weight:600;">
            Clear
          </a>
        <?php endif; ?>
      </form>
    </div>

    <!-- ── Orders table ─────────────────────────────────────────────── -->
    <div style="background:#fff; border-radius:12px; box-shadow:0 2px 6px rgba(0,0,0,.08); overflow-x:auto;">
      <table style="width:100%; border-collapse:collapse; font-size:.9rem;">
        <thead>
          <tr style="background:#f8f9fa;">
            <th style="padding:13px 15px; text-align:left; font-weight:600;">Order ID</th>
            <th style="padding:13px 15px; text-align:left; font-weight:600;">Customer</th>
            <th style="padding:13px 15px; text-align:left; font-weight:600;">Total</th>
            <th style="padding:13px 15px; text-align:left; font-weight:600;">Payment</th>
            <th style="padding:13px 15px; text-align:left; font-weight:600;">Status</th>
            <th style="padding:13px 15px; text-align:left; font-weight:600;">Date</th>
            <th style="padding:13px 15px; text-align:left; font-weight:600;">Actions</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($orders as $order):
            $sc = $status_colors[$order['status']] ?? '#6c757d';
          ?>
            <tr style="border-bottom:1px solid #f5f5f5;" data-order-id="<?php echo (int)$order['id']; ?>">
              <td style="padding:13px 15px; font-weight:600;">#<?php echo (int)$order['id']; ?></td>
              <td style="padding:13px 15px;">
                <div style="font-weight:600;"><?php echo htmlspecialchars($order['first_name'].' '.$order['last_name'], ENT_QUOTES, 'UTF-8'); ?></div>
                <div style="font-size:.8rem; color:#7D6E6E;"><?php echo htmlspecialchars($order['email'], ENT_QUOTES, 'UTF-8'); ?></div>
              </td>
              <td style="padding:13px 15px; font-weight:600; color:#B76E09;"><?php echo format_price($order['total_amount']); ?></td>
              <td style="padding:13px 15px; color:#7D6E6E;"><?php echo htmlspecialchars($order['payment_method'], ENT_QUOTES, 'UTF-8'); ?></td>
              <td style="padding:13px 15px;">
                <select class="status-select"
                        data-order-id="<?php echo (int)$order['id']; ?>"
                        data-current="<?php echo htmlspecialchars($order['status'], ENT_QUOTES, 'UTF-8'); ?>"
                        style="padding:7px 11px; border:2px solid <?php echo $sc; ?>; border-radius:6px;
                               font-weight:600; outline:none; cursor:pointer; color:#333; background:#fff; font-size:.85rem;">
                  <?php foreach ($allowed_statuses as $s): ?>
                    <option value="<?php echo $s; ?>" <?php echo $order['status']===$s?'selected':''; ?>><?php echo $s; ?></option>
                  <?php endforeach; ?>
                </select>
              </td>
              <td style="padding:13px 15px; color:#7D6E6E; font-size:.85rem;">
                <?php echo date('M d, Y', strtotime($order['created_at'])); ?><br>
                <span style="font-size:.78rem;"><?php echo date('h:i A', strtotime($order['created_at'])); ?></span>
              </td>
              <td style="padding:13px 15px;">
                <a href="order_view.php?id=<?php echo (int)$order['id']; ?>"
                   style="color:#B76E09; text-decoration:none; font-weight:600; font-size:.85rem;">
                  View →
                </a>
              </td>
            </tr>
          <?php endforeach; ?>

          <?php if (empty($orders)): ?>
            <tr>
              <td colspan="7" style="padding:30px; text-align:center; color:#7D6E6E;">
                No orders found matching your filters.
              </td>
            </tr>
          <?php endif; ?>
        </tbody>
      </table>
    </div><!-- /table -->

  </div><!-- /pad -->
</main>

<?php
$additional_js = '
<script>
document.querySelectorAll(".status-select").forEach(function(sel){
  sel.addEventListener("change", function(){
    const orderId   = this.dataset.orderId;
    const newStatus = this.value;
    const previous  = this.dataset.current;
    const el        = this;

    Swal.fire({
      title: "Update Order #" + orderId + "?",
      html:  "Change status to <strong>" + newStatus + "</strong>?",
      icon:  "question",
      showCancelButton:    true,
      confirmButtonColor:  "#B76E09",
      cancelButtonColor:   "#7D6E6E",
      confirmButtonText:   "Yes, update"
    }).then(function(result){
      if (!result.isConfirmed) {
        el.value = previous;   // revert
        return;
      }

      el.disabled = true;

      $.ajax({
        url:      "order_update_status.php",
        method:   "POST",
        data:     { order_id: orderId, status: newStatus },
        dataType: "json",
        success: function(r){
          el.disabled          = false;
          el.dataset.current   = newStatus;
          el.style.borderColor = statusColor(newStatus);

          if (r.success) {
            Swal.fire({ icon:"success", title:"Updated!", text:"Order #"+orderId+" → "+newStatus,
                        timer:1400, showConfirmButton:false, toast:true, position:"top-end" });
          } else {
            Swal.fire({ icon:"error", title:"Error", text: r.message||"Update failed." });
            el.value = previous;
          }
        },
        error: function(){
          el.disabled = false;
          el.value    = previous;
          Swal.fire({ icon:"error", title:"Network Error", text:"Please try again." });
        }
      });
    });
  });
});

function statusColor(s){
  const m = {
    "Pending":"#ffc107","Confirmed":"#17a2b8","Preparing":"#fd7e14",
    "Out for Delivery":"#007bff","Delivered":"#28a745","Cancelled":"#dc3545"
  };
  return m[s] || "#6c757d";
}
</script>

<style>
.status-select:focus { box-shadow: 0 0 0 3px rgba(183,110,9,.25); }
</style>
';

include '../includes/footer.php';
?>
