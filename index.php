<?php
session_start();
$error = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = filter_var($_POST['email'], FILTER_SANITIZE_EMAIL);
    $lpassword = $_POST['password'];

    include 'config.php';

    $stmt = $conn->prepare("SELECT id, name, email, password FROM customers WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows === 1) {
        $stmt->bind_result($id, $name, $db_email, $db_password);
        $stmt->fetch();

        if ($lpassword === $db_password) {
            $_SESSION['customer_id'] = $id;
            $_SESSION['customer_name'] = $name;
            $_SESSION['customer_email'] = $db_email;
            header("Location: customer_dashboard.php");
            exit;
        } else {
            $error = "Invalid email or password.";
        }
    } else {
        $error = "Invalid email or password.";
    }

    $stmt->close();
    $conn->close();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Welcome to Our Customer Portal</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            min-height: 100vh;
            background: linear-gradient(135deg, #0a6ebd 50%, #f0f4f8 50%);
            display: flex;
            flex-direction: column;
        }

        header {
            padding: 20px 40px;
            background: rgba(255, 255, 255, 0.9);
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            display: flex;
            justify-content: space-between;
            align-items: center;
            position: sticky;
            top: 0;
            z-index: 100;
        }

        .logo {
            font-size: 1.5rem;
            font-weight: bold;
            color: #0a6ebd;
        }

        nav a {
            margin-left: 20px;
            text-decoration: none;
            color: #333;
            font-weight: 500;
        }

        .main-content {
            flex: 1;
            display: flex;
            flex-wrap: wrap;
            padding: 40px 20px;
        }

        .hero-section {
            flex: 1 1 600px;
            padding: 40px;
            display: flex;
            flex-direction: column;
            justify-content: center;
            color: white;
        }

        .hero-section h1 {
            font-size: 3rem;
            margin-bottom: 20px;
            line-height: 1.2;
        }

        .hero-section p {
            font-size: 1.2rem;
            margin-bottom: 30px;
            opacity: 0.9;
        }

        .features {
            display: flex;
            gap: 20px;
            margin-top: 30px;
        }

        .feature {
            background: rgba(25, 79, 141, 0.5);
            padding: 15px 20px;
            border-radius: 8px;
            flex: 1;
            min-width: 200px;
        }

        .login-section {
            flex: 1 1 400px;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 40px 20px;
        }

        .login-box {
            background: white;
            padding: 30px;
            border-radius: 12px;
            box-shadow: 0 8px 30px rgba(0,0,0,0.1);
            width: 100%;
            max-width: 400px;
        }

        h2 {
            text-align: center;
            color: #0a6ebd;
            margin-bottom: 25px;
            font-size: 1.8rem;
        }

        input[type="text"], 
        input[type="password"] {
            width: 100%;
            padding: 12px 15px;
            margin: 10px 0;
            border: 2px solid #ddd;
            border-radius: 6px;
            font-size: 1rem;
            transition: border-color 0.3s;
        }

        input[type="text"]:focus,
        input[type="password"]:focus {
            border-color: #0a6ebd;
            outline: none;
        }

        input[type="submit"] {
            width: 100%;
            background-color: #0a6ebd;
            color: white;
            border: none;
            padding: 12px;
            border-radius: 6px;
            font-size: 1.1rem;
            cursor: pointer;
            transition: background-color 0.3s;
            margin-top: 10px;
        }

        input[type="submit"]:hover {
            background-color: #08599a;
        }

        .error {
            color: #e74c3c;
            text-align: center;
            margin: 15px 0;
            font-weight: 500;
        }

        .switch-link {
            text-align: center;
            margin-top: 20px;
            font-size: 0.95rem;
            color: #666;
        }

        .switch-link a {
            color: #0a6ebd;
            text-decoration: none;
            margin: 0 8px;
            font-weight: 500;
        }

        .switch-link a:hover {
            text-decoration: underline;
        }

        .forgot-password {
            display: block;
            text-align: right;
            margin-top: 10px;
            font-size: 0.9rem;
            color: #0a6ebd;
            text-decoration: none;
        }

        .forgot-password:hover {
            text-decoration: underline;
        }

        @media (max-width: 900px) {
            .main-content {
                flex-direction: column;
            }

            .features {
                flex-direction: column;
            }
        }
    </style>
</head>
<body>
    <header>
        <div class="logo">Customer Portal</div>
        <nav>
            <a href="#">Home</a>
            <a href="#">Services</a>
            <a href="#">Contact</a>
        </nav>
    </header>

    <main class="main-content">
        <div class="hero-section">
            <h1>Welcome Back to Our Customer Portal</h1>
            <p>Access your account to manage services, view history, and get support</p>
            <div class="features">
                <div class="feature">
                    <h3>24/7 Support</h3>
                    <p>Round-the-clock assistance for all your needs</p>
                </div>
                <div class="feature">
                    <h3>Easy Management</h3>
                    <p>Control all your services from one dashboard</p>
                </div>
            </div>
        </div>

        <div class="login-section">
            <div class="login-box">
                <h2>Customer Login</h2>
                <form method="post">
                    <input type="text" name="email" placeholder="Email" required>
                    <input type="password" name="password" placeholder="Password" required>
                    <a href="forgot_password.php" class="forgot-password">Forgot Password?</a>
                    <input type="submit" value="Login">
                </form>
                <?php if ($error): ?>
                    <p class="error"><?= htmlspecialchars($error) ?></p>
                <?php endif; ?>
                <div class="switch-link">
                    <p><a href="admin_login.php">Admin Login</a> | <a href="register.php">Create Account</a></p>
                </div>
            </div>
        </div>
    </main>

    <footer style="background: #0a6ebd; color: white; text-align: center; padding: 15px; font-size: 0.9rem;">
        &copy; 2025 Customer Portal. All rights reserved.
    </footer>
</body>
</html>