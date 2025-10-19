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

    // Save booking with service_id
    $sql = "INSERT INTO bookings (worker_id, fullname, contact, email, address, date, time, notes, service_id)
            VALUES ('$worker_id', '$fullname', '$contact', '$email', '$address', '$date', '$time', '$notes', '$service_id')";

    if ($conn->query($sql)) {
        header("Location: service_details.php?id=$service_id&success=1");
        exit();
    } else {
        echo "Error: " . $conn->error;
    }
}
?>
