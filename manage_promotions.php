<?php 
session_start();
include 'db.php';

// Ensure the user is an admin
if (!isset($_SESSION['username']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit();
}

$message = ''; // Initialize the message variable

// Handle adding a promotion
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_promotion'])) {
    $code = $_POST['code'];
    $discount = $_POST['discount'];
    $start_date = $_POST['start_date'];
    $end_date = $_POST['end_date'];

    $sql = "INSERT INTO promotions (code, discount, start_date, end_date) VALUES (?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('sdsd', $code, $discount, $start_date, $end_date);

    if ($stmt->execute()) {
        $message = "Promotion added successfully!";
    } else {
        $message = "Error adding promotion: " . $stmt->error;
    }
    $stmt->close();
}

// Handle editing a promotion
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['edit_promotion'])) {
    $promotion_id = $_POST['promotion_id'];
    $code = $_POST['code'];
    $discount = $_POST['discount'];
    $start_date = $_POST['start_date'];
    $end_date = $_POST['end_date'];

    $sql = "UPDATE promotions SET code=?, discount=?, start_date=?, end_date=? WHERE promotion_id=?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('sdssi', $code, $discount, $start_date, $end_date, $promotion_id);

    if ($stmt->execute()) {
        $message = "Promotion updated successfully!";
    } else {
        $message = "Error updating promotion: " . $stmt->error;
    }
    $stmt->close();
}

// Handle deleting a promotion
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_promotion'])) {
    $promotion_id = $_POST['promotion_id'];
    $sql = "DELETE FROM promotions WHERE promotion_id=?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('i', $promotion_id);

    if ($stmt->execute()) {
        $message = "Promotion deleted successfully!";
    } else {
        $message = "Error deleting promotion: " . $stmt->error;
    }
    $stmt->close();
}

// Fetch all promotions
$promotions_sql = "SELECT * FROM promotions";
$promotions_result = $conn->query($promotions_sql);

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Promotions</title>
    <link href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            display: flex;
            height: 100vh;
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
        }
        .sidebar h2 {
            margin-top: 0;
            font-size: 1.5rem;
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
        .container {
            margin-left: 20px; 
            padding: 20px;
            flex-grow: 1;
            background-color: #f8f9fa;
        }
        .vehicle-card {
            border: 1px solid #ddd;
            border-radius: 5px;
            padding: 15px;
            margin: 10px 0;
            background-color: #fff;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            display: flex;
            align-items: center;
        }
        .vehicle-card img {
            width: 150px;
            height: 150px;
            margin-right: 20px;
            border-radius: 5px;
        }
        .modal-content {
            border-radius: 5px;
        }
    </style>
</head>
<body><div class="sidebar">
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
    <div class="container">
    <center><h1>Manage Promotions</h1></center>
        <div class="alert alert-info"><?php echo htmlspecialchars($message); ?></div>

        <h2>Add Promotion</h2>
        <form method="POST" action="">
            <div class="form-group">
                <label for="code">Promotion Code:</label>
                <input type="text" name="code" class="form-control" required>
            </div>
            <div class="form-group">
                <label for="discount">Discount (%):</label>
                <input type="number" name="discount" class="form-control" step="0.01" required>
            </div>
            <div class="form-group">
                <label for="start_date">Start Date:</label>
                <input type="date" name="start_date" class="form-control" required>
            </div>
            <div class="form-group">
                <label for="end_date">End Date:</label>
                <input type="date" name="end_date" class="form-control" required>
            </div>
            <button type="submit" name="add_promotion" class="btn btn-primary">Add Promotion</button>
        </form>

        <h2>Existing Promotions</h2>
        <table class="table table-striped">
            <thead>
                <tr>
                    <th>Promotion ID</th>
                    <th>Code</th>
                    <th>Discount (%)</th>
                    <th>Start Date</th>
                    <th>End Date</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($promotion = $promotions_result->fetch_assoc()): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($promotion['promotion_id']); ?></td>
                        <td><?php echo htmlspecialchars($promotion['code']); ?></td>
                        <td><?php echo htmlspecialchars($promotion['discount']); ?></td>
                        <td><?php echo htmlspecialchars($promotion['start_date']); ?></td>
                        <td><?php echo htmlspecialchars($promotion['end_date']); ?></td>
                        <td>
                            <!-- Edit button triggers modal -->
                            <button class="btn btn-warning" data-toggle="modal" data-target="#editModal<?php echo $promotion['promotion_id']; ?>">Edit</button>
                            <!-- Delete form -->
                            <form method="POST" style="display:inline;">
                                <input type="hidden" name="promotion_id" value="<?php echo $promotion['promotion_id']; ?>">
                                <button type="submit" name="delete_promotion" class="btn btn-danger">Delete</button>
                            </form>
                        </td>
                    </tr>

                    <!-- Edit Promotion Modal -->
                    <div class="modal fade" id="editModal<?php echo $promotion['promotion_id']; ?>" tabindex="-1" role="dialog">
                        <div class="modal-dialog" role="document">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title">Edit Promotion</h5>
                                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                        <span aria-hidden="true">&times;</span>
                                    </button>
                                </div>
                                <div class="modal-body">
                                    <form method="POST">
                                        <input type="hidden" name="promotion_id" value="<?php echo $promotion['promotion_id']; ?>">
                                        <div class="form-group">
                                            <label for="code">Promotion Code:</label>
                                            <input type="text" name="code" class="form-control" value="<?php echo htmlspecialchars($promotion['code']); ?>" required>
                                        </div>
                                        <div class="form-group">
                                            <label for="discount">Discount (%):</label>
                                            <input type="number" name="discount" class="form-control" value="<?php echo htmlspecialchars($promotion['discount']); ?>" step="0.01" required>
                                        </div>
                                        <div class="form-group">
                                            <label for="start_date">Start Date:</label>
                                            <input type="date" name="start_date" class="form-control" value="<?php echo htmlspecialchars($promotion['start_date']); ?>" required>
                                        </div>
                                        <div class="form-group">
                                            <label for="end_date">End Date:</label>
                                            <input type="date" name="end_date" class="form-control" value="<?php echo htmlspecialchars($promotion['end_date']); ?>" required>
                                        </div>
                                        <button type="submit" name="edit_promotion" class="btn btn-primary">Save Changes</button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>

    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.2/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>

<li class="nav-item">
                <a class="nav-link" href="manage_promotions.php"><i class="fas fa-tags"></i> Manage Promotions</a>
            </li>