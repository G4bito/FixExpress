<?php
session_start();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>About FixExpress</title>
    <link rel="stylesheet" href="/FixExpress/PWA/dist/assets/css/index.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
            background-color: #2c2c2c;
        }

        /* Hero Section */
        .about-hero {
            background: linear-gradient(135deg, #D46419 0%, #171B1F 95%);
            padding: 100px 50px;
            color: white;
            text-align: center;
        }

        .about-hero h1 {
            font-size: 3.5rem;
            font-weight: 800;
            background: linear-gradient(to right, #fff, #ffd28a);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            margin-bottom: 20px;
        }

        .about-hero p {
            font-size: 1.2rem;
            color: #fde68a;
            max-width: 800px;
            margin: 0 auto;
            line-height: 1.6;
        }

        /* Our Story Section */
        .story-section {
            background: linear-gradient(135deg, #171B1F 0%, #e67e22 100%);
            padding: 80px 50px;
        }

        .story-content {
            max-width: 1200px;
            margin: 0 auto;
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 60px;
            align-items: center;
        }

        .story-text h2 {
            font-size: 2.8rem;
            font-weight: 800;
            background: linear-gradient(to right, #fff, #ffd28a);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            margin-bottom: 25px;
        }

        .story-text p {
            font-size: 1.1rem;
            line-height: 1.8;
            color: #fde68a;
            margin-bottom: 20px;
        }

        .story-image {
            border-radius: 15px;
            overflow: hidden;
            box-shadow: 0 10px 30px rgba(0,0,0,0.3);
        }

        .story-image img {
            width: 100%;
            height: 400px;
            object-fit: cover;
        }

        /* Statistics Section */
        .stats-section {
            background: linear-gradient(135deg, #D46419 0%, #171B1F 95%);
            padding: 80px 50px;
            text-align: center;
        }

        .stats-section h2 {
            font-size: 2.8rem;
            font-weight: 800;
            background: linear-gradient(to right, #fff, #ffd28a);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            margin-bottom: 15px;
        }

        .stats-section > p {
            color: #fde68a;
            font-size: 1.1rem;
            margin-bottom: 50px;
        }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 30px;
            max-width: 1200px;
            margin: 0 auto;
        }

        .stat-card {
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(12px);
            border-radius: 15px;
            padding: 40px 25px;
            transition: all 0.4s ease;
            border: 1px solid rgba(255, 255, 255, 0.2);
        }

        .stat-card:hover {
            transform: translateY(-10px);
            background: rgba(255, 255, 255, 0.15);
            box-shadow: 0 20px 60px rgba(255, 165, 0, 0.4);
        }

        .stat-icon {
            font-size: 2.5rem;
            color: #ffd28a;
            margin-bottom: 20px;
        }

        .stat-number {
            font-size: 3rem;
            font-weight: 800;
            color: #fff;
            margin-bottom: 10px;
        }

        .stat-label {
            font-size: 1.1rem;
            color: #fde68a;
            margin-bottom: 10px;
        }

        .stat-description {
            font-size: 0.9rem;
            color: rgba(255, 255, 255, 0.8);
            line-height: 1.4;
        }

        /* Mission & Vision Section */
        .mission-section {
            background: linear-gradient(150deg, #e67e22 17%, #171B1F 100%);
            padding: 80px 50px;
        }

        .mission-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 40px;
            max-width: 1200px;
            margin: 0 auto;
        }

        .mission-item {
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(12px);
            border-radius: 15px;
            padding: 40px;
            border: 1px solid rgba(255, 255, 255, 0.2);
            transition: all 0.3s ease;
        }

        .mission-item:hover {
            transform: translateY(-5px);
            background: rgba(255, 255, 255, 0.15);
        }

        .mission-title {
            color: #ffd28a;
            font-size: 1rem;
            font-weight: 600;
            letter-spacing: 2px;
            margin-bottom: 15px;
        }

        .mission-heading {
            font-size: 2rem;
            color: #fff;
            margin-bottom: 20px;
            font-weight: 700;
        }

        .mission-text {
            font-size: 1.1rem;
            color: #fde68a;
            line-height: 1.8;
        }

        /* Values Section */
        .values-section {
            background: linear-gradient(135deg, #171B1F 0%, #e67e22 100%);
            padding: 80px 50px;
            text-align: center;
        }

        .values-section h2 {
            font-size: 2.8rem;
            font-weight: 800;
            background: linear-gradient(to right, #fff, #ffd28a);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            margin-bottom: 15px;
        }

        .values-section > p {
            color: #fde68a;
            font-size: 1.1rem;
            margin-bottom: 50px;
        }

        .values-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 30px;
            max-width: 1200px;
            margin: 0 auto;
        }

        .value-card {
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(12px);
            border-radius: 15px;
            padding: 35px 25px;
            transition: all 0.4s ease;
            border: 1px solid rgba(255, 255, 255, 0.2);
        }

        .value-card:hover {
            transform: translateY(-10px);
            background: rgba(255, 255, 255, 0.15);
            box-shadow: 0 20px 60px rgba(255, 165, 0, 0.4);
        }

        .value-icon {
            font-size: 2.5rem;
            color: #ffd28a;
            margin-bottom: 20px;
        }

        .value-card h3 {
            font-size: 1.4rem;
            color: #fff;
            margin-bottom: 15px;
        }

        .value-card p {
            color: rgba(255, 255, 255, 0.9);
            font-size: 1rem;
            line-height: 1.6;
        }

        /* Journey Section */
        .journey-section {
            background: linear-gradient(135deg, #D46419 0%, #171B1F 95%);
            padding: 80px 50px;
        }

        .journey-section h2 {
            font-size: 2.8rem;
            font-weight: 800;
            background: linear-gradient(to right, #fff, #ffd28a);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            margin-bottom: 15px;
            text-align: center;
        }

        .journey-section > p {
            color: #fde68a;
            font-size: 1.1rem;
            margin-bottom: 60px;
            text-align: center;
        }

        .journey-timeline {
            max-width: 1000px;
            margin: 0 auto;
            position: relative;
            padding: 20px 0;
        }

        .journey-timeline::before {
            content: '';
            position: absolute;
            left: 50%;
            transform: translateX(-50%);
            width: 3px;
            height: 100%;
            background: linear-gradient(to bottom, #ffd28a, #D46419);
        }

        .timeline-item {
            display: flex;
            justify-content: center;
            align-items: center;
            margin-bottom: 60px;
            position: relative;
        }

        .timeline-date {
            background: linear-gradient(135deg, #D46419, #ff8c42);
            color: white;
            padding: 10px 20px;
            border-radius: 25px;
            font-weight: 700;
            position: absolute;
            top: 0;
            left: 50%;
            transform: translateX(-50%);
            z-index: 2;
            box-shadow: 0 4px 15px rgba(0,0,0,0.3);
        }

        .timeline-content {
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(12px);
            padding: 30px;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.3);
            width: calc(50% - 50px);
            margin-top: 40px;
            border: 1px solid rgba(255, 255, 255, 0.2);
        }

        .timeline-item:nth-child(odd) .timeline-content {
            margin-right: auto;
        }

        .timeline-item:nth-child(even) .timeline-content {
            margin-left: auto;
        }

        .timeline-content h3 {
            color: #fff;
            font-size: 1.6rem;
            margin-bottom: 15px;
        }

        .timeline-content p {
            color: #fde68a;
            line-height: 1.6;
            font-size: 1rem;
        }

        /* Team Section */
        .team-section {
            background: linear-gradient(150deg, #e67e22 17%, #171B1F 100%);
            padding: 80px 50px;
        }

        .team-section h2 {
            font-size: 2.8rem;
            font-weight: 800;
            background: linear-gradient(to right, #fff, #ffd28a);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            margin-bottom: 15px;
            text-align: center;
        }

        .team-section > p {
            color: #fde68a;
            font-size: 1.1rem;
            margin-bottom: 50px;
            text-align: center;
        }

        .team-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 30px;
            max-width: 1200px;
            margin: 0 auto;
        }

        .team-card {
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(12px);
            border-radius: 15px;
            overflow: hidden;
            transition: all 0.4s ease;
            border: 1px solid rgba(255, 255, 255, 0.2);
        }

        .team-card:hover {
            transform: translateY(-10px);
            box-shadow: 0 20px 60px rgba(255, 165, 0, 0.4);
        }

        .team-image {
            width: 100%;
            height: 250px;
            object-fit: cover;
        }

        .team-info {
            padding: 20px;
            text-align: center;
        }

        .team-name {
            font-size: 1.2rem;
            color: #fff;
            margin-bottom: 6px;
            font-weight: 700;
        }

        .team-role {
            color: #ffd28a;
            font-weight: 600;
            margin-bottom: 12px;
            font-size: 0.9rem;
        }

        .team-bio {
            color: rgba(255, 255, 255, 0.9);
            font-size: 0.85rem;
            line-height: 1.5;
            margin-bottom: 15px;
        }

        .social-links {
            display: flex;
            justify-content: center;
            gap: 12px;
        }

        .social-links a {
            color: #ffd28a;
            font-size: 1.1rem;
            transition: all 0.3s ease;
        }

        .social-links a:hover {
            color: #fff;
            transform: scale(1.2);
        }

        /* Responsive Design */
        @media (max-width: 1024px) {
            .story-content,
            .mission-grid {
                grid-template-columns: 1fr;
            }

            .stats-grid,
            .values-grid {
                grid-template-columns: repeat(2, 1fr);
            }

            .team-grid {
                grid-template-columns: repeat(2, 1fr);
            }
        }

        @media (max-width: 768px) {
            .about-hero h1 {
                font-size: 2.5rem;
            }

            .story-text h2,
            .stats-section h2,
            .values-section h2,
            .journey-section h2,
            .team-section h2 {
                font-size: 2rem;
            }

            .stats-grid,
            .values-grid,
            .team-grid {
                grid-template-columns: 1fr;
            }

            .journey-timeline::before {
                left: 20px;
            }

            .timeline-date {
                left: 20px;
                transform: none;
            }

            .timeline-content {
                width: calc(100% - 60px);
                margin-left: 40px !important;
            }

            .about-hero,
            .story-section,
            .stats-section,
            .mission-section,
            .values-section,
            .journey-section,
            .team-section {
                padding: 60px 20px;
            }
        }
    </style>
</head>
<body>
    <?php include '/xampp/htdocs/FixExpress/PWA/dist/includes/header.php'; ?>

    <!-- Hero Section -->
    <section class="about-hero">
        <h1>FixExpress</h1>
        <p>Connecting you with skilled professionals for all your service needs since 2025</p>
    </section>

    <!-- Our Story Section -->
    <section class="story-section">
        <div class="story-content">
            <div class="story-text">
                <h2>Our Story</h2>
                <p>Founded with a simple mission: to make quality repair services accessible to everyone. FixExpress was born from the frustration of finding reliable technicians for everyday repairs.</p>
                <p>We recognized that people needed a trusted platform where they could connect with verified, skilled professionals quickly and easily. Today, we're proud to serve thousands of satisfied customers across the region.</p>
            </div>
            <div class="story-image">
                <img src="./dist/assets/images/application/hero.jpg" alt="FixExpress Story">
            </div>
        </div>
    </section>

    <!-- Statistics Section -->
    <section class="stats-section">
        <h2>Our Impact</h2>
        <p>Numbers that reflect our commitment to service excellence</p>
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-icon">
                    <i class="fas fa-smile"></i>
                </div>
                <div class="stat-number">10,000+</div>
                <div class="stat-label">Happy Customers</div>
                <div class="stat-description">Satisfied clients who have experienced our quality service</div>
            </div>
            <div class="stat-card">
                <div class="stat-icon">
                    <i class="fas fa-user-tie"></i>
                </div>
                <div class="stat-number">500+</div>
                <div class="stat-label">Verified Professionals</div>
                <div class="stat-description">Expert service providers vetted for quality</div>
            </div>
            <div class="stat-card">
                <div class="stat-icon">
                    <i class="fas fa-star"></i>
                </div>
                <div class="stat-number">4.8/5</div>
                <div class="stat-label">Average Rating</div>
                <div class="stat-description">Based on thousands of customer reviews</div>
            </div>
            <div class="stat-card">
                <div class="stat-icon">
                    <i class="fas fa-hand-holding-dollar"></i>
                </div>
                <div class="stat-number">₱2M+</div>
                <div class="stat-label">Services Value</div>
                <div class="stat-description">Total value of services provided</div>
            </div>
        </div>
    </section>

    <!-- Mission & Vision Section -->
    <section class="mission-section">
        <div class="mission-grid">
            <div class="mission-item">
                <div class="mission-title">OUR MISSION</div>
                <h2 class="mission-heading">Empowering Service Excellence</h2>
                <p class="mission-text">
                    At FixExpress, our mission is to revolutionize the service industry by connecting skilled professionals with customers who need quality services. We strive to create a platform that ensures reliability, transparency, and satisfaction for both service providers and customers.
                </p>
            </div>
            <div class="mission-item">
                <div class="mission-title">OUR VISION</div>
                <h2 class="mission-heading">Leading Service Innovation</h2>
                <p class="mission-text">
                    To be the Philippines' leading platform for connecting service professionals with customers, creating opportunities for growth while maintaining the highest standards of service quality and customer satisfaction.
                </p>
            </div>
        </div>
    </section>

    <!-- Values Section -->
    <section class="values-section">
        <h2>Our Core Values</h2>
        <p>The principles that guide everything we do</p>
        <div class="values-grid">
            <div class="value-card">
                <div class="value-icon">
                    <i class="fas fa-handshake"></i>
                </div>
                <h3>Trust & Reliability</h3>
                <p>Building lasting relationships through consistent, dependable service delivery</p>
            </div>
            <div class="value-card">
                <div class="value-icon">
                    <i class="fas fa-gem"></i>
                </div>
                <h3>Excellence</h3>
                <p>Maintaining the highest standards in every service we facilitate</p>
            </div>
            <div class="value-card">
                <div class="value-icon">
                    <i class="fas fa-shield-alt"></i>
                </div>
                <h3>Safety & Security</h3>
                <p>Ensuring the wellbeing of our customers and service providers</p>
            </div>
            <div class="value-card">
                <div class="value-icon">
                    <i class="fas fa-heart"></i>
                </div>
                <h3>Customer First</h3>
                <p>Putting our customers' needs at the center of everything we do</p>
            </div>
        </div>
    </section>

    <!-- Journey Section -->
    <section class="journey-section">
        <h2>Our Journey</h2>
        <p>From idea to reality: The story of FixExpress</p>
        <div class="journey-timeline">
            <div class="timeline-item">
                <div class="timeline-date">2023</div>
                <div class="timeline-content">
                    <h3>The Beginning</h3>
                    <p>FixExpress was founded with a vision to transform how people access professional services in the Philippines.</p>
                </div>
            </div>
            <div class="timeline-item">
                <div class="timeline-date">2024</div>
                <div class="timeline-content">
                    <h3>Rapid Growth</h3>
                    <p>Expanded our network to over 500 verified professionals across multiple service categories.</p>
                </div>
            </div>
            <div class="timeline-item">
                <div class="timeline-date">2025</div>
                <div class="timeline-content">
                    <h3>Innovation & Excellence</h3>
                    <p>Launched new features and achieved milestone of 10,000+ satisfied customers.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Team Section -->
    <section class="team-section">
        <h2>Meet Our Team</h2>
        <p>The dedicated minds behind FixExpress working to bring you the best service experience</p>
        <div class="team-grid">
            <div class="team-card">
                <img src="./dist/assets/images/team/team1.jpg" alt="Zyrel Gabriel Maningding" class="team-image">
                <div class="team-info">
                    <h3 class="team-name">Zyrel Gabriel Maningding</h3>
                    <div class="team-role">UI/UX Designer & Frontend Developer</div>
                    <p class="team-bio">
                        Creative designer focused on creating intuitive and beautiful user experiences for our platform.
                    </p>
                    <div class="social-links">
                        <a href="https://www.facebook.com/zyrelgabriel.maningding" title="Facebook"><i class="fab fa-facebook"></i></a>
                        <a href="https://www.instagram.com/itsmezyyy.11/" title="Instagram"><i class="fab fa-instagram"></i></a>
                        <a href="https://github.com/gabgab112005" title="GitHub"><i class="fab fa-github"></i></a>
                    </div>
                </div>
            </div>
            <div class="team-card">
                <img src="./dist/assets/images/team/team2.jpg" alt="Charles Dinver Boy D. Gervacio" class="team-image">
                <div class="team-info">
                    <h3 class="team-name">Charles Dinver Boy D. Gervacio</h3>
                    <div class="team-role">Backend Developer</div>
                    <p class="team-bio">
                        Expert backend developer ensuring robust and scalable infrastructure for seamless service delivery.
                    </p>
                    <div class="social-links">
                        <a href="https://www.facebook.com/charles.gervacio.37/" title="Facebook"><i class="fab fa-facebook"></i></a>
                        <a href="https://www.instagram.com/woopiepoowie/" title="Instagram"><i class="fab fa-instagram"></i></a>
                        <a href="https://github.com/charlesgervacio1124" title="GitHub"><i class="fab fa-github"></i></a>
                    </div>
                </div>
            </div>
            <div class="team-card">
                <img src="./dist/assets/images/team/team3.jpg" alt="Team Member" class="team-image">
                <div class="team-info">
                    <h3 class="team-name">Loraine R. Gonzales</h3>
                    <div class="team-role">System Analyst</div>
                    <p class="team-bio">
                        Analyzes and improves system performance to enhance efficiency and reliability. Works closely with teams to align technical solutions with business goals.
                    </p>
                    <div class="social-links">
                        <a href="https://www.facebook.com/lorininin?mibextid=ZbWKwL" title="Facebook"><i class="fab fa-facebook"></i></a>
                        <a href="https://www.instagram.com/loriiinnnn?igsh=MWpsN291ODVjNHE3dQ==" title="Instagram"><i class="fab fa-instagram"></i></a>
                        <a href="https://github.com/loraine18" title="GitHub"><i class="fab fa-github"></i></a>
                    </div>
                </div>
            </div>
            <div class="team-card">
                <img src="./dist/assets/images/team/team3.jpg" alt="Team Member" class="team-image">
                <div class="team-info">
                    <h3 class="team-name">John Patrick B. Aquino</h3>
                    <div class="team-role">Project Manager</div>
                    <p class="team-bio">
                        Oversees projects from start to finish to ensure timely and successful delivery. Encourages collaboration and maintains clear communication across all teams.
                    </p>
                    <div class="social-links">
                        <a href="https://www.facebook.com/share/17gQKgU7w4/" title="Facebook"><i class="fab fa-facebook"></i></a>
                        <a href="https://www.instagram.com/dj.pat4?igsh=ZDY0azhzcHFmZGtl" title="Instagram"><i class="fab fa-instagram"></i></a>
                        <a href="https://github.com/dpatrick04" title="GitHub"><i class="fab fa-github"></i></a>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <?php include '/xampp/htdocs/FixExpress/PWA/dist/includes/footer.php'; ?>

</body>
</html>