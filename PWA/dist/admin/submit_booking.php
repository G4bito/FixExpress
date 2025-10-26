<?php
include '../database/server.php';

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    // Check if POST data is empty, which can happen if post_max_size is exceeded.
    if (empty($_POST) && isset($_SERVER['CONTENT_LENGTH']) && $_SERVER['CONTENT_LENGTH'] > 0) {
        $max_upload_size = ini_get('post_max_size');
        die("Error: The uploaded file is too large. Your server's maximum upload size is " . $max_upload_size . ". Please upload a smaller file.");
    }


    $worker_id  = $_POST['worker_id'];
    $service_id = $_POST['service_id'];
    $fullname   = $_POST['fullname'];
    $contact    = $_POST['contact'];
    $email      = $_POST['email'];
    $address    = $_POST['address'];
    $date       = $_POST['date'];
    $time       = $_POST['time'];
    $notes      = $_POST['notes'];
    $image_path = null;

    // Handle file upload
    if (isset($_FILES['problem_image']) && $_FILES['problem_image']['error'] == 0) {
        $target_dir = "../../uploads/booking_images/";
        if (!file_exists($target_dir)) {
            mkdir($target_dir, 0777, true);
        }

        $file_extension = pathinfo($_FILES["problem_image"]["name"], PATHINFO_EXTENSION);
        $allowed_types = ['jpg', 'jpeg', 'png', 'gif', 'mp4', 'mov', 'webm'];
        if (!in_array(strtolower($file_extension), $allowed_types)) {
            die("Error: Only JPG, PNG, GIF, MP4, MOV, and WEBM files are allowed.");
        }

        if ($_FILES["problem_image"]["size"] > 52428800) { // 50MB limit
            die("Error: Your file is too large. Maximum size is 50MB.");
        }

        $unique_filename = uniqid('booking_', true) . '.' . $file_extension;
        $target_file = $target_dir . $unique_filename;

        if (move_uploaded_file($_FILES["problem_image"]["tmp_name"], $target_file)) {
            // Store relative path from the web root for web access
            $image_path = "uploads/booking_images/" . $unique_filename;
        } else {
            // Optional: handle file move error
            error_log("Failed to move uploaded file for booking.");
        }
    }

    
    $query = $conn->query("SELECT service_id FROM workers WHERE worker_id = $worker_id");
    $service = $query->fetch_assoc();
    $service_id = $service['service_id'];

    // Get user_id from session
    session_start();
    $user_id = $_SESSION['user_id'] ?? null;
    
    if (!$user_id) {
        die("Error: You must be logged in to make a booking");
    }

    // Save booking with user_id
    $stmt = $conn->prepare("INSERT INTO bookings (user_id, worker_id, fullname, contact, email, address, date, time, notes, service_id, problem_image_path) 
                           VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("iisssssssis", $user_id, $worker_id, $fullname, $contact, $email, $address, $date, $time, $notes, $service_id, $image_path);

    if ($stmt->execute()) {
        header("Location: service_details.php?id=$service_id&success=1");
        exit();
    } else {
        echo "Error: " . $conn->error;
    }
}
?>
