<?php
session_start();
include 'db.php'; 
// Check if vehicle_id is passed in the URL
if (isset($_GET['vehicle_id']) && isset($_GET['vehicle_name'])) {
    $vehicle_id = intval($_GET['vehicle_id']); // Ensure the vehicle_id is an integer
    $vehicle_name = intval($_GET['vehicle_name']);
    // Prepare the SQL query to get unavailable dates
    $sql = "SELECT start_date_and_time, end_date_and_time 
    FROM bookings 
    WHERE vehicle_id = ? AND status = 'confirmed'";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('i', $vehicle_id);
    $stmt->execute();
    $result = $stmt->get_result();

    // Fetch unavailable dates
    $unavailable_dates = [];
    while ($row = $result->fetch_assoc()) {
        // Store both start and end dates in a formatted manner
        $startDateTime = new DateTime($row['start_date_and_time']);
        $endDateTime = new DateTime($row['end_date_and_time']);
        
        $unavailable_dates[] = [
            'start' => $startDateTime->format('F j, Y g:i A'), // Formats to "March 23, 2024 8:40 PM"
            'end' => $endDateTime->format('F j, Y g:i A'),     // Formats to "March 23, 2024 8:40 PM"
        ];
    }
    // Close the statement and connection
    $stmt->close();
    $conn->close();
} else {
    echo "No vehicle ID provided.";
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Unavailable Dates</title>
    <link rel="stylesheet" href="path/to/your/css/styles.css"> <!-- Link to your CSS file -->
    <style>
        body { font-family: Arial, sans-serif; background-color: #f4f4f4; }
        .container { max-width: 600px; margin: 50px auto; padding: 20px; background: #fff; border-radius: 8px; box-shadow: 0 0 10px rgba(0,0,0,0.1); }
        h1 { color: #333; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { padding: 10px; text-align: left; border: 1px solid #ddd; }
        th { background-color: #f2f2f2; }
        a { display: inline-block; margin-top: 20px; padding: 10px 15px; background: #007bff; color: #fff; text-decoration: none; border-radius: 5px; }
        a:hover { background: #0056b3; }
    </style>
</head>
<body>
    <div class="container">
        <h1>Unavailable Dates for Vehicle</h1>
        <?php if (!empty($unavailable_dates)): ?>
            <table>
                <thead>
                    <tr>
                        <th>Start Date and Time</th>
                        <th>End Date and Time</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($unavailable_dates as $date): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($date['start']); ?></td>
                            <td><?php echo htmlspecialchars($date['end']); ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php else: ?>
            <p>No unavailable dates found for this vehicle.</p>
        <?php endif; ?>
        <a href="available_vehicles.php">Go Back</a> <!-- Adjust this to your previous page -->
    </div>
</body>
</html>