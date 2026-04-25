<?php
require_once '../config/config.php';

header('Content-Type: application/json');

if (!is_logged_in()) {
    echo json_encode(['success' => false, 'message' => 'Please log in first']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $cart_id = isset($_POST['cart_id']) ? intval($_POST['cart_id']) : 0;
    $action = isset($_POST['action']) ? $_POST['action'] : '';
    $user_id = $_SESSION['user_id'];
    
    $database = new Database();
    $db = $database->getConnection();
    
    if ($action == 'remove') {
        // Remove item from cart
        $delete_query = "DELETE FROM cart WHERE id = :id AND user_id = :user_id";
        $delete_stmt = $db->prepare($delete_query);
        $delete_stmt->bindParam(':id', $cart_id);
        $delete_stmt->bindParam(':user_id', $user_id);
        
        if ($delete_stmt->execute()) {
            echo json_encode(['success' => true, 'message' => 'Item removed from cart']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to remove item']);
        }
    } elseif ($action == 'increase' || $action == 'decrease') {
        // Get current cart item
        $cart_query = "SELECT c.*, p.stock FROM cart c 
                       JOIN products p ON c.product_id = p.id 
                       WHERE c.id = :id AND c.user_id = :user_id";
        $cart_stmt = $db->prepare($cart_query);
        $cart_stmt->bindParam(':id', $cart_id);
        $cart_stmt->bindParam(':user_id', $user_id);
        $cart_stmt->execute();
        
        if ($cart_stmt->rowCount() == 0) {
            echo json_encode(['success' => false, 'message' => 'Cart item not found']);
            exit;
        }
        
        $cart_item = $cart_stmt->fetch();
        $new_quantity = $cart_item['quantity'];
        
        if ($action == 'increase') {
            $new_quantity++;
            if ($new_quantity > $cart_item['stock']) {
                echo json_encode(['success' => false, 'message' => 'Insufficient stock']);
                exit;
            }
        } else {
            $new_quantity--;
            if ($new_quantity <= 0) {
                // Remove if quantity becomes 0
                $delete_query = "DELETE FROM cart WHERE id = :id";
                $delete_stmt = $db->prepare($delete_query);
                $delete_stmt->bindParam(':id', $cart_id);
                
                if ($delete_stmt->execute()) {
                    echo json_encode(['success' => true, 'message' => 'Item removed from cart']);
                } else {
                    echo json_encode(['success' => false, 'message' => 'Failed to remove item']);
                }
                exit;
            }
        }
        
        // Update quantity
        $update_query = "UPDATE cart SET quantity = :quantity WHERE id = :id";
        $update_stmt = $db->prepare($update_query);
        $update_stmt->bindParam(':quantity', $new_quantity);
        $update_stmt->bindParam(':id', $cart_id);
        
        if ($update_stmt->execute()) {
            echo json_encode(['success' => true, 'message' => 'Cart updated', 'new_quantity' => $new_quantity]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to update cart']);
        }
    } else {
        echo json_encode(['success' => false, 'message' => 'Invalid action']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request']);
}
?>
