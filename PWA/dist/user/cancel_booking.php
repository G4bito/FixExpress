<?php
// Start output buffering right away
ob_start();

// Set headers
header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-cache, must-revalidate');

session_start();

// Prevent PHP errors from being output
ini_set('display_errors', 0);
error_reporting(E_ALL);

// Error handler to catch any PHP errors and return them as JSON
set_error_handler(function($errno, $errstr, $errfile, $errline) {
    ob_clean();
    echo json_encode([
        'success' => false,
        'message' => 'PHP Error: ' . $errstr,
        'error_details' => [
            'file' => $errfile,
            'line' => $errline
        ]
    ]);
    exit();
});

// Check login
if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'You must be logged in to cancel a booking.']);
    exit();
}

require_once '../database/server.php';

// Check database connection
if ($conn->connect_error) {
    ob_clean();
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Database connection failed'
    ]);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Invalid request method.']);
    exit();
}

// Get inputs
$user_id = $_SESSION['user_id'];

// Parse JSON input
$rawInput = file_get_contents('php://input');
$input = json_decode($rawInput, true);

// Check for JSON decode errors
if (json_last_error() !== JSON_ERROR_NONE) {
    ob_clean();
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => 'Invalid JSON input',
        'error' => json_last_error_msg()
    ]);
    exit();
}

// Debug logging
error_log("Raw Input: " . $rawInput);
error_log("Decoded Input: " . print_r($input, true));

$booking_id = isset($input['booking_id']) ? $input['booking_id'] : 0;
$reason = isset($input['reason']) ? $input['reason'] : null;

error_log("Booking ID: " . $booking_id);
error_log("Reason: " . $reason);

if (empty($booking_id) || !is_numeric($booking_id)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Invalid booking ID.']);
    exit();
}

// Get booking
$status_stmt = $conn->prepare(" 
    SELECT b.status, b.worker_id, w.first_name as worker_first_name, w.last_name as worker_last_name
    FROM bookings b
    LEFT JOIN workers w ON b.worker_id = w.worker_id
    LEFT JOIN users u ON b.user_id = u.user_id
    WHERE b.booking_id = ? AND b.user_id = ?
");
$status_stmt->bind_param("ii", $booking_id, $user_id);
$status_stmt->execute();
$result = $status_stmt->get_result();
$booking = $result->fetch_assoc();
$status_stmt->close();

if (!$booking) {
    http_response_code(404);
    echo json_encode(['success' => false, 'message' => 'Booking not found or you are not authorized to modify it.']);
    exit();
}

// Determine new status
$current_status = $booking['status'];
if ($current_status === 'Pending') {
    $new_status = 'Cancelled';
    $success_message = 'Booking cancelled successfully.';
} elseif ($current_status === 'Approved') {
    $new_status = 'Cancellation Requested';
    $success_message = 'Cancellation request sent to the professional.';
} else {
    echo json_encode(['success' => false, 'message' => 'This booking cannot be cancelled at its current stage.']);
    exit();
}

// Start transaction
$conn->begin_transaction();

try {
    // Update booking
    $stmt = $conn->prepare("UPDATE bookings SET status = ?, cancellation_reason = ? WHERE booking_id = ? AND user_id = ?");
    $stmt->bind_param("ssii", $new_status, $reason, $booking_id, $user_id);
    
    // Execute update
    $execute_success = $stmt->execute();
    $affected_rows = $stmt->affected_rows;
    
    if (!$execute_success) {
        throw new Exception('Failed to update booking status');
    }

    if ($affected_rows <= 0) {
        throw new Exception('Booking could not be cancelled. It may have already been completed or cancelled.');
    }
    
    // No need to create notifications as workers can see requests in workers.php
    
    // If we get here, commit the transaction
    $conn->commit();
    
    // Clean output buffer and send success response
    ob_end_clean();
    http_response_code(200);
    echo json_encode([
        'success' => true,
        'message' => $success_message,
        'new_status' => $new_status
    ]);
    
} catch (Exception $e) {
    // Roll back transaction on error
    $conn->rollback();
    
    // Clean output buffer and send error response
    ob_end_clean();
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
} finally {
    // Close prepared statement and connection
    if (isset($stmt)) {
        $stmt->close();
    }
    if (isset($conn)) {
        $conn->close();
    }

}
