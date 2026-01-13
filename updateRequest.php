<?php
// Include database configuration

include 'config.php';

// Parse incoming JSON data
$data = json_decode(file_get_contents('php://input'), true);

// Validate required fields
if (
    !isset($data['request_id']) ||
    !isset($data['address']) ||
    !isset($data['mobile']) ||
    !isset($data['nic']) ||
    !isset($data['order_id']) ||
    !isset($data['total_amount']) ||
    !isset($data['nursing_charge']) ||
    !isset($data['service_charge']) ||
    !isset($data['total_hours'])
) {
    http_response_code(400);
    echo json_encode(['error' => 'Missing required fields']);
    exit;
}

// Extract data
$request_id = $data['request_id'];
$city = $data['address'];
$phone_number = $data['mobile'];
$nic = $data['nic'];
$order_id = $data['order_id'];
$total_amount = $data['total_amount'];
$nursing_charge = $data['nursing_charge'];
$service_charge = $data['service_charge'];
$total_hours = $data['total_hours'];

// Current date and time for payment date
$payment_date = date('Y-m-d H:i:s');

// Update the nurse_requests table
$update_query = "
    UPDATE nurse_requests 
    SET 
        status = 'confirmed',
        city = ?,
        phone_number = ?,
        nic = ?,
        total_amount = ?, 
        nurse_charge = ?, 
        service_charge = ?, 
        payment_status = 'completed', 
        transaction_id = ?, 
        payment_date = ?, 
        total_hours = ? 
    WHERE id = ?
";
$update_stmt = $conn->prepare($update_query);
$update_stmt->bind_param(
    "sssdddssdi", 
    $city,
    $phone_number,
    $nic,
    $total_amount,
    $nursing_charge,
    $service_charge,
    $order_id,
    $payment_date,
    $total_hours,
    $request_id
);

if ($update_stmt->execute()) {
    http_response_code(200);
    echo json_encode(['success' => 'Request updated successfully']);
} else {
    http_response_code(500);
    echo json_encode(['error' => 'Failed to update request']);
}
?>