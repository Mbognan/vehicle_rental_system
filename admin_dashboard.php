<?php  
session_start();
include 'db.php';

// Ensure the user is an admin
if (!isset($_SESSION['username']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit();
}

$message = '';

// Handle adding vehicles
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_vehicle'])) {
    // Add vehicle logic
    $target_dir = "uploads/";
    $target_file = $target_dir . basename($_FILES['vehicle_image']['name']);
    move_uploaded_file($_FILES['vehicle_image']['tmp_name'], $target_file);
    
    $stmt = $conn->prepare("INSERT INTO vehicles (plate_number, brand, model, year, status, price_per_day, owner, vehicle_image, description, purpose) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param('ssssssssss', $_POST['plate_number'], $_POST['brand'], $_POST['model'], $_POST['year'], $_POST['status'], $_POST['price_per_day'], $_POST['owner'], $target_file, $_POST['description'], $_POST['purpose']);
    $stmt->execute();
    $message = "Vehicle added successfully!";
    $stmt->close();
}

// Fetch analytics and data
$available_sql = "SELECT COUNT(*) as count FROM vehicles WHERE status = 'available'";
$unavailable_sql = "SELECT COUNT(*) as count FROM vehicles WHERE status = 'unavailable'";
$vehicles_available = $conn->query($available_sql)->fetch_assoc()['count'];
$vehicles_unavailable = $conn->query($unavailable_sql)->fetch_assoc()['count'];

$verified_sql = "SELECT COUNT(*) as count FROM users WHERE verified = 1";
$unverified_sql = "SELECT COUNT(*) as count FROM users WHERE verified = 0";
$users_verified = $conn->query($verified_sql)->fetch_assoc()['count'];
$users_unverified = $conn->query($unverified_sql)->fetch_assoc()['count'];

// Get vehicles grouped by owner
$vehicles_sql = "SELECT * FROM vehicles ORDER BY owner";
$vehicles_result = $conn->query($vehicles_sql);

$users_sql = "SELECT * FROM users";
$users_result = $conn->query($users_sql);

$bookings_sql = "SELECT b.booking_id, b.start_date_and_time, b.end_date_and_time, b.status, u.fullname, v.brand, v.model, b.number_of_day, b.pickup_location, b.dropoff_location FROM bookings b JOIN users u ON b.username = u.username JOIN vehicles v ON b.vehicle_id = v.vehicle_id";
$bookings_result = $conn->query($bookings_sql);

// Close the connection
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>
    <link href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css" rel="stylesheet">
    <style>
        body {
            display: flex;
            flex-direction: column;
            height: 100vh;
            background-color: #f0f2f5;
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
            color: #ffdd57; 
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
            margin-left: 240px; 
            padding: 20px;
            flex-grow: 1;
            overflow-y: auto; /* Add scroll if content exceeds */
        }
        .analytics-card {
            background-color: #ffffff;
            border-radius: 10px;
            padding: 20px;
            margin-bottom: 20px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            cursor: pointer;
            transition: transform 0.2s;
        }
        .analytics-card:hover {
            transform: scale(1.02);
        }
        .analytics-card h4 {
            color: #007bff; 
        }
        .alert-info {
            background-color: #e7f1ff; 
            color: #0056b3; 
            border-color: #0056b3; 
        }
        table {
            background-color: #fff;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }
        .vehicle-image {
            width: 100px;
            height: 60px;
            object-fit: cover;
            border-radius: 8px;
        }
        .owner-name {
            color: #ffffff;
            background-color: #007bff;
            padding: 8px 15px;
            border-radius: 5px;
            font-size: 1.2rem;
            margin-bottom: 10px;
        }
        footer {
            margin-top: auto;
            text-align: center;
            padding: 10px;
            background-color: #343a40;
            color: white;
        }
        .hidden {
            display: none; 
        }
        .nav-link.active {
            background-color: #007bff !important;
            /* Bootstrap primary color */
            color: white !important;
            border-radius: 5px;
        }
    </style>
    <script>
        function toggleVisibility(sectionId) {
            const section = document.getElementById(sectionId);
            section.classList.toggle('hidden');
        }
    </script>
</head>
<body>
<div class="sidebar">
    <h2>Admin Menu</h2>
    <ul class="nav flex-column">
        <?php 
        $current_page = basename($_SERVER['PHP_SELF']); 
        ?>
        <li class="nav-item">
            <a class="nav-link <?php echo ($current_page == 'admin_dashboard.php') ? 'active' : ''; ?>" href="admin_dashboard.php">
                <i class="fas fa-tachometer-alt"></i> Dashboard
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link <?php echo ($current_page == 'manage_vehicles.php') ? 'active' : ''; ?>" href="manage_vehicles.php">
                <i class="fas fa-car"></i> Manage Vehicles
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link <?php echo ($current_page == 'manage_users.php') ? 'active' : ''; ?>" href="manage_users.php">
                <i class="fas fa-users"></i> Manage Users
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link <?php echo ($current_page == 'manage_bookings.php') ? 'active' : ''; ?>" href="manage_bookings.php">
                <i class="fas fa-book"></i> Manage Bookings
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link <?php echo ($current_page == 'vehicle_inventory.php') ? 'active' : ''; ?>" href="vehicle_inventory.php">
                <i class="fas fa-book"></i> Vehicle Inventory
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link <?php echo ($current_page == 'manage_testimonials.php') ? 'active' : ''; ?>" href="manage_testimonials.php">
                <i class="fas fa-comments"></i> Manage Testimonials
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link <?php echo ($current_page == 'manage_contact_us.php') ? 'active' : ''; ?>" href="manage_contact_us.php">
                <i class="fas fa-envelope-open-text"></i> Manage Contact Us
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link" href="logout.php">
                <i class="fas fa-sign-out-alt"></i> Logout
            </a>
        </li>
    </ul>
</div>


    <div class="content">
        <center><h1>Admin Dashboard</h1></center>
        <div class="alert alert-info"><?php echo htmlspecialchars($message); ?></div>

        <div class="row">
            <div class="col-md-6">
                <!-- Vehicle Analytics Card -->
                <div class="analytics-card" onclick="toggleVisibility('vehicleList')">
                    <h4>Vehicle Analytics</h4>
                    <p><strong>Available Vehicles:</strong> <?php echo $vehicles_available; ?></p>
                    <p><strong>Unavailable Vehicles:</strong> <?php echo $vehicles_unavailable; ?></p>
                </div>
                <div id="vehicleList" class="hidden">
                    <h2> Lists of Vehicle Owner </h2>
                    <?php 
                    // Initialize variables
                    $current_owner = "";
                    while ($vehicle = $vehicles_result->fetch_assoc()) {
                        // Group vehicles by owner
                        if ($vehicle['owner'] !== $current_owner) {
                            if ($current_owner !== "") echo "</table>"; // Close previous owner's list
                            $current_owner = $vehicle['owner'];
                            echo "<h3 class='owner-name'>" . htmlspecialchars($vehicle['owner']) . "</h3>
                                  <table class='table table-bordered'>
                                  <thead class='thead-light'>
                                  <tr><th>Image</th><th>Plate Number</th><th>Brand</th><th>Model</th><th>Year</th><th>Status</th><th>Price</th></tr>
                                  </thead><tbody>";
                        }
                        echo "<tr>
                                <td><img src='" . htmlspecialchars($vehicle['vehicle_image']) . "' class='vehicle-image'></td>
                                <td>" . htmlspecialchars($vehicle['plate_number']) . "</td>
                                <td>" . htmlspecialchars($vehicle['brand']) . "</td>
                                <td>" . htmlspecialchars($vehicle['model']) . "</td>
                                <td>" . htmlspecialchars($vehicle['year']) . "</td>
                                <td>" . htmlspecialchars($vehicle['status']) . "</td>
                                <td>" . htmlspecialchars($vehicle['price_per_day']) . "</td>
                              </tr>";
                    }
                    echo "</tbody></table>";
                    ?>
                </div>
            </div>

            <div class="col-md-6">
                <!-- User Analytics Card -->
                <div class="analytics-card" onclick="toggleVisibility('userList')">
                    <h4>User Analytics</h4>
                    <p><strong>Verified Users:</strong> <?php echo $users_verified; ?></p>
                    <p><strong>Unverified Users:</strong> <?php echo $users_unverified; ?></p>
                </div>
                <div id="userList" class="hidden">
                    <h2>User List</h2>
                    <table class="table table-bordered">
                        <thead class="thead-light">
                            <tr>
                                <th>Username</th>
                                <th>Email</th>
                                <th>Full Name</th>
                                <th>Age</th>
                                <th>Contact Number</th>
                                <th>Verification Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($user = $users_result->fetch_assoc()): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($user['username']); ?></td>
                                    <td><?php echo htmlspecialchars($user['email']); ?></td>
                                    <td><?php echo htmlspecialchars($user['fullname']); ?></td>
                                    <td><?php echo htmlspecialchars($user['age']); ?></td>
                                    <td><?php echo htmlspecialchars($user['contact_number']); ?></td>
                                    <td><?php echo $user['verified'] ? 'Verified' : 'Unverified'; ?></td>
                                </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <h2>Booking List</h2>
        <table class="table table-bordered">
            <thead class="thead-light">
                <tr>
                    <th>Booking ID</th>
                    <th>Fullname</th>
                    <th>Vehicle</th>
                    <th>Pickup Location</th>
                    <th>Dropoff Location</th>
                    <th>Start Date</th>
                    <th>End Date</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($booking = $bookings_result->fetch_assoc()): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($booking['booking_id']); ?></td>
                        <td><?php echo htmlspecialchars($booking['fullname']); ?></td>
                        <td><?php echo htmlspecialchars($booking['brand'] . ' ' . $booking['model']); ?></td>
                        <td><?php echo htmlspecialchars($booking['pickup_location']); ?></td>
                        <td><?php echo htmlspecialchars($booking['dropoff_location']); ?></td>
                        <td><?php echo htmlspecialchars(date('Y-m-d H:i', strtotime($booking['start_date_and_time']))); ?></td>
                        <td><?php echo htmlspecialchars(date('Y-m-d H:i', strtotime($booking['end_date_and_time']))); ?></td>
                        <td><?php echo htmlspecialchars($booking['status']); ?></td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>

    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.2/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>
