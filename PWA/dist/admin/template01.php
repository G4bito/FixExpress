<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

include '../database/server.php';

class Service {
    private $conn;

    public function __construct($db) {
        $this->conn = $db;
    }

    public function getAllServices() {
        $query = "SELECT * FROM services ORDER BY service_name ASC";
        return $this->conn->query($query);
    }
}

$service = new Service($conn);
$result = $service->getAllServices();
?>

<!doctype html>
<html lang="en">
<head>
  <title>FixExpress | Services</title>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <link href="https://fonts.googleapis.com/css2?family=Open+Sans:wght@300;400;600&display=swap" rel="stylesheet" />
  <link rel="stylesheet" href="../assets/fonts/fontawesome.css" />
  <link rel="stylesheet" href="../assets/css/style.css" />

  <style>
    body {
      background: linear-gradient(145deg, #2d1e0b, #a54e07, #ff7b00);
      font-family: 'Open Sans', sans-serif;
      color: #fff;
      background-repeat: no-repeat;
      background-attachment: fixed;
    }
    h4 {
  text-align: center;
  margin: 40px auto 20px;
  color: #fff;
  font-weight: 700;
  font-size: 24px;
  letter-spacing: 0.5px;
  position: relative;
}

h4::after {
  content: '';
  display: block;
  width: 80px;
  height: 3px;
  background: #ffd580;
  margin: 10px auto 0;
  border-radius: 3px;
}

    /* ----- Typing Header ----- */
    #typeHeader {
      text-align: center;
      margin: 40px auto 20px;
      color: #fff;
      font-weight: 700;
      font-size: 26px;
      letter-spacing: 1px;
      overflow: hidden;
      white-space: nowrap;
      border-right: 3px solid rgba(255, 255, 255, 0.75);
      width: fit-content;
      animation: blinkCursor 0.8s steps(2, start) infinite;
    }

    @keyframes blinkCursor {
      50% { border-color: transparent; }
    }

    /* ----- Service Cards ----- */
    .services-container {
      display: flex;
      flex-wrap: wrap;
      gap: 24px;
      margin-top: 25px;
      margin-bottom: 25px;
      justify-content: center;
      margin: 10px auto;
      max-width: 1100px;
    }

    .service-card {
      position: relative;
      background: rgba(255, 255, 255, 0.2);
      backdrop-filter: blur(10px);
      border: 1px solid rgba(255, 255, 255, 0.3);
      border-radius: 14px;
      box-shadow: 0 4px 20px rgba(0,0,0,0.25);
      width: 230px;
      text-align: center;
      padding: 25px 18px;
      transition: all 0.3s ease;
      text-decoration: none;
      color: #fff;
      display: block;
      overflow: hidden;
    }

    .service-card::before {
      content: "";
      position: absolute;
      inset: 0;
      background: radial-gradient(circle at var(--x, 50%) var(--y, 50%), rgba(255,255,255,0.25), transparent 80%);
      opacity: 0;
      transition: opacity 0.4s ease;
      pointer-events: none;
    }

    .service-card:hover::before {
      opacity: 1;
    }

    .service-card:hover {
      transform: translateY(-6px);
      box-shadow: 0 8px 25px rgba(0, 0, 0, 0.3);
    }

    .service-icon {
      font-size: 42px;
      color: #ffd580;
      margin-bottom: 14px;
    }

    .service-title {
      font-size: 18px;
      font-weight: 600;
      color: #fff;
      margin-bottom: 6px;
    }

    .service-desc {
      font-size: 14px;
      color: #f5f5f5;
      line-height: 1.4;
    }

    @media (max-width: 600px) {
      .services-container {
        flex-direction: column;
        align-items: center;
      }
    }
    .home-btn {
      display: inline-flex;
      align-items: center;
      gap: 6px;
      background: linear-gradient(145deg, #ffb347, #ff7b00);
      color: #fff;
      border: none;
      border-radius: 10px;
      padding: 8px 16px;
      font-weight: 600;
      font-size: 15px;
      cursor: pointer;
      transition: all 0.25s ease;
      box-shadow: 0 3px 10px rgba(0, 0, 0, 0.25);
    }

    .home-btn:hover {
      transform: translateY(-2px);
      background: linear-gradient(145deg, #ffcc66, #ff9a00);
      box-shadow: 0 6px 14px rgba(0, 0, 0, 0.3);
    }

    .home-btn:active {
      transform: scale(0.97);
    }

    .home-icon {
      stroke: #fff;
      transition: stroke 0.2s ease;
    }

    .home-btn:hover .home-icon {
      stroke: #fff5d0;
    }

  </style>
</head>

<body>

  <div class="pc-container">
    <div class="pc-content">
      <div class="page-header">
        <div class="page-block">
          <div class="page-header-title">
            <h5 class="mb-0 font-medium"></h5>
          </div>
          <ul class="breadcrumb">
           <button onclick="window.location='../../index.php'" class="home-btn">
              <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                viewBox="0 0 24 24" class="home-icon">
                <path d="M3 9.5L12 3l9 6.5V21a1 1 0 0 1-1 1h-5v-7H9v7H4a1 1 0 0 1-1-1V9.5z"/>
              </svg>
              <span>Home</span>
            </button>
          </ul>
        </div>
      </div>

      <!-- Typing Header -->
      <h4 id="typeHeader"></h4>

      <div class="services-container">
        <?php
        if ($result && $result->num_rows > 0) {
          while ($row = $result->fetch_assoc()) {
            echo "
              <a href='service_details.php?id={$row['service_id']}' class='service-card'>
                <div class='service-icon'><i class='{$row['icon']}'></i></div>
                <div class='service-title'>{$row['service_name']}</div>
                <div class='service-desc'>{$row['description']}</div>
              </a>
            ";
          }
        } else {
          echo '<p class=\"text-center text-muted\">No services available yet.</p>';
        }
        ?>
      </div>
    </div>
  </div>

  <!-- Scripts -->
  <script src="../assets/js/plugins/simplebar.min.js"></script>
  <script src="../assets/js/plugins/popper.min.js"></script>
  <script src="../assets/js/icon/custom-icon.js"></script>
  <script src="../assets/js/plugins/feather.min.js"></script>
  <script src="../assets/js/component.js"></script>
  <script src="../assets/js/theme.js"></script>
  <script src="../assets/js/script.js"></script>

  <!-- Spotlight Effect -->
  <script>
    document.querySelectorAll('.service-card').forEach(card => {
      card.addEventListener('mousemove', e => {
        const rect = card.getBoundingClientRect();
        card.style.setProperty('--x', `${e.clientX - rect.left}px`);
        card.style.setProperty('--y', `${e.clientY - rect.top}px`);
      });
    });
  </script>

  <!-- Typing Animation -->
  <script>
    document.addEventListener("DOMContentLoaded", () => {
      const text = "Explore Our Home & Pet Services";
      const header = document.getElementById("typeHeader");
      let index = 0;
      const typingSpeed = 80;

      function type() {
        if (index < text.length) {
          header.textContent += text.charAt(index);
          index++;
          setTimeout(type, typingSpeed);
        }
      }

      type();
    });
  </script>

  <?php include '../includes/footer.php'; ?>
</body>
</html>
