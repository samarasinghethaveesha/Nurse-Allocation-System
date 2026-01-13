<?php
session_start();
$error = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_POST['username'];
    $password = $_POST['password'];

    if ($username === "admin" && $password === "password123") {
        $_SESSION['loggedin'] = true;
        $_SESSION['role'] = 'admin'; 
        header("Location: dashboard.php");
        exit;
    } else {
        $error = "Invalid username or password.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin Registration - Customer Portal</title>
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

        .register-section {
            flex: 1 1 400px;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 40px 20px;
        }

        .register-box {
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
            background-color: #28a745;
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
            background-color: #218838;
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
            <a href="#">Dashboard</a>
            <a href="#">Support</a>
        </nav>
    </header>

    <main class="main-content">
        <div class="hero-section">
            <h1>Admin Registration</h1>
            <p>Create your admin account to access the customer management dashboard</p>
            <div class="features">
                <div class="feature">
                    <h3>Secure Access</h3>
                    <p>Protect your data with secure admin authentication</p>
                </div>
                <div class="feature">
                    <h3>Full Control</h3>
                    <p>Manage customers and services from a centralized dashboard</p>
                </div>
            </div>
        </div>

        <div class="register-section">
            <div class="register-box">
                <h2>Admin Login</h2>
                <?php if ($error): ?>
                    <p class="error"><?= htmlspecialchars($error) ?></p>
                <?php endif; ?>
                <form method="post">
                    <input type="text" name="username" placeholder="Admin Username" required>
                    <input type="password" name="password" placeholder="Admin Password" required>
                    <input type="submit" value="Login">
                </form>
                <div class="switch-link">
                    <p><a href="index.php">Customer Login</a></p>
                </div>
            </div>
        </div>
    </main>

    <footer style="background: #0a6ebd; color: white; text-align: center; padding: 15px; font-size: 0.9rem;">
        &copy; 2025 Customer Portal. All rights reserved.
    </footer>
</body>
</html>