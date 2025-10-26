<?php
include '../database/server.php';

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $worker_id  = $_POST['worker_id'];
    $service_id = $_POST['service_id'];
    $fullname   = $_POST['fullname'];
    $contact    = $_POST['contact'];
    $email      = $_POST['email'];
    $address    = $_POST['address'];
    $date       = $_POST['date'];
    $time       = $_POST['time'];
    $notes      = $_POST['notes'];

    
    $query = $conn->query("SELECT service_id FROM workers WHERE worker_id = $worker_id");
    $service = $query->fetch_assoc();
    $service_id = $service['service_id'];

    // Get user_id from session
    session_start();
    $user_id = $_SESSION['user_id'] ?? null;
    
    if (!$user_id) {
        die("Error: You must be logged in to make a booking");
    }

    try {
        // Handle file upload
        $upload_file = null;
        if (isset($_FILES['upload_file']) && $_FILES['upload_file']['error'] == 0) {
            // Validate file size (5MB max)
            if ($_FILES['upload_file']['size'] > 5 * 1024 * 1024) {
                throw new Exception("File size too large. Maximum size is 5MB.");
            }
            
            // Validate file type
            $allowed_types = ['image/jpeg', 'image/png', 'image/gif', 'application/pdf', 'application/msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document'];
            $finfo = finfo_open(FILEINFO_MIME_TYPE);
            $mime_type = finfo_file($finfo, $_FILES['upload_file']['tmp_name']);
            finfo_close($finfo);
            
            if (!in_array($mime_type, $allowed_types)) {
                throw new Exception("Invalid file type. Allowed types: JPG, PNG, GIF, PDF, DOC, DOCX");
            }
            
            // Generate unique filename
            $extension = pathinfo($_FILES['upload_file']['name'], PATHINFO_EXTENSION);
            $filename = uniqid('upload_') . '.' . $extension;
            $upload_path = __DIR__ . '/../uploads/' . $filename;
            
            // Move uploaded file
            if (!move_uploaded_file($_FILES['upload_file']['tmp_name'], $upload_path)) {
                throw new Exception("Failed to upload file.");
            }
            
            $upload_file = $filename;
        }
        
        // Start transaction
        $conn->begin_transaction();
        
        // Save booking with user_id and upload_file
        $stmt = $conn->prepare("INSERT INTO bookings (user_id, worker_id, fullname, contact, email, address, date, time, notes, service_id, upload_file) 
                               VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("iisssssssis", $user_id, $worker_id, $fullname, $contact, $email, $address, $date, $time, $notes, $service_id, $upload_file);
        $stmt->execute();
        
        // Save contact number if it doesn't exist
        $stmt = $conn->prepare("INSERT IGNORE INTO user_contacts (user_id, contact_number) VALUES (?, ?)");
        $stmt->bind_param("is", $user_id, $contact);
        $stmt->execute();
        
        // Commit transaction
        $conn->commit();
        header("Location: service_details.php?id=$service_id&success=1");
        exit();
    } catch (Exception $e) {
        // Rollback transaction on error
        $conn->rollback();
        echo "Error: " . $e->getMessage();
    }
}
?>
