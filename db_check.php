<?php
/**
 * Database Diagnostics & Fix Tool
 * Access: http://localhost/CheriesOrderTrackingSystem/db_check.php
 * DELETE this file after everything works.
 */
session_start();

// ── Bootstrap ─────────────────────────────────────────────────────────────
define('SITE_NAME', 'Cheries');
$cfg = [
    'host'   => 'localhost',
    'db'     => 'food_ordering_db',
    'user'   => 'root',
    'pass'   => '',
];

$results = [];
$pdo     = null;

// ── 1. Test PDO connection ────────────────────────────────────────────────
try {
    $pdo = new PDO(
        "mysql:host={$cfg['host']};dbname={$cfg['db']};charset=utf8mb4",
        $cfg['user'],
        $cfg['pass'],
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC]
    );
    $results['connection'] = ['ok' => true, 'msg' => "Connected to <strong>{$cfg['db']}</strong> on {$cfg['host']}"];
} catch (PDOException $e) {
    $results['connection'] = ['ok' => false, 'msg' => htmlspecialchars($e->getMessage())];
}

// ── 2. Check required tables ──────────────────────────────────────────────
$required_tables = ['users', 'categories', 'products', 'orders', 'order_items', 'cart', 'notifications'];
$found_tables    = [];

if ($pdo) {
    $rows = $pdo->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN);
    $found_tables = $rows;
    $missing = array_diff($required_tables, $rows);
    if (empty($missing)) {
        $results['tables'] = ['ok' => true, 'msg' => 'All ' . count($required_tables) . ' required tables found: ' . implode(', ', $required_tables)];
    } else {
        $results['tables'] = ['ok' => false, 'msg' => 'Missing tables: <strong>' . implode(', ', $missing) . '</strong>. Re-import food_ordering_db.sql.'];
    }
}

// ── 3. Check row counts ───────────────────────────────────────────────────
$counts = [];
if ($pdo && empty($missing ?? [])) {
    foreach (['users', 'categories', 'products'] as $t) {
        $counts[$t] = (int)$pdo->query("SELECT COUNT(*) FROM `$t`")->fetchColumn();
    }
    $results['counts'] = [
        'ok'  => ($counts['users'] > 0 && $counts['products'] > 0),
        'msg' => "Users: {$counts['users']} &nbsp;|&nbsp; Categories: {$counts['categories']} &nbsp;|&nbsp; Products: {$counts['products']}"
    ];
}

// ── 4. Check admin account ────────────────────────────────────────────────
$admin = null;
if ($pdo && isset($counts) && $counts['users'] > 0) {
    $admin = $pdo->query("SELECT id, email, password, is_admin FROM users WHERE is_admin = 1 LIMIT 1")->fetch();
    if ($admin) {
        $hash_ok = password_verify('admin123', $admin['password']);
        $results['admin'] = [
            'ok'  => $hash_ok,
            'msg' => $hash_ok
                ? "Admin account found: <strong>{$admin['email']}</strong> — password <strong>admin123</strong> ✓"
                : "Admin account found: <strong>{$admin['email']}</strong> — but password hash does NOT match <strong>admin123</strong>. Use the fix button below."
        ];
    } else {
        $results['admin'] = ['ok' => false, 'msg' => 'No admin user found. Use the fix button below.'];
    }
}

// ── 5. Handle fix actions ─────────────────────────────────────────────────
$action_msg = '';

// Action: reset/create admin password
if (isset($_POST['fix_password']) && $pdo) {
    $new_hash = password_hash('admin123', PASSWORD_BCRYPT);
    if ($admin) {
        $pdo->prepare("UPDATE users SET password = ? WHERE id = ?")->execute([$new_hash, $admin['id']]);
        $action_msg = "✅ Admin password reset to <strong>admin123</strong> for {$admin['email']}.";
    } else {
        // Create admin user
        $pdo->prepare(
            "INSERT INTO users (first_name, last_name, email, password, role, is_admin) VALUES (?,?,?,?,1,1)"
        )->execute(['Admin', 'Cheries', 'admin@cheries.com', $new_hash]);
        $action_msg = "✅ Admin account created: admin@cheries.com / admin123";
    }
    // Re-check
    $admin    = $pdo->query("SELECT id, email, password, is_admin FROM users WHERE is_admin = 1 LIMIT 1")->fetch();
    $hash_ok  = $admin ? password_verify('admin123', $admin['password']) : false;
    $results['admin'] = [
        'ok'  => $hash_ok,
        'msg' => $hash_ok
            ? "Admin account: <strong>{$admin['email']}</strong> — password <strong>admin123</strong> ✓"
            : "Still not matching. Please check manually."
    ];
}

// Action: import schema
if (isset($_POST['import_schema']) && $pdo) {
    $sql_file = __DIR__ . '/database/food_ordering_db.sql';
    if (file_exists($sql_file)) {
        try {
            $sql = file_get_contents($sql_file);
            // Split on semicolons (skip empty lines)
            $statements = array_filter(array_map('trim', explode(";\n", $sql)));
            $count = 0;
            foreach ($statements as $stmt) {
                if (!empty($stmt) && !str_starts_with(ltrim($stmt), '--')) {
                    $pdo->exec($stmt);
                    $count++;
                }
            }
            $action_msg = "✅ Schema imported successfully ({$count} statements executed).";
        } catch (PDOException $e) {
            $action_msg = "⚠️ Import error: " . htmlspecialchars($e->getMessage());
        }
    } else {
        $action_msg = "❌ File not found: database/food_ordering_db.sql";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>DB Diagnostics — Cheries</title>
  <style>
    *, *::before, *::after { box-sizing: border-box; }
    body { font-family: 'Segoe UI', sans-serif; background: #f5f5f5; margin: 0; padding: 30px; color: #222; }
    h1  { font-size: 1.4rem; margin: 0 0 6px; color: #B76E09; }
    p.sub { margin: 0 0 28px; color: #888; font-size: .85rem; }
    .card { background: #fff; border-radius: 10px; padding: 22px 26px; margin-bottom: 16px;
            box-shadow: 0 2px 6px rgba(0,0,0,.08); }
    .row  { display: flex; align-items: flex-start; gap: 14px; }
    .icon { font-size: 1.3rem; margin-top: 2px; flex-shrink: 0; }
    .ok   { color: #28a745; }
    .fail { color: #dc3545; }
    .label { font-weight: 700; font-size: .82rem; text-transform: uppercase;
             letter-spacing: .5px; margin-bottom: 4px; color: #555; }
    .msg  { font-size: .92rem; line-height: 1.5; }
    .actions { display: flex; flex-wrap: wrap; gap: 10px; margin-top: 18px; }
    button { padding: 10px 22px; border: none; border-radius: 7px; font-size: .9rem;
             font-weight: 600; cursor: pointer; transition: opacity .2s; }
    button:hover { opacity: .85; }
    .btn-primary { background: #B76E09; color: #fff; }
    .btn-secondary { background: #6c757d; color: #fff; }
    .action-msg { margin-top: 16px; padding: 13px 18px; border-radius: 8px;
                  background: #e8f5e9; border-left: 4px solid #28a745;
                  font-size: .9rem; font-weight: 600; }
    .action-msg.err { background: #fdecea; border-color: #dc3545; }
    table { width: 100%; border-collapse: collapse; font-size: .88rem; margin-top: 10px; }
    th, td { padding: 8px 12px; text-align: left; border-bottom: 1px solid #f0f0f0; }
    th { background: #f8f9fa; font-weight: 600; }
    .creds { background: #fff8e1; border: 1px solid #ffe082; border-radius: 8px;
             padding: 14px 18px; font-size: .9rem; }
    .creds strong { color: #B76E09; }
    code { background: #f5f5f5; padding: 2px 6px; border-radius: 4px; font-size: .88rem; }
    hr { border: none; border-top: 1px solid #ececec; margin: 20px 0; }
    .warn-box { background: #fff3cd; border: 1px solid #ffc107; border-radius: 8px;
                padding: 10px 16px; font-size: .82rem; margin-bottom: 20px; }
  </style>
</head>
<body>

<h1>🔍 Database Diagnostics</h1>
<p class="sub">Cheries Order Tracking System — run this to verify your setup, then delete the file.</p>

<div class="warn-box">
  ⚠️ <strong>Security reminder:</strong> Delete <code>db_check.php</code> once everything is working.
</div>

<?php if ($action_msg): ?>
<div class="action-msg <?php echo str_starts_with($action_msg, '⚠️') || str_starts_with($action_msg, '❌') ? 'err' : ''; ?>">
  <?php echo $action_msg; ?>
</div>
<?php endif; ?>

<!-- ── Check results ───────────────────────────────────────────────────── -->
<?php
$checks = [
    'connection' => '1. Database Connection',
    'tables'     => '2. Required Tables',
    'counts'     => '3. Seed Data',
    'admin'      => '4. Admin Account',
];
foreach ($checks as $key => $label):
    if (!isset($results[$key])) continue;
    $r = $results[$key];
?>
<div class="card">
  <div class="row">
    <span class="icon <?php echo $r['ok'] ? 'ok' : 'fail'; ?>"><?php echo $r['ok'] ? '✅' : '❌'; ?></span>
    <div>
      <div class="label"><?php echo $label; ?></div>
      <div class="msg"><?php echo $r['msg']; ?></div>
    </div>
  </div>
</div>
<?php endforeach; ?>

<!-- ── Fix actions ─────────────────────────────────────────────────────── -->
<div class="card">
  <div class="label" style="margin-bottom:14px;">🔧 Fix Actions</div>
  <form method="POST" style="display:contents;">
    <div class="actions">
      <button name="fix_password" class="btn-primary">Reset Admin Password to admin123</button>
      <button name="import_schema" class="btn-secondary"
              onclick="return confirm('This will re-run food_ordering_db.sql. Existing data may be overwritten. Continue?')">
        Re-import Schema (food_ordering_db.sql)
      </button>
    </div>
  </form>
</div>

<!-- ── Database info ───────────────────────────────────────────────────── -->
<?php if ($pdo && !empty($found_tables)): ?>
<div class="card">
  <div class="label" style="margin-bottom:10px;">📋 Tables in food_ordering_db</div>
  <table>
    <thead><tr><th>Table</th><th>Rows</th><th>Status</th></tr></thead>
    <tbody>
    <?php foreach ($found_tables as $tbl):
        $cnt = (int)$pdo->query("SELECT COUNT(*) FROM `$tbl`")->fetchColumn();
        $req = in_array($tbl, $required_tables);
    ?>
      <tr>
        <td><strong><?php echo htmlspecialchars($tbl); ?></strong></td>
        <td><?php echo $cnt; ?></td>
        <td><?php echo $req ? '<span class="ok">✓ Required</span>' : '<span style="color:#888">Extra</span>'; ?></td>
      </tr>
    <?php endforeach; ?>
    </tbody>
  </table>
</div>
<?php endif; ?>

<!-- ── PHP environment ─────────────────────────────────────────────────── -->
<div class="card">
  <div class="label" style="margin-bottom:10px;">🖥 PHP Environment</div>
  <table>
    <tbody>
      <tr><td>PHP Version</td><td><?php echo phpversion(); ?></td></tr>
      <tr><td>PDO MySQL</td><td><?php echo extension_loaded('pdo_mysql') ? '<span class="ok">Loaded ✓</span>' : '<span class="fail">NOT loaded ✗</span>'; ?></td></tr>
      <tr><td>Session</td><td><?php echo session_status() === PHP_SESSION_ACTIVE ? '<span class="ok">Active ✓</span>' : '<span class="fail">Not active ✗</span>'; ?></td></tr>
      <tr><td>Document Root</td><td><?php echo htmlspecialchars($_SERVER['DOCUMENT_ROOT']); ?></td></tr>
      <tr><td>Project Path</td><td><?php echo htmlspecialchars(__DIR__); ?></td></tr>
      <tr><td>SQL File</td><td><?php $f = __DIR__.'/database/food_ordering_db.sql'; echo file_exists($f) ? '<span class="ok">Found ✓</span>' : '<span class="fail">Not found ✗</span>'; ?></td></tr>
    </tbody>
  </table>
</div>

<!-- ── Login credentials ───────────────────────────────────────────────── -->
<div class="card">
  <div class="label" style="margin-bottom:10px;">🔑 Login Credentials (after fix)</div>
  <div class="creds">
    URL: <strong><a href="http://localhost/CheriesOrderTrackingSystem/login.php" target="_blank">http://localhost/CheriesOrderTrackingSystem/login.php</a></strong><br><br>
    Email: <strong>admin@cheries.com</strong><br>
    Password: <strong>admin123</strong>
  </div>
</div>

</body>
</html>
