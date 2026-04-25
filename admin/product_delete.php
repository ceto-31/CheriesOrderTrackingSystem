<?php
require_once '../includes/admin_check.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $product_id = isset($_POST['product_id']) ? intval($_POST['product_id']) : 0;
    
    if ($product_id <= 0) {
        echo json_encode(['success' => false, 'message' => 'Invalid product ID']);
        exit;
    }
    
    $database = new Database();
    $db = $database->getConnection();
    
    try {
        // Get product image to delete
        $query = "SELECT image FROM products WHERE id = :id";
        $stmt = $db->prepare($query);
        $stmt->bindParam(':id', $product_id);
        $stmt->execute();
        $product = $stmt->fetch();
        
        if (!$product) {
            echo json_encode(['success' => false, 'message' => 'Product not found']);
            exit;
        }
        
        // Delete product
        $delete_query = "DELETE FROM products WHERE id = :id";
        $delete_stmt = $db->prepare($delete_query);
        $delete_stmt->bindParam(':id', $product_id);
        
        if ($delete_stmt->execute()) {
            // Delete image file
            if (!empty($product['image'])) {
                $image_path = UPLOAD_PATH . $product['image'];
                if (file_exists($image_path)) {
                    unlink($image_path);
                }
            }
            
            echo json_encode(['success' => true, 'message' => 'Product deleted successfully']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to delete product']);
        }
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request']);
}
?>
