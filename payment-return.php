<?php
session_start();
include 'config.php';

// Verify payment details
$order_id = $_POST['order_id'];
$status = $_POST['status'];
$amount = $_POST['amount'];
$merchant_id = $_POST['merchant_id'];
$timestamp = $_POST['timeStamp'];
$signature = $_POST['signature'];

// Generate expected signature
$expected_signature = md5(PAYHERE_MERCHANT_ID . "~" . $order_id . "~" . $amount . "~" . $timestamp . "~" . PAYHERE_SECRET_KEY);

// Verify signature
if ($signature === $expected_signature) {
    // Update payment status in the database
    $update_query = "UPDATE nurse_requests SET payment_status = 'completed', transaction_id = ?, payment_date = NOW() WHERE id = ?";
    $update_stmt = $conn->prepare($update_query);
    $update_stmt->bind_param("si", $order_id, $_SESSION['request_id']);
    $update_stmt->execute();

    echo "Payment completed successfully!";
} else {
    echo "Payment verification failed.";
}
?>