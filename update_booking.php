<?php
session_start();
include 'db.php';

// Check if user is logged in
if (!isset($_SESSION['username']) || $_SESSION['role'] != 'user') {
    header("Location: login.php");
    exit();
}

// Check if POST request
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['booking_id'])) {
    $booking_id = $_POST['booking_id'];
    $fullname = $_POST['fullname'];
    $complete_address = $_POST['complete_address'];
    $mobile = $_POST['mobile'];
    $start_date_and_time = $_POST['start_date_and_time'];
    $end_date_and_time = $_POST['end_date_and_time'];

    // Validate dates
    if (strtotime($start_date_and_time) >= strtotime($end_date_and_time)) {
        echo "Start date must be before end date.";
        exit();
    }

    // Prepare the update statement
    $stmt = $conn->prepare("UPDATE bookings SET fullname = ?, complete_address = ?, mobile = ?, start_date_and_time = ?, end_date_and_time = ? WHERE booking_id = ?");
    $stmt->bind_param("ssssss", $fullname, $complete_address, $mobile, $start_date_and_time, $end_date_and_time, $booking_id);

    if ($stmt->execute()) {
        header("Location: my_bookings.php?success=1");
        exit();
    } else {
        echo "Failed to update booking: " . $stmt->error;
    }

    $stmt->close();
}
$conn->close();
?>
