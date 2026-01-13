<?php
// Include database configuration
include 'config.php';

// Get request ID and amount from the query parameters
$request_id = $_GET['id'];
$amount = $_GET['amount'];

// Validate request ID and amount
if (!isset($request_id) || !isset($amount)) {
    http_response_code(400);
    echo json_encode(['error' => 'Missing request ID or amount']);
    exit;
}

// Fetch request details to ensure it exists
$request_query = "SELECT id, start_datetime, end_datetime FROM nurse_requests WHERE id = ? AND status = 'approved'";
$request_stmt = $conn->prepare($request_query);
$request_stmt->bind_param("i", $request_id);
$request_stmt->execute();
$request_result = $request_stmt->get_result();
$request = $request_result->fetch_assoc();

if (!$request) {
    http_response_code(404);
    echo json_encode(['error' => 'Request not found or invalid']);
    exit;
}

// Calculate total hours
$start_datetime = new DateTime($request['start_datetime']);
$end_datetime = new DateTime($request['end_datetime']);
$interval = $start_datetime->diff($end_datetime);
$total_hours = ($interval->days * 24) + $interval->h;

// Validate total hours
if ($total_hours <= 0) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid date range for the request']);
    exit;
}

// Calculate charges
$hourly_rate = 1000; // Rate per hour
$total_amount = $total_hours * $hourly_rate;
$nursing_charge = $total_amount * 0.8; // 80% of total amount
$service_charge = $total_amount * 0.2; // 20% of total amount

// Generate unique order ID
$order_id = uniqid();

// Merchant details
$merchant_id = "1221417"; // Replace with your actual merchant ID
$merchant_secret = "MTM2OTgzNzgwNTIzMDQxOTExMTcyNjYzODg2MTE0MDg3OTM2NDM3"; // Replace with your actual merchant secret
$currency = "LKR";

// Generate hash
$hash = strtoupper(
    md5(
        $merchant_id . 
        $order_id . 
        number_format($amount, 2, '.', '') . 
        $currency .  
        strtoupper(md5($merchant_secret))
    )
);

// Prepare payment details
$payment_details = [
    "amount" => $amount,
    "merchant_id" => $merchant_id,
    "order_id" => $order_id,
    "currency" => $currency,
    "hash" => $hash,
    "total_hours" => $total_hours,
    "nursing_charge" => $nursing_charge,
    "service_charge" => $service_charge
];

// Return payment details as JSON
echo json_encode($payment_details);
?>