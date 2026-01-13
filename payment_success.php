<?php
session_start();
include 'config.php';

if (!isset($_SESSION['customer_id'])) {
    header("Location: index.php");
    exit;
}

$customer_id = $_SESSION['customer_id'];
$request_id = $_GET['request_id'] ?? null;

if (!$request_id) {
    die("Invalid request.");
}

// Get request info
$stmt = $conn->prepare("SELECT start_datetime, end_datetime FROM nurse_requests WHERE id = ?");
if (!$stmt) {
    die("Prepare failed: " . $conn->error);
}
$stmt->bind_param("i", $request_id);
$stmt->execute();
$result = $stmt->get_result();
$request = $result->fetch_assoc();


if (!$request) {
    die("Booking not found.");
}

$start = new DateTime($request['start_datetime']);
$end = new DateTime($request['end_datetime']);
$interval = $start->diff($end);
$total_hours = $interval->h + ($interval->days * 24);
$total_amount = $total_hours * 1000;
$nurse_charge = $total_amount * 0.8;
$service_charge = $total_amount * 0.2;

// Insert into payments table
$transaction_id = "PH_" . uniqid();
$insert = $conn->prepare("INSERT INTO payments (request_id, customer_id, total_amount, service_charge, nurse_charge, payment_status, payment_method, transaction_id) VALUES (?, ?, ?, ?, ?, 'paid', 'PayHere', ?)");
$insert->bind_param("iiddds", $request_id, $customer_id, $total_amount, $service_charge, $nurse_charge, $transaction_id);

if ($insert->execute()) {
    $stmt = $conn->prepare("SELECT * FROM nurse_requests WHERE id = ?");
    if (!$stmt) {
        die("SQL Prepare failed: " . $conn->error); // Debug output
    }


    echo "✅ Payment successful.";
    echo "<br><a href='customer_dashboard.php'>Back to Dashboard</a>";
} else {
    echo "❌ Error saving payment.";
}
