<?php
// Start session and include database connection
include 'db.php';

// Initialize error and success messages
$error_message = '';  
$success_message = '';

// Check if user is logged in
if (!isset($_SESSION['username'])) {
    header('Location: login.php');  
    exit();
}

// Process the form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = $_SESSION['username'];
    $old_password = $_POST['old_password'];
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];

    // Validate that new passwords match
    if ($new_password !== $confirm_password) {
        $error_message = "New passwords do not match.";
    } else {
        // Fetch the old password from the database
        $sql = "SELECT password FROM users WHERE username = ?";
        if ($stmt = $conn->prepare($sql)) {
            $stmt->bind_param("s", $username);
            $stmt->execute();
            $stmt->bind_result($password_db);
            $stmt->fetch();
            $stmt->close();

            // Verify the old password
            if (password_verify($old_password, $password_db)) {
                // Hash the new password
                $hashed_new_password = password_hash($new_password, PASSWORD_DEFAULT);

                // Update the password in the database
                $update_sql = "UPDATE users SET password = ? WHERE username = ?";
                if ($update_stmt = $conn->prepare($update_sql)) {
                    $update_stmt->bind_param("ss", $hashed_new_password, $username);
                    if ($update_stmt->execute()) {
                        $success_message = "Password changed successfully.";
                    } else {
                        $error_message = "Error updating password.";
                    }
                    $update_stmt->close();
                }
            } else {
                $error_message = "Old password is incorrect.";
            }
        } else {
            $error_message = "Error preparing statement: " . $conn->error;
        }
    }
    $conn->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Change Password</title>

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">

    <style>
        .form-container {
            max-width: 400px;
            padding: 30px;
            border-radius: 12px;

        }

        .form-container h2 {
            margin-bottom: 20px;
            text-align: center;
        }

        .input-group-text {
            border: none;
        }

        .form-control {
            border: none;
        }

        .form-control:focus {
            border-color: #007bff;
            box-shadow: 0 0 5px rgba(0, 123, 255, 0.5);
        }

        .btn-primary {
            border: none;
        }

        /* .btn-primary:hover {
            background-color: #0056b3;
        } */

        .text-muted a {
            text-decoration: none;
        }

        /* .text-muted a:hover {
            color: #fff;
        } */
    </style>
</head>
<body>
    <div class="form-container">
        <h2 style="color:black">Change Password</h2>

        <?php if ($error_message): ?>
            <div class="alert alert-danger text-center" role="alert">
                <?php echo htmlspecialchars($error_message); ?>
            </div>
        <?php endif; ?>

        <?php if ($success_message): ?>
            <div class="alert alert-success text-center" role="alert">
                <?php echo htmlspecialchars($success_message); ?>
            </div>
        <?php endif; ?>

        <form action="" method="post">
            <div class="input-group mb-3">
                <span class="input-group-text"><i class="fas fa-key"></i></span>
                <input type="password" class="form-control" name="old_password" placeholder="Enter Old Password" required>
            </div>

            <div class="input-group mb-3">
                <span class="input-group-text"><i class="fas fa-lock"></i></span>
                <input type="password" class="form-control" name="new_password" placeholder="Enter New Password" required>
            </div>

            <div class="input-group mb-3">
                <span class="input-group-text"><i class="fas fa-lock"></i></span>
                <input type="password" class="form-control" name="confirm_password" placeholder="Confirm New Password" required>
            </div>

            <button type="submit" class="btn btn-primary w-100 mb-3">Change Password</button>
        </form>

        <div class="text-center text-muted">
            <p><a href="index.php">Back to Dashboard</a></p>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
