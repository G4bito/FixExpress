<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$user_id = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : null;

if ($user_id) {
    // Database connection
    $host = "localhost";
    $user = "root";
    $pass = "";
    $dbname = "fixexpress";

    $conn = new mysqli($host, $user, $pass, $dbname);
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    // Fetch user data
    $stmt = $conn->prepare("SELECT first_name, last_name, username, email FROM users WHERE user_id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $user_data = $result->fetch_assoc();

    // Store in variables for easy access
    $first_name = $user_data['first_name'] ?? '';
    $last_name = $user_data['last_name'] ?? '';
    $username = $user_data['username'] ?? '';
    $email = $user_data['email'] ?? '';
    $stmt->close();
    $conn->close();
}
?>

<!-- Header -->
<header class="header">
    <a href="/FixExpress/PWA/index.php" class="logo">
        <div class="logo-icon">
            <img src="/FixExpress/PWA/dist/assets/images/logo.png" alt="FixExpress Logo">
        </div>
        <span>FixExpress</span>
    </a>
    <nav class="nav">
        <a href="#services">Services</a>
        <a href="#how-it-works">How It Works</a>
        <a href="#feedback">Feedback</a>
        <a href="#contact">Contact</a>
        <a href="/FixExpress/PWA/dist/admin/website_ratings.php">Website Ratings</a>
    </nav>
    <div class="auth-buttons">
        <?php if (isset($_SESSION['user_id'])): ?>
            <div class="profile-container">
                <div class="profile-icon" onclick="toggleDropdown()">
                    <?php echo strtoupper(substr($_SESSION['username'], 0, 1)); ?>
                </div>
                <div class="profile-dropdown" id="profileDropdown">
                    <p><strong><?php echo htmlspecialchars($_SESSION['username']); ?></strong></p>
                    <a href="#" id="viewProfile">View Profile</a>
                    <a href="/FixExpress/PWA/dist/user/my_bookings.php" id="viewProfile">My Bookings</a>
                    <a href="<?php echo '/FixExpress/PWA/logout.php'; ?>" class="block px-4 py-2 text-gray-700 hover:bg-gray-100">Logout</a>

                </div>
            </div>
        <?php else: ?>
            <button type="button"><a href="login.php">Login</a></button>
            <button onclick="window.location.href='signup.php'" class="get-started-btn">Get Started</button>
        <?php endif; ?>
    </div>
</header>

<!-- Profile Modal -->
<div id="profileModal" class="profile-modal">
    <div class="profile-content">
        <h2>Edit Profile</h2>
        <form id="profileUpdateForm">
            <div class="form-group">
                <label>Full Name</label>
                <input type="text" name="fullname" value="<?php echo htmlspecialchars(($first_name ?? '') . ' ' . ($last_name ?? '')); ?>" required>
            </div>

            <div class="form-group">
                <label>Username</label>
                <input type="text" name="username" value="<?php echo htmlspecialchars($username ?? ''); ?>" required>
            </div>

            <div class="form-group">
                <label>Email</label>
                <input type="email" name="email" value="<?php echo htmlspecialchars($email ?? ''); ?>" required>
            </div>

            <div class="form-group">
                <label>New Password (optional)</label>
                <input type="password" name="password" placeholder="Leave blank to keep current password">
            </div>

            <div class="profile-actions">
                <button type="submit" class="save-btn">Save Changes</button>
                <button type="button" class="close-btn" id="closeModalBtn">Cancel</button>
            </div>
        </form>
    </div>
</div>

<script>
function toggleDropdown() {
    document.getElementById("profileDropdown").classList.toggle("show");
}

window.onclick = function(e) {
    if (!e.target.closest(".profile-container")) {
        document.getElementById("profileDropdown").classList.remove("show");
    }
}

document.addEventListener("DOMContentLoaded", function() {
    const viewProfileBtn = document.getElementById("viewProfile");
    const profileModal = document.getElementById("profileModal");
    const closeModalBtn = document.getElementById("closeModalBtn");
    const profileUpdateForm = document.getElementById("profileUpdateForm");

    if(viewProfileBtn) {
        viewProfileBtn.addEventListener("click", function(e) {
            e.preventDefault();
            profileModal.style.display = "flex";
        });
    }

    if(closeModalBtn) {
        closeModalBtn.addEventListener("click", function() {
            profileModal.style.display = "none";
        });
    }

    if(profileUpdateForm) {
        profileUpdateForm.addEventListener("submit", function(e) {
            e.preventDefault();
            const formData = new FormData(this);

            fetch('/FixExpress/PWA/dist/database/update_user_profile.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if(data.status === 'success') {
                    alert('Profile updated successfully!');
                    location.reload(); // Reload to update displayed information
                } else {
                    alert(data.message || 'Failed to update profile');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('An error occurred while updating profile');
            });
        });
    }

    window.addEventListener("click", function(e) {
        if (e.target === profileModal) {
            profileModal.style.display = "none";
        }
    });
});
</script>