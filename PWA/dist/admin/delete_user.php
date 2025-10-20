<?php
// Include database connection
require_once '../database/server.php';

// Check if user_id is set and not empty
if(isset($_POST['user_id']) && !empty($_POST['user_id'])) {
    $user_id = $_POST['user_id'];
    
    // Prepare the delete statement
    $stmt = $conn->prepare("DELETE FROM users WHERE user_id = ?");
    $stmt->bind_param("i", $user_id);
    
    // Execute and check if successful
    if($stmt->execute()) {
        header('Location: bookings.php?success=1&message=' . urlencode('User deleted successfully'));
    } else {
        header('Location: bookings.php?error=1&message=' . urlencode('Error deleting user'));
    }
    
    $stmt->close();
} else {
    header('Location: bookings.php?error=1&message=' . urlencode('Invalid user ID'));
}

$conn->close();
?>