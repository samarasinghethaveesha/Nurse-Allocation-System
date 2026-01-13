<?php
session_start();
if (!isset($_SESSION['customer_id'])) {
    header("Location: index.php");
    exit;
}

include 'config.php';

$customer_id = $_SESSION['customer_id'];
$bookings = [];

// Fetch customer bookings
$stmt = $conn->prepare("
    SELECT b.id, n.name as nurse_name, b.request_date, b.shift_type, b.status 
    FROM nurse_bookings b
    JOIN nurses n ON b.nurse_id = n.id
    WHERE b.customer_id = ?
    ORDER BY b.request_date DESC
");
$stmt->bind_param("i", $customer_id);
$stmt->execute();
$result = $stmt->get_result();

while ($row = $result->fetch_assoc()) {
    $bookings[] = $row;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>My Bookings</title>
    <style>
        * { box-sizing: border-box; }
        body {
            margin: 0;
            font-family: Arial, sans-serif;
            background: #f4f6f9;
        }
        .sidebar {
            width: 200px;
            background: #0a6ebd;
            position: fixed;
            height: 100%;
            padding: 20px;
            color: white;
        }
        .sidebar h2 {
            font-size: 20px;
            margin-bottom: 30px;
        }
        .sidebar ul {
            list-style: none;
            padding: 0;
        }
        .sidebar li {
            margin: 15px 0;
        }
        .sidebar a {
            color: white;
            text-decoration: none;
        }
        .content {
            margin-left: 220px;
            padding: 40px;
        }
        h2 {
            color: #333;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        th, td {
            padding: 12px;
            border: 1px solid #ddd;
            text-align: left;
        }
        th {
            background: #e9f0fa;
        }
        a.button {
            display: inline-block;
            margin-top: 20px;
            padding: 10px 15px;
            background-color: #0a6ebd;
            color: white;
            text-decoration: none;
            border-radius: 5px;
        }
        a.button:hover {
            background-color: #08599a;
        }
    </style>
</head>
<body>
<div class="sidebar">
    <h2>Nurse Allocation</h2>
    <ul>
        <li><a href="customer_dashboard.php">Dashboard</a></li>
        <li><a href="bookings.php">Bookings</a></li>
        <li><a href="profile.php">Profile</a></li>
        <li><a href="logout.php">Logout</a></li>
    </ul>
</div>
<div class="content">
    <h2>My Nurse Bookings</h2>

    <?php if (count($bookings) > 0): ?>
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Nurse</th>
                    <th>Date</th>
                    <th>Shift</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($bookings as $b): ?>
                    <tr>
                        <td><?= $b['id'] ?></td>
                        <td><?= $b['nurse_name'] ?></td>
                        <td><?= $b['request_date'] ?></td>
                        <td><?= $b['shift_type'] ?></td>
                        <td><?= ucfirst($b['status']) ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php else: ?>
        <p>You have no bookings yet.</p>
    <?php endif; ?>

    <a href="#" class="button">Request Nurse</a>
</div>
</body>
</html>