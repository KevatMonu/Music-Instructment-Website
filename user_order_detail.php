<?php
session_start();

// Database connection
$conn = new mysqli("localhost", "root", "", "musicstore_database");

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: sign-in.php");
    exit();
}

// Check if order_id is provided
if (!isset($_GET['order_id']) || !is_numeric($_GET['order_id'])) {
    header("Location: user_order_detail.php"); // Redirect if order_id is missing or invalid
    exit();
}

$order_id = $_GET['order_id'];
$user_id = $_SESSION['user_id'];

// Fetch order details
$stmt = $conn->prepare("SELECT * FROM orders WHERE order_id = ? AND user_ref = ?");
$stmt->bind_param("ii", $order_id, $user_id);
$stmt->execute();
$result = $stmt->get_result();
$order = $result->fetch_assoc();

if (!$order) {
    header("Location: user_order_detail.php"); // Redirect if order not found or doesn't belong to user
    exit();
}

// Fetch order items
$stmt = $conn->prepare("SELECT oi.product_ref, oi.item_quantity, oi.item_price, p.product_name 
                       FROM order_items oi 
                       JOIN products p ON oi.product_ref = p.product_id 
                       WHERE oi.order_ref = ?");
$stmt->bind_param("i", $order_id);
$stmt->execute();
$orderItemsResult = $stmt->get_result();
$orderItems = $orderItemsResult->fetch_all(MYSQLI_ASSOC);

// Fetch offer details if applicable
$offerDetails = null;
if ($order['offer_ref']) {
    $stmt = $conn->prepare("SELECT * FROM offers WHERE offer_id = ?");
    $stmt->bind_param("i", $order['offer_ref']);
    $stmt->execute();
    $offerResult = $stmt->get_result();
    $offerDetails = $offerResult->fetch_assoc();
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order Details</title>
    <link rel="stylesheet" href="https://unpkg.com/lucide-static@latest/font/lucide.css">
    <style>
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, sans-serif;
            background-color: #f3f4f6;
            margin: 0;
            padding: 0;
            color: #333;
        }

        .container {
            width: 90%;
            max-width: 1000px;
            margin: 2rem auto;
        }

        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1.5rem;
        }

        .back-link {
            color: #4f46e5;
            text-decoration: none;
            display: flex;
            align-items: center;
            font-size: 0.9rem;
        }

        .back-link:hover {
            text-decoration: underline;
        }

        .back-link svg {
            margin-right: 0.25rem;
        }

        .order-details {
            background-color: white;
            padding: 2rem;
            border-radius: 8px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }

        .order-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1.5rem;
            padding-bottom: 1rem;
            border-bottom: 1px solid #e5e7eb;
        }

        .order-status {
            padding: 0.375rem 0.75rem;
            border-radius: 9999px;
            font-size: 0.75rem;
            font-weight: 500;
            text-transform: uppercase;
        }

        .status-completed {
            background-color: #dcfce7;
            color: #166534;
        }

        .status-cancelled {
            background-color: #fee2e2;
            color: #b91c1c;
        }
        
        .order-type {
            padding: 0.375rem 0.75rem;
            border-radius: 9999px;
            font-size: 0.75rem;
            font-weight: 500;
            margin-left: 0.5rem;
        }

        .type-buy {
            background-color: #e0f2fe;
            color: #0369a1;
        }

        .type-rent {
            background-color: #fef3c7;
            color: #92400e;
        }

        .order-info {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 1.5rem;
            margin-bottom: 2rem;
        }

        .info-group h3 {
            font-size: 0.875rem;
            text-transform: uppercase;
            color: #6b7280;
            margin-bottom: 0.5rem;
        }

        .info-group p {
            margin: 0.25rem 0;
        }

        .order-items {
            margin-top: 2rem;
        }

        .order-items h3 {
            margin-bottom: 1rem;
        }

        .order-items table {
            width: 100%;
            border-collapse: collapse;
        }

        .order-items th, .order-items td {
            padding: 0.75rem 1rem;
            text-align: left;
            border-bottom: 1px solid #e5e7eb;
        }

        .order-items th {
            font-size: 0.875rem;
            text-transform: uppercase;
            color: #6b7280;
            font-weight: 500;
        }

        .order-items tbody tr:last-child td {
            border-bottom: none;
        }

        .order-items tfoot {
            font-weight: 500;
        }

        .order-items tfoot td {
            padding-top: 1rem;
        }

        .subtotal-row td {
            padding-top: 1rem;
            border-top: 1px solid #e5e7eb;
        }

        .discount-row {
            color: #10b981;
        }

        .total-row {
            font-size: 1.125rem;
            font-weight: 600;
        }

        .offer-details {
            margin-top: 1.5rem;
            padding: 1rem;
            background-color: #f8fafc;
            border-radius: 0.5rem;
            border-left: 4px solid #4f46e5;
        }

        .offer-details h4 {
            margin-top: 0;
            color: #4f46e5;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <a href="user_order_detail.php" class="back-link">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide-chevron-left"><path d="m15 18-6-6 6-6"/></svg>
                Back to Orders
            </a>
        </div>

        <div class="order-details">
            <div class="order-header">
                <h2>Order #<?php echo $order['order_id']; ?></h2>
                <div>
                    <span class="order-status status-<?php echo strtolower($order['order_status']); ?>"><?php echo $order['order_status']; ?></span>
                    <span class="order-type type-<?php echo strtolower($order['order_type']); ?>"><?php echo $order['order_type']; ?></span>
                </div>
            </div>

            <div class="order-info">
                <div class="info-group">
                    <h3>Order Details</h3>
                    <p><strong>Order ID:</strong> <?php echo $order['order_id']; ?></p>
                    <p><strong>Date:</strong> <?php echo date('F d, Y - h:i A', strtotime($order['order_created'])); ?></p>
                    <p><strong>Order Type:</strong> <?php echo ucfirst($order['order_type']); ?></p>
                </div>
            </div>

            <div class="order-items">
                <h3>Order Items</h3>
                <table>
                    <thead>
                        <tr>
                            <th>Product</th>
                            <th>Price</th>
                            <th>Quantity</th>
                            <th>Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        $subtotal = 0;
                        foreach ($orderItems as $item): 
                            $itemTotal = $item['item_quantity'] * $item['item_price'];
                            $subtotal += $itemTotal;
                        ?>
                            <tr>
                                <td><?php echo htmlspecialchars($item['product_name']); ?></td>
                                <td>$<?php echo number_format($item['item_price'], 2); ?></td>
                                <td><?php echo $item['item_quantity']; ?></td>
                                <td>$<?php echo number_format($itemTotal, 2); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                    <tfoot>
                        <tr class="subtotal-row">
                            <td colspan="3" class="text-right">Subtotal</td>
                            <td>$<?php echo number_format($subtotal, 2); ?></td>
                        </tr>
                        <?php if ($order['discount_amount'] > 0): ?>
                        <tr class="discount-row">
                            <td colspan="3" class="text-right">Discount</td>
                            <td>-$<?php echo number_format($order['discount_amount'], 2); ?></td>
                        </tr>
                        <?php endif; ?>
                        <tr class="total-row">
                            <td colspan="3" class="text-right">Total</td>
                            <td>$<?php echo number_format($order['total_cost'], 2); ?></td>
                        </tr>
                    </tfoot>
                </table>

                <?php if ($offerDetails): ?>
                <div class="offer-details">
                    <h4>Applied Offer</h4>
                    <p><strong><?php echo htmlspecialchars($offerDetails['offer_name']); ?></strong></p>
                    <p><?php echo htmlspecialchars($offerDetails['offer_description']); ?></p>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</body>
</html>