<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Home</title>
    <!-- Bootstrap CSS -->
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <!-- Custom Styles -->
    <link rel="stylesheet" href="styles.css">
    <style>
        body {
            background-image: url('vehicle_images/palompon2.jpeg');
            background-size: cover;
            background-position: center;
            color: #fff;
            font-family: 'Arial', sans-serif;
            margin: 0;
            padding: 0;
        }
        .navbar {
            background-color: rgba(0, 0, 0, 0.8);
        }
        .navbar-brand, .nav-link {
            font-weight: bold;
            transition: color 0.3s;
        }
        .nav-link:hover {
            transform: scale(1.05); 
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2); 
            color: #FFD700; /* Gold color on hover */
        }
        .container {
            max-width: 800px;
            margin: 10px auto;
            padding: 30px;
            background-color: rgba(0, 0, 0, 0.5);
            border-radius: 10px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.5);
        }
        h1 {
            text-align: center;
            text-shadow: 1px 1px 4px rgba(0, 0, 0, 0.8);
        }
        p {
            text-align: center;
            line-height: 1.6;
        }
        footer {
            text-align: center;
            padding: 20px 0;
            position: fixed;
            bottom: 0;
            width: 100%;
            background-color: rgba(0, 0, 0, 0.8);
        }
    </style>
</head>
<body>

<nav class="navbar navbar-expand-lg navbar-dark fixed-top">
    <a class="navbar-brand" href="index.php">Home</a>
    <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
        <span class="navbar-toggler-icon"></span>
    </button>
    <div class="collapse navbar-collapse" id="navbarNav">
        <ul class="navbar-nav ml-auto">
        <li class="nav-item">
                <a class="nav-link" href="About Us.php">About Us</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="contact_us.php">Contact Us</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="testimonials.php">Testimonials</a>
            </li>
            <?php if (isset($_SESSION['role'])): ?>
                <li class="nav-item">
                    <a class="nav-link" href="logout.php">Logout</a>
                </li>
            <?php else: ?>
                <li class="nav-item">
                    <a class="nav-link" href="login.php">Login</a>
                </li>
            <?php endif; ?>
        </ul>
    </div>
</nav>

<div class="container mt-5">
    <h1>Welcome to Palompon Vehicle Rental System</h1>

    
    <!-- Additional content can go here -->
</div>

<!-- Bootstrap JS and dependencies -->
<script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.6/dist/umd/popper.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>
