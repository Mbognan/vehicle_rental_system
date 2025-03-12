<?php

session_start();
require_once __DIR__ . '/vendor/yidas/pagination/src/data/Pagination.php';

use yidas\data\Pagination;


$pagination = new Pagination(['totalCount' => 100, 'perPage' => 10]);
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

$perPage = 6;


$totalCountQuery = $conn->query("SELECT COUNT(*) as total FROM vehicles");
$totalCount = $totalCountQuery->fetch_assoc()['total'];

// Initialize Pagination
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;

$pagination = new Pagination([
    'totalCount' => $totalCount,
    'perPage' => $perPage
]);

$offset = ($pagination->page - 1) * $perPage;

// Fetch vehicles with limit and offset
$vehicle_sql = "SELECT * FROM vehicles LIMIT $perPage OFFSET $offset";
$vehicle_result = $conn->query($vehicle_sql);



// Categorize vehicles
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
    <link href="//maxcdn.bootstrapcdn.com/bootstrap/4.1.1/css/bootstrap.min.css" rel="stylesheet" id="bootstrap-css">
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
            margin-left: 260px;
            padding: 20px;
            flex-grow: 1;
            background-color: #f8f9fa;
            overflow-y: auto;
            height: 100vh;
        }

        body {
            font-family: 'Montserrat', sans-serif;

        }

        /* Category Ads */

        #ads {
            margin: 30px 0 30px 0;

        }

        #ads .card-notify-badge {
            position: absolute;
            left: -10px;
            top: -20px;
            background: #f2d900;
            text-align: center;
            border-radius: 30px 30px 30px 30px;
            color: #000;
            padding: 5px 10px;
            font-size: 14px;

        }

        #ads .card-notify-year {
            position: absolute;
            right: -10px;
            top: -20px;
            background: #ff4444;
            border-radius: 50%;
            text-align: center;
            color: #fff;
            font-size: 14px;
            width: 50px;
            height: 50px;
            padding: 15px 0 0 0;
        }


        #ads .card-detail-badge {
            background: #f2d900;
            text-align: center;
            border-radius: 30px 30px 30px 30px;
            color: #000;
            padding: 5px 10px;
            font-size: 14px;
        }



        #ads .card:hover {
            background: #fff;
            box-shadow: 12px 15px 20px 0px rgba(46, 61, 73, 0.15);
            border-radius: 4px;
            transition: all 0.3s ease;
        }

        #ads .card-image-overlay {
            font-size: 20px;

        }


        #ads .card-image-overlay span {
            display: inline-block;
        }


        #ads .ad-btn {
            text-transform: uppercase;
            width: 150px;
            height: 40px;
            border-radius: 80px;
            font-size: 16px;
            line-height: 35px;
            text-align: center;
            border: 3px solid #e6de08;
            display: block;
            text-decoration: none;
            margin: 20px auto 1px auto;
            color: #000;
            overflow: hidden;
            position: relative;
            background-color: #e6de08;
        }

        #ads .ad-btn:hover {
            background-color: #e6de08;
            color: #1e1717;
            border: 2px solid #e6de08;
            background: transparent;
            transition: all 0.3s ease;
            box-shadow: 12px 15px 20px 0px rgba(46, 61, 73, 0.15);
        }

        #ads .ad-title h5 {
            text-transform: uppercase;
            font-size: 18px;
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
        <h1 class="text-center">Manage Vehicles</h1>

        <button class="btn btn-primary mb-3" data-toggle="modal" data-target="#addForm">
            <i class="fas fa-plus"></i> Add New Vehicle
        </button>

        <div class="container">
            <br>
            <h4>Available Vehicles</h4>

            <!-- <input type="text" id="search" class="form-control mb-3" placeholder="Search Vehicles..."> -->
            <br>
            <div class="row" id="ads">
                <?php
                foreach ($categorized_vehicles as $purpose => $vehicles): ?>
                    <?php
                    foreach ($vehicles as $vehicle): ?>
                        <div class="col-md-4 mb-4">
                            <div class="card rounded" style="height:500px; width:100%;">
                                <!-- <div class="card vehicle-card" style="width: 100%; height: 500px; overflow: hidden;"> -->
                                <div class="card-image">
                                    <span class="card-notify-badge"><?php echo ucfirst(htmlspecialchars($purpose)); ?></span>
                                    <!-- <span class="card-notify-year"><?php echo date('Y', strtotime($vehicle['manufacture_year'] ?? '2024')); ?></span> -->
                                    <img class="img-fluid" src="<?php echo htmlspecialchars($vehicle['vehicle_image']); ?>" alt="Vehicle Image" style="width: 100%; height: 200px; object-fit: cover;" />
                                </div>
                                <div class="card-image-overlay m-auto">
                                    <span class="card-detail-badge"><?php echo ucfirst(htmlspecialchars($vehicle['status'])); ?></span>
                                    <span class="card-detail-badge">₱<?php echo number_format($vehicle['price_per_day'], 2); ?>/day</span>

                                </div>
                                <div class="card-body text-center">
                                    <div class="ad-title m-auto">
                                        <h5><?php echo htmlspecialchars($vehicle['brand'] . ' ' . $vehicle['model']); ?></h5>
                                    </div>
                                    <p><strong>Plate Number:</strong> <?php echo htmlspecialchars($vehicle['plate_number']); ?></p>
                                    <p><strong>Driver:</strong> <?php echo htmlspecialchars($vehicle['driver_name']); ?></p>
                                    <p><strong>Description:</strong> <?php echo htmlspecialchars($vehicle['description']); ?></p>
                                    <div class="d-flex justify-content-center" style="gap: 10px;">
                                        <a href="#" style="height:32px" class="btn btn-warning btn-sm" data-bs-toggle="modal" data-bs-target="#editForm<?php echo $vehicle['vehicle_id']; ?>">
                                            Edit
                                        </a>

                                        <form action="" method="POST">
                                            <input type="hidden" name="vehicle_id" value="<?php echo $vehicle['vehicle_id']; ?>">
                                            <button class="btn btn-danger btn-sm" name="delete_vehicle">Delete</button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <!-- </div> -->
                    <?php endforeach; ?>
                <?php endforeach; ?>
            </div>
            <div class="d-flex justify-content-center">
                <!-- Pagination Links -->
                <nav>
                    <ul class="pagination">
                        <?php
                        // Calculate the total pages manually
                        $totalPages = ceil($pagination->totalCount / $perPage);

                        for ($i = 1; $i <= $totalPages; $i++) : ?>
                            <li class="page-item <?= ($pagination->page == $i) ? 'active' : '' ?>">
                                <a class="page-link" href="?page=<?= $i ?>"><?= $i ?></a>
                            </li>
                        <?php endfor; ?>
                    </ul>
                </nav>
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
            <script src="//maxcdn.bootstrapcdn.com/bootstrap/4.1.1/js/bootstrap.min.js"></script>
            <script src="//cdnjs.cloudflare.com/ajax/libs/jquery/3.2.1/jquery.min.js"></script>
</body>

</html>