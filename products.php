<?php
session_start();
include 'db_connection.php';

// Handle add to cart action
if (isset($_GET['action']) && $_GET['action'] == "add" && isset($_GET['id'])) {
    $productId = $_GET['id'];

    if (!isset($_SESSION['cart'])) {
        $_SESSION['cart'] = [];
    }

    if (isset($_SESSION['cart'][$productId])) {
        $_SESSION['cart'][$productId] += 1; // Increase quantity
    } else {
        $_SESSION['cart'][$productId] = 1; // Add new item
    }

    $totalItems = array_sum($_SESSION['cart']);

    echo json_encode(["success" => true, "totalItems" => $totalItems]);
    exit;
}

// Handle search
$searchQuery = '';
if (isset($_GET['search']) && !empty($_GET['search'])) {
    $searchQuery = $_GET['search'];
    $query = "SELECT product_id, product_name, product_description, product_price, rental_cost, product_image, image_type 
              FROM products 
              WHERE product_name LIKE ? OR product_description LIKE ?";
    $stmt = $conn->prepare($query);
    $searchParam = "%{$searchQuery}%";
    $stmt->bind_param("ss", $searchParam, $searchParam);
    $stmt->execute();
    $result = $stmt->get_result();
} else {
    // Fetch all products from the database
    $query = "SELECT product_id, product_name, product_description, product_price, rental_cost, product_image, image_type FROM products";
    $result = $conn->query($query);
}

// Count total items in cart
$totalItems = isset($_SESSION['cart']) ? array_sum($_SESSION['cart']) : 0;
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>TechShop - Products</title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="css/product.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
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
          <a href="cart.php" class="cart-link">
                            <i class="fas fa-shopping-cart"></i>
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
            <h1 class="page-title">
                <?php echo !empty($searchQuery) ? 'Search Results for "' . htmlspecialchars($searchQuery) . '"' : 'All Products'; ?>
            </h1>
            
            <?php if ($result->num_rows > 0): ?>
                <div class="product-grid">
                    <?php while ($row = $result->fetch_assoc()): ?>
                        <div class="product-card">
                            <div class="product-image">
                                <?php
                                if (!empty($row['product_image'])) {
                                    echo '<img src="data:' . $row['image_type'] . ';base64,' . base64_encode($row['product_image']) . '" alt="' . htmlspecialchars($row['product_name']) . '">';
                                } else {
                                    echo '<img src="assets/no-image.png" alt="No Image">';
                                }
                                ?>
                            </div>
                            <div class="product-info">
                                <h3 class="product-name"><?php echo htmlspecialchars($row['product_name']); ?></h3>
                                <p class="product-description"><?php echo htmlspecialchars($row['product_description']); ?></p>
                                <div class="product-price-container">
                                    <div class="product-price">₹<?php echo number_format($row['product_price'] ?? 0, 2); ?></div>
                                    <div class="rental-price">Rent: ₹<?php echo number_format($row['rental_cost'] ?? 0, 2); ?>/mo</div>
                                </div>
                                <button class="add-to-cart-btn" data-id="<?php echo $row['product_id']; ?>">
                                    <i class="fas fa-shopping-cart"></i> Add to Cart
                                </button>
                            </div>
                        </div>
                    <?php endwhile; ?>
                </div>
            <?php else: ?>
                <div class="no-results">
                    <i class="fas fa-search" style="font-size: 48px; color: #d1d5db; margin-bottom: 20px;"></i>
                    <p>No products found<?php echo !empty($searchQuery) ? ' for "' . htmlspecialchars($searchQuery) . '"' : ''; ?>.</p>
                    <?php if (!empty($searchQuery)): ?>
                        <a href="products.php" style="color: #4f46e5; margin-top: 10px; display: inline-block;">View all products</a>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Footer -->
    <footer>
        <div class="container">
            <div class="footer-content">
                <div class="footer-column">
                    <h3>TechShop</h3>
                    <p>Your one-stop destination for premium tech products and rentals. We offer the latest gadgets with flexible buying and renting options.</p>
                </div>
                
                <div class="footer-column">
                    <h3>Quick Links</h3>
                    <ul class="footer-links">
                        <li><a href="index.php"><i class="fas fa-chevron-right"></i> Home</a></li>
                        <li><a href="products.php"><i class="fas fa-chevron-right"></i> Products</a></li>
                        <li><a href="about.html"><i class="fas fa-chevron-right"></i> About Us</a></li>
                        <li><a href="contact.php"><i class="fas fa-chevron-right"></i> Contact Us</a></li>
                        <li><a href="rent.php"><i class="fas fa-chevron-right"></i> Rent</a></li>
                    </ul>
                </div>
                
                <div class="footer-column">
                    <h3>Contact Us</h3>
                    <ul class="footer-links">
                        <li><a href="#"><i class="fas fa-map-marker-alt"></i> 123 Tech Street, Digital City</a></li>
                        <li><a href="tel:+911234567890"><i class="fas fa-phone"></i> +91 1234567890</a></li>
                        <li><a href="mailto:info@techshop.com"><i class="fas fa-envelope"></i> info@techshop.com</a></li>
                    </ul>
                </div>
            </div>
            
            <div class="footer-bottom">
                <p>&copy; <?php echo date('Y'); ?> TechShop. All rights reserved.</p>
            </div>
        </div>
    </footer>

    <script>
    $(document).ready(function() {
        // Add to cart functionality
        $(".add-to-cart-btn").click(function(e) {
            e.preventDefault();
            let productId = $(this).data("id");
            let button = $(this);
            
            // Disable button temporarily to prevent multiple clicks
            button.prop('disabled', true);
            
            // Add loading effect
            button.html('<i class="fas fa-spinner fa-spin"></i> Adding...');
            
            $.get("products.php?action=add&id=" + productId, function(response) {
                try {
                    let data = JSON.parse(response);
                    if (data.success) {
                        // Update cart count
                        if ($(".cart-count").length) {
                            $(".cart-count").text(data.totalItems);
                        } else {
                            $(".cart-link").append('<span class="cart-count">' + data.totalItems + '</span>');
                        }
                        
                        // Show success feedback
                        button.html('<i class="fas fa-check"></i> Added!');
                        
                        // Reset button after delay
                        setTimeout(function() {
                            button.html('<i class="fas fa-shopping-cart"></i> Add to Cart');
                            button.prop('disabled', false);
                        }, 1500);
                    }
                } catch (e) {
                    console.error("Error parsing response:", e);
                    button.html('<i class="fas fa-shopping-cart"></i> Add to Cart');
                    button.prop('disabled', false);
                }
            }).fail(function() {
                // Handle error
                button.html('<i class="fas fa-exclamation-circle"></i> Error');
                setTimeout(function() {
                    button.html('<i class="fas fa-shopping-cart"></i> Add to Cart');
                    button.prop('disabled', false);
                }, 1500);
            });
        });
        
        // Auto-submit search form when typing
        let searchTimeout;
        $(".search-form input").on('input', function() {
            clearTimeout(searchTimeout);
            let query = $(this).val();
            
            if (query.length >= 3) {
                searchTimeout = setTimeout(function() {
                    $(".search-form").submit();
                }, 500);
            }
        });
    });
    </script>
</body>
</html>

<?php $conn->close(); ?>