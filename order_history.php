<?php
session_start();
$conn = new mysqli("localhost", "root", "", "music_store");

if ($conn->connect_error) {
    die("Database connection failed: " . $conn->connect_error);
}

// Simulate logged-in customer (replace this with session data)
$customer_email = "test@example.com"; // Change this to $_SESSION['email']

// Fetch all past orders
$order_query = "SELECT * FROM orders WHERE email = ? ORDER BY created_at DESC";
$stmt = $conn->prepare($order_query);
$stmt->bind_param("s", $customer_email);
$stmt->execute();
$orders_result = $stmt->get_result();
$stmt->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Order History</title>
    <style>
        body { font-family: Arial, sans-serif; text-align: center; background: #f8f8f8; padding: 20px; }
        .container { max-width: 700px; margin: 0 auto; background: white; padding: 20px; border-radius: 10px; box-shadow: 0px 4px 10px rgba(0, 0, 0, 0.1); }
        h2 { color: #333; }
        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        th, td { padding: 10px; border-bottom: 1px solid #ddd; text-align: left; }
        th { background: #f4f4f4; }
        .btn { display: inline-block; padding: 5px 10px; background: #007bff; color: white; text-decoration: none; border-radius: 5px; }
        .btn:hover { background: #0056b3; }
    </style>
</head>
<body>

<div class="container">
    <h2>Your Order History</h2>

    <?php if ($orders_result->num_rows > 0): ?>
        <table>
            <tr>
                <th>Order ID</th>
                <th>Date</th>
                <th>Total Price</th>
                <th>Details</th>
            </tr>
            <?php while ($order = $orders_result->fetch_assoc()): ?>
                <tr>
                    <td>#<?php echo $order['id']; ?></td>
                    <td><?php echo date("d M Y, h:i A", strtotime($order['created_at'])); ?></td>
                    <td>₹<?php echo number_format($order['total_price'], 2); ?></td>
                    <td><a href="order_details.php?order_id=<?php echo $order['id']; ?>" class="btn">View</a></td>
                </tr>
            <?php endwhile; ?>
        </table>
    <?php else: ?>
        <p>No past orders found.</p>
    <?php endif; ?>

    <a href="index.php" class="btn">Back to Home</a>
</div>

</body>
</html>
