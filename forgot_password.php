<?php
session_start();
include 'db.php'; // Ensure this file properly connects to your database

$message = '';
$error_message = '';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['email'])) {
    $email = $_POST['email'];

    // Validate email format
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error_message = "Invalid email format.";
    } else {
        // Check if email exists in the database
        $stmt = $conn->prepare("SELECT username FROM users WHERE email = ?");
        $stmt->bind_param('s', $email);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            // Generate a temporary password
            $temporary_password = bin2hex(random_bytes(5)); // Generate a random temporary password
            
            // Hash the temporary password
            $hashed_password = password_hash($temporary_password, PASSWORD_DEFAULT);

            // Update the user's password in the database
            $stmt = $conn->prepare("UPDATE users SET password = ?, is_temporary = 1 WHERE email = ?");
            $stmt->bind_param('ss', $hashed_password, $email);
            if ($stmt->execute()) {
                // Send email with the temporary password
                $subject = "Temporary Password";
                $message_body = "Your temporary password is: $temporary_password";
                if (mail($email, $subject, $message_body)) {
                    $message = "A temporary password has been sent to your email.";
                } else {
                    $error_message = "Failed to send email.";
                }
            } else {
                $error_message = "Error updating password: " . $stmt->error;
            }
        } else {
            $error_message = "Email not found.";
        }
        $stmt->close();
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Forgot Password</title>
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #f8f9fa;
        }
        .container {
            margin-top: 50px;
            max-width: 500px;
        }
    </style>
</head>
<body>
    <div class="container">
        <h2>Forgot Password</h2>
        <?php if ($error_message): ?>
            <div class="alert alert-danger"><?php echo htmlspecialchars($error_message); ?></div>
        <?php endif; ?>
        <?php if ($message): ?>
            <div class="alert alert-success"><?php echo htmlspecialchars($message); ?></div>
        <?php endif; ?>
        <form action="" method="post">
            <div class="form-group">
                <label for="email">Email address</label>
                <input type="email" class="form-control" name="email" required>
            </div>
            <button type="submit" class="btn btn-primary">Request Temporary Password</button>
        </form>
    </div>

    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.3/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>
