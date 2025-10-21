<?php
include '../database/server.php';

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $worker_id  = $_POST['worker_id'];
    $service_id = $_POST['service_id'];
    $fullname   = $_POST['fullname'];
    $contact    = $_POST['contact'];
    $email      = $_POST['email'];
    $address    = $_POST['address'];
    $date       = $_POST['date'];
    $time       = $_POST['time'];
    $notes      = $_POST['notes'];

    
    $query = $conn->query("SELECT service_id FROM workers WHERE worker_id = $worker_id");
    $service = $query->fetch_assoc();
    $service_id = $service['service_id'];

    // Get user_id from session
    session_start();
    $user_id = $_SESSION['user_id'] ?? null;
    
    if (!$user_id) {
        die("Error: You must be logged in to make a booking");
    }

    // Save booking with user_id
    $stmt = $conn->prepare("INSERT INTO bookings (user_id, worker_id, fullname, contact, email, address, date, time, notes, service_id) 
                           VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("iisssssssi", $user_id, $worker_id, $fullname, $contact, $email, $address, $date, $time, $notes, $service_id);

    if ($stmt->execute()) {
        header("Location: service_details.php?id=$service_id&success=1");
        exit();
    } else {
        echo "Error: " . $conn->error;
    }
}
?>
