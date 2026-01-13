<?php
session_start();
if (!isset($_SESSION['loggedin'])) header("Location: index.php");

include 'config.php';

// SQL query to find the nurse who worked the most hours
$query = "
    SELECT nr.nurse_id, n.name,
           SUM(TIMESTAMPDIFF(MINUTE, nr.start_datetime, nr.end_datetime)) / 60 AS total_hours_worked,
           SUM(nr.service_charge) AS total_service_charge
    FROM nurse_requests nr
    JOIN nurses n ON nr.nurse_id = n.id
    GROUP BY nr.nurse_id, YEAR(nr.start_datetime), MONTH(nr.start_datetime)
    ORDER BY total_hours_worked DESC
    LIMIT 1
";

// Execute query to get the nurse with the most hours and eligibility
$result = $conn->query($query);
$top_nurse = null;
$service_charge = 0;

if ($result && $row = $result->fetch_assoc()) {
    $top_nurse = $row['name'];
    $total_hours_worked = $row['total_hours_worked'];
    $service_charge = $row['total_service_charge'];
}

// If there's a nurse eligible for the service charge refund, calculate it
if ($top_nurse && $service_charge > 0) {
    // Logic to give service charge refund can be implemented here
    // You can process the refund, update the records, or notify the nurse
    // This example just shows the charge amount.
    $refund_message = "This nurse is eligible for a service charge refund of LKR " . $service_charge;
} else {
    $refund_message = "No nurse qualifies for a service charge refund.";
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Smart Recommendation</title>
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
        .card {
            background: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 6px rgba(0,0,0,0.1);
            max-width: 500px;
        }
        .highlight {
            font-size: 1.2em;
            color: green;
            margin-top: 10px;
        }
        .refund {
            margin-top: 20px;
            font-size: 1em;
            color: #007BFF;
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
        <li><a href="nurses.php">Manage Nurses</a></li>
        <!-- <li><a href="patients.php">Manage Patients</a></li> -->
        <!-- <li><a href="shifts.php">Assign Shifts</a></li> -->
        <li><a href="recommendation.php">Recommendations</a></li>
        <li><a href="logout.php">Logout</a></li>
    </ul>
</div>
<div class="content">
    <h2>Smart Nurse Recommendation</h2>
    <div class="card">
        <p>The nurse with the most hours worked and who qualifies for the service charge refund is:</p>
        <strong class="highlight"><?= htmlspecialchars($top_nurse ?? 'No eligible nurse found') ?></strong>
        
        <div class="refund">
            <?= htmlspecialchars($refund_message ?? 'No refund available') ?>
        </div>
    </div>
    <a href="dashboard.php" class="button">Back to Dashboard</a>
</div>
</body>
</html>