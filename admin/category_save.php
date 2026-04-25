<?php
require_once '../includes/admin_check.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $category_id = isset($_POST['category_id']) ? intval($_POST['category_id']) : 0;
    $name = clean_input($_POST['name']);
    $description = clean_input($_POST['description']);
    
    if (empty($name)) {
        echo json_encode(['success' => false, 'message' => 'Category name is required']);
        exit;
    }
    
    $database = new Database();
    $db = $database->getConnection();
    
    try {
        if ($category_id > 0) {
            // Update
            $query = "UPDATE categories SET name = :name, description = :description WHERE id = :id";
            $stmt = $db->prepare($query);
            $stmt->bindParam(':name', $name);
            $stmt->bindParam(':description', $description);
            $stmt->bindParam(':id', $category_id);
            
            if ($stmt->execute()) {
                echo json_encode(['success' => true, 'message' => 'Category updated successfully']);
            } else {
                echo json_encode(['success' => false, 'message' => 'Failed to update category']);
            }
        } else {
            // Insert
            $query = "INSERT INTO categories (name, description) VALUES (:name, :description)";
            $stmt = $db->prepare($query);
            $stmt->bindParam(':name', $name);
            $stmt->bindParam(':description', $description);
            
            if ($stmt->execute()) {
                echo json_encode(['success' => true, 'message' => 'Category added successfully']);
            } else {
                echo json_encode(['success' => false, 'message' => 'Failed to add category']);
            }
        }
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request']);
}
?>
