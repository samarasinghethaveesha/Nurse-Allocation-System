<?php
session_start();
include 'config.php';

if (!isset($_SESSION['customer_id'])) {
    header("Location: index.php");
    exit;
}

$customer_id = $_SESSION['customer_id'];
$stmt = $conn->prepare("SELECT * FROM payments WHERE customer_id = ? ORDER BY paid_at DESC");
$stmt->bind_param("i", $customer_id);
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html>
<head>
    <title>My Payments</title>
    <style>
        body { font-family: Arial; padding: 30px; background: #f4f6f9; }
        table { width: 100%; border-collapse: collapse; background: white; }
        th, td { padding: 12px; border: 1px solid #ccc; text-align: center; }
        th { background: #0a6ebd; color: white; }
        .back { margin-top: 20px; text-align: center; }
    </style>
</head>
<body>
    <h2 style="text-align:center; color:#0a6ebd;">Payment History</h2>
    <?php if ($result->num_rows > 0): ?>
    <table>
        <tr>
            <th>Request</th>
            <th>Total</th>
            <th>Nurse (80%)</th>
            <th>Service (20%)</th>
            <th>Status</th>
            <th>Date</th>
        </tr>
        <?php while ($row = $result->fetch_assoc()): ?>
        <tr>
            <td>#<?= $row['request_id'] ?></td>
            <td>LKR <?= number_format($row['total_amount'], 2) ?></td>
            <td>LKR <?= number_format($row['nurse_charge'], 2) ?></td>
            <td>LKR <?= number_format($row['service_charge'], 2) ?></td>
            <td><?= $row['payment_status'] ?></td>
            <td><?= $row['paid_at'] ?></td>
        </tr>
        <?php endwhile; ?>
    </table>
    <?php else: ?>
        <p style="text-align:center;">No payment records.</p>
    <?php endif; ?>
    <div class="back">
        <a href="customer_dashboard.php">⬅ Back to Dashboard</a>
    </div>
</body>
</html>
