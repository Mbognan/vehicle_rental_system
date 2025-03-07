<?php
session_start();

$servername = "localhost";
$username = "root";
$password = "";
$dbname = "vehicle_rental_system";

try {
    $pdo = new PDO("mysql:host=$servername;dbname=$dbname;charset=utf8", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Fetch all vehicles and their owners that have bookings, including booking details
    $stmt = $pdo->prepare("SELECT b.booking_id, b.vehicle_id, b.number_of_day, b.total_cost, b.start_date_and_time, b.end_date_and_time,
                                  v.brand, v.model, v.owner, v.price_per_day
                           FROM bookings b
                           JOIN vehicles v ON b.vehicle_id = v.vehicle_id
                           WHERE b.status IN ('confirmed', 'completed')");
    $stmt->execute();
    $bookings = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    $commission_rate = 0.10; // 10% commission rate
    $total_cost = 0;

    // Store vehicle data grouped by owner
    $owners_vehicles = [];
    
    // Loop through the bookings to calculate owner's income and commission
    foreach ($bookings as $booking) {
        $owner = $booking['owner'];

        // Group vehicles by owner
        if (!isset($owners_vehicles[$owner])) {
            $owners_vehicles[$owner] = [];
        }
        
        // Store each vehicle's information
        $owners_vehicles[$owner][] = $booking;

        // Track the total booking cost
        $total_cost += $booking['total_cost'];
    }
    
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Vehicle Inventory</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f8f9fa;
            margin: 0;
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
            color: #ffdd57; 
        }
        .sidebar ul {
            list-style-type: none;
            padding: 0;
        }
        .sidebar a {
            color: #fff;
            text-decoration: none;
            display: block;
            padding: 10px;
            border-radius: 5px;
            transition: background-color 0.3s;
        }
        .sidebar a:hover {
            background-color: #495057;
        }
        .content {
            margin-left: 240px; 
            padding: 20px;
        }
        h1 {
            text-align: center;
            color: #333;
        }
        h2 {
            text-align: center;
            color: #28a745;
        }
        .owner-container {
            margin: 20px;
            padding: 15px;
            background-color: #fff;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            border-radius: 8px;
        }
        .owner-header {
            font-size: 1.25rem;
            color: #333;
            margin-bottom: 15px;
        }
        .vehicle-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        .vehicle-table th, .vehicle-table td {
            padding: 10px;
            text-align: left;
            border: 1px solid #ddd;
        }
        .vehicle-table th {
            background-color: #f2f2f2;
        }
        .vehicle-table tr:nth-child(even) {
            background-color: #f9f9f9;
        }
        .vehicle-table tr:hover {
            background-color: #f1f1f1;
        }
        .total-row {
            font-weight: bold;
            background-color: #f9f9f9;
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
        <h1>Vehicle Booking Inventory</h1>
        <h2>Total Booking Cost: ₱<?php echo number_format($total_cost, 2); ?></h2>

        <?php foreach ($owners_vehicles as $owner => $vehicles): ?>
            <div class="owner-container">
                <div class="owner-header">
                    <strong>Owner: </strong><?php echo htmlspecialchars($owner); ?>
                </div>
                <table class="vehicle-table">
                    <thead>
                        <tr>
                            <th>Booking ID</th>
                            <th>Vehicle ID</th>
                            <th>Brand</th>
                            <th>Model</th>
                            <th>Number of Day(s)</th>
                            <th>Booking Cost</th>
                            <th>Start Date & Time</th>
                            <th>End Date & Time</th>
                            <th>Owner's Income (After Commission)</th>
                            <th>Rental Commission (10%)</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $owner_total_income = 0;
                        $owner_total_commission = 0;
                        foreach ($vehicles as $vehicle): 
                            $commission = $vehicle['total_cost'] * $commission_rate;
                            $owner_income = $vehicle['total_cost'] - $commission;

                            // Add up the totals for the owner
                            $owner_total_income += $owner_income;
                            $owner_total_commission += $commission;
                        ?>
                            <tr>
                                <td><?php echo htmlspecialchars($vehicle['booking_id']); ?></td>
                                <td><?php echo htmlspecialchars($vehicle['vehicle_id']); ?></td>
                                <td><?php echo htmlspecialchars($vehicle['brand']); ?></td>
                                <td><?php echo htmlspecialchars($vehicle['model']); ?></td>
                                <td><?php echo htmlspecialchars($vehicle['number_of_day']); ?> day(s)</td>
                                <td>₱<?php echo number_format($vehicle['total_cost'], 2); ?></td>
                                <td><?php echo htmlspecialchars($vehicle['start_date_and_time']); ?></td>
                                <td><?php echo htmlspecialchars($vehicle['end_date_and_time']); ?></td>
                                <td>₱<?php echo number_format($owner_income, 2); ?></td>
                                <td>₱<?php echo number_format($commission, 2); ?></td>
                            </tr>
                        <?php endforeach; ?>
                        <tr class="total-row">
                            <td colspan="8" style="text-align: right; font-weight: bold;">Total Income for <?php echo htmlspecialchars($owner); ?>:</td>
                            <td>₱<?php echo number_format($owner_total_income, 2); ?></td>
                            <td>₱<?php echo number_format($owner_total_commission, 2); ?></td>
                        </tr>
                    </tbody>
                </table>
            </div>
        <?php endforeach; ?>
    </div>
</body>
</html>
