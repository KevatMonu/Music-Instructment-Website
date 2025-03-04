<?php
session_start();
include 'db_connection.php';

// Initialize cart if it doesn't exist
if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

// Handle cart actions
if (isset($_GET['action'])) {
    $action = $_GET['action'];
    $productId = isset($_GET['id']) ? $_GET['id'] : 0;
    
    switch ($action) {
        case 'remove':
            if (isset($_SESSION['cart'][$productId])) {
                unset($_SESSION['cart'][$productId]);
            }
            break;
            
        case 'increase':
            if (isset($_SESSION['cart'][$productId])) {
                $_SESSION['cart'][$productId]++;
            }
            break;
            
        case 'decrease':
            if (isset($_SESSION['cart'][$productId])) {
                $_SESSION['cart'][$productId]--;
                if ($_SESSION['cart'][$productId] <= 0) {
                    unset($_SESSION['cart'][$productId]);
                }
            }
            break;
            
        case 'clear':
            $_SESSION['cart'] = [];
            break;
    }
    
    // Redirect back to cart page to prevent form resubmission
    header('Location: cart.php');
    exit;
}

// Get cart items from database
$cartItems = [];
$totalPrice = 0;

if (!empty($_SESSION['cart'])) {
    $productIds = array_keys($_SESSION['cart']);
    $placeholders = implode(',', array_fill(0, count($productIds), '?'));
    
    $query = "SELECT product_id, product_name, product_price, product_image, image_type FROM products WHERE product_id IN ($placeholders)";
    $stmt = $conn->prepare($query);
    
    // Bind parameters dynamically
    $types = str_repeat('i', count($productIds));
    $stmt->bind_param($types, ...$productIds);
    
    $stmt->execute();
    $result = $stmt->get_result();
    
    while ($row = $result->fetch_assoc()) {
        $quantity = $_SESSION['cart'][$row['product_id']];
        $subtotal = $row['product_price'] * $quantity;
        $totalPrice += $subtotal;
        
        $cartItems[] = [
            'id' => $row['product_id'],
            'name' => $row['product_name'],
            'price' => $row['product_price'],
            'quantity' => $quantity,
            'subtotal' => $subtotal,
            'image' => $row['product_image'],
            'image_type' => $row['image_type']
        ];
    }
}

// Count total items in cart
$totalItems = array_sum($_SESSION['cart']);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Shopping Cart - TechShop</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="./css/style.css">
    <link rel="stylesheet" href="./css/cart.css">
    <style>
        /* Cart Specific Styles */
        .cart-container {
            background-color: white;
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.05);
            overflow: hidden;
            margin-bottom: 30px;
            width: 100%;
        }
        
        .cart-header {
            display: grid;
            grid-template-columns: 100px 3fr 1fr 1fr 1fr 50px;
            padding: 15px 20px;
            background-color: #f9fafb;
            border-bottom: 1px solid #e5e7eb;
            font-weight: 600;
            color: #4b5563;
        }
        
        .cart-item {
            display: grid;
            grid-template-columns: 100px 3fr 1fr 1fr 1fr 50px;
            padding: 15px 20px;
            border-bottom: 1px solid #e5e7eb;
            align-items: center;
        }
        
        .cart-item:last-child {
            border-bottom: none;
        }
        
        .cart-item-image {
            width: 80px;
            height: 80px;
            object-fit: cover;
            border-radius: 5px;
        }
        
        .cart-item-name {
            font-weight: 500;
            color: #1f2937;
        }
        
        .cart-item-price {
            color: #4b5563;
        }
        
        .cart-quantity-control {
            display: flex;
            align-items: center;
        }
        
        .cart-quantity-btn {
            width: 30px;
            height: 30px;
            display: flex;
            align-items: center;
            justify-content: center;
            background-color: #f3f4f6;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            color: #4b5563;
            transition: all 0.2s;
        }
        
        .cart-quantity-btn:hover {
            background-color: #e5e7eb;
        }
        
        .cart-quantity-input {
            width: 40px;
            height: 30px;
            text-align: center;
            margin: 0 5px;
            border: 1px solid #e5e7eb;
            border-radius: 5px;
        }
        
        .cart-item-subtotal {
            font-weight: 600;
            color: #1f2937;
        }
        
        .cart-item-remove {
            color: #ef4444;
            cursor: pointer;
            transition: all 0.2s;
        }
        
        .cart-item-remove:hover {
            color: #b91c1c;
        }
        
        .cart-footer {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 20px;
            background-color: #f9fafb;
            border-top: 1px solid #e5e7eb;
        }
        
        .cart-total {
            font-size: 18px;
            font-weight: 600;
        }
        
        .cart-actions {
            display: flex;
            gap: 15px;
        }
        
        .btn {
            display: inline-block;
            padding: 10px 20px;
            border-radius: 5px;
            font-weight: 500;
            cursor: pointer;
            text-decoration: none;
            transition: all 0.2s;
        }
        
        .btn-primary {
            background-color: #4f46e5;
            color: white;
            border: none;
        }
        
        .btn-primary:hover {
            background-color: #4338ca;
        }
        
        .btn-secondary {
            background-color: #f3f4f6;
            color: #4b5563;
            border: 1px solid #e5e7eb;
        }
        
        .btn-secondary:hover {
            background-color: #e5e7eb;
        }
        
        .continue-shopping {
            display: flex;
            align-items: center;
            color: #4f46e5;
            font-weight: 500;
            transition: all 0.2s;
            text-decoration: none;
        }
        
        .continue-shopping i {
            margin-right: 5px;
        }
        
        .continue-shopping:hover {
            color: #4338ca;
        }
        
        .empty-cart {
            text-align: center;
            padding: 50px 20px;
        }
        
        .empty-cart i {
            font-size: 60px;
            color: #d1d5db;
            margin-bottom: 20px;
        }
        
        .empty-cart h2 {
            font-size: 24px;
            color: #4b5563;
            margin-bottom: 10px;
        }
        
        .empty-cart p {
            color: #6b7280;
            margin-bottom: 20px;
        }
        
        @media (max-width: 768px) {
            .cart-header {
                display: none;
            }
            
            .cart-item {
                grid-template-columns: 80px 1fr;
                grid-template-rows: auto auto auto;
                gap: 10px;
                padding: 15px;
            }
            
            .cart-item-image {
                grid-row: span 3;
            }
            
            .cart-item-name {
                grid-column: 2;
                grid-row: 1;
            }
            
            .cart-item-price {
                grid-column: 2;
                grid-row: 2;
            }
            
            .cart-quantity-control {
                grid-column: 2;
                grid-row: 3;
                justify-content: flex-start;
            }
            
            .cart-item-subtotal {
                display: none;
            }
            
            .cart-item-remove {
                position: absolute;
                top: 15px;
                right: 15px;
            }
            
            .cart-footer {
                flex-direction: column;
                gap: 15px;
            }
            
            .cart-actions {
                width: 100%;
                flex-direction: column;
            }
            
            .btn {
                width: 100%;
                text-align: center;
            }
        }
    </style>
</head>
<body>
    <!-- Header -->
    <div id="nav">
      <div class="nav1">
        <div class="logo">
          <img src="assets/home/image/logo.png" alt="" />
        </div>
        <div class="nav-item">
          <ul id="nav-item">
            <a href="index.php"><li>Home</li> </a>
            <a href="products.php"><li>Product</li> </a>
            <a href="about.php"><li>About Us</li> </a>
            <a href="contact.php"><li>Contact Us</li></a>
            <a href="sign-in.php"><li>Login</li> </a>
            <a href="rent.php"><li>Rent</li></a>  
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
          <a href="user_dashboard.php"><i class="fa-solid fa-user"></i></a>
        </div>
      </div>
    </div>

    <!-- Main Content -->
    <div class="main-content">
        <div class="container">
            <h1 class="page-title">Shopping Cart</h1>
            
            <?php if (!empty($cartItems)): ?>
                <div class="cart-container">
                    <div class="cart-header">
                        <div>Image</div>
                        <div>Product</div>
                        <div>Price</div>
                        <div>Quantity</div>
                        <div>Subtotal</div>
                        <div></div>
                    </div>
                    
                    <?php foreach ($cartItems as $item): ?>
                        <div class="cart-item">
                            <div>
                                <?php
                                if (!empty($item['image'])) {
                                    echo '<img src="data:' . $item['image_type'] . ';base64,' . base64_encode($item['image']) . '" alt="' . htmlspecialchars($item['name']) . '" class="cart-item-image">';
                                } else {
                                    echo '<img src="assets/no-image.png" alt="No Image" class="cart-item-image">';
                                }
                                ?>
                            </div>
                            <div class="cart-item-name"><?php echo htmlspecialchars($item['name']); ?></div>
                            <div class="cart-item-price">₹<?php echo number_format($item['price']); ?></div>
                            <div class="cart-quantity-control">
                                <a href="cart.php?action=decrease&id=<?php echo $item['id']; ?>" class="cart-quantity-btn">
                                    <i class="fas fa-minus"></i>
                                </a>
                                <span class="cart-quantity-input"><?php echo $item['quantity']; ?></span>
                                <a href="cart.php?action=increase&id=<?php echo $item['id']; ?>" class="cart-quantity-btn">
                                    <i class="fas fa-plus"></i>
                                </a>
                            </div>
                            <div class="cart-item-subtotal">₹<?php echo number_format($item['subtotal']); ?></div>
                            <div>
                                <a href="cart.php?action=remove&id=<?php echo $item['id']; ?>" class="cart-item-remove">
                                    <i class="fas fa-trash"></i>
                                </a>
                            </div>
                        </div>
                    <?php endforeach; ?>
                    
                    <div class="cart-footer">
                        <div class="cart-total">
                            Total: ₹<?php echo number_format($totalPrice); ?>
                        </div>
                        <div class="cart-actions">
                            <a href="cart.php?action=clear" class="btn btn-secondary">
                                <i class="fas fa-trash"></i> Clear Cart
                            </a>
                            <a href="checkout.php" class="btn btn-primary">
                                <i class="fas fa-lock"></i> Checkout
                            </a>
                        </div>
                    </div>
                </div>
                
                <div class="continue-shopping">
                    <a href="products.php" >
                        <i class="fas fa-arrow-left"></i> Continue Shopping
                    </a>
                </div>
            <?php else: ?>
                <div class="empty-cart">
                    <i class="fas fa-shopping-cart"></i>
                    <h2>Your cart is empty</h2>
                    <p>Looks like you haven't added any products to your cart yet.</p>
                    <a href="products.php" class="btn btn-primary">
                        <i class="fas fa-shopping-bag"></i> Start Shopping
                    </a>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <?php include 'pages/footer.php'; ?>
   
</body>
</html>

<?php $conn->close(); ?>