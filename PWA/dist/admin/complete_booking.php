<?php
include '../database/server.php';
session_start();

header('Content-Type: application/json');

if (!isset($_SESSION['worker_id'])) {
    echo json_encode(['success' => false, 'message' => 'Not logged in']);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $booking_id = isset($_POST['booking_id']) ? intval($_POST['booking_id']) : 0;
    
    if ($booking_id > 0) {
        // First check if the booking exists and belongs to the worker
        $check_stmt = $conn->prepare("SELECT status FROM bookings WHERE booking_id = ? AND worker_id = ? AND status = 'Approved'");
        $check_stmt->bind_param("ii", $booking_id, $_SESSION['worker_id']);
        $check_stmt->execute();
        $result = $check_stmt->get_result();
        
        if ($result->num_rows === 0) {
            echo json_encode(['success' => false, 'message' => 'Booking not found or cannot be completed']);
            $check_stmt->close();
            exit();
        }
        $check_stmt->close();

        // Update the booking status to Completed
        $stmt = $conn->prepare("UPDATE bookings SET status = 'Completed' WHERE booking_id = ? AND worker_id = ? AND status = 'Approved'");
        $stmt->bind_param("ii", $booking_id, $_SESSION['worker_id']);
        
        if ($stmt->execute() && $stmt->affected_rows > 0) {
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