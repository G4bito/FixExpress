<?php
session_start();
if (!isset($_SESSION['worker_id'])) {
    header("Location: login.php");
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

$worker_id = $_SESSION['worker_id'];

// Get worker's ratings
// Get worker's ratings (using a single, efficient query)
$ratingsQuery = "
    SELECT 
        AVG(rating) as average_rating,
        COUNT(*) as total_ratings
    FROM worker_ratings 
    WHERE worker_id = ?
";
$ratingStmt = $conn->prepare($ratingsQuery);
$ratingStmt->bind_param("i", $worker_id);
$ratingStmt->execute();
$ratingResult = $ratingStmt->get_result();
$ratingData = $ratingResult->fetch_assoc();
$averageRating = number_format($ratingData['average_rating'] ?? 0, 1);
$totalRatings = (int)$ratingData['total_ratings'];
$ratingStmt->close(); // This was being called twice, causing the fatal error.

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $first_name = trim($_POST['first_name']);
    $last_name = trim($_POST['last_name']);
    $username_input = trim($_POST['username']);
    $email = trim($_POST['email']);
    $address = trim($_POST['address']);
    $service_id = $_POST['service_id'];

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
        // Redirect to prevent form resubmission
        header("Location: workers.php?updated=1");
        exit();
    } else {
        echo "<script>alert('Error updating profile.');</script>";
    }

    $stmt->close();
}

if (isset($_GET['updated'])) {
    echo "<script>alert('Profile updated successfully!'); window.history.replaceState(null, null, window.location.pathname);</script>";
}


// Fetch worker data
$stmt_worker = $conn->prepare("SELECT * FROM workers WHERE worker_id = ?");
$stmt_worker->bind_param("i", $worker_id);
$stmt_worker->execute();
$result_worker = $stmt_worker->get_result();
$worker = $result_worker->fetch_assoc();
$stmt_worker->close();

$username = $worker['username'] ?? 'W';

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

// Count bookings by status, including the new 'cancellation requested' status
$stats = ['pending'=>0,'approved'=>0,'completed'=>0, 'cancellation requested' => 0];
foreach ($bookings as &$b) {
    $status = strtolower($b['status'] ?? 'pending');
    if ($status === 'confirmed') $status = 'approved'; // Normalize 'confirmed' to 'approved'
    $b['status'] = $status;
    if (isset($stats[$status])) $stats[$status]++;
    $b['fullname'] = $b['fullname'] ?? 'Unknown';
    $b['service_name'] = $b['service_name'] ?? 'Unknown Service';
    $b['date'] = !empty($b['date']) ? date('M d, Y', strtotime($b['date'])) : 'Unknown Date';
    $b['time'] = !empty($b['time']) ? date('g:i A', strtotime($b['time'])) : 'Unknown Time';
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
<script src="https://cdn.tailwindcss.com"></script>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
<link rel="stylesheet" href="dist/css/modal.css">
<style>
/* --------------------------------------------
   MODAL STYLES
-------------------------------------------- */
.modal-backdrop {
    position: fixed;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background-color: rgba(0, 0, 0, 0.5);
    display: none;
    justify-content: center;
    align-items: center;
    z-index: 1050;
}

.modal-content {
    background: #fff;
    border-radius: 8px;
    padding: 24px;
    width: 90%;
    max-width: 600px;
    max-height: 90vh;
    overflow-y: auto;
    position: relative;
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
    animation: modalSlideIn 0.3s ease;
}

@keyframes modalSlideIn {
    from {
        transform: translateY(-20px);
        opacity: 0;
    }
    to {
        transform: translateY(0);
        opacity: 1;
    }
}

.modal-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 20px;
}

.modal-title {
    font-size: 24px;
    font-weight: bold;
    color: #333;
}

.modal-close {
    background: none;
    border: none;
    font-size: 24px;
    color: #666;
    cursor: pointer;
    padding: 4px;
    transition: color 0.2s ease;
}

.modal-close:hover {
    color: #333;
}

.rating-item {
    background: #f8f9fa;
    border-radius: 8px;
    padding: 16px;
    margin-bottom: 16px;
    transition: transform 0.2s ease;
}

.rating-item:hover {
    transform: translateY(-2px);
}
.modal-backdrop {
  position: fixed;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  background: rgba(0, 0, 0, 0.5);
  display: none;
  justify-content: center;
  align-items: center;
  z-index: 1000;
}

.modal-content {
  background: white;
  padding: 2rem;
  border-radius: 0.5rem;
  width: 95%;
  max-width: 500px;
  max-height: 85vh;
  overflow-y: auto;
  position: relative;
  box-shadow: 0 8px 20px rgba(0, 0, 0, 0.15);
}

.modal-content::-webkit-scrollbar {
  width: 8px;
}

.modal-content::-webkit-scrollbar-track {
  background: #f1f1f1;
  border-radius: 4px;
}

.modal-content::-webkit-scrollbar-thumb {
  background: #f39c12;
  border-radius: 4px;
}

.modal-content::-webkit-scrollbar-thumb:hover {
  background: #e67e22;
}

/* Image modal styles */
.image-modal {
  position: fixed;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  background: rgba(0, 0, 0, 0.9);
  backdrop-filter: blur(8px);
  display: none;
  justify-content: center;
  align-items: center;
  z-index: 2000;
}

.image-modal.active {
  display: flex;
  animation: fadeIn 0.3s ease;
}

.image-modal-content {
  position: relative;
  max-width: 95%;
  max-height: 90vh;
  background: transparent;
  border-radius: 8px;
  overflow: hidden;
  box-shadow: 0 10px 30px rgba(0, 0, 0, 0.3);
}

.image-modal-content img {
  display: block;
  max-width: 100%;
  max-height: 85vh;
  object-fit: contain;
  border-radius: 8px;
}

.image-modal-close {
  position: absolute;
  top: -45px;
  right: 0;
  color: white;
  font-size: 32px;
  cursor: pointer;
  background: none;
  border: none;
  padding: 10px;
  opacity: 0.8;
  transition: opacity 0.3s ease;
}

.image-modal-close:hover {
  opacity: 1;
}

@keyframes fadeIn {
  from { opacity: 0; }
  to { opacity: 1; }
}

.view-image-btn {
  background: #f39c12;
  color: white;
  border: none;
  padding: 10px 20px;
  border-radius: 6px;
  cursor: pointer;
  margin-top: 15px;
  transition: all 0.3s ease;
  font-weight: 600;
  display: block;
  width: 100%;
  text-align: center;
  box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
}

.view-image-btn:hover {
  background: #e67e22;
  transform: translateY(-1px);
  box-shadow: 0 4px 8px rgba(0, 0, 0, 0.15);
}

.modal-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 1.5rem;
}

.modal-close {
  background: none;
  border: none;
  font-size: 1.5rem;
  cursor: pointer;
  padding: 0.5rem;
}

.rating-item {
  background: #f8f9fa;
  padding: 1rem;
  border-radius: 0.5rem;
  margin-bottom: 1rem;
}

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
   STAT CARDS
-------------------------------------------- */
.stat-card {
  background: rgba(255, 255, 255, 0.9);
  padding: 1.5rem;
  border-radius: 1rem;
  box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
  transition: transform 0.2s ease, box-shadow 0.2s ease;
}

.stat-card.cursor-pointer:hover {
  transform: translateY(-5px);
  box-shadow: 0 8px 15px rgba(0, 0, 0, 0.2);
  cursor: pointer;
}

.stat-card h3 {
  font-size: 1.1rem;
  color: #4B5563;
  margin-bottom: 0.5rem;
}

.stat-card .stat-value {
  font-size: 2rem;
  font-weight: bold;
  color: #1F2937;
  margin-bottom: 0.25rem;
}

.stat-card .stat-label {
  font-size: 0.875rem;
  color: #6B7280;
}

/* --------------------------------------------
   MODALS
-------------------------------------------- */
#ratingsModal {
  transition: opacity 0.3s ease, visibility 0.3s ease;
  opacity: 0;
  visibility: hidden;
}

#ratingsModal.hidden {
  opacity: 0;
  visibility: hidden;
  pointer-events: none;
}

#ratingsModal.flex {
  opacity: 1;
  visibility: visible;
  pointer-events: auto;
}

#ratingsModal .bg-white {
  transform: scale(0.95);
  transition: transform 0.3s ease;
}

#ratingsModal.flex .bg-white {
  transform: scale(1);
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
  background-clip: text;
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
  cursor: pointer;
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
.status-cancellation-requested { background: #ffe8e6; }
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
.status-cancellation-requested-badge { background: #f8d7da; color: #721c24; }
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
html {
  scroll-behavior: smooth;
}

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
        <div class="stat-card" onclick="filterBookings('pending', event)">
            <h3>Pending</h3>
            <div class="stat-value"><?php echo $stats['pending']; ?></div>
            <div class="stat-label">Awaiting Response</div>
        </div>
        <div class="stat-card" onclick="filterBookings('approved', event)">
            <h3>Approved</h3>
            <div class="stat-value"><?php echo $stats['approved']; ?></div>
            <div class="stat-label">Upcoming Jobs</div>
        </div>
        <div class="stat-card" onclick="filterBookings('completed', event)">
            <h3>Completed</h3>
            <div class="stat-value"><?php echo $stats['completed']; ?></div>
            <div class="stat-label">Total Jobs Done</div>
        </div>
        <div class="stat-card" onclick="filterBookings('cancellation requested', event)">
            <h3>Cancellation Requests</h3>
            <div class="stat-value"><?php echo $stats['cancellation requested']; ?></div>
            <div class="stat-label">Action Required</div>
        </div>
        <div class="stat-card cursor-pointer" onclick="showRatings()">
            <h3>Rating</h3>
            <div class="stat-value"><?= $averageRating ?>⭐</div>
            <div class="stat-label"><?= $totalRatings ?> Rating<?= $totalRatings !== 1 ? 's' : '' ?></div>
        </div>
    </div>

    <!-- Ratings Modal -->
    <div id="ratingsModal" class="modal-backdrop">
        <div class="modal-content">
            <div class="flex justify-between items-center mb-6">
                <h2 class="text-2xl font-bold">Customer Ratings</h2>
                <button onclick="hideRatings()" class="text-gray-500 hover:text-gray-700">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <div class="space-y-4">
                <?php
                $detailedRatingsQuery = "
                    SELECT 
                        wr.*,
                        u.first_name,
                        u.last_name,
                        b.date as booking_date,
                        s.service_name
                    FROM worker_ratings wr
                    JOIN users u ON wr.user_id = u.user_id
                    JOIN bookings b ON wr.booking_id = b.booking_id
                    JOIN services s ON b.service_id = s.service_id
                    WHERE wr.worker_id = ?
                    ORDER BY wr.date_submitted DESC
                ";
                $detailedStmt = $conn->prepare($detailedRatingsQuery);
                $detailedStmt->bind_param("i", $worker_id);
                $detailedStmt->execute();
                $detailedResult = $detailedStmt->get_result();
                
                if ($detailedResult->num_rows > 0) {
                    while ($rating = $detailedResult->fetch_assoc()) {
                        ?>
                        <div class="bg-gray-50 p-4 rounded-lg">
                            <div class="flex justify-between items-start">
                                <div>
                                    <div class="font-medium">
                                        <?= htmlspecialchars($rating['first_name'] . ' ' . $rating['last_name']) ?>
                                    </div>
                                    <div class="text-sm text-gray-500">
                                        <?= htmlspecialchars($rating['service_name']) ?> • 
                                        <?= date('M d, Y', strtotime($rating['booking_date'])) ?>
                                    </div>
                                </div>
                                <div class="flex text-yellow-400">
                                    <?php for ($i = 1; $i <= 5; $i++): ?>
                                        <i class="fas fa-star <?= $i <= $rating['rating'] ? 'text-yellow-400' : 'text-gray-300' ?>"></i>
                                    <?php endfor; ?>
                                </div>
                            </div>
                            <?php if (!empty($rating['comment'])): ?>
                                <div class="mt-2 text-gray-600">
                                    "<?= htmlspecialchars($rating['comment']) ?>"
                                </div>
                            <?php endif; ?>
                        </div>
                        <?php
                    }
                } else {
                    echo '<div class="text-center text-gray-500">No ratings yet</div>';
                }
                $detailedStmt->close();
                ?>
            </div>
        </div>
        
    </div>
    
    <div class="bookings-section" id="bookings-section">
        <div class="section-header">
            <h2>My Bookings</h2>
            <div class="filter-tabs">
                <button class="filter-tab active" data-filter="all" onclick="filterBookings('all', event)">All</button>
                <button class="filter-tab" data-filter="pending" onclick="filterBookings('pending', event)">Pending</button>
                <button class="filter-tab" data-filter="approved" onclick="filterBookings('approved', event)">Approved</button>
                <button class="filter-tab" data-filter="completed" onclick="filterBookings('completed', event)">Completed</button>
                <button class="filter-tab" data-filter="cancellation requested" onclick="filterBookings('cancellation requested', event)">Requests</button>
                <button class="filter-tab" data-filter="cancelled" onclick="filterBookings('cancelled', event)">Cancelled</button>
            </div>
        </div>

        <div class="bookings-grid" id="bookingsGrid">
            <?php foreach($bookings as $booking): ?>
            <div class="booking-card" data-status="<?php echo htmlspecialchars($booking['status']); ?>" data-booking-id="<?php echo (int)$booking['booking_id']; ?>">
                <div class="booking-status-indicator status-<?php echo htmlspecialchars($booking['status']); ?>">
                    <?php 
                        $icons = ['pending'=>'⏳','approved'=>'✓','completed'=>'✔','cancelled'=>'✗', 'cancellation requested' => '❓'];
                        echo $icons[$booking['status']] ?? '';
                    ?>
                </div>
                <div class="booking-details">
                    <h3>
    <?php echo htmlspecialchars($booking['service_name'] ?? 'Unknown Service'); ?>
</h3>

                    <span class="status-badge status-<?php echo htmlspecialchars($booking['status']); ?>-badge">
                        <?php echo ucfirst(htmlspecialchars($booking['status'])); ?>
                    </span>
                    <div class="booking-info">
                        <div><strong>Customer:</strong> <?php echo htmlspecialchars($booking['fullname']); ?></div>
                        <div><strong>Date:</strong> <?php echo $booking['date']; ?></div>
                        <div><strong>Time:</strong> <?php echo htmlspecialchars($booking['time']); ?></div>
                        <div><strong>Location:</strong> <?php echo htmlspecialchars($booking['address']); ?></div>
                    </div>
                    <?php if (isset($booking['price']) && (float)$booking['price'] > 0): ?>
                        <div class="price-tag" style="margin-top: 10px; font-size: 1.2rem;">
                            Price: ₱<?= htmlspecialchars(number_format($booking['price'], 2)) ?>
                        </div>
                    <?php endif; ?>
                </div>
                <div class="booking-actions">
                    <?php if($booking['status'] === 'pending'): ?>
                        <button class="btn-action btn-accept" onclick="showApproveModal(<?php echo (int)$booking['booking_id']; ?>)">Accept</button>
                        <button class="btn-action btn-decline" onclick="showDeclineModal(<?php echo (int)$booking['booking_id']; ?>)">Decline</button>
                    <?php elseif($booking['status'] === 'approved'): ?>
                        <button class="btn-action btn-complete" onclick="completeBooking(<?php echo (int)$booking['booking_id']; ?>)">Mark Complete</button>
                        <button class="btn-action btn-view" onclick="viewDetails(<?php echo (int)$booking['booking_id']; ?>)">View Details</button>
                    <?php elseif($booking['status'] === 'cancellation requested'): ?>
                        <button class="btn-action btn-accept" onclick="approveCancellation(<?php echo (int)$booking['booking_id']; ?>)">Approve Cancel</button>
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

<!-- Decline Booking Modal -->
<div id="declineModal" class="profile-modal">
  <div class="profile-modal-content">
    <span class="close-btn" id="closeDeclineModal">&times;</span>
    <h2>Decline Booking</h2>
    <form id="declineForm">
      <input type="hidden" id="declineBookingId" name="booking_id">
      <div class="profile-field">
        <label for="declineReason">Reason for Declining:</label>
        <textarea 
          id="declineReason" 
          name="reason" 
          required 
          class="form-control" 
          style="width: 100%; height: 120px; padding: 10px; border-radius: 8px; border: 1px solid #ccc;"
          placeholder="Please explain why you are declining this booking..."></textarea>
      </div>
      <div class="profile-actions">
        <button type="submit" class="save-btn">Submit</button>
        <button type="button" class="close-btn-secondary" id="cancelDeclineBtn">Cancel</button>
      </div>
    </form>
  </div>
</div>

<!-- Approve Booking Modal -->
<div id="approveModal" class="profile-modal">
  <div class="profile-modal-content" style="max-width: 650px;">
    <span class="close-btn" id="closeApproveModal">&times;</span>
    <h2>Approve Booking & Set Price</h2>
    <div id="approveBookingDetailsContent" style="margin-top: 20px; max-height: 40vh; overflow-y: auto; padding-right: 15px;">
      <!-- Booking details will be populated here -->
    </div>
    <form id="approveForm" style="margin-top: 20px;">
      <input type="hidden" id="approveBookingId" name="booking_id">
      <div class="profile-field">
        <label for="bookingPrice">Estimated Price (₱):</label>
        <input type="number" id="bookingPrice" name="price" required class="form-control" style="width: 100%; padding: 10px; border-radius: 8px; border: 1px solid #ccc;" placeholder="e.g., 1500.00" step="0.01" min="0">
      </div>
      <div class="profile-actions" style="margin-top: 30px;">
        <button type="submit" class="save-btn">Confirm & Approve</button>
        <button type="button" class="close-btn-secondary" id="cancelApproveBtn">Cancel</button>
      </div>
    </form>
  </div>
</div>


<!-- View Details Modal -->
<div id="viewDetailsModal" class="profile-modal">
  <div class="modal-content">
    <span class="close-btn" id="closeViewDetailsModal">&times;</span>
    <h2 style="font-size: 24px; margin-bottom: 20px; color: #333; padding-right: 30px;">Booking Details</h2>
    <div id="bookingDetailsContent">
      <!-- Content will be populated dynamically -->
    </div>
    <div style="text-align: center;">
      <button type="button" class="close-btn-secondary" id="closeViewDetailsBtn">Close</button>
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
function showRatings() {
    const modal = document.getElementById('ratingsModal');
    if (modal) {
        modal.style.display = 'flex';
        document.body.style.overflow = 'hidden';
        // Add animation class
        modal.querySelector('.modal-content').classList.add('animate-in');
    }
}

function hideRatings() {
    const modal = document.getElementById('ratingsModal');
    if (modal) {
        modal.style.display = 'none';
        document.body.style.overflow = 'auto';
    }
}

// Close modal when clicking outside
document.addEventListener('DOMContentLoaded', function() {
    const modal = document.getElementById('ratingsModal');
    if (modal) {
        modal.addEventListener('click', function(e) {
            if (e.target === modal) {
                hideRatings();
            }
        });
    }
});

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

// Booking rejection function
function showDeclineModal(bookingId) {
    const declineModal = document.getElementById('declineModal');
    const closeDeclineModal = document.getElementById('closeDeclineModal');
    const cancelDeclineBtn = document.getElementById('cancelDeclineBtn');
    const declineForm = document.getElementById('declineForm');
    const declineBookingIdInput = document.getElementById('declineBookingId');

    // Set the booking ID in the form
    declineBookingIdInput.value = bookingId;

    // Show the decline modal
    declineModal.classList.add('show');

    // Close modal handlers
    const closeModal = () => {
        declineModal.classList.remove('show');
        declineForm.reset();
    };

    closeDeclineModal.onclick = closeModal;
    cancelDeclineBtn.onclick = closeModal;
    declineModal.onclick = (e) => {
        if (e.target === declineModal) closeModal();
    };

    // Handle form submission
    declineForm.onsubmit = (e) => {
        e.preventDefault();
        const reason = document.getElementById('declineReason').value.trim();
        
        if (!reason) {
            alert('Please provide a reason for declining the booking.');
            return;
        }

        fetch('./dist/admin/reject_booking.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
                'Accept': 'application/json'
            },
            body: new URLSearchParams({
                'booking_id': bookingId,
                'reason': reason
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert('Booking declined successfully!');
                const bookingCard = document.querySelector(`.booking-card[data-booking-id="${bookingId}"]`);
                if (bookingCard) {
                    bookingCard.querySelector('.status-badge').textContent = 'Cancelled';
                    bookingCard.querySelector('.status-badge').textContent = 'Cancelled'; // Corrected typo
                    bookingCard.querySelector('.status-badge').className = 'status-badge status-cancelled-badge';
                    bookingCard.setAttribute('data-status', 'cancelled');
                }
                closeModal();
                location.reload();
            } else {
                alert('Failed to decline booking: ' + (data.message || 'Unknown error'));
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('An error occurred while processing your request.');
        });
    };
}

// Approve booking with price modal
function showApproveModal(bookingId) {
    const approveModal = document.getElementById('approveModal');
    const detailsContent = document.getElementById('approveBookingDetailsContent');
    const bookingIdInput = document.getElementById('approveBookingId');
    const approveForm = document.getElementById('approveForm');

    // Show loading state and modal
    detailsContent.innerHTML = '<p style="text-align: center; padding: 20px;">Loading booking details...</p>';
    approveModal.classList.add('show');
    bookingIdInput.value = bookingId;

    // Fetch details (reusing viewDetails logic)
    fetch('./dist/admin/get_booking_details.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: 'booking_id=' + bookingId
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // This is a simplified view for the approval modal
            const booking = data.booking;
            let problemMediaHtml = '';
            if (booking.problem_image_path) {
                const mediaPath = `./${booking.problem_image_path}`;
                const isVideo = ['mp4', 'webm', 'mov'].includes(mediaPath.split('.').pop().toLowerCase());
                problemMediaHtml = isVideo 
                    ? `<video src="${mediaPath}" controls style="max-width: 100%; border-radius: 8px; margin-top: 10px;"></video>`
                    : `<img src="${mediaPath}" alt="Problem Image" style="max-width: 100%; border-radius: 8px; margin-top: 10px;">`;
            }
            detailsContent.innerHTML = `
                <p><strong>Customer:</strong> ${booking.fullname}</p>
                <p><strong>Address:</strong> ${booking.address}</p>
                <p><strong>Problem:</strong></p>
                <p style="white-space: pre-wrap; background: #f1f1f1; padding: 10px; border-radius: 5px;">${booking.notes || 'N/A'}</p>
                ${problemMediaHtml}
            `;
        } else {
            detailsContent.innerHTML = '<p style="color: red;">Could not load booking details.</p>';
        }
    }).catch(() => {
        detailsContent.innerHTML = '<p style="color: red;">Error fetching details.</p>';
    });

    // Form submission handler
    approveForm.onsubmit = (e) => {
        e.preventDefault();
        const price = document.getElementById('bookingPrice').value;
        if (!price || price < 0) {
            alert('Please enter a valid estimated price.');
            return;
        }

        fetch('./dist/admin/accept_booking.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: new URLSearchParams({ 'booking_id': bookingId, 'price': price })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert('Booking approved successfully with the estimated price!');
                location.reload();
            } else {
                alert('Failed to approve booking: ' + (data.message || 'Unknown error'));
            }
        }).catch(error => console.error('Error:', error));
    };

    // Close modal handlers
    const closeModal = () => approveModal.classList.remove('show');
    document.getElementById('closeApproveModal').onclick = closeModal;
    document.getElementById('cancelApproveBtn').onclick = closeModal;
    approveModal.onclick = (e) => { if (e.target === approveModal) closeModal(); };
}

// Approve cancellation request
function approveCancellation(bookingId) {
    if (!confirm('Are you sure you want to approve this cancellation request? The booking will be cancelled.')) return;

    fetch('./dist/admin/approve_cancellation.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: new URLSearchParams({ 'booking_id': bookingId })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert('Cancellation approved.');
            location.reload();
        } else {
            alert('Failed to approve cancellation: ' + (data.message || 'Unknown error'));
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('An error occurred while processing the request.');
    });
}

function denyCancellation(bookingId) {
    // Future implementation: A modal could pop up to ask why, or just revert status.
    alert('Deny cancellation functionality can be added here.');
}

// Filter bookings function
function filterBookings(status, event) {
    // Update active tab
    // If the click came from a stat card or a filter tab, scroll to the bookings section
    if (event && (event.currentTarget.classList.contains('stat-card') || event.currentTarget.classList.contains('filter-tab'))) {
        smoothScrollTo('bookings-section');
    }
    
    const filterTabs = document.querySelectorAll('.filter-tab');
    filterTabs.forEach(tab => tab.classList.remove('active'));

    // Find and activate the correct tab based on the status
    const activeTab = document.querySelector(`.filter-tab[data-filter="${status}"]`);
    if (activeTab) {
        activeTab.classList.add('active');
    }

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
async function completeBooking(bookingId) {
    if (!confirm('Are you sure you want to mark this booking as completed?')) return;

    try {
        const response = await fetch('./dist/admin/complete_booking.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: new URLSearchParams({
                booking_id: bookingId
            }).toString()
        });

        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }

        const data = await response.json();
        
        if (!data.success) {
            throw new Error(data.message || 'Failed to complete booking');
        }

        alert('Booking marked as completed!');
        // Refresh the page to update statistics
        location.reload();
    } catch (error) {
        console.error('Error:', error);
        alert(error.message || 'An error occurred while processing your request.');
    }
}

async function viewDetails(bookingId) {
    const viewDetailsModal = document.getElementById('viewDetailsModal');
    const closeViewDetailsModal = document.getElementById('closeViewDetailsModal');
    const closeViewDetailsBtn = document.getElementById('closeViewDetailsBtn');
    const bookingDetailsContent = document.getElementById('bookingDetailsContent');

    // Show loading state
    bookingDetailsContent.innerHTML = '<p style="text-align: center; padding: 20px;">Loading booking details...</p>';
    viewDetailsModal.style.display = 'block';

    // Close modal handlers
    const closeModal = () => {
        viewDetailsModal.style.display = 'none';
    };
    
    closeViewDetailsModal.onclick = closeModal;
    closeViewDetailsBtn.onclick = closeModal;
    viewDetailsModal.onclick = (e) => {
        if (e.target === viewDetailsModal) closeModal();
    };

    // Initialize image modal if not already present
    let imageModal = document.getElementById('imageModal');
    if (!imageModal) {
        imageModal = document.createElement('div');
        imageModal.id = 'imageModal';
        imageModal.className = 'image-modal';
        imageModal.innerHTML = `
            <div class="image-modal-content" style="background: white; padding: 20px; border-radius: 8px; position: relative; max-width: 90%; max-height: 90vh; overflow: hidden;">
                <button class="image-modal-close" onclick="closeImageModal()" style="position: absolute; right: 10px; top: 10px; background: none; border: none; font-size: 24px; cursor: pointer;">&times;</button>
                <img id="modalImage" src="" alt="Uploaded image" style="max-width: 100%; max-height: calc(90vh - 40px); display: block; margin: 0 auto;">
            </div>
        `;
        // Add modal styles
        const style = document.createElement('style');
        style.textContent = `
            .image-modal {
                display: none;
                position: fixed;
                top: 0;
                left: 0;
                width: 100%;
                height: 100%;
                background: rgba(0, 0, 0, 0.8);
                z-index: 9999;
                justify-content: center;
                align-items: center;
            }
            .image-modal.active {
                display: flex;
            }
            .image-modal-content {
                animation: zoomIn 0.3s ease;
            }
            @keyframes zoomIn {
                from {
                    transform: scale(0.5);
                    opacity: 0;
                }
                to {
                    transform: scale(1);
                    opacity: 1;
                }
            }
        `;
        document.head.appendChild(style);
        document.body.appendChild(imageModal);
        
        // Define image modal functions only once
        if (typeof window.openImageModal === 'undefined') {
            window.openImageModal = function(fileSrc, fileType) {
                console.log('Opening media modal with original src:', fileSrc);
                
                // Ensure we have a valid path starting with ./dist/uploads/
                if (!fileSrc.startsWith('./dist/uploads/')) {
                    // If it starts with ../uploads/, replace it
                    if (fileSrc.startsWith('../uploads/')) {
                        fileSrc = fileSrc.replace('../uploads/', './dist/uploads/');
                    } 
                    // If it's just a filename, add the path
                    else if (!fileSrc.includes('/')) {
                        fileSrc = './dist/uploads/' + fileSrc;
                    }
                }
                
                console.log('Processed file path:', fileSrc);
                
                const modal = document.getElementById('imageModal');
                const modalImg = document.getElementById('modalImage');
                const modalVideo = document.getElementById('modalVideo');
                const loadingSpinner = document.getElementById('mediaLoadingSpinner');
                
                // Reset both elements
                modalImg.style.display = 'none';
                modalVideo.style.display = 'none';
                modalImg.style.opacity = '0';
                modalVideo.style.opacity = '0';
                
                // Show loading state
                loadingSpinner.style.display = 'block';
                modal.classList.add('active');

                if (fileType === 'video' || fileSrc.toLowerCase().endsWith('.mp4')) {
                    modalVideo.style.display = 'block';
                    modalVideo.src = fileSrc;
                    modalVideo.style.opacity = '1';
                    loadingSpinner.style.display = 'none';
                } else {
                    modalImg.style.display = 'block';
                    modalImg.src = fileSrc;
                
                // Load the image
                modalImg.onload = function() {
                    console.log('Image loaded successfully');
                    modalImg.style.opacity = '1';
                };
                
                modalImg.onerror = function() {
                    console.error('Failed to load image:', imgSrc);
                    // Log more details about the failure
                    console.log('Image URL attempted:', imgSrc);
                    console.log('Full page URL:', window.location.href);
                    alert('Failed to load image. Ayos mo pare');
                    closeImageModal();
                };
                
                // Add timestamp to prevent caching
                const timestamp = new Date().getTime();
                const imageUrl = imgSrc + '?t=' + timestamp;
                console.log('Loading image with URL:', imageUrl);
                modalImg.src = imageUrl;
            };

            window.closeImageModal = function() {
                const modal = document.getElementById('imageModal');
                const modalVideo = document.getElementById('modalVideo');
                if (modalVideo) {
                    modalVideo.pause();
                    modalVideo.src = '';
                }
                modal.classList.remove('active');
            };
        }

            // Add event listeners only once
            document.addEventListener('keydown', function(event) {
                if (event.key === 'Escape') {
                    closeImageModal();
                }
            });

            imageModal.addEventListener('click', function(event) {
                if (event.target === this) {
                    closeImageModal();
                }
            });
        }
    }

    // Fetch booking details
    try {
        const response = await fetch('./dist/admin/get_booking_details.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: new URLSearchParams({
                booking_id: bookingId
            }).toString()
        });

        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }

        const data = await response.json();
        console.log('Booking data received:', data);

        if (!data || !data.success || !data.booking) {
            throw new Error(data.message || 'Failed to load booking details');
        }

        const booking = data.booking;
            const statusClass = {
                'pending': 'warning',
                'approved': 'info',
                'completed': 'success',
                'cancellation requested': 'danger',
                'cancelled': 'danger'
            }[booking.status.toLowerCase()] || 'secondary';

            let declineReasonHtml = '';
            if (booking.status.toLowerCase() === 'cancelled' && booking.decline_reason) {
                declineReasonHtml = `
                    <div class="detail-group" style="margin-top: 20px; padding: 15px; background: #f8d7da; border-radius: 8px;">
                        <h4 style="color: #721c24; margin-bottom: 10px;">Reason for Declining:</h4>
                        <p style="color: #721c24;">${booking.decline_reason}</p>
                    </div>
                `;
            }

            if (booking.status.toLowerCase() === 'cancellation requested' && booking.cancellation_reason) {
                declineReasonHtml = `
                    <div class="detail-group" style="margin-top: 20px; padding: 15px; background: #fff3cd; border-radius: 8px;">
                        <h4 style="color: #856404; margin-bottom: 10px;">Customer's Reason for Cancellation:</h4>
                        <p style="color: #856404;">${booking.cancellation_reason}</p>
                    </div>
                `;
            }
            
            let problemMediaHtml = '';
            if (booking.problem_image_path) {
                const mediaPath = `./${booking.problem_image_path}`;
                const isVideo = ['mp4', 'webm', 'mov'].includes(mediaPath.split('.').pop().toLowerCase());
                if (isVideo) {
                    problemMediaHtml = `<video src="${mediaPath}" controls style="max-width: 100%; border-radius: 8px; margin-top: 10px;"></video>`;
                } else {
                    problemMediaHtml = `<img src="${mediaPath}" alt="Problem Image" style="max-width: 100%; border-radius: 8px; margin-top: 10px;">`;
                }
            }

            bookingDetailsContent.innerHTML = `
                <div style="display: grid; gap: 15px;">
                    <div class="detail-group" style="padding: 15px; background: #f8f9fa; border-radius: 8px;">
                        <h4 style="color: #333; margin-bottom: 15px; border-bottom: 2px solid #f39c12; padding-bottom: 10px;">
                            Booking Information
                        </h4>
                        <div style="display: grid; gap: 12px;">
                            <div style="display: flex; justify-content: space-between; align-items: center;">
                                <p style="margin: 0;"><strong>Service:</strong> ${booking.service_name}</p>
                                <span class="badge bg-${statusClass}" style="padding: 6px 12px;">${booking.status}</span>
                            </div>
                            <p style="margin: 0;"><strong>Date:</strong> ${booking.date}</p>
                            <p style="margin: 0;"><strong>Time:</strong> ${booking.time}</p>
                            <p style="margin: 0;"><strong>Price:</strong> ${booking.price || 'Not set'}</p>
                            <div style="background: white; padding: 10px; border-radius: 6px; margin-top: 5px;">
                                <p style="margin: 0;"><strong>Problem Description:</strong></p>
                                <p style="margin: 5px 0 0 0; white-space: pre-wrap;">${booking.notes}</p>
                            </div>
                            ${booking.file_info ? `
                                <div class="file-info">
                                    ${booking.file_info.is_image || booking.file_info.is_video ? `
                                        <button class="view-image-btn" onclick="openImageModal('${booking.file_info.path}', '${booking.file_info.file_type}')" 
                                            style="padding: 10px 20px; background: #ff8c1a; color: white; border: none; border-radius: 5px; cursor: pointer; display: flex; align-items: center; gap: 8px;">
                                            <i class="fas ${booking.file_info.is_video ? 'fa-video' : 'fa-image'}"></i>
                                            View Uploaded ${booking.file_info.is_video ? 'Video' : 'Image'}
                                        </button>
                                    ` : `
                                        <p style="margin: 0;"><strong>Uploaded File:</strong> ${booking.file_info.extension.toUpperCase()} file</p>
                                    `}
                                </div>
                            ` : '<p style="color: #666;">No file uploaded</p>'}
                        </div>
                    </div>

                    <hr style="border: none; border-top: 1px solid #eee; margin: 0;">

                    <div class="detail-group" style="padding: 15px; background: #f8f9fa; border-radius: 8px;">
                        <h4 style="color: #333; margin-bottom: 15px; border-bottom: 2px solid #f39c12; padding-bottom: 10px;">
                            Customer Information
                        </h4>
                        <div style="display: grid; gap: 12px;">
                            <p style="margin: 0;"><strong>Name:</strong> ${booking.fullname}</p>
                            <p style="margin: 0;"><strong>Contact:</strong> ${booking.contact}</p>
                            <div style="background: white; padding: 10px; border-radius: 6px;">
                                <p style="margin: 0;"><strong>Address:</strong><br>${booking.address}</p>
                            </div>
                            <p style="margin: 0;"><strong>Email:</strong> ${booking.email}</p>
                        </div>
                    </div>

                    <hr style="border: none; border-top: 1px solid #eee; margin: 0;">

                    <div class="detail-group" style="padding-bottom: 20px;">
                        <h4 style="color: #333; margin-bottom: 15px; border-bottom: 2px solid #f39c12; padding-bottom: 10px;">
                            Customer's Problem
                        </h4>
                        <div style="display: grid; gap: 10px;">
                            <p style="white-space: pre-wrap;">${booking.notes || 'No problem description provided'}</p>
                        </div>
                    </div>

                    ${declineReasonHtml}
                </div>
            `;
    } catch (error) {
        console.error('Error:', error);
        bookingDetailsContent.innerHTML = `
            <p style="color: #721c24; text-align: center; padding: 20px;">
                ${error.message || 'An error occurred while loading the booking details.'}
            </p>
        `;
    }
}

function smoothScrollTo(elementId) {
    const targetElement = document.getElementById(elementId);
    if (!targetElement) return;

    const targetPosition = targetElement.getBoundingClientRect().top + window.pageYOffset;
    const startPosition = window.pageYOffset;
    const distance = targetPosition - startPosition;
    const duration = 800; // milliseconds
    let startTime = null;

    function animation(currentTime) {
        if (startTime === null) startTime = currentTime;
        const timeElapsed = currentTime - startTime;
        const run = Math.min(timeElapsed / duration, 1);
        const ease = run < 0.5 ? 2 * run * run : (4 - 2 * run) * run - 1; // ease in and out
        window.scrollTo(startPosition, startPosition + distance * ease);
        if (timeElapsed < duration) requestAnimationFrame(animation);
    }

    requestAnimationFrame(animation);
}
// Ratings modal functions
function showRatings() {
    const modal = document.getElementById('ratingsModal');
    if (modal) {
        modal.classList.remove('hidden');
        modal.classList.add('flex');
        // Prevent body scrolling when modal is open
        document.body.style.overflow = 'hidden';
    }
}

function hideRatings() {
    const modal = document.getElementById('ratingsModal');
    if (modal) {
        modal.classList.remove('flex');
        modal.classList.add('hidden');
        // Restore body scrolling
        document.body.style.overflow = '';
    }
}

// Event listeners for ratings modal
document.addEventListener('DOMContentLoaded', function() {
    const modal = document.getElementById('ratingsModal');
    if (modal) {
        // Close modal when clicking outside
        modal.addEventListener('click', function(e) {
            if (e.target === modal) {
                hideRatings();
            }
        });

        // Close modal with Escape key
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                hideRatings();
            }
        });
    }
});
</script>
</body>
</html>