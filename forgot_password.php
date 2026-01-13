<?php
require 'vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

$error = $success = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    date_default_timezone_set('Asia/Kolkata');
    $email = filter_var($_POST['email'], FILTER_SANITIZE_EMAIL);

    if (empty($email)) {
        $error = "Please enter your email address.";
    } else {
        // Connect to database
        include 'config.php';

        // Check if email exists
        $stmt = $conn->prepare("SELECT id FROM customers WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $stmt->store_result();

        if ($stmt->num_rows === 0) {
            // Don't expose whether the email exists or not
            $success = "If this email is registered, instructions will be sent.";
        } else {
            // Generate reset token and expiration
            $token = bin2hex(random_bytes(50));
            $expires = date("Y-m-d H:i:s", strtotime("+1 hour")); // Expires in 1 hour

            // Save token and expiration in the database
            $stmt = $conn->prepare("UPDATE customers SET reset_token = ?, reset_expires = ? WHERE email = ?");
            $stmt->bind_param("sss", $token, $expires, $email);
            $stmt->execute();

            // Build reset link
            $reset_link = "http://" . $_SERVER['HTTP_HOST'] . dirname($_SERVER['PHP_SELF']) . "/reset_password.php?token=" . urlencode($token);

            // Send password reset email using PHPMailer
            try {
                $mail = new PHPMailer(true);

                // Server settings
                $mail->isSMTP();                                      // Set mailer to use SMTP
                $mail->Host = 'smtp.gmail.com';                       // Specify main and backup SMTP servers
                $mail->SMTPAuth = true;                               // Enable SMTP authentication
                $mail->Username = 'saveenkudagama2002@gmail.com';             // SMTP username (your Gmail)
                $mail->Password = 'wdfa hvme oesv ymex';                // SMTP password (App Password)
                $mail->SMTPSecure = 'tls';                            // Enable TLS encryption
                $mail->Port = 587;                                    // TCP port to connect to

                // Recipients
                $mail->setFrom('no-reply@nurseallocation.com', 'Nurse Allocation System');
                $mail->addAddress($email);                            // Add recipient

                // Content
                $mail->isHTML(true);                                  // Set email format to HTML
                $mail->Subject = 'Password Reset Request';

                // Email body (HTML)
                $mail->Body = '
                    <div style="font-family: Arial, sans-serif; max-width: 600px; margin: auto; border: 1px solid #ddd; padding: 20px; border-radius: 8px;">
                        <h2 style="color: #0a6ebd;">Password Reset Request</h2>
                        <p>We received a request to reset your password. Click the button below to proceed:</p>
                        <a href="' . $reset_link . '" style="display: inline-block; background-color: #0a6ebd; color: white; text-decoration: none; padding: 12px 20px; font-size: 16px; border-radius: 5px;">Reset Password</a>
                        <p style="margin-top:20px; font-size:14px; color:#555;">This link will expire in 1 hour.</p>
                        <p>If you did not request this change, please ignore this email.</p>
                        <hr>
                        <p style="font-size:12px; color:#999;">© 2025 Nurse Allocation System</p>
                    </div>
                ';

                // Email body (Plain Text for non-HTML clients)
                $mail->AltBody = "We received a request to reset your password. Click the following link to proceed:\n\n$reset_link\n\nThis link will expire in 1 hour.\n\nIf you did not request this change, please ignore this email.";

                $mail->send();
                $success = "If your email is registered, instructions have been sent.";
            } catch (Exception $e) {
                $error = "Failed to send email. Error: {$mail->ErrorInfo}";
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Forgot Password</title>
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
        input[type="email"] {
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
    <h2>Forgot Password</h2>
    <?php if ($success): ?>
        <p class="message success"><?= htmlspecialchars($success) ?></p>
    <?php elseif ($error): ?>
        <p class="message error"><?= htmlspecialchars($error) ?></p>
    <?php endif; ?>
    <form method="post">
        <input type="email" name="email" placeholder="Enter your email" required>
        <input type="submit" value="Send Reset Link">
    </form>
    <div style="text-align:center;margin-top:15px;">
        <a href="index.php">Back to Login</a>
    </div>
</div>
</body>
</html>