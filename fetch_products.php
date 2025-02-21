<?php
session_start();
$conn = new mysqli("localhost", "root", "", "music_store");

if ($conn->connect_error) {
    die("Database connection failed: " . $conn->connect_error);
}

// Get selected categories
$selectedCategories = isset($_POST['categories']) ? $_POST['categories'] : [];
$categoryFilterSQL = "";

// Filter products if categories are selected
if (!empty($selectedCategories)) {
    $escapedCategories = array_map(fn($cat) => $conn->real_escape_string($cat), $selectedCategories);
    $categoryPlaceholders = "'" . implode("','", $escapedCategories) . "'";
    $categoryFilterSQL = " WHERE category IN ($categoryPlaceholders)";
}

// Fetch products
$sql = "SELECT * FROM products" . $categoryFilterSQL;
$result = $conn->query($sql);

// Display products dynamically
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        echo "<div class='product-card'>
                <div class='img-box'>
                    <img src='" . htmlspecialchars($row['image']) . "' alt='" . htmlspecialchars($row['name']) . "'>
                </div>
                <h2>" . htmlspecialchars($row['name']) . "</h2>
                <p class='price'>₹" . number_format($row['price'], 2) . "</p>
                <p class='category'>Category: " . htmlspecialchars($row['category']) . "</p>
                <a href='products.php?action=add&id=" . $row['id'] . "' class='buy-btn'>
                    <i class='fas fa-shopping-cart'></i> Add To Cart
                </a>
              </div>";
    }
} else {
    echo "<p>No products found.</p>";
}

$conn->close();
?>
