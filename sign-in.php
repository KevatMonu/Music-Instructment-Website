<?php
session_start();
$conn = new mysqli("localhost", "root", "", "musicstore_database");

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);

    if (empty($email) || empty($password)) {
        echo "<script>alert('Please enter both email and password.'); window.location.href='sign-in.php';</script>";
        exit();
    }

    $query = "SELECT * FROM users WHERE email_address = ? AND user_password = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("ss", $email, $password);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();
        $_SESSION['user_id'] = $user['user_id'];
        $_SESSION['full_name'] = $user['full_name'];
        $_SESSION['user_role'] = $user['user_role'];

        if ($user['user_role'] == 'admin') {
            echo "<script>alert('Admin Login Successful!'); window.location.href='admin_dashboard.php';</script>";
        } else { // Implicitly, if not admin, it's a customer
            echo "<script>alert('Customer Login Successful!'); window.location.href='user_dashboard.php';</script>";
        }
    } else {
        echo "<script>alert('Invalid email or password!'); window.location.href='sign-in.php';</script>";
    }
    $stmt->close();
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>MusicStore - Login</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css" />
    <style>
        :root {
            --primary-color: red;
            --secondary-color: #d4a373;
            --accent-color: #cc4c2f;
            --text-color: #2b2b2b;
            --heading-color: #000;
            --bg-color: #fdf6eb;
            --card-bg: #fff8e6;
            --border-color: #e0c9a6;
            --hover-color: #f4e3c3;
            --price-color: #d32f2f;
            --old-price-color: #888888;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }

        .navbar {
            background: linear-gradient(to right, var(--primary-color), var(--secondary-color));
            color: white;
            padding: 15px 0;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
            position: relative;
            z-index: 10;
        }

        .navbar-container {
            display: flex;
            align-items: center;
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 20px;
        }

        .logo {
            display: flex;
            align-items: center;
            text-decoration: none;
            color: white;
            font-size: 1.4rem;
            font-weight: 700;
        }

        .logo i {
            font-size: 1.8rem;
            margin-right: 12px;
        }

        .page-title {
            margin-left: 20px;
            font-size: 1.3rem;
            font-weight: 500;
            border-left: 2px solid rgba(255, 255, 255, 0.5);
            padding-left: 20px;
        }

        .main-container {
            flex: 1;
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 40px 20px;
        }

        .login-card {
            background-color: var(--card-bg);
            width: 100%;
            max-width: 420px;
            border-radius: 16px;
            box-shadow: 0 12px 30px rgba(0, 0, 0, 0.1);
            overflow: hidden;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }

        .login-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.15);
        }

        .card-header {
            background: linear-gradient(to right, var(--primary-color), var(--secondary-color));
            color: white;
            padding: 25px 30px;
            position: relative;
            overflow: hidden;
        }

        .card-header h1 {
            font-size: 1.8rem;
            font-weight: 600;
            margin-bottom: 10px;
        }

        .card-header p {
            font-size: 1rem;
            opacity: 0.9;
        }

        .circle-decoration {
            position: absolute;
            background: rgba(255, 255, 255, 0.1);
            border-radius: 50%;
        }

        .circle-1 {
            width: 150px;
            height: 150px;
            top: -40px;
            right: -40px;
        }

        .circle-2 {
            width: 80px;
            height: 80px;
            bottom: -15px;
            right: 50px;
        }

        .card-body {
            padding: 30px;
        }

        .form-group {
            margin-bottom: 24px;
            position: relative;
        }

        .form-group label {
            display: block;
            font-weight: 600;
            margin-bottom: 10px;
            color: var(--text-color);
        }

        .form-control {
            width: 100%;
            padding: 12px 15px;
            font-size: 1rem;
            border: 2px solid #e1e5ee;
            border-radius: 8px;
            background-color: #f8f9fa;
            transition: all 0.3s ease;
        }

        .form-control:focus {
            outline: none;
            border-color: var(--primary-color);
            background-color: white;
            box-shadow: 0 0 0 3px rgba(67, 97, 238, 0.15);
        }

        .password-field {
            position: relative;
        }

        .toggle-password {
            position: absolute;
            right: 15px;
            top: 12px;
            cursor: pointer;
            color: #888;
            font-size: 1.1rem;
            z-index: 10;
        }

        .login-btn {
            display: block;
            width: 100%;
            padding: 14px;
            background: linear-gradient(to right, var(--primary-color), var(--secondary-color));
            border: none;
            border-radius: 8px;
            color: white;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            box-shadow: 0 4px 15px rgba(67, 97, 238, 0.2);
        }

        .login-btn:hover {
            background: linear-gradient(to right, var(--primary-dark), var(--secondary-color));
            box-shadow: 0 6px 18px rgba(67, 97, 238, 0.3);
        }

        .form-footer {
            margin-top: 25px;
            text-align: center;
        }

        .form-footer a {
            color: var(--primary-color);
            text-decoration: none;
            font-weight: 600;
            transition: color 0.3s ease;
        }

        .form-footer a:hover {
            color: var(--secondary-color);
            text-decoration: underline;
        }

        .form-divider {
            display: flex;
            align-items: center;
            margin: 20px 0;
        }

        .form-divider::before, .form-divider::after {
            content: "";
            flex: 1;
            height: 1px;
            background-color: #e1e5ee;
        }

        .form-divider-text {
            padding: 0 15px;
            color: #888;
            font-size: 0.9rem;
        }

        /* Animation */
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .login-card {
            animation: fadeIn 0.6s ease-out;
        }

        /* Responsive adjustments */
        @media (max-width: 480px) {
            .login-card {
                border-radius: 12px;
            }

            .card-header {
                padding: 20px;
            }

            .card-body {
                padding: 20px;
            }

            .navbar {
                padding: 10px 0;
            }
        }
    </style>
</head>
<body>

<div class="navbar">
    <div class="navbar-container">
        <a href="index.php" class="logo">
            <i class="fas fa-music"></i>
            K&P Music Instruments
        </a>
        <div class="page-title">Login</div>
    </div>
</div>

<div class="main-container">
    <div class="login-card">
        <div class="card-header">
            <h1>Welcome Back</h1>
            <p>Sign in to continue to MusicStore</p>
            <div class="circle-decoration circle-1"></div>
            <div class="circle-decoration circle-2"></div>
        </div>

        <div class="card-body">
            <form action="sign-in.php" method="POST">
                <div class="form-group">
                    <label for="email">Email Address</label>
                    <input type="email" id="email" name="email" class="form-control" placeholder="Enter your email" required>
                </div>

                <div class="form-group">
                    <label for="password">Password</label>
                    <div class="password-field">
                        <input type="password" id="password" name="password" class="form-control" placeholder="Enter your password" required>
                        <i class="fas fa-eye toggle-password" id="togglePassword"></i>
                    </div>
                </div>

                <button type="submit" class="login-btn">Sign In</button>

                <div class="form-divider">
                    <span class="form-divider-text">or</span>
                </div>

                <div class="form-footer">
                    <p>Don't have an account? <a href="sign-up.php">Register</a></p>
                    <p style="margin-top: 10px;"><a href="forgot-password.php">Forgot Password?</a></p>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    document.getElementById("togglePassword").addEventListener("click", function() {
        const passwordField = document.getElementById("password");
        const icon = this;

        if (passwordField.type === "password") {
            passwordField.type = "text";
            icon.classList.remove("fa-eye");
            icon.classList.add("fa-eye-slash");
        } else {
            passwordField.type = "password";
            icon.classList.remove("fa-eye-slash");
            icon.classList.add("fa-eye");
        }
    });
</script>

</body>
</html>