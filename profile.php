<?php
session_start();
if (!isset($_SESSION['customer_id'])) {
    header("Location: index.php");
    exit;
}

include 'config.php';

$customer_id = $_SESSION['customer_id'];
$error = $success = "";

// Fetch current customer data
$stmt = $conn->prepare("
    SELECT name, email, profile_pic, phone_number, address, nic, city 
    FROM customers 
    WHERE id = ?
");
$stmt->bind_param("i", $customer_id);
$stmt->execute();
$stmt->bind_result($name, $email, $profile_pic, $phone_number, $address, $nic, $city);
$stmt->fetch();
$stmt->close();

// Handle form submission for name/email update
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['update_profile'])) {
    $new_name = trim($_POST['name']);
    $new_email = filter_var($_POST['email'], FILTER_SANITIZE_EMAIL);
    $new_phone_number = trim($_POST['phone_number']);
    $new_address = trim($_POST['address']);
    $new_nic = trim($_POST['nic']);
    $new_city = trim($_POST['city']);

    if (empty($new_name) || empty($new_email) || empty($new_phone_number) || empty($new_address) || empty($new_nic) || empty($new_city)) {
        $error = "All fields are required.";
    } else {
        // Update database
        $stmt = $conn->prepare("
            UPDATE customers 
            SET name = ?, email = ?, phone_number = ?, address = ?, nic = ?, city = ? 
            WHERE id = ?
        ");
        $stmt->bind_param("ssssssi", $new_name, $new_email, $new_phone_number, $new_address, $new_nic, $new_city, $customer_id);
        if ($stmt->execute()) {
            $_SESSION['customer_name'] = $new_name; // Update session name
            $success = "Your profile has been updated successfully!";
        
        } else {
            $error = "Failed to update profile.";
        }
    }
}

// Handle profile picture upload
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_FILES['profile_pic'])) {
    $target_dir = "uploads/";
    $file_name = $customer_id . "_" . basename($_FILES["profile_pic"]["name"]);
    $target_file = $target_dir . $file_name;
    $imageFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));

    $check = getimagesize($_FILES["profile_pic"]["tmp_name"]);
    if ($check === false) {
        $error = "File is not an image.";
    } elseif (!in_array($imageFileType, ['jpg', 'jpeg', 'png', 'gif'])) {
        $error = "Only JPG, JPEG, PNG & GIF files are allowed.";
    } elseif (move_uploaded_file($_FILES["profile_pic"]["tmp_name"], $target_file)) {
        // Save filename in database
        $stmt = $conn->prepare("UPDATE customers SET profile_pic = ? WHERE id = ?");
        $stmt->bind_param("si", $file_name, $customer_id);
        if ($stmt->execute()) {
            $_SESSION['profile_pic'] = $file_name;
            $success = "Profile picture updated successfully!";
            
        } else {
            $error = "Database error while saving picture.";
        }
    } else {
        $error = "Sorry, there was an error uploading your file.";
    }
}

// Handle account deletion
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['delete_account'])) {
    try {
        // Start a transaction to ensure atomicity
        $conn->begin_transaction();

        // Delete related reviews first (cascade manually)
        $stmt = $conn->prepare("DELETE FROM reviews WHERE customer_id = ?");
        $stmt->bind_param("i", $customer_id);
        $stmt->execute();

        // Delete the customer
        $stmt = $conn->prepare("DELETE FROM customers WHERE id = ?");
        $stmt->bind_param("i", $customer_id);
        $stmt->execute();

        // Commit the transaction
        $conn->commit();

        // Unset session variables and redirect to logout
        session_unset();
        session_destroy();
        header("Location: index.php");
        exit;
    } catch (Exception $e) {
        // Rollback in case of error
        $conn->rollback();
        $error = "An error occurred while deleting your account. Please try again later.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>My Profile - Nurse Allocation</title>
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

        .profile-header {
            display: flex;
            align-items: center;
            margin-bottom: 40px;
        }

        .profile-picture {
            width: 120px;
            height: 120px;
            border-radius: 50%;
            object-fit: cover;
            border: 4px solid var(--light);
            box-shadow: 0 4px 10px rgba(0,0,0,0.1);
            margin-right: 30px;
        }

        .profile-info {
            flex: 1;
        }

        .profile-name {
            font-size: 1.5rem;
            color: var(--dark);
            margin-bottom: 10px;
        }

        .profile-email {
            color: #666;
            margin-bottom: 15px;
        }

        .form-section {
            background: white;
            border-radius: 12px;
            padding: 30px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
            margin-bottom: 30px;
        }

        .form-section h3 {
            color: var(--primary);
            margin-bottom: 25px;
            position: relative;
        }

        .form-section h3::after {
            content: '';
            position: absolute;
            left: 0;
            bottom: -10px;
            width: 40px;
            height: 2px;
            background: var(--primary);
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

        .upload-section {
            text-align: center;
            padding: 30px;
            background: white;
            border-radius: 12px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
        }

        .upload-section input[type="file"] {
            margin: 20px 0;
        }

        @media (max-width: 992px) {
            .sidebar {
                width: 60px;
            }
            .main-content {
                margin-left: 60px;
            }
            .profile-header {
                flex-direction: column;
                align-items: center;
                text-align: center;
            }
            .profile-picture {
                margin-right: 0;
                margin-bottom: 20px;
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
        <div class="profile-header">
            <div class="profile-picture-container">
                <?php if ($profile_pic): ?>
                    <img src="uploads/<?= htmlspecialchars($profile_pic) ?>" 
                         alt="Profile Picture" 
                         class="profile-picture">
                <?php else: ?>
                    <div class="profile-placeholder">
                        <i class="fas fa-user-circle fa-5x"></i>
                    </div>
                <?php endif; ?>
            </div>
            <div class="profile-info">
                <div class="profile-name"><?= htmlspecialchars($name) ?></div>
                <div class="profile-email"><?= htmlspecialchars($email) ?></div>
            </div>
        </div>

        <!-- Profile Form -->
        <div class="form-section">
            <h3>Personal Information</h3>
            <?php if ($success): ?>
                <div class="message success"><?= htmlspecialchars($success) ?></div>
            <?php elseif ($error): ?>
                <div class="message error"><?= htmlspecialchars($error) ?></div>
            <?php endif; ?>
            
            <form method="post">
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="name">Full Name</label>
                            <input type="text" 
                                   name="name" 
                                   id="name" 
                                   class="form-control" 
                                   value="<?= htmlspecialchars($name) ?>" 
                                   required>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="email">Email Address</label>
                            <input type="email" 
                                   name="email" 
                                   id="email" 
                                   class="form-control" 
                                   value="<?= htmlspecialchars($email) ?>" 
                                   required>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="phone_number">Phone Number</label>
                            <input type="text" 
                                   name="phone_number" 
                                   id="phone_number" 
                                   class="form-control" 
                                   value="<?= htmlspecialchars($phone_number) ?>" 
                                   required>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="nic">NIC Number</label>
                            <input type="text" 
                                   name="nic" 
                                   id="nic" 
                                   class="form-control" 
                                   value="<?= htmlspecialchars($nic) ?>" 
                                   required>
                        </div>
                    </div>
                </div>
                <div class="form-group">
                    <label for="address">Address</label>
                    <input type="text" 
                           name="address" 
                           id="address" 
                           class="form-control" 
                           value="<?= htmlspecialchars($address) ?>" 
                           required>
                </div>
                <div class="form-group">
                    <label for="city">City</label>
                    <input type="text" 
                           name="city" 
                           id="city" 
                           class="form-control" 
                           value="<?= htmlspecialchars($city) ?>" 
                           required>
                </div>
                <button type="submit" name="update_profile" class="btn-primary">Save Changes</button>
            </form>
        </div>

        <!-- Profile Picture Upload -->
        <div class="upload-section">
            <h3>Update Profile Picture</h3>
            <form method="post" enctype="multipart/form-data">
                <input type="file" 
                       name="profile_pic" 
                       accept="image/*" 
                       class="form-control"
                       required>
                <button type="submit" class="btn-primary mt-3">Upload Picture</button>
            </form>
        </div>
        <!-- Delete Account Section -->
        <div class="form-section">
            <h3>Delete Account</h3>
            <p class="text-danger">Warning: Deleting your account will permanently remove all your data, including reviews. This action cannot be undone.</p>
            <form method="post">
                <button type="submit" name="delete_account" class="btn-primary" onclick="return confirm('Are you sure you want to delete your account? This action is irreversible.')">Delete Account</button>
            </form>
        </div>
    </div>
</body>
</html>