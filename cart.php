<?php
session_start();

// Database Connection
$conn = new mysqli("localhost", "root", "", "music_store");

// Check Connection
if ($conn->connect_error) {
    die("Database connection failed: " . $conn->connect_error);
}

// Remove Item from Cart
if (isset($_GET['action']) && $_GET['action'] == 'remove' && isset($_GET['id'])) {
    $id = intval($_GET['id']);
    if (isset($_SESSION['cart'][$id])) {
        unset($_SESSION['cart'][$id]); // Remove the item
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

// Fetch product details
$cartItems = [];
$totalPrice = 0;

if (!empty($_SESSION['cart'])) {
    $ids = implode(',', array_keys($_SESSION['cart']));
    $sql = "SELECT * FROM products WHERE id IN ($ids)";
    $result = $conn->query($sql);
    
    while ($row = $result->fetch_assoc()) {
        $row['quantity'] = $_SESSION['cart'][$row['id']];
        $row['subtotal'] = $row['price'] * $row['quantity'];
        $totalPrice += $row['subtotal'];
        $cartItems[] = $row;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Shopping Cart</title>
    <link rel="stylesheet" href="./css/style.css">
    <link rel="stylesheet" href="./css/cart.css">
    <link
      href="https://cdn.jsdelivr.net/npm/remixicon@4.5.0/fonts/remixicon.css"
      rel="stylesheet"
    />
    <link
      rel="stylesheet"
      href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css"
      integrity="sha512-Evv84Mr4kqVGRNSgIGL/F/aIDqQb7xQ2vcrdIwxfjThSH8CSR7PBEakCr51Ck+w+/U6swU2Im1vVX0SVk9ABhg=="
      crossorigin="anonymous"
      referrerpolicy="no-referrer"
    />
   
</head>
<body>
<div id="nav">
      <div class="nav1">
        <div class="logo">
          <img src="assets/home/image/logo.png" alt="" />
        </div>
        <div class="nav-item">
          <ul id="nav-item">
            <a href="index.php"><li>Home</li> </a>
            <a href="products.php"><li>Product</li> </a>
            <a href="about.html"><li>About Us</li> </a>
            <a href="contact.hmtl"><li>Contact Us</li></a>
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
                <th>Quantity</th>
                <th>Subtotal</th>
                <th>Action</th>
            </tr>
            <?php foreach ($cartItems as $item) { ?>
                <tr>
                    <td><img src="<?php echo htmlspecialchars($item['image']); ?>" alt="Product" width="50"></td>
                    <td><?php echo htmlspecialchars($item['name']); ?></td>
                    <td>₹<?php echo number_format($item['price'], 2); ?></td>
                    <td><?php echo $item['quantity']; ?></td>
                    <td>₹<?php echo number_format($item['subtotal'], 2); ?></td>
                    <td><a href="cart.php?action=remove&id=<?php echo $item['id']; ?>" class="remove-btn">Remove</a></td>
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
