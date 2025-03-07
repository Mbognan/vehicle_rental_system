<ul class="navbar-nav ml-auto d-flex align-items-center">
                <li class="nav-item header_search">
                    <input type="text" placeholder="Search..." class="form-control" style="width: 200px;">
                    <button class="btn btn-outline-light ml-1">Search</button>
                    <i class="fas fa-search text-white ml-2"></i>
                </li>
                <?php if (isset($_SESSION['role'])): ?>
                    <li class="nav-item logout">
                        <a class="nav-link" href="logout.php">Logout</a> <!-- Add your logout logic here -->
                    </li>
                <?php endif; ?>
            </ul>



            <?php if (isset($_SESSION['username'])): ?>
            <p class="text-center mt-3">Logged in as: <strong><?php echo htmlspecialchars($_SESSION['username']); ?></strong></p>
        <?php endif; ?>


        $dashboard_link = '';
if (isset($_SESSION['role'])) {
    if ($_SESSION['role'] === 'admin') {
        $dashboard_link = 'admin_dashboard.php';
    } elseif ($_SESSION['role'] === 'user') {
        $dashboard_link = 'user_dashboard.php';
    }
}

<?php if (isset($_SESSION['role']) && $_SESSION['role'] === 'admin'): ?>
                    <li class="nav-item">
                        <a class="nav-link" href="admin_dashboard.php">Admin Dashboard</a>
                    </li>
                <?php elseif (isset($_SESSION['role']) && $_SESSION['role'] === 'user'): ?>
                    <li class="nav-item">
                        <a class="nav-link" href="user_dashboard.php">User Dashboard</a>
                    </li>




                    http://localhost/vehicle_rental_system/index.php


// Fetch testimonials from the database
$stmt = $conn->prepare("SELECT * FROM testimonials ORDER BY created_at DESC");
$stmt->execute();
$testimonials = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<?php if (count($testimonials) > 0): ?>
        <?php foreach ($testimonials as $testimonial): ?>
            <div class="card testimonial-card">
                <div class="card-header">
                    <?php echo htmlspecialchars($testimonial['name']); ?> 
                    <small class="text-white float-right"><?php echo htmlspecialchars($testimonial['created_at']); ?></small>
                </div>
                <div class="card-body">
                    <p class="card-text"><?php echo htmlspecialchars($testimonial['message']); ?></p>
                </div>
            </div>
        <?php endforeach; ?>
    <?php else: ?>
        <div class="alert alert-warning text-center" role="alert">
            No testimonials available.
        </div>
    <?php endif; ?>

    <p><a href="forgot_password.php" class="text-white">Forgot Password</a></p>


    //checkig the date

// if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['vehicle_id'])) {
//     $vehicle_id = intval($_GET['vehicle_id']); // Sanitize input

//     // SQL query to get bookings for the specified vehicle
//     $sql = "SELECT start_date_and_time, end_date_and_time 
//             FROM bookings 
//             WHERE vehicle_id = ?";
//     $stmt = $conn->prepare($sql);
//     $stmt->bind_param('i', $vehicle_id);
//     $stmt->execute();
//     $result = $stmt->get_result();

//     $unavailable_dates = [];
//     while ($row = $result->fetch_assoc()) {
//         $unavailable_dates[] = [
//             'start' => $row['start_date_and_time'],
//             'end' => $row['end_date_and_time']
//         ];
//     }

//     // Return the data as JSON
//     header('Content-Type: application/json');
//     echo json_encode($unavailable_dates);

//     $stmt->close();
//     $conn->close();
// } else {
//     // Handle invalid requests
//     http_response_code(400);
//     echo json_encode(['error' => 'Invalid request']);
// }



<?php 
session_start();
include 'db.php';  // Include database connection

// Ensure the user is logged in and is an admin
if (!isset($_SESSION['username']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit();
}

$message = '';

// Fetch all bookings for all users including complete address, mobile, proof of payment, pick-up and drop-off locations
$booking_sql = "SELECT b.booking_id, b.start_date_and_time, b.end_date_and_time, b.status, u.username, 
                       v.make, v.model, b.complete_address, b.mobile, b.proof_payment, 
                       b.pickup_location, b.dropoff_location
                FROM bookings b 
                JOIN users u ON b.username = u.username 
                JOIN vehicles v ON b.vehicle_id = v.vehicle_id";
$booking_stmt = $conn->prepare($booking_sql);
$booking_stmt->execute();
$booking_result = $booking_stmt->get_result();

// Handle booking updates or deletions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $booking_id = $_POST['booking_id'] ?? null;

    if (isset($_POST['delete_booking'])) {
        $delete_sql = "DELETE FROM bookings WHERE booking_id = ?";
        $delete_stmt = $conn->prepare($delete_sql);
        $delete_stmt->bind_param('i', $booking_id);
        $delete_stmt->execute();
        $message = "Booking deleted successfully!";
        header("Location: manage_bookings.php");
        exit();
    } elseif (isset($_POST['update_status'])) {
        $status = $_POST['update_status'];
        $update_sql = "UPDATE bookings SET status = ? WHERE booking_id = ?";
        $update_stmt = $conn->prepare($update_sql);
        $update_stmt->bind_param('si', $status, $booking_id);
        $update_stmt->execute();
        $message = "Booking status updated!";
        header("Location: manage_bookings.php");
        exit();
    } elseif (isset($_POST['Verify_booking'])) {
        // Capture the edit booking details
        $start_date = $_POST['start_date'] . ' ' . $_POST['start_time'];
        $end_date = $_POST['end_date'] . ' ' . $_POST['end_time'];
        $status = $_POST['status'];
        $pickup_location = $_POST['pickup_location'];
        $dropoff_location = $_POST['dropoff_location'];

        // Update booking details
        $edit_sql = "UPDATE bookings SET start_date_and_time = ?, end_date_and_time = ?, status = ?, 
                     pickup_location = ?, dropoff_location = ? WHERE booking_id = ?";
        $edit_stmt = $conn->prepare($edit_sql);
        $edit_stmt->bind_param('sssssi', $start_date, $end_date, $status, $pickup_location, $dropoff_location, $booking_id);
        $edit_stmt->execute();

        $message = "Booking verified successfully!";
        header("Location: manage_bookings.php");
        exit();
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
                    <p>Vehicle: <?php echo htmlspecialchars($booking['make'] . ' ' . $booking['model']); ?></p>
                    <p>Start: <?php echo isset($booking['start_date_and_time']) ? date('d-m-Y H:i', strtotime($booking['start_date_and_time'])) : 'N/A'; ?></p>
                    <p>End: <?php echo isset($booking['end_date_and_time']) ? date('d-m-Y H:i', strtotime($booking['end_date_and_time'])) : 'N/A'; ?></p>
                    <p>Complete Address: <?php echo htmlspecialchars($booking['complete_address']); ?></p>
                    <p>Mobile: <?php echo htmlspecialchars($booking['mobile']); ?></p>
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
                            <form method="POST">
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







<?php
session_start();
include 'db.php';  // Include database connection

// Include PHPMailer files
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'vendor/autoload.php'; // Ensure PHPMailer's autoloader is included

$message = '';

// Fetch all bookings for all users including complete address, mobile, proof of payment, pick-up and drop-off locations
$booking_sql = "SELECT b.booking_id, b.start_date_and_time, b.end_date_and_time, b.status, u.username, 
                       u.email, v.make, v.model, b.complete_address, b.mobile, b.proof_payment, 
                       b.pickup_location, b.dropoff_location
                FROM bookings b 
                JOIN users u ON b.username = u.username 
                JOIN vehicles v ON b.vehicle_id = v.vehicle_id";
$booking_stmt = $conn->prepare($booking_sql);
$booking_stmt->execute();
$booking_result = $booking_stmt->get_result();

// Handle booking updates or deletions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $booking_id = $_POST['booking_id'] ?? null;

    if (isset($_POST['delete_booking'])) {
        $delete_sql = "DELETE FROM bookings WHERE booking_id = ?";
        $delete_stmt = $conn->prepare($delete_sql);
        $delete_stmt->bind_param('i', $booking_id);
        $delete_stmt->execute();
        $message = "Booking deleted successfully!";
        header("Location: manage_bookings.php");
        exit();
    } elseif (isset($_POST['update_status'])) {
        $status = $_POST['update_status'];
        $update_sql = "UPDATE bookings SET status = ? WHERE booking_id = ?";
        $update_stmt = $conn->prepare($update_sql);
        $update_stmt->bind_param('si', $status, $booking_id);
        $update_stmt->execute();
        $message = "Booking status updated!";
        header("Location: manage_bookings.php");
        exit();
    } elseif (isset($_POST['Verify_booking'])) {
        // Capture the edit booking details
        $start_date = $_POST['start_date'] . ' ' . $_POST['start_time'];
        $end_date = $_POST['end_date'] . ' ' . $_POST['end_time'];
        $status = $_POST['status'];
        $pickup_location = $_POST['pickup_location'];
        $dropoff_location = $_POST['dropoff_location'];

        // Update booking details
        $edit_sql = "UPDATE bookings SET start_date_and_time = ?, end_date_and_time = ?, status = ?, 
                     pickup_location = ?, dropoff_location = ? WHERE booking_id = ?";
        $edit_stmt = $conn->prepare($edit_sql);
        $edit_stmt->bind_param('sssssi', $start_date, $end_date, $status, $pickup_location, $dropoff_location, $booking_id);
        $edit_stmt->execute();

        // Send email notification to user about the booking verification
        $booking_sql = "SELECT u.email, b.start_date_and_time, b.end_date_and_time, b.pickup_location, b.dropoff_location 
                        FROM bookings b 
                        JOIN users u ON b.username = u.username 
                        WHERE b.booking_id = ?";
        $booking_stmt = $conn->prepare($booking_sql);
        $booking_stmt->bind_param('i', $booking_id);
        $booking_stmt->execute();
        $booking_result = $booking_stmt->get_result();
        $booking = $booking_result->fetch_assoc();

        $user_email = $booking['email'];
        $subject = "Booking Verified Successfully";
        $message_body = "
            Hello, 

            Your booking has been successfully verified. Here are the updated details:

            Start Date and Time: " . date('d-m-Y H:i', strtotime($booking['start_date_and_time'])) . "
            End Date and Time: " . date('d-m-Y H:i', strtotime($booking['end_date_and_time'])) . "
            Pick-Up Location: " . htmlspecialchars($booking['pickup_location']) . "
            Drop-Off Location: " . htmlspecialchars($booking['dropoff_location']) . "
            
            If you have any further questions, please contact us.

            Thank you for choosing our service!
        ";

        // Create a PHPMailer instance
        $mail = new PHPMailer(true);
        
        try {
            // Server settings
            $mail->isSMTP();
            $mail->Host = 'smtp.example.com';  // Replace with your SMTP server (e.g., smtp.gmail.com)
            $mail->SMTPAuth = true;
            $mail->Username = 'your-email@example.com';  // SMTP username
            $mail->Password = 'your-email-password';     // SMTP password
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port = 587;  // SMTP port (use 465 for SSL)
            
            // Recipients
            $mail->setFrom('your-email@example.com', 'Your Service Name');
            $mail->addAddress($user_email);
            
            // Content
            $mail->isHTML(false);
            $mail->Subject = $subject;
            $mail->Body    = $message_body;

            // Send the email
            $mail->send();
            $message = "Booking verified successfully and user notified via email!";
        } catch (Exception $e) {
            $message = "Booking verified, but email notification failed. Mailer Error: " . $mail->ErrorInfo;
        }

        header("Location: manage_bookings.php");
        exit();
    }
}

$conn->close();
?>









<?php
session_start();
include 'db.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = $_POST['username'];
    $email = $_POST['email'];
    $password = password_hash($_POST['password'], PASSWORD_BCRYPT);
    $fullname = $_POST['fullname'];
    $age = $_POST['age'];
    $contact_number = $_POST['contact_number'];
    $complete_address = $_POST['complete_address'];

    $drivers_license = $_FILES['drivers_license']['name'];
    $barangay_clearance = $_FILES['barangay_clearance']['name'];
    $target_dir = "uploads/";

    move_uploaded_file($_FILES['drivers_license']['tmp_name'], $target_dir . basename($drivers_license));
    move_uploaded_file($_FILES['barangay_clearance']['tmp_name'], $target_dir . basename($barangay_clearance));

    $role = 'user';

    $sql = "INSERT INTO users (username, email, password, role, fullname, age, contact_number, complete_address, drivers_license, barangay_clearance) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
    
    if ($stmt = $conn->prepare($sql)) {
        $stmt->bind_param("ssssssssss", $username, $email, $password, $role, $fullname, $age, $contact_number, $complete_address, $drivers_license, $barangay_clearance);
        
        if ($stmt->execute()) {
            $success_message = "New user registered successfully.";
        } else {
            $error_message = "Error: " . $stmt->error;
        }
        $stmt->close();
    } else {
        $error_message = "Error preparing statement: " . $conn->error;
    }
    $conn->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register</title>
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
    <style>
        body {
            background-image: url('vehicle_images/palompon.jpeg');
            background-size: cover;
            background-position: center;
            background-repeat: no-repeat;
            color: #fff;
            font-family: Arial, sans-serif;
        }
        .container {
            max-width: 500px;
            margin-top: 50px;
            background: rgba(0, 0, 0, 0.7);
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 0 20px rgba(0, 0, 0, 0.5);
        }
        .alert {
            margin-top: 20px;
        }
        .form-group label {
            font-weight: bold;
        }
        .btn-primary {
            background-color: #007bff;
            border: none;
        }
        .btn-primary:hover {
            background-color: #0056b3;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1 class="text-center"><i class="fas fa-user-plus"></i> Registration Form</h1>

        <?php if (isset($success_message)): ?>
            <div class="alert alert-success" role="alert">
                <?php echo htmlspecialchars($success_message); ?>
            </div>
        <?php endif; ?>

        <?php if (isset($error_message)): ?>
            <div class="alert alert-danger" role="alert">
                <?php echo htmlspecialchars($error_message); ?>
            </div>
        <?php endif; ?>

        <form action="register.php" method="post" enctype="multipart/form-data">
            <div class="form-group">
                <label for="fullname"><i class="fas fa-user"></i> Full Name</label>
                <input type="text" class="form-control" id="fullname" name="fullname" placeholder="Enter your Full Name" required>
            </div>
            <div class="form-group">
                <label for="age"><i class="fas fa-calendar-alt"></i> Age</label>
                <input type="number" class="form-control" id="age" name="age" placeholder="Enter your Age" required>
            </div>
            <div class="form-group">
                <label for="contact_number"><i class="fas fa-phone"></i> Contact Number</label>
                <input type="text" class="form-control" id="contact_number" name="contact_number" placeholder="Enter your Contact Number" required>
            </div>
            <div class="form-group">
                <label for="complete_address"><i class="fas fa-map-marker-alt"></i> Complete Address</label>
                <input type="text" class="form-control" id="complete_address" name="complete_address" placeholder="Enter your Complete Address" required>
            </div>
            <div class="form-group">
                <label for="drivers_license"><i class="fas fa-id-card"></i> Driver's License</label>
                <input type="file" class="form-control-file" id="drivers_license" name="drivers_license" required>
            </div>
            <div class="form-group">
                <label for="barangay_clearance"><i class="fas fa-check-circle"></i> Barangay Clearance</label>
                <input type="file" class="form-control-file" id="barangay_clearance" name="barangay_clearance" required>
            </div>
            <div class="form-group">
                <label for="username"><i class="fas fa-user-circle"></i> Username</label>
                <input type="text" class="form-control" id="username" name="username" placeholder="Enter your Username" required>
            </div>
            <div class="form-group">
                <label for="email"><i class="fas fa-envelope"></i> Email</label>
                <input type="email" class="form-control" id="email" name="email" placeholder="Enter your Email" required>
            </div>
            <div class="form-group">
                <label for="password"><i class="fas fa-lock"></i> Password</label>
                <input type="password" class="form-control" id="password" name="password" placeholder="Enter your Password" required>
            </div>
            <button type="submit" class="btn btn-primary btn-block"><i class="fas fa-paper-plane"></i> Register</button>
        </form>
        <div class="text-center mt-3">
            <p>Already registered? <a href="login.php" style="color: #fff; text-decoration: underline;">Login into your account</a></p>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.3/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>





<div class="form-group">
                <label for="drivers_license"><i class="fas fa-id-card"></i> Driver's License</label>
                <input type="file" class="form-control-file" id="drivers_license" name="drivers_license" required>
            </div>
            <div class="form-group">
                <label for="barangay_clearance"><i class="fas fa-check-circle"></i> Barangay Clearance</label>
                <input type="file" class="form-control-file" id="barangay_clearance" name="barangay_clearance" required>
            </div>




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
                       v.make, v.model, b.complete_address, b.mobile, b.proof_payment, 
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
<!-- Your sidebar and content sections follow -->
</body>
</html>














<?php   
session_start();
include 'db.php'; // Ensure this file properly connects to your database

// Check if user is logged in and has the 'user' role
if (!isset($_SESSION['username']) || $_SESSION['role'] != 'user') {
    header("Location: login.php");
    exit();
}

$username = $_SESSION['username'];

// Initialize message variables
$booking_message = '';
$error_message = '';

// Fetch available vehicles
$vehicles_sql = "SELECT * FROM vehicles";
$vehicles_result = $conn->query($vehicles_sql);

if (!$vehicles_result) {
    $error_message = "Failed to fetch available vehicles: " . $conn->error;
} else {
    // Initialize array to categorize vehicles
    $categorized_vehicles = [
        'TOYOTA' => [],
        'FORD' => [],
        'NISSAN' => [],
        'HYUNDAI' => [],
        'MITSUBISHI' => [],
    ];

    // Categorize vehicles by purpose
    while ($vehicle = $vehicles_result->fetch_assoc()) {
        if (array_key_exists($vehicle['purpose'], $categorized_vehicles)) {
            $categorized_vehicles[$vehicle['purpose']][] = $vehicle;
        } else {
            $categorized_vehicles['other'][] = $vehicle; // Add to 'other' if purpose is not listed
        }
    }
}

// Handle vehicle booking
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['book_vehicle'])) {
    $vehicle_id = $_POST['vehicle_id'];
    $fullname = $_POST['fullname'];
    $complete_address = $_POST['complete_address'];
    $mobile = $_POST['mobile'];
    $pickup_location = $_POST['pickup_location']; // New field for pick-up location
    $dropoff_location = $_POST['dropoff_location']; // New field for drop-off location
    $start_date_and_time = $_POST['start_date_and_time'];
    $end_date_and_time = $_POST['end_date_and_time'];
    $proof_payment = $_FILES['proof_payment']['name'];
    $number_of_day = $_POST['number_of_day']; // Get the number of days from the form

    // Validate dates
    if (strtotime($start_date_and_time) >= strtotime($end_date_and_time)) {
        $error_message = "Start date must be before end date.";
    } else {
        // Check for overlapping bookings
        $check_booking_sql = "SELECT * FROM bookings WHERE vehicle_id = ? AND (start_date_and_time < ? AND end_date_and_time > ?)";
        $check_stmt = $conn->prepare($check_booking_sql);
        $check_stmt->bind_param('iss', $vehicle_id, $end_date_and_time, $start_date_and_time);
        $check_stmt->execute();
        $check_result = $check_stmt->get_result();

        if ($check_result->num_rows > 0) {
            $error_message = "The vehicle is already booked for the selected dates.";
        } else {
            // Fetch vehicle price
            $price_sql = "SELECT price_per_day FROM vehicles WHERE vehicle_id = ?";
            $price_stmt = $conn->prepare($price_sql);
            $price_stmt->bind_param('i', $vehicle_id);
            $price_stmt->execute();
            $price_stmt->bind_result($price_per_day);
            $price_stmt->fetch();
            $price_stmt->close();

            // Calculate total cost using number_of_day * price_per_day
            $total_cost = $number_of_day * $price_per_day;

            // Handle file upload
            if ($_FILES['proof_payment']['error'] == UPLOAD_ERR_OK) {
                $upload_dir = 'uploads/';
                $upload_file = $upload_dir . basename($_FILES['proof_payment']['name']);
                move_uploaded_file($_FILES['proof_payment']['tmp_name'], $upload_file);
            } else {
                $error_message = "Failed to upload proof of payment.";
            }

            // Mark vehicle as unavailable
            $update_vehicle_sql = "UPDATE vehicles SET status = 'unavailable' WHERE vehicle_id = ?";
            $update_stmt = $conn->prepare($update_vehicle_sql);
            $update_stmt->bind_param('i', $vehicle_id);
            $update_stmt->execute();
            $update_stmt->close();

            // Prepare booking insertion
            $stmt = $conn->prepare("INSERT INTO bookings (vehicle_id, username, fullname, complete_address, mobile, pickup_location, dropoff_location, start_date_and_time, end_date_and_time, total_cost, proof_payment, payment_method) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");

            $stmt->bind_param('isssssssssss', $vehicle_id, $username, $fullname, $complete_address, $mobile, $pickup_location, $dropoff_location, $start_date_and_time, $end_date_and_time, $total_cost, $proof_payment, $payment_method);
            
            if ($stmt->execute()) {
                $booking_message = "Vehicle booked successfully! Total cost: ₱" . number_format($total_cost, 2);
            } else {
                $error_message = "Failed to book the vehicle: " . $stmt->error;
            }
            
            $stmt->close();
        }
        $check_stmt->close();
    }
}

// Handle vehicle return (mark as available)
if (isset($_GET['return_vehicle_id'])) {
    $vehicle_id = $_GET['return_vehicle_id'];

    // Mark vehicle as available
    $return_vehicle_sql = "UPDATE vehicles SET status = 'available' WHERE vehicle_id = ?";
    $return_stmt = $conn->prepare($return_vehicle_sql);
    $return_stmt->bind_param('i', $vehicle_id);
    $return_stmt->execute();
    $return_stmt->close();
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Available Vehicles</title>
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.1/css/all.min.css">
    <style>
        body { background-color: #f4f4f4; }
        .sidebar { padding: 20px; background-color: #007bff; color: #fff; height: 100vh; position: fixed; width: 250px; }
        .sidebar h2 { color: #fff; }
        .sidebar .nav-link { color: #fff; }
        .sidebar .nav-link:hover { background-color: #0056b3; }
        .container { margin-left: 270px; padding: 20px; }
        .vehicle-card { border: 1px solid #ddd; background-color: #fff; padding: 15px; margin: 10px 0; border-radius: 5px; box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1); }
        .vehicle-card img { width: 150px; height: 150px; margin-right: 20px; border-radius: 5px; }
        .vehicle-img {
            cursor: pointer;
            width: 150px;
            height: 150px;
            margin-right: 20px;
            border-radius: 5px;
        }
        .vehicle-info { flex: 1; }
        .btn-book { background-color: #007bff; color: #fff; border: none; }
        .btn-book:hover { background-color: #0056b3; }
        .alert { margin-bottom: 20px; }
    </style>
</head>
<body>
    <div class="sidebar">
        <h2>User Menu</h2>
        <ul class="nav flex-column">
            <li class="nav-item">
                <a class="nav-link" href="user_dashboard.php"><i class="fas fa-home"></i> Dashboard</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="my_bookings.php"><i class="fas fa-calendar-check"></i> My Bookings</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="available_vehicles.php"><i class="fas fa-car"></i> Available Vehicles</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="my-testimonials.php"> <i class="fas fa-star"></i> My Testimonials</a>
            </li>
            <li class="nav-item">
                <a class="nav-link active" href="account.php"><i class="fas fa-lock"></i> Account Settings</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a>
            </li>
        </ul>
    </div>
    
    <div class="container">
        <center><h1>Available Vehicles</h1></center>
        
        <?php if ($error_message): ?>
            <div class="alert alert-danger"><?php echo htmlspecialchars($error_message); ?></div>
        <?php endif; ?>
        
        <?php if ($booking_message): ?>
            <div class="alert alert-success"><?php echo htmlspecialchars($booking_message); ?></div>
        <?php endif; ?>

        <?php foreach ($categorized_vehicles as $brand => $vehicles): ?>
            <h2><?php echo $brand; ?> Vehicles</h2>
            <div class="row">
                <?php foreach ($vehicles as $vehicle): ?>
                    <div class="col-md-4">
                        <div class="vehicle-card">
                            <a href="#" data-toggle="modal" data-target="#imageModal<?php echo $vehicle['vehicle_id']; ?>">
                                <img src="<?php echo htmlspecialchars($vehicle['vehicle_image']); ?>" alt="Vehicle Image" class="vehicle-img">
                            </a>
                            <div class="vehicle-info">
                                <h3><?php echo htmlspecialchars($vehicle['brand'] . ' ' . $vehicle['model']); ?></h3>
                                <p><strong>Plate Number:</strong> <?php echo htmlspecialchars($vehicle['plate_number']); ?></p>
                                <p><strong>Driver:</strong> <?php echo htmlspecialchars($vehicle['driver_name']); ?></p>
                                <p><strong>Status:</strong> <?php echo ucfirst(htmlspecialchars($vehicle['status'])); ?></p>
                                <p><strong>Rental Fee/Day:</strong> ₱<?php echo number_format(htmlspecialchars($vehicle['price_per_day']), 2); ?></p>
                                <p><strong>Description:</strong> <?php echo htmlspecialchars($vehicle['description']); ?></p>
                                <button class="btn btn-book" onclick="bookVehicle(<?php echo htmlspecialchars(json_encode($vehicle)); ?>)">Book</button>
                                <button class="btn btn-book" onclick="location.href='available_vehicles_dates.php?vehicle_id=<?php echo $vehicle['vehicle_id']?>&vehicle_name=<?php echo $vehicle['model'] ?>'">Check Dates</button>
                            </div>
                        </div>
                    </div>

    <!-- Book Vehicle Modal -->
    <div id="bookForm" class="modal fade" tabindex="-1" role="dialog">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Book Vehicle</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <form action="" method="post" enctype="multipart/form-data">
                        <input type="hidden" name="book_vehicle" value="1">
                        <input type="hidden" name="vehicle_id" id="book_vehicle_id">
                        <p id="vehicle_details"></p>
                        
                        <div class="form-group">
                            <label for="fullname">Full Name</label>
                            <input type="text" class="form-control" name="fullname" required>
                        </div>
                        <div class="form-group">
                            <label for="complete_address">Complete Address</label>
                            <input type="text" class="form-control" name="complete_address" required>
                        </div>
                        <div class="form-group">
                            <label for="mobile">Mobile Number</label>
                            <input type="text" class="form-control" name="mobile" required>
                        </div>
                        <div class="form-group">
                            <label for="pickup_location">Pick-Up Location</label>
                            <input type="text" class="form-control" name="pickup_location" required>
                        </div>
                        <div class="form-group">
                            <label for="dropoff_location">Drop-Off Location</label>
                            <input type="text" class="form-control" name="dropoff_location" required>
                        </div>

                        <div class="form-group">
                        <label for="start_date_and_time">Start Date and Time</label>
                        <input type="datetime-local" class="form-control" name="start_date_and_time"
                               id="start_date_and_time" required onchange="calculateTotalCost()">
                    </div>
                    <div class="form-group">
                        <label for="end_date_and_time">End Date and Time</label>
                        <input type="datetime-local" class="form-control" name="end_date_and_time"
                               id="end_date_and_time" required onchange="calculateTotalCost()">
                    </div>
                        <div class="form-group">
                            <label for="total_cost">Total Cost</label>
                            <input type="text" class="form-control" id="total_cost" name="total_cost" readonly>
                        </div> 
                        
                        
                        <button type="submit" class="btn btn-primary" onclick="alert('In development')">Pay At the Counter</button>
                        

                        <div class="form-group">
                            <label for="proof_payment">Upload Proof of Payment</label>
                            <input type="file" class="form-control" name="proof_payment" required>
                        </div>
                        
                        <div class="form-group">
                            <label>Payment Instructions:</label>
                            <p>Please make your payment through the QR code provided which is Gcash and BPI.</p>
                            <p>We allow partial payment which is half of your Total Cost for fast booking verification.</p>
                            <img src="vehicle_images/PAY HERE.png" alt="QR Code" style="width: 200px;">
                            <img src="vehicle_images/pay.jpg" alt="QR Code" style="width: 200px;">
                        </div>

                        <button type="submit" class="btn btn-primary">Book Vehicle</button>
                    </form>
                </div>
            </div>
        </div>
    </div>




                    <!-- Modal for vehicle image -->
                    <div class="modal fade" id="imageModal<?php echo $vehicle['vehicle_id']; ?>" tabindex="-1" role="dialog" aria-labelledby="imageModalLabel<?php echo $vehicle['vehicle_id']; ?>" aria-hidden="true">
                        <div class="modal-dialog modal-lg" role="document">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title" id="imageModalLabel<?php echo $vehicle['vehicle_id']; ?>">Vehicle Image</h5>
                                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                        <span aria-hidden="true">&times;</span>
                                    </button>
                                </div>
                                <div class="modal-body">
                                    <img src="<?php echo htmlspecialchars($vehicle['vehicle_image']); ?>" alt="Vehicle Image" class="img-fluid">
                                </div>
                            </div>
                        </div>
                    </div>

                <?php endforeach; ?>
            </div>
        <?php endforeach; ?>

    </div>

    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.10.2/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
    <script>
        window.selectedVehiclePrice = 0; // Initialize global price

        function bookVehicle(vehicle) {
            document.getElementById('book_vehicle_id').value = vehicle.vehicle_id;
            document.getElementById('vehicle_details').innerText = `Booking ${vehicle.make} ${vehicle.model} (Plate: ${vehicle.plate_number})`;
            document.getElementById('total_cost').value = ''; // Reset total cost
            window.selectedVehiclePrice = vehicle.price_per_hour; // Store price globally
            $('#bookForm').modal('show');
        }

        function calculateTotalCost() {
            const start = document.getElementById('start_date_and_time').value;
            const end = document.getElementById('end_date_and_time').value;

            if (start && end) {
                const startDate = new Date(start);
                const endDate = new Date(end);
                
                // Calculate duration in milliseconds
                const durationMilliseconds = endDate - startDate;
                
                // Calculate duration in hours
                const durationHours = Math.ceil(durationMilliseconds / (1000 * 60 * 60));
                
                // Calculate total cost
                const totalCost = durationHours * window.selectedVehiclePrice;
                
                // Set total cost value
                document.getElementById('total_cost').value = totalCost > 0 ? `₱${totalCost.toFixed(2)}` : '';
            }
        }
        
    </script>
</body>
</html>



<?php
session_start();
include 'db.php';

if (!isset($_SESSION['username']) || $_SESSION['role'] != 'user') {
    header("Location: login.php");
    exit();
}

// Retrieve the booking ID from the URL
$booking_id = $_GET['booking_id'] ?? null;
if ($booking_id === null) {
    die("Booking ID is required.");
}

// Fetch booking details from the database
$booking_sql = "SELECT b.*, v.brand, v.model, v.plate_number, v.price_per_day 
                FROM bookings b 
                JOIN vehicles v ON b.vehicle_id = v.vehicle_id 
                WHERE b.booking_id = ?";
$stmt = $conn->prepare($booking_sql);
$stmt->bind_param('i', $booking_id);
$stmt->execute();
$booking_result = $stmt->get_result();

if ($booking_result->num_rows == 0) {
    die("Booking not found.");
}

$booking = $booking_result->fetch_assoc();

// Close the database connection
$stmt->close();
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Booking Receipt</title>
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            font-family: Arial, sans-serif;
        }
        .receipt-container {
            max-width: 800px;
            margin: 50px auto;
            padding: 20px;
            border: 1px solid #ddd;
            border-radius: 10px;
            background-color: #fff;
        }
        .receipt-header {
            text-align: center;
        }
        .total-cost {
            font-size: 1.5em;
            font-weight: bold;
            color: green;
        }
        .btn-print {
            margin-top: 20px;
            background-color: #007bff;
            color: white;
        }
        .btn-print:hover {
            background-color: #0056b3;
        }
    </style>
</head>
<body>

<div class="receipt-container">
    <div class="receipt-header">
        <h2>Booking Receipt</h2>
        <p><strong>Booking ID:</strong> #<?php echo htmlspecialchars($booking['booking_id']); ?></p>
        <p><strong>Booking Date:</strong> <?php echo date("F j, Y, g:i a", strtotime($booking['created_at'])); ?></p>
    </div>

    <h4>User Details</h4>
    <table class="table table-bordered">
        <tr><th>Full Name</th><td><?php echo htmlspecialchars($booking['fullname']); ?></td></tr>
        <tr><th>Mobile</th><td><?php echo htmlspecialchars($booking['mobile']); ?></td></tr>
        <tr><th>Address</th><td><?php echo htmlspecialchars($booking['complete_address']); ?></td></tr>
    </table>

    <h4>Vehicle Details</h4>
    <table class="table table-bordered">
        <tr><th>Brand</th><td><?php echo htmlspecialchars($booking['brand']); ?></td></tr>
        <tr><th>Model</th><td><?php echo htmlspecialchars($booking['model']); ?></td></tr>
        <tr><th>Plate Number</th><td><?php echo htmlspecialchars($booking['plate_number']); ?></td></tr>
        <tr><th>Price per Day</th><td>₱<?php echo number_format($booking['price_per_day'], 2); ?></td></tr>
    </table>

    <h4>Booking Details</h4>
    <table class="table table-bordered">
        <tr><th>Pick-up Location</th><td><?php echo htmlspecialchars($booking['pickup_location']); ?></td></tr>
        <tr><th>Drop-off Location</th><td><?php echo htmlspecialchars($booking['dropoff_location']); ?></td></tr>
        <tr><th>Start Date and Time</th><td><?php echo date("F j, Y, g:i a", strtotime($booking['start_date_and_time'])); ?></td></tr>
        <tr><th>End Date and Time</th><td><?php echo date("F j, Y, g:i a", strtotime($booking['end_date_and_time'])); ?></td></tr>
    </table>

    <h4>Total Cost</h4>
    <p class="total-cost">₱<?php echo number_format($booking['total_cost'], 2); ?></p>

    <h4>Proof of Payment</h4>
    <?php if ($booking['proof_payment']): ?>
        <p><a href="uploads/<?php echo htmlspecialchars($booking['proof_payment']); ?>" target="_blank">View Proof of Payment</a></p>
    <?php else: ?>
        <p>No proof of payment uploaded.</p>
    <?php endif; ?>

    <button class="btn btn-print" onclick="window.print();">Print Receipt</button>
</div>

<script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.10.2/dist/umd/popper.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Available Vehicles</title>
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.1/css/all.min.css">
    <style>
        body { background-color: #f4f4f4; }
        .sidebar { padding: 20px; background-color: #007bff; color: #fff; height: 100vh; position: fixed; width: 250px; }
        .sidebar h2 { color: #fff; }
        .sidebar .nav-link { color: #fff; }
        .sidebar .nav-link:hover { background-color: #0056b3; }
        .container { margin-left: 270px; padding: 20px; }
        .vehicle-card { border: 1px solid #ddd; background-color: #fff; padding: 15px; margin: 10px 0; border-radius: 5px; box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1); }
        .vehicle-card img { width: 150px; height: 150px; margin-right: 20px; border-radius: 5px; }
        .vehicle-info { flex: 1; }
        .btn-book { background-color: #007bff; color: #fff; border: none; }
        .btn-book:hover { background-color: #0056b3; }
        .alert { margin-bottom: 20px; }
    </style>
</head>
<body>
    <div class="sidebar">
        <h2>User Menu</h2>
        <ul class="nav flex-column">
            <li class="nav-item">
                <a class="nav-link" href="user_dashboard.php"><i class="fas fa-home"></i> Dashboard</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="my_bookings.php"><i class="fas fa-calendar-check"></i> My Bookings</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="available_vehicles.php"><i class="fas fa-car"></i> Available Vehicles</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="my-testimonials.php"> <i class="fas fa-star"></i> My Testimonials</a>
            </li>
            <li class="nav-item">
                <a class="nav-link active" href="account.php"><i class="fas fa-lock"></i> Account Settings</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a>
            </li>
        </ul>
    </div>
    
    <div class="container">
        <center><h1>Available Vehicles</h1></center>
        
        <?php if ($error_message): ?>
            <div class="alert alert-danger"><?php echo htmlspecialchars($error_message); ?></div>
        <?php endif; ?>
        
        <?php if ($booking_message): ?>
            <div class="alert alert-success"><?php echo htmlspecialchars($booking_message); ?></div>
        <?php endif; ?>
        
        <?php foreach ($categorized_vehicles as $purpose => $vehicles): ?>
            <h2><?php echo ucfirst(htmlspecialchars($purpose)); ?> Vehicles</h2>
            <div class="row">
                <?php if (!empty($vehicles)): ?>
                    <?php foreach ($vehicles as $vehicle): ?>
                        <div class="col-md-4">
                            <div class="vehicle-card">
                                <img src="<?php echo htmlspecialchars($vehicle['vehicle_image']); ?>" alt="Vehicle Image">
                                <div class="vehicle-info">
                                    <h3><?php echo htmlspecialchars($vehicle['make'] . ' ' . $vehicle['model']); ?></h3>
                                    <p><strong>Plate Number:</strong> <?php echo htmlspecialchars($vehicle['plate_number']); ?></p>
                                    <p><strong>Driver:</strong> <?php echo htmlspecialchars($vehicle['driver_name']); ?></p>
                                    <p><strong>Status:</strong> <?php echo ucfirst(htmlspecialchars($vehicle['status'])); ?></p>
                                    <p><strong>Price:</strong> ₱<?php echo number_format(htmlspecialchars($vehicle['price_per_day']), 2); ?></p>
                                    <p><strong>Description:</strong> <?php echo htmlspecialchars($vehicle['description']); ?></p>
                                    <button class="btn btn-book" onclick="bookVehicle(<?php echo htmlspecialchars(json_encode($vehicle)); ?>)">Book</button>
                                    <button class="btn btn-book" onclick="location.href='available_vehicles_dates.php?vehicle_id=<?php echo $vehicle['vehicle_id']?>&vehicle_name=<?php echo $vehicle['model'] ?>'">Check Dates</button>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <p>No vehicles available for this purpose.</p>
                <?php endif; ?>
            </div>
        <?php endforeach; ?>
    </div>

    <!-- Book Vehicle Modal -->
    <div id="bookForm" class="modal fade" tabindex="-1" role="dialog">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Book Vehicle</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <form action="" method="post" enctype="multipart/form-data">
                        <input type="hidden" name="book_vehicle" value="1">
                        <input type="hidden" name="vehicle_id" id="book_vehicle_id">
                        <p id="vehicle_model"></p>
                        
                        <div class="form-group">
                            <label for="fullname">Full Name</label>
                            <input type="text" class="form-control" name="fullname" required>
                        </div>

                        <div class="form-group">
                            <label for="complete_address">Complete Address</label>
                            <input type="text" class="form-control" name="complete_address" required>
                        </div>

                        <div class="form-group">
                            <label for="mobile">Mobile Number</label>
                            <input type="text" class="form-control" name="mobile" required>
                        </div>

                        <div class="form-group">
                            <label for="pickup_location">Pick-up Location</label>
                            <input type="text" class="form-control" name="pickup_location" required>
                        </div>

                        <div class="form-group">
                            <label for="dropoff_location">Drop-off Location</label>
                            <input type="text" class="form-control" name="dropoff_location" required>
                        </div>

                        <div class="form-group">
                            <label for="start_date_and_time">Start Date & Time</label>
                            <input type="datetime-local" class="form-control" name="start_date_and_time" id="start_date_and_time" required>
                        </div>

                        <div class="form-group">
                            <label for="end_date_and_time">End Date & Time</label>
                            <input type="datetime-local" class="form-control" name="end_date_and_time" id="end_date_and_time" required>
                        </div>

                        <div class="form-group">
                            <label for="proof_payment">Proof of Payment</label>
                            <input type="file" class="form-control-file" name="proof_payment" required>
                        </div>

                        <div class="form-group">
                            <label for="total_cost">Total Cost</label>
                            <input type="text" class="form-control" id="total_cost" readonly>
                        </div>
                        <button type="submit" class="btn btn-primary">Confirm Booking</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- JS -->
    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.3/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>

    <script>
        let selectedVehiclePrice = 0;

        // Open modal with vehicle data
        function bookVehicle(vehicle) {
            document.getElementById('book_vehicle_id').value = vehicle.vehicle_id;
            document.getElementById('vehicle_model').innerText = "Model: " + vehicle.model;
            selectedVehiclePrice = vehicle.price_per_day;

            $('#bookForm').modal('show');
        }

        // Calculate total cost based on selected dates
        function calculateTotalCost() {
            const start = document.getElementById('start_date_and_time').value;
            const end = document.getElementById('end_date_and_time').value;

            if (start && end) {
                const startDate = new Date(start);
                const endDate = new Date(end);
                
                // Calculate duration in milliseconds
                const durationMilliseconds = endDate - startDate;
                
                // Calculate duration in days
                const durationDays = Math.ceil(durationMilliseconds / (1000 * 60 * 60 * 24));
                
                // Calculate total cost based on days
                const totalCost = durationDays * selectedVehiclePrice;
                
                // Set total cost value
                document.getElementById('total_cost').value = totalCost > 0 ? `₱${totalCost.toFixed(2)}` : '';
            }
        }

        // Attach event listeners for date change
        document.getElementById('start_date_and_time').addEventListener('change', calculateTotalCost);
        document.getElementById('end_date_and_time').addEventListener('change', calculateTotalCost);
    </script>
</body>
</html>
