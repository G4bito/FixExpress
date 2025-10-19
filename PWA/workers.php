<?php
session_start();
if (!isset($_SESSION['worker_id'])) {
    header("Location: login.php");
    exit();
}

$worker_id = $_SESSION['worker_id'];
$username = $_SESSION['username'] ?? 'W';

$host = "localhost";
$user = "root";
$pass = "";
$dbname = "fixexpress";

$conn = new mysqli($host, $user, $pass, $dbname);
if ($conn->connect_error) die("Connection failed: " . $conn->connect_error);

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $first_name = trim($_POST['first_name']);
    $last_name = trim($_POST['last_name']);
    $username_input = trim($_POST['username']);
    $email = trim($_POST['email']);
    $address = trim($_POST['address']);
    $service_id = $_POST['service_id'];
    $worker_id = $_SESSION['worker_id'];


    // Basic validation
    if (strlen($first_name) > 50 || strlen($last_name) > 50) {
      die("Error: First name or last name cannot exceed 50 characters.");
    }
    if (strlen($username_input) > 50) {
        die("Error: Username cannot exceed 50 characters.");
    }
    if (strlen($email) > 254) {
        die("Error: Email cannot exceed 254 characters.");
    }
    if (strlen($address) > 100) {
        die("Error: Address cannot exceed 100 characters.");
    }

    $stmt = $conn->prepare("
        UPDATE workers 
        SET first_name = ?, last_name = ?, username = ?, email = ?, address = ?, service_id = ?
        WHERE worker_id = ?
    ");
    $stmt->bind_param("ssssssi", $first_name, $last_name, $username_input, $email, $address, $service_id, $worker_id);


    if ($stmt->execute()) {
        $_SESSION['username'] = $username_input;
        echo "<script>alert('Profile updated successfully!');</script>";
    } else {
        echo "<script>alert('Error updating profile.');</script>";
    }

    $stmt->close();
}



// Fetch worker data
$stmt_worker = $conn->prepare("SELECT * FROM workers WHERE worker_id = ?");
$stmt_worker->bind_param("i", $worker_id);
$stmt_worker->execute();
$result_worker = $stmt_worker->get_result();
$worker = $result_worker->fetch_assoc();
$stmt_worker->close();

if ($worker) {
    $worker['fullname'] = trim(($worker['first_name'] ?? '') . ' ' . ($worker['last_name'] ?? ''));
    $worker['email'] = $worker['email'] ?? '';
    $worker['phone'] = $worker['phone'] ?? '';
    $worker['address'] = $worker['address'] ?? '';
    $worker['service_id'] = $worker['service_id'] ?? '';
} else {
    $worker = [
        'fullname' => '',
        'email' => '',
        'phone' => '',
        'address' => '',
        'specialization' => '',
        'service_id' => '',
        'experience' => '',
    ];
}

// Fetch bookings (excluding rejected ones)
$stmt = $conn->prepare("
    SELECT b.*, s.service_name, s.description, s.icon, s.page_link, s.category
    FROM bookings b
    LEFT JOIN services s ON b.service_id = s.service_id
    WHERE b.worker_id = ? AND b.status != 'rejected'
    ORDER BY b.date DESC, b.time DESC
");
$stmt->bind_param("i", $worker_id);
$stmt->execute();
$result = $stmt->get_result();
$bookings = $result->fetch_all(MYSQLI_ASSOC);
$stmt->close();

// Count bookings by status
$stats = ['pending'=>0,'approved'=>0,'completed'=>0];
foreach ($bookings as &$b) {
    $status = strtolower($b['status'] ?? 'pending');
    if ($status === 'approved') $status = 'approved';
    $b['status'] = $status;
    if (isset($stats[$status])) $stats[$status]++;
    $b['fullname'] = $b['fullname'] ?? 'Unknown';
    $b['service_name'] = $b['service_name'] ?? 'Unknown Service';
    $b['date'] = !empty($b['date']) ? date('M d, Y', strtotime($b['date'])) : 'Unknown Date';
    $b['time'] = $b['time'] ?? 'Unknown Time';
    $b['address'] = $b['address'] ?? 'Unknown Location';
    $b['price'] = $b['price'] ?? '₱0';
}

// --- Keep connection open until after services <select> ---
?>


<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8" />
<meta name="viewport" content="width=device-width, initial-scale=1.0" />
<title>Worker Dashboard - FixExpress</title>
<style>
/* --------------------------------------------
   GLOBAL RESETS
-------------------------------------------- */
* {
  margin: 0;
  padding: 0;
  box-sizing: border-box;
}

body {
  font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
  background: linear-gradient(135deg, #D46419 0%, #171B1F 95%);
  min-height: 100vh;
}

/* --------------------------------------------
   HEADER
-------------------------------------------- */
.header {
  background-color: #fff;
  padding: 15px 50px;
  display: flex;
  justify-content: space-between;
  align-items: center;
  box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
}

/* LOGO */
.logo {
  display: flex;
  align-items: center;
  gap: 10px;
  font-size: 24px;
  font-weight: bold;
  color: #333;
  text-decoration: none;
}

.logo-icon {
  width: 40px;
  height: 40px;
  background-color: #c85a28;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  overflow: hidden;
}

/* NAVIGATION */
.nav {
  display: flex;
  gap: 35px;
  align-items: center;
}

.nav a {
  color: #555;
  text-decoration: none;
  font-size: 16px;
  transition: color 0.3s;
}

.nav a:hover,
.nav a.active {
  color: #c85a28;
}

/* --------------------------------------------
   USER INFO / LOGOUT
-------------------------------------------- */
.user-info {
  display: flex;
  align-items: center;
  gap: 15px;
}

.user-avatar {
  width: 40px;
  height: 40px;
  border-radius: 50%;
  border: 2px solid #c85a28;
}

.logout-btn {
  background: linear-gradient(to bottom, #d97843, #a94824);
  color: white;
  padding: 10px 24px;
  border: none;
  border-radius: 5px;
  font-size: 14px;
  cursor: pointer;
  text-decoration: none;
  transition: all 0.3s;
}

.logout-btn:hover {
  background: linear-gradient(to bottom, #c85a28, #923d1e);
}

/* --------------------------------------------
   DASHBOARD CONTAINER
-------------------------------------------- */
.dashboard-container {
  max-width: 1400px;
  margin: 0 auto;
  padding: 40px 20px;
}

/* --------------------------------------------
   WELCOME SECTION
-------------------------------------------- */
.welcome-section {
  background: rgba(255, 255, 255, 0.12);
  border-radius: 20px;
  padding: 40px;
  margin-bottom: 40px;
  backdrop-filter: blur(12px);
  border: 1px solid rgba(255, 255, 255, 0.2);
}

.welcome-section h1 {
  font-size: 2.5rem;
  font-weight: 800;
  background: linear-gradient(to right, #fff, #ffd28a);
  -webkit-background-clip: text;
  -webkit-text-fill-color: transparent;
  margin-bottom: 10px;
}

.welcome-section p {
  color: #fde68a;
  font-size: 1.1rem;
}

/* --------------------------------------------
   STATS GRID
-------------------------------------------- */
.stats-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
  gap: 25px;
  margin-bottom: 40px;
}

.stat-card {
  background: rgba(255, 255, 255, 0.12);
  border-radius: 15px;
  padding: 30px;
  backdrop-filter: blur(12px);
  border: 1px solid rgba(255, 255, 255, 0.2);
  transition: all 0.3s ease;
}

.stat-card:hover {
  transform: translateY(-5px);
  background: rgba(255, 255, 255, 0.18);
}

.stat-card h3 {
  color: #ffcc80;
  font-size: 0.9rem;
  margin-bottom: 10px;
  text-transform: uppercase;
  letter-spacing: 1px;
}

.stat-card .stat-value {
  font-size: 2.5rem;
  font-weight: bold;
  color: #fff;
  margin-bottom: 5px;
}

.stat-card .stat-label {
  color: #fde68a;
  font-size: 0.95rem;
}

/* --------------------------------------------
   BOOKINGS SECTION
-------------------------------------------- */
.bookings-section {
  background: rgba(255, 255, 255, 0.12);
  border-radius: 20px;
  padding: 40px;
  backdrop-filter: blur(12px);
  border: 1px solid rgba(255, 255, 255, 0.2);
}

/* SECTION HEADER */
.section-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 30px;
  flex-wrap: wrap;
  gap: 20px;
}

.section-header h2 {
  font-size: 2rem;
  color: #fff;
}

/* FILTER TABS */
.filter-tabs {
  display: flex;
  gap: 10px;
  flex-wrap: wrap;
}

.filter-tab {
  background: rgba(255, 255, 255, 0.1);
  color: #fff;
  padding: 10px 20px;
  border: 1px solid rgba(255, 255, 255, 0.2);
  border-radius: 25px;
  cursor: pointer;
  transition: all 0.3s ease;
  font-size: 0.9rem;
}

.filter-tab:hover,
.filter-tab.active {
  background: rgba(255, 140, 0, 0.7);
  border-color: #ff8c1a;
}

/* BOOKINGS GRID */
.bookings-grid {
  display: grid;
  gap: 20px;
}

/* BOOKING CARD */
.booking-card {
  background: rgba(255, 255, 255, 0.95);
  border-radius: 15px;
  padding: 25px;
  display: grid;
  grid-template-columns: auto 1fr auto;
  gap: 25px;
  align-items: center;
  transition: all 0.3s ease;
  border-left: 5px solid #d97843;
}

.booking-card:hover {
  transform: translateX(5px);
  box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
}

/* STATUS INDICATOR COLORS */
.booking-status-indicator {
  width: 60px;
  height: 60px;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 1.5rem;
}

.status-pending { background: #fff3cd; }
.status-confirmed { background: #d1ecf1; }
.status-completed { background: #d4edda; }
.status-cancelled { background: #f8d7da; }

/* BOOKING DETAILS */
.booking-details h3 {
  color: #333;
  font-size: 1.3rem;
  margin-bottom: 8px;
}

.booking-info {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
  gap: 10px;
  color: #666;
  font-size: 0.9rem;
}

.booking-info div {
  display: flex;
  align-items: center;
  gap: 8px;
}

/* BOOKING ACTIONS */
.booking-actions {
  display: flex;
  flex-direction: column;
  gap: 10px;
}

.btn-action {
  padding: 10px 20px;
  border: none;
  border-radius: 8px;
  cursor: pointer;
  font-size: 0.9rem;
  transition: all 0.3s ease;
  text-align: center;
  font-weight: 500;
}

/* BUTTON STYLES */
.btn-accept { background: #28a745; color: white; }
.btn-accept:hover { background: #218838; transform: scale(1.05); }

.btn-complete { background: #007bff; color: white; }
.btn-complete:hover { background: #0056b3; transform: scale(1.05); }

.btn-view { background: #17a2b8; color: white; }
.btn-view:hover { background: #138496; transform: scale(1.05); }

.btn-decline { background: #dc3545; color: white; }
.btn-decline:hover { background: #c82333; transform: scale(1.05); }

/* STATUS BADGES */
.status-badge {
  display: inline-block;
  padding: 5px 15px;
  border-radius: 20px;
  font-size: 0.85rem;
  font-weight: 600;
  text-transform: uppercase;
}

.status-pending-badge { background: #fff3cd; color: #856404; }
.status-confirmed-badge { background: #d1ecf1; color: #0c5460; }
.status-completed-badge { background: #d4edda; color: #155724; }
.status-cancelled-badge { background: #f8d7da; color: #721c24; }

.price-tag {
  font-size: 1.3rem;
  font-weight: bold;
  color: #d97843;
  margin-top: 10px;
}

/* EMPTY STATE */
.empty-state {
  text-align: center;
  padding: 60px 20px;
  color: #fde68a;
}

.empty-state h3 {
  font-size: 1.5rem;
  margin-bottom: 10px;
  color: #fff;
}

/* --------------------------------------------
   WORKER PROFILE DROPDOWN
-------------------------------------------- */
.worker-profile {
  position: relative;
}

.worker-icon {
  width: 58px;
  height: 58px;
  border-radius: 50%;
  background: linear-gradient(135deg, #FF7B00, #D46419);
  color: #fff;
  font-weight: 700;
  font-size: 1.5rem;
  display: flex;
  align-items: center;
  justify-content: center;
  cursor: pointer;
  border: 3px solid rgba(255, 255, 255, 0.3);
  box-shadow: 0 4px 16px rgba(255, 123, 0, 0.35);
  transition: all 0.3s ease;
}

.worker-icon:hover {
  transform: scale(1.08);
  box-shadow: 0 6px 20px rgba(255, 123, 0, 0.6);
}

/* DROPDOWN PANEL */
.worker-dropdown {
  display: none;
  position: absolute;
  top: 72px;
  right: 0;
  background: #fff;
  border-radius: 16px;
  box-shadow: 0 8px 32px rgba(0, 0, 0, 0.25);
  overflow: hidden;
  min-width: 300px;
  z-index: 999;
  animation: slideDown 0.3s ease forwards;
}

.worker-dropdown.show {
  display: block;
}

.worker-dropdown p {
  padding: 24px 28px;
  margin: 0;
  background: linear-gradient(135deg, #ea9a2a 0%, #d4760b 50%, #a95807 100%);
  color: #fff;
  font-size: 1.15rem;
  font-weight: 500;
  display: flex;
  align-items: center;
  gap: 8px;
  border-bottom: 2px solid rgba(255, 255, 255, 0.2);
  border-top-left-radius: 16px;
  border-top-right-radius: 16px;
}

.worker-dropdown p strong {
  font-size: 1.4rem;
  font-weight: 700;
  text-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
  letter-spacing: 0.3px;
}

.worker-dropdown .menu-section {
  padding: 16px 20px 24px;
  background: #fff;
}

.worker-dropdown a {
  display: flex;
  align-items: center;
  gap: 14px;
  padding: 16px 20px;
  color: #7A7A7A;
  text-decoration: none;
  transition: all 0.25s ease;
  font-size: 1.05rem;
  border-radius: 10px;
  margin: 8px 0;
  font-weight: 500;
  background: transparent;
  cursor: pointer;
}

.worker-dropdown a:hover {
  background: #F5F5F5;
  color: #333;
  transform: translateX(4px);
}

.worker-dropdown .logout-btn {
  display: block;
  width: calc(100% - 40px);
  margin: 0 auto 24px;
  padding: 16px 0;
  background: linear-gradient(135deg, #d07813 0%, #d47b06 100%);
  color: #fff;
  text-align: center;
  border-radius: 12px;
  cursor: pointer;
  font-weight: 700;
  font-size: 1.05rem;
  transition: all 0.3s ease;
  box-shadow: 0 4px 16px rgba(221, 114, 7, 0.35);
  border: none;
}

.worker-dropdown .logout-btn:hover {
  transform: translateY(-2px);
  box-shadow: 0 6px 24px rgba(216, 105, 8, 0.5);
  background: linear-gradient(135deg, #bc7408 0%, #c97c08 100%);
}

/* --------------------------------------------
   ANIMATIONS
-------------------------------------------- */
@keyframes slideDown {
  from { opacity: 0; transform: translateY(-12px); }
  to { opacity: 1; transform: translateY(0); }
}
/* --- Profile Modal Styles --- */
.profile-modal {
  display: none;
  position: fixed;
  z-index: 999;
  top: 0; left: 0;
  width: 100%; height: 100%;
  background: rgba(0,0,0,0.6);
  justify-content: center;
  align-items: center;
}

.profile-modal.show {
  display: flex;
}

.profile-modal-content {
  background: white;
  padding: 30px 40px;
  border-radius: 20px;
  max-width: 500px;
  width: 90%;
  box-shadow: 0 8px 30px rgba(0,0,0,0.2);
  position: relative;
}

.profile-modal-content h2 {
  text-align: center;
  color: #d46419;
  margin-bottom: 20px;
}

.profile-field {
  margin-bottom: 15px;
}

.profile-field label {
  display: block;
  font-weight: 600;
  margin-bottom: 5px;
}

.profile-field input {
  width: 100%;
  padding: 10px;
  border: 1px solid #ccc;
  border-radius: 8px;
}

.profile-actions {
  display: flex;
  justify-content: center;
  align-items: center;
  gap: 15px;
  margin-top: 25px;
}

.save-btn,
.close-btn-secondary {
  padding: 10px 25px;
  border: none;
  border-radius: 10px;
  color: #fff;
  font-size: 1rem;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.3s ease;
}

/* Save Button */
.save-btn {
  background: linear-gradient(135deg, #28a745, #218838);
}

.save-btn:hover {
  transform: translateY(-2px);
  background: linear-gradient(135deg, #34c759, #2fa54a);
}

/* Cancel Button */
.close-btn-secondary {
  background: linear-gradient(135deg, #d46419, #a64810);
}

.close-btn-secondary:hover {
  transform: translateY(-2px);
  background: linear-gradient(135deg, #e5792b, #b24f12);
}


.save-btn {
  background: linear-gradient(135deg, #28a745, #218838);
  color: #fff;
  border: none;
  border-radius: 10px;
  padding: 10px 20px;
  cursor: pointer;
}

.close-btn-secondary {
  background: linear-gradient(135deg, #d46419, #a64810);
  color: #fff;
  border: none;
  border-radius: 10px;
  padding: 10px 20px;
  cursor: pointer;
}

.close-btn {
  position: absolute;
  top: 15px;
  right: 20px;
  font-size: 24px;
  cursor: pointer;
  color: #888;
}

.char-counter {
  display: block;
  text-align: right;
  font-size: 0.8rem;
  color: #999;
  margin-top: 4px;
  transition: color 0.3s ease;
}

</style>
</head>
<body>
<header class="header">
    <a href="#" class="logo">
        <div class="logo-icon"><img src="" alt="FixExpress Logo"></div>
        <span>FixExpress</span>
    </a>
    <nav class="nav">
        <a href="#" class="active">Dashboard</a>
    </nav>
    <div class="worker-profile">
        <div class="worker-icon" id="workerIcon">
            <?php echo strtoupper(substr($username,0,1)); ?>
        </div>
        <div class="worker-dropdown" id="workerDropdown">
            <p><strong><?php echo htmlspecialchars($username); ?></strong></p>
            <div class="menu-section">
                <a href="#" id="viewProfile">View Profile</a>

            </div>
            <button class="logout-btn" onclick="window.location.href='logout.php'">Logout</button>
        </div>
    </div>
</header>

<div class="dashboard-container">
    <div class="welcome-section">
        <h1>Welcome to your Dashboard, <?php echo htmlspecialchars(ucfirst($username)); ?>!</h1>
        <p>Here's an overview of your bookings and performance</p>
    </div>

    <div class="stats-grid">
        <div class="stat-card">
            <h3>Pending</h3>
            <div class="stat-value"><?php echo $stats['pending']; ?></div>
            <div class="stat-label">Awaiting Response</div>
        </div>
        <div class="stat-card">
            <h3>Approved</h3>
            <div class="stat-value"><?php echo $stats['approved']; ?></div>
            <div class="stat-label">Upcoming Jobs</div>
        </div>
        <div class="stat-card">
            <h3>Completed</h3>
            <div class="stat-value"><?php echo $stats['completed']; ?></div>
            <div class="stat-label">Total Jobs Done</div>
        </div>
        <div class="stat-card">
            <h3>Rating</h3>
            <div class="stat-value">4.9⭐</div>
            <div class="stat-label">Average Rating</div>
        </div>
        
    </div>

    <div class="bookings-section">
        <div class="section-header">
            <h2>My Bookings</h2>
            <div class="filter-tabs">
                <button class="filter-tab active" onclick="filterBookings('all', event)">All</button>
                <button class="filter-tab" onclick="filterBookings('pending', event)">Pending</button>
                <button class="filter-tab" onclick="filterBookings('Approved', event)">Approved</button>
                <button class="filter-tab" onclick="filterBookings('completed', event)">Completed</button>
                <button class="filter-tab" onclick="filterBookings('cancelled', event)">Cancelled</button>
            </div>
        </div>

        <div class="bookings-grid" id="bookingsGrid">
            <?php foreach($bookings as $booking): ?>
            <div class="booking-card" data-status="<?php echo htmlspecialchars($booking['status']); ?>" data-booking-id="<?php echo (int)$booking['booking_id']; ?>">
                <div class="booking-status-indicator status-<?php echo htmlspecialchars($booking['status']); ?>">
                    <?php 
                        $icons = ['pending'=>'⏳','Approved'=>'✓','completed'=>'✔','cancelled'=>'✗'];
                        echo $icons[$booking['status']] ?? '';
                    ?>
                </div>
                <div class="booking-details">
                    <h3>
    <?php echo htmlspecialchars($booking['service_name'] ?? 'Unknown Service'); ?>
</h3>

                    <span class="status-badge status-<?php echo htmlspecialchars($booking['status']); ?>-badge">
                        <?php echo ucfirst($booking['status']); ?>
                    </span>
                    <div class="booking-info">
                        <div><strong>Customer:</strong> <?php echo htmlspecialchars($booking['fullname']); ?></div>
                        <div><strong>Date:</strong> <?php echo $booking['date']; ?></div>
                        <div><strong>Time:</strong> <?php echo htmlspecialchars($booking['time']); ?></div>
                        <div><strong>Location:</strong> <?php echo htmlspecialchars($booking['address']); ?></div>
                    </div>
                    <div class="price-tag"><?php echo htmlspecialchars($booking['price']); ?></div>
                </div>
                <div class="booking-actions">
                    <?php if($booking['status'] === 'pending'): ?>
                        <button class="btn-action btn-accept" onclick="acceptBooking(<?php echo (int)$booking['booking_id']; ?>)">Accept</button>
                        <button class="btn-action btn-decline" onclick="declineBooking(<?php echo (int)$booking['booking_id']; ?>)">Decline</button>
                    <?php elseif($booking['status'] === 'approved'): ?>
                        <button class="btn-action btn-complete" onclick="completeBooking(<?php echo (int)$booking['booking_id']; ?>)">Mark Complete</button>
                        <button class="btn-action btn-view" onclick="viewDetails(<?php echo (int)$booking['booking_id']; ?>)">View Details</button>
                    <?php else: ?>
                        <button class="btn-action btn-view" onclick="viewDetails(<?php echo (int)$booking['booking_id']; ?>)">View Details</button>
                    <?php endif; ?>
                </div>
            </div>
            <?php endforeach; ?>
        </div>

        <div class="empty-state" id="emptyState" style="display:none;">
            <h3>No bookings found</h3>
            <p>You don't have any bookings in this category yet.</p>
        </div>
    </div>
</div>

<!-- Profile Modal -->
<div id="profileModal" class="profile-modal">
  <div class="profile-modal-content">
    <span class="close-btn" id="closeModal">&times;</span>
    <h2>My Profile</h2>

    <form method="POST" action="" id="profileForm">
    <input type="hidden" name="update_profile" value="1">
<!-- First Name -->
<div class="profile-field">
  <label for="first_name">First Name</label>
  <input 
    type="text" 
    id="first_name" 
    name="first_name" 
    value="<?php echo htmlspecialchars($worker['first_name'] ?? ''); ?>" 
    maxlength="50"
    required
    placeholder="Enter first name">
</div>

<!-- Last Name -->
<div class="profile-field">
  <label for="last_name">Last Name</label>
  <input 
    type="text" 
    id="last_name" 
    name="last_name" 
    value="<?php echo htmlspecialchars($worker['last_name'] ?? ''); ?>" 
    maxlength="50"
    required
    placeholder="Enter last name">
</div>


<!-- Username -->
 
<div class="profile-field">
  <label for="username">Username</label>
  <input 
    type="text" 
    id="username" 
    name="username" 
    value="<?php echo htmlspecialchars($worker['username'] ?? ''); ?>" 
    maxlength="50"
    required
    placeholder="Enter username"
    oninput="updateCounter(this, 'usernameCounter', 50)">
</div>

<!-- Email -->
<div class="profile-field">
  <label for="email">Email</label>
  <input 
    type="email" 
    id="email" 
    name="email" 
    value="<?php echo htmlspecialchars($worker['email']); ?>" 
    maxlength="254"
    required
    pattern="^[a-zA-Z0-9._%+-]+@gmail\.com$"
    title="Please enter a valid Gmail address (e.g., example@gmail.com)"
    placeholder="example@gmail.com">
</div>

<!-- Address -->
<div class="profile-field">
  <label for="address">Address</label>
  <input 
    type="text" 
    id="address" 
    name="address" 
    value="<?php echo htmlspecialchars($worker['address']); ?>" 
    maxlength="100"
    required
    placeholder="Enter address">
</div>

       <div class="profile-field">
    <label>Specialization</label>
    <select name="service_id" required>
        <?php
        $services = $conn->query("SELECT * FROM services");

        while($s = $services->fetch_assoc()){
            $selected = ($s['service_id'] == $worker['service_id']) ? 'selected' : '';
            echo "<option value='{$s['service_id']}' $selected>{$s['service_name']}</option>";
        }
        ?>
    </select>
</div>
      <div class="profile-actions">
        <button type="submit" class="save-btn">💾 Save Changes</button>
        <button type="button" class="close-btn-secondary" id="closeModalBtn">Cancel</button>
      </div>
    </form>
  </div>
</div>

<script>
// --- Toggle Worker Dropdown ---
const workerIcon = document.getElementById('workerIcon');
const workerDropdown = document.getElementById('workerDropdown');

workerIcon.addEventListener('click', (event) => {
  event.stopPropagation(); // Prevent click from closing immediately
  workerDropdown.classList.toggle('show');
});

// Close dropdown if clicked outside
document.addEventListener('click', (event) => {
  if (!workerDropdown.contains(event.target) && !workerIcon.contains(event.target)) {
    workerDropdown.classList.remove('show');
  }
});
// --- Profile Modal ---
const profileModal = document.getElementById('profileModal');
const viewProfileBtn = document.getElementById('viewProfile');
const closeModal = document.getElementById('closeModal');
const closeModalBtn = document.getElementById('closeModalBtn');

viewProfileBtn.addEventListener('click', (e) => {
  e.preventDefault();
  workerDropdown.classList.remove('show');
  profileModal.classList.add('show');
});

closeModal.addEventListener('click', () => profileModal.classList.remove('show'));
closeModalBtn.addEventListener('click', () => profileModal.classList.remove('show'));

// Close modal when clicking outside content
profileModal.addEventListener('click', (e) => {
  if (e.target === profileModal) profileModal.classList.remove('show');
});
document.getElementById('profileForm').addEventListener('submit', function(e) {
  const email = document.getElementById('email').value.trim();
  const gmailPattern = /^[a-zA-Z0-9._%+-]+@gmail\.com$/;

  if (!gmailPattern.test(email)) {
    e.preventDefault();
    alert('Please enter a valid Gmail address (e.g., example@gmail.com)');
  }
});

function updateCounter(input, counterId, maxLength) {
  const counter = document.getElementById(counterId);
  const currentLength = input.value.length;
  counter.textContent = `${currentLength} / ${maxLength}`;
  if (currentLength >= maxLength) {
    counter.style.color = "#d32f2f";
    counter.textContent = `${maxLength} / ${maxLength} (Limit reached)`;
  } else {
    counter.style.color = "#999";
  }
}
// no page reload 
document.querySelector('.worker-icon').textContent = document.getElementById('username').value.trim().charAt(0).toUpperCase();

// Booking acceptance function
function acceptBooking(bookingId) {
    if (!confirm('Are you sure you want to accept this booking?')) return;

    console.log('Accepting booking:', bookingId);
    
    fetch('./dist/admin/accept_booking.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
            'Accept': 'application/json'
        },
        body: new URLSearchParams({
            'booking_id': bookingId
        })
    })
    .then(response => {
        console.log('Response status:', response.status);
        return response.text().then(text => {
            try {
                return JSON.parse(text);
            } catch (e) {
                console.error('Error parsing JSON:', text);
                throw new Error('Invalid JSON response from server');
            }
        });
    })
    .then(data => {
        console.log('Response data:', data);
        if (data.success) {
            alert('Booking accepted successfully!');
            // Update the UI
            const bookingCard = document.querySelector(`.booking-card[data-booking-id="${bookingId}"]`);
            if (bookingCard) {
                bookingCard.querySelector('.status-badge').textContent = 'Approved';
                bookingCard.querySelector('.status-badge').className = 'status-badge status-approved-badge';
                bookingCard.setAttribute('data-status', 'approved');
                // Refresh the page to update statistics
                location.reload();
            }
        } else {
            alert('Failed to accept booking: ' + (data.message || 'Unknown error'));
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('An error occurred while processing your request. Check the browser console for details.');
    });
}

// Booking rejection function
function declineBooking(bookingId) {
    if (!confirm('Are you sure you want to decline this booking?')) return;

    fetch('./dist/admin/reject_booking.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
            'Accept': 'application/json'
        },
        body: new URLSearchParams({
            'booking_id': bookingId
        })
    })
    .then(response => {
        console.log('Response status:', response.status);
        return response.text().then(text => {
            try {
                return JSON.parse(text);
            } catch (e) {
                console.error('Error parsing JSON:', text);
                throw new Error('Invalid JSON response from server');
            }
        });
    })
    .then(data => {
        console.log('Response data:', data);
        if (data.success) {
            alert('Booking declined successfully!');
            // Update the UI
            const bookingCard = document.querySelector(`.booking-card[data-booking-id="${bookingId}"]`);
            if (bookingCard) {
                bookingCard.querySelector('.status-badge').textContent = 'Cancelled';
                bookingCard.querySelector('.status-badge').className = 'status-badge status-cancelled-badge';
                bookingCard.setAttribute('data-status', 'cancelled');
                // Refresh the page to update statistics
                location.reload();
            }
        } else {
            alert('Failed to decline booking: ' + (data.message || 'Unknown error'));
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('An error occurred while processing your request. Check the browser console for details.');
    });
}

// Filter bookings function
function filterBookings(status, event) {
    // Update active tab
    const filterTabs = document.querySelectorAll('.filter-tab');
    filterTabs.forEach(tab => tab.classList.remove('active'));
    event.target.classList.add('active');

    // Get all booking cards
    const bookingCards = document.querySelectorAll('.booking-card');
    let visibleCount = 0;

    // Show/hide cards based on status
    bookingCards.forEach(card => {
        const cardStatus = card.getAttribute('data-status').toLowerCase();
        if (status === 'all' || cardStatus === status.toLowerCase()) {
            card.style.display = 'grid';
            visibleCount++;
        } else {
            card.style.display = 'none';
        }
    });

    // Show/hide empty state message
    const emptyState = document.getElementById('emptyState');
    if (visibleCount === 0) {
        emptyState.style.display = 'block';
    } else {
        emptyState.style.display = 'none';
    }
}

// Booking completion function
function completeBooking(bookingId) {
    if (!confirm('Are you sure you want to mark this booking as completed?')) return;

    fetch('./dist/admin/complete_booking.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: 'booking_id=' + bookingId
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert('Booking marked as completed!');
            // Refresh the page to update statistics
            location.reload();
        } else {
            alert('Failed to complete booking: ' + data.message);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('An error occurred while processing your request.');
    });
}
</script>
</body>
</html>
