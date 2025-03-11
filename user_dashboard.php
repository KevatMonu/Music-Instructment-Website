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
    <link rel="stylesheet" href="css/user_dashboard.css">
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
                <a href="user_order_detail.php" class="dashboard-card card-orders">
                    <div class="card-content">
                        <div class="card-info">
                            <h3>Total Orders</h3>
                            <div class="value"><?php echo $order_count; ?></div>
                        </div>
                        <div class="card-icon"><i class="lucide-package-check"></i></div>
                    </div>
                </a>

                <a href="cart.php" class="dashboard-card card-cart">
                    <div class="card-content">
                        <div class="card-info">
                            <h3>Cart Items</h3>
                            <div class="value"><?php echo $cart_count; ?></div>
                        </div>
                        <div class="card-icon"><i class="lucide-shopping-cart"></i></div>
                    </div>
                </a>
            </div>
        </main>
    </div>
</body>
</html>