<?php
require_once '../includes/admin_check.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $order_id = isset($_POST['order_id']) ? intval($_POST['order_id']) : 0;
    $status = isset($_POST['status']) ? clean_input($_POST['status']) : '';
    
    $allowed_statuses = ['Pending', 'Confirmed', 'Preparing', 'Out for Delivery', 'Delivered', 'Cancelled'];
    
    if ($order_id <= 0 || !in_array($status, $allowed_statuses)) {
        echo json_encode(['success' => false, 'message' => 'Invalid input']);
        exit;
    }
    
    $database = new Database();
    $db = $database->getConnection();
    
    $query = "UPDATE orders SET status = :status WHERE id = :id";
    $stmt = $db->prepare($query);
    $stmt->bindParam(':status', $status);
    $stmt->bindParam(':id', $order_id);
    
    if ($stmt->execute()) {
        // Create notification for user
        $order_query = "SELECT user_id FROM orders WHERE id = :id";
        $order_stmt = $db->prepare($order_query);
        $order_stmt->bindParam(':id', $order_id);
        $order_stmt->execute();
        $order = $order_stmt->fetch();
        
        if ($order) {
            $notif_query = "INSERT INTO notifications (user_id, title, message) VALUES (:user_id, :title, :message)";
            $notif_stmt = $db->prepare($notif_query);
            $notif_title = "Order Status Updated";
            $notif_message = "Your order #" . $order_id . " status has been updated to: " . $status;
            $notif_stmt->bindParam(':user_id', $order['user_id']);
            $notif_stmt->bindParam(':title', $notif_title);
            $notif_stmt->bindParam(':message', $notif_message);
            $notif_stmt->execute();
        }
        
        echo json_encode(['success' => true, 'message' => 'Order status updated']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to update status']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request']);
}
?>
