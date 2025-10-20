<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

include '../database/server.php';

if (!isset($_GET['id'])) {
  die("No service selected.");
}

$id = intval($_GET['id']);
$sql = "SELECT * FROM services WHERE service_id = $id";
$result = $conn->query($sql);

if ($result->num_rows == 0) {
  die("Service not found.");
}

$row = $result->fetch_assoc();
?>

<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8" />
  <title><?php echo $row['service_name']; ?> | FixExpress</title>
  <link href="https://fonts.googleapis.com/css2?family=Open+Sans:wght@400;600&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="../assets/fonts/fontawesome.css" />
  <style>
    /* Variables */
    :root {
      /* Brand Colors */
      --primary-color: #f39c12;      /* for buttons and highlights */
      --primary-hover: #e67e22;      /* hover states on buttons */
      
      /* Text Colors */
      --text-dark: #2c2c2c;          /* Main text color for headings and important content */
      --text-medium: #555;           /* Secondary text color for paragraphs */
      --text-light: #666;            /* Lighter text for less important content */
      
      /* Background and Border Colors */
      --bg-light: #fffdfa;           /* Light background for cards and containers */
      --border-light: #ddd;          /* Standard border color for inputs and dividers */
      --border-warm: #f0e0c0;        /* Warmer border color for worker cards */
      
      /* Shadow Variables */
      --shadow-light: rgba(0, 0, 0, 0.1);    /* Subtle shadow for cards and containers */
      --shadow-medium: rgba(0, 0, 0, 0.25);   /* Stronger shadow for modals and popups */
      
      /* Status Colors */
      --success-color: #27ae60;      /* Green color for success messages and confirmations */
      
      /* Border Radius Scale */
      --border-radius-sm: 6px;       /* Small radius for buttons */
      --border-radius-md: 8px;       /* Medium radius for inputs */
      --border-radius-lg: 12px;      /* Large radius for cards */
      --border-radius-xl: 12px;      /* Extra large radius for modals */
      --border-radius-xxl: 18px;     /* Double extra large radius for main container */
      
      /* Spacing Scale */
      --spacing-xs: 8px;             /* Extra small spacing for tight gaps */
      --spacing-sm: 12px;            /* Small spacing for buttons and inputs */
      --spacing-md: 16px;            /* Medium spacing for inner padding */
      --spacing-lg: 20px;            /* Large spacing for card padding */
      --spacing-xl: 24px;            /* Extra large spacing for sections */
      --spacing-xxl: 30px;           /* Double extra large spacing for containers */
    }

    /* Back Button */
    .back-btn {
      position: fixed;
      top: 25px;
      left: 30px;
      display: inline-flex;
      align-items: center;
      gap: 8px;
      background: rgba(255, 255, 255, 0.15);
      border: 1px solid rgba(255, 255, 255, 0.3);
      color: white;
      padding: 10px 18px;
      border-radius: 10px;
      text-decoration: none;
      font-weight: 600;
      font-size: 0.9em;
      cursor: pointer;
      backdrop-filter: blur(5px);
      box-shadow: 0 3px 10px rgba(0,0,0,0.2);
      transition: all 0.3s ease;
      z-index: 100;
    }

    .back-btn svg {
      width: 20px;
      height: 20px;
      fill: white;
      transition: transform 0.3s ease;
    }

    .back-btn:hover {
      background: rgba(255, 255, 255, 0.3);
      transform: translateY(-2px) scale(1.05);
      box-shadow: 0 6px 20px rgba(0,0,0,0.3);
    }

    .back-btn:hover svg {
      transform: translateX(-3px);
    }

    /* Reset & Base Styles */
    body {
      font-family: 'Open Sans', sans-serif;
      background: linear-gradient(145deg, #2d1e0b, #a54e07, #ff7b00);
      background-repeat: no-repeat;
      background-size: cover;
      background-position: center;
      margin: 0;
      padding: 0;
      color: var(--text-dark);
      min-height: 100vh;
    }

    /* Main Container */
    .service-container {
      max-width: 950px;
      margin: 60px auto;
      background: #fff;
      border-radius: var(--border-radius-xxl);
      box-shadow: 0 8px 30px var(--shadow-light);
      padding: 45px;
      text-align: center;
    }

    /* Typography */
    h2 {
      font-size: 28px;
      color: var(--text-dark);
      margin-bottom: var(--spacing-xs);
    }

    h3 {
      color: #a54e07;
      font-size: 20px;
      margin-bottom: var(--spacing-xl);
      text-align: left;
    }

    p.description {
      color: var(--text-medium);
      font-size: 16px;
      margin-bottom: var(--spacing-xxl);
    }

    /* Service Icon */
    .service-icon {
      font-size: 70px;
      color: var(--primary-color);
      margin-bottom: var(--spacing-md);
    }

    /* Divider */
    hr {
      border: none;
      border-top: 1px solid var(--border-light);
      margin: var(--spacing-xxl) 0;
    }

    /* Worker Cards */
    .workers-list {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
      gap: 22px;
    }

    .worker-card {
      background: var(--bg-light);
      border: 1px solid var(--border-warm);
      border-radius: var(--border-radius-lg);
      padding: var(--spacing-lg);
      box-shadow: 0 4px 10px var(--shadow-light);
      transition: transform 0.25s ease, box-shadow 0.25s ease;
      text-align: left;
    }

    .worker-card:hover {
      transform: translateY(-6px);
      box-shadow: 0 10px 25px var(--shadow-light);
      background-color: #fff3e0;
    }

    .worker-card h4 {
      font-size: 18px;
      color: var(--primary-hover);
      margin-bottom: var(--spacing-xs);
    }

    .worker-card p {
      font-size: 14px;
      color: var(--text-medium);
      margin: 4px 0;
    }

    .rating {
      color: #ffb400;
      font-size: 14px;
    }

    /* Buttons */
    .btn-book {
      display: inline-block;
      margin-top: var(--spacing-sm);
      background: var(--primary-color);
      color: white;
      padding: var(--spacing-sm) var(--spacing-lg);
      border-radius: var(--border-radius-sm);
      text-decoration: none;
      transition: background 0.3s;
      font-weight: 600;
      cursor: pointer;
      border: none;
    }

    .btn-book:hover {
      background: var(--primary-hover);
    }

    /* Modal */
    .modal {
      display: none;
      position: fixed;
      z-index: 1000;
      left: 0;
      top: 0;
      width: 100%;
      height: 100%;
      background-color: rgba(0,0,0,0.6);
      justify-content: center;
      align-items: center;
    }

    .modal-content {
      background: #fff;
      padding: 30px 35px;
      border-radius: var(--border-radius-xl);
      width: 550px;
      max-height: 85vh;
      overflow-y: auto;
      box-shadow: 0 8px 25px var(--shadow-medium);
      position: relative;
      animation: fadeInUp 0.3s ease;
      margin: var(--spacing-lg);
      box-sizing: border-box;
    }

    .modal-content h3 {
      margin-bottom: 2px;
      text-align: center;
      color: var(--primary-color);
    }

    .modal-subtitle {
      text-align: center;
      color: var(--text-medium);
      font-size: 14px;
      margin-bottom: var(--spacing-md);
    }

    .close-btn {
      position: absolute;
      top: 15px;
      right: 20px;
      font-size: 26px;
      cursor: pointer;
      color: var(--text-light);
    }

    .close-btn:hover {
      color: var(--text-dark);
    }

    /* Form Styles */
    .form-group {
      margin-bottom: var(--spacing-lg);
      text-align: left;
    }

    .form-group label {
      font-weight: 600;
      font-size: 14px;
      color: var(--text-dark);
      display: block;
      margin-bottom: 4px;
    }

    .form-group input,
    .form-group textarea {
      width: 100%;
      padding: 8px 12px;
      border: 1px solid var(--border-light);
      border-radius: var(--border-radius-md);
      font-size: 14px;
      resize: none;
      box-sizing: border-box;
      transition: border-color 0.3s ease;
    }

    .form-group input:focus,
    .form-group textarea:focus {
      border-color: var(--primary-color);
      outline: none;
    }

    .form-group textarea {
      height: 80px;
      line-height: 1.4;
    }

    .form-group.half {
      display: grid;
      grid-template-columns: 1fr 1fr;
      gap: var(--spacing-lg);
      margin-bottom: var(--spacing-xl);
    }

    .form-group.half div {
      display: flex;
      flex-direction: column;
      gap: var(--spacing-xs);
    }

    .form-group.half input {
      width: 100%;
      height: 45px;
    }

    /* Success Popup */
    .success-popup {
      display: none;
      position: fixed;
      z-index: 2000;
      inset: 0;
      background: rgba(0,0,0,0.6);
      justify-content: center;
      align-items: center;
    }

    .success-popup .popup-content {
      background: var(--bg-light);
      padding: var(--spacing-xxl) 40px;
      border-radius: var(--border-radius-xl);
      text-align: center;
      box-shadow: 0 8px 25px var(--shadow-medium);
      animation: fadeInUp 0.3s ease;
    }

    .success-popup h3 {
      color: var(--success-color);
      margin-bottom: var(--spacing-sm);
    }

    .success-popup button {
      background: var(--primary-color);
      color: white;
      border: none;
      border-radius: var(--border-radius-md);
      padding: var(--spacing-sm) var(--spacing-lg);
      margin-top: var(--spacing-md);
      cursor: pointer;
      transition: 0.3s;
      font-weight: 600;
    }

    .success-popup button:hover {
      background: var(--primary-hover);
    }

    /* Animations */
    @keyframes fadeInUp {
      from { transform: translateY(40px); opacity: 0; }
      to { transform: translateY(0); opacity: 1; }
    }

    /* Responsive Styles */
    @media (max-width: 600px) {
      .form-group.half {
        grid-template-columns: 1fr;
      }

      .modal-content {
        padding: var(--spacing-lg);
        margin: var(--spacing-sm);
      }

      .service-container {
        margin: var(--spacing-lg);
        padding: var(--spacing-lg);
      }
    }
  </style>

</head>

<body>
  <!-- Back Button -->
  <a href="/FixExpress/PWA/dist/admin/template01.php" class="back-btn">
    <svg viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
      <path d="M15.41 7.41L14 6l-6 6 6 6 1.41-1.41L10.83 12z"/>
    </svg>
    Back
  </a>

  <div class="service-container">
    <div class="service-icon"><i class="<?php echo $row['icon']; ?>"></i></div>
    <h2><?php echo $row['service_name']; ?></h2>
    <p class="description"><?php echo $row['description']; ?></p>

    <hr>

    <h3>Available Professionals</h3>
    <?php
    $service_id = $row['service_id'];
    $workerQuery = "SELECT * FROM workers WHERE service_id = $service_id AND (status = 'Approved')";
    $workerResult = $conn->query($workerQuery);

    if ($workerResult->num_rows > 0) {
      echo "<div class='workers-list'>";
      while ($worker = $workerResult->fetch_assoc()) {
        echo "
          <div class='worker-card'>
            <h4>{$worker['first_name']} {$worker['last_name']}</h4>
            <p><strong>Experience:</strong> {$worker['experience']}</p>
            <p><strong>Contact:</strong> {$worker['contact']}</p>
            <p class='rating'><strong>Rating:</strong> ⭐ {$worker['rating']}/5</p>
            <button class='btn-book' onclick='openModal({$worker['worker_id']})'>Hire Now</button>
          </div>
        ";
      }
      echo "</div>";
    } else {
      echo "<p>No workers available for this service yet.</p>";
    }
    ?>
  </div>

  <!-- Booking Modal -->
  <div id="bookingModal" class="modal">
    <div class="modal-content">
      <span class="close-btn" onclick="closeModal()">&times;</span>
      <h3>Book This Professional</h3>
      <p class="modal-subtitle">Fill in your details and the worker will contact you soon.</p>

      <form id="bookingForm" method="POST" action="submit_booking.php">
        <input type="hidden" name="worker_id" id="workerId">
        

        <div class="form-group">
          <label>Full Name</label>
        <input type="text" name="fullname" placeholder="Enter your full name" required pattern="^[A-Za-zÀ-ÿÑñ]+(\s([A-Za-zÀ-ÿÑñ]\.|[A-Za-zÀ-ÿÑñ]+)){1,3}$" title="Please enter a valid full name (e.g., Charles D. Gervacio)">

        </div>

        <div class="form-group">
          <label>Contact Number</label>
          <input type="text" name="contact" maxlength="13" placeholder="e.g. 09123456789 or +639123456789" required pattern="^(09\d{9}|\+639\d{9})$" title="Please enter a valid PH number (e.g., 09123456789 or +639123456789)">

        </div>

        <div class="form-group">
          <label>Email Address</label>
          <input type="text" id="email" name="email" placeholder="you@example.com" required title="Please enter a valid email ending with .com or .com.ph (e.g., name@example.com or name@example.com.ph)">
        </div>

        <div class="form-group">
          <label>Complete Address</label>
          <textarea name="address" placeholder="House No., Street, Barangay, City, Province, ZIP Code" required pattern="^[A-Za-z0-9\s.,#-]{10,}\s\d{4}$" title="Please enter a complete address with ZIP code (e.g., 123 Rizal St., Brgy. Poblacion, Calasiao, Pangasinan 2418)"></textarea>

        </div>

        <div class="form-group half">
          <div>
            <label>Date</label>
            <input type="date" name="date" required>
          </div>
          <div>
            <label>Preferred Time</label>
            <input type="time" name="time" required>
          </div>
        </div>

        <div class="form-group" style="margin-bottom: 30px;">
          <label>Please Specify Your Problem <span style="color: #ff0000;">*</span></label>
          <textarea name="notes" placeholder="Please describe your problem in detail..." required></textarea>
        </div>

        <div style="margin-top: -10px; text-align: center;">
          <button type="submit" class="btn-book" style="padding: 12px 30px; font-size: 16px;">Confirm Booking</button>
        </div>
      </form>
    </div>
  </div>

  <!-- Success Popup -->
  <div id="successPopup" class="success-popup">
    <div class="popup-content">
      <h3>✅ Booking Successful!</h3>
      <p>Your request has been sent. The professional will contact you soon.</p>
      <button onclick="closeSuccess()">OK</button>
    </div>
  </div>

  <script>
function openModal(workerId) {
  document.getElementById('bookingModal').style.display = 'flex';
  document.getElementById('workerId').value = workerId;
}

function closeModal() {
  document.getElementById('bookingModal').style.display = 'none';
}

function closeSuccess() {
  document.getElementById('successPopup').style.display = 'none';
}

document.getElementById('email').addEventListener('input', function(e) {
  const email = e.target.value;
  const emailPattern = /^[A-Za-z0-9._%+-]+@[A-Za-z0-9.-]+\.com(\.ph)?$/;
  
  if (email && !emailPattern.test(email)) {
    e.target.setCustomValidity('Please enter a valid email ending with .com or .com.ph');
  } else {
    e.target.setCustomValidity('');
  }
});

document.getElementById('bookingForm').addEventListener('submit', function(e) {
  const email = document.getElementById('email').value;
  const emailPattern = /^[A-Za-z0-9._%+-]+@[A-Za-z0-9.-]+\.com(\.ph)?$/;
  
  if (!emailPattern.test(email)) {
    e.preventDefault();
    alert('Please enter a valid email ending with .com or .com.ph');
    return false;
  }
});

const urlParams = new URLSearchParams(window.location.search);
if (urlParams.get('success') === '1') {
  document.getElementById('successPopup').style.display = 'flex';
  history.replaceState(null, "", window.location.pathname);
}
</script>

</body>
</html>