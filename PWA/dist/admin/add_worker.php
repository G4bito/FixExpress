<?php
include '../database/server.php';
include 'includes/admin_auth.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $first_name = $_POST['first_name'] ?? '';
    $last_name = $_POST['last_name'] ?? '';
    $username = $_POST['username'] ?? '';
    $contact = $_POST['contact'] ?? '';
    $email = $_POST['email'] ?? '';
    $address = $_POST['address'] ?? '';
    $experience = $_POST['experience'] ?? 0;
    $service_id = intval($_POST['service_id'] ?? 0);
    $password = $_POST['password'] ?? '';
    $status = 'Approved'; // Admins add workers as pre-approved

    if (empty($first_name) || empty($username) || empty($contact) || empty($email) || empty($password) || $service_id <= 0) {
        header("Location: bookings.php?error=1&message=" . urlencode('Missing required fields for new worker.'));
        exit();
    }

    $hashed_password = password_hash($password, PASSWORD_DEFAULT);

    $sql = "INSERT INTO workers (first_name, last_name, username, contact, email, address, experience, service_id, password, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssssssssss", $first_name, $last_name, $username, $contact, $email, $address, $experience, $service_id, $hashed_password, $status);

    if ($stmt->execute()) {
        header("Location: bookings.php?success=1&message=" . urlencode('Worker added successfully.'));
    } else {
        header("Location: bookings.php?error=1&message=" . urlencode('Error adding worker: ' . $conn->error));
    }

    $stmt->close();
    $conn->close();
} else {
    header("Location: bookings.php");
    exit();
}
?>