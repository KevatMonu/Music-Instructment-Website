<?php
session_start();

// Database connection
$conn = new mysqli("localhost", "root", "", "musicstore_database");

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: sign-in.php");
    exit();
}

// Fetch user data
$user_id = $_SESSION['user_id'];
$stmt = $conn->prepare("SELECT full_name FROM users WHERE user_id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();
$full_name = $user['full_name'];

// Get order count
$stmt = $conn->prepare("SELECT COUNT(*) as order_count FROM orders WHERE user_ref = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$order_data = $result->fetch_assoc();
$order_count = $order_data['order_count'];

// Get cart count
$stmt = $conn->prepare("SELECT COUNT(*) as cart_count FROM cart WHERE user_ref = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$cart_data = $result->fetch_assoc();
$cart_count = $cart_data['cart_count'];

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Customer Dashboard</title>
    <link rel="stylesheet" href="https://unpkg.com/lucide-static@latest/font/lucide.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, sans-serif;
        }

        body {
            background-color: #f3f4f6;
            min-height: 100vh;
        }

        .dashboard {
            display: flex;
            min-height: 100vh;
        }

        /* Sidebar */
        .sidebar {
            width: 280px;
            background: linear-gradient(to bottom, #2563eb, #1d4ed8);
            color: white;
            padding: 1.5rem;
            position: fixed;
            height: 100vh;
            overflow-y: auto;
        }

        .sidebar-header {
            text-align: center;
            margin-bottom: 2rem;
        }

        .nav-list {
            list-style: none;
        }

        .nav-link {
            display: flex;
            align-items: center;
            padding: 0.75rem 1rem;
            color: white;
            text-decoration: none;
            border-radius: 0.5rem;
            transition: background-color 0.2s;
        }

        .nav-link:hover {
            background-color: rgba(255, 255, 255, 0.1);
        }

        .logout-btn {
            display: flex;
            align-items: center;
            justify-content: center;
            width: 100%;
            padding: 0.75rem;
            background-color: #ef4444;
            color: white;
            text-decoration: none;
            border-radius: 0.5rem;
            transition: background-color 0.2s;
        }

        .logout-btn:hover {
            background-color: #dc2626;
        }

        /* Main Content */
        .main-content {
            flex: 1;
            margin-left: 280px;
            padding: 2rem;
        }

        .welcome-banner {
            background: white;
            padding: 2rem;
            border-radius: 1rem;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.05);
            margin-bottom: 2rem;
        }

        .dashboard-cards {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 1.5rem;
            margin-bottom: 2rem;
        }

        .dashboard-card {
            background: white;
            padding: 1.5rem;
            border-radius: 1rem;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.05);
            transition: transform 0.2s;
            position: relative;
            overflow: hidden;
        }

        .dashboard-card:hover {
            transform: translateY(-2px);
        }

        .card-orders::before {
            background-color: #2563eb;
        }

        .card-cart::before {
            background-color: #10b981;
        }

        .card-icon {
            width: 48px;
            height: 48px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .card-orders .card-icon {
            background-color: rgba(37, 99, 235, 0.1);
            color: #2563eb;
        }

        .card-cart .card-icon {
            background-color: rgba(16, 185, 129, 0.1);
            color: #10b981;
        }
    </style>
</head>
<body>
    <div class="dashboard">
        <!-- Sidebar -->
        <div class="sidebar">
            <div class="sidebar-header">
                <h2>Customer Panel</h2>
            </div>
            <nav>
                <ul class="nav-list">
                    <li><a href="user_dashboard.php" class="nav-link"><i class="lucide-home"></i> Dashboard</a></li>
                    <li><a href="products.php" class="nav-link"><i class="lucide-package"></i> View Products</a></li>
                    <li><a href="cart.php" class="nav-link"><i class="lucide-shopping-cart"></i> View Cart</a></li>
                    <li><a href="user_order_detail.php" class="nav-link"><i class="lucide-clipboard-list"></i> View Orders</a></li>
                    <li><a href="profile.php" class="nav-link"><i class="lucide-user"></i> Profile</a></li>
                </ul>
            </nav>
            <div class="logout-section">
                <a href="logout.php" class="logout-btn"><i class="lucide-log-out"></i> Logout</a>
            </div>
        </div>

        <!-- Main Content -->
        <main class="main-content">
            <div class="welcome-banner">
                <h1>Welcome back, <?php echo htmlspecialchars($full_name); ?>! 👋</h1>
                <p>Here's what's happening with your store today.</p>
            </div>

            <div class="dashboard-cards">
                <div class="dashboard-card card-orders">
                    <div class="card-content">
                        <div class="card-info">
                            <h3>Total Orders</h3>
                            <div class="value"><?php echo $order_count; ?></div>
                        </div>
                        <div class="card-icon"><i class="lucide-package-check"></i></div>
                    </div>
                </div>

                <div class="dashboard-card card-cart">
                    <div class="card-content">
                        <div class="card-info">
                            <h3>Cart Items</h3>
                            <div class="value"><?php echo $cart_count; ?></div>
                        </div>
                        <div class="card-icon"><i class="lucide-shopping-cart"></i></div>
                    </div>
                </div>
            </div>
        </main>
    </div>
</body>
</html>
