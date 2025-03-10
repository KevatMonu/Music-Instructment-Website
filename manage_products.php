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
    <link rel="stylesheet" href="css/manage_product.css">
 
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