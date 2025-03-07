<?php
session_start();
include 'db.php'; 
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Palompon Vehicle Rental System</title>
    <link href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #white;
        }
        .card {
            transition: transform 0.3s; 
            background-color: #ffffff; 
            color: #333; 
        }
        .card:hover {
            transform: scale(1.05); 
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2); 
        }
        .text-center h1 {
            color: #007bff; 
        }
        .text-center p {
            color: #555; 
        }
    </style>
</head>
<body>

<?php include 'header.php'; ?>

<div class="container mt-5">
    <div class="text-right mb-5">
    </div>

    <div class="row">
        <div class="col-md-4 mb-4">
            <div class="card shadow1">
                <img src="vehicle_images/imgs3.jpeg" class="card-img-top" alt="Affordable Rentals">
                <div class="card-body">
                    <h5 class="card-title">Affordable Rentals</h5>
                    <p class="card-text">Explore our range of vehicles at competitive prices. Perfect for all occasions!</p>
                    <a href="vehicle_list.php" class="btn btn-primary">View Vehicles</a>
                </div>
            </div>
        </div>
        <div class="col-md-4 mb-4">
            <div class="card shadow2">
                <img src="vehicle_images/banner.jpg" class="card-img-top" alt="Easy Booking">
                <div class="card-body">
                    <h5 class="card-title">Easy Booking</h5>
                    <p class="card-text">Book your dream vehicle in your special occasions in just a few clicks!</p>
                    <a href="my_bookings.php" class="btn btn-primary">Book Now</a>
                </div>
            </div>
        </div>
        <div class="col-md-4 mb-4">
            <div class="card shadow3">
                <img src="vehicle_images/social-icons.png" class="card-img-top" alt="Customer Support">
                <div class="card-body">
                    <h5 class="card-title">Customer Support</h5>
                    <p class="card-text">Our support team is here to assist you 24/7. Your satisfaction is our priority!</p>
                    <a href="contact_us.php" class="btn btn-primary">Contact Us</a>
                </div>
            </div>
        </div>
    </div>
</div>


<?php include 'footer.php'; ?>

<script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.2/dist/umd/popper.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>

</body>
</html>
