<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

include '../database/server.php';

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $rating = $_POST['rating'];
    $comment = mysqli_real_escape_string($conn, $_POST['comment']);
    $name = mysqli_real_escape_string($conn, $_POST['name']);
    $date = date('Y-m-d H:i:s');

    $sql = "INSERT INTO website_ratings (rating, comment, reviewer_name, date_submitted) 
            VALUES ('$rating', '$comment', '$name', '$date')";
    
    if ($conn->query($sql) === TRUE) {
        $success = true;
    } else {
        $error = "Error: " . $sql . "<br>" . $conn->error;
    }
}

// Fetch existing ratings
$sql = "SELECT * FROM website_ratings ORDER BY date_submitted DESC";
$result = $conn->query($sql);

// Calculate average rating
$avgSql = "SELECT AVG(rating) as average_rating FROM website_ratings";
$avgResult = $conn->query($avgSql);
$avgRow = $avgResult->fetch_assoc();
$averageRating = $avgRow['average_rating'] ? round($avgRow['average_rating'], 1) : 0;
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Website Ratings | FixExpress</title>
    <link href="https://fonts.googleapis.com/css2?family=Open+Sans:wght@400;600&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary-color: #f39c12;
            --primary-hover: #e67e22;
            --text-dark: #2c2c2c;
            --text-medium: #555;
            --text-light: #666;
            --bg-light: #fffdfa;
            --shadow-light: rgba(0, 0, 0, 0.1);
            --shadow-medium: rgba(0, 0, 0, 0.25);
        }

        body {
            font-family: 'Open Sans', sans-serif;
            background: linear-gradient(145deg, #2d1e0b, #a54e07, #ff7b00);
            margin: 0;
            padding: 0;
            min-height: 100vh;
            color: var(--text-dark);
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
            z-index: 1000;
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

        .container {
            max-width: 900px;
            margin: 80px auto 40px;
            padding: 20px;
        }

        .rating-card {
            background: rgba(255, 255, 255, 0.95);
            border-radius: 20px;
            padding: 30px;
            box-shadow: 0 8px 30px rgba(0,0,0,0.2);
            margin-bottom: 30px;
            backdrop-filter: blur(10px);
        }

        .rating-header {
            text-align: center;
            margin-bottom: 30px;
        }

        .rating-header h1 {
            color: var(--text-dark);
            font-size: 28px;
            margin-bottom: 10px;
        }

        .average-rating {
            font-size: 48px;
            font-weight: bold;
            color: var(--primary-color);
            margin: 10px 0;
        }

        .stars {
            font-size: 24px;
            color: #FFD700;
            margin: 10px 0;
        }

        /* Rating Form */
        .rating-form {
            max-width: 600px;
            margin: 0 auto;
            text-align: center;
        }

        .star-rating {
            display: inline-flex;
            flex-direction: row-reverse;
            gap: 5px;
            margin: 20px 0;
        }

        .star-rating input {
            display: none;
        }

        .star-rating label {
            font-size: 40px;
            color: #ddd;
            cursor: pointer;
            transition: color 0.2s;
        }

        .star-rating label:hover,
        .star-rating label:hover ~ label,
        .star-rating input:checked ~ label {
            color: #FFD700;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-group label {
            display: block;
            margin-bottom: 8px;
            color: var(--text-dark);
            font-weight: 600;
        }

        .form-group input,
        .form-group textarea {
            width: 100%;
            padding: 12px;
            border: 1px solid #ddd;
            border-radius: 8px;
            font-size: 16px;
            transition: border-color 0.3s;
            box-sizing: border-box;
        }

        .form-group textarea {
            height: 120px;
            resize: vertical;
        }

        .form-group input:focus,
        .form-group textarea:focus {
            border-color: var(--primary-color);
            outline: none;
        }

        .submit-btn {
            background: var(--primary-color);
            color: white;
            border: none;
            padding: 12px 30px;
            border-radius: 8px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
        }

        .submit-btn:hover {
            background: var(--primary-hover);
            transform: translateY(-2px);
        }

        /* Existing Ratings */
        .ratings-list {
            margin-top: 40px;
        }

        .rating-item {
            background: rgba(255, 255, 255, 0.8);
            border-radius: 12px;
            padding: 20px;
            margin-bottom: 15px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }

        .rating-item .name {
            font-weight: 600;
            color: var(--text-dark);
            margin-bottom: 5px;
        }

        .rating-item .date {
            font-size: 0.9em;
            color: var(--text-light);
            margin-bottom: 10px;
        }

        .rating-item .stars {
            font-size: 18px;
            margin-bottom: 10px;
        }

        .rating-item .comment {
            color: var(--text-medium);
            line-height: 1.5;
        }

        .success-message {
            background: #4CAF50;
            color: white;
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
            text-align: center;
        }

        .error-message {
            background: #f44336;
            color: white;
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
            text-align: center;
        }

        @media (max-width: 768px) {
            .container {
                margin: 60px auto 20px;
                padding: 15px;
            }

            .rating-card {
                padding: 20px;
            }

            .star-rating label {
                font-size: 32px;
            }

            .average-rating {
                font-size: 36px;
            }
        }
    </style>
</head>
<body>
    <!-- Back Button -->
    <a href="../../index.php" class="back-btn">
        <svg viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
            <path d="M15.41 7.41L14 6l-6 6 6 6 1.41-1.41L10.83 12z"/>
        </svg>
        Back
    </a>

    <div class="container">
        <div class="rating-card">
            <div class="rating-header">
                <h1>Website Feedback</h1>
                <?php if ($result->num_rows > 0): ?>
                    <div class="average-rating"><?php echo $averageRating; ?></div>
                    <div class="stars">
                        <?php
                        $fullStars = floor($averageRating);
                        $halfStar = $averageRating - $fullStars >= 0.5;
                        
                        for ($i = 1; $i <= 5; $i++) {
                            if ($i <= $fullStars) {
                                echo "★";
                            } elseif ($i == $fullStars + 1 && $halfStar) {
                                echo "☆";
                            } else {
                                echo "☆";
                            }
                        }
                        ?>
                    </div>
                    <p>Based on <?php echo $result->num_rows; ?> ratings</p>
                <?php else: ?>
                    <div class="average-rating">-</div>
                    <div class="stars">☆☆☆☆☆</div>
                    <p>No ratings yet</p>
                <?php endif; ?>
            </div>

            <?php if (isset($success)): ?>
                <div class="success-message">Thank you for your feedback!</div>
            <?php endif; ?>

            <?php if (isset($error)): ?>
                <div class="error-message"><?php echo $error; ?></div>
            <?php endif; ?>

            <form class="rating-form" method="POST">
                <div class="star-rating">
                    <input type="radio" name="rating" value="5" id="star5" required>
                    <label for="star5">★</label>
                    <input type="radio" name="rating" value="4" id="star4">
                    <label for="star4">★</label>
                    <input type="radio" name="rating" value="3" id="star3">
                    <label for="star3">★</label>
                    <input type="radio" name="rating" value="2" id="star2">
                    <label for="star2">★</label>
                    <input type="radio" name="rating" value="1" id="star1">
                    <label for="star1">★</label>
                </div>

                <div class="form-group">
                    <label for="name">Your Name</label>
                    <input type="text" id="name" name="name" required>
                </div>

                <div class="form-group">
                    <label for="comment">Your Feedback</label>
                    <textarea id="comment" name="comment" required></textarea>
                </div>

                <button type="submit" class="submit-btn">Submit Rating</button>
            </form>

            <div class="ratings-list">
                <h2>Recent Feedback</h2>
                <?php
                if ($result->num_rows > 0) {
                    while($row = $result->fetch_assoc()) {
                        echo '<div class="rating-item">';
                        echo '<div class="name">' . htmlspecialchars($row['reviewer_name']) . '</div>';
                        echo '<div class="date">' . date('F j, Y', strtotime($row['date_submitted'])) . '</div>';
                        echo '<div class="stars">';
                        for ($i = 1; $i <= 5; $i++) {
                            echo $i <= $row['rating'] ? "★" : "☆";
                        }
                        echo '</div>';
                        echo '<div class="comment">' . htmlspecialchars($row['comment']) . '</div>';
                        echo '</div>';
                    }
                } else {
                    echo '<p>No ratings yet. Be the first to provide feedback!</p>';
                }
                ?>
            </div>
        </div>
    </div>

    <script>
        // Prevent form resubmission on page refresh
        if (window.history.replaceState) {
            window.history.replaceState(null, null, window.location.href);
        }
    </script>
</body>
</html>
