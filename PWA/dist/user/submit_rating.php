<?php
session_start();

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['error' => 'User not logged in']);
    exit();
}

// Database connection
$host = "localhost";
$user = "root";
$pass = "";
$dbname = "fixexpress";

$conn = new mysqli($host, $user, $pass, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['error' => 'User not logged in']);
    exit();
}

// Get POST data
$data = json_decode(file_get_contents('php://input'), true);

if (!isset($data['booking_id']) || !isset($data['rating'])) {
    http_response_code(400);
    echo json_encode(['error' => 'Missing required fields']);
    exit();
}

$booking_id = $data['booking_id'];
$rating = $data['rating'];
$comment = $data['comment'] ?? '';
$user_id = $_SESSION['user_id'];

// Verify the booking belongs to the user and is completed
$checkQuery = "SELECT worker_id FROM bookings WHERE booking_id = ? AND user_id = ? AND status = 'Completed'";
$stmt = $conn->prepare($checkQuery);
$stmt->bind_param("ii", $booking_id, $user_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    http_response_code(403);
    echo json_encode(['error' => 'Invalid booking or not authorized']);
    exit();
}

$booking = $result->fetch_assoc();
$worker_id = $booking['worker_id'];

// Check if rating already exists
$checkRating = "SELECT rating_id FROM worker_ratings WHERE booking_id = ? AND user_id = ?";
$stmt = $conn->prepare($checkRating);
$stmt->bind_param("ii", $booking_id, $user_id);
$stmt->execute();

if ($stmt->get_result()->num_rows > 0) {
    http_response_code(400);
    echo json_encode(['error' => 'Rating already submitted']);
    exit();
}

// Insert the rating
$query = "INSERT INTO worker_ratings (booking_id, user_id, worker_id, rating, comment) VALUES (?, ?, ?, ?, ?)";
$stmt = $conn->prepare($query);
$stmt->bind_param("iiiis", $booking_id, $user_id, $worker_id, $rating, $comment);

if ($stmt->execute()) {
    http_response_code(200);
    echo json_encode(['success' => true]);
} else {
    http_response_code(500);
    echo json_encode(['error' => 'Failed to submit rating']);
}

$stmt->close();
$conn->close();
?>