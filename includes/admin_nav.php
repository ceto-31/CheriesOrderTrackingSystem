<?php
/**
 * Shared admin navigation — sidebar + top header bar.
 *
 * Variables consumed (must be set before include):
 *   $page_title  string  Page heading shown in <h3>. Falls back to 'Admin'.
 *   $active_nav  string  Optional override for which nav link is highlighted.
 *                        Defaults to basename($_SERVER['PHP_SELF']).
 */
$_nav_current = basename($_SERVER['PHP_SELF']);
if (!isset($active_nav)) {
    $active_nav = $_nav_current;
}

$_nav_links = [
    ['file' => 'dashboard.php',       'label' => 'Dashboard'],
    ['file' => 'products.php',        'label' => 'Products'],
    ['file' => 'orders_manage.php',   'label' => 'Orders'],
    ['file' => 'categories.php',      'label' => 'Categories'],
    ['file' => 'support_tickets.php', 'label' => 'Tickets'],
    ['file' => 'reports.php',         'label' => 'Reports'],
];

$_nav_heading = isset($page_title) ? htmlspecialchars($page_title, ENT_QUOTES, 'UTF-8') : 'Admin';
?>

<!-- ═══════════════════════════════════════════════════════════ SIDEBAR -->
<aside>
  <div class="logo">🍽️ Cheries Admin</div>
  <div class="sidebar-menu">
    <?php foreach ($_nav_links as $_link): ?>
      <a href="<?php echo $_link['file']; ?>"
         <?php if ($active_nav === $_link['file']) echo 'class="active"'; ?>>
        <?php echo $_link['label']; ?>
      </a>
    <?php endforeach; ?>
    <a href="../logout.php">Logout</a>
  </div>
</aside>

<!-- ════════════════════════════════════════════════════════════== MAIN -->
<main>
  <header>
    <h3><?php echo $_nav_heading; ?></h3>
    <nav>
      <?php foreach ($_nav_links as $_link): ?>
        <a href="<?php echo $_link['file']; ?>"
           <?php if ($active_nav === $_link['file']) echo 'class="active"'; ?>>
          <?php echo $_link['label']; ?>
        </a>
      <?php endforeach; ?>
      <a href="../logout.php"
         style="background:#dc3545 !important; color:#fff !important;">Logout</a>
    </nav>
  </header>
