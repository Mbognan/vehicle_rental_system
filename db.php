<?php
$servername = "localhost";
$username = "root"; 
$password = ""; 
$dbname = "vehicle_rental_system";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connection_error);
}
?>