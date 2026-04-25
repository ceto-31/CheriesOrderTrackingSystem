<?php
require_once '../includes/admin_check.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request']);
    exit;
}

$order_id = isset($_POST['order_id']) ? (int)$_POST['order_id'] : 0;
$status   = isset($_POST['status'])   ? clean_input($_POST['status']) : '';

$allowed = ['Pending','Confirmed','Preparing','Out for Delivery','Delivered','Cancelled'];

if ($order_id <= 0 || !in_array($status, $allowed, true)) {
    echo json_encode(['success' => false, 'message' => 'Invalid input']);
    exit;
}

$db = (new Database())->getConnection();

// ── Update order status ───────────────────────────────────────────────────
$upd = $db->prepare("UPDATE orders SET status = :status WHERE id = :id");
$upd->execute([':status' => $status, ':id' => $order_id]);

// ── Notify customer ───────────────────────────────────────────────────────
$ord = $db->prepare("SELECT user_id, total_amount FROM orders WHERE id = :id");
$ord->execute([':id' => $order_id]);
$order = $ord->fetch();

if ($order) {
    $notif = $db->prepare(
        "INSERT INTO notifications (user_id, title, message)
         VALUES (:uid, 'Order Status Updated',
                 :msg)"
    );
    $notif->execute([
        ':uid' => $order['user_id'],
        ':msg' => "Your order #{$order_id} status has been updated to: {$status}",
    ]);
}

echo json_encode(['success' => true, 'message' => 'Order status updated']);
?>

$order_id = isset($_POST['order_id']) ? (int)$_POST['order_id'] : 0;
$status   = isset($_POST['status'])   ? clean_input($_POST['status']) : '';

$allowed_statuses = ['Pending', 'Confirmed', 'Preparing', 'Out for Delivery', 'Delivered', 'Cancelled'];

if ($order_id <= 0 || !in_array($status, $allowed_statuses, true)) {
    echo json_encode(['success' => false, 'message' => 'Invalid input']);
    exit;
}

// ── DB connections ────────────────────────────────────────────
$dbMain      = (new DbMain())->getConnection();
$dbAnalytics = (new DbAnalytics())->getConnection();

// ── Update order status in food_order_db ─────────────────────
$stmt = $dbMain->prepare("UPDATE orders SET status = :status WHERE id = :id");
$stmt->bindValue(':status', $status);
$stmt->bindValue(':id',     $order_id, PDO::PARAM_INT);

if (!$stmt->execute()) {
    echo json_encode(['success' => false, 'message' => 'Failed to update status']);
    exit;
}

// ── Notify the customer ───────────────────────────────────────
$order_row = $dbMain->prepare("SELECT user_id, total_amount, created_at FROM orders WHERE id = :id");
$order_row->bindValue(':id', $order_id, PDO::PARAM_INT);
$order_row->execute();
$order = $order_row->fetch();

if ($order) {
    $notif = $dbMain->prepare(
        "INSERT INTO notifications (user_id, title, message)
         VALUES (:uid, :title, :msg)"
    );
    $notif->execute([
        ':uid'   => $order['user_id'],
        ':title' => 'Order Status Updated',
        ':msg'   => "Your order #{$order_id} status has been updated to: {$status}",
    ]);
}

// ── Analytics logging (only when order is Delivered) ─────────
if ($status === 'Delivered' && $order) {

    // 1. Fetch all items for this order (food_order_db)
    $items_stmt = $dbMain->prepare(
        "SELECT oi.product_id, oi.product_name, oi.quantity, oi.price, oi.subtotal,
                c.name AS category_name
         FROM   order_items oi
         LEFT   JOIN products p ON oi.product_id = p.id
         LEFT   JOIN categories c ON p.category_id = c.id
         WHERE  oi.order_id = :oid"
    );
    $items_stmt->bindValue(':oid', $order_id, PDO::PARAM_INT);
    $items_stmt->execute();
    $items = $items_stmt->fetchAll();

    $delivery_date = date('Y-m-d', strtotime($order['created_at']));

    // 2. Upsert into system_analytics_db.daily_revenue
    $upsert_rev = $dbAnalytics->prepare(
        "INSERT INTO daily_revenue (revenue_date, total_revenue, order_count)
         VALUES (:date, :rev, 1)
         ON DUPLICATE KEY UPDATE
             total_revenue = total_revenue + VALUES(total_revenue),
             order_count   = order_count   + 1"
    );
    $upsert_rev->execute([
        ':date' => $delivery_date,
        ':rev'  => (float)$order['total_amount'],
    ]);

    // 3. Insert into system_analytics_db.sales_logs (one row per item)
    $log_stmt = $dbAnalytics->prepare(
        "INSERT INTO sales_logs
             (order_id, product_id, product_name, category_name,
              quantity_sold, unit_price, total_revenue)
         VALUES
             (:oid, :pid, :pname, :cat, :qty, :price, :rev)"
    );

    foreach ($items as $item) {
        $log_stmt->execute([
            ':oid'   => $order_id,
            ':pid'   => (int)$item['product_id'],
            ':pname' => $item['product_name'],
            ':cat'   => $item['category_name'] ?? null,
            ':qty'   => (int)$item['quantity'],
            ':price' => (float)$item['price'],
            ':rev'   => (float)$item['subtotal'],
        ]);
    }

    // 4. Record stock deduction in system_analytics_db.stock_history
    $hist_stmt = $dbAnalytics->prepare(
        "INSERT INTO stock_history
             (product_id, product_name, old_stock, new_stock, change_type)
         VALUES (:pid, :pname, :old, :new, 'sale')"
    );

    $get_stock = $dbMain->prepare("SELECT id, name, stock FROM products WHERE id = :pid");

    foreach ($items as $item) {
        $get_stock->execute([':pid' => (int)$item['product_id']]);
        $product = $get_stock->fetch();
        if ($product) {
            $old_stock = (int)$product['stock'];
            $new_stock = max(0, $old_stock - (int)$item['quantity']);

            // Deduct stock in food_order_db
            $deduct = $dbMain->prepare(
                "UPDATE products SET stock = :new_stock WHERE id = :pid AND stock >= :qty"
            );
            $deduct->execute([
                ':new_stock' => $new_stock,
                ':pid'       => (int)$item['product_id'],
                ':qty'       => (int)$item['quantity'],
            ]);

            $hist_stmt->execute([
                ':pid'   => (int)$item['product_id'],
                ':pname' => $item['product_name'],
                ':old'   => $old_stock,
                ':new'   => $new_stock,
            ]);
        }
    }
}

echo json_encode(['success' => true, 'message' => 'Order status updated']);
?>
