<?php
session_start();
include 'config.php';

// Update payment status in the database
$update_query = "UPDATE nurse_requests SET payment_status = 'failed' WHERE id = ?";
$update_stmt = $conn->prepare($update_query);
$update_stmt->bind_param("i", $_SESSION['request_id']);
$update_stmt->execute();

echo "Payment canceled.";
?>