<?php
session_start();
$conn = new mysqli("localhost", "root", "", "music_store");

if ($conn->connect_error) {
    die("Database connection failed: " . $conn->connect_error);
}

// Check if cart is empty
if (!isset($_SESSION['cart']) || empty($_SESSION['cart'])) {
    die("Your cart is empty. <a href='products.php'>Go Back</a>");
}

// Process Order
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $customer_name = $conn->real_escape_string($_POST['name']);  // Use 'name' (matches DB column)
    $customer_email = $conn->real_escape_string($_POST['email']);  // Use 'email'
    $address = $conn->real_escape_string($_POST['address']);
    $payment_method = $conn->real_escape_string($_POST['payment_method']);

    $total_order_price = 0;

    // Calculate total price from cart
    foreach ($_SESSION['cart'] as $product_id => $quantity) {
        $query = "SELECT price FROM products WHERE id = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("i", $product_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $product = $result->fetch_assoc();
        $stmt->close();

        if ($product) {
            $total_order_price += $product['price'] * $quantity;
        }
    }

    // Insert into orders table
    $stmt = $conn->prepare("INSERT INTO orders (name, email, address, total_price, payment_method) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("sssds", $customer_name, $customer_email, $address, $total_order_price, $payment_method);
    $stmt->execute();
    $order_id = $stmt->insert_id;
    $stmt->close();

    // Insert into order_items table
    foreach ($_SESSION['cart'] as $product_id => $quantity) {
        $query = "SELECT price FROM products WHERE id = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("i", $product_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $product = $result->fetch_assoc();
        $stmt->close();

        if ($product) {
            $item_price = $product['price'] * $quantity;

            $stmt = $conn->prepare("INSERT INTO order_items (order_id, product_id, quantity, price) VALUES (?, ?, ?, ?)");
            $stmt->bind_param("iiid", $order_id, $product_id, $quantity, $item_price);
            $stmt->execute();
            $stmt->close();
        }
    }

    // Clear cart after successful order
    unset($_SESSION['cart']);

    // Redirect to success page
    header("Location: success.php?order_id=" . $order_id);
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Checkout</title>
    <style>
        body { font-family: Arial, sans-serif; text-align: center; background: #f8f8f8; padding: 20px; }
        .container { max-width: 400px; margin: 0 auto; background: white; padding: 20px; border-radius: 10px; box-shadow: 0px 4px 10px rgba(0, 0, 0, 0.1); }
        input, button { width: 100%; padding: 10px; margin: 10px 0; border: 1px solid #ddd; border-radius: 5px; }
        button { background: #28a745; color: white; cursor: pointer; }
        button:hover { background: #218838; }
    </style>
</head>
<body>

<div class="container">
    <h2>Checkout</h2>
    <form method="POST">
        <input type="text" name="name" placeholder="Enter your name" required>
        <input type="email" name="email" placeholder="Enter your email" required>
        <input type="text" name="address" placeholder="Enter your address" required>
        <select name="payment_method" required>
            <option value="Credit Card">Credit Card</option>
            <option value="PayPal">PayPal</option>
            <option value="Cash on Delivery">Cash on Delivery</option>
        </select>
        <button type="submit">Place Order</button>
    </form>
</div>

</body>
</html>
