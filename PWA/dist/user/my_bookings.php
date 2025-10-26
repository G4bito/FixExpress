<?php
session_start();

// Redirect if not logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: ../../login.php');
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
        w.address as worker_address,
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
        .glass-card {
            background: rgba(255, 255, 255, 0.7);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.2);
        }

        .booking-card {
            transition: all 0.3s ease;
        }

        .booking-card:hover {
            transform: translateY(-5px);
        }

        .star {
            cursor: pointer;
            transition: color 0.2s ease;
        }

        .star:hover,
        .star.active {
            color: #fbbf24;
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
            border-bottom: 3px solid #f97316;
            color: #f97316;
        }
        
    </style>
</head>
<body class="bg-gradient-to-br from-orange-50 to-orange-100 min-h-screen">
    <?php include '../includes/header.php'; ?>

    <!-- Page Header with Navigation -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <div class="flex justify-between items-center mb-6">
            <div class="flex items-center space-x-4">
                <a href="/FixExpress/PWA/index.php" class="inline-flex items-center px-4 py-2 bg-orange-500 text-white rounded-lg hover:bg-orange-600 transition-colors shadow-sm">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" viewBox="0 0 20 20" fill="currentColor">
                        <path d="M10.707 2.293a1 1 0 00-1.414 0l-7 7a1 1 0 001.414 1.414L4 10.414V17a1 1 0 001 1h2a1 1 0 001-1v-2a1 1 0 011-1h2a1 1 0 011 1v2a1 1 0 001 1h2a1 1 0 001-1v-6.586l.293.293a1 1 0 001.414-1.414l-7-7z" />
                    </svg>
                    Home
                </a>
                <h1 class="text-2xl font-bold text-gray-800">My Bookings</h1>
            </div>
        </div>

        <!-- Search Bar -->
        <div class="mb-6">
            <div class="relative">
                <input type="text" id="searchInput" 
                    class="w-full pl-10 pr-4 py-2 rounded-lg border border-gray-300 focus:ring-2 focus:ring-orange-500 focus:border-transparent"
                    placeholder="Search by service name or status...">
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                    <i class="fas fa-search text-gray-400"></i>
                </div>
            </div>
        </div>

        <!-- Tabs -->
        <div class="flex space-x-4 border-b border-gray-200 mb-6">
            <button class="tab-button py-2 px-4 text-gray-600 hover:text-orange-600 transition-colors tab-active" data-tab="all">
                All Bookings
            </button>
            <button class="tab-button py-2 px-4 text-gray-600 hover:text-orange-600 transition-colors" data-tab="recent">
                Pending
            </button>
            <button class="tab-button py-2 px-4 text-gray-600 hover:text-orange-600 transition-colors" data-tab="current">
                Current
            </button>
            <button class="tab-button py-2 px-4 text-gray-600 hover:text-orange-600 transition-colors" data-tab="completed">
                Completed
            </button>
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
                                            <div class="flex">
                                                <?php
                                                $rating = floatval($booking['worker_rating']);
                                                $ratingColor = 'text-gray-300'; // Default color for 0 rating
                                                if ($rating >= 4.0) {
                                                    $ratingColor = 'text-green-500'; // Green for high ratings
                                                } elseif ($rating >= 2.5) {
                                                    $ratingColor = 'text-yellow-400'; // Yellow for medium ratings
                                                } elseif ($rating > 0) {
                                                    $ratingColor = 'text-red-500'; // Red for low ratings
                                                }

                                                for ($i = 1; $i <= 5; $i++) {
                                                    $starClass = $i <= round($rating) ? 'fas ' . $ratingColor : 'far text-gray-300';
                                                    echo '<i class="' . $starClass . ' fa-star text-sm"></i>';
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

        <!-- Price Section -->
        <?php if (isset($booking['price']) && (float)$booking['price'] > 0): ?>
            <div class="mt-4 pt-4 border-t border-gray-200">
                <div class="flex justify-between items-center">
                    <span class="text-gray-600 font-medium">Estimated Price:</span>
                    <span class="text-xl font-bold text-orange-600">₱<?= htmlspecialchars(number_format($booking['price'], 2)) ?></span>
                </div>
            </div>
        <?php endif; ?>

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
                        <div class="mt-6 flex space-x-2">
                            <button onclick='openDetailsModal(<?= json_encode($booking, JSON_HEX_APOS | JSON_HEX_QUOT) ?>)' 
                                    class="flex-1 bg-white border border-orange-500 text-orange-500 rounded-md py-2 hover:bg-orange-50 transition-colors">
                                View Details
                            </button>
                            <?php if (in_array($booking['status'], ['Pending', 'Approved'])): ?>
                                <button onclick="openCancelModal(<?= $booking['booking_id'] ?>)" class="flex-1 bg-orange-400 text-white rounded-md py-2 hover:bg-orange-500 transition-colors">
                                    Cancel
                                </button>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endforeach; ?>
        </div>
    </div>

    <!-- Details Modal -->
    <div id="detailsModal" class="fixed inset-0 bg-black bg-opacity-50 hidden flex items-center justify-center z-50">
        <div class="bg-white rounded-lg p-6 w-full max-w-2xl mx-4 transform transition-all">
            <div class="flex justify-between items-start mb-4">
                <h2 class="text-2xl font-bold text-gray-800" id="modalServiceName"></h2>
                <button onclick="closeDetailsModal()" class="text-gray-500 hover:text-gray-700">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            
            <div class="space-y-4">
                <!-- Status -->
                <div class="flex items-center">
                    <span class="font-medium text-gray-700 w-32">Status:</span>
                    <span id="modalStatus" class="px-3 py-1 rounded-full text-sm font-medium"></span>
                </div>

                <!-- Date and Time -->
                <div class="flex items-center">
                    <span class="font-medium text-gray-700 w-32">Date & Time:</span>
                    <span id="modalDateTime" class="text-gray-600"></span>
                </div>

                <!-- Service Location -->
                <div class="flex items-start">
                    <span class="font-medium text-gray-700 w-32">Your Address:</span>
                    <span id="modalLocation" class="text-gray-600 flex-1"></span>
                </div>

                <!-- Notes -->
                <div>
                    <span class="font-medium text-gray-700 block mb-1">Problem: </span>
                    <p id="modalNotes" class="text-gray-600 bg-gray-50 p-3 rounded"></p>
                    <div id="modalProblemMedia" class="mt-4"></div>
                </div>

                <!-- Uploaded Media -->
                <div id="modalMediaContainer" class="hidden">
                    <h3 class="font-medium text-gray-700 mb-2">Uploaded Media</h3>
                    <div id="modalMediaContent"></div>
                </div>

                <!-- Professional Info -->
                <div class="border-t pt-4 mt-4">
                    <h3 class="font-medium text-gray-700 mb-2">Professional Information</h3>
                    <div id="modalProfessionalInfo" class="space-y-2"></div>
                </div>

                <!-- Price -->
                <div class="border-t pt-4 mt-4">
                    <div class="flex justify-between items-center">
                        <span class="font-medium text-gray-700">Total Price:</span>
                        <span id="modalPrice" class="text-xl font-bold text-gray-900"></span>
                    </div>
                </div>
            </div>

            <div class="mt-6 flex justify-end">
                <button onclick="closeDetailsModal()" 
                        class="bg-gray-100 text-gray-700 px-4 py-2 rounded-md hover:bg-gray-200 transition-colors">
                    Close
                </button>
            </div>
        </div>
    </div>

    <!-- Cancel Booking Modal -->
    <div id="cancelModal" class="fixed inset-0 bg-black bg-opacity-50 hidden flex items-center justify-center z-50">
        <div class="bg-white rounded-lg p-6 w-full max-w-md mx-4 transform transition-all">
            <h2 class="text-2xl font-bold text-gray-800 mb-4">Cancel Booking</h2>
            <p class="text-gray-600 mb-4">Are you sure you want to cancel this booking? This action cannot be undone.</p>
            
            <form id="cancelForm">
                <input type="hidden" id="cancelBookingId" name="booking_id">
                <div class="mb-4">
                    <label for="cancellationReason" class="block text-sm font-medium text-gray-700 mb-1">Reason for Cancellation (Optional)</label>
                    <textarea id="cancellationReason" name="reason" rows="3" class="w-full p-2 border rounded-md" placeholder="e.g., Found another service, schedule conflict..."></textarea>
                </div>

                <div class="flex justify-end space-x-3">
                    <button type="button" onclick="closeCancelModal()" class="bg-gray-200 text-gray-700 px-4 py-2 rounded-md hover:bg-gray-300">
                        Nevermind
                    </button>
                    <button type="submit" class="bg-red-500 text-white px-4 py-2 rounded-md hover:bg-red-600">
                        Confirm Cancellation
                    </button>
                </div>
            </form>
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

            // Modal functionality
            window.openDetailsModal = function(booking) {
                const modal = document.getElementById('detailsModal');
                const statusColors = {
                    'Pending': 'bg-yellow-100 text-yellow-800',
                    'Approved': 'bg-blue-100 text-blue-800',
                    'Completed': 'bg-green-100 text-green-800',
                    'Cancelled': 'bg-red-100 text-red-800'
                };

                // Set modal content
                document.getElementById('modalServiceName').textContent = booking.service_name;
                
                const statusElement = document.getElementById('modalStatus');
                statusElement.textContent = booking.status;
                statusElement.className = `px-3 py-1 rounded-full text-sm font-medium ${statusColors[booking.status] || 'bg-gray-100 text-gray-800'}`;
                
                document.getElementById('modalDateTime').textContent = `${new Date(booking.date).toLocaleDateString('en-US', { 
                    weekday: 'long',
                    year: 'numeric', 
                    month: 'long', 
                    day: 'numeric'
                })} at ${new Date(booking.date + ' ' + booking.time).toLocaleTimeString('en-US', {
                    hour: 'numeric',
                    minute: '2-digit'
                })}`;
                
                document.getElementById('modalLocation').textContent = booking.address || 'Not specified';
                document.getElementById('modalNotes').textContent = booking.notes || 'No additional notes';

                // Handle problem media
                const mediaContainer = document.getElementById('modalProblemMedia');
                mediaContainer.innerHTML = ''; // Clear previous media
                if (booking.problem_image_path) {
                    const mediaPath = `../../${booking.problem_image_path}`;
                    const isVideo = ['mp4', 'webm', 'mov'].includes(mediaPath.split('.').pop().toLowerCase());
                    if (isVideo) {
                        mediaContainer.innerHTML = `<video src="${mediaPath}" controls style="max-width: 100%; border-radius: 8px; margin-top: 10px;"></video>`;
                    } else {
                        mediaContainer.innerHTML = `<img src="${mediaPath}" alt="Problem Image" style="max-width: 100%; border-radius: 8px; margin-top: 10px;">`;
                    }
                }
                
                // Professional info
                const professionalInfo = document.getElementById('modalProfessionalInfo');
                if (booking.worker_id) {
                    let professionalContent = '';
                    if (booking.worker_first_name) {
                        professionalContent += `
                            <p class="text-gray-800">
                                <span class="font-medium">Name:</span> 
                                ${booking.worker_first_name} ${booking.worker_last_name}
                            </p>`;
                    }
                    if (booking.worker_contact) {
                        professionalContent += `
                            <p class="text-gray-800">
                                <span class="font-medium">Contact:</span> 
                                ${booking.worker_contact}
                            </p>`;
                    }
                    if (booking.worker_rating) {
                        const rating = parseFloat(booking.worker_rating);
                        let ratingColor = 'text-gray-300';
                        if (rating >= 4.0) ratingColor = 'text-green-500';
                        else if (rating >= 2.5) ratingColor = 'text-yellow-400';
                        else if (rating > 0) ratingColor = 'text-red-500';

                        professionalContent += `
                            <div class="flex items-center">
                                <span class="font-medium text-gray-800 mr-2">Rating:</span>
                                <div class="flex items-center">
                                    <div class="flex mr-1">`;
                        
                        for (let i = 1; i <= 5; i++) {
                            professionalContent += `<i class="${i <= Math.round(rating) ? 'fas' : 'far'} fa-star ${ratingColor}"></i>`;
                        }
                        
                        professionalContent += `
                                    </div>
                                    <span class="text-sm text-gray-500">(${booking.rating_count} reviews)</span>
                                </div>
                            </div>`;
                    }
                    professionalInfo.innerHTML = professionalContent;
                } else {
                    professionalInfo.innerHTML = '<p class="text-gray-500">No professional assigned yet</p>';
                }

                const priceValue = parseFloat(booking.price);
                document.getElementById('modalPrice').textContent = (priceValue > 0)
                    ? new Intl.NumberFormat('en-PH', { style: 'currency', currency: 'PHP' }).format(priceValue)
                    : 'Not yet set by professional';

                // Show modal
                modal.classList.remove('hidden');
                document.body.style.overflow = 'hidden';
            }

            window.closeDetailsModal = function() {
                const modal = document.getElementById('detailsModal');
                modal.classList.add('hidden');
                document.body.style.overflow = '';
            }

            // Close modal when clicking outside
            const modal = document.getElementById('detailsModal');
            modal.addEventListener('click', function(e) {
                if (e.target === modal) {
                    closeDetailsModal();
                }
            });

            // Cancel Modal functionality
            const cancelModal = document.getElementById('cancelModal');
            const cancelForm = document.getElementById('cancelForm');
            const cancelBookingIdInput = document.getElementById('cancelBookingId');

            window.openCancelModal = function(bookingId) {
                cancelBookingIdInput.value = bookingId;
                cancelModal.classList.remove('hidden');
                document.body.style.overflow = 'hidden';
            }

            window.closeCancelModal = function() {
                cancelModal.classList.add('hidden');
                document.body.style.overflow = '';
                cancelForm.reset();
            }

            cancelModal.addEventListener('click', function(e) {
                if (e.target === cancelModal) {
                    closeCancelModal();
                }
            });

            cancelForm.addEventListener('submit', async function(e) {
                e.preventDefault();
                const bookingId = cancelBookingIdInput.value;
                const reason = document.getElementById('cancellationReason').value;

                try {
                    console.log('Sending request with:', { booking_id: parseInt(bookingId), reason });
                    
                    const response = await fetch('cancel_booking.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'Accept': 'application/json'
                        },
                        body: JSON.stringify({
                            booking_id: parseInt(bookingId),
                            reason: reason
                        })
                    });

                    const text = await response.text(); // Get raw response text
                    console.log('Raw response:', text);
                    
                    let result;
                    try {
                        result = JSON.parse(text);
                    } catch (e) {
                        console.error('JSON parse error:', e);
                        throw new Error('Server response was not valid JSON: ' + text);
                    }

                    if (response.ok && result.success) {
                        alert(result.message);
                        location.reload();
                    } else {
                        throw new Error(result.message || 'Failed to cancel booking');
                    }
                } catch (error) {
                    console.error('Error details:', error);
                    alert('Error: ' + error.message);
                } finally {
                    closeCancelModal();
                }
            });
        });
    </script>
</body>
</html>