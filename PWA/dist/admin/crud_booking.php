<?php
include '../database/server.php';

$action = $_POST['action'] ?? '';

if ($action === 'add') {
    $stmt = $conn->prepare("INSERT INTO bookings (fullname, contact, email, address, date, time) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("ssssss", $_POST['fullname'], $_POST['contact'], $_POST['email'], $_POST['address'], $_POST['date'], $_POST['time']);
    $stmt->execute();
    header("Location: bookings.php?updated=1");
    exit;
}

if ($action === 'edit') {
    $stmt = $conn->prepare("UPDATE bookings SET fullname=?, contact=?, email=?, address=?, date=?, time=?, status=? WHERE booking_id=?");
    $stmt->bind_param("sssssssi", $_POST['fullname'], $_POST['contact'], $_POST['email'], $_POST['address'], $_POST['date'], $_POST['time'], $_POST['status'], $_POST['booking_id']);
    $stmt->execute();
    header("Location: bookings.php?updated=1");
    exit;
}

if ($action === 'delete') {
    $stmt = $conn->prepare("DELETE FROM bookings WHERE booking_id=?");
    $stmt->bind_param("i", $_POST['booking_id']);
    $stmt->execute();
    header("Location: bookings.php?updated=1");
    exit;
}
?>
