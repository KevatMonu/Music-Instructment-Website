<?php
// Start session to manage user login state
session_start();

// Database connection
$servername = "localhost";
$username = "root"; // Change this to your MySQL username
$password = ""; // Change this to your MySQL password
$dbname = "musicstore_database";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Function to sanitize user input
function sanitize_input($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}

// Check if user is logged in
$logged_in = isset($_SESSION['user_id']);
$is_admin = $logged_in && isset($_SESSION['user_role']) && $_SESSION['user_role'] == 'admin';

// Handle order lookup by invoice number or order ID
$order_details = [];
$shipping_details = [];
$order_items = [];
$payment_info = [];
$error_message = "";
$success = false;

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['tracking_id'])) {
    $tracking_id = sanitize_input($_POST['tracking_id']);
    
    // Search by invoice number or order ID
    $sql = "SELECT o.*, u.full_name, u.email_address, u.phone_number 
            FROM orders o 
            JOIN users u ON o.user_ref = u.user_id 
            WHERE o.invoice_number = ? OR o.order_id = ?";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ss", $tracking_id, $tracking_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $order_details = $result->fetch_assoc();
        $success = true;
        
        // Get shipping details
        $shipping_sql = "SELECT * FROM order_shipping WHERE order_ref = ?";
        $stmt = $conn->prepare($shipping_sql);
        $stmt->bind_param("i", $order_details['order_id']);
        $stmt->execute();
        $shipping_result = $stmt->get_result();
        
        if ($shipping_result->num_rows > 0) {
            $shipping_details = $shipping_result->fetch_assoc();
        }
        
        // Get order items
        $items_sql = "SELECT oi.*, p.product_name 
                    FROM order_items oi 
                    JOIN products p ON oi.product_ref = p.product_id 
                    WHERE oi.order_ref = ?";
        $stmt = $conn->prepare($items_sql);
        $stmt->bind_param("i", $order_details['order_id']);
        $stmt->execute();
        $items_result = $stmt->get_result();
        
        if ($items_result->num_rows > 0) {
            while ($row = $items_result->fetch_assoc()) {
                $order_items[] = $row;
            }
        }
        
        // Get payment info
        $payment_sql = "SELECT * FROM payments WHERE order_ref = ? ORDER BY paid_on DESC LIMIT 1";
        $stmt = $conn->prepare($payment_sql);
        $stmt->bind_param("i", $order_details['order_id']);
        $stmt->execute();
        $payment_result = $stmt->get_result();
        
        if ($payment_result->num_rows > 0) {
            $payment_info = $payment_result->fetch_assoc();
        }
    } else {
        $error_message = "No order found with the provided tracking ID or invoice number";
    }
}

// Get user orders if logged in
$user_orders = [];
if ($logged_in && !isset($_POST['tracking_id'])) {
    $user_id = $_SESSION['user_id'];
    
    $sql = "SELECT o.*, 
            (SELECT MAX(paid_on) FROM payments WHERE order_ref = o.order_id) as payment_date,
            (SELECT shipping_id FROM order_shipping WHERE order_ref = o.order_id) as has_shipping
            FROM orders o 
            WHERE o.user_ref = ? 
            ORDER BY o.order_created DESC";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $user_orders[] = $row;
        }
    }
}

// Get all orders for admin
$all_orders = [];
if ($is_admin) {
    $sql = "SELECT o.*, u.full_name, u.email_address,
            (SELECT MAX(paid_on) FROM payments WHERE order_ref = o.order_id) as payment_date,
            (SELECT shipping_id FROM order_shipping WHERE order_ref = o.order_id) as has_shipping
            FROM orders o 
            JOIN users u ON o.user_ref = u.user_id
            ORDER BY o.order_created DESC";
    
    $result = $conn->query($sql);
    
    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $all_orders[] = $row;
        }
    }
}

// Determine order status progress for tracking
function getOrderProgress($status) {
    switch(strtolower($status)) {
        case 'pending':
            return 20;
        case 'processing':
            return 40;
        case 'shipped':
            return 60;
        case 'in transit':
            return 80;
        case 'delivered':
        case 'completed':
            return 100;
        case 'cancelled':
            return 0;
        default:
            return 0;
    }
}

// Get order status steps completed
function getCompletedSteps($status) {
    $statusOrder = ['pending', 'processing', 'shipped', 'in transit', 'delivered'];
    $statusIndex = array_search(strtolower($status), $statusOrder);
    
    if ($statusIndex === false) {
        return ($status == 'completed') ? 5 : 0;
    }
    
    return $statusIndex + 1;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Music Store - Order Tracking</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="css/ordertracking.css">
</head>
<body>
    <div class="container my-5">
        <h1 class="text-center mb-5">Order Tracking System</h1>
        
        <div class="tracking-section">
            <h3 class="mb-4">Track Your Order</h3>
            <form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">
                <div class="input-group mb-3">
                    <input type="text" class="form-control" name="tracking_id" placeholder="Enter Order ID or Invoice Number">
                    <button class="btn btn-primary" type="submit">Track Order</button>
                </div>
            </form>
            
            <?php if ($error_message): ?>
                <div class="alert alert-danger mt-3">
                    <?php echo $error_message; ?>
                </div>
            <?php endif; ?>
        </div>
        
        <?php if ($success): ?>
            <div class="order-details">
                <h3>Order #<?php echo $order_details['order_id']; ?></h3>
                <p><strong>Invoice Number:</strong> <?php echo $order_details['invoice_number']; ?></p>
                
                <!-- Graphical Order Tracking -->
                <?php 
                    $orderStatus = strtolower($order_details['order_status']);
                    $progressPercentage = getOrderProgress($orderStatus);
                    $completedSteps = getCompletedSteps($orderStatus);
                    $isCancelled = ($orderStatus == 'cancelled');
                ?>
                
                <?php if (!$isCancelled): ?>
                <div class="tracking-visual my-4">
                    <div class="progress-container">
                        <div class="progress">
                            <div class="progress-bar bg-primary" role="progressbar" style="width: <?php echo $progressPercentage; ?>%" aria-valuenow="<?php echo $progressPercentage; ?>" aria-valuemin="0" aria-valuemax="100"></div>
                        </div>
                        
                        <div class="tracking-steps">
                            <div class="tracking-step <?php echo ($completedSteps >= 1) ? 'completed' : ''; ?>">
                                <div class="step-icon">
                                    <i class="bi bi-cart-check"></i>
                                </div>
                                <div class="step-label">Ordered</div>
                            </div>
                            <div class="tracking-step <?php echo ($completedSteps >= 2) ? 'completed' : ''; ?>">
                                <div class="step-icon">
                                    <i class="bi bi-box-seam"></i>
                                </div>
                                <div class="step-label">Processing</div>
                            </div>
                            <div class="tracking-step <?php echo ($completedSteps >= 3) ? 'completed' : ''; ?>">
                                <div class="step-icon">
                                    <i class="bi bi-truck"></i>
                                </div>
                                <div class="step-label">Shipped</div>
                            </div>
                            <div class="tracking-step <?php echo ($completedSteps >= 4) ? 'completed' : ''; ?>">
                                <div class="step-icon">
                                    <i class="bi bi-geo-alt"></i>
                                </div>
                                <div class="step-label">In Transit</div>
                            </div>
                            <div class="tracking-step <?php echo ($completedSteps >= 5) ? 'completed' : ''; ?>">
                                <div class="step-icon">
                                    <i class="bi bi-house-check"></i>
                                </div>
                                <div class="step-label">Delivered</div>
                            </div>
                        </div>
                    </div>
                </div>
                <?php else: ?>
                <div class="cancelled-order-alert my-4">
                    <div class="alert alert-danger">
                        <i class="bi bi-x-circle me-2"></i> This order has been cancelled
                    </div>
                </div>
                <?php endif; ?>
                
                <div class="row mt-4">
                    <div class="col-md-6">
                        <div class="card order-info-card">
                            <div class="card-header">
                                <i class="bi bi-info-circle me-2"></i> Order Information
                            </div>
                            <div class="card-body">
                                <p><strong>Customer:</strong> <?php echo $order_details['full_name']; ?></p>
                                <p><strong>Email:</strong> <?php echo $order_details['email_address']; ?></p>
                                <p><strong>Phone:</strong> <?php echo $order_details['phone_number']; ?></p>
                                <p><strong>Order Date:</strong> <?php echo date('F j, Y g:i A', strtotime($order_details['order_created'])); ?></p>
                                <p><strong>Order Type:</strong> <?php echo ucfirst($order_details['order_type']); ?></p>
                                <p><strong>Status:</strong> <span class="badge status-badge <?php echo $order_details['order_status']; ?>"><?php echo ucfirst($order_details['order_status']); ?></span></p>
                                <p><strong>Total:</strong> $<?php echo number_format($order_details['total_cost'], 2); ?></p>
                                <?php if ($order_details['discount_amount'] > 0): ?>
                                    <p><strong>Discount Applied:</strong> $<?php echo number_format($order_details['discount_amount'], 2); ?></p>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-md-6">
                        <div class="card shipping-info-card">
                            <div class="card-header">
                                <i class="bi bi-truck me-2"></i> Shipping Information
                            </div>
                            <div class="card-body">
                                <?php if (!empty($shipping_details)): ?>
                                    <div class="shipping-box">
                                        <p><strong>Address:</strong> <?php echo $shipping_details['shipping_address']; ?></p>
                                        <p><strong>City:</strong> <?php echo $shipping_details['shipping_city']; ?></p>
                                        <p><strong>State:</strong> <?php echo $shipping_details['shipping_state']; ?></p>
                                        <p><strong>Pincode:</strong> <?php echo $shipping_details['shipping_pincode']; ?></p>
                                        <p><strong>Shipping Date:</strong> <?php echo date('F j, Y', strtotime($shipping_details['created_at'])); ?></p>
                                    </div>
                                <?php else: ?>
                                    <p>No shipping information available for this order.</p>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
                
                <?php if (!empty($payment_info)): ?>
                    <div class="row mt-4">
                        <div class="col-12">
                            <div class="card payment-info-card">
                                <div class="card-header">
                                    <i class="bi bi-credit-card me-2"></i> Payment Information
                                </div>
                                <div class="card-body">
                                    <p><strong>Payment Mode:</strong> <?php echo str_replace('_', ' ', ucfirst($payment_info['payment_mode'])); ?></p>
                                    <p><strong>Payment Date:</strong> <?php echo date('F j, Y g:i A', strtotime($payment_info['paid_on'])); ?></p>
                                    <p><strong>Amount:</strong> $<?php echo number_format($payment_info['payment_amount'], 2); ?></p>
                                    <p><strong>Status:</strong> 
                                        <span class="payment-status <?php echo $payment_info['payment_status']; ?>">
                                            <i class="bi <?php echo ($payment_info['payment_status'] == 'success') ? 'bi-check-circle' : 'bi-x-circle'; ?> me-1"></i>
                                            <?php echo ucfirst($payment_info['payment_status']); ?>
                                        </span>
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>
                
                <?php if (!empty($order_items)): ?>
                    <div class="row mt-4">
                        <div class="col-12">
                            <div class="card items-card">
                                <div class="card-header">
                                    <i class="bi bi-box2 me-2"></i> Order Items
                                </div>
                                <div class="card-body">
                                    <div class="table-responsive">
                                        <table class="table">
                                            <thead class="table-light">
                                                <tr>
                                                    <th>Product</th>
                                                    <th>Price</th>
                                                    <th>Quantity</th>
                                                    <th>Total</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php foreach($order_items as $item): ?>
                                                    <tr>
                                                        <td><?php echo isset($item['product_name']) ? $item['product_name'] : 'Product #' . $item['product_ref']; ?></td>
                                                        <td>$<?php echo number_format($item['item_price'], 2); ?></td>
                                                        <td><?php echo $item['item_quantity']; ?></td>
                                                        <td>$<?php echo number_format($item['item_price'] * $item['item_quantity'], 2); ?></td>
                                                    </tr>
                                                <?php endforeach; ?>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>
                
                <div class="text-center mt-4">
                    <a href="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" class="btn btn-outline-primary">
                        <i class="bi bi-arrow-left me-2"></i> Track Another Order
                    </a>
                </div>
            </div>
        <?php endif; ?>
        
        <?php if ($logged_in && !empty($user_orders) && !$success): ?>
            <div class="mt-5">
                <h3><i class="bi bi-clock-history me-2"></i>Your Orders</h3>
                <div class="table-responsive">
                    <table class="table table-striped orders-table">
                        <thead>
                            <tr>
                                <th>Order ID</th>
                                <th>Invoice</th>
                                <th>Date</th>
                                <th>Type</th>
                                <th>Amount</th>
                                <th>Status</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach($user_orders as $order): ?>
                                <tr>
                                    <td>#<?php echo $order['order_id']; ?></td>
                                    <td><?php echo $order['invoice_number']; ?></td>
                                    <td><?php echo date('M j, Y', strtotime($order['order_created'])); ?></td>
                                    <td><?php echo ucfirst($order['order_type']); ?></td>
                                    <td>$<?php echo number_format($order['total_cost'], 2); ?></td>
                                    <td>
                                        <span class="badge status-badge <?php echo $order['order_status']; ?>">
                                            <?php echo ucfirst($order['order_status']); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">
                                            <input type="hidden" name="tracking_id" value="<?php echo $order['order_id']; ?>">
                                            <button type="submit" class="btn btn-sm btn-info">
                                                <i class="bi bi-eye me-1"></i> View
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        <?php endif; ?>
        
        <?php if ($is_admin && !empty($all_orders) && !$success): ?>
            <div class="mt-5">
                <h3><i class="bi bi-clipboard-data me-2"></i>All Orders (Admin View)</h3>
                <div class="table-responsive">
                    <table class="table table-striped orders-table">
                        <thead>
                            <tr>
                                <th>Order ID</th>
                                <th>Invoice</th>
                                <th>Customer</th>
                                <th>Date</th>
                                <th>Type</th>
                                <th>Amount</th>
                                <th>Status</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach($all_orders as $order): ?>
                                <tr>
                                    <td>#<?php echo $order['order_id']; ?></td>
                                    <td><?php echo $order['invoice_number']; ?></td>
                                    <td><?php echo $order['full_name']; ?></td>
                                    <td><?php echo date('M j, Y', strtotime($order['order_created'])); ?></td>
                                    <td><?php echo ucfirst($order['order_type']); ?></td>
                                    <td>$<?php echo number_format($order['total_cost'], 2); ?></td>
                                    <td>
                                        <span class="badge status-badge <?php echo $order['order_status']; ?>">
                                            <?php echo ucfirst($order['order_status']); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">
                                            <input type="hidden" name="tracking_id" value="<?php echo $order['order_id']; ?>">
                                            <button type="submit" class="btn btn-sm btn-info">
                                                <i class="bi bi-eye me-1"></i> View
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        <?php endif; ?>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

<?php
// Close connection
$conn->close();
?>