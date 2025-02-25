<?php
session_start();

// Database Connection
$conn = new mysqli("localhost", "root", "", "musicstore_database");

// Check Connection
if ($conn->connect_error) {
    die("Database connection failed: " . $conn->connect_error);
}

// Check if user is signed in
if (!isset($_SESSION['user_id'])) {
    // User is not signed in, redirect to login page
    $_SESSION['redirect_after_login'] = 'checkout.php'; // Set redirect after login
    header("Location: sign-in.php");
    exit();
}

// Initialize variables
$user_id = $_SESSION['user_id'];
$error = '';
$success = '';
$cartItems = [];
$totalPrice = 0;
$discount_amount = 0; // Default discount is 0

// Fetch user information
$stmt = $conn->prepare("SELECT * FROM users WHERE user_id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$user_result = $stmt->get_result();
$user_data = $user_result->fetch_assoc();

// Fetch Cart Items
if (!empty($_SESSION['cart'])) {
    foreach ($_SESSION['cart'] as $product_id => $quantity) {
        // Fetch product details from database
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

// Process order submission
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['place_order'])) {
    // Get form data
    $address = $_POST['address'];
    $city = $_POST['city'];
    $state = $_POST['state'];
    $pincode = $_POST['pincode'];
    $payment_method = $_POST['payment_method'];
    $order_type = ($payment_method == "Cash on Delivery") ? "COD" : "Online";
    
    // Validate input
    if (empty($address) || empty($city) || empty($state) || empty($pincode)) {
        $error = "All shipping details are required.";
    } else {
        // Start transaction
        $conn->begin_transaction();
        
        try {
            // 1. Create order in orders table
            $order_date = date("Y-m-d H:i:s");
            $status = "Pending";
            $offer_ref = null; // Set to actual offer reference if using offers
            
            // Insert order into your orders table structure
            $stmt = $conn->prepare("INSERT INTO orders (user_ref, total_cost, order_created, order_status, order_type, offer_ref, discount_amount) VALUES (?, ?, ?, ?, ?, ?, ?)");
            $stmt->bind_param("idsssis", $user_id, $totalPrice, $order_date, $status, $order_type, $offer_ref, $discount_amount);
            $stmt->execute();
            
            $order_id = $conn->insert_id;
            
            // 2. Store shipping information in a session variable or in user_addresses table if you have one
            $_SESSION['last_order_shipping'] = [
                'address' => $address,
                'city' => $city,
                'state' => $state,
                'pincode' => $pincode
            ];
            
            // // 3. Add order items
            // foreach ($cartItems as $item) {
            //     $product_id = $item['product_id'];
            //     $quantity = $item['quantity'];
            //     $price = $item['product_price'];
                
            //     $stmt = $conn->prepare("INSERT INTO order_items (order_id, product_id, quantity, price) VALUES (?, ?, ?, ?)");
            //     $stmt->bind_param("iiid", $order_id, $product_id, $quantity, $price);
            //     $stmt->execute();
            // }
            
            // 4. Commit transaction
            $conn->commit();
            
            // 5. Clear cart
            $_SESSION['cart'] = [];
            
            // 6. Set success message
            $success = "Order placed successfully! Your order ID is: " . $order_id;
            
        } catch (Exception $e) {
            // Rollback transaction on error
            $conn->rollback();
            $error = "Error placing order: " . $e->getMessage();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Checkout</title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="css/cart.css">
    <link href="https://cdn.jsdelivr.net/npm/remixicon@4.5.0/fonts/remixicon.css" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css" />
    <style>
        .checkout-container {
            max-width: 1200px;
            margin: 20px auto;
            padding: 20px;
            background-color: #fff;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }
        
        .checkout-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 30px;
        }
        
        .checkout-form {
            padding: 20px;
            border: 1px solid #ddd;
            border-radius: 8px;
        }
        
        .order-summary {
            padding: 20px;
            background-color: #f9f9f9;
            border-radius: 8px;
        }
        
        .form-group {
            margin-bottom: 15px;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
        }
        
        .form-group input, .form-group select {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 4px;
        }
        
        .place-order-btn {
            background-color: #4CAF50;
            color: white;
            border: none;
            padding: 12px 20px;
            border-radius: 4px;
            cursor: pointer;
            font-size: 16px;
            width: 100%;
            margin-top: 20px;
        }
        
        .place-order-btn:hover {
            background-color: #45a049;
        }
        
        .error-message {
            color: red;
            margin: 10px 0;
        }
        
        .success-message {
            color: green;
            margin: 10px 0;
            font-size: 18px;
            text-align: center;
            padding: 20px;
            background-color: #f0f8f0;
            border-radius: 8px;
        }
        
        .summary-item {
            display: flex;
            justify-content: space-between;
            margin-bottom: 10px;
            padding-bottom: 10px;
            border-bottom: 1px solid #eee;
        }
        
        .total-line {
            font-weight: bold;
            font-size: 18px;
            margin-top: 15px;
            padding-top: 15px;
            border-top: 2px solid #ddd;
        }
    </style>
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
            <a href="cart.php"><i class="fa-solid fa-cart-shopping"></i></a>
            <i class="fa-solid fa-user"></i>
        </div>
    </div>
</div>

<h1>Checkout</h1>

<div class="checkout-container">
    <?php if ($success): ?>
        <div class="success-message">
            <?php echo $success; ?>
            <p>Thank you for your order! You will receive a confirmation email shortly.</p>
            <p>Your order will be delivered to: 
                <?php echo htmlspecialchars($_SESSION['last_order_shipping']['address']); ?>, 
                <?php echo htmlspecialchars($_SESSION['last_order_shipping']['city']); ?>, 
                <?php echo htmlspecialchars($_SESSION['last_order_shipping']['state']); ?> - 
                <?php echo htmlspecialchars($_SESSION['last_order_shipping']['pincode']); ?>
            </p>
            <a href="index.php" class="back-btn">Return to Homepage</a>
        </div>
    <?php else: ?>
        <?php if (empty($cartItems)): ?>
            <p>Your cart is empty. <a href="products.php">Go shopping</a></p>
        <?php else: ?>
            <?php if ($error): ?>
                <div class="error-message"><?php echo $error; ?></div>
            <?php endif; ?>
            
            <div class="checkout-grid">
                <div class="checkout-form">
                    <h2>Shipping Information</h2>
                    <form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">
                        <div class="form-group">
                            <label for="name">Full Name</label>
                            <input type="text" id="name" name="name" value="<?php echo htmlspecialchars($user_data['name'] ?? ''); ?>" required>
                        </div>
                        <div class="form-group">
                            <label for="email">Email</label>
                            <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($user_data['email'] ?? ''); ?>" required readonly>
                        </div>
                        <div class="form-group">
                            <label for="phone">Phone</label>
                            <input type="text" id="phone" name="phone" value="<?php echo htmlspecialchars($user_data['phone'] ?? ''); ?>" required>
                        </div>
                        <div class="form-group">
                            <label for="address">Address</label>
                            <input type="text" id="address" name="address" required>
                        </div>
                        <div class="form-group">
                            <label for="city">City</label>
                            <input type="text" id="city" name="city" required>
                        </div>
                        <div class="form-group">
                            <label for="state">State</label>
                            <input type="text" id="state" name="state" required>
                        </div>
                        <div class="form-group">
                            <label for="pincode">Pincode</label>
                            <input type="text" id="pincode" name="pincode" required>
                        </div>
                        
                        <h2>Payment Method</h2>
                        <div class="form-group">
                            <label for="payment_method">Select Payment Method</label>
                            <select id="payment_method" name="payment_method" required>
                                <option value="Cash on Delivery">Cash on Delivery</option>
                                <option value="Credit Card">Credit Card</option>
                                <option value="UPI">UPI</option>
                            </select>
                        </div>
                        
                        <button type="submit" name="place_order" class="place-order-btn">Place Order</button>
                    </form>
                </div>
                
                <div class="order-summary">
                    <h2>Order Summary</h2>
                    <?php foreach ($cartItems as $item): ?>
                        <div class="summary-item">
                            <div>
                                <?php echo htmlspecialchars($item['product_name']); ?> 
                                <span>(x<?php echo $item['quantity']; ?>)</span>
                            </div>
                            <div>₹<?php echo number_format($item['subtotal'], 2); ?></div>
                        </div>
                    <?php endforeach; ?>
                    
                    <div class="summary-item total-line">
                        <div>Total</div>
                        <div>₹<?php echo number_format($totalPrice, 2); ?></div>
                    </div>
                    
                    <a href="cart.php" class="back-btn">← Back to Cart</a>
                </div>
            </div>
        <?php endif; ?>
    <?php endif; ?>
</div>

</body>
</html>

<?php $conn->close(); ?>