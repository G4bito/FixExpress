<?php
include '../database/server.php';
include 'includes/admin_auth.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $booking_id = isset($_POST['booking_id']) ? intval($_POST['booking_id']) : 0;
    
    // Validate booking_id
    if ($booking_id <= 0) {
        die("Invalid booking ID");
    }
    
    // Get and sanitize form data
    $fullname = $conn->real_escape_string($_POST['fullname'] ?? '');
    $contact = $conn->real_escape_string($_POST['contact'] ?? '');
    $email = $conn->real_escape_string($_POST['email'] ?? '');
    $address = $conn->real_escape_string($_POST['address'] ?? '');
    $date = $conn->real_escape_string($_POST['date'] ?? '');
    $time = $conn->real_escape_string($_POST['time'] ?? '');
    $service_id = intval($_POST['service_id'] ?? 0);
    $status = $conn->real_escape_string($_POST['status'] ?? '');
    
    // Validate status
    $valid_statuses = ['Pending', 'Approved', 'Completed', 'Cancelled', 'Cancellation Requested'];
    if (!in_array($status, $valid_statuses)) {
        die("Invalid status");
    }
    
    // Update booking
    $sql = "UPDATE bookings SET 
            fullname = ?, 
            contact = ?, 
            email = ?, 
            address = ?, 
            date = ?, 
            time = ?, 
            service_id = ?,
            status = ?
            WHERE booking_id = ?";
            
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssssssssi", 
        $fullname, 
        $contact, 
        $email, 
        $address, 
        $date, 
        $time, 
        $service_id, 
        $status, 
        $booking_id
    );
    
    if ($stmt->execute()) {
        header("Location: bookings.php?msg=Booking updated successfully");
    } else {
        die("Error updating booking: " . $conn->error);
    }
    
    $stmt->close();
} else {
    die("Invalid request method");
}
?>