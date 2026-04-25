<?php
session_start();
require_once '../config/config.php';
require_once '../config/database.php';

header('Content-Type: application/json');

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Not authenticated']);
    exit();
}

// Check if notification_id is provided
if (!isset($_POST['notification_id']) || !is_numeric($_POST['notification_id'])) {
    echo json_encode(['success' => false, 'message' => 'Invalid notification ID']);
    exit();
}

$notification_id = intval($_POST['notification_id']);
$user_id = $_SESSION['user_id'];

try {
    $database = new Database();
    $db = $database->getConnection();
    
    // Update notification to mark as read - only if it belongs to the current user
    $query = "UPDATE notifications 
              SET is_read = 1 
              WHERE id = :notification_id 
              AND user_id = :user_id 
              AND is_read = 0";
    
    $stmt = $db->prepare($query);
    $stmt->bindParam(':notification_id', $notification_id);
    $stmt->bindParam(':user_id', $user_id);
    
    if ($stmt->execute()) {
        // Get updated unread count
        $count_query = "SELECT COUNT(*) as count FROM notifications WHERE user_id = :user_id AND is_read = 0";
        $count_stmt = $db->prepare($count_query);
        $count_stmt->bindParam(':user_id', $user_id);
        $count_stmt->execute();
        $result = $count_stmt->fetch();
        $unread_count = $result['count'];
        
        echo json_encode([
            'success' => true, 
            'message' => 'Notification marked as read',
            'unread_count' => $unread_count
        ]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to update notification']);
    }
    
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
}
?>
