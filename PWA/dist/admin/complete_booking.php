<?php
include '../database/server.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $booking_id = isset($_POST['booking_id']) ? intval($_POST['booking_id']) : 0;
    
    if ($booking_id > 0) {
        // Update the booking status to Completed
        $stmt = $conn->prepare("UPDATE bookings SET status = 'Completed' WHERE booking_id = ? AND status = 'Approved'");
        $stmt->bind_param("i", $booking_id);
        
        if ($stmt->execute()) {
            echo json_encode(['success' => true, 'message' => 'Booking marked as completed successfully']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to complete booking']);
        }
        $stmt->close();
    } else {
        echo json_encode(['success' => false, 'message' => 'Invalid booking ID']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
}
?>