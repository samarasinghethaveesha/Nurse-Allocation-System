<?php
session_start();
include 'config.php';

// Get POST data
$customer_id = $_SESSION['customer_id'];
$nurse_id = $_POST['nurse_id'];
$service_type = $_POST['service_type'];
$rating = $_POST['rating'];
$comment = $_POST['comment'];
$booking_id = $_POST['booking_id']; // Add this line to capture booking_id

// Insert review
$stmt = $conn->prepare("INSERT INTO reviews (customer_id, nurse_id, service_type, rating, comment, booking_id) VALUES (?, ?, ?, ?, ?, ?)");
$stmt->bind_param("iisssi", $customer_id, $nurse_id, $service_type, $rating, $comment, $booking_id);

if ($stmt->execute()) {
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'error' => $conn->error]);
}
?>