<?php
include 'db.php'; // Database connection
session_start();

$error_message = ''; // Initialize error message

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = $_POST['username'];
    $password = $_POST['password'];

    // Prepare and execute the SQL statement
    $sql = "SELECT username, password, role, verified FROM users WHERE username = ?";
    if ($stmt = $conn->prepare($sql)) {
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $stmt->store_result();
        $stmt->bind_result($username_db, $password_db, $role, $verified);
        
        if ($stmt->num_rows > 0) {
            $stmt->fetch();

            // Check if the user is verified
            if ($verified == 0) {
                $error_message = "Your account is not yet verified. Please wait for the admin to verify your Account.";
            } else {
                // Verify the password
                if (password_verify($password, $password_db)) {
                    $_SESSION['username'] = $username_db;
                    $_SESSION['role'] = $role;

                    // Redirect based on user role
                    if ($role === 'admin') {
                        header("Location: admin_dashboard.php");
                    } elseif ($role === 'user') {
                        header("Location: user_dashboard.php");
                    } else {
                        header("Location: login.php"); // Default redirect
                    }
                    exit();
                } else {
                    $error_message = "Invalid password.";
                }
            }
        } else {
            $error_message = "No user found with that username.";
        }
        $stmt->close();
    } else {
        $error_message = "Error preparing statement: " . $conn->error;
    }
    $conn->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
    <!-- Bootstrap CSS -->
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome CSS -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" rel="stylesheet">
    <style>
        body {
    background-image: url('vehicle_images/palompon2.jpeg');
    background-size: cover; 
    background-position: center;
    background-repeat: no-repeat;
    background-attachment: fixed;
    color: #fff;
    font-family: Arial, sans-serif;
    height: 100vh;
    display: flex;
    flex-direction: column;
    justify-content: center;
    align-items: center;
}
        .welcome-text {
            text-align: center;
            margin-bottom: 50px;
            font-size: 2.5rem;
            font-weight: bold;
            color: black;
            text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.7);
        }
        .container {
            max-width: 500px;
            background: rgba(0, 0, 0, 0.5);
            padding: 40px;
            border-radius: 8px;
            box-shadow: 0 0 20px rgba(0, 0, 0, 0.5);
        }
        .error-message {
            color: #dc3545;
        }
        .input-group {
            margin-bottom: 15px;
        }
        .input-group-text {
            background: #007bff;
            border: none;
            color: #fff;
        }
    </style>
</head>
<body>
    <h1 class="welcome-text">Welcome to Palompon Vehicle Rental System</h1>
    <div class="container">
        <h2 class="text-center">Login</h2>
        <?php if ($error_message): ?>
            <div class="alert alert-danger error-message" role="alert">
                <?php echo htmlspecialchars($error_message); ?>
            </div>
        <?php endif; ?>
        <form action="login.php" method="post">
            <div class="input-group">
                <span class="input-group-text"><i class="fas fa-user"></i></span>
                <input type="text" class="form-control" id="username" name="username" placeholder="Enter your Username" required>
            </div>
            <div class="input-group">
                <span class="input-group-text"><i class="fas fa-lock"></i></span>
                <input type="password" class="form-control" id="password" name="password" placeholder="Enter your Password" required>
            </div>
            <button type="submit" class="btn btn-primary btn-block">Login</button>
        </form>
        <div class="text-center mt-2">
    <a href="forgot-password.php" class="text-white">Forgot Password?</a>
</div>
        <div class="text-center mt-3">
            <p>Not registered? <a href="register.php" class="text-white">Register Now!</a></p>
        </div>
    </div>
    <a href="index.php" class="btn btn-secondary mt-4">Return to Home</a>

    <!-- Bootstrap JS and dependencies -->
    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.3/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>
