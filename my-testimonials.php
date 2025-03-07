<?php
// Database connection
$servername = 'localhost'; 
$dbname = 'vehicle_rental_system'; 
$username = 'root'; 
$password = ''; 

try {
    $conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    echo "Connection failed: " . $e->getMessage();
}

// Handle form submission for new testimonials
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = $_POST['name'];
    $message = $_POST['message'];

    $stmt = $conn->prepare("INSERT INTO testimonials (name, message) VALUES (?, ?)");
    $stmt->execute([$name, $message]);

    $successMessage = "Thank you for your testimonial!";
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Testimonials - Palompon Vehicle Rental</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.1/css/all.min.css">
    <style>
        body {
            background-color: #f8f9fa;
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
            margin-bottom: 20px;
        }
        .sidebar .nav-link {
            color: #fff;
            transition: background-color 0.3s;
        }
        .sidebar .nav-link:hover {
            background-color: #0056b3;
        }
        .testimonial-container {
            margin-left: 270px; /* Adjust for sidebar width */
            padding: 20px;
        }
        .testimonial-card {
            margin-bottom: 30px;
        }
        .card-header {
            background-color: #007bff;
            color: white;
        }
        .alert {
            margin-top: 20px;
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
            <a class="nav-link active" href="my-testimonials.php"><i class="fas fa-star"></i> My Testimonials</a>
        </li>
        <li class="nav-item">
            <a class="nav-link" href="account.php"><i class="fas fa-lock"></i> Account Settings</a>
        </li>
        <li class="nav-item">
            <a class="nav-link" href="logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a>
        </li>
    </ul>
</div>

<div class="container testimonial-container">
    <h1 class="text-center mb-4">User Testimonials</h1>

    <?php if (isset($successMessage)): ?>
        <div class="alert alert-success" role="alert">
            <?php echo $successMessage; ?>
        </div>
    <?php endif; ?>

    <!-- Form to create a new testimonial -->
    <div class="mb-4">
        <h3>Add Your Testimonial</h3>
        <form method="POST" action="">
            <div class="form-group">
                <label for="name">Your Name</label>
                <input type="text" class="form-control" id="name" name="name" required>
            </div>
            <div class="form-group">
                <label for="message">Your Testimonial</label>
                <textarea class="form-control" id="message" name="message" rows="4" required></textarea>
            </div>
            <button type="submit" class="btn btn-primary">Submit Testimonial</button>
        </form>
    </div>

</div>

<script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.3/dist/umd/popper.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>
