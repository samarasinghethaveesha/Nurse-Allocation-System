<?php
session_start();
include 'config.php';

$nurse_id = $_GET['nurse_id'];

// Fetch nurse name
$nurse_query = "SELECT name FROM nurses WHERE id = ?";
$nurse_stmt = $conn->prepare($nurse_query);
$nurse_stmt->bind_param("i", $nurse_id);
$nurse_stmt->execute();
$nurse_stmt->store_result();

if ($nurse_stmt->num_rows === 0) {
    echo json_encode(['success' => false, 'error' => 'Nurse not found']);
    exit;
}

$nurse_stmt->bind_result($nurse_name);
$nurse_stmt->fetch();

// Fetch ratings
$ratings_query = "
    SELECT 
        rating, 
        comment
    FROM reviews
    WHERE nurse_id = ?
";
$ratings_stmt = $conn->prepare($ratings_query);
$ratings_stmt->bind_param("i", $nurse_id);
$ratings_stmt->execute();
$ratings_result = $ratings_stmt->get_result();

$ratings = [];
while ($row = $ratings_result->fetch_assoc()) {
    $ratings[] = $row;
}

echo json_encode([
    'success' => true,
    'nurse_name' => $nurse_name,
    'ratings' => $ratings
]);
?>