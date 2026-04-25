<?php
require_once '../config/config.php';

header('Content-Type: application/json');

if (!is_logged_in()) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $order_id = isset($_POST['order_id']) ? intval($_POST['order_id']) : 0;
    $user_id = $_SESSION['user_id'];
    
    $database = new Database();
    $db = $database->getConnection();
    
    // Verify order belongs to user and is pending
    $check_query = "SELECT id, status FROM orders WHERE id = :id AND user_id = :user_id";
    $check_stmt = $db->prepare($check_query);
    $check_stmt->bindParam(':id', $order_id);
    $check_stmt->bindParam(':user_id', $user_id);
    $check_stmt->execute();
    
    if ($check_stmt->rowCount() == 0) {
        echo json_encode(['success' => false, 'message' => 'Order not found']);
        exit;
    }
    
    $order = $check_stmt->fetch();
    
    if ($order['status'] != 'Pending') {
        echo json_encode(['success' => false, 'message' => 'Only pending orders can be cancelled']);
        exit;
    }
    
    // Update order status
    $update_query = "UPDATE orders SET status = 'Cancelled' WHERE id = :id";
    $update_stmt = $db->prepare($update_query);
    $update_stmt->bindParam(':id', $order_id);
    
    if ($update_stmt->execute()) {
        // Restore stock
        $items_query = "SELECT product_id, quantity FROM order_items WHERE order_id = :order_id";
        $items_stmt = $db->prepare($items_query);
        $items_stmt->bindParam(':order_id', $order_id);
        $items_stmt->execute();
        $items = $items_stmt->fetchAll();
        
        foreach ($items as $item) {
            $stock_query = "UPDATE products SET stock = stock + :quantity WHERE id = :id";
            $stock_stmt = $db->prepare($stock_query);
            $stock_stmt->bindParam(':quantity', $item['quantity']);
            $stock_stmt->bindParam(':id', $item['product_id']);
            $stock_stmt->execute();
        }
        
        echo json_encode(['success' => true, 'message' => 'Order cancelled successfully']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to cancel order']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request']);
}
?>
