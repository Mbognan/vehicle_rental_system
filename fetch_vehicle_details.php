<?php
include 'db.php';

if (isset($_POST['id'])) {
    $stmt = $conn->prepare("SELECT * FROM vehicles WHERE id = ?");
    $stmt->bind_param('i', $_POST['id']);
    $stmt->execute();
    $result = $stmt->get_result();
    echo json_encode($result->fetch_assoc());
    $stmt->close();
}
$conn->close();
?>
