<?php
session_start();
include 'db.php';

// Check if the user is logged in
if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit();
}

// Fetch user details
$username = $_SESSION['username'];
$sql = "SELECT email, fullname, age, contact_number, complete_address, role, drivers_license, barangay_clearance FROM users WHERE username=?";
$stmt = $conn->prepare($sql);
$stmt->bind_param('s', $username);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();
$stmt->close();

// Handle profile update
$message = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $fullname = $_POST['fullname'];
    $email = $_POST['email'];
    $age = $_POST['age'];
    $contact_number = $_POST['contact_number'];
    $complete_address = $_POST['complete_address'];

    $sql = "UPDATE users SET fullname=?, email=?, age=?, contact_number=?, complete_address=? WHERE username=?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('ssisss', $fullname, $email, $age, $contact_number, $complete_address, $username);

    if ($stmt->execute()) {
        $message = "Profile updated successfully!";
        // Refresh the user data
        $stmt->close();
        $stmt = $conn->prepare($sql);
        $stmt->bind_param('s', $username);
        $stmt->execute();
        $result = $stmt->get_result();
        $user = $result->fetch_assoc();
    } else {
        $message = "Error updating profile: " . $stmt->error;
    }
    $stmt->close();
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Profile</title>
    <link href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container">
        <h1 class="mt-4">My Profile</h1>
        <div class="alert alert-info"><?php echo htmlspecialchars($message); ?></div>

        <h2>Profile Information</h2>
        <table class="table">
            <tbody>
                <tr>
                    <th>Full Name</th>
                    <td><?php echo htmlspecialchars($user['fullname']); ?></td>
                </tr>
                <tr>
                    <th>Email</th>
                    <td><?php echo htmlspecialchars($user['email']); ?></td>
                </tr>
                <tr>
                    <th>Age</th>
                    <td><?php echo htmlspecialchars($user['age']); ?></td>
                </tr>
                <tr>
                    <th>Contact Number</th>
                    <td><?php echo htmlspecialchars($user['contact_number']); ?></td>
                </tr>
                <tr>
                    <th>Complete Address</th>
                    <td><?php echo htmlspecialchars($user['complete_address']); ?></td>
                </tr>
                <tr>
                    <th>Role</th>
                    <td><?php echo htmlspecialchars($user['role']); ?></td>
                </tr>
                <tr>
                    <th>Driver's License</th>
                    <td>
                        <?php if ($user['drivers_license']): ?>
                            <a href="uploads/<?php echo htmlspecialchars($user['drivers_license']); ?>" target="_blank">View Document</a>
                        <?php else: ?>
                            Not Uploaded
                        <?php endif; ?>
                    </td>
                </tr>
                <tr>
                    <th>Barangay Clearance</th>
                    <td>
                        <?php if ($user['barangay_clearance']): ?>
                            <a href="uploads/<?php echo htmlspecialchars($user['barangay_clearance']); ?>" target="_blank">View Document</a>
                        <?php else: ?>
                            Not Uploaded
                        <?php endif; ?>
                    </td>
                </tr>
            </tbody>
        </table>

        <h2>Update Profile</h2>
        <form action="my_profile.php" method="post">
            <div class="form-group">
                <label for="fullname">Full Name</label>
                <input type="text" class="form-control" name="fullname" value="<?php echo htmlspecialchars($user['fullname']); ?>" required>
            </div>
            <div class="form-group">
                <label for="email">Email</label>
                <input type="email" class="form-control" name="email" value="<?php echo htmlspecialchars($user['email']); ?>" required>
            </div>
            <div class="form-group">
                <label for="age">Age</label>
                <input type="number" class="form-control" name="age" value="<?php echo htmlspecialchars($user['age']); ?>" required>
            </div>
            <div class="form-group">
                <label for="contact_number">Contact Number</label>
                <input type="text" class="form-control" name="contact_number" value="<?php echo htmlspecialchars($user['contact_number']); ?>" required>
            </div>
            <div class="form-group">
                <label for="complete_address">Complete Address</label>
                <textarea class="form-control" name="complete_address" required><?php echo htmlspecialchars($user['complete_address']); ?></textarea>
            </div>
            <button type="submit" class="btn btn-primary">Update Profile</button>
        </form>
    </div>
    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.3/dist/umd/popper.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>
