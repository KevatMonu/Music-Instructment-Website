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

// Fetch user information including address details
$stmt = $conn->prepare("SELECT user_id, full_name, email_address, phone_number, user_address, created_on FROM users WHERE user_id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$user_result = $stmt->get_result();
$user_data = $user_result->fetch_assoc();

// Process user address if it exists
$address_parts = [
    'address' => '',
    'city' => '',
    'state' => '',
    'pincode' => ''
];

if (!empty($user_data['user_address'])) {
    // Assuming the address is stored in format: "address, city, state - pincode"
    $address_string = $user_data['user_address'];
    
    // Extract pincode (if it exists after a dash)
    if (strpos($address_string, ' - ') !== false) {
        $parts = explode(' - ', $address_string);
        $address_string = $parts[0];
        $address_parts['pincode'] = trim($parts[1]);
    }
    
    // Extract state, city, and address
    $comma_parts = explode(',', $address_string);
    $count = count($comma_parts);
    
    if ($count >= 3) {
        $address_parts['state'] = trim($comma_parts[$count-1]);
        $address_parts['city'] = trim($comma_parts[$count-2]);
        $address_parts['address'] = trim(implode(',', array_slice($comma_parts, 0, $count-2)));
    } elseif ($count == 2) {
        $address_parts['state'] = trim($comma_parts[1]);
        $address_parts['city'] = trim($comma_parts[0]);
    } elseif ($count == 1) {
        $address_parts['address'] = trim($comma_parts[0]);
    }
}

// Fetch Cart Items
if (!empty($_SESSION['cart'])) {
    foreach ($_SESSION['cart'] as $product_id => $quantity) {
        // Fetch product details from database
        $stmt = $conn->prepare("SELECT product_id, product_name, product_price FROM products WHERE product_id = ?");
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
    $order_type = "buy"; // Default to buy
    
    // Validate input
    if (empty($address) || empty($city) || empty($state) || empty($pincode)) {
        $error = "All shipping details are required.";
    } else {
        // Start transaction
        $conn->begin_transaction();
        
        try {
            // 1. Create order in orders table
            $order_date = date("Y-m-d H:i:s");
            $status = "completed"; // Default status
            $offer_ref = null; // Set to actual offer reference if using offers
            
            // Insert order into orders table
            $stmt = $conn->prepare("INSERT INTO orders (user_ref, total_cost, order_created, order_status, order_type, offer_ref, discount_amount) VALUES (?, ?, ?, ?, ?, ?, ?)");
            $stmt->bind_param("idsssis", $user_id, $totalPrice, $order_date, $status, $order_type, $offer_ref, $discount_amount);
            $stmt->execute();
            
            $order_id = $conn->insert_id;
            
            // 2. Store shipping information in a session variable
            $_SESSION['last_order_shipping'] = [
                'address' => $address,
                'city' => $city,
                'state' => $state,
                'pincode' => $pincode
            ];
            
            // 3. Add order items
            foreach ($cartItems as $item) {
                $product_id = $item['product_id'];
                $quantity = $item['quantity'];
                $price = $item['product_price'];
                
                // Insert into order_items table
                $stmt = $conn->prepare("INSERT INTO order_items (order_ref, product_ref, item_quantity, item_price) VALUES (?, ?, ?, ?)");
                $stmt->bind_param("iiid", $order_id, $product_id, $quantity, $price);
                $stmt->execute();
            }
            
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
    <title>Checkout | Music Store</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="css/checkout.css">
</head>
<body>
    <!-- Navigation -->
    <header class="navbar">
        <div class="navbar-container">
            <div class="navbar-logo">
                <i class="fas fa-music"></i>
                <span>Music Store</span>
            </div>
            <nav class="navbar-menu">
                <ul>
                    <li><a href="index.php">Home</a></li>
                    <li><a href="products.php">Products</a></li>
                    <li><a href="about.php">About Us</a></li>
                    <li><a href="contact.php">Contact Us</a></li>
                </ul>
            </nav>
            <div class="navbar-actions">
                <div class="search-box">
                    <input type="search" placeholder="Search products...">
                    <i class="fas fa-search"></i>
                </div>
                <a href="cart.php" class="icon-button">
                    <i class="fas fa-shopping-cart"></i>
                </a>
                <a href="account.php" class="icon-button">
                    <i class="fas fa-user"></i>
                </a>
            </div>
        </div>
    </header>

    <main class="container">
        <h1 class="page-title">Checkout</h1>
        
        <!-- Checkout Steps -->
        <div class="checkout-steps">
            <div class="step">
                <div class="step-number">1</div>
                <div class="step-title">Shopping Cart</div>
            </div>
            <div class="step-line"></div>
            <div class="step active">
                <div class="step-number">2</div>
                <div class="step-title">Checkout Details</div>
            </div>
            <div class="step-line"></div>
            <div class="step <?php echo $success ? 'active' : ''; ?>">
                <div class="step-number">3</div>
                <div class="step-title">Order Complete</div>
            </div>
        </div>
        
        <?php if ($success): ?>
            <!-- Order Success -->
            <div class="success-message">
                <i class="fas fa-check-circle success-icon"></i>
                <h2 class="success-title">Order Placed Successfully!</h2>
                <p>Thank you for your purchase. You will receive a confirmation email shortly.</p>
                
                <div class="order-details">
                    <div class="detail-row">
                        <div class="detail-label">Order ID:</div>
                        <div class="detail-value"><?php echo htmlspecialchars(substr($success, strrpos($success, ':') + 2)); ?></div>
                    </div>
                    <div class="detail-row">
                        <div class="detail-label">Shipping Address:</div>
                        <div class="detail-value">
                            <?php echo htmlspecialchars($_SESSION['last_order_shipping']['address']); ?>,
                            <?php echo htmlspecialchars($_SESSION['last_order_shipping']['city']); ?>,
                            <?php echo htmlspecialchars($_SESSION['last_order_shipping']['state']); ?> - 
                            <?php echo htmlspecialchars($_SESSION['last_order_shipping']['pincode']); ?>
                        </div>
                    </div>
                    <div class="detail-row">
                        <div class="detail-label">Amount Paid:</div>
                        <div class="detail-value">₹<?php echo number_format($totalPrice - $discount_amount, 2); ?></div>
                    </div>
                </div>
                
                <a href="index.php" class="btn btn-primary">
                    <i class="fas fa-home"></i> Return to Homepage
                </a>
            </div>
        <?php elseif (empty($cartItems)): ?>
            <!-- Empty Cart -->
            <div class="empty-cart">
                <i class="fas fa-shopping-cart"></i>
                <h2>Your Cart is Empty</h2>
                <p>Add items to your cart before proceeding to checkout.</p>
                <a href="products.php" class="btn btn-primary">
                    <i class="fas fa-music"></i> Browse Products
                </a>
            </div>
        <?php else: ?>
            <!-- Error Message -->
            <?php if ($error): ?>
                <div class="error-message">
                    <i class="fas fa-exclamation-circle"></i> <?php echo $error; ?>
                </div>
            <?php endif; ?>
            
            <!-- Checkout Form and Order Summary -->
            <div class="checkout-grid">
                <div class="checkout-form">
                    <form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">
                        <!-- Personal Information -->
                        <div class="form-section">
                            <div class="section-header">
                                <i class="fas fa-user"></i>
                                <h3>Personal Information</h3>
                            </div>
                            
                            <div class="form-row">
                                <div class="form-group">
                                    <label for="name">Full Name</label>
                                    <input type="text" id="name" name="name" value="<?php echo htmlspecialchars($user_data['full_name'] ?? ''); ?>" required>
                                </div>
                                <div class="form-group">
                                    <label for="phone">Phone Number</label>
                                    <input type="text" id="phone" name="phone" value="<?php echo htmlspecialchars($user_data['phone_number'] ?? ''); ?>" required>
                                </div>
                            </div>
                            
                            <div class="form-group">
                                <label for="email">Email Address</label>
                                <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($user_data['email_address'] ?? ''); ?>" readonly>
                            </div>
                        </div>
                        
                        <!-- Shipping Information -->
                        <div class="form-section">
                            <div class="section-header">
                                <i class="fas fa-map-marker-alt"></i>
                                <h3>Shipping Information</h3>
                            </div>
                            
                            <div class="form-group">
                                <label for="address">Street Address</label>
                                <input type="text" id="address" name="address" value="<?php echo htmlspecialchars($address_parts['address']); ?>" required>
                            </div>
                            
                            <div class="form-row">
                                <div class="form-group">
                                    <label for="city">City</label>
                                    <input type="text" id="city" name="city" value="<?php echo htmlspecialchars($address_parts['city']); ?>" required>
                                </div>
                                <div class="form-group">
                                    <label for="state">State</label>
                                    <input type="text" id="state" name="state" value="<?php echo htmlspecialchars($address_parts['state']); ?>" required>
                                </div>
                            </div>
                            
                            <div class="form-group">
                                <label for="pincode">Pincode</label>
                                <input type="text" id="pincode" name="pincode" value="<?php echo htmlspecialchars($address_parts['pincode']); ?>" required>
                            </div>
                        </div>
                        
                        <!-- Payment Method -->
                        <div class="form-section">
                            <div class="section-header">
                                <i class="fas fa-credit-card"></i>
                                <h3>Payment Method</h3>
                            </div>
                            
                            <div class="payment-options">
                                <label class="payment-option">
                                    <input type="radio" name="payment_method" value="Cash on Delivery" checked>
                                    <div class="payment-option-content">
                                        <i class="fas fa-money-bill-wave"></i>
                                        <span>Cash on Delivery</span>
                                    </div>
                                </label>
                                
                                <label class="payment-option">
                                    <input type="radio" name="payment_method" value="Credit Card">
                                    <div class="payment-option-content">
                                        <i class="fas fa-credit-card"></i>
                                        <span>Credit Card</span>
                                    </div>
                                </label>
                                
                                <label class="payment-option">
                                    <input type="radio" name="payment_method" value="UPI">
                                    <div class="payment-option-content">
                                        <i class="fas fa-mobile-alt"></i>
                                        <span>UPI</span>
                                    </div>
                                </label>
                            </div>
                        </div>
                        
                        <!-- Place Order Button -->
                        <button type="submit" name="place_order" class="btn btn-primary place-order-btn">
                            <i class="fas fa-lock"></i> Place Order
                        </button>
                    </form>
                </div>
                
                <!-- Order Summary -->
                <div class="order-summary">
                    <div class="summary-header">
                        <h3>Order Summary</h3>
                    </div>
                    
                    <div class="summary-content">
                        <div class="summary-items">
                            <?php foreach ($cartItems as $item): ?>
                                <div class="summary-item">
                                    <div class="item-details">
                                        <div class="item-name"><?php echo htmlspecialchars($item['product_name']); ?></div>
                                        <div class="item-quantity">Qty: <?php echo $item['quantity']; ?></div>
                                    </div>
                                    <div class="item-price">₹<?php echo number_format($item['subtotal'], 2); ?></div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                        
                        <div class="summary-totals">
                            <div class="total-row">
                                <span>Subtotal</span>
                                <span>₹<?php echo number_format($totalPrice, 2); ?></span>
                            </div>
                            <div class="total-row">
                                <span>Shipping</span>
                                <span class="free-shipping">Free</span>
                            </div>
                            <?php if ($discount_amount > 0): ?>
                                <div class="total-row">
                                    <span>Discount</span>
                                    <span class="discount">-₹<?php echo number_format($discount_amount, 2); ?></span>
                                </div>
                            <?php endif; ?>
                            <div class="total-row grand-total">
                                <span>Total</span>
                                <span>₹<?php echo number_format($totalPrice - $discount_amount, 2); ?></span>
                            </div>
                        </div>
                        
                        <a href="cart.php" class="back-to-cart">
                            <i class="fas fa-arrow-left"></i> Back to Cart
                        </a>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </main>

    <script>
    document.addEventListener('DOMContentLoaded', function() {
        // Handle payment option selection
        const paymentOptions = document.querySelectorAll('.payment-option');
        
        paymentOptions.forEach(option => {
            const radio = option.querySelector('input[type="radio"]');
            
            option.addEventListener('click', function() {
                // Check the radio button
                radio.checked = true;
                
                // Update active class
                paymentOptions.forEach(opt => {
                    if (opt.querySelector('input[type="radio"]').checked) {
                        opt.classList.add('active');
                    } else {
                        opt.classList.remove('active');
                    }
                });
            });
            
            // Initial setup
            if (radio.checked) {
                option.classList.add('active');
            }
        });
    });
    </script>
</body>
</html>

<?php $conn->close(); ?>