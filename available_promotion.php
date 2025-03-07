<?php 
session_start();
include 'db.php';

// Check if user is logged in and has the 'user' role
if (!isset($_SESSION['username']) || $_SESSION['role'] != 'user') {
    header("Location: login.php");
    exit();
}

// Fetch available promotions
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
    <title>Available Promotions</title>
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
        .promotion-card {
            border: 1px solid #ddd;
            background-color: #fff;
            padding: 15px;
            margin: 10px 0;
            border-radius: 5px;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
        }
        .alert {
            margin-bottom: 20px;
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
                <a class="nav-link" href="available_promotion.php"><i class="fas fa-tags"></i> Available Promotions</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a>
            </li>
        </ul>
    </div>
    
    <div class="container">
    <center><h1>Available Promotions</h1></center>
        
        <?php if (count($available_promotions) > 0): ?>
            <div class="row">
                <?php foreach ($available_promotions as $promotion): ?>
                    <div class="col-md-4">
                        <div class="promotion-card">
                            <h3><?php echo htmlspecialchars($promotion['code']); ?></h3>
                            <p><strong>Discount:</strong> <?php echo htmlspecialchars($promotion['discount']); ?>%</p>
                            <p><strong>Valid From:</strong> <?php echo htmlspecialchars($promotion['start_date']); ?></p>
                            <p><strong>Valid Until:</strong> <?php echo htmlspecialchars($promotion['end_date']); ?></p>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <div class="alert alert-info">No available promotions at the moment.</div>
        <?php endif; ?>
    </div>

    <!-- Bootstrap JS and dependencies -->
    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.3/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>
