<?php 
session_start();
include 'db.php';

if (!isset($_SESSION['username']) || $_SESSION['role'] != 'user') {
    header("Location: login.php");
    exit();
}

$username = $_SESSION['username'];

$vehicles_sql = "SELECT * FROM vehicles WHERE status = 'available'";
$vehicles_result = $conn->query($vehicles_sql);

$categorized_vehicles = [
    'TOYOTA' => [],
    'FORD' => [],
    'NISSAN' => [],
    'HYUNDAI' => [],
    'MITSUBISHI' => [],
];

while ($vehicle = $vehicles_result->fetch_assoc()) {
    $categorized_vehicles[$vehicle['purpose']][] = $vehicle;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['book_vehicle'])) {
    $vehicle_id = $_POST['vehicle_id'];
    $start_date = $_POST['start_date'];
    $end_date = $_POST['end_date'];
    
    $stmt = $conn->prepare("INSERT INTO bookings (vehicle_id, username, start_date, end_date) VALUES (?, ?, ?, ?)");
    $stmt->bind_param('isss', $vehicle_id, $username, $start_date, $end_date);
    
    if ($stmt->execute()) {
        $conn->query("UPDATE vehicles SET status = 'unavailable' WHERE vehicle_id = $vehicle_id");
        $booking_message = "Vehicle booked successfully!";
    } else {
        $booking_message = "Failed to book the vehicle.";
    }
    
    $stmt->close();
}

$current_date = date('Y-m-d');
$promotions_sql = "SELECT * FROM promotions WHERE start_date <= ? AND end_date >= ?";
$stmt = $conn->prepare($promotions_sql);
$stmt->bind_param('ss', $current_date, $current_date);
$stmt->execute();
$promotions_result = $stmt->get_result();
$available_promotions = $promotions_result->fetch_all(MYSQLI_ASSOC);
$stmt->close();

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
        .container {
            margin-left: 270px;
            padding: 20px;
        }
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
            cursor: pointer;
        }
        .vehicle-info {
            flex: 1;
        }
        .btn-book {
            background-color: #007bff;
            color: #fff;
            border: none;
        }
        .btn-book:hover {
            background-color: #0056b3;
        }
        .alert {
            margin-bottom: 20px;
        }
    </style>
    <script>
        function goBack() {
            window.history.back();
        }

        function showImageModal(imageUrl) {
            // Set the modal image source to the clicked image's URL
            document.getElementById("modal-image").src = imageUrl;
            // Show the modal
            $('#imageModal').modal('show');
        }
    </script>
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
        <b><center><h1>User Dashboard</h1></center></b>
        
        <?php if (isset($booking_message)): ?>
            <div class="alert alert-info"><?php echo htmlspecialchars($booking_message); ?></div>
        <?php endif; ?>
        
        <?php foreach ($categorized_vehicles as $purpose => $vehicles): ?>
            <?php if (!empty($vehicles)): ?>
                <h2><?php echo ucfirst(htmlspecialchars($purpose)); ?> Vehicles</h2>
                <div class="row">
                    <?php foreach ($vehicles as $vehicle): ?>
                        <div class="col-md-4">
                            <div class="vehicle-card">
                                <img src="<?php echo htmlspecialchars($vehicle['vehicle_image']); ?>" alt="Vehicle Image" onclick="showImageModal('<?php echo htmlspecialchars($vehicle['vehicle_image']); ?>')">
                                <div class="vehicle-info">
                                    <h3><?php echo htmlspecialchars($vehicle['brand'] . ' ' . $vehicle['model']); ?></h3>
                                    <p><strong>Plate Number:</strong> <?php echo htmlspecialchars($vehicle['plate_number']); ?></p>
                                    <p><strong>Driver:</strong> <?php echo htmlspecialchars($vehicle['driver_name']); ?></p>
                                    <p><strong>Status:</strong> <?php echo ucfirst(htmlspecialchars($vehicle['status'])); ?></p>
                                    <p><strong>Rental Fee/Day:</strong> ₱<?php echo number_format(htmlspecialchars($vehicle['price_per_day']), 2); ?></p>
                                    <p><strong>Description:</strong> <?php echo htmlspecialchars($vehicle['description']); ?></p>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        <?php endforeach; ?>
    </div>

    <!-- Modal for Image Preview -->
    <div class="modal fade" id="imageModal" tabindex="-1" role="dialog" aria-labelledby="imageModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="imageModalLabel">Vehicle Image</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <img id="modal-image" src="" alt="Vehicle Image" class="img-fluid">
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS and dependencies -->
    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.3/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>
