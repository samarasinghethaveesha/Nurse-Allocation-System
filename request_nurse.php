<?php
session_start();

// Redirect if not logged in
if (!isset($_SESSION['customer_id'])) {
    header("Location: index.php");
    exit;
}

$error = $success = "";

// Fetch available nurses
include 'config.php';
$nurses_query = "SELECT id, name, mobile FROM nurses WHERE status = 'available'";
$nurses_result = $conn->query($nurses_query);

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Fetch form data
    $selected_nurse_id = $_POST['nurse_id']; // Nurse ID from dropdown
    $service_type = trim($_POST['service_type']);
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $age = trim($_POST['age']);
    $start_datetime = trim($_POST['start_datetime']);
    $end_datetime = trim($_POST['end_datetime']);
    $notes = trim($_POST['notes']);
    

    // Validate inputs
    if (empty($service_type)) {
        $error = "Service type is required.";
    } elseif (empty($start_datetime) || empty($end_datetime)) {
        $error = "Start and end date/time are required.";
    } elseif (strtotime($start_datetime) >= strtotime($end_datetime)) {
        $error = "End date/time must be after start date/time.";
    } elseif (empty($selected_nurse_id)) {
        $error = "Please select a nurse.";
    } else {
        // Check if nurse is already booked during the selected time
        $conflict_query = "SELECT * FROM nurse_requests 
WHERE nurse_id = ? 
AND status = 'approved' 
AND (
    (start_datetime <= ? AND end_datetime > ?) OR
    (start_datetime < ? AND end_datetime >= ?) OR
    (start_datetime >= ? AND end_datetime <= ?)
)";

        $conflict_stmt = $conn->prepare($conflict_query);
        $conflict_stmt->bind_param("issssss", $selected_nurse_id, $start_datetime, $start_datetime, $end_datetime, $end_datetime, $start_datetime, $end_datetime);
        $conflict_stmt->execute();
        $conflict_result = $conflict_stmt->get_result();

        if ($conflict_result->num_rows > 0) {
            $error = "Selected nurse is already booked during this time.";
        } else {
            // Insert into database
            $stmt = $conn->prepare("INSERT INTO nurse_requests (customer_id,name,email, service_type, notes, nurse_id,start_datetime, end_datetime,age) VALUES (?,?,?,?,?,?,?,?,?)");
            $stmt->bind_param("issssisss", $_SESSION['customer_id'], $name, $email, $service_type, $notes, $selected_nurse_id, $start_datetime, $end_datetime,$age);

            if ($stmt->execute()) {
                $success = "Your request has been submitted successfully!";
            } else {
                $error = "Failed to submit your request. Please try again later.";
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Request Nurse - Nurse Allocation System</title>
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

        .form-container {
            background: white;
            border-radius: 12px;
            padding: 30px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
        }

        .form-group {
            margin-bottom: 25px;
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

        .form-control:disabled {
            background: #f8f9fa;
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
            <li><a href="customer_dashboard.php"><i class="fas fa-tachometer-alt"></i> Dashboard</a></li>
            <li><a href="request_nurse.php" class="active"><i class="fas fa-plus"></i> Request Nurse</a></li>
            <li><a href="profile.php"><i class="fas fa-user"></i> Profile</a></li>
            <li><a href="logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
        </ul>
    </div>

    <!-- Main Content -->
    <div class="main-content">
        <div class="header">
            <h2 class="text-primary">Request Nurse</h2>
        </div>

        <div class="form-container">
            <?php if ($success): ?>
                <div class="message success"><?= htmlspecialchars($success) ?></div>
            <?php elseif ($error): ?>
                <div class="message error"><?= htmlspecialchars($error) ?></div>
            <?php endif; ?>

            <form method="post">
                <div class="form-group">
                    <label for="name">Patient Name</label>
                    <input type="text" name="name" id="name" class="form-control"
                        value="<?= htmlspecialchars($_SESSION['customer_name']) ?>" readonly>
                </div>

                <div class="form-group">
                    <label for="email">Patient Email</label>
                    <input type="email" name="email" id="email" class="form-control"
                        value="<?= htmlspecialchars($_SESSION['customer_email']) ?>" readonly>
                </div>
                <div class="form-group">
                    <label for="age">Patient Age</label>
                    <input type="text" name="age" id="age" class="form-control" placeholder="Age"
                        value="<?= $edit_nurse['age'] ?? '' ?>" required>
                </div>

                <div class="form-group">
                    <label for="nurse_id">Select Nurse</label>
                    <select name="nurse_id" id="nurse_id" class="form-control" required>
                        <option value="">Select a Nurse</option>
                        <?php while ($nurse = $nurses_result->fetch_assoc()): ?>
                            <option value="<?= $nurse['id'] ?>">
                                <?= htmlspecialchars($nurse['name']) ?>
                            </option>
                        <?php endwhile; ?>
                    </select>
                </div>

                <div class="form-group">
                    <label for="service_type">Service Type</label>
                    <select name="service_type" id="service_type" class="form-control" required>
                        <option value="">Select Service Type</option>
                        <option value="Home Care">Home Care</option>
                    </select>
                </div>

                <div class="form-group">
                    <label for="start_datetime">Start Date & Time</label>
                    <input type="datetime-local" name="start_datetime" id="start_datetime"
                        class="form-control" required>
                </div>

                <div class="form-group">
                    <label for="end_datetime">End Date & Time</label>
                    <input type="datetime-local" name="end_datetime" id="end_datetime"
                        class="form-control" required>
                </div>

                <div class="form-group">
                    <label for="notes">Additional Notes</label>
                    <textarea name="notes" id="notes" class="form-control" rows="4"
                        placeholder="Special requirements or instructions..."></textarea>
                </div>

                <button type="submit" class="btn-primary">Submit Request</button>
            </form>
        </div>


    </div>
</body>

</html>