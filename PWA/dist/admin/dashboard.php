<?php
include 'db_connection.php';
if (!isset($_SESSION['worker_id'])) {
    header("Location: login.php");
    exit();
}

$worker_id = $_SESSION['worker_id'];
$sql = "SELECT fullname, email, phone, address, specialization FROM workers WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $worker_id);
$stmt->execute();
$result = $stmt->get_result();
$worker = $result->fetch_assoc();
$stmt->close();
?>
