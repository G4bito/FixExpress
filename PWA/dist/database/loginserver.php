<?php
// --- Database Connection ---
$server = "localhost";
$username = "root";
$password = "";
$dbname = "fixexpress";

$conn = new mysqli($server, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

error_reporting(E_ALL);
ini_set('display_errors', 1);

// --- CLIENT SIGNUP PROCESS ---
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['signup'])) {
    $first = trim($_POST['first_name']);                                         
    $last = trim($_POST['last_name']);
    $user = trim($_POST['username']);
    $email = trim($_POST['email']);
    $pass = $_POST['password'];
    $confirm = $_POST['confirmPassword'];

    if ($pass !== $confirm) {
        echo "<script>alert('Passwords do not match!');</script>";
    } else {
        $hashed = password_hash($pass, PASSWORD_DEFAULT);

        $stmt = $conn->prepare("INSERT INTO users (first_name, last_name, username, email, password) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param("sssss", $first, $last, $user, $email, $hashed);

        if ($stmt->execute()) {
            echo "<script>alert('Signup successful! You can now log in.'); window.location.href='login.php';</script>";
        } else {
            echo "<script>alert('Error: Username or email already exists.');</script>";
        }

        $stmt->close();
    }
}

// --- WORKER SIGNUP PROCESS ---
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['signup_worker'])) {
    $first = trim($_POST['first_name']);
    $last = trim($_POST['last_name']);
    $user = trim($_POST['username']);
    $email = trim($_POST['email']);
    $contact = trim($_POST['contact']);
    $address = trim($_POST['address']);
    $service_id = intval($_POST['service_id']);
    $experience = trim($_POST['bio']);
    $rating = 0;
    $pass = $_POST['password'];
    $confirm = $_POST['confirmPassword'];

    if ($pass !== $confirm) {
        echo "<script>alert('Passwords do not match!');</script>";
    } else {
        $hashed = password_hash($pass, PASSWORD_DEFAULT);

        $check = $conn->prepare("SELECT * FROM workers WHERE username = ? OR email = ?");
        $check->bind_param("ss", $user, $email);
        $check->execute();
        $check->store_result();

        if ($check->num_rows > 0) {
            echo "<script>alert('Username or email already exists!');</script>";
        } else {
            // ✅ New workers default to Pending status
            $stmt = $conn->prepare("
                INSERT INTO workers (first_name, last_name, username, contact, email, address, service_id, experience, rating, password, status)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'Pending')
            ");
            $stmt->bind_param("ssssssisds", $first, $last, $user, $contact, $email, $address, $service_id, $experience, $rating, $hashed);

            if ($stmt->execute()) {
                echo "<script>alert('Worker application submitted! Please wait for admin approval.'); window.location.href='login.php';</script>";
            } else {
                echo "<script>alert('Error submitting worker application.');</script>";
            }
            $stmt->close();
        }
        $check->close();
    }
}

// --- LOGIN PROCESS ---
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['login'])) {
    $user = trim($_POST['username']);
    $pass = $_POST['password'];

    // Try login as user first
    $stmt = $conn->prepare("SELECT user_id, username, password FROM users WHERE username = ?");
    $stmt->bind_param("s", $user);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows === 1) {
        $stmt->bind_result($user_id, $username, $hashed);
        $stmt->fetch();

        if (password_verify($pass, $hashed)) {
            session_start();
            $_SESSION['user_id'] = $user_id;
            $_SESSION['username'] = $username;
            echo "<script>alert('Login successful!'); window.location.href='index.php';</script>";
            exit();
        }
    }

    // Try login as worker
    $stmt_worker = $conn->prepare("SELECT worker_id, username, password, status FROM workers WHERE username = ?");
    $stmt_worker->bind_param("s", $user);
    $stmt_worker->execute();
    $stmt_worker->store_result();

    if ($stmt_worker->num_rows === 1) {
        $stmt_worker->bind_result($worker_id, $username, $hashed, $status);
        $stmt_worker->fetch();

        if (password_verify($pass, $hashed)) {
            if ($status === 'Pending') {
                echo "<script>alert('Your account is waiting for admin approval.');</script>";
            } elseif ($status === 'Rejected') {
                echo "<script>alert('Your application was rejected.');</script>";
            } elseif ($status === 'Approved') {
                session_start();
                $_SESSION['worker_id'] = $worker_id;
                $_SESSION['username'] = $username;
                echo "<script>alert('Welcome, worker!'); window.location.href='workers.php';</script>";
                exit();
            }
        } else {
            echo "<script>alert('Incorrect password!');</script>";
        }
    } else {
        echo "<script>alert('User not found!');</script>";
    }

    $stmt_worker->close();
    $stmt->close();
}
?>
