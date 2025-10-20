<?php
include '../database/server.php';
include 'includes/admin_auth.php';

// Fetch bookings with service names
$result = $conn->query("SELECT b.*, s.service_name FROM bookings b LEFT JOIN services s ON b.service_id = s.service_id ORDER BY b.booking_id DESC");

// Fetch workers (include service name)
$workersResult = $conn->query("SELECT w.*, s.service_name FROM workers w LEFT JOIN services s ON w.service_id = s.service_id ORDER BY w.worker_id DESC");

// Fetch users
$usersResult = $conn->query("SELECT * FROM users ORDER BY user_id DESC");

// Calculate statistics
$totalBookings = $conn->query("SELECT COUNT(*) as count FROM bookings")->fetch_assoc()['count'];
$totalWorkers = $conn->query("SELECT COUNT(*) as count FROM workers")->fetch_assoc()['count'];
$totalUsers = $conn->query("SELECT COUNT(*) as count FROM users")->fetch_assoc()['count'];
$pendingBookings = $conn->query("SELECT COUNT(*) as count FROM bookings WHERE status = 'Pending'")->fetch_assoc()['count'];
$pendingWorkers = $conn->query("SELECT COUNT(*) as count FROM workers WHERE status = 'Pending'")->fetch_assoc()['count'];
$approvedWorkers = $conn->query("SELECT COUNT(*) as count FROM workers WHERE status = 'Approved'")->fetch_assoc()['count'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Manage Bookings & Workers</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<style>
    :root {
        --primary-orange: #C8631E;
        --dark-brown: #3E2415;
        --medium-brown: #6B4423;
        --light-brown: #8B5E34;
        --cream: #F5E6D3;
        --hover-orange: #D97235;
    }

    body {
        background: linear-gradient(135deg, var(--dark-brown) 0%, var(--medium-brown) 100%);
        min-height: 100vh;
        font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    }

    .header-section {
        background: rgba(255, 255, 255, 0.1);
        backdrop-filter: blur(10px);
        border-radius: 15px;
        padding: 25px;
        margin-bottom: 30px;
        border: 1px solid rgba(255, 255, 255, 0.2);
    }

    .header-section h3 {
        color: var(--cream);
        font-weight: 600;
        margin: 0;
    }

    .btn-custom-primary {
        background: var(--primary-orange);
        color: white;
        border: none;
        border-radius: 8px;
        padding: 8px 20px;
        font-weight: 500;
        transition: all 0.3s ease;
    }

    .btn-custom-primary:hover {
        background: var(--hover-orange);
        transform: translateY(-2px);
        box-shadow: 0 5px 15px rgba(200, 99, 30, 0.4);
        color: white;
    }

    .btn-custom-secondary {
        background: rgba(255, 255, 255, 0.2);
        color: white;
        border: 1px solid rgba(255, 255, 255, 0.3);
        border-radius: 8px;
        padding: 8px 20px;
        font-weight: 500;
        transition: all 0.3s ease;
    }

    .btn-custom-secondary:hover {
        background: rgba(255, 255, 255, 0.3);
        color: white;
    }

    .btn-custom-danger {
        background: #dc3545;
        color: white;
        border: none;
        border-radius: 8px;
        padding: 8px 20px;
        font-weight: 500;
        transition: all 0.3s ease;
    }

    .btn-custom-danger:hover {
        background: #c82333;
        transform: translateY(-2px);
        box-shadow: 0 5px 15px rgba(220, 53, 69, 0.4);
    }

    /* Statistics Cards */
    .stats-container {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
        gap: 20px;
        margin-bottom: 30px;
    }

    .stat-card {
        background: rgba(255, 255, 255, 0.95);
        backdrop-filter: blur(10px);
        border-radius: 15px;
        padding: 25px;
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.3);
        transition: all 0.3s ease;
        border: 2px solid transparent;
    }
    .stat-icon {
        width: 60px;
        height: 60px;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 28px;
        margin-bottom: 15px;
    }

    .stat-icon.orange {
        background: linear-gradient(135deg, var(--primary-orange), var(--hover-orange));
        color: white;
    }

    .stat-icon.blue {
        background: linear-gradient(135deg, #17a2b8, #138496);
        color: white;
    }

    .stat-icon.green {
        background: linear-gradient(135deg, #28a745, #218838);
        color: white;
    }

    .stat-icon.purple {
        background: linear-gradient(135deg, #6f42c1, #5a32a3);
        color: white;
    }

    .stat-icon.yellow {
        background: linear-gradient(135deg, #ffc107, #e0a800);
        color: white;
    }

    .stat-icon.red {
        background: linear-gradient(135deg, #dc3545, #c82333);
        color: white;
    }

    .stat-label {
        font-size: 0.9rem;
        color: #6c757d;
        font-weight: 500;
        margin-bottom: 5px;
    }

    .stat-value {
        font-size: 2rem;
        font-weight: 700;
        color: var(--dark-brown);
        margin: 0;
    }

    .stat-description {
        font-size: 0.85rem;
        color: #6c757d;
        margin-top: 5px;
    }

    .content-card {
        background: rgba(255, 255, 255, 0.95);
        backdrop-filter: blur(10px);
        border-radius: 15px;
        overflow: hidden;
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.3);
        margin-bottom: 30px;
    }

    .card-header-custom {
        background: linear-gradient(135deg, var(--primary-orange) 0%, var(--hover-orange) 100%);
        color: white;
        padding: 20px 25px;
        font-size: 1.4rem;
        font-weight: 600;
    }

    .table {
        margin: 0;
    }

    .table thead {
        background: var(--dark-brown);
        color: white;
    }

    .table thead th {
        border: none;
        padding: 15px;
        font-weight: 600;
    }

    .table tbody tr {
        transition: all 0.3s ease;
    }


    .table tbody td {
        padding: 15px;
        vertical-align: middle;
    }

    .badge {
        padding: 8px 15px;
        border-radius: 20px;
        font-weight: 500;
        font-size: 0.85rem;
    }

    .btn-table-action {
        padding: 6px 15px;
        border-radius: 6px;
        font-size: 0.875rem;
        margin: 2px;
        transition: all 0.3s ease;
    }

    .btn-table-action:hover {
        transform: translateY(-2px);
        box-shadow: 0 3px 10px rgba(0, 0, 0, 0.2);
    }

    .btn-edit {
        background: #17a2b8;
        color: white;
        border: none;
    }

    .btn-edit:hover {
        background: #138496;
        color: white;
    }

    .btn-delete {
        background: #dc3545;
        color: white;
        border: none;
    }

    .btn-delete:hover {
        background: #c82333;
        color: white;
    }

    .btn-approve {
        background: #28a745;
        color: white;
        border: none;
    }

    .btn-approve:hover {
        background: #218838;
        color: white;
    }

    .btn-reject {
        background: #dc3545;
        color: white;
        border: none;
    }

    .btn-reject:hover {
        background: #c82333;
        color: white;
    }

    .empty-state {
        padding: 40px;
        text-align: center;
        color: #6c757d;
        font-size: 1.1rem;
    }

    .table-responsive {
        border-radius: 0 0 15px 15px;
    }

    /* Custom scrollbar */
    .table-responsive::-webkit-scrollbar {
        height: 8px;
    }

    .table-responsive::-webkit-scrollbar-track {
        background: #f1f1f1;
    }

    .table-responsive::-webkit-scrollbar-thumb {
        background: var(--primary-orange);
        border-radius: 4px;
    }

    /* Increase container width */
    @media (min-width: 1200px) {
        .container {
            max-width: 1800px;
        }
    }

    /* Ensure buttons stay horizontal */
    .btn-group-horizontal {
        display: flex;
        gap: 8px;
        flex-wrap: nowrap;
    }

    /* Ensure table cells have enough width */
    .table td {
        min-width: 100px;
        white-space: nowrap;
    }

    /* Action buttons column should not wrap */
    .action-buttons {
        min-width: 200px;
        white-space: nowrap;
    }
</style>
</head>
<body>

<div class="container py-4">
    <!-- Header Section -->
    <div class="header-section">
        <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
            <h3>📋 Admin Dashboard</h3>
            <div class="d-flex gap-2 flex-wrap">
                <!--<a href="index.php" class="btn btn-custom-secondary btn-sm">← Back to Dashboard</a>-->
                <button class="btn btn-custom-primary btn-sm" data-bs-toggle="modal" data-bs-target="#addBookingModal">+ Add Booking</button>
                <a href="logout_admin.php" class="btn btn-custom-danger btn-sm">Logout</a>
            </div>
        </div>
    </div>

    <!-- Statistics Cards -->
    <div class="stats-container">
        <div class="stat-card">
            <div class="stat-icon blue">
                👥
            </div>
            <div class="stat-label">Total Users</div>
            <h2 class="stat-value"><?= number_format($totalUsers) ?></h2>
            <div class="stat-description">Registered users on the platform</div>
        </div>

        <div class="stat-card">
            <div class="stat-icon orange">
                📅
            </div>
            <div class="stat-label">Total Bookings</div>
            <h2 class="stat-value"><?= number_format($totalBookings) ?></h2>
            <div class="stat-description">All booking requests</div>
        </div>

        <div class="stat-card">
            <div class="stat-icon yellow">
                ⏳
            </div>
            <div class="stat-label">Pending Bookings</div>
            <h2 class="stat-value"><?= number_format($pendingBookings) ?></h2>
            <div class="stat-description">Awaiting approval</div>
        </div>

        <div class="stat-card">
            <div class="stat-icon purple">
                👷
            </div>
            <div class="stat-label">Total Workers</div>
            <h2 class="stat-value"><?= number_format($totalWorkers) ?></h2>
            <div class="stat-description">All registered workers</div>
        </div>

        <div class="stat-card">
            <div class="stat-icon green">
                ✅
            </div>
            <div class="stat-label">Approved Workers</div>
            <h2 class="stat-value"><?= number_format($approvedWorkers) ?></h2>
            <div class="stat-description">Active workers on platform</div>
        </div>

        <div class="stat-card">
            <div class="stat-icon red">
                ⏰
            </div>
            <div class="stat-label">Pending Workers</div>
            <h2 class="stat-value"><?= number_format($pendingWorkers) ?></h2>
            <div class="stat-description">Awaiting approval</div>
        </div>
    </div>

    <!-- Bookings Section -->
    <div class="content-card">
        <div class="card-header-custom">
            📅 Manage Bookings
        </div>
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Full Name</th>
                        <th>Contact</th>
                        <th>Email</th>
                        <th>Address</th>
                        <th>Date</th>
                        <th>Time</th>
                        <th>Service</th>
                        <th>Status</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($result && $result->num_rows > 0): ?>
                        <?php while ($row = $result->fetch_assoc()): ?>
                            <tr>
                                <td><strong>#<?= $row['booking_id'] ?></strong></td>
                                <td><?= htmlspecialchars($row['fullname']) ?></td>
                                <td><?= htmlspecialchars($row['contact']) ?></td>
                                <td><?= htmlspecialchars($row['email']) ?></td>
                                <td><?= htmlspecialchars($row['address']) ?></td>
                                <td><?= htmlspecialchars($row['date']) ?></td>
                                <td><?= htmlspecialchars($row['time']) ?></td>
                                <td><?= htmlspecialchars($row['service_name'] ?? 'Unknown Service') ?></td>
                                <td>
                                    <?php
                                    $statusClass = match($row['status']) {
                                        'Pending' => 'warning',
                                        'Approved' => 'info',
                                        'Completed' => 'success',
                                        'Cancelled' => 'danger',
                                        default => 'secondary'
                                    };
                                    ?>
                                    <span class="badge bg-<?= $statusClass ?>">
                                        <?= htmlspecialchars($row['status']) ?>
                                    </span>
                                </td>
                                <td class="action-buttons">
                                    <div class="btn-group-horizontal">
                                        <button class="btn btn-table-action btn-edit"
                                            data-bs-toggle="modal"
                                            data-bs-target="#editBookingModal"
                                            data-id="<?= $row['booking_id'] ?>"
                                            data-fullname="<?= htmlspecialchars($row['fullname']) ?>"
                                            data-contact="<?= htmlspecialchars($row['contact']) ?>"
                                            data-email="<?= htmlspecialchars($row['email']) ?>"
                                            data-address="<?= htmlspecialchars($row['address']) ?>"
                                            data-date="<?= htmlspecialchars($row['date']) ?>"
                                            data-time="<?= htmlspecialchars($row['time']) ?>"
                                            data-status="<?= htmlspecialchars($row['status']) ?>">
                                            Edit
                                        </button>
                                        <button class="btn btn-table-action btn-delete"
                                            data-bs-toggle="modal"
                                            data-bs-target="#deleteBookingModal"
                                            data-id="<?= $row['booking_id'] ?>">
                                            Delete
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr><td colspan="9" class="empty-state">No bookings found.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Users Section -->
    <div class="content-card">
        <div class="card-header-custom">
            👥 Manage Users
        </div>
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Full Name</th>
                        <th>Username</th>
                        <th>Email</th>
                        <th>Password</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($usersResult && $usersResult->num_rows > 0): ?>
                        <?php while ($user = $usersResult->fetch_assoc()): ?>
                            <tr>
                                <td><strong>#<?= $user['user_id'] ?></strong></td>
                                <td><?= htmlspecialchars($user['first_name'] . ' ' . $user['last_name']) ?></td>
                                <td><?= htmlspecialchars($user['username']) ?></td>
                                <td><?= htmlspecialchars($user['email']) ?></td>
                                <td><?= htmlspecialchars($user['password']) ?></td>
                                <td class="action-buttons">
                                    <div class="btn-group-horizontal">
                                        <button class="btn btn-table-action btn-edit"
                                            data-bs-toggle="modal"
                                            data-bs-target="#editUserModal"
                                            data-id="<?= $user['user_id'] ?>"
                                            data-firstname="<?= htmlspecialchars($user['first_name']) ?>"
                                            data-lastname="<?= htmlspecialchars($user['last_name']) ?>"
                                            data-email="<?= htmlspecialchars($user['email']) ?>"
                                            data-username="<?= htmlspecialchars($user['username']) ?>">
                                            Edit
                                        </button>
                                        <button class="btn btn-table-action btn-delete"
                                            data-bs-toggle="modal"
                                            data-bs-target="#deleteUserModal"
                                            data-id="<?= $user['user_id'] ?>">
                                            Delete
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr><td colspan="8" class="empty-state">No users found.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Workers Section -->
    <div class="content-card">
        <div class="card-header-custom">
            👷 Manage Workers
        </div>
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Full Name</th>
                        <th>Contact</th>
                        <th>Email</th>
                        <th>Address</th>
                        <th>Service</th>
                        <th>Experience</th>
                        <th>Status</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($workersResult && $workersResult->num_rows > 0): ?>
                        <?php while ($worker = $workersResult->fetch_assoc()): ?>
                            <tr>
                                <td><strong>#<?= $worker['worker_id'] ?></strong></td>
                                <td><?= htmlspecialchars($worker['first_name'] . ' ' . $worker['last_name']) ?></td>
                                <td><?= htmlspecialchars($worker['contact']) ?></td>
                                <td><?= htmlspecialchars($worker['email']) ?></td>
                                <td><?= htmlspecialchars($worker['address']) ?></td>
                                <td><?= htmlspecialchars($worker['service_name'] ?? 'Unknown Service') ?></td>
                                <td><?= htmlspecialchars($worker['experience']) ?></td>
                                <td>
                                    <?php
                                    $statusClass = match($worker['status'] ?? 'Pending') {
                                        'Pending' => 'warning',
                                        'Approved' => 'success',
                                        'Rejected' => 'danger',
                                        default => 'secondary'
                                    };
                                    ?>
                                    <span class="badge bg-<?= $statusClass ?>">
                                        <?= htmlspecialchars($worker['status'] ?? 'Pending') ?>
                                    </span>
                                </td>
                                <td class="action-buttons">
                                    <div class="btn-group-horizontal">
                                        <button class="btn btn-table-action btn-edit"
                                            data-bs-toggle="modal"
                                            data-bs-target="#editWorkerModal"
                                            data-id="<?= $worker['worker_id'] ?>"
                                            data-firstname="<?= htmlspecialchars($worker['first_name']) ?>"
                                            data-lastname="<?= htmlspecialchars($worker['last_name']) ?>"
                                            data-contact="<?= htmlspecialchars($worker['contact']) ?>"
                                            data-email="<?= htmlspecialchars($worker['email']) ?>"
                                            data-address="<?= htmlspecialchars($worker['address']) ?>"
                                            data-service-id="<?= $worker['service_id'] ?>"
                                            data-experience="<?= htmlspecialchars($worker['experience']) ?>"
                                            data-status="<?= htmlspecialchars($worker['status'] ?? 'Pending') ?>">
                                            Edit
                                        </button>
                                        <button class="btn btn-table-action btn-delete"
                                            data-bs-toggle="modal"
                                            data-bs-target="#deleteWorkerModal"
                                            data-id="<?= $worker['worker_id'] ?>">
                                            Delete
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr><td colspan="9" class="empty-state">No workers found.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Edit Booking Modal -->
<div class="modal fade" id="editBookingModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Edit Booking</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="editBookingForm" action="update_booking.php" method="POST">
                    <input type="hidden" name="booking_id" id="editBookingID">

                    <div class="mb-3">
                        <label class="form-label">Full Name</label>
                        <input type="text" class="form-control" name="fullname" id="editBookingFullName" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Contact</label>
                        <input type="text" class="form-control" name="contact" id="editBookingContact" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Email</label>
                        <input type="email" class="form-control" name="email" id="editBookingEmail" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Address</label>
                        <input type="text" class="form-control" name="address" id="editBookingAddress" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Date</label>
                        <input type="date" class="form-control" name="date" id="editBookingDate" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Time</label>
                        <input type="time" class="form-control" name="time" id="editBookingTime" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Service</label>
                        <select class="form-select" name="service_id" id="editBookingService" required>
                            <?php
                            $services = $conn->query("SELECT * FROM services ORDER BY service_name");
                            while($service = $services->fetch_assoc()) {
                                echo "<option value='{$service['service_id']}'>{$service['service_name']}</option>";
                            }
                            ?>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Status</label>
                        <select class="form-select" name="status" id="editBookingStatus" required>
                            <option value="Pending">Pending</option>
                            <option value="Approved">Approved</option>
                            <option value="Completed">Completed</option>
                            <option value="Cancelled">Cancelled</option>
                        </select>
                    </div>

                    <div class="text-end">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Update Booking</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Edit User Modal -->
<div class="modal fade" id="editUserModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Edit User</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="editUserForm" action="update_user.php" method="POST">
                    <input type="hidden" name="user_id" id="editUserID">
                    
                    <div class="mb-3">
                        <label class="form-label">First Name</label>
                        <input type="text" class="form-control" name="first_name" id="editUserFirstName" maxlength="100">
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Last Name</label>
                        <input type="text" class="form-control" name="last_name" id="editUserLastName" maxlength="100">
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Username</label>
                        <input type="text" class="form-control" name="username" id="editUserUsername" maxlength="100">
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Email</label>
                        <input type="email" class="form-control" name="email" id="editUserEmail" required maxlength="100">
                    </div>

                    <div class="mb-3">
                        <label class="form-label">New Password (leave blank to keep current)</label>
                        <input type="password" class="form-control" name="password" id="editUserPassword" maxlength="100">
                    </div>

                    <div class="text-end">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Update User</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Edit Worker Modal -->
<div class="modal fade" id="editWorkerModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Edit Worker</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="editWorkerForm" action="update_worker.php" method="POST">
                    <input type="hidden" name="worker_id" id="editWorkerID">
                    
                    <div class="mb-3">
                        <label class="form-label">First Name</label>
                        <input type="text" class="form-control" name="first_name" id="editWorkerFirstName" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Last Name</label>
                        <input type="text" class="form-control" name="last_name" id="editWorkerLastName" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Contact</label>
                        <input type="text" class="form-control" name="contact" id="editWorkerContact" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Email</label>
                        <input type="email" class="form-control" name="email" id="editWorkerEmail" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Address</label>
                        <input type="text" class="form-control" name="address" id="editWorkerAddress" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Service</label>
                        <select class="form-select" name="service_id" id="editWorkerService" required>
                            <?php
                            $services = $conn->query("SELECT * FROM services ORDER BY service_name");
                            while($service = $services->fetch_assoc()) {
                                echo "<option value='{$service['service_id']}'>{$service['service_name']}</option>";
                            }
                            ?>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Experience</label>
                        <input type="text" class="form-control" name="experience" id="editWorkerExperience" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Status</label>
                        <select class="form-select" name="status" id="editWorkerStatus" required>
                            <option value="Pending">Pending</option>
                            <option value="Approved">Approved</option>
                            <option value="Rejected">Rejected</option>
                        </select>
                    </div>

                    <div class="text-end">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Update Worker</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script type="text/javascript">
document.addEventListener('DOMContentLoaded', function() {
    // Initialize edit booking modal
    const editBookingModal = document.getElementById('editBookingModal');
    if (editBookingModal) {
        editBookingModal.addEventListener('show.bs.modal', function(event) {
            const button = event.relatedTarget;
            const bookingId = button.getAttribute('data-id');
            const fullname = button.getAttribute('data-fullname');
            const contact = button.getAttribute('data-contact');
            const email = button.getAttribute('data-email');
            const address = button.getAttribute('data-address');
            const date = button.getAttribute('data-date');
            const time = button.getAttribute('data-time');
            const status = button.getAttribute('data-status');
            const serviceId = button.getAttribute('data-service-id');
            
            const modalForm = this.querySelector('#editBookingForm');
            modalForm.querySelector('#editBookingID').value = bookingId;
            modalForm.querySelector('#editBookingFullName').value = fullname || '';
            modalForm.querySelector('#editBookingContact').value = contact || '';
            modalForm.querySelector('#editBookingEmail').value = email || '';
            modalForm.querySelector('#editBookingAddress').value = address || '';
            modalForm.querySelector('#editBookingDate').value = date || '';
            modalForm.querySelector('#editBookingTime').value = time || '';
            modalForm.querySelector('#editBookingStatus').value = status || 'Pending';
            if (serviceId) modalForm.querySelector('#editBookingService').value = serviceId;
        });
    }

    // Initialize edit user modal
    const editUserModal = document.getElementById('editUserModal');
    if (editUserModal) {
        editUserModal.addEventListener('show.bs.modal', function(event) {
            const button = event.relatedTarget;
            const userId = button.getAttribute('data-id');
            const firstName = button.getAttribute('data-firstname');
            const lastName = button.getAttribute('data-lastname');
            const email = button.getAttribute('data-email');
            const username = button.getAttribute('data-username');
            
            const modalForm = this.querySelector('#editUserForm');
            modalForm.querySelector('#editUserID').value = userId;
            modalForm.querySelector('#editUserFirstName').value = firstName || '';
            modalForm.querySelector('#editUserLastName').value = lastName || '';
            modalForm.querySelector('#editUserEmail').value = email || '';
            modalForm.querySelector('#editUserUsername').value = username || '';
            modalForm.querySelector('#editUserPassword').value = '';
        });
    }

    // Initialize edit worker modal
    const editWorkerModal = document.getElementById('editWorkerModal');
    if (editWorkerModal) {
        editWorkerModal.addEventListener('show.bs.modal', function(event) {
            const button = event.relatedTarget;
            const workerId = button.getAttribute('data-id');
            const firstName = button.getAttribute('data-firstname');
            const lastName = button.getAttribute('data-lastname');
            const contact = button.getAttribute('data-contact');
            const email = button.getAttribute('data-email');
            const address = button.getAttribute('data-address');
            const serviceId = button.getAttribute('data-service-id');
            const experience = button.getAttribute('data-experience');
            const status = button.getAttribute('data-status');
            
            const modalForm = this.querySelector('#editWorkerForm');
            modalForm.querySelector('#editWorkerID').value = workerId;
            modalForm.querySelector('#editWorkerFirstName').value = firstName || '';
            modalForm.querySelector('#editWorkerLastName').value = lastName || '';
            modalForm.querySelector('#editWorkerContact').value = contact || '';
            modalForm.querySelector('#editWorkerEmail').value = email || '';
            modalForm.querySelector('#editWorkerAddress').value = address || '';
            if (serviceId) modalForm.querySelector('#editWorkerService').value = serviceId;
            modalForm.querySelector('#editWorkerExperience').value = experience || '';
            modalForm.querySelector('#editWorkerStatus').value = status || 'Pending';
        });
    }
});
</script>

<script>
// Initialize edit user modal
document.addEventListener('DOMContentLoaded', function() {
    const editUserModal = document.getElementById('editUserModal');
    if (editUserModal) {
        editUserModal.addEventListener('show.bs.modal', function(event) {
            const button = event.relatedTarget;
            const userId = button.getAttribute('data-id');
            const firstName = button.getAttribute('data-firstname');
            const lastName = button.getAttribute('data-lastname');
            const email = button.getAttribute('data-email');
            const username = button.getAttribute('data-username');
            
            const modalForm = this.querySelector('#editUserForm');
            modalForm.querySelector('#editUserID').value = userId;
            modalForm.querySelector('#editUserFirstName').value = firstName || '';
            modalForm.querySelector('#editUserLastName').value = lastName || '';
            modalForm.querySelector('#editUserEmail').value = email || '';
            modalForm.querySelector('#editUserUsername').value = username || '';
            modalForm.querySelector('#editUserPassword').value = '';
        });
    }
});

// Initialize edit worker modal
document.addEventListener('DOMContentLoaded', function() {
    const editWorkerModal = document.getElementById('editWorkerModal');
    if (editWorkerModal) {
        editWorkerModal.addEventListener('show.bs.modal', function(event) {
            const button = event.relatedTarget;
            const workerId = button.getAttribute('data-id');
            const status = button.getAttribute('data-status');
            
            const modalForm = this.querySelector('#editWorkerForm');
            const workerIdInput = modalForm.querySelector('#editWorkerID');
            const statusSelect = modalForm.querySelector('#editWorkerStatus');
            
            workerIdInput.value = workerId;
            statusSelect.value = status;
        });
    }
});
</script>
<script>
function viewUser(userId) {
    // You can implement a modal or redirect to a detailed view page
    alert('View user details for User ID: ' + userId);
    // Or redirect: window.location.href = 'view_user.php?id=' + userId;
}
</script>

<!-- Delete Booking Modal -->
<div class="modal fade" id="deleteBookingModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Delete Booking</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p>Are you sure you want to delete this booking? This action cannot be undone.</p>
                <form id="deleteBookingForm" action="delete_booking.php" method="POST">
                    <input type="hidden" name="booking_id" id="deleteBookingID">
                    <div class="text-end">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-danger">Delete Booking</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Delete User Modal -->
<div class="modal fade" id="deleteUserModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Delete User</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p>Are you sure you want to delete this user? This action cannot be undone.</p>
                <form id="deleteUserForm" action="delete_user.php" method="POST">
                    <input type="hidden" name="user_id" id="deleteUserID">
                    <div class="text-end">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-danger">Delete User</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Delete Worker Modal -->
<div class="modal fade" id="deleteWorkerModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Delete Worker</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p>Are you sure you want to delete this worker? This action cannot be undone.</p>
                <form id="deleteWorkerForm" action="delete_worker.php" method="POST">
                    <input type="hidden" name="worker_id" id="deleteWorkerID">
                    <div class="text-end">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-danger">Delete Worker</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
// Initialize delete modals
document.addEventListener('DOMContentLoaded', function() {
    // Delete booking modal
    const deleteBookingModal = document.getElementById('deleteBookingModal');
    if (deleteBookingModal) {
        deleteBookingModal.addEventListener('show.bs.modal', function(event) {
            const button = event.relatedTarget;
            const bookingId = button.getAttribute('data-id');
            this.querySelector('#deleteBookingID').value = bookingId;
        });
    }

    // Delete user modal
    const deleteUserModal = document.getElementById('deleteUserModal');
    if (deleteUserModal) {
        deleteUserModal.addEventListener('show.bs.modal', function(event) {
            const button = event.relatedTarget;
            const userId = button.getAttribute('data-id');
            this.querySelector('#deleteUserID').value = userId;
        });
    }

    // Delete worker modal
    const deleteWorkerModal = document.getElementById('deleteWorkerModal');
    if (deleteWorkerModal) {
        deleteWorkerModal.addEventListener('show.bs.modal', function(event) {
            const button = event.relatedTarget;
            const workerId = button.getAttribute('data-id');
            this.querySelector('#deleteWorkerID').value = workerId;
        });
    }
});
</script>
</body>
</html>