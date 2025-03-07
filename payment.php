<?php
session_start();
include 'db.php';

// Check if user is logged in
if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit();
}

$username = $_SESSION['username'];

// Handle payment form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['make_payment'])) {
    $amount = $_POST['amount'];
    $vehicle_id = $_POST['vehicle_id']; // Assuming you have this in your form
    $user_id = $_SESSION['user_id']; // Assuming user ID is stored in session

    // GCash API credentials
    $api_url = 'https://api.gcash.com/v1/payment'; // Update to the actual endpoint
    $api_key = 'YOUR_API_KEY';

    $data = [
        'amount' => $amount,
        'currency' => 'PHP',
        'description' => 'Vehicle Booking Payment',
        'user_id' => $user_id,
        'vehicle_id' => $vehicle_id,
    ];

    // Make the API request
    $ch = curl_init($api_url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/json',
        'Authorization: Bearer ' . $api_key,
    ]);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));

    $response = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($http_code == 200) {
        // Payment successful
        $message = "Payment successful!";
        // Here, you can update the booking status in your database
    } else {
        // Handle payment failure
        $error_message = json_decode($response, true);
        $message = "Payment failed: " . ($error_message['message'] ?? 'Unknown error');
        file_put_contents('payment_log.txt', print_r($error_message, true), FILE_APPEND);
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payment</title>
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-5">
        <h1>Make Payment</h1>
        <?php if (isset($message)): ?>
            <div class="alert alert-info"><?php echo $message; ?></div>
        <?php endif; ?>
        <form action="payment.php" method="post">
            <input type="hidden" name="vehicle_id" value="<?php echo htmlspecialchars($vehicle_id); ?>">
            <div class="form-group">
                <label for="amount">Amount (PHP)</label>
                <input type="number" class="form-control" name="amount" required>
            </div>
            <button type="submit" class="btn btn-primary" name="make_payment">Pay Now</button>
        </form>
    </div>
</body>
</html>
