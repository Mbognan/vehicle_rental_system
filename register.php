<?php 
session_start();
include 'db.php';  // Make sure db.php contains your DB connection setup

if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    // Sanitize and validate input data
    $username = htmlspecialchars(trim($_POST['username']));
    $email = filter_var($_POST['email'], FILTER_SANITIZE_EMAIL);
    $password = password_hash($_POST['password'], PASSWORD_BCRYPT);
    $fullname = htmlspecialchars(trim($_POST['fullname']));
    $age = intval($_POST['age']);  
    $contact_number = htmlspecialchars(trim($_POST['contact_number']));
    $complete_address = htmlspecialchars(trim($_POST['complete_address']));

    // Ensure all required fields are not empty
    if (empty($username) || empty($email) || empty($password) || empty($fullname) || empty($age) || empty($contact_number) || empty($complete_address)) {
        $error_message = "All fields are required!";
    } else {
        $role = 'user';  // Default role as user

        // SQL Query to insert new user into the database
        $sql = "INSERT INTO users (username, email, password, role, fullname, age, contact_number, complete_address) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?)";

        if ($stmt = $conn->prepare($sql)) {
            $stmt->bind_param("ssssssss", $username, $email, $password, $role, $fullname, $age, $contact_number, $complete_address);

            // Execute the statement and check for success
            if ($stmt->execute()) {
                $success_message = "New user registered successfully.";
            } else {
                $error_message = "Error: " . $stmt->error;
            }
            $stmt->close();
        } else {
            $error_message = "Error preparing statement: " . $conn->error;
        }
    }

    // Close the connection
    $conn->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register</title>
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
    <style>
        body {
            background-image: url('vehicle_images/palompon2.jpeg');
            background-size: cover;
            background-position: center;
            background-repeat: no-repeat;
            color: #fff;
            font-family: Arial, sans-serif;
        }
        .container {
            max-width: 500px;
            margin-top: 50px;
            background: rgba(0, 0, 0, 0.5);
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 0 20px rgba(0, 0, 0, 0.5);
        }
        .alert {
            margin-top: 20px;
        }
        .form-group label {
            font-weight: bold;
        }
        .btn-primary {
            background-color: #007bff;
            border: none;
        }
        .btn-primary:hover {
            background-color: #0056b3;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1 class="text-center"><i class="fas fa-user-plus"></i> Registration Form</h1>

        <?php if (isset($success_message)): ?>
            <div class="alert alert-success" role="alert">
                <?php echo htmlspecialchars($success_message); ?>
            </div>
        <?php endif; ?>

        <?php if (isset($error_message)): ?>
            <div class="alert alert-danger" role="alert">
                <?php echo htmlspecialchars($error_message); ?>
            </div>
        <?php endif; ?>

        <form action="register.php" method="post" enctype="multipart/form-data">
            <div class="form-group">
                <label for="fullname"><i class="fas fa-user"></i> Full Name</label>
                <input type="text" class="form-control" id="fullname" name="fullname" placeholder="Enter your Full Name" required>
            </div>
            <div class="form-group">
                <label for="age"><i class="fas fa-calendar-alt"></i> Age</label>
                <input type="number" class="form-control" id="age" name="age" placeholder="Enter your Age" required>
            </div>
            <div class="form-group">
                <label for="contact_number"><i class="fas fa-phone"></i> Contact Number</label>
                <input type="text" class="form-control" id="contact_number" name="contact_number" placeholder="Enter your Contact Number" required>
            </div>
            
            <div class="form-group">
                <label for="complete_address"><i class="fas fa-map-marker-alt"></i> Complete Address</label>
                <input type="text" class="form-control" id="complete_address" name="complete_address" placeholder="Enter your Complete Address" required>
            </div>
            <div class="form-group">
                <label for="username"><i class="fas fa-user-circle"></i> Username</label>
                <input type="text" class="form-control" id="username" name="username" placeholder="Enter your Username" required>
            </div>
            <div class="form-group">
                <label for="email"><i class="fas fa-envelope"></i> Email</label>
                <input type="email" class="form-control" id="email" name="email" placeholder="Enter your Email" required>
            </div>
            <div class="form-group">
                <label for="password"><i class="fas fa-lock"></i> Password</label>
                <input type="password" class="form-control" id="password" name="password" placeholder="Enter your Password" required>
            </div>
            <button type="submit" class="btn btn-primary btn-block"><i class="fas fa-paper-plane"></i> Register</button>
        </form>
        <div class="text-center mt-3">
            <p>Already registered? <a href="login.php" style="color: #fff; text-decoration: underline;">Login into your account</a></p>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.3/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>
