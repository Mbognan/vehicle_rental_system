<?php
// Start the session
session_start();

// Database connection parameters
$servername = "localhost";
$username = "root"; 
$password = ""; 
$dbname = "vehicle_rental_system";

// Database connection and data fetching
try {
    // Create a new PDO instance
    $pdo = new PDO("mysql:host=$servername;dbname=$dbname;charset=utf8", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Query to fetch all vehicles including their purpose
    $stmt = $pdo->prepare("SELECT vehicle_image, brand, model, description, year, price_per_day, purpose FROM vehicles ORDER BY purpose");
    $stmt->execute();
    $vehicles = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
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
    <title>Vehicle List</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
    <style>
        /* General Styles */
        body {
            font-family: Arial, sans-serif;
            background-color: #f8f9fa;
            margin: 0;
        }
        .content {
            margin-left: 240px; 
            padding: 20px;
        }
        h1 {
            text-align: center;
            color: #333;
        }

        /* Table Styles */
        table {
            width: 90%;
            margin: 20px auto;
            border-collapse: collapse;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }
        th, td {
            border: 1px solid #ccc;
            padding: 10px;
            text-align: left;
        }
        th {
            background-color: #f2f2f2;
            color: #333;
        }
        tr:nth-child(even) {
            background-color: #f9f9f9;
        }
        tr:hover {
            background-color: #f1f1f1;
        }
        .purpose-header {
            background-color: #e2e6ea;
            font-weight: bold;
            text-align: left;
            padding: 5px;
        }
        .text-center {
            text-align: center;
            margin-top: 20px;
        }
        .btn {
            padding: 10px 20px;
            background-color: #6c757d;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
        }
        .btn:hover {
            background-color: #5a6268;
        }
    </style>
    <script>
        function goBack() {
            window.history.back();
        }
    </script>
</head>
<body>
    <div class="content">
        <h1>Vehicle List</h1>
        <table>
            <thead>
                <tr>
                    <th>Vehicle Image</th>
                    <th>Brand</th>
                    <th>Model</th>
                    <th>description</th>
                    <th>Year</th>
                    <th>Price per day</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($vehicles)): ?>
                    <tr>
                        <td colspan="5" style="text-align:center;">No vehicles found.</td>
                    </tr>
                <?php else: ?>
                    <?php 
                    $currentPurpose = ""; 
                    foreach ($vehicles as $vehicle): 
                        // Check if the purpose has changed to add a header row
                        if ($vehicle['purpose'] !== $currentPurpose): 
                            if ($currentPurpose !== ""): ?>
                                <tr><td colspan="5" style="height: 10px;"></td></tr> <!-- Add space between sections -->
                            <?php endif; ?>
                            <tr>
                                <td colspan="5" class="purpose-header"><?php echo htmlspecialchars($vehicle['purpose']); ?></td>
                            </tr>
                            <?php $currentPurpose = $vehicle['purpose']; 
                        endif; ?>
                        <tr>
                            <td><img src="<?php echo htmlspecialchars($vehicle['vehicle_image']); ?>" alt="Vehicle Image" style="width:100px; height:auto;"></td>
                            <td><?php echo htmlspecialchars($vehicle['brand']); ?></td>
                            <td><?php echo htmlspecialchars($vehicle['model']); ?></td>
                            <td><?php echo htmlspecialchars($vehicle['description']); ?></td>
                            <td><?php echo htmlspecialchars($vehicle['year']); ?></td>
                            <td>₱<?php echo number_format($vehicle['price_per_day'], 2); ?></td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
    <!-- Return Button -->
    <div class="text-center">
        <button class="btn" onclick="goBack()">Return</button>
    </div>
</body>
</html>
