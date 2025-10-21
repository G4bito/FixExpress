<?php
$conn = new mysqli('localhost', 'root', '', 'fixexpress');
if ($conn->connect_error) {
    die('Connection failed: ' . $conn->connect_error);
}

// Check workers table
echo "=== Workers Table ===\n";
$result = $conn->query('SELECT COUNT(*) as count FROM workers');
$row = $result->fetch_assoc();
echo "Total workers: " . $row['count'] . "\n\n";

// Check bookings with workers
echo "=== Bookings with Workers ===\n";
$result = $conn->query('SELECT COUNT(*) as total, 
                              SUM(CASE WHEN worker_id IS NOT NULL THEN 1 ELSE 0 END) as with_worker,
                              SUM(CASE WHEN worker_id IS NULL THEN 1 ELSE 0 END) as without_worker 
                       FROM bookings');
$row = $result->fetch_assoc();
echo "Total bookings: " . $row['total'] . "\n";
echo "Bookings with worker assigned: " . $row['with_worker'] . "\n";
echo "Bookings without worker: " . $row['without_worker'] . "\n\n";

// Sample of recent bookings
echo "=== Recent Bookings Sample ===\n";
$result = $conn->query('SELECT b.booking_id, b.worker_id, b.status, 
                              w.first_name, w.last_name
                       FROM bookings b
                       LEFT JOIN workers w ON b.worker_id = w.worker_id
                       ORDER BY b.created_at DESC LIMIT 5');

while ($row = $result->fetch_assoc()) {
    echo sprintf("Booking ID: %d | Status: %s | Worker ID: %s | Worker Name: %s %s\n",
        $row['booking_id'],
        $row['status'],
        $row['worker_id'] ?? 'NULL',
        $row['first_name'] ?? 'NULL',
        $row['last_name'] ?? 'NULL'
    );
}
?>