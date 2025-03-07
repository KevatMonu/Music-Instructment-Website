<?php
// Start session only if not already started
if (session_status() === PHP_SESSION_NONE) {
  session_start();
}

// Database Connection (Using PDO)
try {
  $conn = new PDO("mysql:host=localhost;dbname=musicstore_database;charset=utf8mb4", "root", "");
  $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
  die("Database connection failed: " . $e->getMessage());
}

// Initialize cart if not exists
if (!isset($_SESSION['cart'])) {
  $_SESSION['cart'] = [];
}

// Add to cart functionality
if (isset($_GET['action'], $_GET['id']) && is_numeric($_GET['id'])) {
  $id = intval($_GET['id']);

  if ($_GET['action'] === 'add') {
    $_SESSION['cart'][$id] = ($_SESSION['cart'][$id] ?? 0) + 1;
    header("Location: index.php");
    exit();
  }
}

// **Check if 'product_id' column exists**
$query = "SHOW COLUMNS FROM products LIKE 'product_id'";
$stmt = $conn->prepare($query);
$stmt->execute();
if ($stmt->rowCount() == 0) {
  die("Error: 'product_id' column does not exist in 'products' table.");
}

// **Fetch Products with Category Names**
$query = "
    SELECT 
        p.product_id, 
        p.product_name, 
        p.product_price, 
        p.rental_cost, 
        p.stock_quantity, 
        p.product_image, 
        c.category_name
    FROM products p
    INNER JOIN categories c ON p.category_ref = c.category_id
    WHERE p.stock_quantity > 0
";
$stmt = $conn->prepare($query);
$stmt->execute();
$products = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Count total items in cart
$totalItems = array_sum($_SESSION['cart'] ?? []);
?>


<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>K&P Music Instrument Store</title>
  <link rel="stylesheet" href="css/style.css?v=<?php echo time(); ?>">
  <link rel="stylesheet" href="./css/product.css">
  <link rel="stylesheet" href="./css/about.css" />
  <link rel="stylesheet" href="./css/sign-in-up.css">
  <link rel="stylesheet" href="./css/contact.css">
  <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <link
    href="https://cdn.jsdelivr.net/npm/remixicon@4.5.0/fonts/remixicon.css"
    rel="stylesheet" />

  <link
    rel="stylesheet"
    href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css"
    integrity="sha512-Evv84Mr4kqVGRNSgIGL/F/aIDqQb7xQ2vcrdIwxfjThSH8CSR7PBEakCr51Ck+w+/U6swU2Im1vVX0SVk9ABhg=="
    crossorigin="anonymous"
    referrerpolicy="no-referrer" />

  <link
    rel="stylesheet"
    href="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.css" />
  <style>

  </style>
</head>

<body>
  <div id="nav">
    <div class="nav1">
      <div class="logo">
        <img src="assets/home/image/logo.png" alt="" />
      </div>
      <div class="nav-item">
        <ul id="nav-item">
          <a href="index.php">
            <li>Home</li>
          </a>
          <a href="products.php">
            <li>Product</li>
          </a>
          <a href="about.php">
            <li>About Us</li>
          </a>
          <a href="contact.php">
            <li>Contact Us</li>
          </a>
          <?php if (!isset($_SESSION['user_id'])): ?>
            <!-- Only show Sign In link when user is NOT logged in -->
            <a href="sign-in.php">
              <li>Sign In</li>
            </a>
          <?php endif; ?>
          <a href="rent.php">
            <li>Rent</li>
          </a>
        </ul>
      </div>
    </div>
    <div class="nav2">
      
      <div class="nav2-icon">
        <i class="fa-regular fa-heart"></i>
        <a href="cart.php" class="cart-link">
          <i class="fa-solid fa-cart-shopping"></i>
          <?php if ($totalItems > 0): ?>
            <span class="cart-count"><?php echo $totalItems; ?></span>
          <?php endif; ?>
        </a>
        <?php if (!isset($_SESSION['user_id'])): ?>
          <!-- Show user icon but link to sign-in when not logged in -->
          <a href="sign-in.php"><i class="fa-solid fa-user"></i></a>
        <?php else: ?>
          <!-- Link to dashboard when logged in -->
          <?php if (isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin'): ?>
            <a href="admin_dashboard.php"><i class="fa-solid fa-user-shield"></i></a>
          <?php else: ?>
            <a href="user_dashboard.php"><i class="fa-solid fa-user"></i></a>
          <?php endif; ?>
        <?php endif; ?>
      </div>
    </div>
  </div>