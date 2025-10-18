<?php
// edit_booking.php
// Place this file in /PWA/dist/admin/


include('./PWA/dist/database/server.php');
include 'includes/admin_auth.php';




if (!isset($_GET['id']) && $_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: bookings.php');
    exit;
}

$errors = [];
$success = false;

// Handle POST: update booking
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $booking_id = intval($_POST['booking_id']);
    $fullname   = trim($_POST['fullname']);
    $contact    = trim($_POST['contact']);
    $email      = trim($_POST['email']);
    $address    = trim($_POST['address']);
    $date       = trim($_POST['date']);
    $time       = trim($_POST['time']);
    $notes      = trim($_POST['notes']);
    $status     = trim($_POST['status']);

    if ($booking_id <= 0) $errors[] = "Invalid booking ID.";
    if (empty($fullname)) $errors[] = "Full name is required.";
    if (empty($contact)) $errors[] = "Contact number is required.";
    if (empty($date)) $errors[] = "Date is required.";
    if (empty($time)) $errors[] = "Time is required.";
    if (empty($status)) $errors[] = "Status is required.";

    if (empty($errors)) {
        $sql = "UPDATE bookings 
                SET fullname=?, contact=?, email=?, address=?, date=?, time=?, notes=?, status=? 
                WHERE booking_id=?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ssssssssi", $fullname, $contact, $email, $address, $date, $time, $notes, $status, $booking_id);
        if ($stmt->execute()) {
            $success = true;
            header('Location: bookings.php?updated=1');
            exit;
        } else {
            $errors[] = "Error updating booking: " . $stmt->error;
        }
        $stmt->close();
    }
}

// Handle GET: load existing booking
$booking = null;
if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['id'])) {
    $id = intval($_GET['id']);
    $sql = "SELECT * FROM bookings WHERE booking_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    $booking = $result->fetch_assoc();
    $stmt->close();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Edit Booking</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">

<div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h3>Edit Booking</h3>
        <a href="bookings.php" class="btn btn-secondary btn-sm">← Back to Bookings</a>
    </div>

    <?php if (!empty($errors)): ?>
    <div class="alert alert-danger">
        <ul class="mb-0">
            <?php foreach ($errors as $error): ?>
                <li><?php echo htmlspecialchars($error); ?></li>
            <?php endforeach; ?>
        </ul>
    </div>
    <?php endif; ?>

    <?php if ($booking): ?>
    <div class="card shadow-sm">
        <div class="card-body">
            <form method="post" action="">
                <input type="hidden" name="booking_id" value="<?php echo $booking['booking_id']; ?>">

                <div class="mb-3">
                    <label class="form-label">Full Name</label>
                    <input type="text" name="fullname" class="form-control" value="<?php echo htmlspecialchars($booking['fullname']); ?>" required>
                </div>

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Contact</label>
                        <input type="text" name="contact" class="form-control" value="<?php echo htmlspecialchars($booking['contact']); ?>" required>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Email</label>
                        <input type="email" name="email" class="form-control" value="<?php echo htmlspecialchars($booking['email']); ?>">
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label">Address</label>
                    <textarea name="address" class="form-control" rows="2"><?php echo htmlspecialchars($booking['address']); ?></textarea>
                </div>

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Date</label>
                        <input type="date" name="date" class="form-control" value="<?php echo htmlspecialchars($booking['date']); ?>" required>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Time</label>
                        <input type="time" name="time" class="form-control" value="<?php echo htmlspecialchars($booking['time']); ?>" required>
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label">Notes</label>
                    <textarea name="notes" class="form-control" rows="2"><?php echo htmlspecialchars($booking['notes']); ?></textarea>
                </div>

                <div class="mb-3">
                    <label class="form-label">Status</label>
                    <select name="status" class="form-select" required>
                        <?php
                        $statuses = ['Pending','Confirmed','Completed','Cancelled'];
                        foreach ($statuses as $s) {
                            $sel = ($booking['status'] === $s) ? 'selected' : '';
                            echo "<option value='$s' $sel>$s</option>";
                        }
                        ?>
                    </select>
                </div>

                <button type="submit" class="btn btn-primary">Save Changes</button>
                <a href="bookings.php" class="btn btn-outline-secondary">Cancel</a>
            </form>
        </div>
    </div>
    <?php else: ?>
    <div class="alert alert-warning">Booking not found.</div>
    <?php endif; ?>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
