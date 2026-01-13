<?php
session_start();
include 'config.php';

$booking_id = $_GET['booking_id'];

$stmt = $conn->prepare("SELECT nurse_id, service_type FROM nurse_requests WHERE id = ?");
$stmt->bind_param("i", $booking_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $booking = $result->fetch_assoc();
    $booking['booking_id'] = $booking_id; // Include booking_id in the response
    echo json_encode($booking);
} else {
    echo json_encode(['error' => 'Booking not found']);
}
?>