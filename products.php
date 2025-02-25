<?php
session_start();
include 'db_connection.php';

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

// Fetch products from the database
$query = "SELECT product_id, product_name, product_description, product_price, rental_cost, product_image, image_type FROM products";
$result = $conn->query($query);

// Count total items in cart
$totalItems = isset($_SESSION['cart']) ? array_sum($_SESSION['cart']) : 0;
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Products</title>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <link href="https://cdn.jsdelivr.net/npm/remixicon@4.5.0/fonts/remixicon.css" rel="stylesheet"/>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css" />
    <style>
        .product-container {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 15px;
            padding: 15px;
        }

        .product-card {
            border: 1px solid #ddd;
            padding: 10px;
            text-align: center;
            border-radius: 6px;
            box-shadow: 0 3px 6px rgba(0, 0, 0, 0.1);
            transition: transform 0.3s ease;
            font-size: 0.9em;
        }

        .product-card:hover {
            transform: translateY(-3px);
        }

        .product-card img {
            max-width: 80%;
            height: auto;
            margin-bottom: 8px;
            border-radius: 4px;
        }

        .product-card h3 {
            margin-bottom: 3px;
            font-size: 1em;
        }

        .product-card p {
            margin-bottom: 8px;
        }

        .product-card .add-to-cart {
            background-color: #007bff;
            color: white;
            border: none;
            padding: 8px 12px;
            border-radius: 4px;
            cursor: pointer;
            transition: background-color 0.3s ease;
            font-size: 0.85em;
        }

        .product-card .add-to-cart:hover {
            background-color: #0056b3;
        }

        h2 {
            text-align: center;
            margin-top: 15px;
        }

        #nav {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 15px 25px; /* Increased padding */
            background-color: #f8f8f8;
            border-bottom: 1px solid #ddd;
            height: 80px; /* Increased height */
        }

        .nav1 {
            display: flex;
            align-items: center;
        }

        .logo img {
            height: 60px; /* Increased logo size */
        }

        .nav-item ul {
            list-style: none;
            display: flex;
            margin-left: 20px; /* Increased margin */
        }

        .nav-item li {
            margin-right: 15px; /* Increased margin */
        }

        .nav-item a {
            text-decoration: none;
            color: #333;
            font-size: 1.1em; /* Increased font size */
        }

        .nav2-icon a {
            margin-left: 15px; /* Increased margin */
            color: #333;
            font-size: 1.2em; /* Increased icon size */
        }

    </style>
</head>
<body>

    <div id="nav">
        <div class="nav1">
            <div class="logo">
                <img src="assets/home/image/logo.png" alt="Logo" />
            </div>
            <div class="nav-item">
                <ul>
                    <a href="index.php"><li>Home</li></a>
                    <a href="products.php"><li>Products</li></a>
                    <a href="about.html"><li>About Us</li></a>
                    <a href="contact.html"><li>Contact Us</li></a>
                </ul>
            </div>
        </div>
        <div class="nav2">
            <div class="nav2-icon">
                <a href="cart.php"><i class="fa-solid fa-cart-shopping"></i>(<span id="cart-count"><?php echo $totalItems; ?></span>)</a>
                <a href="user_dashboard.php"><i class="fa-solid fa-user"></i></a>
            </div>
        </div>
    </div>

    <h2>All Products</h2>
    <div class="product-container">
        <?php while ($row = $result->fetch_assoc()): ?>
            <div class="product-card">
                <?php
                if (!empty($row['product_image'])) {
                    echo '<img src="data:' . $row['image_type'] . ';base64,' . base64_encode($row['product_image']) . '" alt="' . htmlspecialchars($row['product_name']) . '">';
                } else {
                    echo '<img src="assets/no-image.png" alt="No Image">';
                }
                ?>
                <h3><?php echo htmlspecialchars($row['product_name']); ?></h3>
                <p><?php echo htmlspecialchars($row['product_description']); ?></p>
                <p>Price: ₹<?php echo number_format($row['product_price'], 2); ?></p>
                <button class="add-to-cart" data-id="<?php echo $row['product_id']; ?>">Add to Cart</button>
            </div>
        <?php endwhile; ?>
    </div>

    <script>
    $(document).ready(function() {
        $(".add-to-cart").click(function(e) {
            e.preventDefault();
            let productId = $(this).data("id");

            $.get("products.php?action=add&id=" + productId, function(response) {
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

<?php $conn->close(); ?>