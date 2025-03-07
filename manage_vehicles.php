<?php 
session_start();
include 'db.php';

// Ensure the user is an admin
if (!isset($_SESSION['username']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit();
}

$message = '';

// Handle adding, editing, and deleting vehicles
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['add_vehicle'])) {
        // Add vehicle logic
        $target_dir = "uploads/";
        $target_file = $target_dir . basename($_FILES['vehicle_image']['name']);
        move_uploaded_file($_FILES['vehicle_image']['tmp_name'], $target_file);
        
        $stmt = $conn->prepare("INSERT INTO vehicles (plate_number, brand, model, year, status, price_per_day, owner, driver_name, vehicle_image, description, purpose) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param('sssssssssss', $_POST['plate_number'], $_POST['brand'], $_POST['model'], $_POST['year'], $_POST['status'], $_POST['price_per_day'], $_POST['owner'], $_POST['driver_name'], $target_file, $_POST['description'], $_POST['purpose']);
        $stmt->execute();
        $message = "Vehicle added successfully!";
        $stmt->close();
    } elseif (isset($_POST['edit_vehicle'])) {
        // Edit vehicle logic
        $vehicle_id = $_POST['vehicle_id'];
        $target_file = null;
        if (!empty($_FILES['vehicle_image']['name'])) {
            $target_file = "uploads/" . basename($_FILES['vehicle_image']['name']);
            move_uploaded_file($_FILES['vehicle_image']['tmp_name'], $target_file);
        }

        $sql = "UPDATE vehicles SET plate_number=?, brand=?, model=?, year=?, status=?, price_per_day=?, owner=?, driver_name=?, description=?, purpose=?";
        if ($target_file) {
            $sql .= ", vehicle_image=?";
        }
        $sql .= " WHERE vehicle_id=?";
        $stmt = $conn->prepare($sql);
        if ($target_file) {
            $stmt->bind_param('sssssssssssi', $_POST['plate_number'], $_POST['brand'], $_POST['model'], $_POST['year'], $_POST['status'], $_POST['price_per_day'], $_POST['owner'], $_POST['driver_name'], $_POST['description'], $_POST['purpose'], $target_file, $vehicle_id);
        } else {
            $stmt->bind_param('ssssssssssi', $_POST['plate_number'], $_POST['brand'], $_POST['model'], $_POST['year'], $_POST['status'], $_POST['price_per_day'], $_POST['owner'], $_POST['driver_name'], $_POST['description'], $_POST['purpose'], $vehicle_id);
        }
        $stmt->execute();
        $message = "Vehicle updated successfully!";
        $stmt->close();
    } elseif (isset($_POST['delete_vehicle'])) {
        // Delete vehicle logic
        $stmt = $conn->prepare("DELETE FROM vehicles WHERE vehicle_id=?");
        $stmt->bind_param('i', $_POST['vehicle_id']);
        $stmt->execute();
        $message = "Vehicle deleted successfully!";
        $stmt->close();
    }
}

// Fetch and categorize vehicles
$vehicle_sql = "SELECT * FROM vehicles";
$vehicle_result = $conn->query($vehicle_sql);

$categorized_vehicles = [
    'TOYOTA' => [],
    'FORD' => [],
    'NISSAN' => [],
    'HYUNDAI' => [],
    'MITSUBISHI' => [],
];

while ($vehicle = $vehicle_result->fetch_assoc()) {
    $categorized_vehicles[$vehicle['purpose']][] = $vehicle;
}

// Fetch promotions
$promotions_sql = "SELECT * FROM promotions";
$promotions_result = $conn->query($promotions_sql);

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>
    <link href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css" rel="stylesheet">
    <style>
        body {
            display: flex;
            height: 100vh;
            overflow: hidden;
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
        .content {
            margin-left: 240px; 
            padding: 20px;
            flex-grow: 1;
            background-color: #f8f9fa;
            overflow-y: auto;
            height: 100vh;
        }
        .vehicle-card {
            border: 1px solid #ddd;
            background-color: #fff;
            padding: 15px;
            margin: 10px 0;
            border-radius: 5px;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
            display: flex;
            align-items: center;
            transition: transform 0.2s;
        }
        .vehicle-card:hover {
            transform: scale(1.02);
        }
        .vehicle-card img {
            width: 150px;
            height: 150px;
            margin-right: 20px;
            border-radius: 5px;
        }
        .btn-book {
            background-color: #007bff;
            color: #fff;
            border: none;
        }
        .btn-book:hover {
            background-color: #0056b3;
        }
        .alert {
            margin-bottom: 20px;
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
    <div class="content">
        <h1 class="text-center">Manage Vehicles</h1>
        <div class="alert alert-info"><?php echo htmlspecialchars($message); ?></div>
        <button class="btn btn-primary mb-3" data-toggle="modal" data-target="#addForm">
            <i class="fas fa-plus"></i> Add New Vehicle
        </button>

        <?php foreach ($categorized_vehicles as $purpose => $vehicles): ?>
            <h2><?php echo ucfirst(htmlspecialchars($purpose)); ?> Vehicles</h2>
            <div class="row">
                <?php foreach ($vehicles as $vehicle): ?>
                    <div class="col-md-4 mb-4">
                        <div class="vehicle-card">
                            <img src="<?php echo htmlspecialchars($vehicle['vehicle_image']); ?>" alt="Vehicle Image">
                            <div>
                                <h3><?php echo htmlspecialchars($vehicle['brand'] . ' ' . $vehicle['model']); ?></h3>
                                <p><strong>Plate Number:</strong> <?php echo htmlspecialchars($vehicle['plate_number']); ?></p>
                                <p><strong>Driver:</strong> <?php echo htmlspecialchars($vehicle['driver_name']); ?></p>
                                <p><strong>Status:</strong> <?php echo ucfirst(htmlspecialchars($vehicle['status'])); ?></p>
                                <p><strong>Rental Fee/Day:</strong> ₱<?php echo number_format(htmlspecialchars($vehicle['price_per_day']), 2); ?></p>
                                <p><strong>Description:</strong> <?php echo htmlspecialchars($vehicle['description']); ?></p>
                                <button class="btn btn-warning" data-toggle="modal" data-target="#editForm<?php echo $vehicle['vehicle_id']; ?>">Edit</button>
                                <form action="" method="POST" style="display:inline;">
                                    <input type="hidden" name="vehicle_id" value="<?php echo $vehicle['vehicle_id']; ?>">
                                    <button class="btn btn-danger" name="delete_vehicle">Delete</button>
                                </form>
                            </div>
                        </div>
                    </div>

                    <!-- Image Modal -->
                    <div class="modal fade" id="imageModal<?php echo $vehicle['vehicle_id']; ?>" tabindex="-1" role="dialog">
                        <div class="modal-dialog modal-lg" role="document">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title">Vehicle Image</h5>
                                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                        <span aria-hidden="true">&times;</span>
                                    </button>
                                </div>
                                <div class="modal-body">
                                    <img src="<?php echo htmlspecialchars($vehicle['vehicle_image']); ?>" alt="Vehicle Image" class="img-fluid">
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endforeach; ?>
    </div>

    <!-- Modal for Adding Vehicle -->
    <div class="modal fade" id="addForm" tabindex="-1" role="dialog">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Add Vehicle</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <form action="" method="POST" enctype="multipart/form-data">
                    <div class="modal-body">
                        <div class="form-group">
                            <label for="plate_number">Plate Number</label>
                            <input type="text" class="form-control" name="plate_number" required>
                        </div>
                        <div class="form-group">
                            <label for="brand">Brand</label>
                            <input type="text" class="form-control" name="brand" required>
                        </div>
                        <div class="form-group">
                            <label for="model">Model</label>
                            <input type="text" class="form-control" name="model" required>
                        </div>
                        <div class="form-group">
                            <label for="year">Year</label>
                            <input type="text" class="form-control" name="year" required>
                        </div>
                        <div class="form-group">
                            <label for="status">Status</label>
                            <select class="form-control" name="status" required>
                                <option value="Available">Available</option>
                                <option value="Unavailable">Unavailable</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="price_per_day">Rental fee/Day</label>
                            <input type="number" class="form-control" name="price_per_day" required>
                        </div>
                        <div class="form-group">
                            <label for="owner">Owner</label>
                            <input type="text" class="form-control" name="owner" required>
                        </div>
                        <div class="form-group">
                            <label for="driver_name">Driver Name</label>
                            <input type="text" class="form-control" name="driver_name" required>
                        </div>
                        <div class="form-group">
                            <label for="vehicle_image">Vehicle Image</label>
                            <input type="file" class="form-control-file" name="vehicle_image" required>
                        </div>
                        <div class="form-group">
                            <label for="description">Description</label>
                            <textarea class="form-control" name="description" rows="3" required></textarea>
                        </div>
                        <div class="form-group">
                            <label for="purpose">Purpose</label>
                            <select class="form-control" name="purpose" required>
                                <option value="TOYOTA">TOYOTA</option>
                                <option value="FORD">FORD</option>
                                <option value="NISSAN">NISSAN</option>
                                <option value="HYUNDAI">HYUNDAI</option>
                                <option value="MITSUBISHI">MITSUBISHI</option>
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-primary" name="add_vehicle">Add Vehicle</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal for Editing Vehicle -->
    <?php foreach ($categorized_vehicles as $purpose => $vehicles): ?>
        <?php foreach ($vehicles as $vehicle): ?>
            <div class="modal fade" id="editForm<?php echo $vehicle['vehicle_id']; ?>" tabindex="-1" role="dialog">
                <div class="modal-dialog modal-lg" role="document">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title">Edit Vehicle</h5>
                            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                        <form action="" method="POST" enctype="multipart/form-data">
                            <div class="modal-body">
                                <input type="hidden" name="vehicle_id" value="<?php echo $vehicle['vehicle_id']; ?>">
                                <div class="form-group">
                                    <label for="plate_number">Plate Number</label>
                                    <input type="text" class="form-control" name="plate_number" value="<?php echo $vehicle['plate_number']; ?>" required>
                                </div>
                                <div class="form-group">
                                    <label for="brand">Brand</label>
                                    <input type="text" class="form-control" name="brand" value="<?php echo $vehicle['brand']; ?>" required>
                                </div>
                                <div class="form-group">
                                    <label for="model">Model</label>
                                    <input type="text" class="form-control" name="model" value="<?php echo $vehicle['model']; ?>" required>
                                </div>
                                <div class="form-group">
                                    <label for="year">Year</label>
                                    <input type="text" class="form-control" name="year" value="<?php echo $vehicle['year']; ?>" required>
                                </div>
                                <div class="form-group">
                                    <label for="status">Status</label>
                                    <select class="form-control" name="status" required>
                                        <option value="Available" <?php echo ($vehicle['status'] == 'Available') ? 'selected' : ''; ?>>Available</option>
                                        <option value="Unavailable" <?php echo ($vehicle['status'] == 'Unavailable') ? 'selected' : ''; ?>>Unavailable</option>
                                    </select>
                                </div>
                                <div class="form-group">
                                    <label for="price_per_day">Rental fee/Day</label>
                                    <input type="number" class="form-control" name="price_per_day" value="<?php echo $vehicle['price_per_day']; ?>" required>
                                </div>
                                <div class="form-group">
                                    <label for="owner">Owner</label>
                                    <input type="text" class="form-control" name="owner" value="<?php echo $vehicle['owner']; ?>" required>
                                </div>
                                <div class="form-group">
                                    <label for="driver_name">Driver Name</label>
                                    <input type="text" class="form-control" name="driver_name" value="<?php echo $vehicle['driver_name']; ?>" required>
                                </div>
                                <div class="form-group">
                                    <label for="vehicle_image">Vehicle Image</label>
                                    <input type="file" class="form-control-file" name="vehicle_image">
                                </div>
                                <div class="form-group">
                                    <label for="description">Description</label>
                                    <textarea class="form-control" name="description" rows="3"><?php echo $vehicle['description']; ?></textarea>
                                </div>
                                <div class="form-group">
                                    <label for="purpose">Purpose</label>
                                    <select class="form-control" name="purpose" required>
                                        <option value="TOYOTA" <?php echo ($vehicle['purpose'] == 'TOYOTA') ? 'selected' : ''; ?>>TOYOTA</option>
                                        <option value="FORD" <?php echo ($vehicle['purpose'] == 'FORD') ? 'selected' : ''; ?>>FORD</option>
                                        <option value="NISSAN" <?php echo ($vehicle['purpose'] == 'NISSAN') ? 'selected' : ''; ?>>NISSAN</option>
                                        <option value="HYUNDAI" <?php echo ($vehicle['purpose'] == 'HYUNDAI') ? 'selected' : ''; ?>>HYUNDAI</option>
                                        <option value="MITSUBISHI" <?php echo ($vehicle['purpose'] == 'MITSUBISHI') ? 'selected' : ''; ?>>MITSUBISHI</option>
                                    </select>
                                </div>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                                <button type="submit" class="btn btn-primary" name="edit_vehicle">Save Changes</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    <?php endforeach; ?>

    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.3/dist/umd/popper.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>
