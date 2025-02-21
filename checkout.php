<?php
session_start();
$conn = new mysqli("localhost", "root", "", "music_website");

if ($conn->connect_error) {
    die("Database connection failed: " . $conn->connect_error);
}

// Check if the user is logged in
$user_id = $_SESSION['user_id'] ?? null;
if (!$user_id) {
    die("<p style='color:red;'>Please <a href='login.php'>log in</a> to proceed with checkout.</p>");
}

// Fetch cart items from database
$query = "
    SELECT c.product_id, p.name, p.price, c.quantity, c.offer_id, 
           o.discount_type, o.discount_value
    FROM cart c
    JOIN products p ON c.product_id = p.product_id
    LEFT JOIN offers o ON c.offer_id = o.offer_id
    WHERE c.user_id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

$cartItems = [];
$total_order_price = 0;

while ($row = $result->fetch_assoc()) {
    // Apply discount if applicable
    $discount = 0;
    if ($row['offer_id']) {
        if ($row['discount_type'] == 'flat') {
            $discount = $row['discount_value'];
        } elseif ($row['discount_type'] == 'percentage') {
            $discount = ($row['discount_value'] / 100) * $row['price'];
        }
    }

    $final_price = $row['price'] - $discount;
    $row['subtotal'] = $final_price * $row['quantity'];
    $total_order_price += $row['subtotal'];
    $cartItems[] = $row;
}

if (empty($cartItems)) {
    die("Your cart is empty. <a href='products.php'>Go Back</a>");
}

// Process Order
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $customer_name = $conn->real_escape_string($_POST['name']);
    $customer_email = $conn->real_escape_string($_POST['email']);
    $address = $conn->real_escape_string($_POST['address']);
    $payment_method = $conn->real_escape_string($_POST['payment_method']);

    // Insert into orders table
    $stmt = $conn->prepare("INSERT INTO orders (user_id, name, email, address, total_price, payment_method) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("isssds", $user_id, $customer_name, $customer_email, $address, $total_order_price, $payment_method);
    $stmt->execute();
    $order_id = $stmt->insert_id;
    $stmt->close();

    // Insert into order_items table
    foreach ($cartItems as $item) {
        $stmt = $conn->prepare("INSERT INTO order_items (order_id, product_id, quantity, price) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("iiid", $order_id, $item['product_id'], $item['quantity'], $item['subtotal']);
        $stmt->execute();
        $stmt->close();
    }

    // Clear cart after successful order
    $stmt = $conn->prepare("DELETE FROM cart WHERE user_id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $stmt->close();

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
