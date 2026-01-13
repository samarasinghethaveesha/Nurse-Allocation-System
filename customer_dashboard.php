<?php
session_start();

// Redirect if not logged in
if (!isset($_SESSION['customer_id'])) {
    header("Location: index.php");
    exit;
}

$customer_id = $_SESSION['customer_id'];
$customer_name = $_SESSION['customer_name'];
$customer_email = isset($_SESSION['customer_email']) ? $_SESSION['customer_email'] : 'Not Available';

include 'config.php';

// Fetch available nurses
$nurses_query = "
    SELECT 
        n.id, 
        n.name, 
        n.age,
        n.experience_years, 
        n.mobile, 
        AVG(r.rating) AS average_rating
    FROM nurses n
    LEFT JOIN reviews r ON n.id = r.nurse_id
    WHERE n.status = 'available'
    GROUP BY n.id, n.name, n.experience_years, n.mobile
";
$nurses_result = $conn->query($nurses_query);

// Fetch requests made by the customer
$requests_query = "SELECT id, service_type, start_datetime, end_datetime, status FROM nurse_requests WHERE customer_id = ? AND status IN ('pending', 'approved','confirmed')";
$requests_stmt = $conn->prepare($requests_query);
$requests_stmt->bind_param("i", $customer_id);
$requests_stmt->execute();
$requests_result = $requests_stmt->get_result();

// Fetch previous bookings (completed requests)
$bookings_query = "SELECT id, service_type,start_datetime, end_datetime, status FROM nurse_requests WHERE customer_id = ? AND status = 'confirmed'";
$bookings_stmt = $conn->prepare($bookings_query);
$bookings_stmt->bind_param("i", $customer_id);
$bookings_stmt->execute();
$bookings_result = $bookings_stmt->get_result();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Customer Dashboard</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary: #0a6ebd;
            --success: #28a745;
            --danger: #dc3545;
            --warning: #ffc107;
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

        /* Sidebar */
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

        /* Main Content */
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

        .user-info {
            display: flex;
            align-items: center;
        }

        .user-info img {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            margin-right: 15px;
            object-fit: cover;
        }

        .welcome-message {
            color: var(--dark);
            font-size: 1.2rem;
        }

        .card-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 30px;
            margin-bottom: 40px;
        }

        .card {
            background: white;
            border-radius: 12px;
            padding: 25px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
            transition: transform 0.3s;
        }

        .card:hover {
            transform: translateY(-5px);
        }

        .card-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 15px;
        }

        .card-title {
            color: var(--primary);
            font-size: 1.25rem;
            margin-bottom: 10px;
        }

        .card-meta {
            color: #666;
            font-size: 0.9rem;
            margin: 10px 0;
        }

        .rating-stars {
            display: flex;
            align-items: center;
            margin: 15px 0;
        }

        .rating-stars i {
            color: #FFD700;
            margin-right: 3px;
            font-size: 1.2rem;
        }

        .btn {
            display: inline-block;
            padding: 10px 20px;
            border-radius: 6px;
            text-decoration: none;
            font-weight: 500;
            transition: all 0.3s;
        }

        .btn-primary {
            background: var(--primary);
            color: white;
        }

        .btn-success {
            background: var(--success);
            color: white;
        }

        .btn-danger {
            background: var(--danger);
            color: white;
        }

        .btn:hover {
            opacity: 0.9;
        }

        /* Modals */
        .modal {
            display: none;
            position: fixed;
            z-index: 9999;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background: rgba(0,0,0,0.7);
        }

        .modal-content {
            background: white;
            margin: 5% auto;
            padding: 30px;
            border-radius: 15px;
            width: 90%;
            max-width: 500px;
            position: relative;
        }

        .modal-header {
            border-bottom: 1px solid #eee;
            padding-bottom: 15px;
            margin-bottom: 20px;
        }

        .modal-title {
            color: var(--primary);
            font-size: 1.5rem;
        }

        .close {
            position: absolute;
            right: 15px;
            top: 10px;
            font-size: 24px;
            cursor: pointer;
            color: #aaa;
        }

        /* Responsive */
        @media (max-width: 992px) {
            .sidebar {
                width: 60px;
                padding: 20px;
            }
            
            .sidebar-brand span {
                display: none;
            }
            
            .main-content {
                margin-left: 60px;
            }
            
            .sidebar-menu a span {
                display: none;
            }
        }

        @media (max-width: 768px) {
            .header {
                flex-direction: column;
                align-items: flex-start;
            }
            
            .user-info {
                margin-bottom: 15px;
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
            <li><a href="request_nurse.php"><i class="fas fa-plus"></i> Request Nurse</a></li>
            <li><a href="profile.php"><i class="fas fa-user"></i> Profile</a></li>
            <li><a href="logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
        </ul>
    </div>

    <!-- Main Content -->
    <div class="main-content">
        <div class="header">
            <div class="user-info">
                <img src="data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='40' height='40' fill='%230a6ebd' class='bi bi-person-circle' viewBox='0 0 16 16'%3E%3Cpath d='M11 6a3 3 0 1 1-6 0 3 3 0 0 1 6 0z'/%3E%3Cpath fill-rule='evenodd' d='M0 8a8 8 0 1 1 16 0A8 8 0 0 1 0 8zm8-7a7 7 0 0 0-5.468 11.37C3.242 11.226 4.805 10 8 10s4.757 1.225 5.468 2.37A7 7 0 0 0 8 1z'/%3E%3C/svg%3E" alt="User">
                <div class="welcome-message">
                    Welcome back, <?= htmlspecialchars($customer_name) ?>!<br>
                    <small><?= htmlspecialchars($customer_email) ?></small>
                </div>
            </div>
        </div>

        <!-- Available Nurses -->
        <h3 class="card-title">Available Nurses</h3>
        <div class="card-grid">
            <?php if ($nurses_result->num_rows > 0): ?>
                <?php while ($nurse = $nurses_result->fetch_assoc()): ?>
                    <div class="card">
                        <div class="card-header">
                            <div class="card-title"><?= htmlspecialchars($nurse['name']) ?>
                            <br/>
                            Age : <?= htmlspecialchars($nurse['age']) ?>
                        </div>
                            <div class="card-meta"><?= htmlspecialchars($nurse['mobile']) ?></div>
                        </div>
                        <div class="card-body">
                            <p><strong>Experience:</strong> <?= htmlspecialchars($nurse['experience_years']) ?> years</p>
                            <div class="rating-stars">
                                <?php
                                $average_rating = isset($nurse['average_rating']) ? round($nurse['average_rating'], 1) : 0;
                                echo "<span class='mr-2'>" . htmlspecialchars($average_rating) . "/5</span>";
                                for ($i = 1; $i <= 5; $i++): ?>
                                    <i class="fas fa-star<?= ($i <= $average_rating) ? '' : '-regular' ?>"></i>
                                <?php endfor; ?>
                            </div>
                            <button class="btn btn-primary mt-3" onclick="openRatingsModal(this)" data-nurse-id="<?= htmlspecialchars($nurse['id']) ?>">
                                View Ratings
                            </button>
                        </div>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <div class="card">
                    <div class="card-body text-center">
                        <i class="fas fa-exclamation-circle fa-2x mb-3 text-warning"></i>
                        <p>No nurses are currently available.</p>
                    </div>
                </div>
            <?php endif; ?>
        </div>

        <!-- Your Requests -->
        <h3 class="card-title">Your Requests</h3>
        <div class="card-grid">
            <?php if ($requests_result->num_rows > 0): ?>
                <?php while ($request = $requests_result->fetch_assoc()): ?>
                    <div class="card">
                        <div class="card-header">
                            <div class="card-title">Request #<?= htmlspecialchars($request['id']) ?></div>
                            <div class="card-meta"><?= htmlspecialchars($request['service_type']) ?></div>
                        </div>
                        <div class="card-body">
                            <p><strong>Start:</strong> <?= htmlspecialchars($request['start_datetime']) ?></p>
                            <p><strong>End:</strong> <?= htmlspecialchars($request['end_datetime']) ?></p>
                            <p class="badge <?= $request['status'] === 'approved' ? 'badge-success' : 'badge-warning' ?>">
                                <?= htmlspecialchars($request['status']) ?>
                            </p>
                            <?php if ($request['status'] === 'approved'): ?>
                                <a href="confirm_booking.php?request_id=<?= htmlspecialchars($request['id']) ?>" class="btn btn-success mt-3">
                                    Confirm Booking
                                </a>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <div class="card">
                    <div class="card-body text-center">
                        <i class="fas fa-inbox fa-2x mb-3 text-muted"></i>
                        <p>You have not made any requests yet.</p>
                    </div>
                </div>
            <?php endif; ?>
        </div>

        <!-- Previous Bookings -->
        <h3 class="card-title">Previous Bookings</h3>
        <div class="card-grid">
            <?php if ($bookings_result->num_rows > 0): ?>
                <?php while ($booking = $bookings_result->fetch_assoc()): ?>
                    <div class="card">
                        <div class="card-header">
                            <div class="card-title">Booking #<?= htmlspecialchars($booking['id']) ?></div>
                            <div class="card-meta"><?= htmlspecialchars($booking['service_type']) ?></div>
                        </div>
                        <div class="card-body">
                            <p><strong>Start:</strong> <?= htmlspecialchars($booking['start_datetime']) ?></p>
                            <p><strong>End:</strong> <?= htmlspecialchars($booking['end_datetime']) ?></p>
                            <p class="badge <?= $booking['status'] === 'confirmed' ? 'badge-success' : 'badge-info' ?>">
                                <?= htmlspecialchars($booking['status']) ?>
                            </p>
                            <?php
                            $booking_id = $booking['id'];
                            $review_check_query = "SELECT id FROM reviews WHERE customer_id = ? AND booking_id = ?";
                            $review_check_stmt = $conn->prepare($review_check_query);
                            $review_check_stmt->bind_param("ii", $_SESSION['customer_id'], $booking_id);
                            $review_check_stmt->execute();
                            $review_check_result = $review_check_stmt->get_result();
                            $review_exists = $review_check_result->num_rows > 0;
                            ?>
                            <?php if ($review_exists): ?>
                                <button class="btn btn-success mt-3" disabled>
                                    <i class="fas fa-check"></i> Review Added
                                </button>
                            <?php else: ?>
                                <button class="btn btn-primary mt-3" onclick="openReviewModal(this)" data-booking-id="<?= htmlspecialchars($booking['id']) ?>">
                                    Add Review
                                </button>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <div class="card">
                    <div class="card-body text-center">
                        <i class="fas fa-history fa-2x mb-3 text-muted"></i>
                        <p>No previous bookings found.</p>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Modals -->
    <div id="ratings-modal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Ratings for <span id="modal-nurse-name"></span></h5>
                <span class="close" onclick="closeRatingsModal()">&times;</span>
            </div>
            <div id="ratings-list"></div>
        </div>
    </div>

    <div id="review-modal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Add Review</h5>
                <span class="close" onclick="closeReviewModal()">&times;</span>
            </div>
            <form method="post" id="review-form">
                <input type="hidden" name="booking_id" id="booking-id">
                <input type="hidden" name="nurse_id" id="nurse-id">
                <input type="hidden" name="service_type" id="service-type">
                <div class="form-group">
                    <label>Rating (1–5):</label>
                    <input type="number" name="rating" id="rating" min="1" max="5" required class="form-control">
                </div>
                <div class="form-group">
                    <label>Comment:</label>
                    <textarea name="comment" id="comment" rows="4" class="form-control"></textarea>
                </div>
                <button type="submit" class="btn btn-primary btn-block mt-3">Submit Review</button>
            </form>
        </div>
    </div>

    <script src="script.js"></script>
</body>
</html>