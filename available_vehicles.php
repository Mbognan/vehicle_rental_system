<?php
session_start();
require_once __DIR__ . '/vendor/yidas/pagination/src/data/Pagination.php';

use yidas\data\Pagination;
$pagination = new Pagination(['totalCount' => 100, 'perPage' => 10]);
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

// Set default values for sorting and filtering
$perPage = 6;

$totalCountQuery = $conn->query("SELECT COUNT(*) as total FROM vehicles");
$totalCount = $totalCountQuery->fetch_assoc()['total'];

// Initialize Pagination
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;

$pagination = new Pagination([
    'totalCount' => $totalCount,
    'perPage' => $perPage
]);

$offset = ($pagination->page - 1) * $perPage;

// Fetch vehicles with limit and offset
$vehicles_sql = "SELECT * FROM vehicles LIMIT $perPage OFFSET $offset";
$vehicles_result = $conn->query($vehicles_sql);

// Fetch available vehicles
// $vehicles_sql = "SELECT * FROM vehicles";
// $vehicles_result = $conn->query($vehicles_sql);

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
    $pickup_location = $_POST['pickup_location'];
    $dropoff_location = $_POST['dropoff_location'];
    $start_date_and_time = $_POST['start_date_and_time'];
    $end_date_and_time = $_POST['end_date_and_time'];
    $proof_payment = $_FILES['proof_payment']['name'];
    $number_of_day = isset($_POST['number_of_day']) ? $_POST['number_of_day'] : 1; // Get the number of days from the form

    // Validate dates
    if (strtotime($start_date_and_time) >= strtotime($end_date_and_time)) {
        $error_message = "Start date must be before end date.";
    } else {
        // Calculate the number of days
        $start_date = new DateTime($start_date_and_time);
        $end_date = new DateTime($end_date_and_time);

        // Calculate the difference between the two dates
        $interval = $start_date->diff($end_date);
        $number_of_day = $interval->days;

        // If the booking duration is less than 1 day, set it to 1 day (rounding)
        if ($number_of_day < 1) {
            $number_of_day = 1;
        }

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
    <link href="//maxcdn.bootstrapcdn.com/bootstrap/4.1.1/css/bootstrap.min.css" rel="stylesheet" id="bootstrap-css">
    <style>
        body {
            background-color: #f4f4f4;
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
        }

        .sidebar .nav-link {
            color: #fff;
        }

        .sidebar .nav-link:hover {
            background-color: #0056b3;
        }
    

        /* .container {
            margin-left: 100px;
            padding: 20px;
        } */

        .vehicle-card {
            border: 1px solid #ddd;
            background-color: #fff;
            padding: 15px;
            margin: 10px 0;
            border-radius: 5px;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
        }

        .vehicle-card img {
            width: 150px;
            height: 150px;
            margin-right: 20px;
            border-radius: 5px;
        }

        .vehicle-img {
            cursor: pointer;
            width: 150px;
            height: 150px;
            margin-right: 20px;
            border-radius: 5px;
        }

        .vehicle-info {
            flex: 1;
        }

        .btn-book {
            background-color: #f2d900;
            color: #fff;
            border: none;
        }

        .btn-book:hover {
            background-color: #f2d900;
        }

        .alert {
            margin-bottom: 20px;
        }
         /* Category Ads */

         #ads {
            margin: 30px 0 30px 0;

        }

        #ads .card-notify-badge {
            position: absolute;
            left: -10px;
            top: -20px;
            background: #f2d900;
            text-align: center;
            border-radius: 30px 30px 30px 30px;
            color: #000;
            padding: 5px 10px;
            font-size: 14px;

        }

        #ads .card-notify-year {
            position: absolute;
            right: -10px;
            top: -20px;
            background: #ff4444;
            border-radius: 50%;
            text-align: center;
            color: #fff;
            font-size: 14px;
            width: 50px;
            height: 50px;
            padding: 15px 0 0 0;
        }


        #ads .card-detail-badge {
            background: #f2d900;
            text-align: center;
            border-radius: 30px 30px 30px 30px;
            color: #000;
            padding: 5px 10px;
            font-size: 14px;
        }



        #ads .card:hover {
            background: #fff;
            box-shadow: 12px 15px 20px 0px rgba(46, 61, 73, 0.15);
            border-radius: 4px;
            transition: all 0.3s ease;
        }

        #ads .card-image-overlay {
            font-size: 20px;

        }


        #ads .card-image-overlay span {
            display: inline-block;
        }


        #ads .ad-btn {
            text-transform: uppercase;
            width: 150px;
            height: 40px;
            border-radius: 80px;
            font-size: 16px;
            line-height: 35px;
            text-align: center;
            border: 3px solid #e6de08;
            display: block;
            text-decoration: none;
            margin: 20px auto 1px auto;
            color: #000;
            overflow: hidden;
            position: relative;
            background-color: #e6de08;
        }

        #ads .ad-btn:hover {
            background-color: #e6de08;
            color: #1e1717;
            border: 2px solid #e6de08;
            background: transparent;
            transition: all 0.3s ease;
            box-shadow: 12px 15px 20px 0px rgba(46, 61, 73, 0.15);
        }

        #ads .ad-title h5 {
            text-transform: uppercase;
            font-size: 18px;
        }
        .content {
            margin-left: 260px;
            padding: 20px;
            flex-grow: 1;
            background-color: #f8f9fa;
            overflow-y: auto;
            height: 100vh;
        }
        
        .nav-link.active {
            background-color:rgb(1, 31, 165);
            /* Change this to your preferred color */
            color: white !important;
            font-weight: bold;
            border-radius: 5px;
        }
    </style>
</head>

<body>
<?php
$current_page = basename($_SERVER['PHP_SELF']); // Get the current page file name
?>
<div class="sidebar">
    <h2>User Menu</h2>
    <ul class="nav flex-column">
        <li class="nav-item">
            <a class="nav-link <?php echo ($current_page == 'user_dashboard.php') ? 'active' : ''; ?>" href="user_dashboard.php">
                <i class="fas fa-home"></i> Dashboard
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link <?php echo ($current_page == 'my_bookings.php') ? 'active' : ''; ?>" href="my_bookings.php">
                <i class="fas fa-calendar-check"></i> My Bookings
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link <?php echo ($current_page == 'available_vehicles.php') ? 'active' : ''; ?>" href="available_vehicles.php">
                <i class="fas fa-car"></i> Available Vehicles
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link <?php echo ($current_page == 'my-testimonials.php') ? 'active' : ''; ?>" href="my-testimonials.php">
                <i class="fas fa-star"></i> My Testimonials
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link <?php echo ($current_page == 'account.php') ? 'active' : ''; ?>" href="account.php">
                <i class="fas fa-lock"></i> Account Settings
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link <?php echo ($current_page == 'logout.php') ? 'active' : ''; ?>" href="logout.php">
                <i class="fas fa-sign-out-alt"></i> Logout
            </a>
        </li>
    </ul>
</div>
    
    <div class="content">
        <center>
            <h1>Available Vehicles</h1>
        </center>

        <?php if ($error_message): ?>
            <div class="alert alert-danger"><?php echo htmlspecialchars($error_message); ?></div>
        <?php endif; ?>

        <?php if ($booking_message): ?>
            <div class="alert alert-success"><?php echo htmlspecialchars($booking_message); ?></div>
        <?php endif; ?>

        <div class="container">
            <br>
            <h4>Available Vehicles</h4>
            <!-- <input type="text" id="search" class="form-control mb-3" placeholder="Search Vehicles..."> -->
            <br>
            <div class="row" id="ads">
                <?php
                foreach ($categorized_vehicles as $purpose => $vehicles): ?>
                    <?php
                    foreach ($vehicles as $vehicle): ?>
                        <div class="col-md-4 mb-4">
                            <div class="card rounded" style="height:500px; width:100%;">
                                <!-- <div class="card vehicle-card" style="width: 100%; height: 500px; overflow: hidden;"> -->
                                <div class="card-image">
                                    <span class="card-notify-badge"><?php echo ucfirst(htmlspecialchars($purpose)); ?></span>
                                    <!-- <span class="card-notify-year"><?php echo date('Y', strtotime($vehicle['manufacture_year'] ?? '2024')); ?></span> -->
                                    <img class="img-fluid" src="<?php echo htmlspecialchars($vehicle['vehicle_image']); ?>" alt="Vehicle Image" style="width: 100%; height: 200px; object-fit: cover;" />
                                </div>
                                <div class="card-image-overlay m-auto">
                                    <span class="card-detail-badge"><?php echo ucfirst(htmlspecialchars($vehicle['status'])); ?></span>
                                    <span class="card-detail-badge">₱<?php echo number_format($vehicle['price_per_day'], 2); ?>/day</span>

                                </div>
                                <div class="card-body text-center">
                                    <div class="ad-title m-auto">
                                        <h5><?php echo htmlspecialchars($vehicle['brand'] . ' ' . $vehicle['model']); ?></h5>
                                    </div>
                                    <p><strong>Plate Number:</strong> <?php echo htmlspecialchars($vehicle['plate_number']); ?></p>
                                    <p><strong>Driver:</strong> <?php echo htmlspecialchars($vehicle['driver_name']); ?></p>
                                    <p><strong>Description:</strong> <?php echo htmlspecialchars($vehicle['description']); ?></p>
                                    <div class="d-flex justify-content-center" style="gap: 10px;">
                                    <a class="btn btn-book btn-sm" onclick="bookVehicle(<?php echo htmlspecialchars(json_encode($vehicle), ENT_QUOTES, 'UTF-8'); ?>)">
                        <i class="fas fa-calendar-check"></i> Book Vehicle
                    </a>
                    <a class="btn btn-info btn-sm" href="available_vehicles_dates.php?vehicle_id=<?php echo $vehicle['vehicle_id']?>&vehicle_name=<?php echo urlencode($vehicle['model']); ?>">
                        <i class="fas fa-calendar-alt"></i> Check Dates
                    </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <!-- </div> -->
                    <?php endforeach; ?>
                <?php endforeach; ?>
            </div>
        </div>

        <div class="d-flex justify-content-center">
                <!-- Pagination Links -->
                <nav>
                    <ul class="pagination">
                        <?php
                        // Calculate the total pages manually
                        $totalPages = ceil($pagination->totalCount / $perPage);

                        for ($i = 1; $i <= $totalPages; $i++) : ?>
                            <li class="page-item <?= ($pagination->page == $i) ? 'active' : '' ?>">
                                <a class="page-link" href="?page=<?= $i ?>"><?= $i ?></a>
                            </li>
                        <?php endfor; ?>
                    </ul>
                </nav>
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

    
            </div>
      

    </div>

    <!-- Modal for booking vehicle -->
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

                        <!-- Hidden input for number of days -->
                        <input type="hidden" name="number_of_day" id="number_of_day">

                        <div class="form-group">
                            <label for="total_cost">Total Cost</label>
                            <input type="text" class="form-control" id="total_cost" name="total_cost" readonly>
                        </div>

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

    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.10.2/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
    <script>
        window.selectedVehiclePrice = 0; // Initialize global price

        function bookVehicle(vehicle) {
            document.getElementById('book_vehicle_id').value = vehicle.vehicle_id;
            window.selectedVehiclePrice = vehicle.price_per_day; // Store price globally
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

                // Calculate duration in days
                let durationDays = Math.ceil(durationMilliseconds / (1000 * 60 * 60 * 24));

                // If the duration is less than 24 hours, round up to 1 day
                if (durationMilliseconds <= 1000 * 60 * 60 * 24) {
                    durationDays = 1; // Set to 1 day if duration is within 24 hours
                }

                // Calculate total cost
                const totalCost = durationDays * window.selectedVehiclePrice;

                // Set total cost value
                document.getElementById('total_cost').value = totalCost > 0 ? `₱${totalCost.toFixed(2)}` : '';
                document.getElementById('number_of_day').value = durationDays; // Set number of days in the hidden field
            }
        }
    </script>
</body>

</html>