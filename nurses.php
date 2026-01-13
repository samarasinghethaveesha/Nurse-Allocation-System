<?php
session_start();
$error = $success = "";
if (!isset($_SESSION['loggedin'])) header("Location: index.php");

include 'config.php';

// Handle Add Nurse
if (isset($_POST['add_nurse'])) {
    $name = $_POST['name'];
    $age = $_POST['age'];
    $mobile = $_POST['mobile'];
    $exp = $_POST['experience'];

    if (empty($name) || empty($mobile) || empty($age)) {
        $error = "All fields are required.";
    } elseif (!is_numeric($age) || $age > 55) {
        $error = "Age must be a number and not more than 55.";
    } elseif (!preg_match('/^[0-9]{10}$/', $mobile)) {
        $error = "Phone number must be 10 digits.";
    } else {
        $stmt = $conn->prepare("INSERT INTO nurses (name, age, mobile, experience_years) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("sssi", $name, $age, $mobile, $exp);
        $stmt->execute();
        $success = "Nurse added successfully!";
    }
}

// Handle Delete Nurse
if (isset($_GET['delete'])) {
    $id = $_GET['delete'];
    $conn->query("DELETE FROM nurses WHERE id=$id");
    header("Location: nurses.php");
    exit;
}

// Handle Edit Nurse
$edit_nurse = null;
if (isset($_GET['edit'])) {
    $id = $_GET['edit'];
    $result = $conn->query("SELECT * FROM nurses WHERE id=$id");
    if ($result->num_rows > 0) {
        $edit_nurse = $result->fetch_assoc();
    }
}

// Handle Update Nurse
if (isset($_POST['update_nurse'])) {
    $id = $_POST['nurse_id'];
    $name = $_POST['name'];
    $age = $_POST['age'];
    $mobile = $_POST['mobile'];
    $exp = $_POST['experience'];

    if (empty($name) || empty($mobile) || empty($age)) {
        $error = "All fields are required.";
    } elseif (!is_numeric($age) || $age > 55) {
        $error = "Age must be a number and not more than 55.";
    } elseif (!preg_match('/^[0-9]{10}$/', $mobile)) {
        $error = "Phone number must be 10 digits.";
    } else {
        $stmt = $conn->prepare("UPDATE nurses SET name=?, age=?, mobile=?, experience_years=? WHERE id=?");
        $stmt->bind_param("sssii", $name, $age, $mobile, $exp, $id);
        $stmt->execute();
        $success = "Nurse updated successfully!";
        header("Location: nurses.php");
        exit;
    }
}

$nurses = $conn->query("SELECT * FROM nurses");
?>


<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Manage Nurses - Nurse Allocation</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary: #0a6ebd;
            --success: #28a745;
            --danger: #dc3545;
            --light: #f8f9fa;
            --dark: #343a40;
        }

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            font-family: 'Poppins', sans-serif;
            background: linear-gradient(135deg, #f0f4f8 50%, var(--light) 50%);
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }

        .sidebar {
            width: 260px;
            background: var(--primary);
            color: white;
            height: 100vh;
            position: fixed;
            padding: 30px;
            box-shadow: 2px 0 15px rgba(0, 0, 0, 0.1);
            transition: all 0.3s;
        }

        .sidebar-brand {
            font-size: 1.5rem;
            font-weight: 600;
            margin-bottom: 30px;
            display: flex;
            align-items: center;
        }

        .sidebar-brand i {
            margin-right: 10px;
            font-size: 1.8rem;
        }

        .sidebar-menu {
            list-style: none;
        }

        .sidebar-menu li {
            margin: 15px 0;
        }

        .sidebar-menu a {
            color: white;
            text-decoration: none;
            font-size: 1.1rem;
            display: flex;
            align-items: center;
            padding: 10px;
            border-radius: 8px;
            transition: all 0.3s;
        }

        .sidebar-menu a:hover {
            background: rgba(255, 255, 255, 0.1);
            transform: translateX(10px);
        }

        .sidebar-menu a i {
            margin-right: 15px;
            width: 24px;
            text-align: center;
        }

        .main-content {
            margin-left: 260px;
            padding: 40px;
            flex: 1;
            transition: all 0.3s;
        }

        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 40px;
            flex-wrap: wrap;
        }

        .form-section {
            background: white;
            border-radius: 12px;
            padding: 30px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
            margin-bottom: 30px;
        }

        .form-section h3 {
            color: var(--primary);
            margin-bottom: 25px;
            position: relative;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-group label {
            display: block;
            margin-bottom: 8px;
            color: var(--dark);
            font-weight: 500;
        }

        .form-control {
            width: 100%;
            padding: 12px;
            border: 1px solid #ced4da;
            border-radius: 6px;
            font-size: 1rem;
            transition: border-color 0.3s;
        }

        .form-control:focus {
            border-color: var(--primary);
            outline: none;
        }

        .btn-primary {
            background: var(--primary);
            color: white;
            border: none;
            padding: 12px;
            border-radius: 6px;
            width: 100%;
            font-size: 1rem;
            cursor: pointer;
            transition: background 0.3s;
        }

        .btn-primary:hover {
            background: #08599a;
        }

        .table-container {
            background: white;
            border-radius: 12px;
            padding: 30px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th {
            background: #e9f0fa;
            color: var(--dark);
            padding: 15px;
            text-align: left;
            font-weight: 600;
        }

        td {
            padding: 12px;
            border-bottom: 1px solid #eee;
        }

        tr:hover {
            background: #f8f9fa;
        }

        .btn-action {
            padding: 8px 15px;
            border-radius: 6px;
            border: none;
            cursor: pointer;
            font-weight: 500;
            transition: all 0.3s;
        }

        .btn-delete {
            background: var(--danger);
            color: white;
        }

        .btn-edit {
            background: var(--primary);
            color: white;
        }

        .message {
            text-align: center;
            margin: 15px 0;
            padding: 10px;
            border-radius: 6px;
        }

        .success {
            background: var(--success);
            color: white;
        }

        .error {
            background: var(--danger);
            color: white;
        }

        @media (max-width: 992px) {
            .sidebar {
                width: 60px;
            }

            .main-content {
                margin-left: 60px;
            }
        }
    </style>
</head>

<body>
    <!-- Sidebar -->
    <div class="sidebar">
        <div class="sidebar-brand">
            <i class="fas fa-user-nurse"></i>
            <span>Nurse Allocation</span>
        </div>
        <ul class="sidebar-menu">
            <li><a href="nurses.php" class="active"><i class="fas fa-users"></i> Manage Nurses</a></li>
            <!-- <li><a href="patients.php"><i class="fas fa-procedures"></i> Manage Patients</a></li> -->
            <!-- <li><a href="shifts.php"><i class="fas fa-calendar-alt"></i> Assign Shifts</a></li> -->
            <li><a href="recommendation.php"><i class="fas fa-chart-line"></i> Recommendations</a></li>
            <li><a href="logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
        </ul>
    </div>

    <!-- Main Content -->
    <div class="main-content">
        <div class="header">
            <h2 class="text-primary">Manage Nurses</h2>
        </div>

        <!-- Add Nurse Form -->
        <div class="form-section">
            <h3><?= $edit_nurse ? "Edit Nurse" : "Add New Nurse" ?></h3>
            <?php if ($success): ?>
                <div class="message success"><?= htmlspecialchars($success) ?></div>
            <?php elseif ($error): ?>
                <div class="message error"><?= htmlspecialchars($error) ?></div>
            <?php endif; ?>
            <form method="post">
                <?php if ($edit_nurse): ?>
                    <input type="hidden" name="nurse_id" value="<?= $edit_nurse['id'] ?>">
                <?php endif; ?>
                <div class="row">
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="name">Nurse Name</label>
                            <input type="text" name="name" id="name" class="form-control" placeholder="Enter nurse's full name"
                                value="<?= $edit_nurse['name'] ?? '' ?>" required>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="age">Age</label>
                            <input type="text" name="age" id="age" class="form-control" placeholder="Age"
                                value="<?= $edit_nurse['age'] ?? '' ?>" required>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="mobile">Mobile</label>
                            <input type="text" name="mobile" id="mobile" class="form-control" placeholder="Enter Mobile Number"
                                pattern="^\d{10}$" title="Phone number must be 10 digits."
                                value="<?= $edit_nurse['mobile'] ?? '' ?>" required>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="experience">Experience (Years)</label>
                            <input type="number" name="experience" id="experience" class="form-control"
                                placeholder="Years of experience"
                                value="<?= $edit_nurse['experience_years'] ?? '' ?>" required>
                        </div>
                    </div>
                </div>
                <button type="submit" name="<?= $edit_nurse ? 'update_nurse' : 'add_nurse' ?>" class="btn-primary">
                    <?= $edit_nurse ? 'Update Nurse' : 'Add Nurse' ?>
                </button>
            </form>

        </div>

        <!-- Nurse List Table -->
        <div class="table-container">
            <h3 class="mb-4">List of Nurses</h3>
            <?php if ($nurses->num_rows > 0): ?>
                <table>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Name</th>
                            <th>Mobile</th>
                            <th>Age</th>
                            <th>Experience (Years)</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($row = $nurses->fetch_assoc()): ?>
                            <tr>
                                <td><?= htmlspecialchars($row['id']) ?></td>
                                <td><?= htmlspecialchars($row['name']) ?></td>
                                <td><?= htmlspecialchars($row['mobile']) ?></td>
                                <td><?= htmlspecialchars($row['age']) ?></td>
                                <td><?= htmlspecialchars($row['experience_years']) ?></td>
                                <td>
                                    <a href="?edit=<?= $row['id'] ?>" class="btn-action btn-edit">Edit</a>
                                    <a href="?delete=<?= $row['id'] ?>"
                                        onclick="return confirm('Are you sure you want to delete this nurse?')"
                                        class="btn-action btn-delete">Delete</a>
                                </td>

                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <p class="text-center">No nurses found.</p>
            <?php endif; ?>
        </div>

        <div style="text-align:center;margin-top:20px;">
            <a href="dashboard.php" class="btn-primary">Back to Dashboard</a>
        </div>
    </div>
</body>

</html>