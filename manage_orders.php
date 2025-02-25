<?php
session_start();

// Check if the user is logged in and is an admin
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    echo "<script>alert('Unauthorized access!'); window.location.href='sign-in.php';</script>";
    exit();
}

$conn = new mysqli("localhost", "root", "", "musicstore_database");

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Fetch all orders with user details
$sql = "SELECT orders.*, users.full_name FROM orders LEFT JOIN users ON orders.user_ref = users.user_id ORDER BY orders.order_created DESC";
$result = $conn->query($sql);

$full_name = $_SESSION['full_name'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Orders</title>
    <link rel="stylesheet" href="style.css">
    <style>
        /* (The styles from manage_categories.php are kept here) */
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
            margin: 0;
            padding: 0;
        }
        .dashboard-container {
            display: flex;
            min-height: 100vh;
        }
        .sidebar {
            width: 250px;
            background: #007bff;
            color: white;
            padding: 20px;
            box-shadow: 2px 0 5px rgba(0, 0, 0, 0.1);
        }
        .sidebar h2 {
            text-align: center;
            margin-bottom: 20px;
        }
        .sidebar ul {
            list-style: none;
            padding: 0;
        }
        .sidebar ul li {
            padding: 10px;
            border-bottom: 1px solid #0056b3;
        }
        .sidebar ul li a {
            color: white;
            text-decoration: none;
            display: block;
        }
        .sidebar ul li a:hover {
            background: #0056b3;
            border-radius: 5px;
            padding: 10px;
        }
        .main-content {
            flex: 1;
            padding: 20px;
        }
        .logout-btn {
            margin-top: 20px;
            background: red;
            padding: 10px;
            text-align: center;
            border-radius: 5px;
        }
        .logout-btn a {
            color: white;
            text-decoration: none;
            font-weight: bold;
        }
        .logout-btn a:hover {
            opacity: 0.8;
        }
        .add-btn {
            background-color: #4CAF50;
            color: white;
            padding: 10px 15px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 16px;
            margin-bottom: 20px;
        }
        .add-btn:hover {
            background-color: #45a049;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        th, td {
            padding: 12px 15px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }
        th {
            background-color: #007bff;
            color: white;
        }
        tr:hover {
            background-color: #f5f5f5;
        }
        .action-btn {
            background-color: #007bff;
            color: white;
            padding: 6px 10px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            margin-right: 5px;
        }
        .delete-btn {
            background-color: #dc3545;
        }
    </style>
</head>
<body>

<div class="dashboard-container">
    <div class="sidebar">
        <h2>Admin Panel</h2>
        <ul>
            <li><a href="admin_dashboard.php">Dashboard</a></li>
            <li><a href="manage_users.php">Manage Users</a></li>
            <li><a href="manage_categories.php">Manage Categories</a></li>
            <li><a href="manage_products.php">Manage Products</a></li>
            <li><a href="manage_orders.php">View Orders</a></li>
            <li><a href="reports.php">Reports</a></li>
        </ul>
        <div class="logout-btn">
            <a href="logout.php">Logout</a>
        </div>
    </div>

    <div class="main-content">
        <h1>Manage Orders</h1>

        <table>
            <thead>
                <tr>
                    <th>Order ID</th>
                    <th>User</th>
                    <th>Total Cost</th>
                    <th>Order Created</th>
                    <th>Status</th>
                    <th>Type</th>
                    <th>Offer Ref</th>
                    <th>Discount</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php
                if ($result->num_rows > 0) {
                    while($row = $result->fetch_assoc()) {
                        echo "<tr>";
                        echo "<td>" . $row["order_id"] . "</td>";
                        echo "<td>" . $row["full_name"] . "</td>";
                        echo "<td>" . $row["total_cost"] . "</td>";
                        echo "<td>" . $row["order_created"] . "</td>";
                        echo "<td>" . $row["order_status"] . "</td>";
                        echo "<td>" . $row["order_type"] . "</td>";
                        echo "<td>" . $row["offer_ref"] . "</td>";
                        echo "<td>" . $row["discount_amount"] . "</td>";
                        echo "<td>
                                    <a href='edit_order.php?id=" . $row["order_id"] . "'><button class='action-btn'>Edit</button></a>
                                    <a href='delete_order.php?id=" . $row["order_id"] . "' onclick='return confirm(\"Are you sure you want to delete this order?\")'><button class='action-btn delete-btn'>Delete</button></a>
                                </td>";
                        echo "</tr>";
                    }
                } else {
                    echo "<tr><td colspan='9' style='text-align:center'>No orders found</td></tr>";
                }
                ?>
            </tbody>
        </table>
    </div>
</div>

</body>
</html>
<?php $conn->close(); ?>