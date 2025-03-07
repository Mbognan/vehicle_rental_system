<?php
session_start();
include 'db.php';

// Check if user is logged in
if (!isset($_SESSION['username']) || $_SESSION['role'] != 'user') {
    header("Location: login.php");
    exit();
}

// Check if booking ID is provided
if (isset($_GET['booking_id'])) {
    $booking_id = $_GET['booking_id'];

    // Prepare the update statement
    $stmt = $conn->prepare("UPDATE bookings SET status = 'cancelled' WHERE booking_id = ?");
    $stmt->bind_param("s", $booking_id);

    if ($stmt->execute()) {
        // Redirect to my_bookings.php with success message
        header("Location: my_bookings.php?cancelled=1");
        exit();
    } else {
        echo "Failed to cancel booking: " . $stmt->error;
    }

    $stmt->close();
} else {
    echo "No booking ID provided.";
}

$conn->close();
?>
