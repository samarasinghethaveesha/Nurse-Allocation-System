<?php
session_start();
if (!isset($_SESSION['loggedin'])) header("Location: index.php");

include 'config.php';

if (isset($_POST['add_patient'])) {
    $name = $_POST['name'];
    $ward = $_POST['ward'];
    $stmt = $conn->prepare("INSERT INTO patients (name, ward) VALUES (?, ?)");
    $stmt->bind_param("ss", $name, $ward);
    $stmt->execute();
}

$patients = $conn->query("SELECT * FROM patients");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Manage Patients</title>
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
        form input, form button {
            display: block;
            width: 100%;
            margin: 10px 0;
            padding: 10px;
            border-radius: 5px;
            border: 1px solid #ddd;
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
        a.delete {
            background: #dc3545;
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
    <h2>Manage Patients</h2>

    <form method="post">
        <input type="text" name="name" placeholder="Patient Name" required>
        <input type="text" name="ward" placeholder="Ward" required>
        <button type="submit" name="add_patient">Add Patient</button>
    </form>

    <table>
        <thead>
            <tr>
                <th>ID</th><th>Name</th><th>Ward</th><th>Admitted At</th>
            </tr>
        </thead>
        <tbody>
            <?php while($row = $patients->fetch_assoc()): ?>
            <tr>
                <td><?= $row['id'] ?></td>
                <td><?= $row['name'] ?></td>
                <td><?= $row['ward'] ?></td>
                <td><?= $row['admitted_at'] ?></td>
            </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
    <a href="dashboard.php" class="button">Back to Dashboard</a>
</div>
</body>
</html>