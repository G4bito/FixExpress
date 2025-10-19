<?php
include '../database/server.php';
include 'includes/admin_auth.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $worker_id = $_POST['worker_id'] ?? '';
    $first_name = $_POST['first_name'] ?? '';
    $last_name = $_POST['last_name'] ?? '';
    $contact = $_POST['contact'] ?? '';
    $email = $_POST['email'] ?? '';
    $address = $_POST['address'] ?? '';
    $service_id = $_POST['service_id'] ?? '';
    $experience = $_POST['experience'] ?? '';
    $status = $_POST['status'] ?? 'Pending';

    // Validate inputs
    if (!$worker_id || !$first_name || !$last_name || !$contact || !$email || !$address || !$service_id || !$experience) {
        header("Location: bookings.php?error=missing_fields");
        exit();
    }

    // Sanitize inputs
    $worker_id = mysqli_real_escape_string($conn, $worker_id);
    $first_name = mysqli_real_escape_string($conn, $first_name);
    $last_name = mysqli_real_escape_string($conn, $last_name);
    $contact = mysqli_real_escape_string($conn, $contact);
    $email = mysqli_real_escape_string($conn, $email);
    $address = mysqli_real_escape_string($conn, $address);
    $service_id = mysqli_real_escape_string($conn, $service_id);
    $experience = mysqli_real_escape_string($conn, $experience);
    $status = mysqli_real_escape_string($conn, $status);

    // Update the worker
    $sql = "UPDATE workers SET 
            first_name = '$first_name',
            last_name = '$last_name',
            contact = '$contact',
            email = '$email',
            address = '$address',
            service_id = '$service_id',
            experience = '$experience',
            status = '$status'
            WHERE worker_id = '$worker_id'";

    if ($conn->query($sql)) {
        header("Location: bookings.php?success=worker_updated");
    } else {
        header("Location: bookings.php?error=update_failed");
    }
    exit();
} else {
    header("Location: bookings.php");
    exit();
}
?>