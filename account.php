<?php
session_start();
include 'db.php';

// Check if user is logged in
if (!isset($_SESSION['username']) || $_SESSION['role'] != 'user') {
    header("Location: login.php");
    exit();
}

// Fetch user's booking information
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

        .content-wrapper {
            max-width: 600px;
            margin: auto;
            padding: 30px;
            background: white;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }

        h1,
        h2 {
            margin-bottom: 20px;
            text-align: center;
        }

        hr {
            margin-top: 10px;
            margin-bottom: 20px;
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


    <div class="container">
        <div class="content-wrapper">
            <h2>Password Management</h2>
            <hr />
            <?php include 'change_password.php'; ?>
        </div>
    </div>

    <!-- Bootstrap JS and dependencies -->
    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.3/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>

</body>

</html>