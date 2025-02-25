<?php
session_start();

// Database Connection
$conn = new mysqli("localhost", "root", "", "musicstore_database");

// Check Connection
if ($conn->connect_error) {
    die("Database connection failed: " . $conn->connect_error);
}

// Initialize cart if not exists
if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

// Remove Item from Cart
if (isset($_GET['action']) && $_GET['action'] == 'remove' && isset($_GET['id'])) {
    $id = intval($_GET['id']);
    if (isset($_SESSION['cart'][$id])) {
        unset($_SESSION['cart'][$id]);
    }
    header("Location: cart.php");
    exit();
}

// Clear Cart
if (isset($_GET['action']) && $_GET['action'] == 'clear') {
    $_SESSION['cart'] = [];
    header("Location: cart.php");
    exit();
}

// Fetch Cart Items
$cartItems = [];
$totalPrice = 0;

if (!empty($_SESSION['cart'])) {
    foreach ($_SESSION['cart'] as $product_id => $quantity) {
        // Fetch product details from database securely
        $stmt = $conn->prepare("SELECT product_id, product_name, product_image, product_price FROM products WHERE product_id = ?");
        $stmt->bind_param("i", $product_id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($row = $result->fetch_assoc()) {
            $row['quantity'] = $quantity;
            $row['subtotal'] = $row['product_price'] * $quantity;
            $totalPrice += $row['subtotal'];
            $cartItems[] = $row;
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Shopping Cart</title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="css/cart.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css" />
</head>
<body>

<!-- Navigation -->
<div id="nav">
    <div class="nav1">
        <div class="logo">
            <img src="assets/home/image/logo.png" alt="Logo" />
        </div>
        <div class="nav-item">
            <ul id="nav-item">
                <a href="index.php"><li>Home</li></a>
                <a href="products.php"><li>Products</li></a>
                <a href="about.html"><li>About Us</li></a>
                <a href="contact.html"><li>Contact Us</li></a>
            </ul>
        </div>
    </div>
    <div class="nav2">
        <div class="nav2-1">
            <input type="search" placeholder="Search product..." />
            <i class="fa-solid fa-magnifying-glass"></i>
        </div>
        <div class="nav2-icon">
            <a href="cart.php"><i class="fa-solid fa-cart-shopping"></i></a>
            <a href="user_dashboard.php"><i class="fa-solid fa-user"></i></a>
        </div>
    </div>
</div>

<h1>Your Shopping Cart</h1>
<div class="cart-container">
    <?php if (!empty($cartItems)) { ?>
        <table>
            <tr>
                <th>Image</th>
                <th>Product</th>
                <th>Price</th>
                <th>Quantity</th>
                <th>Subtotal</th>
                <th>Action</th>
            </tr>
            <?php foreach ($cartItems as $item) { ?>
                <tr>
                    <td>
                        <img src="<?php echo htmlspecialchars($item['product_image'] ?? 'assets/placeholder.png'); ?>" 
                             alt="Product" width="50">
                    </td>
                    <td><?php echo htmlspecialchars($item['product_name']); ?></td>
                    <td>₹<?php echo number_format($item['product_price'], 2); ?></td>
                    <td><?php echo $item['quantity']; ?></td>
                    <td>₹<?php echo number_format($item['subtotal'], 2); ?></td>
                    <td>
                        <a href="cart.php?action=remove&id=<?php echo $item['product_id']; ?>" 
                           class="remove-btn">Remove</a>
                    </td>
                </tr>
            <?php } ?>
        </table>
        <h2>Total: ₹<?php echo number_format($totalPrice, 2); ?></h2>
        <a href="checkout.php" class="checkout-btn">Proceed to Checkout</a>
        <a href="cart.php?action=clear" class="clear-btn">Clear Cart</a>
    <?php } else { ?>
        <p>Your cart is empty.</p>
    <?php } ?>
    <a href="products.php" class="back-btn">← Continue Shopping</a>
</div>

</body>
</html>

<?php
// Close the database connection
$conn->close();
?>
