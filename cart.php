<?php
session_start();

// Database Connection
$conn = new mysqli("localhost", "root", "", "music_website");

// Check Connection
if ($conn->connect_error) {
    die("Database connection failed: " . $conn->connect_error);
}

// Get Logged-In User ID (Assuming user is logged in)
$user_id = $_SESSION['user_id'] ?? null;

// if (!$user_id) {
//     die("<p style='color:red;'>Please <a href='login.php'>log in</a> to view your cart.</p>");
// }

// Remove Item from Cart
if (isset($_GET['action']) && $_GET['action'] == 'remove' && isset($_GET['id'])) {
    $id = intval($_GET['id']);
    $stmt = $conn->prepare("DELETE FROM cart WHERE cart_id = ? AND user_id = ?");
    $stmt->bind_param("ii", $id, $user_id);
    $stmt->execute();
    header("Location: cart.php");
    exit();
}

// Clear Cart
if (isset($_GET['action']) && $_GET['action'] == 'clear') {
    $stmt = $conn->prepare("DELETE FROM cart WHERE user_id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    header("Location: cart.php");
    exit();
}

// Fetch Cart Items from Database
$cartItems = [];
$totalPrice = 0;

$query = "
    SELECT c.cart_id, p.product_id, p.name, p.image, p.price, c.quantity, c.type, c.offer_id,
           o.discount_type, o.discount_value
    FROM cart c
    JOIN products p ON c.product_id = p.product_id
    LEFT JOIN offers o ON c.offer_id = o.offer_id
    WHERE c.user_id = ?
";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

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
    $totalPrice += $row['subtotal'];
    $cartItems[] = $row;
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
    <link href="https://cdn.jsdelivr.net/npm/remixicon@4.5.0/fonts/remixicon.css" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css" />
</head>
<body>

<!-- Navigation -->
<div id="nav">
    <div class="nav1">
        <div class="logo"><img src="assets/home/image/logo.png" alt="Logo" /></div>
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
            <i class="fa-regular fa-heart"></i>
            <i class="fa-solid fa-user"></i>
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
                <th>Discount</th>
                <th>Quantity</th>
                <th>Subtotal</th>
                <th>Action</th>
            </tr>
            <?php foreach ($cartItems as $item) { ?>
                <tr>
                    <td><img src="<?php echo htmlspecialchars($item['image']); ?>" alt="Product" width="50"></td>
                    <td><?php echo htmlspecialchars($item['name']); ?></td>
                    <td>₹<?php echo number_format($item['price'], 2); ?></td>
                    <td>
                        <?php if ($item['offer_id']) { ?>
                            -₹<?php echo number_format($item['price'] - ($item['subtotal'] / $item['quantity']), 2); ?>
                        <?php } else { echo "-"; } ?>
                    </td>
                    <td><?php echo $item['quantity']; ?></td>
                    <td>₹<?php echo number_format($item['subtotal'], 2); ?></td>
                    <td><a href="cart.php?action=remove&id=<?php echo $item['cart_id']; ?>" class="remove-btn">Remove</a></td>
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

<?php $conn->close(); ?>
