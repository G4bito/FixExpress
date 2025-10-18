<?php
include '../database/server.php';

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $worker_id = $_POST['worker_id'];
    $fullname  = $_POST['fullname'];
    $contact   = $_POST['contact'];
    $email     = $_POST['email'];
    $address   = $_POST['address'];
    $date      = $_POST['date'];
    $time      = $_POST['time'];
    $notes     = $_POST['notes'];

    // Get service_id from worker_id
    $query = $conn->query("SELECT service_id FROM workers WHERE worker_id = $worker_id");
    $service = $query->fetch_assoc();
    $service_id = $service['service_id'];

    // Save booking
    $sql = "INSERT INTO bookings (worker_id, fullname, contact, email, address, date, time, notes)
            VALUES ('$worker_id', '$fullname', '$contact', '$email', '$address', '$date', '$time', '$notes')";

    if ($conn->query($sql)) {
        header("Location: service_details.php?id=$service_id&success=1");
        exit();
    } else {
        echo "Error: " . $conn->error;
    }
}
?>
