<?php
session_start();
include 'db.php';

if (!isset($_SESSION['username']) || $_SESSION['role'] != 'user') {
    header("Location: login.php");
    exit();
}

// Retrieve the booking ID from the URL
$booking_id = $_GET['booking_id'] ?? null;
if ($booking_id === null) {
    die("Booking ID is required.");
}

// Fetch booking details from the database
$booking_sql = "SELECT b.*, v.brand, v.model, v.plate_number, v.price_per_day 
                FROM bookings b 
                JOIN vehicles v ON b.vehicle_id = v.vehicle_id 
                WHERE b.booking_id = ?";
$stmt = $conn->prepare($booking_sql);
$stmt->bind_param('i', $booking_id);
$stmt->execute();
$booking_result = $stmt->get_result();

if ($booking_result->num_rows == 0) {
    die("Booking not found.");
}

$booking = $booking_result->fetch_assoc();

// Close the database connection
$stmt->close();
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Booking Receipt</title>
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/FileSaver.js/2.0.5/FileSaver.min.js"></script>
    <style>
        body {
            font-family: Arial, sans-serif;
        }
        .receipt-container {
            max-width: 800px;
            margin: 50px auto;
            padding: 20px;
            border: 1px solid #ddd;
            border-radius: 10px;
            background-color: #fff;
        }
        .receipt-header {
            text-align: center;
        }
        .total-cost {
            font-size: 1.5em;
            font-weight: bold;
            color: green;
        }
        .btn-print, .btn-download {
            margin-top: 20px;
            background-color: #007bff;
            color: white;
        }
        .btn-print:hover, .btn-download:hover {
            background-color: #0056b3;
        }
        .proof-payment-image {
            max-width: 100%;
            height: auto;
            margin-top: 10px;
            max-height: 300px;
            object-fit: contain;
        }
    </style>
</head>
<body>

<div class="receipt-container">
    <div class="receipt-header">
        <h2>Booking Receipt</h2>
        <p><strong>Booking ID:</strong> #<?php echo htmlspecialchars($booking['booking_id']); ?></p>
        <p><strong>Booking Date:</strong> <?php echo date("F j, Y, g:i a", strtotime($booking['created_at'])); ?></p>
    </div>

    <h4>Palompon Vehicle Rental System</h4>
    <table class="table table-bordered">
        <tr><th>System Admins: Noel Loreto</th></tr>
        <tr><th> Address: Lopez street corner pintor luna Brgy. Guiwan II Palompon, Leyte</th></tr>
        <tr><th> Phone: +63 928 701 4264</th></tr>
        <tr><th> Email: loretonowls19@gmail.com</th></tr>
    </table>

    <h4>User Details</h4>
    <table class="table table-bordered">
        <tr><th>Full Name</th><td><?php echo htmlspecialchars($booking['fullname']); ?></td></tr>
        <tr><th>Mobile</th><td><?php echo htmlspecialchars($booking['mobile']); ?></td></tr>
        <tr><th>Address</th><td><?php echo htmlspecialchars($booking['complete_address']); ?></td></tr>
    </table>

    <h4>Vehicle Details</h4>
    <table class="table table-bordered">
        <tr><th>Brand</th><td><?php echo htmlspecialchars($booking['brand']); ?></td></tr>
        <tr><th>Model</th><td><?php echo htmlspecialchars($booking['model']); ?></td></tr>
        <tr><th>Plate Number</th><td><?php echo htmlspecialchars($booking['plate_number']); ?></td></tr>
        <tr><th>Rental Fee/Day</th><td>₱<?php echo number_format($booking['price_per_day'], 2); ?></td></tr>
    </table>

    <h4>Booking Details</h4>
    <table class="table table-bordered">
        <tr><th>Pick-up Location</th><td><?php echo htmlspecialchars($booking['pickup_location']); ?></td></tr>
        <tr><th>Drop-off Location</th><td><?php echo htmlspecialchars($booking['dropoff_location']); ?></td></tr>
        <tr><th>Start Date and Time</th><td><?php echo date("F j, Y, g:i a", strtotime($booking['start_date_and_time'])); ?></td></tr>
        <tr><th>End Date and Time</th><td><?php echo date("F j, Y, g:i a", strtotime($booking['end_date_and_time'])); ?></td></tr>
    </table>

    <h4>Total Cost</h4>
    <p class="total-cost">₱<?php echo number_format($booking['total_cost'], 2); ?></p>

    <h4>Proof of Payment</h4>
    <?php if ($booking['proof_payment']): ?>
        <div>
            <!-- Display the proof of payment image -->
            <img src="uploads/<?php echo htmlspecialchars($booking['proof_payment']); ?>" class="proof-payment-image" alt="Proof of Payment Image">
        </div>
    <?php else: ?>
        <p>No proof of payment uploaded.</p>
    <?php endif; ?>

    <button class="btn btn-print" onclick="window.print();">Print Receipt</button>
    <button class="btn btn-download" id="downloadReceiptBtn">Download Receipt as Document</button>
    <button class="btn btn-download" onclick="downloadProofPayment()">Download Proof of Payment</button>
</div>

<script>
    // Function to download the receipt as a .docx file
    document.getElementById('downloadReceiptBtn').addEventListener('click', function () {
        // Create a new Blob for the Word document
        const docContent = `
            <html>
            <head>
                <title>Booking Receipt</title>
                <style>
                    body { font-family: Arial, sans-serif; }
                    .receipt-container { padding: 20px; }
                    .total-cost { font-size: 1.5em; font-weight: bold; color: green; }
                </style>
            </head>
            <body>
                <div class="receipt-container">
                    <h2>Booking Receipt</h2>
                    <p><strong>Booking ID:</strong> #${<?php echo json_encode($booking['booking_id']); ?>}</p>
                    <p><strong>Booking Date:</strong> ${<?php echo json_encode(date("F j, Y, g:i a", strtotime($booking['created_at']))); ?>}</p>
                    
                    <h4>User Details</h4>
                    <p><strong>Full Name:</strong> ${<?php echo json_encode($booking['fullname']); ?>}</p>
                    <p><strong>Mobile:</strong> ${<?php echo json_encode($booking['mobile']); ?>}</p>
                    <p><strong>Address:</strong> ${<?php echo json_encode($booking['complete_address']); ?>}</p>

                    <h4>Vehicle Details</h4>
                    <p><strong>Brand:</strong> ${<?php echo json_encode($booking['brand']); ?>}</p>
                    <p><strong>Model:</strong> ${<?php echo json_encode($booking['model']); ?>}</p>
                    <p><strong>Plate Number:</strong> ${<?php echo json_encode($booking['plate_number']); ?>}</p>
                    <p><strong>Rental Fee/Day:</strong> ₱${<?php echo json_encode(number_format($booking['price_per_day'], 2)); ?>}</p>

                    <h4>Booking Details</h4>
                    <p><strong>Pick-up Location:</strong> ${<?php echo json_encode($booking['pickup_location']); ?>}</p>
                    <p><strong>Drop-off Location:</strong> ${<?php echo json_encode($booking['dropoff_location']); ?>}</p>
                    <p><strong>Start Date and Time:</strong> ${<?php echo json_encode(date("F j, Y, g:i a", strtotime($booking['start_date_and_time']))); ?>}</p>
                    <p><strong>End Date and Time:</strong> ${<?php echo json_encode(date("F j, Y, g:i a", strtotime($booking['end_date_and_time']))); ?>}</p>

                    <h4>Total Cost</h4>
                    <p class="total-cost">₱${<?php echo json_encode(number_format($booking['total_cost'], 2)); ?>}</p>
                </div>
            </body>
            </html>
        `;
        
        // Create a Blob and trigger download
        const blob = new Blob([docContent], { type: 'application/msword' });
        saveAs(blob, 'Booking_Receipt.doc'); // Trigger the download
    });

    // Function to download the proof of payment image
    function downloadProofPayment() {
        const imageUrl = 'uploads/<?php echo htmlspecialchars($booking['proof_payment']); ?>';
        const link = document.createElement('a');
        link.href = imageUrl;
        link.download = "<?php echo htmlspecialchars($booking['proof_payment']); ?>"; // Filename for the image
        link.click(); // Trigger the download
    }
</script>

<script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.10.2/dist/umd/popper.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>
