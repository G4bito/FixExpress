<?php
include '../database/server.php';
include 'includes/admin_auth.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $fullname = $_POST['fullname'] ?? '';
    $contact = $_POST['contact'] ?? '';
    $email = $_POST['email'] ?? '';
    $address = $_POST['address'] ?? '';
    $date = $_POST['date'] ?? '';
    $time = $_POST['time'] ?? '';
    $service_id = intval($_POST['service_id'] ?? 0);
    $status = $_POST['status'] ?? 'Pending';

    // Basic validation
    if (empty($fullname) || empty($contact) || empty($date) || empty($time) || $service_id <= 0) {
        header("Location: bookings.php?error=1&message=" . urlencode('Missing required fields.'));
        exit();
    }

    $sql = "INSERT INTO bookings (fullname, contact, email, address, date, time, service_id, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssssssis", $fullname, $contact, $email, $address, $date, $time, $service_id, $status);

    if ($stmt->execute()) {
        header("Location: bookings.php?success=1&message=" . urlencode('Booking added successfully.'));
    } else {
        header("Location: bookings.php?error=1&message=" . urlencode('Error adding booking: ' . $conn->error));
    }

    $stmt->close();
    $conn->close();
} else {
    header("Location: bookings.php");
    exit();
}
?>