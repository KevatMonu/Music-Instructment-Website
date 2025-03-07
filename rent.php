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

// Add to cart as rental (AJAX)
if (isset($_GET['action']) && isset($_GET['id'])) {
    $id = intval($_GET['id']);
    if ($_GET['action'] == 'rent') {
        $_SESSION['cart'][$id] = ['quantity' => ($_SESSION['cart'][$id]['quantity'] ?? 0) + 1, 'type' => 'rent'];
        echo json_encode(["success" => true, "totalItems" => count($_SESSION['cart'])]);
        exit();
    }
}

// Count total rented items in cart
$totalItems = count($_SESSION['cart']);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Rent Instruments</title>
    <link rel="stylesheet" href="./css/style.css">
    <link rel="stylesheet" href="./css/products.css">
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

    <!-- Rental Section -->
    <h2>Rent Instruments</h2>
    <div class="products-container">
        <div class="product-containers" id="rental-list">
            <?php
            $result = $conn->query("SELECT * FROM products WHERE rental_price > 0 AND stock > 0");
            while ($row = $result->fetch_assoc()):
            ?>
                <div class="product">
                    <img src="<?php echo htmlspecialchars($row['image']); ?>" alt="<?php echo htmlspecialchars($row['name']); ?>" width="200px">
                    <h3><?php echo htmlspecialchars($row['name']); ?></h3>
                    <p><?php echo htmlspecialchars($row['description']); ?></p>
                    <p>Rent: ₹<?php echo number_format($row['rental_price'], 2); ?>/day</p>
                    <a href="#" class="rent-instrument" data-id="<?php echo $row['product_id']; ?>">Rent Now</a>
                </div>
            <?php endwhile; ?>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
    $(document).ready(function(){
        // Rent instrument using AJAX
        $(document).on("click", ".rent-instrument", function(e) {
            e.preventDefault();
            let productId = $(this).data("id");

            $.get("rent.php?action=rent&id=" + productId, function(response) {
                let data = JSON.parse(response);
                if (data.success) {
                    $("#cart-count").text(data.totalItems);
                }
            });
        });
    });
    </script>
</body>
</html>

<?php
$conn->close();
?>
