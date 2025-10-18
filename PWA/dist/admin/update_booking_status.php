<?php
session_start();
include './dist/database/dbconfig.php';

// Make sure it's a POST request
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // Check if worker is logged in
    if(!isset($_SESSION['worker_id'])){
        echo 'error';
        exit();
    }

    // Get POST data and sanitize
    $booking_id = intval($_POST['booking_id']);
    $status = $_POST['status'];
    $worker_id = $_SESSION['worker_id'];

    // Validate status
    $valid_statuses = ['pending','confirmed','completed','cancelled'];
    if(!in_array($status, $valid_statuses)){
        echo 'error';
        exit();
    }

    // Optional: Make sure the booking belongs to this worker
    $stmt = $connect->prepare("SELECT worker_id FROM bookings WHERE booking_id = ?");
    $stmt->bind_param("i", $booking_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $booking = $result->fetch_assoc();

    if(!$booking || $booking['worker_id'] != $worker_id){
        echo 'error';
        exit();
    }

    // Update booking status
    $stmt = $connect->prepare("UPDATE bookings SET status = ? WHERE booking_id = ?");
    $stmt->bind_param("si", $status, $booking_id);
    if($stmt->execute()){
        echo 'success';
    } else {
        echo 'error';
    }
} else {
    echo 'error';
}
?>
