<?php
require_once '../includes/admin_check.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $category_id = isset($_POST['category_id']) ? intval($_POST['category_id']) : 0;
    
    if ($category_id <= 0) {
        echo json_encode(['success' => false, 'message' => 'Invalid category ID']);
        exit;
    }
    
    $database = new Database();
    $db = $database->getConnection();
    
    $query = "DELETE FROM categories WHERE id = :id";
    $stmt = $db->prepare($query);
    $stmt->bindParam(':id', $category_id);
    
    if ($stmt->execute()) {
        echo json_encode(['success' => true, 'message' => 'Category deleted successfully']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to delete category']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request']);
}
?>
