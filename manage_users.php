<?php
session_start();
include 'db.php';

// Import PHPMailer classes into the global namespace
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require __DIR__ . '/vendor/autoload.php';

// Ensure the user is an admin
if (!isset($_SESSION['username']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit();
}

$message = ''; // Initialize the message variable

// Handle user operations
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Add user
    if (isset($_POST['add_user'])) {
        $username = $_POST['username'];
        $email = $_POST['email'];
        $password = password_hash($_POST['password'], PASSWORD_BCRYPT);
        $fullname = $_POST['fullname'];
        $age = $_POST['age'];
        $contact_number = $_POST['contact_number'];
        $complete_address = $_POST['complete_address'];
        $role = $_POST['role'];

        // Handle file uploads
        $drivers_license = $_FILES['drivers_license']['name'];
        $barangay_clearance = $_FILES['barangay_clearance']['name'];
        $target_dir = "uploads/";

        // Move uploaded files to target directory
        move_uploaded_file($_FILES['drivers_license']['tmp_name'], $target_dir . basename($drivers_license));
        move_uploaded_file($_FILES['barangay_clearance']['tmp_name'], $target_dir . basename($barangay_clearance));

        $sql = "INSERT INTO users (username, email, password, fullname, age, contact_number, complete_address, role, drivers_license, barangay_clearance, verified) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 0)";
        $stmt = $conn->prepare($sql);

        if ($stmt) {
            $stmt->bind_param('ssssissssss', $username, $email, $password, $fullname, $age, $contact_number, $complete_address, $role, $drivers_license, $barangay_clearance);

            if ($stmt->execute()) {
                $message = "User added successfully!";
            } else {
                $message = "Error adding user: " . $stmt->error;
            }
            $stmt->close(); // Close the statement here
        } else {
            $message = "Error preparing the query: " . $conn->error;
        }
    }

    // Edit user
    if (isset($_POST['edit_user'])) {
        $username = $_POST['username'];
        $fullname = $_POST['fullname'];
        $email = $_POST['email'];
        $age = $_POST['age'];
        $contact_number = $_POST['contact_number'];
        $complete_address = $_POST['complete_address'];
        $role = $_POST['role'];

        $sql = "UPDATE users SET fullname=?, email=?, age=?, contact_number=?, complete_address=?, role=? WHERE username=?";
        $stmt = $conn->prepare($sql);

        if ($stmt) {
            $stmt->bind_param('sssssss', $fullname, $email, $age, $contact_number, $complete_address, $role, $username);

            if ($stmt->execute()) {
                $message = "User updated successfully!";
            } else {
                $message = "Error updating user: " . $stmt->error;
            }
            $stmt->close();
        } else {
            $message = "Error preparing the query: " . $conn->error;
        }
    }

    // Delete user
    if (isset($_POST['delete_user'])) {
        $username = $_POST['username'];
        $sql = "DELETE FROM users WHERE username=?";
        $stmt = $conn->prepare($sql);

        if ($stmt) {
            $stmt->bind_param('s', $username);

            if ($stmt->execute()) {
                $message = "User deleted successfully!";
            } else {
                $message = "Error deleting user: " . $stmt->error;
            }
            $stmt->close();
        } else {
            $message = "Error preparing the query: " . $conn->error;
        }
    }

    // Verify user
    if (isset($_POST['verify_user'])) {
        $username = $_POST['username'];
        $sql = "UPDATE users SET verified=1 WHERE username=?";
        $stmt = $conn->prepare($sql);

        if ($stmt) {
            $stmt->bind_param('s', $username);

            if ($stmt->execute()) {
                $message = "User verified successfully!";
                // Fetch user details (email and fullname) to send the notification
                $sql = "SELECT email, fullname FROM users WHERE username=?";
                $stmt2 = $conn->prepare($sql);

                if ($stmt2) {
                    $stmt2->bind_param('s', $username);
                    $stmt2->execute();
                    $stmt2->store_result();
                    $stmt2->bind_result($email, $fullname);
                    $stmt2->fetch();

                    // Send email notification
                    sendVerificationEmail($email, $fullname);

                    $stmt2->close();
                } else {
                    $message = "Error fetching user details: " . $conn->error;
                }
            } else {
                $message = "Error verifying user: " . $stmt->error;
            }
            $stmt->close();
        } else {
            $message = "Error preparing the query: " . $conn->error;
        }
    }
}

// Fetch all users
$users_sql = "SELECT username, email, fullname, age, contact_number, complete_address, role, verified FROM users";
$users_result = $conn->query($users_sql);
$conn->close();

function sendVerificationEmail($email, $fullname)
{
    $mail = new PHPMailer(true);

    try {
        // Server settings
        $mail->isSMTP();                                            // Send using SMTP
        $mail->Host = 'smtp.gmail.com';                               // Set the SMTP server to Gmail
        $mail->SMTPAuth = true;                                       // Enable SMTP authentication
        $mail->Username = 'loretonowls19@gmail.com';                     // Your Gmail address
        $mail->Password = 'beqv jvyt uzmi iyst';                      // Your Gmail password
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;           // Enable TLS encryption
        $mail->Port = 587;                                           // TCP port for TLS

        // Recipients
        $mail->setFrom('loretonowls19@gmail.com', 'Notification');
        $mail->addAddress($email, $fullname);                         // Add recipient's email

        // Content
        $mail->isHTML(true);                                          // Set email format to HTML
        $mail->Subject = 'User Verified';
        $mail->Body    = "Hello $fullname,<br><br>Your account has been successfully verified.";

        // Send the email
        $mail->send();
    } catch (Exception $e) {
        echo "Message could not be sent. Mailer Error: {$mail->ErrorInfo}";
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Users</title>
    <link href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.4/css/jquery.dataTables.min.css">

    <style>
        body {
            background-color: #e9ecef;
            font-family: 'Arial', sans-serif;
        }

        .sidebar {
            width: 240px;
            background-color: #343a40;
            color: #fff;
            padding: 20px;
            position: fixed;
            top: 0;
            left: 0;
            height: 100%;
            overflow-y: auto;
            transition: background-color 0.3s;
        }

        .sidebar h2 {
            margin-top: 0;
            font-size: 1.5rem;
        }

        .sidebar a {
            color: #fff;
            text-decoration: none;
            padding: 10px;
            display: block;
            border-radius: 5px;
            transition: background-color 0.3s;
        }

        .sidebar a:hover {
            background-color: #495057;
        }

        .content {
            margin-left: 250px;
            padding: 20px;
        }


        .btn-custom {
            margin-right: 5px;
        }

        .modal-header {
            background-color: #343a40;
            color: white;
        }

        .modal-footer {
            border-top: none;
        }

        .alert {
            display: none;
            /* Initially hidden */
        }
        .nav-link.active {
            background-color: #007bff !important;
            /* Bootstrap primary color */
            color: white !important;
            border-radius: 5px;
        }
    </style>
</head>

<body>
<div class="sidebar">
    <h2>Admin Menu</h2>
    <ul class="nav flex-column">
        <?php 
        $current_page = basename($_SERVER['PHP_SELF']); 
        ?>
        <li class="nav-item">
            <a class="nav-link <?php echo ($current_page == 'admin_dashboard.php') ? 'active' : ''; ?>" href="admin_dashboard.php">
                <i class="fas fa-tachometer-alt"></i> Dashboard
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link <?php echo ($current_page == 'manage_vehicles.php') ? 'active' : ''; ?>" href="manage_vehicles.php">
                <i class="fas fa-car"></i> Manage Vehicles
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link <?php echo ($current_page == 'manage_users.php') ? 'active' : ''; ?>" href="manage_users.php">
                <i class="fas fa-users"></i> Manage Users
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link <?php echo ($current_page == 'manage_bookings.php') ? 'active' : ''; ?>" href="manage_bookings.php">
                <i class="fas fa-book"></i> Manage Bookings
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link <?php echo ($current_page == 'vehicle_inventory.php') ? 'active' : ''; ?>" href="vehicle_inventory.php">
                <i class="fas fa-book"></i> Vehicle Inventory
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link <?php echo ($current_page == 'manage_testimonials.php') ? 'active' : ''; ?>" href="manage_testimonials.php">
                <i class="fas fa-comments"></i> Manage Testimonials
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link <?php echo ($current_page == 'manage_contact_us.php') ? 'active' : ''; ?>" href="manage_contact_us.php">
                <i class="fas fa-envelope-open-text"></i> Manage Contact Us
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link" href="logout.php">
                <i class="fas fa-sign-out-alt"></i> Logout
            </a>
        </li>
    </ul>
</div>

    <div class="content">
        <h1 class="text-center">Manage Users</h1>
        <div class="alert alert-info"><?php echo htmlspecialchars($message); ?></div>
        <button class="btn btn-primary mb-3" data-toggle="modal" data-target="#addUserModal"><i class="fas fa-plus"></i> Add New User</button>


        <table id="user_table" class="display">
            <thead>
                <tr>
                    <th>Full Name</th>
                    <th>Username</th>
                    <th>Email</th>
                    <th>Age</th>
                    <th>Contact Number</th>
                    <th>Address</th>
                    <th>Role</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($user = $users_result->fetch_assoc()): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($user['fullname']); ?></td>
                        <td><?php echo htmlspecialchars($user['username']); ?></td>
                        <td><?php echo htmlspecialchars($user['email']); ?></td>
                        <td><?php echo htmlspecialchars($user['age']); ?></td>
                        <td><?php echo htmlspecialchars($user['contact_number']); ?></td>
                        <td><?php echo htmlspecialchars($user['complete_address']); ?></td>
                        <td><?php echo htmlspecialchars($user['role']); ?></td>
                        <td class="d-flex gap-2">
                            <a href="#" class="btn btn-primary btn-custom btn-sm " onclick='editUser(<?php echo json_encode($user); ?>)'>
                                <i class="fas fa-edit"></i> Edit
                            </a>

                            <a href="manage_users.php?delete_user=1&username=<?php echo urlencode($user['username']); ?>"
                                class="btn btn-danger btn-custom btn-sm "
                                onclick="return confirm('Are you sure you want to delete this user?');">
                                <i class="fas fa-trash"></i> Delete
                            </a>

                            <?php if (!$user['verified']): ?>
                                <a href="manage_users.php?verify_user=1&username=<?php echo urlencode($user['username']); ?>"
                                    class="btn btn-success btn-custom btn-sm">
                                    <i class="fas fa-check"></i> Verify
                                </a>
                            <?php else: ?>
                                <a href="#" class="btn btn-success btn-custom btn-sm disabled">
                                    <i class="fas fa-check"></i> Verified
                                </a>
                            <?php endif; ?>
                        </td>

                    </tr>
                <?php endwhile; ?>

            </tbody>

        </table>


        <!-- Modal for adding a new user -->
        <div id="addUserModal" class="modal fade" tabindex="-1" role="dialog">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Add New User</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <form action="manage_users.php" method="post" enctype="multipart/form-data">
                            <div class="form-group">
                                <label for="username">Username</label>
                                <input type="text" class="form-control" name="username" required>
                            </div>
                            <div class="form-group">
                                <label for="email">Email</label>
                                <input type="email" class="form-control" name="email" required>
                            </div>
                            <div class="form-group">
                                <label for="password">Password</label>
                                <input type="password" class="form-control" name="password" required>
                            </div>
                            <div class="form-group">
                                <label for="fullname">Full Name</label>
                                <input type="text" class="form-control" name="fullname" required>
                            </div>
                            <div class="form-group">
                                <label for="age">Age</label>
                                <input type="number" class="form-control" name="age" required>
                            </div>
                            <div class="form-group">
                                <label for="contact_number">Contact Number</label>
                                <input type="text" class="form-control" name="contact_number" required>
                            </div>
                            <div class="form-group">
                                <label for="complete_address">Complete Address</label>
                                <textarea class="form-control" name="complete_address" required></textarea>
                            </div>
                            <div class="form-group">
                                <label for="role">Role</label>
                                <select class="form-control" name="role" required>
                                    <option value="admin">Admin</option>
                                    <option value="user">User</option>
                                </select>
                            </div>
                            <button type="submit" name="add_user" class="btn btn-primary">Add User</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <!-- Modal for editing a user -->
        <div id="editUserModal" class="modal fade" tabindex="-1" role="dialog">
            <div class="modal-dialog" role="document">
                <div class="modal-content" id="editUserContent">
                    <!-- Content will be dynamically inserted here -->
                </div>
            </div>
        </div>

        <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.3/dist/umd/popper.min.js"></script>
        <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
        <script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>


        <script>
            function editUser(user) {
                const formHtml = `
                    <div class="modal-header">
                        <h5 class="modal-title">Edit User</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <form action="manage_users.php" method="post">
                            <input type="hidden" name="username" value="${user.username}">
                            <div class="form-group">
                                <label for="fullname">Full Name</label>
                                <input type="text" class="form-control" name="fullname" value="${user.fullname}" required>
                            </div>
                            <div class="form-group">
                                <label for="email">Email</label>
                                <input type="email" class="form-control" name="email" value="${user.email}" required>
                            </div>
                            <div class="form-group">
                                <label for="age">Age</label>
                                <input type="number" class="form-control" name="age" value="${user.age}" required>
                            </div>
                            <div class="form-group">
                                <label for="contact_number">Contact Number</label>
                                <input type="text" class="form-control" name="contact_number" value="${user.contact_number}" required>
                            </div>
                            <div class="form-group">
                                <label for="complete_address">Address</label>
                                <textarea class="form-control" name="complete_address" required>${user.complete_address}</textarea>
                            </div>
                            <div class="form-group">
                                <label for="role">Role</label>
                                <select class="form-control" name="role" required>
                                    <option value="admin" ${user.role === 'admin' ? 'selected' : ''}>Admin</option>
                                    <option value="user" ${user.role === 'user' ? 'selected' : ''}>User</option>
                                </select>
                            </div>
                            <button type="submit" name="edit_user" class="btn btn-warning">Update User</button>
                        </form>
                    </div>
                `;
                document.getElementById('editUserContent').innerHTML = formHtml;
                $('#editUserModal').modal('show');
            }
        </script>
        <script>
            $(document).ready(function() {
                $('#user_table').DataTable();
            });
        </script>
    </div>
</body>

</html>