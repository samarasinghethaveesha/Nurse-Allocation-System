<?php
session_start();

// Redirect if not logged in
if (!isset($_SESSION['customer_id'])) {
    header("Location: index.php");
    exit;
}

$error = $success = "";

include 'config.php';

// Fetch customer profile details
$customer_id = $_SESSION['customer_id'];
$profile_query = "SELECT phone_number, address, nic, city FROM customers WHERE id = ?";
$profile_stmt = $conn->prepare($profile_query);
$profile_stmt->bind_param("i", $customer_id);
$profile_stmt->execute();
$profile_result = $profile_stmt->get_result();
$profile = $profile_result->fetch_assoc();
if (!$profile) {
    die("Profile not found.");
}

// Fetch request details from the URL
$request_id = $_GET['request_id'] ?? null;
if (!$request_id) {
    die("Invalid request.");
}
$request_query = "SELECT id, service_type, start_datetime, end_datetime FROM nurse_requests WHERE id = ? AND status = 'approved'";
$request_stmt = $conn->prepare($request_query);
$request_stmt->bind_param("i", $request_id);
$request_stmt->execute();
$request_result = $request_stmt->get_result();
$request = $request_result->fetch_assoc();
if (!$request) {
    die("Request not found or invalid.");
}

// Calculate hours and charges
$start_datetime = new DateTime($request['start_datetime']);
$end_datetime = new DateTime($request['end_datetime']);
$interval = $start_datetime->diff($end_datetime);
$total_hours = ($interval->days * 24) + $interval->h; // Total hours (including days)
if ($total_hours <= 0) {
    die("Invalid date range for the request.");
}

// Calculate charges
$hourly_rate = 1000; // Rate per hour
$total_amount = $total_hours * $hourly_rate;
$nursing_charge = $total_amount * 0.8; // 80% of total amount
$service_charge = $total_amount * 0.2; // 20% of total amount

?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Confirm Booking - Nurse Allocation</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary: #0a6ebd;
            --success: #28a745;
            --danger: #dc3545;
            --light: #f8f9fa;
            --dark: #343a40;
            --shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
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
            justify-content: center;
            align-items: center;
        }

        .form-container {
            background: white;
            border-radius: 12px;
            padding: 30px;
            box-shadow: var(--shadow);
            width: 100%;
            max-width: 600px;
            animation: fadeIn 0.3s ease-in-out;
        }

        h2 {
            text-align: center;
            color: var(--primary);
            margin-bottom: 25px;
            font-size: 1.75rem;
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

        .readonly-field {
            background: #f8f9fa;
            cursor: not-allowed;
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
            margin-bottom: 20px;
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

        .back-link {
            text-align: center;
            margin-top: 20px;
        }

        .back-link a {
            color: var(--primary);
            text-decoration: none;
            font-weight: 500;
            transition: color 0.3s;
        }

        .back-link a:hover {
            color: #08599a;
        }

        /* Enhanced Styling for Read-Only Fields */
        .readonly-field {
            background: #f8f9fa;
            border-color: #ddd;
            color: var(--dark);
        }

        /* Highlight Total Amount Section */
        .total-section {
            background: #e9f7ff;
            padding: 15px;
            border-radius: 8px;
            margin-top: 20px;
            box-shadow: var(--shadow);
        }

        .total-section h4 {
            margin-bottom: 15px;
            color: var(--primary);
            font-size: 1.25rem;
        }

        .total-section .row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 10px;
        }

        .total-section .label {
            font-weight: 500;
            color: var(--dark);
        }

        .total-section .value {
            font-weight: 600;
            color: var(--primary);
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(-20px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        @media (max-width: 576px) {
            .form-container {
                padding: 20px;
            }

            h2 {
                font-size: 1.5rem;
            }
        }
    </style>
</head>

<body>
    <div class="form-container">
        <h2>Confirm Booking</h2>
        <?php if ($success): ?>
            <p class="message success"><?= htmlspecialchars($success) ?></p>
        <?php elseif ($error): ?>
            <p class="message error"><?= htmlspecialchars($error) ?></p>
        <?php endif; ?>
        <!-- <form id="payment-form" method="POST"> -->
        <input type="hidden" name="request_id" value="<?= htmlspecialchars($request['id']) ?>">
        <!-- City -->
        <div class="form-group">
            <label for="city">City:</label>
            <input type="text"
                name="city"
                id="city"
                class="form-control"
                value="<?= htmlspecialchars($profile['city']) ?>"
                placeholder="Enter city"
                required>
        </div>
        <!-- Address -->
        <div class="form-group">
            <label for="address">Address:</label>
            <input type="text"
                name="address"
                id="address"
                class="form-control"
                value="<?= htmlspecialchars($profile['address']) ?>"
                placeholder="Enter address"
                required>
        </div>
        <!-- Phone Number -->
        <div class="form-group">
            <label for="phone_number">Phone Number:</label>
            <input type="text"
                name="phone_number"
                id="phone_number"
                class="form-control"
                value="<?= htmlspecialchars($profile['phone_number']) ?>"
                placeholder="Enter phone number"
                required>
        </div>
        <!-- NIC -->
        <div class="form-group">
            <label for="nic">NIC:</label>
            <input type="text"
                name="nic"
                id="nic"
                class="form-control"
                value="<?= htmlspecialchars($profile['nic']) ?>"
                placeholder="Enter NIC"
                required>
        </div>
        <!-- Total Section -->
        <div class="total-section">
            <h4>Booking Summary</h4>
            <div class="row">
                <span class="label">Total Hours:</span>
                <span class="value"><?= htmlspecialchars($total_hours) ?> hrs</span>
            </div>
            <div class="row">
                <span class="label">Nursing Charge (80%):</span>
                <span class="value"><?= htmlspecialchars(number_format($nursing_charge, 2)) ?></span>
            </div>
            <div class="row">
                <span class="label">Service Charge (20%):</span>
                <span class="value"><?= htmlspecialchars(number_format($service_charge, 2)) ?></span>
            </div>
            <div class="row">
                <span class="label">Total Amount (LKR):</span>
                <span class="value"><?= htmlspecialchars(number_format($total_amount, 2)) ?></span>
            </div>
        </div>
        <button type="submit" class="btn-primary"
            onclick="payNow('<?= htmlspecialchars($request['id'], ENT_QUOTES) ?>', '<?= htmlspecialchars($total_amount, ENT_QUOTES) ?>');">
            Confirm Booking
        </button>
        <!-- </form> -->
        <div class="back-link">
            <a href="customer_dashboard.php"><i class="fas fa-arrow-left"></i> Back to Dashboard</a>
        </div>
    </div>
    <script src="script.js"></script>
    <script type="text/javascript" src="https://www.payhere.lk/lib/payhere.js"></script>
    
</body>

</html>