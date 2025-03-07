<?php
session_start();
include 'db.php';

// Check if the user is an admin
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header('Location: login.php');
    exit();
}

// Fetch all contact inquiries from the database
$stmt = $conn->prepare("SELECT * FROM contact_us ORDER BY created_at DESC");
$stmt->execute();

// Use mysqli_fetch_all instead of fetchAll for mysqli
$contactInquiries = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

// Handle deletion
if (isset($_GET['delete_id'])) {
    $deleteStmt = $conn->prepare("DELETE FROM contact_us WHERE id = ?");
    $deleteStmt->bind_param("i", $_GET['delete_id']); // Bind parameter for integer
    $deleteStmt->execute();
    header('Location: manage_Contact_Us.php');
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Contact Us</title>
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
    <style>
        body {
            padding-left: 240px; 
        }
        .sidebar {
            width: 240px; 
            background-color: #343a40;
            color: #fff;
            position: fixed;
            top: 0;
            left: 0;
            height: 100%;
            padding: 20px;
            overflow-y: auto;
        }
        .sidebar h2 {
            font-size: 1.5rem;
            color: #ffdd57; 
        }
        .sidebar a {
            color: #fff;
            text-decoration: none;
        }
        .sidebar a:hover {
            background-color: #495057;
            padding: 10px;
            border-radius: 5px;
        }
        .table th, .table td {
            vertical-align: middle;
        }
    </style>
</head>
<body>
    <div class="sidebar">
        <h2>Admin Menu</h2>
        <ul class="nav flex-column">
            <li class="nav-item"><a class="nav-link" href="admin_dashboard.php"><i class="fas fa-tachometer-alt"></i> Dashboard</a></li>
            <li class="nav-item"><a class="nav-link" href="manage_vehicles.php"><i class="fas fa-car"></i> Manage Vehicles</a></li>
            <li class="nav-item"><a class="nav-link" href="manage_users.php"><i class="fas fa-users"></i> Manage Users</a></li>
            <li class="nav-item"><a class="nav-link" href="manage_bookings.php"><i class="fas fa-book"></i> Manage Bookings</a></li>
            <li class="nav-item"><a class="nav-link" href="vehicle_inventory.php"><i class="fas fa-book"></i> Vehicle Inventory</a></li>
            <li class="nav-item"><a class="nav-link" href="manage_testimonials.php"><i class="fas fa-comments"></i> Manage Testimonials</a></li>
            <li class="nav-item"><a class="nav-link" href="manage_contact_us.php"><i class="fas fa-envelope-open-text"></i> Manage Contact Us</a></li>
            <li class="nav-item"><a class="nav-link" href="logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
        </ul>
    </div>

    <div class="container mt-5">
        <h1 class="text-center">Manage Inquiries</h1>

        <table class="table table-bordered">
            <thead class="thead-dark">
                <tr>
                    <th>ID</th>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Message</th>
                    <th>Date</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (count($contactInquiries) > 0): ?>
                    <?php foreach ($contactInquiries as $row): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($row['id']); ?></td>
                            <td><?php echo htmlspecialchars($row['name']); ?></td>
                            <td><?php echo htmlspecialchars($row['email']); ?></td>
                            <td><?php echo htmlspecialchars($row['message']); ?></td>
                            <td><?php echo htmlspecialchars(date('Y-m-d', strtotime($row['created_at']))); ?></td>
                            <td>
                                <a href="?delete_id=<?php echo $row['id']; ?>" class="btn btn-danger btn-sm" onclick="return confirm('Are you sure you want to delete this inquiry?');">Delete</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="6" class="text-center">No Contact Us inquiries found.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.6/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>
