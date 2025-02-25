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

// Fetch all products
$sql = "SELECT products.*, categories.category_name FROM products LEFT JOIN categories ON products.category_ref = categories.category_id ORDER BY products.created_on DESC";
$result = $conn->query($sql);

$full_name = $_SESSION['full_name'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Products</title>
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
        .product-img {
            width: 100px;
            height: 100px;
            object-fit: cover;
            border-radius: 5px;
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
        <h1>Manage Products</h1>
        <a href="admin_add_product.php"><button class="add-btn">Add Product</button></a>
        
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Name</th>
                    <th>Description</th>
                    <th>Category</th>
                    <th>Price</th>
                    <th>Rental Cost</th>
                    <th>Stock</th>
                    <th>Image</th>
                    <th>Created On</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php
                if ($result->num_rows > 0) {
                    while($row = $result->fetch_assoc()) {
                        echo "<tr>";
                        echo "<td>" . $row["product_id"] . "</td>";
                        echo "<td>" . $row["product_name"] . "</td>";
                        echo "<td>" . $row["product_description"] . "</td>";
                        echo "<td>" . $row["category_name"] . "</td>";
                        echo "<td>" . $row["product_price"] . "</td>";
                        echo "<td>" . $row["rental_cost"] . "</td>";
                        echo "<td>" . $row["stock_quantity"] . "</td>";
                        echo "<td>";
                        if(!empty($row["product_image"])) {
                            $image_data = $row["product_image"];
                            $finfo = new finfo(FILEINFO_MIME_TYPE);
                            $mime_type = $finfo->buffer($image_data);
                            $base64 = base64_encode($image_data);
                            echo "<img src='data:$mime_type;base64,$base64' alt='" . $row["product_name"] . "' class='product-img'>";
                        } else {
                            echo "No image";
                        }
                        echo "</td>";
                        echo "<td>" . $row["created_on"] . "</td>";
                        echo "<td>
                                    <a href='edit_product.php?id=" . $row["product_id"] . "'><button class='action-btn'>Edit</button></a>
                                    <a href='delete_product.php?id=" . $row["product_id"] . "' onclick='return confirm(\"Are you sure you want to delete this product?\")'><button class='action-btn delete-btn'>Delete</button></a>
                                </td>";
                        echo "</tr>";
                    }
                } else {
                    echo "<tr><td colspan='10' style='text-align:center'>No products found</td></tr>";
                }
                ?>
            </tbody>
        </table>
    </div>
</div>

</body>
</html>
<?php $conn->close(); ?>