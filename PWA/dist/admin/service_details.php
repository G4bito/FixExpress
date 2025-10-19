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
  <style>
  body {
    font-family: 'Open Sans', sans-serif;
    background: linear-gradient(145deg, #2d1e0b, #a54e07, #ff7b00);
    margin: 0;
    padding: 0;
    color: #222;
    min-height: 100vh;
    display: flex;
    flex-direction: column;
    align-items: center;
  }

  .service-container {
    max-width: 950px;
    width: 90%;
    margin: 100px auto 60px auto;
    background: #fffdfa;
    border-radius: 20px;
    box-shadow: 0 10px 30px rgba(0,0,0,0.25);
    padding: 50px;
    text-align: center;
    animation: fadeIn 0.5s ease-in-out;
  }

  @keyframes fadeIn {
    from { opacity: 0; transform: translateY(20px); }
    to { opacity: 1; transform: translateY(0); }
  }

  .service-icon {
    font-size: 70px;
    color: #f39c12;
    margin-bottom: 15px;
  }

  h2 {
    font-size: 30px;
    color: #2c2c2c;
    margin-bottom: 8px;
  }

  p.description {
    color: #555;
    font-size: 16px;
    margin-bottom: 30px;
    line-height: 1.6;
  }

  hr {
    border: none;
    border-top: 1px solid #eee;
    margin: 30px 0;
  }

  h3 {
    color: #a54e07;
    font-size: 22px;
    margin-bottom: 25px;
    text-align: left;
  }

  .workers-list {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(260px, 1fr));
    gap: 22px;
  }

  .worker-card {
    background: #fffdfa;
    border: 1px solid #f0e0c0;
    border-radius: 15px;
    padding: 22px;
    box-shadow: 0 4px 12px rgba(0,0,0,0.08);
    transition: all 0.25s ease;
  }

  .worker-card:hover {
    transform: translateY(-6px);
    box-shadow: 0 10px 25px rgba(0,0,0,0.15);
    background-color: #fff3e0;
  }

  .worker-card h4 {
    font-size: 18px;
    color: #e67e22;
    margin-bottom: 8px;
  }

  .worker-card p {
    font-size: 14px;
    color: #444;
    margin: 4px 0;
  }

  .rating {
    color: #ffb400;
    font-weight: 600;
  }

  .btn-book {
    display: inline-block;
    margin-top: 12px;
    background: #f39c12;
    color: white;
    padding: 10px 18px;
    border-radius: 6px;
    text-decoration: none;
    transition: background 0.3s;
    font-weight: 600;
    cursor: pointer;
    border: none;
  }

  .btn-book:hover {
    background: #e67e22;
  }

  /* Modal */
  .modal {
    display: none;
    position: fixed;
    z-index: 1000;
    left: 0; top: 0;
    width: 100%; height: 100%;
    background-color: rgba(0,0,0,0.6);
    justify-content: center;
    align-items: center;
  }

  .modal-content {
    background: #fff;
    padding: 35px 40px;
    border-radius: 15px;
    width: 550px;
    max-height: 90vh;
    overflow-y: auto;
    box-shadow: 0 8px 25px rgba(0,0,0,0.25);
    position: relative;
    animation: fadeInUp 0.3s ease;
  }

  @keyframes fadeInUp {
    from { transform: translateY(40px); opacity: 0; }
    to { transform: translateY(0); opacity: 1; }
  }

  .close-btn {
    position: absolute;
    top: 15px;
    right: 20px;
    font-size: 26px;
    cursor: pointer;
    color: #666;
  }

  .close-btn:hover {
    color: #000;
  }

  .modal-content h3 {
    margin-bottom: 5px;
    text-align: center;
    color: #f39c12;
  }

  .modal-subtitle {
    text-align: center;
    color: #555;
    font-size: 14px;
    margin-bottom: 20px;
  }

  .form-group {
    margin-bottom: 18px;
    text-align: left;
  }

  .form-group label {
    font-weight: 600;
    font-size: 14px;
    color: #333;
    display: block;
    margin-bottom: 5px;
  }

  .form-group input,
  .form-group textarea {
    width: 100%;
    padding: 10px 12px;
    border: 1px solid #ccc;
    border-radius: 8px;
    font-size: 15px;
    resize: none;
  }

  .form-group textarea {
    height: 80px;
  }

  .form-group.half {
    display: grid;
    grid-template-columns: 1fr 1fr;
    column-gap: 25px;
  }

  @media (max-width: 600px) {
    .form-group.half { grid-template-columns: 1fr; }
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
    background: #fffdfa;
    padding: 30px 40px;
    border-radius: 15px;
    text-align: center;
    box-shadow: 0 8px 25px rgba(0,0,0,0.25);
    animation: fadeInUp 0.3s ease;
  }

  .success-popup h3 {
    color: #27ae60;
    margin-bottom: 10px;
  }

  .success-popup button {
    background: #f39c12;
    color: white;
    border: none;
    border-radius: 8px;
    padding: 10px 20px;
    margin-top: 15px;
    cursor: pointer;
    transition: 0.3s;
    font-weight: 600;
  }

  .success-popup button:hover {
    background: #e67e22;
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
          <input type="text" name="fullname" placeholder="Enter your full name" required>
        </div>

        <div class="form-group">
          <label>Contact Number</label>
          <input type="text" name="contact" placeholder="e.g. 09123456789" required>
        </div>

        <div class="form-group">
          <label>Email Address</label>
          <input type="email" name="email" placeholder="you@example.com" required>
        </div>

        <div class="form-group">
          <label>Complete Address</label>
          <textarea name="address" placeholder="House No., Street, Barangay, City, Province, ZIP Code" required></textarea>
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

        <div class="form-group">
          <label>Additional Notes (Optional)</label>
          <textarea name="notes" placeholder="Add details about your request..."></textarea>
        </div>

        <button type="submit" class="btn-book">Confirm Booking</button>
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

const urlParams = new URLSearchParams(window.location.search);
if (urlParams.get('success') === '1') {
  document.getElementById('successPopup').style.display = 'flex';
  history.replaceState(null, "", window.location.pathname);
}
</script>

</body>
</html>
