<?php
session_start();

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
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

// Get POST data
$user_id = $_SESSION['user_id'];
$fullname = $_POST['fullname'] ?? '';
$username = $_POST['username'] ?? '';
$email = $_POST['email'] ?? '';
$password = $_POST['password'] ?? '';

// Split fullname into first and last name
$parts = explode(' ', trim($fullname));
$first_name = $parts[0] ?? '';
$last_name = isset($parts[1]) ? implode(' ', array_slice($parts, 1)) : '';

// Check if email already exists for another user
$checkEmail = $conn->prepare("SELECT user_id FROM users WHERE email = ? AND user_id != ?");
$checkEmail->bind_param("si", $email, $user_id);
$checkEmail->execute();
$emailResult = $checkEmail->get_result();
if ($emailResult->num_rows > 0) {
    echo json_encode(['status' => 'error', 'message' => 'Email already in use']);
    exit();
}
$checkEmail->close();

// Check if username already exists for another user
$checkUsername = $conn->prepare("SELECT user_id FROM users WHERE username = ? AND user_id != ?");
$checkUsername->bind_param("si", $username, $user_id);
$checkUsername->execute();
$usernameResult = $checkUsername->get_result();
if ($usernameResult->num_rows > 0) {
    echo json_encode(['status' => 'error', 'message' => 'Username already in use']);
    exit();
}
$checkUsername->close();

// Prepare base update query
$updateFields = ["first_name=?", "last_name=?", "username=?", "email=?"];
$params = [$first_name, $last_name, $username, $email];
$types = "ssss";

// Add password to update if provided
if (!empty($password)) {
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);
    $updateFields[] = "password=?";
    $params[] = $hashed_password;
    $types .= "s";
}

// Add user_id to parameters
$params[] = $user_id;
$types .= "i";

// Prepare and execute update query
$query = "UPDATE users SET " . implode(", ", $updateFields) . " WHERE user_id=?";
$stmt = $conn->prepare($query);
$stmt->bind_param($types, ...$params);

try {
    if ($stmt->execute()) {
        // Update session data
        $_SESSION['username'] = $username;
        echo json_encode([
            'status' => 'success',
            'message' => 'Profile updated successfully',
            'data' => [
                'first_name' => $first_name,
                'last_name' => $last_name,
                'username' => $username,
                'email' => $email
            ]
        ]);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Update failed: ' . $stmt->error]);
    }
} catch (Exception $e) {
    echo json_encode(['status' => 'error', 'message' => 'Update failed: ' . $e->getMessage()]);
}

$stmt->close();
$conn->close();
?>
