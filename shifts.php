<?php
session_start();
if (!isset($_SESSION['loggedin'])) header("Location: index.php");

include 'config.php';

$nurses = $conn->query("SELECT * FROM nurses");

if (isset($_POST['assign_shift'])) {
    $nurse_id = $_POST['nurse_id'];
    $shift = $_POST['shift_type'];
    $day = $_POST['day_of_week'];
    $stmt = $conn->prepare("INSERT INTO shifts (nurse_id, shift_type, day_of_week) VALUES (?, ?, ?)");
    $stmt->bind_param("iss", $nurse_id, $shift, $day);
    $stmt->execute();
}

$shifts = $conn->query("
    SELECT s.id, n.name, s.shift_type, s.day_of_week 
    FROM shifts s JOIN nurses n ON s.nurse_id = n.id
");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Assign Shifts</title>
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
        form select,
        form input[type="submit"] {
            width: 100%;
            padding: 10px;
            margin: 10px 0;
            border-radius: 5px;
            border: 1px solid #ccc;
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
            padding: 8px 15px;
            margin-top: 10px;
            background: #28a745;
            color: white;
            text-decoration: none;
            border-radius: 5px;
        }
    </style>
</head>
<body>
<div class="sidebar">
    <h2>Nurse Allocation</h2>
    <ul>
        <li><a href="nurses.php">Manage Nurses</a></li>
        <li><a href="patients.php">Manage Patients</a></li>
        <li><a href="shifts.php">Assign Shifts</a></li>
        <li><a href="recommendation.php">Recommendations</a></li>
        <li><a href="logout.php">Logout</a></li>
    </ul>
</div>
<div class="content">
    <h2>Assign Nurse Shifts</h2>

    <form method="post">
        <label for="nurse_id"><strong>Nurse:</strong></label>
        <select name="nurse_id" required>
            <?php while ($nurse = $nurses->fetch_assoc()): ?>
                <option value="<?= $nurse['id'] ?>"><?= htmlspecialchars($nurse['name']) ?></option>
            <?php endwhile; ?>
        </select>

        <label for="shift_type"><strong>Shift Type:</strong></label>
        <select name="shift_type" required>
            <option>Morning</option>
            <option>Evening</option>
            <option>Night</option>
        </select>

        <label for="day_of_week"><strong>Day of Week:</strong></label>
        <select name="day_of_week" required>
            <option>Monday</option>
            <option>Tuesday</option>
            <option>Wednesday</option>
            <option>Thursday</option>
            <option>Friday</option>
            <option>Saturday</option>
            <option>Sunday</option>
        </select>

        <input type="submit" name="assign_shift" value="Assign Shift">
    </form>

    <table>
        <thead>
            <tr>
                <th>ID</th><th>Nurse</th><th>Shift</th><th>Day</th>
            </tr>
        </thead>
        <tbody>
            <?php while($row = $shifts->fetch_assoc()): ?>
            <tr>
                <td><?= $row['id'] ?></td>
                <td><?= $row['name'] ?></td>
                <td><?= $row['shift_type'] ?></td>
                <td><?= $row['day_of_week'] ?></td>
            </tr>
            <?php endwhile; ?>
        </tbody>
    </table>

    <a href="dashboard.php" class="button">Back to Dashboard</a>
</div>
</body>
</html>