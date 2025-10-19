<?php
include '../database/server.php';
include 'includes/admin_auth.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $worker_id = isset($_POST['worker_id']) ? intval($_POST['worker_id']) : 0;
    $status = $_POST['status'] ?? '';
    
    // Validate input
    if ($worker_id <= 0) {
        die("Invalid worker ID");
    }
    
    if (!in_array($status, ['Pending', 'Approved', 'Rejected'])) {
        die("Invalid status");
    }
    
    // Update worker status
    $stmt = $conn->prepare("UPDATE workers SET status = ? WHERE worker_id = ?");
    $stmt->bind_param("si", $status, $worker_id);
    
    if ($stmt->execute()) {
        header("Location: bookings.php?msg=Worker status updated successfully");
    } else {
        die("Error updating worker status: " . $conn->error);
    }
    
    $stmt->close();
} else {
    die("Invalid request method");
}
?>