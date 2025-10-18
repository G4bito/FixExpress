<?php
session_start();

if (!isset($_SESSION['worker_id'])) {
    echo json_encode(['status' => 'error', 'message' => 'Not logged in']);
    exit();
}

$host = "localhost";
$user = "root";
$pass = "";
$dbname = "fixexpress";

$conn = new mysqli($host, $user, $pass, $dbname);
if ($conn->connect_error) {
    echo json_encode(['status' => 'error', 'message' => 'Database connection failed']);
    exit();
}

$worker_id = $_SESSION['worker_id'];
$fullname = $_POST['fullname'] ?? '';
$username = $_POST['username'] ?? '';
$email = $_POST['email'] ?? '';
$address = $_POST['address'] ?? '';
$service_id = $_POST['service_id'] ?? '';

$parts = explode(' ', trim($fullname));
$first_name = $parts[0] ?? '';
$last_name = isset($parts[1]) ? implode(' ', array_slice($parts, 1)) : '';

$stmt = $conn->prepare("UPDATE workers SET first_name=?, last_name=?, email=?, phone=?, address=?, service_id=? WHERE worker_id=?");
$stmt->bind_param("ssssssi", $first_name, $last_name, $email, $address, $service_id, $worker_id);

if ($stmt->execute()) {
    echo json_encode(['status' => 'success', 'message' => 'Profile updated successfully']);
} else {
    echo json_encode(['status' => 'error', 'message' => 'Update failed']);
}

$stmt->close();
$conn->close();
?>
