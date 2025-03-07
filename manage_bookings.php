<?php
session_start();
include 'db.php';  // Include database connection

// Ensure the user is logged in and is an admin
if (!isset($_SESSION['username']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit();
}

$message = '';

// Fetch all bookings for all users, sorted with 'pending' status on top
$booking_sql = "SELECT b.booking_id, b.start_date_and_time, b.end_date_and_time, b.status, u.username, 
                       u.fullname, v.brand, v.model, b.complete_address, b.mobile, b.proof_payment, 
                       b.pickup_location, u.email, b.dropoff_location
                FROM bookings b 
                JOIN users u ON b.username = u.username 
                JOIN vehicles v ON b.vehicle_id = v.vehicle_id
                ORDER BY CASE 
                            WHEN b.status = 'pending' THEN 1
                            ELSE 2
                          END, b.start_date_and_time DESC";

// Prepare the SQL statement
$booking_stmt = $conn->prepare($booking_sql);
if ($booking_stmt === false) {
    die('Error preparing SQL statement: ' . $conn->error);
}

// Execute the SQL statement
$booking_stmt->execute();
$booking_result = $booking_stmt->get_result();

// Handle booking updates or deletions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $booking_id = $_POST['booking_id'] ?? null;

    if (isset($_POST['delete_booking'])) {
        if ($booking_id) {
            $delete_sql = "DELETE FROM bookings WHERE booking_id = ?";
            $delete_stmt = $conn->prepare($delete_sql);
            if ($delete_stmt === false) {
                die('Error preparing SQL statement: ' . $conn->error);
            }
            $delete_stmt->bind_param('i', $booking_id);
            $delete_stmt->execute();
            $message = "Booking deleted successfully!";
            header("Location: manage_bookings.php");
            exit();
        } else {
            $message = "Invalid booking ID for deletion.";
        }
    } elseif (isset($_POST['update_status'])) {
        $status = $_POST['update_status'];
        if ($booking_id) {
            $update_sql = "UPDATE bookings SET status = ? WHERE booking_id = ?";
            $update_stmt = $conn->prepare($update_sql);
            if ($update_stmt === false) {
                die('Error preparing SQL statement: ' . $conn->error);
            }
            $update_stmt->bind_param('si', $status, $booking_id);
            $update_stmt->execute();
            $message = "Booking status updated!";
            header("Location: manage_bookings.php");
            exit();
        } else {
            $message = "Invalid booking ID for status update.";
        }
    } elseif (isset($_POST['Verify_booking'])) {
        // Capture the edit booking details
        $start_date = $_POST['start_date'] . ' ' . $_POST['start_time'];
        $end_date = $_POST['end_date'] . ' ' . $_POST['end_time'];
        $status = $_POST['status'];
        $pickup_location = $_POST['pickup_location'];
        $dropoff_location = $_POST['dropoff_location'];

        if ($booking_id) {
            // Update booking details
            $edit_sql = "UPDATE bookings SET start_date_and_time = ?, end_date_and_time = ?, status = ?, 
                         pickup_location = ?, dropoff_location = ? WHERE booking_id = ?";
            $edit_stmt = $conn->prepare($edit_sql);
            if ($edit_stmt === false) {
                die('Error preparing SQL statement: ' . $conn->error);
            }
            $edit_stmt->bind_param('sssssi', $start_date, $end_date, $status, $pickup_location, $dropoff_location, $booking_id);
            $edit_stmt->execute();

            $message = "Booking verified successfully!";
            header("Location: manage_bookings.php");
            exit();
        } else {
            $message = "Invalid booking ID for verification.";
        }
    }
}

$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Bookings</title>
    <link href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.3/dist/umd/popper.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
    <style>
        body {
            display: flex;
            height: 100vh;
            font-family: Arial, sans-serif;
        }
        .sidebar {
            width: 240px;
            background-color: #343a40;
            color: #fff;
            padding: 20px;
            position: fixed;
            top: 0;
            left: 0;
            height: 100%;
            overflow-y: auto;
        }
        .sidebar h2 {
            margin-top: 0;
            font-size: 1.5rem;
        }
        .sidebar a {
            color: #fff;
            text-decoration: none;
        }
        .sidebar a:hover {
            background-color: #495057;
            padding: 10px;
            border-radius: 5px;
        }
        .content {
            margin-left: 240px; /* Adjusted for sidebar */
            padding: 20px;
            flex-grow: 1;
            background-color: #f8f9fa;
            overflow-y: auto; /* Allows scrolling if content is long */
        }
        .booking-card {
            border: 1px solid #ddd;
            border-radius: 5px; /* Softened corners */
            padding: 20px;
            margin: 10px 0;
            background-color: #ffffff;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            transition: transform 0.2s;
            max-width: 600px;
            width: 100%; /* Ensure it takes full width */
        }
        .booking-card:hover {
            transform: scale(1.02);
        }
        .btn {
            margin-right: 5px;
        }
    </style>
</head>
<body>
<div class="sidebar">
        <h2>Admin Menu</h2>
        <ul class="nav flex-column">
            <li class="nav-item"><a class="nav-link" href="admin_dashboard.php"><i class="fas fa-tachometer-alt"></i> Dashboard</a></li>
            <li class="nav-item"><a class="nav-link" href="manage_vehicles.php"><i class="fas fa-car"></i> Manage Vehicles</a></li>
            <li class="nav-item"><a class="nav-link" href="manage_users.php"><i class="fas fa-users"></i> Manage Users</a></li>
            <li class="nav-item"><a class="nav-link" href="manage_bookings.php"><i class="fas fa-book"></i> Manage Bookings</a></li>
            <li class="nav-item"><a class="nav-link" href="vehicle_inventory.php"><i class="fas fa-book"></i> Vehicle Inventory</a></li>
            <li class="nav-item"><a class="nav-link" href="manage_testimonials.php"><i class="fas fa-comments"></i> Manage Testimonials</a></li>
            <li class="nav-item"><a class="nav-link" href="manage_contact_us.php"><i class="fas fa-envelope-open-text"></i> Manage Contact Us</a></li>
            <li class="nav-item"><a class="nav-link" href="logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
        </ul>
    </div>


    <div class="content">
        <center><h1>Manage Bookings</h1></center>
        <?php if ($message): ?>
            <div class="alert alert-info"><?php echo htmlspecialchars($message); ?></div>
        <?php endif; ?>

        <h2>All User Bookings</h2>
        <?php if ($booking_result->num_rows > 0): ?>
            <?php while ($booking = $booking_result->fetch_assoc()): ?>
                <div class="booking-card">
                    <h5>Booking ID: <?php echo htmlspecialchars($booking['booking_id']); ?></h5>
                    <p>User: <?php echo htmlspecialchars($booking['username']); ?></p>
                    <p>fullname: <?php echo htmlspecialchars($booking['fullname']); ?></p>
                    <p>Vehicle: <?php echo htmlspecialchars($booking['brand'] . ' ' . $booking['model']); ?></p>
                    <p>Start: <?php echo isset($booking['start_date_and_time']) ? date('d-m-Y H:i', strtotime($booking['start_date_and_time'])) : 'N/A'; ?></p>
                    <p>End: <?php echo isset($booking['end_date_and_time']) ? date('d-m-Y H:i', strtotime($booking['end_date_and_time'])) : 'N/A'; ?></p>
                    <p>Complete Address: <?php echo htmlspecialchars($booking['complete_address']); ?></p>
                    <p>Mobile: <?php echo htmlspecialchars($booking['mobile']); ?></p>
                    <p>Email: <?php echo htmlspecialchars($booking['email']); ?></p>
                    <p>Pick-Up Location: <?php echo htmlspecialchars($booking['pickup_location']); ?></p>
                    <p>Drop-Off Location: <?php echo htmlspecialchars($booking['dropoff_location']); ?></p>
                    <p>Status: <strong><?php echo htmlspecialchars($booking['status']); ?></strong></p>
                    <p>Proof of Payment: 
                        <?php if (!empty($booking['proof_payment'])): ?>
                            <a href="uploads/<?php echo htmlspecialchars($booking['proof_payment']); ?>" target="_blank">
                                View Proof of Payment
                            </a>
                        <?php else: ?>
                            No proof uploaded.
                        <?php endif; ?>
                    </p>

                    <form method="POST" style="display:inline;">
                        <input type="hidden" name="booking_id" value="<?php echo htmlspecialchars($booking['booking_id']); ?>">
                        <button type="submit" name="delete_booking" class="btn btn-danger">Delete</button>
                    </form>
                    <form method="POST" style="display:inline;">
                        <input type="hidden" name="booking_id" value="<?php echo htmlspecialchars($booking['booking_id']); ?>">
                        <button type="submit" name="update_status" value="completed" class="btn btn-success">Mark as Completed</button>
                        <button type="submit" name="update_status" value="canceled" class="btn btn-warning">Cancel</button>
                    </form>
                    <button type="button" class="btn btn-secondary" data-toggle="modal" data-target="#editBookingModal<?php echo $booking['booking_id']; ?>">Verify</button>
                </div>

                <!-- Verify Booking Modal -->
                <div class="modal fade" id="editBookingModal<?php echo $booking['booking_id']; ?>" tabindex="-1" role="dialog">
                    <div class="modal-dialog" role="document">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title">Verify Booking</h5>
                                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                    <span aria-hidden="true">&times;</span>
                                </button>
                            </div>
                            <form action="verify_booking.php" method="post">
                                <div class="modal-body">
                                    <input type="hidden" name="booking_id" value="<?php echo htmlspecialchars($booking['booking_id']); ?>">
                                    <div class="form-group">
                                        <label for="start_date">Start Date:</label>
                                        <input type="date" name="start_date" class="form-control" value="<?php echo date('Y-m-d', strtotime($booking['start_date_and_time'])); ?>" required>
                                    </div>
                                    <div class="form-group">
                                        <label for="start_time">Start Time:</label>
                                        <input type="time" name="start_time" class="form-control" value="<?php echo date('H:i', strtotime($booking['start_date_and_time'])); ?>" required>
                                    </div>
                                    <div class="form-group">
                                        <label for="end_date">End Date:</label>
                                        <input type="date" name="end_date" class="form-control" value="<?php echo date('Y-m-d', strtotime($booking['end_date_and_time'])); ?>" required>
                                    </div>
                                    <div class="form-group">
                                        <label for="end_time">End Time:</label>
                                        <input type="time" name="end_time" class="form-control" value="<?php echo date('H:i', strtotime($booking['end_date_and_time'])); ?>" required>
                                    </div>
                                    <div class="form-group">
                                        <label for="pickup_location">Pick-Up Location:</label>
                                        <input type="text" name="pickup_location" class="form-control" value="<?php echo htmlspecialchars($booking['pickup_location']); ?>" required>
                                    </div>
                                    <div class="form-group">
                                        <label for="dropoff_location">Drop-Off Location:</label>
                                        <input type="text" name="dropoff_location" class="form-control" value="<?php echo htmlspecialchars($booking['dropoff_location']); ?>" required>
                                    </div>
                                    <div class="form-group">
                                <label for="status">Email:</label>
                                <input type="email" name="email" class="form-control" value="<?php echo htmlspecialchars($booking['email']); ?>" required>
                                </div>
                                    <div class="form-group">
                                        <label for="status">Status:</label>
                                        <select name="status" class="form-control" required>
                                            <option value="<?php echo htmlspecialchars($booking['status']); ?>">
                                                <?php echo ucwords(htmlspecialchars($booking['status'])); ?>
                                            </option>
                                            <option value="pending">Pending</option>
                                            <option value="confirmed">Confirmed</option>
                                            <option value="completed">Completed</option>
                                            <option value="cancelled">Cancelled</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="modal-footer">
                                    <button type="submit" name="Verify_booking" class="btn btn-primary">Save changes</button>
                                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

            <?php endwhile; ?>
        <?php else: ?>
            <p>No bookings found.</p>
        <?php endif; ?>
    </div>
</body>
</html>
