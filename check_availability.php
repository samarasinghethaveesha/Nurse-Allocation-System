<?php
// Set error handling
ini_set('display_errors', 0);
ini_set('log_errors', 1);
error_reporting(E_ALL);

// Include database configuration
include 'config.php';

// Validate database connection
if ($conn->connect_error) {
    http_response_code(500);
    echo json_encode(['error' => 'Database connection failed: ' . $conn->connect_error]);
    exit;
}

// Parse incoming JSON data
$data = json_decode(file_get_contents('php://input'), true);

// Extract data
$nurse_id = $data['nurse_id'] ?? null;
$start_datetime = $data['start_datetime'] ?? null;
$end_datetime = $data['end_datetime'] ?? null;

// Validate inputs
if (!$nurse_id || !$start_datetime || !$end_datetime) {
    http_response_code(400);
    echo json_encode(['error' => 'Missing required fields']);
    exit;
}

// Query to check if the nurse is already booked during the specified time range
$query = "
    SELECT COUNT(*) AS count 
    FROM nurse_requests 
    WHERE nurse_id = ? 
    AND (
        (? BETWEEN start_datetime AND end_datetime) OR
        (? BETWEEN start_datetime AND end_datetime) OR
        (start_datetime BETWEEN ? AND ?) OR
        (end_datetime BETWEEN ? AND ?)
    )
";
$stmt = $conn->prepare($query);
if (!$stmt) {
    http_response_code(500);
    echo json_encode(['error' => 'Query preparation failed: ' . $conn->error]);
    exit;
}
$stmt->bind_param(
    "isssss",
    $nurse_id,
    $start_datetime,
    $end_datetime,
    $start_datetime,
    $end_datetime,
    $start_datetime,
    $end_datetime
);
$stmt->execute();
$result = $stmt->get_result();
$row = $result->fetch_assoc();

// Check if the nurse is booked
$isAvailable = $row['count'] == 0;

// Return JSON response
header('Content-Type: application/json');
http_response_code(200);
echo json_encode(['available' => $isAvailable]);
?>