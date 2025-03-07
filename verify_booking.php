<?php
// Include PHPMailer classes
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require __DIR__ . '/vendor/autoload.php'; // Include PHPMailer's autoload

/**
 * Sends a Gmail notification when a booking is verified.
 *
 * @param array $bookingData Booking details including booking_id, status, pickup_location, dropoff_location, email.
 * @return string Notification status.
 */
function sendNotification($bookingData)
{
    $mail = new PHPMailer(true); // Create PHPMailer instance

    // Prepare email content
    $subject = "Booking Verified: Booking ID " . $bookingData['booking_id'];

    $body = "Hello,\n\nYour booking with ID " . $bookingData['booking_id'] . " has been successfully confirmed.\n\nThank you for choosing our service!\n\nYou can Download Booking receipt at user dashboard going to my_booking and click view receipt.\n\nBest regards,\nVehicle Rental System Team";

    // Send email
    try {
        // Server settings
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com'; // Set the SMTP server to Gmail
        $mail->SMTPAuth = true;  // Enable SMTP authentication
        $mail->Username = 'loretonowls19@gmail.com'; // Your Gmail address
        $mail->Password = 'beqv jvyt uzmi iyst'; // Gmail app password (not your regular Gmail password)
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS; // Enable TLS encryption
        $mail->Port = 587;  // SMTP port

        // Recipients
        $mail->setFrom('loretonowls19@gmail.com', 'Booking Notifications'); // From email address
        
        // Validate the user's email before sending
        if (filter_var($bookingData['email'], FILTER_VALIDATE_EMAIL)) {
            $mail->addAddress($bookingData['email']); // Send to the user's email address
        } else {
            echo 'Invalid email address.'; 
            exit;
        }

        // Content
        $mail->isHTML(false); // Set email format to plain text
        $mail->Subject = $subject; // Email subject
        $mail->Body    = $body; // Email body content

        // Send email
        if ($mail->send()) {
            return "Notification sent successfully.";
        } else {
            return "Message could not be sent. Mailer Error: " . $mail->ErrorInfo;
        }
    } catch (Exception $e) {
        return "Message could not be sent. Mailer Error: {$mail->ErrorInfo}";
    }
}

// Handle POST request for booking verification
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['Verify_booking'])) {
    // Check for all required fields
    if (isset($_POST['email'], $_POST['booking_id'], $_POST['pickup_location'], $_POST['dropoff_location'], $_POST['status'])) {
        
        // Get the POST data
        $bookingData = [
            'booking_id' => $_POST['booking_id'],
            'pickup_location' => $_POST['pickup_location'],
            'dropoff_location' => $_POST['dropoff_location'],
            'status' => $_POST['status'],
            'email' => $_POST['email'],
        ];

        // Email validation
        if (!filter_var($bookingData['email'], FILTER_VALIDATE_EMAIL)) {
            echo 'Invalid email address.';
            exit;
        }

        // Prepare SQL update query to update booking status in the database
        $conn = new mysqli('localhost', 'root', '', 'vehicle_rental_system'); // Replace with your actual database credentials

        if ($conn->connect_error) {
            die("Connection failed: " . $conn->connect_error);
        }

        // Update query assuming there are no 'start_date' or 'end_date' columns
        $stmt = $conn->prepare("UPDATE bookings SET status = ?, pickup_location = ?, dropoff_location = ? WHERE booking_id = ?");
        
        if ($stmt === false) {
            // If prepare fails, display the error
            echo "SQL prepare failed: " . $conn->error;
            exit;
        }

        $stmt->bind_param("ssss", $bookingData['status'], $bookingData['pickup_location'], $bookingData['dropoff_location'], $bookingData['booking_id']);

        // Execute the query to update the booking status
        if ($stmt->execute()) {
            echo "Booking updated successfully.";
        } else {
            echo "Error updating booking: " . $stmt->error;
        }

        $stmt->close();
        $conn->close();

        // Send the notification email
        echo sendNotification($bookingData);

    } else {
        echo "Missing required fields.";
    }
} else {
    echo 'Invalid request or no booking details received.';
}
?>
  