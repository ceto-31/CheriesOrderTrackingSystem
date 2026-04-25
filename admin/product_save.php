<?php
require_once '../includes/admin_check.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $database = new Database();
    $db = $database->getConnection();
    
    $product_id = isset($_POST['product_id']) ? intval($_POST['product_id']) : 0;
    $name = clean_input($_POST['name']);
    $description = clean_input($_POST['description']);
    $category_id = intval($_POST['category_id']);
    $price = floatval($_POST['price']);
    $stock = intval($_POST['stock']);
    $is_available = isset($_POST['is_available']) ? 1 : 0;
    
    // Validation
    if (empty($name) || $category_id <= 0 || $price <= 0 || $stock < 0) {
        echo json_encode(['success' => false, 'message' => 'Please fill in all required fields']);
        exit;
    }
    
    // Handle image upload
    $image_name = '';
    $upload_dir = UPLOAD_PATH;
    
    // Create upload directory if it doesn't exist
    if (!file_exists($upload_dir)) {
        mkdir($upload_dir, 0777, true);
    }
    
    if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
        $allowed_types = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif', 'image/webp'];
        $file_type = $_FILES['image']['type'];
        
        if (in_array($file_type, $allowed_types)) {
            $file_extension = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
            $image_name = uniqid() . '_' . time() . '.' . $file_extension;
            $upload_path = $upload_dir . $image_name;
            
            if (!move_uploaded_file($_FILES['image']['tmp_name'], $upload_path)) {
                echo json_encode(['success' => false, 'message' => 'Failed to upload image']);
                exit;
            }
        } else {
            echo json_encode(['success' => false, 'message' => 'Invalid image type. Allowed: JPG, PNG, GIF, WEBP']);
            exit;
        }
    }
    
    try {
        if ($product_id > 0) {
            // Update existing product
            if (!empty($image_name)) {
                // Delete old image
                $old_query = "SELECT image FROM products WHERE id = :id";
                $old_stmt = $db->prepare($old_query);
                $old_stmt->bindParam(':id', $product_id);
                $old_stmt->execute();
                $old_product = $old_stmt->fetch();
                
                if ($old_product && !empty($old_product['image'])) {
                    $old_image_path = $upload_dir . $old_product['image'];
                    if (file_exists($old_image_path)) {
                        unlink($old_image_path);
                    }
                }
                
                $query = "UPDATE products SET name = :name, description = :description, 
                          category_id = :category_id, price = :price, stock = :stock, 
                          is_available = :is_available, image = :image 
                          WHERE id = :id";
            } else {
                $query = "UPDATE products SET name = :name, description = :description, 
                          category_id = :category_id, price = :price, stock = :stock, 
                          is_available = :is_available 
                          WHERE id = :id";
            }
            
            $stmt = $db->prepare($query);
            $stmt->bindParam(':name', $name);
            $stmt->bindParam(':description', $description);
            $stmt->bindParam(':category_id', $category_id);
            $stmt->bindParam(':price', $price);
            $stmt->bindParam(':stock', $stock);
            $stmt->bindParam(':is_available', $is_available);
            $stmt->bindParam(':id', $product_id);
            
            if (!empty($image_name)) {
                $stmt->bindParam(':image', $image_name);
            }
            
            if ($stmt->execute()) {
                echo json_encode(['success' => true, 'message' => 'Product updated successfully']);
            } else {
                echo json_encode(['success' => false, 'message' => 'Failed to update product']);
            }
        } else {
            // Insert new product
            $query = "INSERT INTO products (name, description, category_id, price, stock, is_available, image) 
                      VALUES (:name, :description, :category_id, :price, :stock, :is_available, :image)";
            $stmt = $db->prepare($query);
            $stmt->bindParam(':name', $name);
            $stmt->bindParam(':description', $description);
            $stmt->bindParam(':category_id', $category_id);
            $stmt->bindParam(':price', $price);
            $stmt->bindParam(':stock', $stock);
            $stmt->bindParam(':is_available', $is_available);
            $stmt->bindParam(':image', $image_name);
            
            if ($stmt->execute()) {
                echo json_encode(['success' => true, 'message' => 'Product added successfully']);
            } else {
                echo json_encode(['success' => false, 'message' => 'Failed to add product']);
            }
        }
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request']);
}
?>
