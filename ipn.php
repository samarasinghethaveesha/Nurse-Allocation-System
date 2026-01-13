<?php
include 'config.php';

$data = json_decode(file_get_contents('php://input'), true);

if (isset($data['order_id'])) {
    $request_id = $data['order_id'];
    $stmt = $conn->prepare("UPDATE nurse_requests SET status = 'confirmed' WHERE id = ?");
    $stmt->bind_param("i", $request_id);
    $stmt->execute();
}
?>