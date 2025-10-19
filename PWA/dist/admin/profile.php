<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$host = "localhost";
$user = "root";
$pass = "";
$dbname = "fixexpress";

$conn = new mysqli($host, $user, $pass, $dbname);
if ($conn->connect_error) die("Connection failed: " . $conn->connect_error);

$user_id = $_SESSION['user_id'];
$firstname = $_POST['firstname'];
$lastname = $_POST['lastname'];
$username = $_POST['username'];
$email = $_POST['email'];
$password = $_POST['password'];

// Update query with password optional
if (!empty($password)) {
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);
    $sql = "UPDATE users SET firstname=?, lastname=?, username=?, email=?, password=? WHERE id=?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sssssi", $firstname, $lastname, $username, $email, $hashed_password, $user_id);
} else {
    $sql = "UPDATE users SET firstname=?, lastname=?, username=?, email=? WHERE id=?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssssi", $firstname, $lastname, $username, $email, $user_id);
}

if ($stmt->execute()) {
    $_SESSION['username'] = $username;
    echo "<script>alert('Profile updated successfully!'); window.location.href='index.php';</script>";
} else {
    echo "<script>alert('Error updating profile.'); window.history.back();</script>";
}

$stmt->close();
$conn->close();
?>
