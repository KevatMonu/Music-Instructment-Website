<?php
session_start();
$conn = new mysqli("localhost", "root", "", "music_store");

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
                    <a href="products.php"><li>Product</li></a>
                    <a href="about.html"><li>About Us</li></a>
                    <a href="contact.html"><li>Contact Us</li></a>
                </ul>
            </div>
        </div>
        <div class="nav2">
            <div class="nav2-1">
                <input type="search" id="search-box" placeholder="Search product..." />
                <i class="fa-solid fa-magnifying-glass"></i>
            </div>
            <div class="nav2-icon">
                <i class="fa-regular fa-heart"></i>
                <a href="cart.php" class="cart-link">
                    <i class="fa-solid fa-cart-shopping"></i>(<span id="cart-count"><?php echo $totalItems; ?></span>)
                </a>
                <i class="fa-solid fa-user"></i>
            </div>
        </div>
    </div>

    <!-- Product Section -->
    <h2>All Products</h2>
    <div class="products-container">
        <!-- Sidebar Filters -->
        <aside class="filters-sidebar">
            <div class="filter-header">
                <h2>Filters</h2>
                <button id="clear-filters">Clear All</button>
            </div>

            <form id="filter-form">
                <div class="filter-section">
                    <h3>Categories</h3>
                    <div class="filter-options">
                        <?php
                        $categories = ['guitars', 'pianos', 'drums', 'wind', 'accessories'];
                        foreach ($categories as $category) {
                            echo "<label class='checkbox-container'>
                                    <input type='checkbox' class='filter-checkbox' name='category[]' value='$category'>
                                    <span class='checkmark'></span>
                                    " . ucfirst($category) . "
                                </label>";
                        }
                        ?>
                    </div>
                </div>

                <div class="filter-section">
                    <h3>Price Range</h3>
                    <input type="range" id="price-range" min="0" max="10000" value="10000" step="100">
                    <p>Up to ₹<span id="price-value">10000</span></p>
                </div>

                <div class="filter-section">
                    <h3>Sort By</h3>
                    <select id="sort-options">
                        <option value="">Default</option>
                        <option value="price_low">Price: Low to High</option>
                        <option value="price_high">Price: High to Low</option>
                        <option value="name_asc">Name: A to Z</option>
                        <option value="name_desc">Name: Z to A</option>
                    </select>
                </div>
            </form>
        </aside>

        <!-- Products List -->
        <div class="product-containers" id="product-list">
            <?php
            $result = $conn->query("SELECT * FROM products");
            while ($row = $result->fetch_assoc()):
            ?>
                <div class="product">
                    <img src="<?php echo htmlspecialchars($row['image']); ?>" alt="<?php echo htmlspecialchars($row['name']); ?>" width="200px">
                    <h3><?php echo htmlspecialchars($row['name']); ?></h3>
                    <p><?php echo htmlspecialchars($row['description']); ?></p>
                    <p>₹<?php echo number_format($row['price'], 2); ?></p>
                    <a href="#" class="add-to-cart" data-id="<?php echo $row['id']; ?>">Add to Cart</a>
                </div>
            <?php endwhile; ?>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
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

        // Load all products initially
        fetchProducts();

        // Filters event listener
        $(".filter-checkbox, #price-range, #sort-options, #search-box").on("input change keyup", function() {
            fetchProducts();
        });

        // Clear filters
        $("#clear-filters").click(function(){
            $(".filter-checkbox").prop("checked", false);
            $("#price-range").val(10000);
            $("#price-value").text(10000);
            $("#sort-options").val("");
            $("#search-box").val("");
            fetchProducts();
        });

        // Update price display
        $("#price-range").on("input", function(){
            $("#price-value").text($(this).val());
        });

        // Add to Cart with AJAX
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

<?php
$conn->close();
?>
