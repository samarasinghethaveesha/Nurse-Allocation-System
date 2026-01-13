<?php
session_start();

// Redirect if not logged in as admin
if (!isset($_SESSION['loggedin']) || $_SESSION['role'] !== 'admin') {
    header("Location: admin_login.php");
    exit;
}

include 'config.php';

// Handle Approve/Reject Actions
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $request_id = $_POST['request_id'];
    $action = $_POST['action'];

    if ($action === 'approve') {
        // Update status to approved
        $stmt = $conn->prepare("UPDATE nurse_requests SET status = 'approved' WHERE id = ?");
        $stmt->bind_param("i", $request_id);
        $stmt->execute();
    } elseif ($action === 'reject') {
        // Update status to rejected
        $stmt = $conn->prepare("UPDATE nurse_requests SET status = 'rejected' WHERE id = ?");
        $stmt->bind_param("i", $request_id);
        $stmt->execute();
    }
}

// Fetch all pending nurse requests
$requests_query = "
    SELECT 
        nr.id AS request_id, 
        c.name AS customer_name, 
        c.email AS customer_email, 
        nr.service_type, 
        nr.start_datetime, 
        nr.end_datetime, 
        nr.notes, 
        n.name AS nurse_name, 
        nr.status
    FROM nurse_requests nr
    LEFT JOIN customers c ON nr.customer_id = c.id
    LEFT JOIN nurses n ON nr.nurse_id = n.id
    WHERE nr.status = 'pending'
";
$requests_result = $conn->query($requests_query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin Dashboard - Nurse Allocation</title>
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
            box-shadow: 2px 0 15px rgba(0,0,0,0.1);
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
            background: rgba(255,255,255,0.1);
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

        .table-container {
            background: white;
            border-radius: 12px;
            padding: 30px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
        }

        .requests-table {
            width: 100%;
            border-collapse: collapse;
        }

        .requests-table th {
            background: var(--primary);
            color: white;
            padding: 15px;
            text-align: left;
            font-weight: 600;
        }

        .requests-table td {
            padding: 12px;
            border-bottom: 1px solid #eee;
        }

        .requests-table tr:hover {
            background: #f8f9fa;
        }

        .btn {
            padding: 8px 15px;
            border-radius: 6px;
            border: none;
            cursor: pointer;
            font-weight: 500;
            transition: all 0.3s;
        }

        .btn-approve {
            background: var(--success);
            color: white;
        }

        .btn-reject {
            background: var(--danger);
            color: white;
        }

        .btn:hover {
            opacity: 0.9;
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
            <li><a href="nurses.php"><i class="fas fa-users"></i> Manage Nurses</a></li>
            <!-- <li><a href="patients.php"><i class="fas fa-procedures"></i> Manage Patients</a></li> -->
            <!-- <li><a href="shifts.php"><i class="fas fa-calendar-alt"></i> Assign Shifts</a></li> -->
            <li><a href="recommendation.php"><i class="fas fa-chart-line"></i> Recommendations</a></li>
            <li><a href="logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
        </ul>
    </div>

    <!-- Main Content -->
    <div class="main-content">
        <div class="header">
            <h2 class="text-primary">Admin Dashboard</h2>
        </div>

        <div class="table-container">
            <h3 class="mb-4">Pending Nurse Requests</h3>
            <?php if ($requests_result->num_rows > 0): ?>
                <table class="requests-table">
                    <thead>
                        <tr>
                            <th>Request ID</th>
                            <th>Customer</th>
                            <th>Service Type</th>
                            <th>Start Date</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($request = $requests_result->fetch_assoc()): ?>
                            <tr>
                                <td><?= htmlspecialchars($request['request_id']) ?></td>
                                <td>
                                    <?= htmlspecialchars($request['customer_name']) ?><br>
                                    <small><?= htmlspecialchars($request['customer_email']) ?></small>
                                </td>
                                <td><?= htmlspecialchars($request['service_type']) ?></td>
                                <td><?= htmlspecialchars($request['start_datetime']) ?></td>
                                <td>
                                    <span class="badge 
                                        <?= $request['status'] === 'approved' ? 'badge-success' : 
                                            ($request['status'] === 'rejected' ? 'badge-danger' : 'badge-warning') ?>">
                                        <?= htmlspecialchars($request['status']) ?>
                                    </span>
                                </td>
                                <td>
                                    <form method="post" style="display:inline;">
                                        <input type="hidden" name="request_id" value="<?= $request['request_id'] ?>">
                                        <button type="submit" name="action" value="approve" 
                                                class="btn btn-approve btn-sm mr-2">
                                            Approve
                                        </button>
                                    </form>
                                    <form method="post" style="display:inline;">
                                        <input type="hidden" name="request_id" value="<?= $request['request_id'] ?>">
                                        <button type="submit" name="action" value="reject" 
                                                class="btn btn-reject btn-sm">
                                            Reject
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <div class="text-center py-4">
                    <i class="fas fa-inbox fa-3x text-muted"></i>
                    <p class="mt-3">No pending requests found</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>