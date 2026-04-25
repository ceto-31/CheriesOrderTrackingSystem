<?php
/**
 * Shared cashier navigation — sidebar + top header bar.
 * Include AFTER include '../includes/header.php'.
 *
 * Variables consumed:
 *   $page_title  string   Page heading (optional, falls back to 'Cashier').
 *   $active_nav  string   Override active link. Defaults to basename of current file.
 */
$_cnav_current = basename($_SERVER['PHP_SELF']);
if (!isset($active_nav)) {
    $active_nav = $_cnav_current;
}

$_cnav_links = [
    ['file' => 'order_entry.php',   'label' => 'Order Entry',     'icon' => '🧾'],
    ['file' => 'order_history.php', 'label' => "Today's Orders",  'icon' => '📋'],
];

$_cnav_heading = isset($page_title)
    ? htmlspecialchars($page_title, ENT_QUOTES, 'UTF-8')
    : 'Cashier';

$_cashier_name = htmlspecialchars($_SESSION['user_name'] ?? 'Cashier', ENT_QUOTES, 'UTF-8');
?>

<!-- ════════════════════════════════════════ CASHIER SIDEBAR -->
<aside style="background:#3b1f0a;">
  <div class="logo" style="color:#f5c87a; font-size:1.1rem; letter-spacing:.5px;">
    🍽️ Cheries<br>
    <span style="font-size:.72rem; font-weight:400; opacity:.75;">Cashier Station</span>
  </div>

  <div style="background:rgba(255,255,255,.08); border-radius:8px; padding:10px 12px; margin-bottom:18px; font-size:.82rem; color:#f5c87a;">
    👤 <?php echo $_cashier_name; ?>
  </div>

  <div class="sidebar-menu">
    <?php foreach ($_cnav_links as $_lnk): ?>
      <a href="<?php echo $_lnk['file']; ?>"
         <?php if ($active_nav === $_lnk['file']) echo 'class="active"'; ?>
         style="<?php echo $active_nav === $_lnk['file'] ? 'background:#a66b27;' : 'color:#d4a96a;'; ?>">
        <?php echo $_lnk['icon']; ?> <?php echo $_lnk['label']; ?>
      </a>
    <?php endforeach; ?>
    <a href="../logout.php" style="color:#d4a96a; margin-top:auto;">🚪 Logout</a>
  </div>
</aside>

<!-- ════════════════════════════════════════ CASHIER MAIN -->
<main>
  <header style="background:#a66b27; color:#fff;">
    <h3 style="color:#fff;"><?php echo $_cnav_heading; ?></h3>
    <nav>
      <?php foreach ($_cnav_links as $_lnk): ?>
        <a href="<?php echo $_lnk['file']; ?>"
           <?php if ($active_nav === $_lnk['file']) echo 'class="active"'; ?>
           style="color:<?php echo $active_nav === $_lnk['file'] ? '#fff' : 'rgba(255,255,255,.75)'; ?>;
                  <?php echo $active_nav === $_lnk['file'] ? 'background:rgba(255,255,255,.2);' : ''; ?>">
          <?php echo $_lnk['label']; ?>
        </a>
      <?php endforeach; ?>
      <a href="../logout.php"
         style="background:rgba(0,0,0,.25) !important; color:#fff !important;">Logout</a>
    </nav>
  </header>
