<?php
session_start();
$error = $success = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Sanitize and trim inputs
    $name = trim($_POST['name']);
    $email = filter_var($_POST['email'], FILTER_SANITIZE_EMAIL);
    $p = $_POST['pp'];
    $confirm_password = $_POST['confirm_password'];
    $phone_number = trim($_POST['phone_number']);
    $address = trim($_POST['address']);
    $nic = trim($_POST['nic']);
    $city = trim($_POST['city']);

    // Basic server-side validations
    if (
        empty($name) || empty($email) || empty($p) || empty($confirm_password) ||
        empty($phone_number) || empty($address) || empty($nic) || empty($city)
    ) {
        $error = "All fields are required.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Invalid email format.";
    } elseif (!preg_match('/^[0-9]{10}$/', $phone_number)) {
        $error = "Phone number must be 10 digits.";
    } elseif (!preg_match('/^[0-9]{9}[vVxX]$/', $nic) && !preg_match('/^[0-9]{12}$/', $nic)) {
        $error = "NIC must be either 9 digits + V/X or 12 digits.";
    } elseif ($p !== $confirm_password) {
        $error = "Passwords do not match.";
    } elseif (strlen($p) < 6) {
        $error = "Password must be at least 6 characters.";
    } else {
        include 'config.php';
        // Check for existing email
        $stmt = $conn->prepare("SELECT id FROM customers WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $stmt->store_result();
        if ($stmt->num_rows > 0) {
            $error = "Email already exists. Please use another.";
        } else {
            // Insert customer
            $stmt = $conn->prepare("
                INSERT INTO customers (name, email, password, phone_number, address, nic, city) 
                VALUES (?, ?, ?, ?, ?, ?, ?)
            ");
            $stmt->bind_param("sssssss", $name, $email, $p, $phone_number, $address, $nic, $city);
            if ($stmt->execute()) {
                $success = "Registration successful! <a href='index.php'>Login here</a>";
            } else {
                $error = "Something went wrong. Please try again later.";
            }
        }
    }
}
?>


<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Register - Customer Portal</title>
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
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
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
            box-shadow: 0 8px 30px rgba(0, 0, 0, 0.1);
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
        input[type="email"],
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
        input[type="email"]:focus,
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

        .success {
            color: #28a745;
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
            <a href="#">Services</a>
            <a href="#">Contact</a>
        </nav>
    </header>

    <main class="main-content">
        <div class="hero-section">
            <h1>Join Our Customer Portal</h1>
            <p>Create an account to access our services and manage your information</p>
            <div class="features">
                <div class="feature">
                    <h3>Secure Registration</h3>
                    <p>Your information is protected with industry-standard security</p>
                </div>
                <div class="feature">
                    <h3>Instant Access</h3>
                    <p>Get immediate access to our services after registration</p>
                </div>
            </div>
        </div>

        <div class="register-section">
            <div class="register-box">
                <h2>Customer Registration</h2>
                <?php if ($error): ?>
                    <p class="error"><?= htmlspecialchars($error) ?></p>
                <?php endif; ?>
                <?php if ($success): ?>
                    <p class="success"><?= $success ?></p>
                <?php endif; ?>
                <form method="post" novalidate autocomplete="off">
                    <input type="text" name="name" placeholder="Full Name" required pattern="^[a-zA-Z\s]{3,50}$" title="Only letters and spaces allowed.">
                    <input type="email" name="email" placeholder="Email Address" required>
                    <input type="password" name="pp" placeholder="Password (min 6 chars)" required minlength="6" autocomplete="new-password">
                    <input type="password" name="confirm_password" placeholder="Confirm Password" required minlength="6" autocomplete="new-password">
                    <input type="text" name="phone_number" placeholder="Phone Number" required pattern="^\d{10}$" title="Phone number must be 10 digits.">
                    <input type="text" name="address" placeholder="Address" required>
                    <input type="text" name="nic" placeholder="NIC/ID Number" required pattern="^(\d{9}[vVxX]|\d{12})$" title="Enter 9 digits + V/X or 12 digits.">
                    <input type="text" name="city" placeholder="City" required pattern="^[a-zA-Z\s]{2,50}$">
                    <input type="submit" value="Register">
                </form>

                <div class="switch-link">
                    <p>Already have an account? <a href="index.php">Login here</a></p>
                </div>
            </div>
        </div>
    </main>

    <footer style="background: #0a6ebd; color: white; text-align: center; padding: 15px; font-size: 0.9rem;">
        &copy; 2025 Customer Portal. All rights reserved.
    </footer>
</body>

</html>