<?php
/* =====================================================================
 * cashier/ajax/submit_order.php
 * AJAX endpoint — validates cart, opens a DB transaction, inserts order
 * + order_items, decrements stock, returns JSON.
 * ===================================================================== */
require_once '../../includes/cashier_check.php';

header('Content-Type: application/json; charset=utf-8');

// ── Accept JSON body ──────────────────────────────────────────────────
$raw   = file_get_contents('php://input');
$input = json_decode($raw, true);

if (json_last_error() !== JSON_ERROR_NONE || empty($input['items'])) {
    echo json_encode(['success' => false, 'message' => 'Invalid request data.']);
    exit;
}

$items          = $input['items'];           // [{ product_id, qty }, …]
$customer_name  = trim($input['customer_name']  ?? 'Walk-in Customer');
$payment_method = trim($input['payment_method'] ?? 'Cash');
$cashier_id     = (int)$_SESSION['user_id'];

// Basic validation
$allowed_payment = ['Cash', 'GCash', 'PayMaya', 'Card'];
if (!in_array($payment_method, $allowed_payment, true)) {
    $payment_method = 'Cash';
}
$customer_name = mb_substr(htmlspecialchars($customer_name, ENT_QUOTES, 'UTF-8'), 0, 100);

if (empty($customer_name)) {
    $customer_name = 'Walk-in Customer';
}

// ── Fetch walk-in placeholder user ───────────────────────────────────
$db = (new Database())->getConnection();

$walkin_stmt = $db->prepare("SELECT id FROM users WHERE email = 'walkin@cheries.com' LIMIT 1");
$walkin_stmt->execute();
$walkin_id = (int)($walkin_stmt->fetchColumn() ?: 0);

if ($walkin_id === 0) {
    echo json_encode(['success' => false, 'message' => 'Walk-in user not found. Run the cashier migration.']);
    exit;
}

// ── Validate each item & fetch current prices / stock ─────────────────
$validated = [];
$total     = 0.0;

foreach ($items as $item) {
    $pid = (int)($item['product_id'] ?? 0);
    $qty = (int)($item['qty']        ?? 0);

    if ($pid <= 0 || $qty <= 0) {
        echo json_encode(['success' => false, 'message' => 'Invalid item in cart.']);
        exit;
    }

    $ps = $db->prepare(
        "SELECT id, name, price, stock, is_available
         FROM   products
         WHERE  id = :id FOR UPDATE"
    );
    // note: FOR UPDATE requires a transaction — we'll run this inside one below
    // For pre-validation, use a plain select:
    $ps = $db->prepare(
        "SELECT id, name, price, stock, is_available FROM products WHERE id = :id LIMIT 1"
    );
    $ps->execute([':id' => $pid]);
    $prod = $ps->fetch(PDO::FETCH_ASSOC);

    if (!$prod || !$prod['is_available']) {
        echo json_encode(['success' => false, 'message' => "Product #{$pid} is not available."]);
        exit;
    }
    if ($prod['stock'] < $qty) {
        echo json_encode(['success' => false,
            'message' => "Insufficient stock for \"{$prod['name']}\". Available: {$prod['stock']}."]);
        exit;
    }

    $subtotal    = round((float)$prod['price'] * $qty, 2);
    $total      += $subtotal;
    $validated[] = [
        'id'       => $pid,
        'name'     => $prod['name'],
        'price'    => (float)$prod['price'],
        'qty'      => $qty,
        'subtotal' => $subtotal,
    ];
}

if (empty($validated)) {
    echo json_encode(['success' => false, 'message' => 'Cart is empty.']);
    exit;
}

// ── Transaction: insert order + items + update stock ─────────────────
try {
    $db->beginTransaction();

    // 1. Insert order
    $ins = $db->prepare(
        "INSERT INTO orders
             (user_id, created_by, order_source, customer_name,
              total_amount, delivery_fee, payment_method,
              delivery_address, contact_number, status, notes)
         VALUES
             (:uid, :cby, 'cashier', :cname,
              :total, 0.00, :pm,
              'In-Store', NULL, 'Confirmed', NULL)"
    );
    $ins->execute([
        ':uid'   => $walkin_id,
        ':cby'   => $cashier_id,
        ':cname' => $customer_name,
        ':total' => round($total, 2),
        ':pm'    => $payment_method,
    ]);
    $order_id = (int)$db->lastInsertId();

    // 2. Insert order items + decrement stock
    $item_ins = $db->prepare(
        "INSERT INTO order_items (order_id, product_id, product_name, price, quantity, subtotal)
         VALUES (:oid, :pid, :pname, :price, :qty, :sub)"
    );
    $stock_upd = $db->prepare(
        "UPDATE products SET stock = stock - :qty WHERE id = :id AND stock >= :qty"
    );

    foreach ($validated as $v) {
        $item_ins->execute([
            ':oid'   => $order_id,
            ':pid'   => $v['id'],
            ':pname' => $v['name'],
            ':price' => $v['price'],
            ':qty'   => $v['qty'],
            ':sub'   => $v['subtotal'],
        ]);

        $stock_upd->execute([':qty' => $v['qty'], ':id' => $v['id']]);
        if ($stock_upd->rowCount() === 0) {
            // Stock changed between validation and update — rollback
            $db->rollBack();
            echo json_encode(['success' => false,
                'message' => "Stock changed for \"{$v['name']}\". Please refresh and retry."]);
            exit;
        }
    }

    $db->commit();

    echo json_encode([
        'success'  => true,
        'order_id' => $order_id,
        'total'    => round($total, 2),
        'message'  => "Order #{$order_id} placed successfully.",
    ]);

} catch (PDOException $e) {
    if ($db->inTransaction()) {
        $db->rollBack();
    }
    // Never expose raw DB error to client
    error_log('submit_order.php PDO error: ' . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Database error. Please try again.']);
}
