<?php
require_once('../database/server.php');

if (!isset($_GET['booking_id'])) {
    die(json_encode(['error' => 'No booking ID provided']));
}

$booking_id = intval($_GET['booking_id']);

// Get the uploaded file path for the booking
$sql = "SELECT upload_file FROM bookings WHERE booking_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $booking_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    die(json_encode(['error' => 'Booking not found']));
}

$row = $result->fetch_assoc();
$filePath = $row['upload_file'];

// Check if there's an uploaded file
if (empty($filePath)) {
    die(json_encode(['error' => 'No file uploaded for this booking']));
}

// Return the file path
echo json_encode(['success' => true, 'filePath' => $filePath]);