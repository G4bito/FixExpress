<?php
$server = "localhost";
$username = "root";
$password = "";
$dbname = "fixexpress";
$conn = new mysqli($server, $username, $password, $dbname);
if ($conn->connect_error) die("Connection failed: " . $conn->connect_error);

$service_id = $_GET['service_id'];

if ($service_id === 'all') {
    $query = "
        SELECT w.first_name, w.last_name, w.contact, w.email, w.experience, w.rating, s.service_name
        FROM workers AS w
        LEFT JOIN services AS s ON w.service_id = s.service_id
        ORDER BY s.service_name, w.rating DESC
    ";
} else {
    $service_id = intval($service_id);
    $query = "
        SELECT w.first_name, w.last_name, w.contact, w.email, w.experience, w.rating, s.service_name
        FROM workers AS w
        LEFT JOIN services AS s ON w.service_id = s.service_id
        WHERE w.service_id = $service_id
        ORDER BY w.rating DESC
    ";
}

$result = $conn->query($query);

if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        echo "
        <div class='card'>
            <div class='name'>{$row['first_name']} {$row['last_name']}</div>
            <div class='info'>
                <svg class='icon' xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24'>
                    <path d='M6.62 10.79a15.05 15.05 0 006.59 6.59l2.2-2.2a1 1 0 011.05-.24 11.36 11.36 0 003.55.57 1 1 0 011 1V20a1 1 0 01-1 1A17 17 0 013 4a1 1 0 011-1h3.5a1 1 0 011 1 11.36 11.36 0 00.57 3.55 1 1 0 01-.24 1.05z'/>
                </svg>
                {$row['contact']}
            </div>
            <div class='info'>
                <svg class='icon' xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24'>
                    <path d='M12 13.065L2 6.5V18h20V6.5l-10 6.565zM12 11L22 4H2l10 7z'/>
                </svg>
                {$row['email']}
            </div>
            <div class='info'>
                <svg class='icon' xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24'>
                    <path d='M14 6V4h-4v2H3v14h18V6h-7zm-2 2a2 2 0 110 4 2 2 0 010-4zm-4 10v-2a4 4 0 118 0v2H8z'/>
                </svg>
                Experience: {$row['experience']} yrs
            </div>
            <div class='info'>
                <svg class='icon' xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24'>
                    <path d='M9 21.17l-5.59-5.59L5.83 14l3.17 3.17L18.17 8l1.41 1.41z'/>
                </svg>
                Service: {$row['service_name']}
            </div>
            <div class='rating'>
                <svg class='icon' xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' fill='#ffbf00'>
                    <path d='M12 17.27L18.18 21l-1.64-7.03L22 9.24l-7.19-.61L12 2 9.19 8.63 2 9.24l5.46 4.73L5.82 21z'/>
                </svg>
                {$row['rating']}
            </div>
        </div>";
    }
} else {
    echo "<div style='grid-column: 1 / -1; text-align:center; color:#ccc;'>No professionals found.</div>";
}

$conn->close();
?>
