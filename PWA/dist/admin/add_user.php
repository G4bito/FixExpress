<?php
include '../database/server.php';
include 'includes/admin_auth.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $first_name = $_POST['first_name'] ?? '';
    $last_name = $_POST['last_name'] ?? '';
    $username = $_POST['username'] ?? '';
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';

    if (empty($first_name) || empty($last_name) || empty($username) || empty($email) || empty($password)) {
        header("Location: bookings.php?error=1&message=" . urlencode('All fields are required for new user.'));
        exit();
    }

    $hashed_password = password_hash($password, PASSWORD_DEFAULT); 

    $sql = "INSERT INTO users (first_name, last_name, username, email, password) VALUES (?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sssss", $first_name, $last_name, $username, $email, $hashed_password);

    if ($stmt->execute()) {
        header("Location: bookings.php?success=1&message=" . urlencode('User added successfully.'));
    } else {
        header("Location: bookings.php?error=1&message=" . urlencode('Error adding user: ' . $conn->error));
    }

    $stmt->close();
    $conn->close();
}
?>