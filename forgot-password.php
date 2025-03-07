<?php
// Import PHPMailer classes into the global namespace
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require __DIR__ . '/vendor/autoload.php';

session_start();

$conn = new mysqli("localhost", "root", "", "vehicle_rental_system");

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['send_otp'])) {
        // Step 1: Validate email and check if it exists
        $email = $_POST['email'];
        $stmt = $conn->prepare("SELECT username FROM users WHERE email = ?");
        
        // Check if prepare failed
        if ($stmt === false) {
            die("Prepare failed: (" . $conn->errno . ") " . $conn->error);
        }

        $stmt->bind_param("s", $email);
        $stmt->execute();
        $stmt->store_result();

        // Check if email exists
        if ($stmt->num_rows > 0) {
            // Generate OTP
            $otp = rand(100000, 999999);
            $_SESSION['otp'] = $otp;
            $_SESSION['email'] = $email; // Save email to session

            // Send OTP via email
            $mail = new PHPMailer(true);
            try {
                $mail->isSMTP();
                $mail->Host       = 'smtp.gmail.com';
                $mail->SMTPAuth   = true;
                $mail->Username   = 'loretonowls19@gmail.com'; // Your Gmail address
                $mail->Password   = 'beqv jvyt uzmi iyst'; // Your Gmail password or App Password
                $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
                $mail->Port       = 587;

                // Recipients
                $mail->setFrom('loretonowls19@gmail.com', 'Notification');
                $mail->addAddress($email);

                // Content
                $mail->isHTML(false);
                $mail->Subject = 'Your Password Reset OTP';
                $mail->Body    = 'Your OTP for password reset is: ' . $otp;

                $mail->send();
                echo 'OTP has been sent to your email.';

            } catch (Exception $e) {
                echo "Message could not be sent. Mailer Error: {$mail->ErrorInfo}";
            }
        } else {
            echo "Email not found in the database.";
        }
        $stmt->close();
    } elseif (isset($_POST['reset_password'])) {
        // Step 3: Validate OTP and update password
        if ($_POST['otp'] == $_SESSION['otp']) {
            $new_password = password_hash($_POST['new_password'], PASSWORD_BCRYPT);
            $email = $_SESSION['email'];

            // Update password in the database
            $stmt = $conn->prepare("UPDATE users SET password = ? WHERE email = ?");
            if ($stmt === false) {
                die("Prepare failed: (" . $conn->errno . ") " . $conn->error);
            }

            // Bind parameters
            if (!$stmt->bind_param("ss", $new_password, $email)) {
                die("Binding parameters failed: (" . $stmt->errno . ") " . $stmt->error);
            }

            if ($stmt->execute()) {
                echo "Password has been reset successfully.";
                // Clear session data
                unset($_SESSION['otp']);
                unset($_SESSION['email']);
            } else {
                echo "Error updating password: " . $stmt->error;
            }
            $stmt->close();
        } else {
            echo "Invalid OTP.";
        }
    }
}

$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Password</title>
    <!-- Bootstrap CSS -->
    <link href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #f3f6f9;
            font-family: Arial, sans-serif;
        }
        .container {
            margin-top: 70px;
            max-width: 500px;
            background-color: #ffffff;
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            padding: 30px;
        }
        h2 {
            font-size: 1.8em;
            color: #333;
            margin-bottom: 20px;
            font-weight: 600;
            text-align: center;
        }
        .form-group label {
            font-weight: 500;
            color: #555;
        }
        .btn-primary, .btn-success {
            font-weight: 600;
            padding: 10px 15px;
            border-radius: 5px;
            transition: background-color 0.3s;
        }
        .btn-primary {
            background-color: #007bff;
            border: none;
        }
        .btn-primary:hover {
            background-color: #0056b3;
        }
        .btn-success {
            background-color: #28a745;
            border: none;
        }
        .btn-success:hover {
            background-color: #218838;
        }
        hr {
            border: 1px solid #e9ecef;
            margin: 30px 0;
        }
        .form-control {
            height: calc(2.25rem + 10px);
            border-radius: 4px;
            padding: 10px;
        }
        .text-muted {
            font-size: 0.9em;
            color: #6c757d;
            text-align: center;
        }
        .text-muted a {
            color: #007bff;
            text-decoration: none;
        }
        .text-muted a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <div class="container">
        <h2>Reset Password</h2>

        <!-- Email Form -->
        <form method="POST">
            <div class="form-group">
                <label for="email">Email:</label>
                <input type="email" class="form-control" id="email" name="email" required placeholder="Enter your email address">
            </div>
            <button type="submit" name="send_otp" class="btn btn-primary btn-block">Send OTP</button>
        </form>

        <hr>

        <!-- OTP and New Password Form -->
        <form method="POST">
            <div class="form-group">
                <label for="otp">OTP:</label>
                <input type="text" class="form-control" id="otp" name="otp" required placeholder="Enter OTP">
            </div>
            <div class="form-group">
                <label for="new_password">New Password:</label>
                <input type="password" class="form-control" id="new_password" name="new_password" required placeholder="Enter new password">
            </div>
            <button type="submit" name="reset_password" class="btn btn-success btn-block">Reset Password</button>
        </form>

        <p class="text-muted mt-4">Remembered your password? <a href="login.php">Back to Login</a></p>
    </div>

    <!-- Bootstrap JS and dependencies -->
    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.0.11/dist/umd/popper.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>
