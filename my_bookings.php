<?php
session_start();
include 'db.php';

if (!isset($_SESSION['username']) || $_SESSION['role'] != 'user') {
    header("Location: login.php");
    exit();
}

$username = $_SESSION['username'];
$bookings_sql = "SELECT b.*, v.brand, v.model, v.plate_number 
                 FROM bookings b 
                 JOIN vehicles v ON b.vehicle_id = v.vehicle_id 
                 WHERE b.username = ?";
$stmt = $conn->prepare($bookings_sql);
$stmt->bind_param("s", $username); 
$stmt->execute();
$bookings_result = $stmt->get_result();

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Bookings</title>
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.1/css/all.min.css">
    <style>
        body {
            background-color: #f4f4f4;
            font-family: Arial, sans-serif;
        }
        .sidebar {
            padding: 20px;
            background-color: #007bff;
            color: #fff;
            height: 100vh;
            position: fixed;
            width: 250px;
        }
        .sidebar h2 {
            color: #fff;
            margin-bottom: 30px;
        }
        .sidebar .nav-link {
            color: #fff;
        }
        .sidebar .nav-link:hover {
            background-color: #0056b3;
        }
        .container {
            margin-left: 270px;
            padding: 20px;
        }
        h1 {
            margin-bottom: 20px;
        }
        table {
            background-color: #fff;
            border-radius: 0.5rem;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        th, td {
            vertical-align: middle;
        }
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
        <h1 class="text-center"><i class="fas fa-calendar-alt"></i> My Booked Vehicles</h1>
        <?php if ($bookings_result->num_rows > 0): ?>
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th><i class="fas fa-car"></i> Vehicle</th>
                        <th><i class="fas fa-clipboard"></i> Plate Number</th>
                        <th><i class="fas fa-calendar-alt"></i> Start Date & Time</th>
                        <th><i class="fas fa-calendar-alt"></i> End Date & Time</th>
                        <th><i class="fas fa-map-marker-alt"></i> Pick-Up Location</th>
                        <th><i class="fas fa-map-marker-alt"></i> Drop-Off Location</th>
                        <th><i class="fas fa-info-circle"></i> Status</th>
                        <th><i class="fas fa-cogs"></i> Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($booking = $bookings_result->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($booking['brand'] . ' ' . $booking['model']); ?></td>
                            <td><?php echo htmlspecialchars($booking['plate_number']); ?></td>
                            <td><?php echo htmlspecialchars(date('Y-m-d H:i', strtotime($booking['start_date_and_time']))); ?></td>
                            <td><?php echo htmlspecialchars(date('Y-m-d H:i', strtotime($booking['end_date_and_time']))); ?></td>
                            <td><?php echo htmlspecialchars($booking['pickup_location']); ?></td>
                            <td><?php echo htmlspecialchars($booking['dropoff_location']); ?></td>
                            <td>
                                <?php
                                $status_map = [
                                    'pending' => 'Pending',
                                    'confirmed' => 'Confirmed',
                                    'cancelled' => 'Cancelled',
                                    'completed' => 'Completed'
                                ];
                                $status = strtolower($booking['status']);
                                echo htmlspecialchars($status_map[$status] ?? 'Unknown Status');
                                ?>
                            </td>
                            <td>
                                <button class="btn btn-primary" onclick="editBooking(<?php echo htmlspecialchars(json_encode($booking)); ?>)"><i class="fas fa-edit"></i> Edit</button>
                                <a href="cancel_booking.php?booking_id=<?php echo htmlspecialchars($booking['booking_id']); ?>" class="btn btn-danger"><i class="fas fa-trash-alt"></i> Cancel</a>
                            </td>
                            <td> <a href="booking_receipt.php?booking_id=<?php echo htmlspecialchars($booking['booking_id']); ?>" class="btn btn-success"> <i class="fas fa-file-invoice"></i> View Receipt</a></td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        <?php else: ?>
            <p class="text-center">No bookings found.</p>
        <?php endif; ?>
    </div>

    <!-- Edit Booking Modal -->
    <div id="editBookingModal" class="modal fade" tabindex="-1" role="dialog">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="fas fa-edit"></i> Edit Booking</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <form action="update_booking.php" method="post">
                        <input type="hidden" name="booking_id" id="booking_id">
                        <div class="form-group">
                            <label for="fullname">Full Name</label>
                            <input type="text" class="form-control" name="fullname" id="fullname" required>
                        </div>
                        <div class="form-group">
                            <label for="complete_address">Complete Address</label>
                            <input type="text" class="form-control" name="complete_address" id="complete_address" required>
                        </div>
                        <div class="form-group">
                            <label for="mobile">Mobile Number</label>
                            <input type="text" class="form-control" name="mobile" id="mobile" required>
                        </div>
                        <div class="form-group">
                            <label for="pickup_location">Pick-Up Location</label>
                            <input type="text" class="form-control" name="pickup_location" id="pickup_location" required>
                        </div>
                        <div class="form-group">
                            <label for="dropoff_location">Drop-Off Location</label>
                            <input type="text" class="form-control" name="dropoff_location" id="dropoff_location" required>
                        </div>
                        <div class="form-group">
                            <label for="start_date_and_time">Start Date & Time</label>
                            <input type="datetime-local" class="form-control" name="start_date_and_time" id="start_date_and_time" required>
                        </div>
                        <div class="form-group">
                            <label for="end_date_and_time">End Date & Time</label>
                            <input type="datetime-local" class="form-control" name="end_date_and_time" id="end_date_and_time" required>
                        </div>
                        <button type="submit" class="btn btn-primary"><i class="fas fa-check"></i> Update Booking</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS and dependencies -->
    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="cdn.jsdelivr.net/npm/@popperjs/core@2.9.3/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>

    <script>
        function editBooking(booking) {
            document.getElementById('booking_id').value = booking.booking_id;
            document.getElementById('fullname').value = booking.fullname;
            document.getElementById('complete_address').value = booking.complete_address;
            document.getElementById('mobile').value = booking.mobile;
            document.getElementById('pickup_location').value = booking.pickup_location;
            document.getElementById('dropoff_location').value = booking.dropoff_location;
            document.getElementById('start_date_and_time').value = booking.start_date_and_time.split('T')[0] + 'T' + booking.start_date_and_time.split('T')[1];
            document.getElementById('end_date_and_time').value = booking.end_date_and_time.split('T')[0] + 'T' + booking.end_date_and_time.split('T')[1];
            $('#editBookingModal').modal('show');
        }
    </script>
</body>
</html>
