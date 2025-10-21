<?php
session_start();

// Redirect if not logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: ../login.php');
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

$user_id = $_SESSION['user_id'];

// Fetch complete user data
$userQuery = "SELECT email, first_name, last_name, username FROM users WHERE user_id = ?";
$userStmt = $conn->prepare($userQuery);
$userStmt->bind_param("i", $user_id);
$userStmt->execute();
$userResult = $userStmt->get_result();
$userData = $userResult->fetch_assoc();
$userEmail = $userData['email'];
$first_name = $userData['first_name'];
$last_name = $userData['last_name'];
$username = $userData['username'];
$userStmt->close();
// Fetch user's bookings
$query = "
    SELECT 
        b.*, 
        s.service_name,
        w.first_name as worker_first_name,
        w.last_name as worker_last_name,
        w.contact as worker_contact,
        w.worker_id,
        (SELECT AVG(rating) FROM worker_ratings WHERE worker_id = w.worker_id) as worker_rating,
        (SELECT COUNT(*) FROM worker_ratings WHERE worker_id = w.worker_id) as rating_count,
        ur.rating as user_rating,
        ur.comment as user_comment,
        ur.rating_id as user_rating_id
    FROM bookings b
    LEFT JOIN services s ON b.service_id = s.service_id
    LEFT JOIN workers w ON b.worker_id = w.worker_id
    LEFT JOIN worker_ratings ur ON b.booking_id = ur.booking_id AND ur.user_id = ?
    WHERE b.user_id = ?
    ORDER BY b.date DESC, b.time DESC
";

$stmt = $conn->prepare($query);
$stmt->bind_param("ii", $user_id, $user_id);
$stmt->execute();
$result = $stmt->get_result();

$bookings = [];
while ($row = $result->fetch_assoc()) {
    $status = $row['status'];
    if ($status == 'Completed') {
        $category = 'completed';
    } elseif ($status == 'Pending') {
        $category = 'recent';
    } else {
        $category = 'current';
    }
    $bookings[$category][] = $row;
}
?>

<!doctype html>
<html lang="en" data-pc-preset="preset-1" data-pc-sidebar-caption="true" data-pc-direction="ltr" dir="ltr" data-pc-theme="light">
<head>
    <title>My Bookings - FixExpress</title>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=0, minimal-ui" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="description" content="My bookings page for FixExpress" />
    <meta name="keywords" content="bookings, services, maintenance" />
    <meta name="author" content="Sniper 2025" />
    <link rel="stylesheet" href="../assets/css/index.css" />
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        :root {
            --primary-color: #d97f3e;
            --secondary-color: #151010;
        }

        .glass-card {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(217, 127, 62, 0.1);
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.05);
        }

        .booking-card {
            transition: all 0.3s ease;
        }

        .booking-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 15px rgba(217, 127, 62, 0.1);
        }

        .star {
            cursor: pointer;
            transition: all 0.2s ease;
        }

        .star:hover,
        .star.active {
            color: var(--primary-color);
            transform: scale(1.1);
        }

        @keyframes slideUp {
            from {
                opacity: 0;
                transform: translateY(20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .animate-slide-up {
            animation: slideUp 0.5s ease forwards;
        }

        .tab-active {
            background: linear-gradient(to right, var(--primary-color), var(--secondary-color));
            color: white !important;
        }

        .tab-button {
            position: relative;
            overflow: hidden;
        }

        .tab-button::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 0;
            width: 100%;
            height: 2px;
            background: linear-gradient(to right, var(--primary-color), var(--secondary-color));
            transform: scaleX(0);
            transition: transform 0.3s ease;
        }

        .tab-button:hover::after {
            transform: scaleX(1);
        }
        
    </style>
</head>
<body class="bg-gradient-to-br from-orange-50 to-orange-100 min-h-screen">
    <?php include '../includes/header.php'; ?>

    <!-- Main Content Container -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <!-- Page Header with Navigation -->
        <div class="flex flex-col sm:flex-row justify-between items-center mb-8 gap-4">
            <div class="flex items-center gap-4">
                <a href="/FixExpress/PWA/index.php" class="inline-flex items-center px-4 py-2 bg-gradient-to-r from-[#d97f3e] to-[#151010] text-white rounded-lg shadow hover:opacity-90 transition-opacity">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" viewBox="0 0 20 20" fill="currentColor">
                        <path d="M10.707 2.293a1 1 0 00-1.414 0l-7 7a1 1 0 001.414 1.414L4 10.414V17a1 1 0 001 1h2a1 1 0 001-1v-2a1 1 0 011-1h2a1 1 0 011 1v2a1 1 0 001 1h2a1 1 0 001-1v-6.586l.293.293a1 1 0 001.414-1.414l-7-7z" />
                    </svg>
                    Home
                </a>
                <h1 class="text-3xl font-bold text-gray-800">My Bookings</h1>
            </div>
        </div>

        <!-- Tabs Navigation -->
        <div class="mb-8 bg-white rounded-lg shadow-sm p-1">
            <div class="flex flex-wrap justify-center sm:justify-start gap-2">
                <button class="tab-button px-6 py-3 text-gray-600 hover:text-[#d97f3e] transition-colors rounded-md tab-active" data-tab="all">
                    <i class="fas fa-list-ul mr-2"></i>All Bookings
                </button>
                <button class="tab-button px-6 py-3 text-gray-600 hover:text-[#d97f3e] transition-colors rounded-md" data-tab="recent">
                    <i class="fas fa-clock mr-2"></i>Recent
                </button>
                <button class="tab-button px-6 py-3 text-gray-600 hover:text-[#d97f3e] transition-colors rounded-md" data-tab="current">
                    <i class="fas fa-spinner mr-2"></i>Current
                </button>
                <button class="tab-button px-6 py-3 text-gray-600 hover:text-[#d97f3e] transition-colors rounded-md" data-tab="completed">
                    <i class="fas fa-check-circle mr-2"></i>Completed
                </button>
            </div>
        </div>

        <!-- Bookings Grid -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            <?php foreach ($bookings as $category => $categoryBookings): ?>
                <?php foreach ($categoryBookings as $index => $booking): ?>
                    <div class="booking-card glass-card rounded-xl p-6 shadow-lg animate-slide-up"
                         data-category="<?= $category ?>"
                         style="animation-delay: <?= $index * 0.1 ?>s">
                        <!-- Service Info -->
                        <div class="flex justify-between items-start mb-4">
                            <h3 class="text-xl font-semibold text-gray-800">
                                <?= htmlspecialchars($booking['service_name']) ?>
                            </h3>
                            <?php
                            $statusColors = [
                                'Pending' => 'bg-yellow-100 text-yellow-800',
                                'Approved' => 'bg-blue-100 text-blue-800',
                                'Completed' => 'bg-green-100 text-green-800',
                                'Cancelled' => 'bg-red-100 text-red-800'
                            ];
                            $statusColor = $statusColors[$booking['status']] ?? 'bg-gray-100 text-gray-800';
                            ?>
                            <span class="px-3 py-1 rounded-full text-sm font-medium <?= $statusColor ?>">
                                <?= htmlspecialchars($booking['status']) ?>
                            </span>
                        </div>

                        <!-- Date & Time -->
                        <div class="flex items-center text-gray-600 mb-4">
                            <i class="far fa-calendar mr-2"></i>
                            <?= date('F j, Y', strtotime($booking['date'])) ?>
                            <i class="far fa-clock ml-4 mr-2"></i>
                            <?= date('g:i A', strtotime($booking['time'])) ?>
                        </div>

                        <!-- Worker Info -->
                        <div class="flex items-center mb-4">
                            <div class="flex-shrink-0">
                                <div class="h-10 w-10 rounded-full bg-orange-100 flex items-center justify-center">
                                    <i class="fas fa-user text-orange-600"></i>
                                </div>
                            </div>
                            <div class="ml-3">
                                <?php if (!empty($booking['worker_id'])): ?>
                                    <?php if (!empty($booking['worker_first_name'])): ?>
                                        <p class="text-sm font-medium text-gray-900">
                                            <?= htmlspecialchars($booking['worker_first_name'] . ' ' . $booking['worker_last_name']) ?>
                                            <?php if (!empty($booking['worker_contact'])): ?>
                                                <span class="text-sm text-gray-500"> | <?= htmlspecialchars($booking['worker_contact']) ?></span>
                                            <?php endif; ?>
                                        </p>
                                        <div class="flex items-center">
                                            <div class="flex text-yellow-400">
                                                <?php
                                                $rating = round($booking['worker_rating']);
                                                for ($i = 1; $i <= 5; $i++) {
                                                    echo '<i class="' . ($i <= $rating ? 'fas' : 'far') . ' fa-star text-sm"></i>';
                                                }
                                                ?>
                                            </div>
                                            <span class="ml-1 text-sm text-gray-500">
                                                (<?= $booking['rating_count'] ?> reviews)
                                            </span>
                                        </div>
                                    <?php else: ?>
                                        <p class="text-sm text-gray-500">Professional assigned (ID: <?= $booking['worker_id'] ?>)</p>
                                    <?php endif; ?>
                                <?php else: ?>
                                    <p class="text-sm text-gray-500">No professional assigned yet</p>
                                <?php endif; ?>
                            </div>
                        </div>

                        <!-- Rating Section for Completed Bookings -->
                        <?php if ($booking['status'] == 'Completed'): ?>
                            <div class="mt-4 pt-4 border-t border-gray-200">
                                <?php if ($booking['user_rating_id']): ?>
                                    <!-- Display existing rating -->
                                    <div class="flex flex-col">
                                        <div class="flex text-yellow-400 mb-2">
                                            <?php
                                            $userRating = $booking['user_rating'];
                                            for ($i = 1; $i <= 5; $i++) {
                                                echo '<i class="fas fa-star ' . ($i <= $userRating ? 'text-yellow-400' : 'text-gray-300') . '"></i>';
                                            }
                                            ?>
                                        </div>
                                        <?php if ($booking['user_comment']): ?>
                                            <p class="text-gray-600 text-sm italic">
                                                "<?= htmlspecialchars($booking['user_comment']) ?>"
                                            </p>
                                        <?php endif; ?>
                                    </div>
                                <?php else: ?>
                                    <!-- Rating form -->
                                    <form class="rating-form" data-booking-id="<?= $booking['booking_id'] ?>">
                                        <div class="flex items-center mb-2">
                                            <?php for ($i = 1; $i <= 5; $i++): ?>
                                                <i class="star far fa-star text-2xl text-gray-300 hover:text-yellow-400 cursor-pointer mr-1"
                                                   data-rating="<?= $i ?>"></i>
                                            <?php endfor; ?>
                                        </div>
                                        <textarea class="w-full p-2 border rounded-md text-sm"
                                                  placeholder="Leave a comment (optional)"
                                                  rows="2"></textarea>
                                        <button type="submit" 
                                                class="mt-2 w-full bg-orange-500 text-white rounded-md py-2 hover:bg-orange-600 transition-colors">
                                            Submit Rating
                                        </button>
                                    </form>
                                <?php endif; ?>
                            </div>
                        <?php endif; ?>

                        <!-- View Details Button -->
                        <button class="mt-4 w-full bg-white border border-orange-500 text-orange-500 rounded-md py-2 hover:bg-orange-50 transition-colors">
                            View Details
                        </button>
                    </div>
                <?php endforeach; ?>
            <?php endforeach; ?>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Tab functionality
            const tabs = document.querySelectorAll('.tab-button');
            const bookingCards = document.querySelectorAll('.booking-card');

            tabs.forEach(tab => {
                tab.addEventListener('click', () => {
                    // Remove active class from all tabs
                    tabs.forEach(t => t.classList.remove('tab-active'));
                    // Add active class to clicked tab
                    tab.classList.add('tab-active');

                    const category = tab.dataset.tab;
                    bookingCards.forEach(card => {
                        if (category === 'all' || card.dataset.category === category) {
                            card.style.display = 'block';
                        } else {
                            card.style.display = 'none';
                        }
                    });
                });
            });

            // Search functionality
            const searchInput = document.getElementById('searchInput');
            searchInput.addEventListener('input', () => {
                const searchTerm = searchInput.value.toLowerCase();
                bookingCards.forEach(card => {
                    const serviceName = card.querySelector('h3').textContent.toLowerCase();
                    const status = card.querySelector('.rounded-full').textContent.toLowerCase();
                    if (serviceName.includes(searchTerm) || status.includes(searchTerm)) {
                        card.style.display = 'block';
                    } else {
                        card.style.display = 'none';
                    }
                });
            });

            // Rating functionality
            const ratingForms = document.querySelectorAll('.rating-form');
            ratingForms.forEach(form => {
                const stars = form.querySelectorAll('.star');
                let selectedRating = 0;

                stars.forEach(star => {
                    star.addEventListener('click', () => {
                        selectedRating = star.dataset.rating;
                        stars.forEach(s => {
                            if (s.dataset.rating <= selectedRating) {
                                s.classList.remove('far');
                                s.classList.add('fas', 'text-yellow-400');
                            } else {
                                s.classList.remove('fas', 'text-yellow-400');
                                s.classList.add('far');
                            }
                        });
                    });
                });

                form.addEventListener('submit', async (e) => {
                    e.preventDefault();
                    if (!selectedRating) {
                        alert('Please select a rating');
                        return;
                    }

                    const bookingId = form.dataset.bookingId;
                    const comment = form.querySelector('textarea').value;

                    try {
                        const response = await fetch('submit_rating.php', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                            },
                            body: JSON.stringify({
                                booking_id: bookingId,
                                rating: selectedRating,
                                comment: comment
                            })
                        });

                        if (response.ok) {
                            location.reload();
                        } else {
                            throw new Error('Failed to submit rating');
                        }
                    } catch (error) {
                        alert('Error submitting rating: ' + error.message);
                    }
                });
            });
        });
    </script>
</body>
</html>