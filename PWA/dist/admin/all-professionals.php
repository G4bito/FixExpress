<?php
// --- Database connection ---
$server = "localhost";
$username = "root";
$password = "";
$dbname = "fixexpress";
$conn = new mysqli($server, $username, $password, $dbname);
if ($conn->connect_error) die("Connection failed: " . $conn->connect_error);

// Get all service categories
$categories = $conn->query("SELECT * FROM services ORDER BY service_name ASC");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>All Professionals</title>
    <style>
        body {
            margin: 0;
            font-family: 'Segoe UI', sans-serif;
            display: flex;
            height: 100vh;
            background: linear-gradient(135deg, #c15b00, #2b1b00);
            color: white;
            overflow: hidden;
        }

        /* Sidebar */
        .sidebar {
            width: 260px;
            background: rgba(0,0,0,0.25);
            display: flex;
            flex-direction: column;
            justify-content: flex-start;
            align-items: flex-start;
            padding: 60px 30px;
            border-right: 1px solid rgba(255,255,255,0.2);
            position: relative;
        }

        .category {
            margin: 10px 0;
            font-size: 1.1em;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .category:hover {
            transform: translateX(5px);
            color: #ffbf00;
        }

        .category.active {
            font-weight: bold;
            color: #ffbf00;
            text-decoration: underline;
        }

        /* Back and View All buttons */
        .top-buttons {
            position: absolute;
            top: 20px;
            left: 20px;
            display: flex;
            flex-direction: column;
            gap: 10px;
        }

        .btn {
            background: rgba(255,255,255,0.15);
            border: 1px solid rgba(255,255,255,0.3);
            color: white;
            padding: 10px 18px;
            border-radius: 8px;
            text-decoration: none;
            font-weight: bold;
            font-size: 0.9em;
            transition: all 0.3s ease;
            cursor: pointer;
            text-align: center;
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }

        .btn:hover {
            background: rgba(255,255,255,0.3);
            transform: scale(1.05);
        }

        /* SVG icon style */
        .icon {
            width: 18px;
            height: 18px;
            display: inline-block;
            vertical-align: middle;
            fill: white;
            flex: 0 0 18px;
        }

        /* Main content */
        .main-content {
            flex-grow: 1;
            padding: 40px;
            overflow-y: auto;
            position: relative;
        }

        .category-title {
            font-size: 1.8em;
            margin-bottom: 20px;
            border-bottom: 2px solid rgba(255,255,255,0.2);
            padding-bottom: 10px;
            opacity: 0;
            transform: translateY(-10px);
            transition: all 0.5s ease;
        }

        .category-title.visible {
            opacity: 1;
            transform: translateY(0);
        }

        /* Professionals grid */
        .grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(260px, 1fr));
            gap: 20px;
            opacity: 0;
            transform: translateY(20px);
            transition: all 0.5s ease;
        }

        .grid.visible {
            opacity: 1;
            transform: translateY(0);
        }

        .card {
            background: rgba(255,255,255,0.1);
            border-radius: 12px;
            padding: 20px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.3);
            transition: all 0.3s ease;
        }

        .card:hover {
            transform: translateY(-6px) scale(1.02);
            background: rgba(255,255,255,0.15);
        }

        .name {
            font-size: 1.2em;
            font-weight: bold;
            margin-bottom: 8px;
        }

        .info {
            color: #ddd;
            font-size: 0.9em;
            margin-bottom: 5px;
        }

        .rating {
            color: #ffbf00;
            font-weight: bold;
        }

        /* Scrollbar styling */
        ::-webkit-scrollbar {
            width: 10px;
        }
        ::-webkit-scrollbar-thumb {
            background: rgba(255,255,255,0.2);
            border-radius: 10px;
        }
        ::-webkit-scrollbar-thumb:hover {
            background: rgba(255,255,255,0.4);
        }
    </style>
</head>
<body>
    <div class="sidebar">
        <div class="top-buttons">
            <a href="/FixExpress/PWA/index.php" class="btn">

                <!-- Left arrow SVG -->
                <svg class="icon" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg" aria-hidden="true" focusable="false">
                    <path d="M15.41 7.41L14 6l-6 6 6 6 1.41-1.41L10.83 12z"/>
                </svg>
                Back
            </a>
            <div class="btn" id="viewAll">
                <!-- Group / users SVG -->
                <svg class="icon" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg" aria-hidden="true" focusable="false">
                    <path d="M16 11c1.66 0 2.99-1.34 2.99-3S17.66 5 16 5s-3 1.34-3 3 1.34 3 3 3zm-8 0c1.66 0 2.99-1.34 2.99-3S9.66 5 8 5 5 6.34 5 8s1.34 3 3 3zm0 2c-2.33 0-7 1.17-7 3.5V19h14v-2.5C15 14.17 10.33 13 8 13zm8 0c-.29 0-.62.02-.97.05 1.16.84 1.97 1.97 1.97 3.45V19h6v-2.5C23 14.17 18.33 13 16 13z"/>
                </svg>
                View All Professionals
            </div>
        </div>

        <div style="margin-top: 100px;">
            <?php while ($row = $categories->fetch_assoc()): ?>
                <div class="category" data-id="<?= $row['service_id']; ?>">
                    <?= htmlspecialchars($row['service_name']); ?>
                </div>
            <?php endwhile; ?>
        </div>
    </div>

    <div class="main-content">
        <div class="category-title">All Professionals</div>
        <div class="grid visible" id="professionalGrid">
            <!-- Professionals will appear here -->
        </div>
    </div>

<script>
const categories = document.querySelectorAll('.category');
const grid = document.getElementById('professionalGrid');
const title = document.querySelector('.category-title');
const viewAllBtn = document.getElementById('viewAll');

// Load all professionals initially
document.addEventListener('DOMContentLoaded', () => {
    loadProfessionals('all');
});

// Load all professionals button
viewAllBtn.addEventListener('click', () => {
    categories.forEach(c => c.classList.remove('active'));
    title.classList.remove('visible');
    setTimeout(() => {
        title.textContent = "All Professionals";
        title.classList.add('visible');
    }, 200);
    loadProfessionals('all');
});

// Category click behavior
categories.forEach(cat => {
    cat.addEventListener('click', () => {
        categories.forEach(c => c.classList.remove('active'));
        cat.classList.add('active');

        const categoryId = cat.getAttribute('data-id');
        const categoryName = cat.textContent;

        title.classList.remove('visible');
        setTimeout(() => {
            title.textContent = categoryName;
            title.classList.add('visible');
        }, 200);

        loadProfessionals(categoryId);
    });
});

// Fetch and show professionals
function loadProfessionals(serviceId) {
    grid.classList.remove('visible');
    fetch(`get_professionals.php?service_id=${serviceId}`)
        .then(res => res.text())
        .then(data => {
            setTimeout(() => {
                grid.innerHTML = data;
                grid.classList.add('visible');
            }, 300);
        });
}
</script>
</body>
</html>
