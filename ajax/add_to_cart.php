<?php
require_once '../config/config.php';

header('Content-Type: application/json');

if (!is_logged_in()) {
    echo json_encode(['success' => false, 'message' => 'Please log in first']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $product_id = isset($_POST['product_id']) ? intval($_POST['product_id']) : 0;
    $quantity = isset($_POST['quantity']) ? intval($_POST['quantity']) : 1;
    $user_id = $_SESSION['user_id'];
    
    if ($product_id <= 0 || $quantity <= 0) {
        echo json_encode(['success' => false, 'message' => 'Invalid input']);
        exit;
    }
    
    $database = new Database();
    $db = $database->getConnection();
    
    // Check if product exists and has stock
    $check_query = "SELECT id, name, stock FROM products WHERE id = :id AND is_available = 1";
    $check_stmt = $db->prepare($check_query);
    $check_stmt->bindParam(':id', $product_id);
    $check_stmt->execute();
    
    if ($check_stmt->rowCount() == 0) {
        echo json_encode(['success' => false, 'message' => 'Product not available']);
        exit;
    }
    
    $product = $check_stmt->fetch();
    
    if ($product['stock'] < $quantity) {
        echo json_encode(['success' => false, 'message' => 'Insufficient stock']);
        exit;
    }
    
    // Check if item already in cart
    $cart_check = "SELECT id, quantity FROM cart WHERE user_id = :user_id AND product_id = :product_id";
    $cart_stmt = $db->prepare($cart_check);
    $cart_stmt->bindParam(':user_id', $user_id);
    $cart_stmt->bindParam(':product_id', $product_id);
    $cart_stmt->execute();
    
    if ($cart_stmt->rowCount() > 0) {
        // Update quantity
        $cart_item = $cart_stmt->fetch();
        $new_quantity = $cart_item['quantity'] + $quantity;
        
        if ($new_quantity > $product['stock']) {
            echo json_encode(['success' => false, 'message' => 'Cannot add more than available stock']);
            exit;
        }
        
        $update_query = "UPDATE cart SET quantity = :quantity WHERE id = :id";
        $update_stmt = $db->prepare($update_query);
        $update_stmt->bindParam(':quantity', $new_quantity);
        $update_stmt->bindParam(':id', $cart_item['id']);
        
        if ($update_stmt->execute()) {
            echo json_encode(['success' => true, 'message' => 'Cart updated successfully']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to update cart']);
        }
    } else {
        // Insert new item
        $insert_query = "INSERT INTO cart (user_id, product_id, quantity) VALUES (:user_id, :product_id, :quantity)";
        $insert_stmt = $db->prepare($insert_query);
        $insert_stmt->bindParam(':user_id', $user_id);
        $insert_stmt->bindParam(':product_id', $product_id);
        $insert_stmt->bindParam(':quantity', $quantity);
        
        if ($insert_stmt->execute()) {
            echo json_encode(['success' => true, 'message' => 'Added to cart successfully']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to add to cart']);
        }
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request']);
}
?>
