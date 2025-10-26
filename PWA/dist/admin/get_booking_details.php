<?php
include '../database/server.php';
session_start();

header('Content-Type: application/json');

if (!isset($_SESSION['worker_id'])) {
    echo json_encode(['success' => false, 'message' => 'Not logged in']);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit();
}

$booking_id = isset($_POST['booking_id']) ? intval($_POST['booking_id']) : 0;

if ($booking_id <= 0) {
    echo json_encode(['success' => false, 'message' => 'Invalid booking ID']);
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
    
    // Debug information
    error_log('Booking data: ' . print_r($booking, true));
    
    // Add file information if there's an uploaded file
    if (!empty($booking['upload_file'])) {
        error_log('Processing file: ' . $booking['upload_file']);
        
        // Check the physical file path
        $uploads_dir = dirname(dirname(__FILE__)) . '/uploads/';
        $file_path = $uploads_dir . $booking['upload_file'];
        error_log('Checking file in: ' . $uploads_dir);
        error_log('Full file path: ' . $file_path);
        
        if (file_exists($file_path)) {
            // Use path relative to workers.php location
            $file_url = './dist/uploads/' . $booking['upload_file'];
            error_log('File exists and URL set to: ' . $file_url);
        } else {
            error_log('File not found at: ' . $file_path);
            $file_url = null;
        }
        
        $extension = strtolower(pathinfo($booking['upload_file'], PATHINFO_EXTENSION));
        error_log('File extension: ' . $extension);
        
        // Check if it's an image or video
        $is_image = in_array($extension, ['jpg', 'jpeg', 'png', 'gif']);
        $is_video = in_array($extension, ['mp4']);
        
        $file_type = $is_image ? 'image' : ($is_video ? 'video' : 'other');
        
        if ($file_url !== null) {
            $booking['has_file'] = true;
            $booking['file_info'] = [
                'name' => $booking['upload_file'],
                'path' => $file_url,
                'is_image' => $is_image,
                'is_video' => $is_video,
                'file_type' => $file_type,
                'extension' => $extension
            ];
            error_log('Added file info to response: ' . json_encode($booking['file_info']));
        } else {
            $booking['has_file'] = false;
            $booking['file_info'] = null;
            error_log('No valid file found for this booking');
        }
    }
    
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