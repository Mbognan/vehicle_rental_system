<?php
session_start();
include 'db.php'; // Ensure this file properly connects to your database

$message = '';
$error_message = '';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['token'], $_POST['new_password'])) {
    $token = $_POST['token'];
    $new_password = $_POST['new_password'];

    // Validate new password (you may want to add more validation rules)
    if (strlen($new_password) < 6) {
        $error_message = "Password must be at least 6 characters long.";
    } else {
        // Check if the token is valid
        $stmt = $conn->prepare("SELECT email FROM password_resets WHERE token = ? AND expiry > NOW()");
        $stmt->bind_param('s', $token);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $row = $result->fetch_assoc();
            $email = $row['email'];

            // Update the user's password
            $hashed_password = password_hash($new_password, PASSWORD_BCRYPT);
            $update_stmt = $conn->prepare("UPDATE users SET password = ? WHERE email = ?");
            $update_stmt->bind_param('ss', $hashed_password, $email);

            if ($update_stmt->execute()) {
                // Delete the reset token after successful password reset
                $delete_stmt = $conn->prepare("DELETE FROM password_resets WHERE token = ?");
                $delete_stmt->bind_param('s', $token);
                $delete_stmt->execute();

                $message = "Your password has been reset successfully. You can now log in.";
            } else {
                $error_message = "Error updating password: " . $update_stmt->error;
            }

            $update_stmt->close();
        } else {
            $error_message = "Invalid or expired token.";
        }
        $stmt->close();
    }
}

// Check if token is present in the URL
if (isset($_GET['token'])) {
    $token = $_GET['token'];
} else {
    $error_message = "No token provided.";
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Password</title>
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-5">
        <h2>Reset Password</h2>
        <?php if ($error_message): ?>
            <div class="alert alert-danger"><?php echo htmlspecialchars($error_message); ?></div>
        <?php endif; ?>
        <?php if ($message): ?>
            <div class="alert alert-success"><?php echo htmlspecialchars($message); ?></div>
        <?php endif; ?>
        
        <?php if (!isset($error_message) || empty($error_message)): ?>
            <form action="" method="post">
                <input type="hidden" name="token" value="<?php echo htmlspecialchars($token); ?>">
                <div class="form-group">
                    <label for="new_password">New Password</label>
                    <input type="password" class="form-control" name="new_password" required>
                </div>
                <button type="submit" class="btn btn-primary">Reset Password</button>
            </form>
        <?php endif; ?>
    </div>

    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.3/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>
