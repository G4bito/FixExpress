<?php
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['worker_id'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Worker not logged in.']);
    exit();
}

include '../database/server.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Invalid request method.']);
    exit();
}

$worker_id = $_SESSION['worker_id'];
$booking_id = $_POST['booking_id'] ?? 0;

if (empty($booking_id)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Invalid booking ID.']);
    exit();
}

$stmt = $conn->prepare("UPDATE bookings SET status = 'Cancelled' WHERE booking_id = ? AND worker_id = ? AND status = 'Cancellation Requested'");
$stmt->bind_param("ii", $booking_id, $worker_id);

if ($stmt->execute() && $stmt->affected_rows > 0) {
    echo json_encode(['success' => true, 'message' => 'Cancellation approved.']);
} else {
    echo json_encode(['success' => false, 'message' => 'Could not approve cancellation. The request may no longer be valid.']);
}

$stmt->close();
$conn->close();