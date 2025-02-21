<?php
session_start();
$conn = new mysqli("localhost", "root", "", "music_website");

if ($conn->connect_error) {
    die("Database connection failed: " . $conn->connect_error);
}

// Get filters
$selectedCategories = $_POST['categories'] ?? [];
$maxPrice = $_POST['maxPrice'] ?? 10000;
$sortBy = $_POST['sortBy'] ?? "";
$searchQuery = $_POST['searchQuery'] ?? "";

// Build SQL query
$sql = "SELECT * FROM products WHERE price <= $maxPrice";

// Category Filter
if (!empty($selectedCategories)) {
    $escapedCategories = array_map(fn($cat) => $conn->real_escape_string($cat), $selectedCategories);
    $categoryPlaceholders = "'" . implode("','", $escapedCategories) . "'";
    $sql .= " AND category_id IN ($categoryPlaceholders)";
}

// Search Filter
if (!empty($searchQuery)) {
    $searchQuery = $conn->real_escape_string($searchQuery);
    $sql .= " AND name LIKE '%$searchQuery%'";
}

// Sorting
switch ($sortBy) {
    case "price_low":
        $sql .= " ORDER BY price ASC";
        break;
    case "price_high":
        $sql .= " ORDER BY price DESC";
        break;
    default:
        $sql .= " ORDER BY created_at DESC";
}

$result = $conn->query($sql);

if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        echo "<div class='product'>
                <img src='" . htmlspecialchars($row['image'] ?? 'assets/default.jpg') . "' alt='" . htmlspecialchars($row['name']) . "' width='200px'>
                <h3>" . htmlspecialchars($row['name']) . "</h3>
                <p>" . htmlspecialchars($row['description']) . "</p>
                <p>₹" . number_format($row['price'], 2) . "</p>
                <a href='#' class='add-to-cart' data-id='" . $row['product_id'] . "'>Add to Cart</a>
              </div>";
    }
} else {
    echo "<p>No products found.</p>";
}

$conn->close();
?>
