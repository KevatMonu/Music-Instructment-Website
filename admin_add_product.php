<?php
session_start();

// Database Connection
$conn = new mysqli("localhost", "root", "", "musicstore_database");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$message = "";

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = trim($_POST['name']);
    $description = trim($_POST['description']);
    $category_ref = intval($_POST['category']); // Fix: Using correct variable
    $price = floatval($_POST['price']);
    $image = $_FILES['image'];

    // Basic Validation
    if (empty($name) || empty($price) || empty($category_ref) || empty($image['name'])) {
        $message = "<p class='error-message'>All fields are required!</p>";
    } else {
        // Allowed Image Formats
        $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif'];
        $imageExtension = strtolower(pathinfo($image['name'], PATHINFO_EXTENSION));

        if (!in_array($imageExtension, $allowedExtensions)) {
            $message = "<p class='error-message'>Invalid image format! Only JPG, PNG, GIF allowed.</p>";
        } elseif ($image['size'] > 5000000) { // 5MB limit
            $message = "<p class='error-message'>File is too large! Max size: 5MB.</p>";
        } else {
            // Read image content
            $imageContent = file_get_contents($image['tmp_name']);
            $imageType = $image['type'];
            
            // Insert Product into Database
            $stmt = $conn->prepare("INSERT INTO products (product_name, product_description, category_ref, product_price, product_image, image_type) VALUES (?, ?, ?, ?, ?, ?)");
            $stmt->bind_param("ssidss", $name, $description, $category_ref, $price, $imageContent, $imageType);

            if ($stmt->execute()) {
                $message = "<p class='success-message'>Product added successfully!</p>";
            } else {
                $message = "<p class='error-message'>Error adding product: " . $stmt->error . "</p>";
            }
            $stmt->close();
        }
    }
}
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Product</title>
    <link rel="stylesheet" href="css/add_product.css">
</head>
<body>
<div class="container">
    <h2>Add New Product</h2>
    <?php echo $message; ?>
    <form action="" method="POST" enctype="multipart/form-data">
        <input type="text" name="name" placeholder="Product Name" required>
        <textarea name="description" placeholder="Product Description"></textarea>
        <select name="category" required>
            <option value="">Select Category</option>
            <?php
            // Fetch categories dynamically
            $conn = new mysqli("localhost", "root", "", "musicstore_database");
            if ($conn->connect_error) {
                die("Connection failed: " . $conn->connect_error);
            }
            $category_query = "SELECT category_id, category_name FROM categories";
            $result = $conn->query($category_query);
            
            if ($result) {
                while ($row = $result->fetch_assoc()) {
                    echo "<option value='{$row['category_id']}'>{$row['category_name']}</option>"; // Fixed category_id
                }
            } else {
                echo "<option value=''>Error loading categories</option>";
            }
            $conn->close();
            ?>
        </select>
        <input type="number" step="0.01" name="price" placeholder="Price" required>
        <input type="file" name="image" accept="image/*" required>
        <button type="submit">Add Product</button>
    </form>
</div>
</body>
</html>
