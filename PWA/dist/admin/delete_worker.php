<?php
// Include database connection
require_once '../database/server.php';

// Check if worker_id is set and not empty
if(isset($_POST['worker_id']) && !empty($_POST['worker_id'])) {
    $worker_id = $_POST['worker_id'];
    
    // Prepare the delete statement
    $stmt = $conn->prepare("DELETE FROM workers WHERE worker_id = ?");
    $stmt->bind_param("i", $worker_id);
    
    // Execute and check if successful
    if($stmt->execute()) {
        header('Location: bookings.php?success=1&message=' . urlencode('Worker deleted successfully'));
    } else {
        header('Location: bookings.php?error=1&message=' . urlencode('Error deleting worker'));
    }
    
    $stmt->close();
} else {
    header('Location: bookings.php?error=1&message=' . urlencode('Invalid worker ID'));
}

$conn->close();
?>