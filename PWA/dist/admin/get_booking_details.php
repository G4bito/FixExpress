<?php
include '../database/server.php';
session_start();

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit();
}

$booking_id = $_POST['booking_id'] ?? '';

if (!$booking_id) {
    echo json_encode(['success' => false, 'message' => 'Booking ID is required']);
    exit();
}

// Get booking details including decline reason
// Get booking details including decline reason and service name
$stmt = $conn->prepare("
    SELECT b.*, s.service_name 
    FROM bookings b 
    LEFT JOIN services s ON b.service_id = s.service_id 
    WHERE b.booking_id = ? AND b.worker_id = ?
");

$worker_id = $_SESSION['worker_id'];

$stmt->bind_param("ii", $booking_id, $worker_id);
$stmt->execute();
$result = $stmt->get_result();
$booking = $result->fetch_assoc();

if ($booking) {
    // Format the data
    $booking['date'] = date('M d, Y', strtotime($booking['date']));
    $booking['time'] = date('g:i A', strtotime($booking['time']));
    $booking['status'] = ucfirst(strtolower($booking['status']));
    $booking['service_name'] = $booking['service_name'] ?? 'Unknown Service';
    
    echo json_encode([
        'success' => true,
        'booking' => $booking
    ]);
} else {
    echo json_encode([
        'success' => false,
        'message' => 'Booking not found'
    ]);
}

$stmt->close();
?>