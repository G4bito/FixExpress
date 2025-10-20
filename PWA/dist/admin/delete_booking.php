<?php
// Include database connection
require_once '../database/server.php';

// Check if booking_id is set and not empty
if(isset($_POST['booking_id']) && !empty($_POST['booking_id'])) {
    $booking_id = $_POST['booking_id'];
    
    // Prepare the delete statement
    $stmt = $conn->prepare("DELETE FROM bookings WHERE booking_id = ?");
    $stmt->bind_param("i", $booking_id);
    
    // Execute and check if successful
    if($stmt->execute()) {
        header('Location: bookings.php?success=1&message=' . urlencode('Booking deleted successfully'));
    } else {
        header('Location: bookings.php?error=1&message=' . urlencode('Error deleting booking'));
    }
    
    $stmt->close();
} else {
    header('Location: bookings.php?error=1&message=' . urlencode('Invalid booking ID'));
}

$conn->close();
?>