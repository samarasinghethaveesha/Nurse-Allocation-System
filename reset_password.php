<?php
require 'vendor/autoload.php';
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

$error = $success = "";
$token = isset($_GET['token']) ? $_GET['token'] : null;

if (!$token) {
    die("Invalid request.");
}

include 'config.php';

// Check token validity
$stmt = $conn->prepare("SELECT id FROM customers WHERE reset_token = ? AND reset_expires > NOW()");
$stmt->bind_param("s", $token);
$stmt->execute();
$stmt->store_result();

if ($stmt->num_rows !== 1) {
    die("Invalid or expired token.");
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $rpassword = $_POST['password'];
    $rconfirm_password = $_POST['confirm_password'];

    if (empty($rpassword) || empty($rconfirm_password)) {
        $error = "All fields are required.";
    } elseif ($rpassword !== $rconfirm_password) {
        $error = "Passwords do not match.";
    } else {
        

        // Update password and clear token
        $stmt = $conn->prepare("UPDATE customers SET password = ?, reset_token = NULL, reset_expires = NULL WHERE reset_token = ?");
        $stmt->bind_param("ss", $rpassword, $token);
        if ($stmt->execute()) {
            $success = "Password updated successfully! <a href='index.php'>Login here</a>";
        } else {
            $error = "Failed to update password.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Reset Password</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background: #f4f6f9;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
        }
        .container {
            background: white;
            padding: 30px;
            border-radius: 10px;
            width: 350px;
            box-shadow: 0 4px 10px rgba(0,0,0,0.1);
        }
        h2 {
            text-align: center;
            color: #0a6ebd;
        }
        input[type="password"] {
            width: 100%;
            padding: 10px;
            margin: 10px 0;
            border: 1px solid #ccc;
            border-radius: 5px;
        }
        input[type="submit"] {
            background-color: #0a6ebd;
            color: white;
            border: none;
            padding: 10px;
            border-radius: 5px;
            width: 100%;
            cursor: pointer;
        }
        input[type="submit"]:hover {
            background-color: #08599a;
        }
        .message {
            text-align: center;
            margin-top: 10px;
        }
        .success { color: green; }
        .error { color: red; }
    </style>
</head>
<body>
<div class="container">
    <h2>Reset Your Password</h2>
    <?php if ($success): ?>
        <p class="message success"><?= $success ?></p>
    <?php elseif ($error): ?>
        <p class="message error"><?= htmlspecialchars($error) ?></p>
    <?php endif; ?>
    <form method="post">
        <input type="password" name="password" placeholder="New Password" required><br>
        <input type="password" name="confirm_password" placeholder="Confirm Password" required><br>
        <input type="submit" value="Update Password">
    </form>
</div>
</body>
</html>