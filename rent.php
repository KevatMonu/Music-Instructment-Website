<?php
session_start();
$conn = new mysqli("localhost", "root", "", "musicstore_database");

// Check Connection
if ($conn->connect_error) {
    die("Database connection failed: " . $conn->connect_error);
}

// Initialize cart if not exists
if (!isset($_SESSION['cart'])) {    
    $_SESSION['cart'] = [];
}

// Check if user is logged in (you might need to implement proper authentication)
$user_id = $_SESSION['user_id'] ?? null;
if (!$user_id) {
    // Redirect to login page if not logged in
    header("Location: sign-in.php");
    exit();
}

// Return rental functionality
if (isset($_GET['action']) && $_GET['action'] == 'return' && isset($_GET['rental_id'])) {
    $rental_id = intval($_GET['rental_id']);
    
    // Update rental status to 'returned'
    $stmt = $conn->prepare("UPDATE rentals SET rental_status = 'returned', rental_end = NOW() WHERE rental_id = ? AND rental_status = 'active'");
    $stmt->bind_param("i", $rental_id);
    
    if ($stmt->execute()) {
        // Update product stock
        $stmt2 = $conn->prepare("UPDATE products p JOIN order_items oi ON p.product_id = oi.product_ref JOIN orders o ON oi.order_item_id = o.order_id JOIN rentals r ON o.order_id = r.order_ref SET p.stock = p.stock + oi.item_quantity WHERE r.rental_id = ?");
        $stmt2->bind_param("i", $rental_id);
        $stmt2->execute();
        
        $success_message = "Instrument successfully returned!";
    } else {
        $error_message = "Error returning instrument: " . $conn->error;
    }
}

// Extend rental functionality
if (isset($_POST['extend_rental']) && isset($_POST['rental_id']) && isset($_POST['additional_days'])) {
    $rental_id = intval($_POST['rental_id']);
    $additional_days = intval($_POST['additional_days']);
    
    if ($additional_days > 0) {
        // Update rental end date
        $stmt = $conn->prepare("UPDATE rentals SET rental_end = DATE_ADD(rental_end, INTERVAL ? DAY) WHERE rental_id = ? AND rental_status = 'active'");
        $stmt->bind_param("ii", $additional_days, $rental_id);
        
        if ($stmt->execute()) {
            $success_message = "Rental extended successfully!";
        } else {
            $error_message = "Error extending rental: " . $conn->error;
        }
    } else {
        $error_message = "Please enter a valid number of days.";
    }
}

// Count total rented items in cart
$totalItems = count($_SESSION['cart']);

// Get user's active rentals
$active_rentals_query = "
    SELECT r.rental_id, r.rental_start, r.rental_end, r.rental_status, 
           o.order_id, o.order_created, o.total_cost,
           p.product_name, p.product_image, p.rental_cost, oi.item_quantity
    FROM rentals r
    JOIN orders o ON r.order_ref = o.order_id
    JOIN order_items oi ON o.order_id = oi.order_item_id
    JOIN products p ON oi.product_ref = p.product_id
    WHERE o.user_ref = ? AND r.rental_status = 'active'
    ORDER BY r.rental_start DESC
";

$active_stmt = $conn->prepare($active_rentals_query);
$active_stmt->bind_param("i", $user_id);
$active_stmt->execute();
$active_result = $active_stmt->get_result();

// Get user's rental history
$history_rentals_query = "
    SELECT r.rental_id, r.rental_start, r.rental_end, r.rental_status, 
           o.order_id, o.order_created, o.total_cost,
           p.product_name, p.product_image, p.rental_cost, oi.item_quantity
    FROM rentals r
    JOIN orders o ON r.order_ref = o.order_id
    JOIN order_items oi ON o.order_id = oi.order_item_id
    JOIN products p ON oi.product_ref = p.product_id
    WHERE o.user_ref = ? AND r.rental_status = 'returned'
    ORDER BY r.rental_end DESC
";

$history_stmt = $conn->prepare($history_rentals_query);
$history_stmt->bind_param("i", $user_id);
$history_stmt->execute();
$history_result = $history_stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Rentals</title>
    <link rel="stylesheet" href="./css/style.css">
    <link rel="stylesheet" href="./css/products.css">
    <link rel="stylesheet" href="./css/rentals.css">
    <link href="https://cdn.jsdelivr.net/npm/remixicon@4.5.0/fonts/remixicon.css" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css" />
</head>
<body>

    <!-- Navigation Bar -->
    <div id="nav">
        <div class="nav1">
            <div class="logo">
                <img src="assets/home/image/logo.png" alt="Logo" />
            </div>
            <div class="nav-item">
                <ul id="nav-item">
                    <a href="index.php"><li>Home</li></a>
                    <a href="products.php"><li>Buy</li></a>
                    <a href="rent.php"><li>Rent</li></a>
                    <a href="rentals.php"><li class="active">My Rentals</li></a>
                    <a href="about.html"><li>About Us</li></a>
                    <a href="contact.html"><li>Contact Us</li></a>
                </ul>
            </div>
        </div>
        <div class="nav2">
            <div class="nav2-icon">
                <a href="cart.php" class="cart-link">
                    <i class="fa-solid fa-cart-shopping"></i>(<span id="cart-count"><?php echo $totalItems; ?></span>)
                </a>
            </div>
        </div>
    </div>

    <!-- Rentals Section -->
    <div class="rentals-container">
        <h1>My Rentals</h1>
        
        <?php if (isset($success_message)): ?>
            <div class="message success">
                <?php echo $success_message; ?>
            </div>
        <?php endif; ?>
        
        <?php if (isset($error_message)): ?>
            <div class="message error">
                <?php echo $error_message; ?>
            </div>
        <?php endif; ?>
        
        <div class="tabs">
            <div class="tab active" data-tab="active-rentals">Active Rentals</div>
            <div class="tab" data-tab="rental-history">Rental History</div>
        </div>
        
        <!-- Active Rentals Tab -->
        <div class="tab-content active" id="active-rentals">
            <div class="rental-section">
                <h2>Active Rentals</h2>
                
                <?php if ($active_result->num_rows > 0): ?>
                    <?php while ($rental = $active_result->fetch_assoc()): 
                        // Calculate days remaining
                        $end_date = new DateTime($rental['rental_end']);
                        $today = new DateTime();
                        $days_remaining = $today->diff($end_date)->days;
                        $is_overdue = $today > $end_date;
                    ?>
                    <div class="rental-card">
                        <img src="<?php echo htmlspecialchars($rental['product_image']); ?>" alt="<?php echo htmlspecialchars($rental['product_name']); ?>" class="rental-image">
                        <div class="rental-details">
                            <h3><?php echo htmlspecialchars($rental['product_name']); ?> (x<?php echo $rental['item_quantity']; ?>)</h3>
                            <span class="rental-status status-active">Active</span>
                            <p class="rental-date">
                                Rented on: <?php echo date('F j, Y', strtotime($rental['rental_start'])); ?><br>
                                Due on: <?php echo date('F j, Y', strtotime($rental['rental_end'])); ?>
                                <?php if ($is_overdue): ?>
                                    <span class="days-remaining">(OVERDUE)</span>
                                <?php else: ?>
                                    <span class="days-remaining">(<?php echo $days_remaining; ?> days remaining)</span>
                                <?php endif; ?>
                            </p>
                            <p class="rental-price">
                                ₹<?php echo number_format($rental['rental_cost'], 2); ?>/day
                            </p>
                            
                            <div class="rental-actions">
                                <button class="btn btn-extend show-extend-form" data-rental-id="<?php echo $rental['rental_id']; ?>">Extend Rental</button>
                                <a href="rentals.php?action=return&rental_id=<?php echo $rental['rental_id']; ?>" class="btn btn-return" onclick="return confirm('Are you sure you want to return this item?');">Return Item</a>
                            </div>
                            
                            <div class="extend-form" id="extend-form-<?php echo $rental['rental_id']; ?>">
                                <form method="post" action="rentals.php">
                                    <input type="hidden" name="rental_id" value="<?php echo $rental['rental_id']; ?>">
                                    <input type="number" name="additional_days" min="1" max="30" placeholder="Days" required>
                                    <button type="submit" name="extend_rental" class="btn">Confirm Extension</button>
                                </form>
                            </div>
                        </div>
                    </div>
                    <?php endwhile; ?>
                <?php else: ?>
                    <p>You don't have any active rentals. <a href="rent.php">Browse instruments to rent</a>.</p>
                <?php endif; ?>
            </div>
        </div>
        
        <!-- Rental History Tab -->
        <div class="tab-content" id="rental-history">
            <div class="rental-section">
                <h2>Rental History</h2>
                
                <?php if ($history_result->num_rows > 0): ?>
                    <?php while ($rental = $history_result->fetch_assoc()): ?>
                    <div class="rental-card">
                        <img src="<?php echo htmlspecialchars($rental['product_image']); ?>" alt="<?php echo htmlspecialchars($rental['product_name']); ?>" class="rental-image">
                        <div class="rental-details">
                            <h3><?php echo htmlspecialchars($rental['product_name']); ?> (x<?php echo $rental['item_quantity']; ?>)</h3>
                            <span class="rental-status status-returned">Returned</span>
                            <p class="rental-date">
                                Rented on: <?php echo date('F j, Y', strtotime($rental['rental_start'])); ?><br>
                                Returned on: <?php echo date('F j, Y', strtotime($rental['rental_end'])); ?>
                            </p>
                            <p class="rental-price">
                                ₹<?php echo number_format($rental['rental_cost'], 2); ?>/day
                            </p>
                            <?php
                                // Calculate total days rented
                                $start_date = new DateTime($rental['rental_start']);
                                $end_date = new DateTime($rental['rental_end']);
                                $days_rented = $start_date->diff($end_date)->days;
                                $total_cost = $days_rented * $rental['rental_cost'] * $rental['item_quantity'];
                            ?>
                            <p>Total rental duration: <?php echo $days_rented; ?> days</p>
                            <p>Total cost: ₹<?php echo number_format($total_cost, 2); ?></p>
                        </div>
                    </div>
                    <?php endwhile; ?>
                <?php else: ?>
                    <p>You don't have any rental history.</p>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
    $(document).ready(function(){
        // Toggle extend form visibility
        $(".show-extend-form").click(function(e) {
            e.preventDefault();
            const rentalId = $(this).data("rental-id");
            $("#extend-form-" + rentalId).toggle();
        });
        
        // Tab switching
        $(".tab").click(function() {
            $(".tab").removeClass("active");
            $(this).addClass("active");
            
            const tabId = $(this).data("tab");
            $(".tab-content").removeClass("active");
            $("#" + tabId).addClass("active");
        });
    });
    </script>
</body>
</html>

<?php
$conn->close();
?>