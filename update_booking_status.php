<?php
session_start();
include 'config.php';

// Retrieve POST data
$data = json_decode(file_get_contents('php://input'), true);
$request_id = $data['request_id'];
$transaction_id = $data['transaction_id'];
$status = $data['status'];

// Update booking status in the database
$stmt = $conn->prepare("
    UPDATE nurse_requests 
    SET payment_status = ?, transaction_id = ?, payment_date = NOW()
    WHERE id = ?
");
$stmt->bind_param("ssi", $status, $transaction_id, $request_id);
if ($stmt->execute()) {
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'error' => 'Failed to update booking status.']);
}
?>