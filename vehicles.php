<?php
include 'db.php';

// Fetch vehicles
$sql = "SELECT * FROM vehicles";
$result = $conn->query($sql);

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Potpot Vehicle Rental</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <div class="container">
        <h1 class="mt-5">Vehicle List</h1>
        <table class="table table-bordered mt-3">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Plate Number</th>
                    <th>Make</th>
                    <th>Model</th>
                    <th>Year</th>
                    <th>Status</th>
                    <th>Price</th>
                </tr>
            </thead>
            <tbody>
                <?php while($row = $result->fetch_assoc()): ?>
                    <tr>
                        <td><?php echo $row['id']; ?></td>
                        <td><?php echo $row['plate_number']; ?></td>
                        <td><?php echo $row['make']; ?></td>
                        <td><?php echo $row['model']; ?></td>
                        <td><?php echo $row['year']; ?></td>
                        <td><?php echo $row['status']; ?></td>
                        <td><?php echo $row['price']; ?></td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</body>
</html>
<?php $conn->close(); ?>