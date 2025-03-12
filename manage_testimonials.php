<?php
session_start();
include 'db.php';

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header('Location: login.php');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action'])) {
    $name = $_POST['name'];
    $message = $_POST['testimonial'];
    
    if ($_POST['action'] == 'add') {
        $stmt = $conn->prepare("INSERT INTO testimonials (name, message) VALUES (?, ?)");
        $stmt->bind_param("ss", $name, $message);
        $stmt->execute();
    } elseif ($_POST['action'] == 'edit') {
        $id = $_POST['id'];
        $stmt = $conn->prepare("UPDATE testimonials SET name=?, message=? WHERE id=?");
        $stmt->bind_param("ssi", $name, $message, $id);
        $stmt->execute();
    }
}

if (isset($_GET['delete_id'])) {
    $id = $_GET['delete_id'];
    $stmt = $conn->prepare("DELETE FROM testimonials WHERE id=?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
}

$query = "SELECT * FROM testimonials ORDER BY created_at DESC";
$result = $conn->query($query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Testimonials</title>
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

    <div class="container mt-5">
        <center><h1>Manage Testimonials</h1></center>
        

        <table class="table table-bordered">
            <thead class="thead-dark">
                <tr>
                    <th>ID</th>
                    <th>Name</th>
                    <th>Testimonial</th>
                    <th>Date</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($result->num_rows > 0): ?>
                    <?php while ($row = $result->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($row['id']); ?></td>
                            <td><?php echo htmlspecialchars($row['name']); ?></td>
                            <td><?php echo htmlspecialchars($row['message']); ?></td>
                            <td><?php echo htmlspecialchars(date('Y-m-d', strtotime($row['created_at']))); ?></td>
                            <td>
                                <button class="btn btn-warning btn-sm" data-toggle="modal" data-target="#editModal" data-id="<?php echo $row['id']; ?>" data-name="<?php echo htmlspecialchars($row['name']); ?>" data-testimonial="<?php echo htmlspecialchars($row['message']); ?>">Edit</button>
                                <a href="?delete_id=<?php echo $row['id']; ?>" class="btn btn-danger btn-sm" onclick="return confirm('Are you sure you want to delete this testimonial?');">Delete</a>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="5" class="text-center">No testimonials found.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <div class="modal fade" id="editModal" tabindex="-1" role="dialog" aria-labelledby="editModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <form method="POST">
                    <div class="modal-header">
                        <h5 class="modal-title" id="editModalLabel">Edit Testimonial</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" name="action" value="edit">
                        <input type="hidden" name="id" id="edit_id">
                        <div class="form-group">
                            <label for="edit_name">Name</label>
                            <input type="text" class="form-control" id="edit_name" name="name" required>
                        </div>
                        <div class="form-group">
                            <label for="edit_testimonial">Testimonial</label>
                            <textarea class="form-control" id="edit_testimonial" name="testimonial" rows="4" required></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-primary">Update Testimonial</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.6/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
    <script>
        $('#editModal').on('show.bs.modal', function (event) {
            var button = $(event.relatedTarget);
            var id = button.data('id');
            var name = button.data('name');
            var testimonial = button.data('testimonial');

            var modal = $(this);
            modal.find('#edit_id').val(id);
            modal.find('#edit_name').val(name);
            modal.find('#edit_testimonial').val(testimonial);
        });
    </script>
</body>
</html>
