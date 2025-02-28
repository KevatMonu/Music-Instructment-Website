<?php
session_start();
include 'db_connection.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php?redirect=ordertracking.php');
    exit();
}

$user_id = $_SESSION['user_id'];
$message = '';
$orderDetails = null;

// Handle search by order ID
if (isset($_POST['search_order']) && !empty($_POST['order_id'])) {
    $order_id = trim($_POST['order_id']);
    
    // Check if the order belongs to the logged-in user
    $stmt = $conn->prepare("SELECT * FROM orders WHERE order_id = ? AND user_ref = ?");
    $stmt->bind_param("ii", $order_id, $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $orderDetails = $result->fetch_assoc();
    } else {
        $message = "Order not found or you don't have permission to view this order.";
    }
}

// Get recent orders for the user
$stmt = $conn->prepare("
    SELECT o.*, 
           COUNT(oi.order_item_id) as item_count,
           p.payment_status, 
           p.payment_mode
    FROM orders o
    LEFT JOIN order_items oi ON o.order_id = oi.order_ref
    LEFT JOIN payments p ON o.order_id = p.order_ref
    WHERE o.user_ref = ?
    GROUP BY o.order_id
    ORDER BY o.order_created DESC
    LIMIT 10
");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$recentOrders = $stmt->get_result();

// Get order items and details if an order is selected
if (isset($_GET['order_id'])) {
    $order_id = $_GET['order_id'];
    
    // Get order info
    $stmt = $conn->prepare("
        SELECT o.*, p.payment_status, p.payment_mode, p.paid_on
        FROM orders o
        LEFT JOIN payments p ON o.order_id = p.order_ref
        WHERE o.order_id = ? AND o.user_ref = ?
    ");
    $stmt->bind_param("ii", $order_id, $user_id);
    $stmt->execute();
    $orderResult = $stmt->get_result();
    
    if ($orderResult->num_rows > 0) {
        $orderDetails = $orderResult->fetch_assoc();
        
        // Get order items
        $stmt = $conn->prepare("
            SELECT oi.*, p.product_name, p.product_image, p.image_type
            FROM order_items oi
            JOIN products p ON oi.product_ref = p.product_id
            WHERE oi.order_ref = ?
        ");
        $stmt->bind_param("i", $order_id);
        $stmt->execute();
        $orderItems = $stmt->get_result();
    } else {
        $message = "Order not found or you don't have permission to view this order.";
    }
}

// Get user info
$stmt = $conn->prepare("SELECT * FROM users WHERE user_id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$userInfo = $stmt->get_result()->fetch_assoc();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order Tracking - TechShop</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="css/ordertracking.css">
   
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
            <a href="#index.hmtl"><li>Home</li> </a>
            <a href="products.php"><li>Product</li> </a>
            <a href="about.html"><li>About Us</li> </a>
            <a href="contact.hmtl"><li>Contact Us</li></a>
            <a href="sign-in.php"><li>Login</li> </a>
            <a href="rent.php"><li>Rent</li></a>  
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
          
          <a href="user_dashboard.php"><i class="fa-solid fa-user"></i></a>
        </div>
      </div>
    </div>

    <div class="main-content">
        <div class="container">
            <h1 class="page-title">Order Tracking</h1>
            
            <?php if (!empty($message)): ?>
                <div class="alert alert-danger"><?php echo $message; ?></div>
            <?php endif; ?>
            
            <div class="tracking-container">
                <div class="tracking-sidebar">
                    <form class="tracking-form" method="post">
                        <h3 class="recent-orders-title">Track Order</h3>
                        <input type="text" name="order_id" class="form-control" placeholder="Enter Order ID" required>
                        <button type="submit" name="search_order" class="btn-search">Track</button>
                    </form>
                    
                    <h3 class="recent-orders-title">Recent Orders</h3>
                    
                    <?php if ($recentOrders->num_rows > 0): ?>
                        <?php while($order = $recentOrders->fetch_assoc()): ?>
                            <div class="order-item <?php echo (isset($_GET['order_id']) && $_GET['order_id'] == $order['order_id']) ? 'active' : ''; ?>" onclick="window.location.href='ordertracking.php?order_id=<?php echo $order['order_id']; ?>'">
                                <div class="order-item-header">
                                    <div class="order-id">#<?php echo $order['order_id']; ?></div>
                                    <div class="order-date"><?php echo date('M d, Y', strtotime($order['order_created'])); ?></div>
                                </div>
                                <div class="order-info">
                                    <div>
                                        <span class="order-type <?php echo $order['order_type']; ?>"><?php echo ucfirst($order['order_type']); ?></span>
                                        <span class="order-status <?php echo $order['order_status']; ?>"><?php echo ucfirst($order['order_status']); ?></span>
                                    </div>
                                    <div class="order-amount">₹<?php echo number_format($order['total_cost'], 2); ?></div>
                                </div>
                            </div>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <div class="empty-state">
                            <i class="fas fa-shopping-bag"></i>
                            <p>You haven't placed any orders yet.</p>
                        </div>
                    <?php endif; ?>
                </div>
                
                <div class="tracking-content">
                    <?php if ($orderDetails): ?>
                        <div class="order-detail-header">
                            <h2 class="order-detail-title">Order #<?php echo $orderDetails['order_id']; ?></h2>
                            <div>
                                <span class="order-type <?php echo $orderDetails['order_type']; ?>"><?php echo ucfirst($orderDetails['order_type']); ?></span>
                                <span class="order-status <?php echo $orderDetails['order_status']; ?>"><?php echo ucfirst($orderDetails['order_status']); ?></span>
                            </div>
                        </div>
                        
                        <div class="tracking-progress">
                            <?php
                            $statusClass = '';
                            if ($orderDetails['order_status'] == 'completed') {
                                $statusClass = 'completed';
                            } elseif ($orderDetails['order_status'] == 'cancelled') {
                                $statusClass = 'cancelled';
                            }
                            ?>
                            <div class="tracking-steps <?php echo $statusClass; ?>">
                                <div class="tracking-step completed">
                                    <div class="step-icon">
                                        <i class="fas fa-shopping-cart"></i>
                                    </div>
                                    <div class="step-text">Order Placed</div>
                                </div>
                                
                                <div class="tracking-step <?php echo (!empty($orderDetails['payment_status']) && $orderDetails['payment_status'] == 'success') ? 'completed' : ''; ?>">
                                    <div class="step-icon">
                                        <i class="fas fa-credit-card"></i>
                                    </div>
                                    <div class="step-text">Payment</div>
                                </div>
                                
                                <div class="tracking-step <?php echo ($orderDetails['order_status'] == 'completed') ? 'completed' : (($orderDetails['order_status'] == 'cancelled') ? 'cancelled' : ''); ?>">
                                    <div class="step-icon <?php echo ($orderDetails['order_status'] == 'cancelled') ? 'cancelled' : ''; ?>">
                                        <?php if ($orderDetails['order_status'] == 'cancelled'): ?>
                                            <i class="fas fa-times"></i>
                                        <?php else: ?>
                                            <i class="fas fa-box"></i>
                                        <?php endif; ?>
                                    </div>
                                    <div class="step-text">
                                        <?php echo ($orderDetails['order_status'] == 'cancelled') ? 'Cancelled' : 'Processing'; ?>
                                    </div>
                                </div>
                                
                                <div class="tracking-step <?php echo ($orderDetails['order_status'] == 'completed') ? 'completed' : ''; ?>">
                                    <div class="step-icon">
                                        <i class="fas fa-check"></i>
                                    </div>
                                    <div class="step-text">Completed</div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="order-info-grid">
                            <div class="order-info-box">
                                <div class="info-title">Order Information</div>
                                <div class="info-content">
                                    <p><strong>Order ID:</strong> #<?php echo $orderDetails['order_id']; ?></p>
                                    <p><strong>Order Date:</strong> <?php echo date('F d, Y H:i', strtotime($orderDetails['order_created'])); ?></p>
                                    <p><strong>Order Type:</strong> <?php echo ucfirst($orderDetails['order_type']); ?></p>
                                    <p><strong>Order Status:</strong> <?php echo ucfirst($orderDetails['order_status']); ?></p>
                                    <?php if (!empty($orderDetails['offer_ref'])): ?>
                                    <p><strong>Applied Offer:</strong> Yes (ID: <?php echo $orderDetails['offer_ref']; ?>)</p>
                                    <p><strong>Discount Amount:</strong> ₹<?php echo number_format($orderDetails['discount_amount'], 2); ?></p>
                                    <?php endif; ?>
                                </div>
                            </div>
                            
                            <div class="order-info-box">
                                <div class="info-title">Payment Information</div>
                                <div class="info-content">
                                    <p><strong>Payment Method:</strong> 
                                        <?php echo !empty($orderDetails['payment_mode']) ? ucfirst(str_replace('_', ' ', $orderDetails['payment_mode'])) : 'Not available'; ?>
                                    </p>
                                    <p><strong>Payment Status:</strong> 
                                        <?php echo !empty($orderDetails['payment_status']) ? ucfirst($orderDetails['payment_status']) : 'Pending'; ?>
                                    </p>
                                    <p><strong>Payment Date:</strong> 
                                        <?php echo !empty($orderDetails['paid_on']) ? date('F d, Y H:i', strtotime($orderDetails['paid_on'])) : 'Not available'; ?>
                                    </p>
                                    <p><strong>Total Amount:</strong> ₹<?php echo number_format($orderDetails['total_cost'], 2); ?></p>
                                </div>
                            </div>
                            
                            <div class="order-info-box">
                                <div class="info-title">Customer Information</div>
                                <div class="info-content">
                                    <p><strong>Name:</strong> <?php echo htmlspecialchars($userInfo['full_name']); ?></p>
                                    <p><strong>Email:</strong> <?php echo htmlspecialchars($userInfo['email_address']); ?></p>
                                    <p><strong>Phone:</strong> <?php echo htmlspecialchars($userInfo['phone_number']); ?></p>
                                    <p><strong>Address:</strong> <?php echo htmlspecialchars($userInfo['user_address']); ?></p>
                                </div>
                            </div>
                        </div>
                        
                        <?php if (isset($orderItems) && $orderItems->num_rows > 0): ?>
                            <div class="order-items-container">
                                <h3 class="order-items-title">Order Items</h3>
                                
                                <?php while($item = $orderItems->fetch_assoc()): ?>
                                    <div class="order-item-product">
                                        <div>
                                            <?php
                                            if (!empty($item['product_image'])) {
                                                echo '<img src="data:' . $item['image_type'] . ';base64,' . base64_encode($item['product_image']) . '" alt="' . htmlspecialchars($item['product_name']) . '" class="product-image">';
                                            } else {
                                                echo '<img src="assets/no-image.png" alt="No Image" class="product-image">';
                                            }
                                            ?>
                                        </div>
                                        <div class="product-name"><?php echo htmlspecialchars($item['product_name']); ?></div>
                                        <div class="product-price">₹<?php echo number_format($item['item_price'], 2); ?> × <?php echo $item['item_quantity']; ?></div>
                                        <div class="product-subtotal">₹<?php echo number_format($item['item_price'] * $item['item_quantity'], 2); ?></div>
                                    </div>
                                <?php endwhile; ?>
                                
                                <div class="order-summary">
                                    <div class="order-total">Total: ₹<?php echo number_format($orderDetails['total_cost'], 2); ?></div>
                                </div>
                            </div>
                        <?php endif; ?>
                        
                    <?php else: ?>
                        <div class="empty-state">
                            <i class="fas fa-search"></i>
                            <h3>No Order Selected</h3>
                            <p>Select an order from the list or enter an order ID to track.</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

   

</body>
</html>

<?php $conn->close(); ?>