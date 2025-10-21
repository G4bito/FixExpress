<?php
header('Content-Type: text/html; charset=utf-8');

$server = "localhost";
$username = "root";
$password = "";
$dbname = "fixexpress";
$conn = new mysqli($server, $username, $password, $dbname);
if ($conn->connect_error) {
    die("<p>Connection failed.</p>");
}

if (!isset($_GET['worker_id'])) {
    die('<div style="text-align: center; color: #ff6b6b;">No worker specified.</div>');
}

$worker_id = intval($_GET['worker_id']);

// --- Fetch worker details ---
$workerQuery = "
    SELECT 
        w.first_name, 
        w.last_name, 
        w.contact, 
        w.email, 
        w.experience,
        s.service_name
    FROM workers w
    LEFT JOIN services s ON w.service_id = s.service_id
    WHERE w.worker_id = ?
";
$workerStmt = $conn->prepare($workerQuery);
$workerStmt->bind_param("i", $worker_id);
$workerStmt->execute();
$workerResult = $workerStmt->get_result();
$worker = $workerResult->fetch_assoc();
$workerStmt->close();

if ($worker) {
    echo '<div style="margin-bottom: 20px; padding-bottom: 15px; border-bottom: 1px solid rgba(255,255,255,0.2); display: grid; grid-template-columns: 1fr 1fr; gap: 10px; color: #eee;">';
    echo '<div><strong>Contact:</strong> ' . htmlspecialchars($worker['contact']) . '</div>';
    echo '<div><strong>Email:</strong> ' . htmlspecialchars($worker['email']) . '</div>';
    echo '<div><strong>Service:</strong> ' . htmlspecialchars($worker['service_name']) . '</div>';
    echo '<div><strong>Experience:</strong> ' . htmlspecialchars($worker['experience']) . ' yrs</div>';
    echo '</div>';
}

echo '<h3 style="font-size: 1.25rem; color: #ffbf00; margin-top: 10px; margin-bottom: 20px;">Customer Feedback</h3>';

$ratingsQuery = "
    SELECT 
        wr.rating,
        wr.comment,
        wr.date_submitted,
        u.first_name,
        u.last_name,
        s.service_name
    FROM worker_ratings wr
    JOIN users u ON wr.user_id = u.user_id
    JOIN bookings b ON wr.booking_id = b.booking_id
    JOIN services s ON b.service_id = s.service_id
    WHERE wr.worker_id = ?
    ORDER BY wr.date_submitted DESC
";

$stmt = $conn->prepare($ratingsQuery);
$stmt->bind_param("i", $worker_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    while ($rating = $result->fetch_assoc()) {
        echo '<div style="background: rgba(255,255,255,0.1); padding: 15px; border-radius: 10px; margin-bottom: 15px;">';
        echo '<div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 8px;">';
        echo '<div>';
        echo '<div style="font-weight: bold;">' . htmlspecialchars($rating['first_name'] . ' ' . $rating['last_name']) . '</div>';
        echo '<div style="font-size: 0.85em; color: #ccc;">' . htmlspecialchars($rating['service_name']) . ' &bull; ' . date('M d, Y', strtotime($rating['date_submitted'])) . '</div>';
        echo '</div>';
        echo '<div style="color: #ffbf00;">';
        for ($i = 1; $i <= 5; $i++) {
            echo $i <= $rating['rating'] ? '★' : '☆';
        }
        echo '</div>';
        echo '</div>';
        if (!empty($rating['comment'])) {
            echo '<p style="font-style: italic; color: #eee;">"' . htmlspecialchars($rating['comment']) . '"</p>';
        }
        echo '</div>';
    }
} else {
    echo '<div style="text-align: center; padding: 20px; color: #ccc;">This professional has no ratings yet.</div>';
}

$stmt->close();
$conn->close();
?>