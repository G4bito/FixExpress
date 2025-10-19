<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

include '../database/server.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Log received data
    error_log('Received POST data: ' . print_r($_POST, true));
    
    $booking_id = isset($_POST['booking_id']) ? intval($_POST['booking_id']) : 0;
    
    if ($booking_id <= 0) {
        echo json_encode([
            'success' => false, 
            'message' => 'Invalid booking ID: ' . $booking_id,
            'debug' => ['received' => $_POST]
        ]);
        exit;
    }

    // Verify booking exists and is in Pending state
    $check = $conn->prepare("SELECT status FROM bookings WHERE booking_id = ?");
    $check->bind_param("i", $booking_id);
    $check->execute();
    $result = $check->get_result();
    
    if ($result->num_rows === 0) {
        echo json_encode([
            'success' => false, 
            'message' => 'Booking not found'
        ]);
        exit;
    }
    
    $booking = $result->fetch_assoc();
    if ($booking['status'] !== 'Pending') {
        echo json_encode([
            'success' => false, 
            'message' => 'Booking is not in Pending state'
        ]);
        exit;
    }

    // Update the booking status to Cancelled
    $stmt = $conn->prepare("UPDATE bookings SET status = 'Cancelled' WHERE booking_id = ? AND status = 'Pending'");
    $stmt->bind_param("i", $booking_id);
    
    if ($stmt->execute()) {
        if ($stmt->affected_rows > 0) {
            echo json_encode([
                'success' => true, 
                'message' => 'Booking cancelled successfully'
            ]);
        } else {
            echo json_encode([
                'success' => false, 
                'message' => 'No changes made to booking'
            ]);
        }
    } else {
        echo json_encode([
            'success' => false, 
            'message' => 'Database error: ' . $stmt->error
        ]);
    }
    $stmt->close();
} else {
    echo json_encode([
        'success' => false, 
        'message' => 'Invalid request method'
    ]);
}
?>