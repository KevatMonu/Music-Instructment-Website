<?php
session_start();
$conn = new mysqli("localhost", "root", "", "music_website");

if ($conn->connect_error) {
    die("Database connection failed: " . $conn->connect_error);
}

$message = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = trim($_POST['name']);
    $description = trim($_POST['description']);
    $category_id = intval($_POST['category_id']);
    $price = floatval($_POST['price']);
    $rental_price = floatval($_POST['rental_price']);
    $stock = intval($_POST['stock']);

    // Ensure the uploads directory exists
    $target_dir = "uploads/";
    if (!is_dir($target_dir)) {
        mkdir($target_dir, 0777, true);
    }

    // Image validation
    if ($_FILES["image"]["error"] !== UPLOAD_ERR_OK) {
        $message = "<p style='color: red;'>Image upload error: " . $_FILES["image"]["error"] . "</p>";
    } else {
        $image_name = basename($_FILES["image"]["name"]);
        $target_file = $target_dir . $image_name;
        $imageFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));

        // Allow only JPG, JPEG, PNG files
        $allowed_types = ["jpg", "jpeg", "png"];
        if (!in_array($imageFileType, $allowed_types)) {
            $message = "<p style='color: red;'>Only JPG, JPEG, PNG files are allowed.</p>";
        } elseif ($_FILES["image"]["size"] > 5000000) { // Limit 5MB
            $message = "<p style='color: red;'>File is too large. Max size: 5MB</p>";
        } else {
            if (move_uploaded_file($_FILES["image"]["tmp_name"], $target_file)) {
                $image_path = $target_file;

                // Insert product data
                $stmt = $conn->prepare("INSERT INTO products (name, description, category_id, price, rental_price, stock, image) 
                                        VALUES (?, ?, ?, ?, ?, ?, ?)");
                $stmt->bind_param("ssiidis", $name, $description, $category_id, $price, $rental_price, $stock, $image_path);

                if ($stmt->execute()) {
                    $message = "<p style='color: green;'>Product added successfully!</p>";
                } else {
                    $message = "<p style='color: red;'>Error: " . $stmt->error . "</p>";
                }

                $stmt->close();
            } else {
                $message = "<p style='color: red;'>Failed to upload image.</p>";
            }
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
</head>
<body>

<h2>Add New Product</h2>

<?php echo $message; ?>

<form method="POST" enctype="multipart/form-data">
    <input type="text" name="name" placeholder="Product Name" required><br>
    <textarea name="description" placeholder="Product Description"></textarea><br>
    <select name="category_id" required>
        <option value="">Select Category</option>
        <?php
        // Fetch categories dynamically
        $conn = new mysqli("localhost", "root", "", "music_website");
        $category_query = "SELECT category_id, name FROM categories";
        $result = $conn->query($category_query);
        while ($row = $result->fetch_assoc()) {
            echo "<option value='{$row['category_id']}'>{$row['name']}</option>";
        }
        $conn->close();
        ?>
    </select><br>
    <input type="number" step="0.01" name="price" placeholder="Price" required><br>
    <input type="number" step="0.01" name="rental_price" placeholder="Rental Price" required><br>
    <input type="number" name="stock" placeholder="Stock Quantity" required><br>
    <input type="file" name="image" accept="image/*" required><br>
    <button type="submit">Add Product</button>
</form>

</body>
</html>
