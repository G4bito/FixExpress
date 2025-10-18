<?php
include '../database/server.php';

if (isset($_GET['id'])) {
    $id = $_GET['id'];
    $update = $conn->prepare("UPDATE workers SET status = 'Approved' WHERE worker_id = ?");
    $update->bind_param("i", $id);
    $update->execute();
}

header("Location: bookings.php?worker_updated=1");
exit;
?>
