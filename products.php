<?php
session_start();
$conn = new mysqli("localhost", "root", "", "music_website");

// Check Connection
if ($conn->connect_error) {
    die("Database connection failed: " . $conn->connect_error);
}

// Initialize cart if not exists
if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

// Add to cart functionality (AJAX)
if (isset($_GET['action']) && isset($_GET['id'])) {
    $id = intval($_GET['id']);
    if ($_GET['action'] == 'add') {
        $_SESSION['cart'][$id] = ($_SESSION['cart'][$id] ?? 0) + 1;
        echo json_encode(["success" => true, "totalItems" => array_sum($_SESSION['cart'])]);
        exit();
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
    <title>Products</title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="css/products.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
</head>
<body>

    <!-- Navigation Bar -->
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
            <div class="nav2-1">
                <input type="search" id="search-box" placeholder="Search product..." />
            </div>
            <div class="nav2-icon">
                <a href="cart.php"><i class="fa-solid fa-cart-shopping"></i>(<span id="cart-count"><?php echo $totalItems; ?></span>)</a>
            </div>
        </div>
    </div>

    <!-- Product Section -->
    <h2>All Products</h2>
    <div class="products-container">
        <aside class="filters-sidebar">
            <h3>Filters</h3>
            <form id="filter-form">
                <div class="filter-section">
                    <h3>Categories</h3>
                    <div class="filter-options">
                        <?php
                        $categories = ['guitars', 'pianos', 'drums', 'wind', 'accessories'];
                        foreach ($categories as $category) {
                            echo "<label>
                                    <input type='checkbox' class='filter-checkbox' name='category[]' value='$category'>
                                    " . ucfirst($category) . "
                                </label>";
                        }
                        ?>
                    </div>
                </div>
                <div class="filter-section">
                    <h3>Price Range</h3>
                    <input type="range" id="price-range" min="0" max="10000" step="100">
                    <p>Up to ₹<span id="price-value">10000</span></p>
                </div>
                <div class="filter-section">
                    <h3>Sort By</h3>
                    <select id="sort-options">
                        <option value="">Default</option>
                        <option value="price_low">Price: Low to High</option>
                        <option value="price_high">Price: High to Low</option>
                    </select>
                </div>
            </form>
        </aside>

        <div class="product-containers" id="product-list">
            <!-- Products will be loaded here via AJAX -->
        </div>
    </div>

    <script>
    $(document).ready(function(){
        function fetchProducts() {
            let selectedCategories = [];
            $(".filter-checkbox:checked").each(function() {
                selectedCategories.push($(this).val());
            });

            let maxPrice = $("#price-range").val();
            let sortBy = $("#sort-options").val();
            let searchQuery = $("#search-box").val();

            $.ajax({
                url: "fetch_products.php",
                type: "POST",
                data: {
                    categories: selectedCategories,
                    maxPrice: maxPrice,
                    sortBy: sortBy,
                    searchQuery: searchQuery
                },
                success: function(response) {
                    $("#product-list").html(response);
                }
            });
        }

        // Initial Load
        fetchProducts();

        // Event Listeners for Filters
        $(".filter-checkbox, #price-range, #sort-options, #search-box").on("input change keyup", fetchProducts);

        // Update price display
        $("#price-range").on("input", function(){
            $("#price-value").text($(this).val());
        });

        // Add to Cart (AJAX)
        $(document).on("click", ".add-to-cart", function(e) {
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
