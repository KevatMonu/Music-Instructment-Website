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

        /* Sidebar Styles */
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

        .sidebar-header h2 {
            font-size: 1.5rem;
            font-weight: bold;
        }

        .nav-list {
            list-style: none;
        }

        .nav-item {
            margin-bottom: 0.5rem;
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

        .nav-link i {
            margin-right: 0.75rem;
        }

        .logout-section {
            margin-top: 2rem;
            padding-top: 1.5rem;
            border-top: 1px solid rgba(255, 255, 255, 0.2);
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

        .logout-btn i {
            margin-right: 0.5rem;
        }

        /* Main Content Styles */
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

        .welcome-banner h1 {
            font-size: 2rem;
            color: #1f2937;
            margin-bottom: 0.5rem;
        }

        .welcome-banner p {
            color: #6b7280;
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

        .dashboard-card::before {
            content: '';
            position: absolute;
            left: 0;
            top: 0;
            bottom: 0;
            width: 4px;
        }

        .card-orders::before {
            background-color: #2563eb;
        }

        .card-cart::before {
            background-color: #10b981;
        }

        .card-content {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .card-info h3 {
            font-size: 0.875rem;
            color: #6b7280;
            margin-bottom: 0.5rem;
        }

        .card-info .value {
            font-size: 1.5rem;
            font-weight: bold;
            color: #1f2937;
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

        .recent-activity {
            background: white;
            padding: 1.5rem;
            border-radius: 1rem;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.05);
        }

        .recent-activity h2 {
            font-size: 1.25rem;
            color: #1f2937;
            margin-bottom: 1rem;
        }

        .activity-item {
            display: flex;
            align-items: center;
            padding: 1rem;
            background: #f9fafb;
            border-radius: 0.5rem;
            margin-bottom: 0.75rem;
        }

        .activity-icon {
            margin-right: 1rem;
        }

        .activity-details p {
            margin: 0;
        }

        .activity-description {
            font-size: 0.875rem;
            color: #1f2937;
            font-weight: 500;
        }

        .activity-date {
            font-size: 0.75rem;
            color: #6b7280;
        }

        @media (max-width: 1024px) {
            .sidebar {
                width: 240px;
            }
            .main-content {
                margin-left: 240px;
            }
        }

        @media (max-width: 768px) {
            .dashboard {
                flex-direction: column;
            }
            .sidebar {
                width: 100%;
                height: auto;
                position: relative;
            }
            .main-content {
                margin-left: 0;
            }
            .dashboard-cards {
                grid-template-columns: 1fr;
            }
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
                    <li class="nav-item">
                        <a href="user_dashboard.php" class="nav-link">
                            <i class="lucide-home"></i>
                            <span>Dashboard</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="products.php" class="nav-link">
                            <i class="lucide-package"></i>
                            <span>View Products</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="cart.php" class="nav-link">
                            <i class="lucide-shopping-cart"></i>
                            <span>View Cart</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="user_order_detail.php" class="nav-link">
                            <i class="lucide-clipboard-list"></i>
                            <span>View Orders</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="profile.php" class="nav-link">
                            <i class="lucide-user"></i>
                            <span>Profile</span>
                        </a>
                    </li>
                </ul>
            </nav>

            <div class="logout-section">
                <a href="logout.php" class="logout-btn">
                    <i class="lucide-log-out"></i>
                    <span>Logout</span>
                </a>
            </div>
        </div>

        <!-- Main Content -->
        <main class="main-content">
            <!-- Welcome Banner -->
            <div class="welcome-banner">
                <h1>Welcome back, <?php echo htmlspecialchars($full_name); ?>! 👋</h1>
                <p>Here's what's happening with your store today.</p>
            </div>

            <!-- Dashboard Cards -->
            <div class="dashboard-cards">
                <!-- Orders Card -->
                <div class="dashboard-card card-orders">
                    <div class="card-content">
                        <div class="card-info">
                            <h3>Total Orders</h3>
                            <div class="value"><?php echo $order_count; ?></div>
                        </div>
                        <div class="card-icon">
                            <i class="lucide-package-check"></i>
                        </div>
                    </div>
                </div>

                <!-- Cart Card -->
                <div class="dashboard-card card-cart">
                    <div class="card-content">
                        <div class="card-info">
                            <h3>Cart Items</h3>
                            <div class="value"><?php echo $cart_count; ?></div>
                        </div>
                        <div class="card-icon">
                            <i class="lucide-shopping-cart"></i>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Recent Activity -->
            <div class="recent-activity">
                <h2>Recent Activity</h2>
                <div id="recent-activity">
                    <p>Loading recent activity...</p>
                </div>
            </div>
        </main>
    </div>

    <script>
        async function fetchRecentActivity() {
            try {
                const response = await fetch('get_recent_activity.php');
                const data = await response.json();
                
                const activityContainer = document.getElementById('recent-activity');
                if (data.length === 0) {
                    activityContainer.innerHTML = '<p>No recent activity found.</p>';
                    return;
                }

                activityContainer.innerHTML = data.map(activity => `
                    <div class="activity-item">
                        <div class="activity-icon">
                            <i class="lucide-${activity.type === 'order' ? 'package' : 'shopping-cart'}" 
                               style="color: ${activity.type === 'order' ? '#2563eb' : '#10b981'}"></i>
                        </div>
                        <div class="activity-details">
                            <p class="activity-description">${activity.description}</p>
                            <p class="activity-date">${activity.date}</p>
                        </div>
                    </div>
                `).join('');
            } catch (error) {
                console.error('Error fetching recent activity:', error);
                document.getElementById('recent-activity').innerHTML = 
                    '<p style="color: #ef4444;">Error loading recent activity. Please try again later.</p>';
            }
        }

        document.addEventListener('DOMContentLoaded', fetchRecentActivity);
    </script>
</body>
</html>