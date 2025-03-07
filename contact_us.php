<?php
session_start();
include 'db.php';

$successMessage = '';
$errorMessage = '';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $message = trim($_POST['message']);

    // Basic validation
    if (empty($name) || empty($email) || empty($message)) {
        $errorMessage = "All fields are required.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errorMessage = "Invalid email format.";
    } else {
        // Prepare and execute the insert statement
        $stmt = $conn->prepare("INSERT INTO contact_us (name, email, message) VALUES (?, ?, ?)");
        if ($stmt->execute([$name, $email, $message])) {
            $successMessage = "Thank you for your message! We will get back to you soon.";
        } else {
            $errorMessage = "There was an error submitting your message. Please try again.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Contact Us</title>
    <!-- Bootstrap CSS -->
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
    <style>
        body {
            background-color: #f4f6f9;
            font-family: 'Arial', sans-serif;
        }

        .container {
            margin-top: 50px;
            max-width: 700px;
            background-color: #ffffff;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.7);
        }

        h1 {
            font-size: 2.5rem;
            font-weight: bold;
            color: #007bff;
        }

        .form-group label {
            font-weight: 600;
            color: #333;
        }

        .btn-primary {
            background-color: #007bff;
            border: none;
            transition: background-color 0.3s;
        }

        .btn-primary:hover {
            background-color: #0056b3;
        }

        .return-btn {
            margin-top: 20px;
        }

        .alert {
            font-size: 1rem;
            margin-bottom: 20px;
        }

        .btn-secondary {
            background-color: #6c757d;
            border: none;
            transition: background-color 0.3s;
        }

        .btn-secondary:hover {
            background-color: #5a6268;
        }

        .footer-text {
            text-align: center;
            font-size: 0.9rem;
            color: #888;
            margin-top: 50px;
        }
    </style>
</head>
<body>

    <div class="container">
        <h1 class="text-center"><i class="fas fa-envelope"></i> Contact Us</h1>

        <p class="text-center text-muted">We are happy to answer any questions you may have. Please reach out to us!</p>

        <!-- Success/Error Messages -->
        <?php if ($successMessage): ?>
            <div class="alert alert-success"><?php echo $successMessage; ?></div>
        <?php endif; ?>

        <?php if ($errorMessage): ?>
            <div class="alert alert-danger"><?php echo $errorMessage; ?></div>
        <?php endif; ?>

        <!-- Contact Form -->
        <form method="POST" action="contact_us.php">
            <div class="form-group">
                <label for="name"><i class="fas fa-user"></i> Name</label>
                <input type="text" class="form-control" id="name" name="name" required>
            </div>
            <div class="form-group">
                <label for="email"><i class="fas fa-envelope"></i> Email</label>
                <input type="email" class="form-control" id="email" name="email" required>
            </div>
            <div class="form-group">
                <label for="message"><i class="fas fa-comment-dots"></i> Message</label>
                <textarea class="form-control" id="message" name="message" rows="4" required></textarea>
            </div>
            <button type="submit" class="btn btn-primary btn-block"><i class="fas fa-paper-plane"></i> Submit</button>
        </form>

        <!-- Return Button -->
        <div class="text-center return-btn">
            <button class="btn btn-secondary" onclick="goBack()"><i class="fas fa-arrow-left"></i> Return</button>
        </div>
    </div>

    <script>
        function goBack() {
            window.history.back();
        }
    </script>

    <!-- Bootstrap JS and dependencies -->
    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.6/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>

    <!-- Footer Text -->
    <div class="footer-text">
        <p>&copy; 2024 Palompon Vehicle Rental System. All rights reserved.</p>
    </div>

</body>
</html>
