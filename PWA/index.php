<?php
session_start();
include './dist/database/server.php';

// Handle profile update
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['update_profile'])) {
    // ...existing profile update code...
}

// Fetch top 3 rated technicians
$top_workers = [];
$sql = "SELECT w.first_name, w.last_name, w.contact, w.email, w.experience, w.rating, s.service_name
        FROM workers w
        LEFT JOIN services s ON w.service_id = s.service_id
        ORDER BY w.rating DESC
        LIMIT 3";

$result = $conn->query($sql);
if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $top_workers[] = $row;
    }
}
?>

  <!doctype html>
  <html lang="en" data-pc-preset="preset-1" data-pc-sidebar-caption="true" data-pc-direction="ltr" dir="ltr" data-pc-theme="light">
    <head>
      <title>FixExpress</title>
      <meta charset="utf-8" />
      <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=0, minimal-ui" />
      <meta http-equiv="X-UA-Compatible" content="IE=edge" />
      <meta name="description" content="." />
      <meta name="keywords" content="." />
      <meta name="author" content="Sniper 2025" />
      <link rel="stylesheet" href="./dist/assets/css/index.css" /> 
      
      
      
    </head>

  </head>
  <body>
    <?php include './dist/includes/header.php'; ?>
     
      <!-- Hero Section -->
      <section class="hero">
          <div class="hero-content">
              <h1 class="hero-title">
                  <span>Quick & Reliable</span><br>
                  <span>Repairs</span> <span >When You</span><br>
                  <span>Need Them</span>
              </h1>
              <p class="hero-description">
                  FixExpress connects you with skilled professionals for fast, high-quality repairs. From home maintenance to device fixes, we've got you covered.
              </p>
              <div class="hero-buttons">
                  <button onclick="window.location.href='./dist/admin/template01.php'" class="get-started-btn">Book a Service</button>
                  <button class="learn-btn">Learn More</button>
              </div>
              <div class="search-box">
                  <span class="search-icon">
                      <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"><path fill="#000" d="m19.6 21l-6.3-6.3q-.75.6-1.725.95T9.5 16q-2.725 0-4.612-1.888T3 9.5t1.888-4.612T9.5 3t4.613 1.888T16 9.5q0 1.1-.35 2.075T14.7 13.3l6.3 6.3zM9.5 14q1.875 0 3.188-1.312T14 9.5t-1.312-3.187T9.5 5T6.313 6.313T5 9.5t1.313 3.188T9.5 14"/></svg>
                  </span>
                  <input type="text" class="search-input" placeholder="Search for services...">
                
              </div>
          </div>
          <div class="hero-image">
              <div class="image-container">
                  <img src="./dist/assets/images/application/hero.jpg" alt="Hero Image">
              </div>
          </div>
      </section>

      <!-- Ready to Get Your Fix Section -->
      <section>
          <div class="ready-to-get-fix">
              <h1>Ready to Get Fix?</h1>
              <p>Join thousands of satisfied customers who trust FixExpress for all their repair needs. Our network of professionals is ready to help you today.</p>
          </div>
      </section>

      <!-- Process Section -->
      <section class="process-section" id="how-it-works">
          <div class="badge">How It Works</div>
          <h2>Simple 4-Step Process</h2>
          <p class="process-description">Getting your items fixed with FixExpress is quick and easy. Our streamlined process ensures you get the help you need with minimal hassle.</p>

          <div class="steps-container">
              <!-- Step 1 -->
              <div class="step-card">
                  <div class="icon-circle">
                      <svg viewBox="0 0 24 24">
                          <rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect>
                          <line x1="16" y1="2" x2="16" y2="6"></line>
                          <line x1="8" y1="2" x2="8" y2="6"></line>
                          <line x1="3" y1="10" x2="21" y2="10"></line>
                      </svg>
                  </div>
                  <h3>Book a Service</h3>
                  <p>Select the service you need and choose a convenient date and time.</p>
                  <div class="step-number">1</div>
              </div>

              <!-- Step 2 -->
              <div class="step-card">
                  <div class="icon-circle">
                      <svg viewBox="0 0 24 24">
                          <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path>
                          <circle cx="9" cy="7" r="4"></circle>
                          <path d="M23 21v-2a4 4 0 0 0-3-3.87"></path>
                          <path d="M16 3.13a4 4 0 0 1 0 7.75"></path>
                      </svg>
                  </div>
                  <h3>Get Matched</h3>
                  <p>We'll match you with a qualified professional in your area.</p>
                  <div class="step-number">2</div>
              </div>

              <!-- Step 3 -->
              <div class="step-card">
                  <div class="icon-circle">
                      <svg viewBox="0 0 24 24">
                          <circle cx="12" cy="12" r="10"></circle>
                          <polyline points="12 6 12 12 16 14"></polyline>
                      </svg>
                  </div>
                  <h3>Service Delivery</h3>
                  <p>The professional arrives on time and completes the repair.</p>
                  <div class="step-number">3</div>
              </div>

              <!-- Step 4 -->
              <div class="step-card">
                  <div class="icon-circle">
                      <svg viewBox="0 0 24 24">
                          <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path>
                          <polyline points="22 4 12 14.01 9 11.01"></polyline>
                      </svg>
                  </div>
                  <h3>Satisfaction Guaranteed</h3>
                  <p>Enjoy your fixed item with our satisfaction guarantee.</p>
                  <div class="step-number">4</div>
              </div>
          </div>

          <a href="signup.php" class="btn btn-primary btn-large">Get Started Now</a>
      </section>
      <!-- Expert Repair Solutions Section -->
      <section class="services-section" id="services">
          <div class="services-header">
              <span class="services-badge">Our Services</span>
              <h2>Expert Repair Solutions</h2>
              <p class="services-description">We offer a wide range of repair and maintenance services to keep your home, devices, and more in top working order.</p>
          </div>

          <div class="services-grid">
              <!-- Service 1 -->
              <div class="service-card">
                  <div class="service-icon">
                      <svg xmlns="http://www.w3.org/2000/svg" width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                          <path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"></path>
                          <polyline points="9 22 9 12 15 12 15 22"></polyline>
                      </svg>
                  </div>
                  <h3>Home Improvement</h3>
                  <p>Provides services to improve and upgrade your home.</p>
              </div>

              <!-- Service 2 -->
              <div class="service-card">
                  <div class="service-icon">
                      <svg xmlns="http://www.w3.org/2000/svg" width="47" height="47" viewBox="0 0 24 24"><g fill="none" stroke="#fff" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"><path d="M4 14.07c1.015 0 2.431-.302 3.32.35l1.762 1.29c.655.48 1.364.322 2.095.208c.962-.151 1.823.67 1.823 1.738c0 .292-2.073 1.035-2.372 1.176a1.75 1.75 0 0 1-1.798-.182l-1.988-1.457"/><path d="m13 17l4.091-1.89a1.98 1.98 0 0 1 2.089.515l.67.701c.24.25.184.672-.113.844l-7.854 4.561a1.96 1.96 0 0 1-1.552.187L4 20.027M12.002 12s2.1-2.239 2.1-5s-2.1-5-2.1-5s-2.1 2.239-2.1 5s2.1 5 2.1 5m0 0s3.067-.068 5-2.04c1.933-1.973 2-5.103 2-5.103s-1.27.028-2.69.574M12.002 12s-3.067-.068-5-2.04c-1.933-1.973-2-5.103-2-5.103s1.27.028 2.69.574"/></g></svg>
                  </div>
                  <h3>Wellnesss</h3>
                  <p>Offers massage, fitness, and therapy services.</p>
              </div>

              <!-- Service 3 -->
              <div class="service-card">
                  <div class="service-icon">
                      <svg xmlns="http://www.w3.org/2000/svg" width="48" height="48" viewBox="0 0 24 24"><path fill="#fff" d="M4.5 12.125q-1.05 0-1.775-.725T2 9.625t.725-1.775T4.5 7.125t1.775.725T7 9.625T6.275 11.4t-1.775.725m4.5-4q-1.05 0-1.775-.725T6.5 5.625t.725-1.775T9 3.125t1.775.725t.725 1.775t-.725 1.775T9 8.125m6 0q-1.05 0-1.775-.725T12.5 5.625t.725-1.775T15 3.125t1.775.725t.725 1.775t-.725 1.775T15 8.125m4.5 4q-1.05 0-1.775-.725T17 9.625t.725-1.775t1.775-.725t1.775.725T22 9.625t-.725 1.775t-1.775.725m-12.85 10q-1.125 0-1.888-.862T4 19.225q0-1.3.888-2.275t1.762-1.925q.725-.775 1.25-1.687t1.25-1.713q.55-.65 1.275-1.075T12 10.125t1.575.4t1.275 1.05q.7.8 1.238 1.725t1.262 1.725q.875.95 1.762 1.925T20 19.225q0 1.175-.763 2.038t-1.887.862q-1.35 0-2.675-.225T12 21.675t-2.675.225t-2.675.225"/></svg>
                  </div>
                  <h3>Pets</h3>
                  <p>Provides pet care, grooming, and vet services.</p>
              </div>

              <!-- Service 4 -->
              <div class="service-card">
                  <div class="service-icon">
                      <svg xmlns="http://www.w3.org/2000/svg" width="48" height="48" viewBox="0 0 24 24"><g fill="none" stroke="#fff" stroke-width="1.6"><path d="M11 11L6 6"/><path stroke-linejoin="round" d="M5 7.5L7.5 5l-3-1.5l-1 1zm14.975 1.475a3.5 3.5 0 0 0 .79-3.74l-1.422 1.422h-2v-2l1.422-1.422a3.5 3.5 0 0 0-4.529 4.53l-6.47 6.471a3.5 3.5 0 0 0-4.53 4.529l1.421-1.422h2v2l-1.422 1.422a3.5 3.5 0 0 0 4.53-4.528l6.472-6.472a3.5 3.5 0 0 0 3.738-.79Z"/><path stroke-linejoin="round" d="m11.797 14.5l5.604 5.604a1.35 1.35 0 0 0 1.911 0l.792-.792a1.35 1.35 0 0 0 0-1.911L14.5 11.797"/></g></svg>
                  </div>
                  <h3>Repair & Technical Support</h3>
                  <p>Fast and reliable fix for gadgets and appliances.</p>
              </div>

              <!-- Service 5 -->
              <div class="service-card">
                  <div class="service-icon">
                      <svg xmlns="http://www.w3.org/2000/svg" width="48" height="48" viewBox="0 0 15 15"><path fill="#fff" d="M3 5a2 2 0 0 0 1.732-1H12a1 1 0 1 0 0-2H4.732a2 2 0 0 0-3.464 0H3v2H1.268A2 2 0 0 0 3 5m-.854 4.354A.5.5 0 0 0 2 9.707V13.5a.5.5 0 0 0 .5.5H4a.5.5 0 0 0 .5-.5V13h6v.5a.5.5 0 0 0 .5.5h1.5a.5.5 0 0 0 .5-.5V9.707a.5.5 0 0 0-.146-.353L12 8.5l-1.354-2.257a.5.5 0 0 0-.43-.243H4.784a.5.5 0 0 0-.429.243L3 8.5zM11.134 9H3.866l1.2-2h4.868zM5.5 10.828v.372a.3.3 0 0 1-.3.3H3.3a.3.3 0 0 1-.3-.3v-.834a.3.3 0 0 1 .359-.294l1.82.364a.4.4 0 0 1 .321.392m6.5-.34v.712a.3.3 0 0 1-.3.3H9.8a.3.3 0 0 1-.3-.3v-.454a.3.3 0 0 1 .241-.294l1.78-.356a.4.4 0 0 1 .479.392"/></svg>
                  </div>
                  <h3>Auto Repair</h3>
                  <p>Quality car maintenance and repair services.</p>
              </div>

              <!-- Service 6 -->
              <div class="service-card">
                  <div class="service-icon">
                      <svg xmlns="http://www.w3.org/2000/svg" width="48" height="48" viewBox="0 0 24 24"><path fill="#fff" d="M18 15v-2h2q.425 0 .713.288T21 14t-.288.713T20 15zm0 4v-2h2q.425 0 .713.288T21 18t-.288.713T20 19zm-4 1q-.825 0-1.412-.587T12 18h-2v-4h2q0-.825.588-1.412T14 12h3v8zm-7-3q-1.65 0-2.825-1.175T3 13t1.175-2.825T7 9h1.5q.625 0 1.063-.437T10 7.5t-.437-1.062T8.5 6H5q-.425 0-.712-.288T4 5t.288-.712T5 4h3.5q1.45 0 2.475 1.025T12 7.5t-1.025 2.475T8.5 11H7q-.825 0-1.412.588T5 13t.588 1.413T7 15h2v2z"/></svg>
                  </div>
                  <h3>Electrical Repair</h3>
                  <p>Fixes wiring, lighting, and other electrical issues.</p>
              </div>
          </div>

          <div class="view-all-container">
              <button onclick="window.location.href='./dist/admin/template01.php'" class="view-all-btn"style="display: inline-block; padding: 12px 30px; background: var(--primary-color, #f39c12); color: white; text-decoration: none; border-radius: 8px; font-weight: 600; transition: all 0.3s ease; box-shadow: 0 4px 15px rgba(0,0,0,0.1); cursor: pointer;">View all services</button>
          </div>
      </section>

      <!-- ======Customer ratings====== -->
      
      <section id="top-rated" class="top-rated-section">
  <h2 class="section-title">Top Rated Technicians</h2>

  <div class="technicians-container">
    <?php if (!empty($top_workers)): ?>
      <?php foreach ($top_workers as $worker): ?>
        <div class="technician-card">
          <h3><?php echo htmlspecialchars($worker['first_name'] . ' ' . $worker['last_name']); ?></h3>
          
          <p><i class="fas fa-phone"></i> <?php echo htmlspecialchars($worker['contact']); ?></p>
          <p><i class="fas fa-envelope"></i> <?php echo htmlspecialchars($worker['email']); ?></p>
          <p><i class="fas fa-briefcase"></i> Experience: <?php echo htmlspecialchars($worker['experience']); ?> yrs</p>
          <p><i class="fas fa-check"></i> Service: <?php echo htmlspecialchars($worker['service_name'] ?? 'N/A'); ?></p>
          <p class="rating"><i class="fas fa-star"></i> <?php echo htmlspecialchars($worker['rating']); ?></p>
        </div>
      <?php endforeach; ?>
    <?php else: ?>
      <p>No top rated technicians yet.</p>
    <?php endif; ?>
  </div>

  <!-- View All Ratings Button -->
    <div class="tech-footer">
      <a href="./dist/admin/all-professionals.php" class="view-all-btn" style="display: inline-block; padding: 12px 30px; background: var(--primary-color, #f39c12); color: white; text-decoration: none; border-radius: 8px; font-weight: 600; transition: all 0.3s ease; box-shadow: 0 4px 15px rgba(0,0,0,0.1); cursor: pointer;">View All Professionals</a>
    </div>
</section>

  <!-- Customer Feedback Section -->
  <section id="feedback" class="feedback-unique">
    <div class="feedback-header">
      <h2>What Our Customers Say</h2>
      <p>Real stories from people who trust FixExpress with their repairs.</p>
    </div>
      <div class="feedback-showcase" style="display: flex; justify-content: space-between; gap: 20px; margin: 0 auto; max-width: 1200px; padding: 0 20px;">
    <?php
    $ratingQuery = "SELECT * FROM website_ratings ORDER BY rating DESC, date_submitted DESC LIMIT 3";
    $ratingResult = $conn->query($ratingQuery);
    
    $positions = ['left', 'center', 'right'];
    $i = 0;

    if ($ratingResult && $ratingResult->num_rows > 0) {
      while ($rating = $ratingResult->fetch_assoc()) {
        $stars = str_repeat('⭐', $rating['rating']);
        $position = $positions[$i];
        echo "
        <div class='feedback-bubble bubble-{$position}' style='flex: 1; min-width: 0; background: rgba(255, 255, 255, 0.1); backdrop-filter: blur(10px); padding: 20px; border-radius: 15px; box-shadow: 0 8px 32px 0 rgba(31, 38, 135, 0.37); margin: 10px; transition: all 0.3s ease;'>
          <div class='rating-stars' style='margin-bottom: 10px; font-size: 18px;'>{$stars}</div>
          <p style='font-style: italic; margin-bottom: 15px;'>\"" . htmlspecialchars($rating['comment']) . "\"</p>
          <div class='feedback-author' style='display: flex; flex-direction: column; align-items: center; text-align: center; width: 100%; margin-top: 15px; padding-top: 15px; border-top: 1px solid rgba(255,255,255,0.2);'>
            <h4 style='margin: 0 0 5px 0; color: var(--primary-color, #ffffffff);'>" . htmlspecialchars($rating['reviewer_name']) . "</h4>
            <span style='font-size: 0.9em; opacity: 0.8;'>" . date('F j, Y', strtotime($rating['date_submitted'])) . "</span>
          </div>
        </div>";
        $i++;
      }

      // Fill remaining bubbles if less than 3 ratings
      while ($i < 3) {
        $position = $positions[$i];
        echo "
        <div class='feedback-bubble bubble-{$position}' style='flex: 1; min-width: 0; background: rgba(255, 255, 255, 0.1); backdrop-filter: blur(10px); padding: 20px; border-radius: 15px; box-shadow: 0 8px 32px 0 rgba(31, 38, 135, 0.37); margin: 10px; transition: all 0.3s ease;'>
          <p style='font-style: italic; margin-bottom: 15px;'>Be the first to share your experience!</p>
          <div class='feedback-author' style='display: flex; align-items: center;'>
            <div>
              <h4 style='margin: 0; color: var(--primary-color, #ffffffff);'>Your feedback matters</h4>
              <span style='font-size: 0.9em; opacity: 0.8;'>Rate our service</span>
            </div>
          </div>
        </div>";
        $i++;
      }
    } else {
      // Show placeholder for all three bubbles if no ratings
      foreach ($positions as $position) {
        echo "
        <div class='feedback-bubble bubble-{$position}' style='flex: 1; min-width: 0; background: rgba(255, 255, 255, 0.1); backdrop-filter: blur(10px); padding: 20px; border-radius: 15px; box-shadow: 0 8px 32px 0 rgba(31, 38, 135, 0.37); margin: 10px; transition: all 0.3s ease;'>
          <p style='font-style: italic; margin-bottom: 15px;'>Be the first to share your experience!</p>
          <div class='feedback-author' style='display: flex; align-items: center;'>
            <div>
              <h4 style='margin: 0; color: var(--primary-color, #ffffffff);'>Your feedback matters</h4>
              <span style='font-size: 0.9em; opacity: 0.8;'>Rate our service</span>
            </div>
          </div>
        </div>";
      }
    }
    ?>
  </div>

  <div class="view-ratings-container" style="text-align: center; margin: 30px 0;">
    <a href="/FixExpress/PWA/dist/admin/website_ratings.php" class="view-all-btn" style="display: inline-block; padding: 12px 30px; background: var(--primary-color, #f39c12); color: white; text-decoration: none; border-radius: 8px; font-weight: 600; transition: all 0.3s ease; box-shadow: 0 4px 15px rgba(0,0,0,0.1); cursor: pointer;">View All Ratings</a>
  </div>
    
  </section>

  <!-- ======= Footer Section ======= -->
  <footer class="footer">
    <div class="footer-container">
      <!-- FixExpress -->
      <div class="footer-box">
        <h2>FixExpress</h2>
        <p>Connecting you with skilled professionals for all your repair and maintenance needs.</p>
        <div class="footer-socials">
          <a href="#"><svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24">
                          <path fill="currentColor" d="M14 13.5h2.5l1-4H14v-2c0-1.03 0-2 2-2h1.5V2.14c-.326-.043-1.557-.14-2.857-.14C11.928 2 10 3.657 10 6.7v2.8H7v4h3V22h4z"/>
                      </svg></a>
          <a href="#"><svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24">
                          <path fill="none" stroke="#ffffffff" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="m3 21l7.548-7.548M21 3l-7.548 7.548m0 0L8 3H3l7.548 10.452m2.904-2.904L21 21h-5l-5.452-7.548"/>
                      </svg></a>
          <a href="#"><svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24">
                          <path fill="currentColor" d="M12 2c2.717 0 3.056.01 4.122.06c1.065.05 1.79.217 2.428.465c.66.254 1.216.598 1.772 1.153a4.908 4.908 0 0 1 1.153 1.772c.247.637.415 1.363.465 2.428c.047 1.066.06 1.405.06 4.122c0 2.717-.01 3.056-.06 4.122c-.05 1.065-.218 1.79-.465 2.428a4.883 4.883 0 0 1-1.153 1.772a4.915 4.915 0 0 1-1.772 1.153c-.637.247-1.363.415-2.428.465c-1.066.047-1.405.06-4.122.06c-2.717 0-3.056-.01-4.122-.06c-1.065-.05-1.79-.218-2.428-.465a4.89 4.89 0 0 1-1.772-1.153a4.904 4.904 0 0 1-1.153-1.772c-.248-.637-.415-1.363-.465-2.428C2.013 15.056 2 14.717 2 12c0-2.717.01-3.056.06-4.122c.05-1.066.217-1.79.465-2.428a4.88 4.88 0 0 1 1.153-1.772A4.897 4.897 0 0 1 5.45 2.525c.638-.248 1.362-.415 2.428-.465C8.944 2.013 9.283 2 12 2zm0 5a5 5 0 1 0 0 10a5 5 0 0 0 0-10zm6.5-.25a1.25 1.25 0 0 0-2.5 0a1.25 1.25 0 0 0 2.5 0zM12 9a3 3 0 1 1 0 6a3 3 0 0 1 0-6z"/>
                      </svg></a>
          <a href="#"> <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24">
                          <path fill="currentColor" d="M19 3a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h14m-.5 15.5v-5.3a3.26 3.26 0 0 0-3.26-3.26c-.85 0-1.84.52-2.32 1.3v-1.11h-2.79v8.37h2.79v-4.93c0-.77.62-1.4 1.39-1.4a1.4 1.4 0 0 1 1.4 1.4v4.93h2.79M6.88 8.56a1.68 1.68 0 0 0 1.68-1.68c0-.93-.75-1.69-1.68-1.69a1.69 1.69 0 0 0-1.69 1.69c0 .93.76 1.68 1.69 1.68m1.39 9.94v-8.37H5.5v8.37h2.77z"/>
                      </svg></a>
        </div>
      </div>

      <!-- Quick Links -->
      <div class="footer-box">
        <h3>Quick Links</h3>
        <ul>
          <li><a href="#">About Us</a></li>
          <li><a href="#services">Services</a></li>
          <li><a href="#how-it-works">How It Works</a></li>
          <li><a href="#contact">Contact</a></li>
        </ul>
      </div>

      <!-- Services -->
      <div class="footer-box">
        <h3>Services</h3>
        <ul>
          <li><a href="#">Home Improvement</a></li>
          <li><a href="#">Wellness</a></li>
          <li><a href="#">Pets</a></li>
          <li><a href="#">Repair & Technical Support</a></li>
          <li><a href="#">Auto</a></li>
          <li><a href="#">Electrical</a></li>
        </ul>
      </div>

      <!-- Contact -->
      <div class="footer-box">
        <h3>Contact Us</h3>
        <p><i class="fas fa-map-marker-alt"></i> 123 Repair Street, Fixville, FX 12345</p>
        <p><i class="fas fa-phone"></i> (555) 123-4567</p>
        <p><i class="fas fa-envelope"></i> info@fixexpress.com</p>
      </div>
    </div>

    <div class="footer-bottom">
      <p>&copy; 2025 FixExpress. All Rights Reserved.</p>
    </div>
  </footer>

 
    </body>
  </html>