<?php
include '../database/server.php';
include 'includes/admin_auth.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user_id = isset($_POST['user_id']) ? intval($_POST['user_id']) : 0;
    
    // Validate user_id
    if ($user_id <= 0) {
        die("Invalid user ID");
    }
    
    // Get and sanitize form data
    $first_name = $conn->real_escape_string($_POST['first_name'] ?? '');
    $last_name = $conn->real_escape_string($_POST['last_name'] ?? '');
    $username = $conn->real_escape_string($_POST['username'] ?? '');
    $email = $conn->real_escape_string($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    
    // Build update query based on whether password was provided
    if (!empty($password)) {
        // Hash the new password
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        $sql = "UPDATE users SET 
                first_name = ?, 
                last_name = ?, 
                username = ?, 
                email = ?, 
                password = ? 
                WHERE user_id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("sssssi", $first_name, $last_name, $username, $email, $hashed_password, $user_id);
    } else {
        // Update without changing password
        $sql = "UPDATE users SET 
                first_name = ?, 
                last_name = ?, 
                username = ?, 
                email = ?
                WHERE user_id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ssssi", $first_name, $last_name, $username, $email, $user_id);
    }
    
    if ($stmt->execute()) {
        header("Location: bookings.php?msg=User updated successfully");
    } else {
        die("Error updating user: " . $conn->error);
    }
    
    $stmt->close();
} else {
    die("Invalid request method");
}
?>